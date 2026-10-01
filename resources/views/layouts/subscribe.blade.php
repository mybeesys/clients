<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('main.subscribe.title') }} — My Bee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/brand/mybee-mark.png') }}">
    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        :root {
            --bee-ink: #1a1a1a;
            --bee-muted: #6b7280;
            --bee-accent: #ebb81e;
            --bee-accent-2: #b88912;
            --bee-cream: #f7f4eb;
            --bee-soft: #fffdf7;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Cairo", system-ui, sans-serif;
            color: var(--bee-ink);
            background-color: var(--bee-cream);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(235, 184, 30, 0.22), transparent 55%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='84' height='48' viewBox='0 0 84 48'%3E%3Cpath fill='none' stroke='%23ebb81e' stroke-opacity='0.11' stroke-width='1' d='M21 0 L42 12 L42 36 L21 48 L0 36 L0 12 Z M63 0 L84 12 L84 36 L63 48 L42 36 L42 12 Z'/%3E%3C/svg%3E");
            background-size: auto, 84px 48px;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    {{ $slot }}
    {{-- Must load before Livewire/Alpine so customize-mode morph can call planConfigurator() --}}
    @include('partials.plan-configurator-script')
    @livewireScripts
</body>
</html>
