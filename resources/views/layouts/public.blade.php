<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Dummy CMS'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|outfit:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="cms-shell font-sans antialiased">
        <div class="cms-grid-overlay min-h-screen">
            <header class="border-b border-slate-200/70 bg-white/75 backdrop-blur">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="cms-brand text-2xl font-semibold text-[var(--cms-ink)]">
                        {{ config('app.name', 'Dummy CMS') }}
                    </a>

                    <nav class="flex flex-wrap items-center gap-3 text-sm font-medium text-slate-600">
                        <a href="{{ route('news.index') }}" class="hover:text-[var(--cms-accent)]">News</a>
                        <a href="{{ route('notices.index') }}" class="hover:text-[var(--cms-accent)]">Notices</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-[var(--cms-accent)]">Dashboard</a>
                            <a href="{{ route('admin.news.index') }}" class="cms-btn-accent">Admin</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-[var(--cms-accent)]">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="cms-btn-primary">Register</a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            <footer class="border-t border-slate-200/80 bg-white/60 py-8 text-center text-sm text-slate-500">
                <p class="cms-brand text-base font-semibold text-[var(--cms-ink)]">{{ config('app.name', 'Dummy CMS') }}</p>
                <p class="mt-1">Campus-style dummy CMS for auth, content, uploads, and deploy checks.</p>
            </footer>
        </div>
    </body>
</html>
