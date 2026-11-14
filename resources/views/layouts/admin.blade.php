<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin') — {{ config('app.name', 'Dummy CMS') }}</title>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { background: #f4f6f8; }
            .admin-shell { min-height: 100vh; }
            .admin-nav { background: #1f2937; }
            .admin-nav a { color: #e5e7eb; }
            .admin-nav a:hover { color: #fff; text-decoration: none; }
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
