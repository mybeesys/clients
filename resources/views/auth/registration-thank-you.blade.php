<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('main.registration_thank_you.title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Cairo', system-ui, sans-serif;
            background: linear-gradient(160deg, #fafafa 0%, #f3f4f6 45%, #fef9c3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #111827;
        }
        .card {
            width: 100%;
            max-width: 32rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            border: 1px solid #e5e7eb;
            padding: 2rem;
            text-align: center;
        }
        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.25rem;
            border-radius: 9999px;
            background: #fef9c3;
            color: #a16207;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
        h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.75rem; }
        p { margin: 0 0 1rem; color: #4b5563; line-height: 1.7; font-size: 0.95rem; }
        .company { font-weight: 700; color: #111827; }
        .url-box {
            margin: 1.25rem 0;
            padding: 0.875rem 1rem;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 0.75rem;
            word-break: break-all;
            font-size: 0.875rem;
            color: #374151;
            direction: ltr;
            text-align: left;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
            transition: opacity 0.15s;
        }
        .btn-primary {
            background: #eab308;
            color: #1f2937;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-secondary:hover { background: #e5e7eb; }
        .note { font-size: 0.8rem; color: #6b7280; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon" aria-hidden="true">✓</div>
        <h1>{{ __('main.registration_thank_you.title') }}</h1>
        <p>{{ __('main.registration_thank_you.message') }}</p>

        @if (! empty($company_name))
            <p class="company">{{ $company_name }}</p>
        @endif

        @if (! empty($tenant_url))
            <p>{{ __('main.registration_thank_you.link_intro') }}</p>
            <div class="url-box">{{ $tenant_url }}</div>
            <div class="actions">
                <a href="{{ $tenant_url }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                    {{ __('main.registration_thank_you.open_system') }}
                </a>
            </div>
        @else
            <p class="note">{{ __('main.registration_thank_you.no_link_yet') }}</p>
        @endif

        <div class="actions">
            <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-secondary">
                {{ __('main.registration_thank_you.back_to_login') }}
            </a>
        </div>

        @if (! empty($email))
            <p class="note">{{ __('main.registration_thank_you.verify_email_note', ['email' => $email]) }}</p>
        @endif
    </div>
</body>
</html>
