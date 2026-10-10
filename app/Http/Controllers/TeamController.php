<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('teams.index', [
            'teams' => Team::with(['owner:id,name', 'members:id,name'])
                ->withCount('members', 'projects')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('teams.create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'members' => ['array'],
            'members.*' => ['exists:users,id'],
        ]);

        $team = Team::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => auth()->id(),
        ]);

        $team->members()->attach([auth()->id(), ...$data['members'] ?? []]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team created.');
    }

    public function show(Team $team): View
    {
        $team->load(['owner:id,name', 'members:id,name,role', 'projects:id,name,status']);

        return view('teams.show', [
            'team' => $team,
            'users' => User::whereNotIn('id', $team->members()->pluck('users.id'))->orderBy('name')->get(['id', 'name']),
            'canManage' => $this->canManage($team),
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        abort_unless($this->canManage($team), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['exists:users,id'],
            'remove' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $team->update($request->only(['name', 'description']));

        if ($request->has('members')) {
            $team->members()->syncWithoutDetaching($data['members']);
        }

        if ($request->filled('remove')) {
            $team->members()->detach($data['remove']);
        }

        return back()->with('success', 'Team updated.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        abort_unless($this->canManage($team), 403);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted.');
    }

    protected function canManage(Team $team): bool
    {
        return $team->owner_id === auth()->id() || auth()->user()->role === UserRole::Admin;
    }
}
