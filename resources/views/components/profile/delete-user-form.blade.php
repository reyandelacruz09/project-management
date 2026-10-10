<section {{ $attributes->merge(['class' => 'space-y-6']) }}>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>

        <p class="mt-1 text-sm text-gray-600">
            Once your account is deleted, all of its resources and data will be permanently deleted. Before
            deleting your account, please download any data or information that you wish to retain.
        </p>
    </header>

    <x-modal>
        <x-slot:trigger>
            <x-danger-button class="uppercase">Delete Account</x-danger-button>
        </x-slot:trigger>

        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to delete your account?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Please
                enter your password to confirm you would like to permanently delete your account.
            </p>

            <div class="mt-6">
                <x-input-label for="delete_password" value="Password" class="sr-only" />

                <x-text-input id="delete_password" name="password" type="password" class="mt-1 block w-3/4"
                    placeholder="Password" autofocus />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="open = false">Cancel</x-secondary-button>
                <x-danger-button>Delete Account</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>