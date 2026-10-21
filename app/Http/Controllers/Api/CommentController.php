<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $commentableType = $request->query('commentable_type');

        return response()->json(
            Comment::query()
                ->when($commentableType, fn ($query) => $query->where('commentable_type', $commentableType))
                ->with('user:id,name')
                ->latest()
                ->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->commentableExists($data['commentable_type'], (int) $data['commentable_id'])) {
            return response()->json(['message' => 'Commentable not found.'], 404);
        }

        $comment = Comment::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($comment->load('user:id,name'), 201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }

    protected function commentableExists(string $type, int $id): bool
    {
        $model = $type === Project::class ? Project::class : Task::class;

        return $model::whereKey($id)->exists();
    }
}
