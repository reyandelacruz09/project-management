<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Team::with('owner:id,name', 'members:id,name')->withCount('members', 'projects')->get()
        );
    }

    public function store(Request $request): JsonResponse
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
            'owner_id' => $request->user()->id,
        ]);

        $team->members()->attach([$request->user()->id, ...$data['members'] ?? []]);

        return response()->json($team->load('owner:id,name', 'members:id,name'), 201);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json($team->load('owner:id,name', 'members:id,name', 'projects:id,name,status'));
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'members' => ['array'],
            'members.*' => ['exists:users,id'],
            'remove' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $team->update([
            'name' => $data['name'] ?? $team->name,
            'description' => $data['description'] ?? $team->description,
        ]);

        if ($request->has('members')) {
            $team->members()->syncWithoutDetaching($data['members']);
        }

        if ($request->filled('remove')) {
            $team->members()->detach($data['remove']);
        }

        return response()->json($team->load('owner:id,name', 'members:id,name'));
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json(null, 204);
    }
}
