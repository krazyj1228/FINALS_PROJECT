<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Build your portfolio') · FolioLab</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <div class="shell">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}"><img class="brand-mark" src="{{ asset('foliolab-mark.svg') }}" alt=""> FolioLab</a>
            <nav class="nav-links" aria-label="Main navigation">
                @auth
                    <a class="nav-manage-link" href="{{ route('portfolios.index') }}">My portfolios</a>
                    <a class="button button-small" href="{{ route('portfolios.create') }}">Create portfolio <span aria-hidden="true">↗</span></a>
                    <span class="nav-user">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="post">@csrf<button class="nav-logout" type="submit">Log out</button></form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a class="button button-small" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </header>
    </div>
    @yield('content')
    <footer class="site-footer"><div class="shell">FolioLab · A home for your next chapter.</div></footer>
</body>
</html>