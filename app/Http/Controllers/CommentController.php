<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        abort_unless($this->commentableExists($data['commentable_type'], (int) $data['commentable_id']), 404);

        Comment::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }

    protected function commentableExists(string $type, int $id): bool
    {
        $model = $type === Project::class ? Project::class : Task::class;

        return $model::whereKey($id)->exists();
    }
}
