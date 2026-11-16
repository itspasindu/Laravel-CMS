<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin') — {{ config('app.name', 'Dummy CMS') }}</title>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                background:
                    radial-gradient(900px 420px at 0% 0%, rgba(15, 118, 110, 0.12), transparent 55%),
                    #eef3f7;
                font-family: Outfit, system-ui, sans-serif;
            }
            .admin-shell { min-height: 100vh; }
            .admin-nav {
                background: linear-gradient(120deg, #10233f, #0f766e);
                box-shadow: 0 8px 24px rgba(16, 35, 63, 0.18);
            }
            .admin-nav a { color: #e5e7eb; font-weight: 500; }
            .admin-nav a:hover { color: #fff; text-decoration: none; }
            .admin-nav .navbar-brand { letter-spacing: -0.02em; }
            .card { border-radius: 1rem; }
            .btn-primary { background: #0f766e; border-color: #0f766e; }
            .btn-primary:hover { background: #0d9488; border-color: #0d9488; }
        </style>
    </head>
    <body>
        <div class="admin-shell">
            <nav class="admin-nav navbar navbar-expand-md px-3 py-3">
                <a class="navbar-brand text-white font-weight-bold" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> {{ config('app.name', 'Dummy CMS') }} Admin
                </a>
                <div class="ml-auto d-flex align-items-center" style="gap: 1rem;">
                    <a href="{{ route('home') }}"><i class="bi bi-house"></i> Public site</a>
                    <a href="{{ route('admin.news.index') }}"><i class="bi bi-newspaper"></i> News</a>
                    <a href="{{ route('admin.notices.index') }}"><i class="bi bi-megaphone"></i> Notices</a>
                    <a href="{{ route('dashboard') }}"><i class="bi bi-grid"></i> Dashboard</a>
                </div>
            </nav>

            <main class="container py-4">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
