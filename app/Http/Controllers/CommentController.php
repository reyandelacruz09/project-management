<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commentable_type' => ['required', 'string', 'in:App\Models\Project,App\Models\Task'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Comment::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === auth()->id() || auth()->user()->role === UserRole::Admin, 403);

        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }
}
