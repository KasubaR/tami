<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#fffaf3">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-dvh">
    <div class="mx-auto flex min-h-dvh w-full max-w-3xl flex-col px-4 pb-[env(safe-area-inset-bottom)]">
        @hasSection('header')
            @yield('header')
        @endif

        <main class="flex-1 py-6">
            @yield('content')
        </main>

        <footer class="py-6 text-center text-sm text-ink-soft">
            Made with love for Tamara 🎀
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
