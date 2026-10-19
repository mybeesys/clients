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
            --bee-cream: #f7f4eb;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Cairo", system-ui, sans-serif;
            color: var(--bee-ink);
            background: var(--bee-cream);
        }
        body { padding: 0.75rem 0.85rem 1.25rem; }
    </style>
</head>
<body>
    {{ $slot }}
    @include('partials.plan-configurator-script')
    @livewireScripts
    <script>
        (function () {
            function reportHeight() {
                if (window.parent === window) return;
                var height = Math.max(
                    document.documentElement.scrollHeight || 0,
                    document.body.scrollHeight || 0,
                    480
                );
                window.parent.postMessage(
                    { type: 'subscribe-embed-height', height: height },
                    '*'
                );
            }
            window.addEventListener('load', reportHeight);
            window.addEventListener('resize', reportHeight);
            if (window.Livewire) {
                document.addEventListener('livewire:navigated', reportHeight);
                document.addEventListener('livewire:update', reportHeight);
            }
            setInterval(reportHeight, 1200);
        })();
    </script>
</body>
</html>
