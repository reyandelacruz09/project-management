@props(['trigger'])

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = true" {{ $attributes }}>
        {{ $trigger }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-gray-500/75" x-on:click="open = false" aria-hidden="true"></div>

        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div x-show="open" x-transition.duration.200ms class="relative w-full rounded-lg bg-white p-6 text-left shadow-xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>