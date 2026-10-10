@props(['type' => 'success'])

@if (session($type))
    <div class="rounded-md {{ $type === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }} px-4 py-3 text-sm font-medium">
        {{ session($type) }}
    </div>
@endif