@props(['commentableType', 'commentableId', 'comments'])

<details class="group">
    <summary class="cursor-pointer list-none text-sm font-medium text-indigo-600 hover:underline">
        Comments ({{ $comments->count() }})
    </summary>

    <div class="mt-3 space-y-3">
        <ul class="space-y-3">
            @foreach ($comments as $comment)
                <li class="rounded-md bg-gray-50 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="text-sm">
                            <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                            <span class="ml-2 text-xs text-gray-400">{{ $comment->created_at }}</span>
                        </div>

                        @can('delete', $comment)
                            <form method="POST" action="{{ route('comments.destroy', $comment) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                            </form>
                        @endcan
                    </div>

                    <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700">{{ $comment->body }}</p>
                </li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('comments.store') }}" class="flex items-start gap-2">
            @csrf
            <input type="hidden" name="commentable_type" value="{{ $commentableType }}">
            <input type="hidden" name="commentable_id" value="{{ $commentableId }}">

            <textarea name="body" rows="2" placeholder="Write a comment..." required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            <x-primary-button>Add</x-primary-button>
        </form>
    </div>
</details>