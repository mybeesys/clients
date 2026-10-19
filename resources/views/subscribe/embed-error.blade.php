<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('main.subscribe.embed_error_title') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Cairo", system-ui, sans-serif;
            background: #f7f4eb;
            color: #1a1a1a;
            padding: 1.5rem;
        }
        .box {
            max-width: 420px;
            width: 100%;
            background: #fffdf7;
            border: 1px solid rgba(26,26,26,.08);
            border-radius: 1rem;
            padding: 1.35rem 1.25rem;
            box-shadow: 0 16px 40px rgba(26,26,26,.07);
            text-align: center;
        }
        h1 { font-size: 1.15rem; margin: 0 0 .5rem; }
        p { margin: 0 0 1rem; color: #6b7280; line-height: 1.55; }
        button {
            border: 0;
            border-radius: .75rem;
            background: #ebb81e;
            color: #1a1a1a;
            font-weight: 800;
            padding: .7rem 1.1rem;
            cursor: pointer;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>{{ __('main.subscribe.embed_error_title') }}</h1>
        <p>{{ $message ?? __('main.subscribe.embed_error_body') }}</p>
        <button type="button" onclick="window.parent.postMessage({ type: 'subscribe-embed-reload' }, '*')">
            {{ __('main.subscribe.embed_error_retry') }}
        </button>
    </div>
    <script>
        window.parent.postMessage({ type: 'subscribe-embed-failed' }, '*');
    </script>
</body>
</html>
