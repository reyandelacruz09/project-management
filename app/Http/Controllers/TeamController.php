<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

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

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $team = Team::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => auth()->id(),
        ]);

        $team->members()->syncWithoutDetaching([auth()->id(), ...$data['members'] ?? []]);

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

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $data = $request->validated();

        $team->update($request->safe()->only(['name', 'description']));

        if ($request->has('members')) {
            $team->members()->syncWithoutDetaching($data['members']);
        }

        if ($request->filled('remove') && (int) $data['remove'] !== $team->owner_id) {
            $team->members()->detach($data['remove']);
        }

        return back()->with('success', 'Team updated.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        Gate::authorize('delete', $team);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted.');
    }

    public function canManage(Team $team): bool
    {
        return Gate::allows('update', $team);
    }
}
