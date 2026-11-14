<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Dummy CMS'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name', 'Dummy CMS') }}
                </a>

                <nav class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
                        <a href="{{ route('admin.news.index') }}" class="text-slate-600 hover:text-slate-900">Admin</a>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md bg-slate-900 px-3 py-1.5 text-white hover:bg-slate-700">Register</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 py-6 text-center text-sm text-slate-500">
            Lightweight dummy CMS for build, migrate, auth, and storage checks.
        </footer>
    </body>
</html>
