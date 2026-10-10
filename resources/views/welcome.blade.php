<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg" alt="">

        <div class="relative flex min-h-screen flex-col items-center justify-center">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <x-application-logo class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]" />
                    </div>

                    <nav class="-mx-3 flex flex-1 justify-end">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70">
                                Log in
                            </a>
                            <a href="{{ route('register') }}"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70">
                                Register
                            </a>
                        @endauth
                    </nav>
                </header>

                <main class="mt-6">
                    <div class="rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10">
                        <h2 class="text-xl font-semibold text-black dark:text-white">{{ config('app.name') }}</h2>

                        <p class="mt-4 text-sm/relaxed">
                            A project management workspace for tracking projects, teams, tasks and discussions.
                            Start by creating a project, inviting your team and breaking the work down into tasks.
                        </p>

                        <div class="mt-6 flex gap-4">
                            @auth
                                <a href="{{ route('projects.index') }}"
                                    class="rounded-md bg-[#FF2D20] px-4 py-2 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-red-700">
                                    View projects
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="rounded-md bg-[#FF2D20] px-4 py-2 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-red-700">
                                    Log in
                                </a>
                            @endauth
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </footer>
            </div>
        </div>
    </div>
</body>
</html>