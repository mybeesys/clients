<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('main.wizard.email_verify_confirmed_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Cairo, system-ui, sans-serif;
            background: #faf8f2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1a1a1a;
        }
        .card {
            width: 100%;
            max-width: 30rem;
            background: #fff;
            border-radius: 1.5rem;
            border: 1px solid rgba(235, 184, 30, .35);
            box-shadow: 0 18px 48px rgba(26, 26, 26, .08);
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .mark {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 999px;
            background: #fff7db;
            color: #b88912;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 800;
        }
        h1 { margin: 0 0 .75rem; font-size: 1.45rem; }
        p { margin: 0; color: #4b5563; line-height: 1.75; }
        .email { margin-top: .8rem; font-weight: 800; color: #1a1a1a; direction: ltr; }
    </style>
</head>
<body>
    <div class="card">
        <div class="mark" aria-hidden="true">✓</div>
        <h1>{{ __('main.wizard.email_verify_confirmed_title') }}</h1>
        <p>{{ __('main.wizard.email_verify_confirmed_body') }}</p>
        @if (! empty($email))
            <p class="email">{{ $email }}</p>
        @endif
    </div>
</body>
</html>
