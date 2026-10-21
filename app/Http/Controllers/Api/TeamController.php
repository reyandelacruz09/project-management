<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Team::with('owner:id,name', 'members:id,name')->withCount('members', 'projects')->get()
        );
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $data = $request->validated();

        $team = Team::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => $request->user()->id,
        ]);

        $team->members()->syncWithoutDetaching([$request->user()->id, ...$data['members'] ?? []]);

        return response()->json($team->load('owner:id,name', 'members:id,name'), 201);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json($team->load('owner:id,name', 'members:id,name', 'projects:id,name,status'));
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $data = $request->validated();

        $team->update($request->safe()->only(['name', 'description']));

        if ($request->has('members')) {
            $team->members()->syncWithoutDetaching($data['members']);
        }

        if ($request->filled('remove') && (int) $data['remove'] !== $team->owner_id) {
            $team->members()->detach($data['remove']);
        }

        return response()->json($team->load('owner:id,name', 'members:id,name'));
    }

    public function destroy(Team $team): JsonResponse
    {
        Gate::authorize('delete', $team);

        $team->delete();

        return response()->json(null, 204);
    }
}
