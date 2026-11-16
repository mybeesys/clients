<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('main.company_portal.choose_title') }} — My Bee</title>
    <link rel="icon" href="{{ asset('images/brand/mybee-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --honey: #ebb81e;
            --ink: #1c1917;
            --muted: #78716c;
            --paper: #fffdf8;
            --line: #f0e6d4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Cairo", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(235, 184, 30, 0.18), transparent 42%),
                linear-gradient(180deg, #fff8e8 0%, var(--paper) 55%, #fff 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .card {
            width: min(100%, 460px);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 32px 28px;
            box-shadow: 0 24px 60px rgba(28, 25, 23, 0.08);
        }
        .logo {
            display: block;
            width: min(180px, 60%);
            margin: 0 auto 18px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 1.35rem;
            text-align: center;
        }
        p {
            margin: 0 0 22px;
            color: var(--muted);
            text-align: center;
            line-height: 1.7;
            font-size: 0.95rem;
        }
        .list {
            display: grid;
            gap: 10px;
        }
        a.company {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--line);
            text-decoration: none;
            color: inherit;
            background: #fffefb;
            transition: border-color .15s ease, transform .15s ease, background .15s ease;
        }
        a.company:hover {
            border-color: var(--honey);
            background: #fff8e4;
            transform: translateY(-1px);
        }
        .name {
            font-weight: 700;
        }
        .hint {
            font-size: 0.8rem;
            color: var(--muted);
        }
        .chevron {
            color: var(--honey);
            font-size: 1.2rem;
            line-height: 1;
        }
    </style>
</head>
<body>
    <main class="card">
        <img class="logo" src="{{ asset('images/brand/mybee-lockup.png') }}" alt="My Bee">
        <h1>{{ __('main.company_portal.choose_title') }}</h1>
        <p>{{ __('main.company_portal.choose_message') }}</p>

        <div class="list">
            @foreach ($companies as $company)
                <a class="company" href="{{ route('company-portal.enter', $company['tenant_id']) }}">
                    <span>
                        <span class="name">{{ $company['company_name'] }}</span>
                        @unless ($company['has_subscription'])
                            <div class="hint">{{ __('main.company_portal.needs_subscription') }}</div>
                        @endunless
                    </span>
                    <span class="chevron" aria-hidden="true">{{ app()->getLocale() === 'ar' ? '‹' : '›' }}</span>
                </a>
            @endforeach
        </div>
    </main>
</body>
</html>
