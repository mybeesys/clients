<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status === 'ready' ? __('main.registration_thank_you.title') : __('main.registration_progress.title') }}</title>
    <link rel="icon" href="{{ asset('images/brand/mybee-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Cairo', system-ui, sans-serif;
            color: #1c1917;
            background:
                radial-gradient(900px 420px at 50% -10%, rgba(235, 184, 30, 0.28), transparent 60%),
                linear-gradient(180deg, #fffaf0 0%, #f7f3ea 48%, #f3efe4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 34rem;
            background: #fff;
            border-radius: 1.75rem;
            border: 1px solid rgba(235, 184, 30, 0.28);
            box-shadow: 0 24px 60px rgba(92, 64, 8, 0.08);
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
        }
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 1.35rem;
        }
        .brand img {
            width: 148px;
            height: auto;
            object-fit: contain;
        }
        .tagline {
            margin: 0;
            font-size: 0.82rem;
            font-weight: 600;
            color: #a16207;
            letter-spacing: 0.01em;
        }
        .rule {
            height: 1px;
            margin: 0 0 1.4rem;
            background: linear-gradient(90deg, transparent, rgba(235, 184, 30, 0.7), transparent);
        }
        .mark {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 0.9rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff7d6;
            color: #a16207;
            box-shadow: inset 0 0 0 1px rgba(235, 184, 30, 0.35);
        }
        .spinner {
            width: 1.35rem;
            height: 1.35rem;
            border: 2.5px solid #fde68a;
            border-top-color: #a16207;
            border-radius: 999px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 {
            margin: 0 0 0.45rem;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        p { margin: 0 0 0.85rem; color: #57534e; line-height: 1.75; font-size: 0.98rem; }
        .company {
            display: inline-block;
            margin: 0 0 1rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: #fffaf0;
            color: #1c1917;
            font-weight: 800;
            border: 1px solid rgba(235, 184, 30, 0.35);
        }
        .link-panel {
            margin: 0.4rem 0 1.15rem;
            padding: 0.9rem;
            border-radius: 1rem;
            background: #fafaf9;
            border: 1px solid #e7e5e4;
            text-align: start;
        }
        .link-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #a16207;
        }
        .link-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .url {
            flex: 1;
            min-width: 0;
            direction: ltr;
            text-align: left;
            font-size: 0.92rem;
            font-weight: 700;
            color: #292524;
            word-break: break-all;
        }
        .copy {
            flex-shrink: 0;
            border: 0;
            background: #fff;
            color: #57534e;
            border-radius: 0.7rem;
            padding: 0.45rem 0.7rem;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.78rem;
            cursor: pointer;
            box-shadow: inset 0 0 0 1px #e7e5e4;
        }
        .btn {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            padding: 0.9rem 1.25rem;
            border-radius: 0.95rem;
            background: #ebb81e;
            color: #1c1917;
            font-weight: 800;
            font-size: 1rem;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 10px 24px rgba(235, 184, 30, 0.28);
        }
        .note { font-size: 0.8rem; color: #78716c; margin: 1rem 0 0; }
        .error {
            margin: 0 0 1rem;
            padding: 0.8rem 0.9rem;
            border-radius: 0.85rem;
            background: #fff7ed;
            color: #9a3412;
            font-size: 0.82rem;
            line-height: 1.6;
            text-align: start;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <img src="{{ asset('images/brand/mybee-lockup.png') }}" alt="My Bee">
            <p class="tagline">{{ __('main.wizard.register_tagline') }}</p>
        </div>
        <div class="rule"></div>

        @if ($status === 'ready' && ! empty($tenantUrl))
            <div class="mark" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12.5 9.2 17 19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1>{{ __('main.registration_thank_you.title') }}</h1>
            <p>{{ __('main.registration_thank_you.message') }}</p>
            @if (! empty($companyName))
                <div class="company">{{ $companyName }}</div>
            @endif
            <div class="link-panel">
                <span class="link-label">{{ __('main.registration_thank_you.link_intro') }}</span>
                <div class="link-row">
                    <div class="url" id="tenant-url">{{ $tenantUrl }}</div>
                    <button type="button" class="copy" id="copy-link">{{ __('main.registration_thank_you.copy_link') }}</button>
                </div>
            </div>
            <a href="{{ $tenantUrl }}" class="btn" target="_blank" rel="noopener noreferrer">
                {{ __('main.registration_thank_you.open_system') }}
            </a>
            @if (! empty($email))
                <p class="note">{{ __('main.registration_thank_you.verify_email_note', ['email' => $email]) }}</p>
            @endif
            <script>
                document.getElementById('copy-link').addEventListener('click', function () {
                    var button = this;
                    var url = document.getElementById('tenant-url').textContent.trim();
                    var done = function () {
                        button.textContent = @json(__('main.registration_thank_you.copied'));
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(done);
                        return;
                    }
                    var field = document.createElement('textarea');
                    field.value = url;
                    document.body.appendChild(field);
                    field.select();
                    document.execCommand('copy');
                    field.remove();
                    done();
                });
            </script>
        @elseif ($status === 'failed')
            <div class="mark" aria-hidden="true">!</div>
            <h1>{{ __('main.registration_progress.failed_title') }}</h1>
            <p>{{ __('main.registration_progress.failed') }}</p>
            @if (! empty($companyName))
                <div class="company">{{ $companyName }}</div>
            @endif
            @if (! empty($error))
                <div class="error">{{ $error }}</div>
            @endif
            <form method="post" action="{{ route('register.pending.retry') }}">
                @csrf
                <button type="submit" class="btn">{{ __('main.registration_progress.retry') }}</button>
            </form>
        @else
            <div class="mark" aria-hidden="true"><div class="spinner"></div></div>
            <h1>{{ __('main.registration_progress.title') }}</h1>
            <p>{{ __('main.registration_progress.message') }}</p>
            @if (! empty($companyName))
                <div class="company">{{ $companyName }}</div>
            @endif
            <p class="note" id="progress-detail">{{ $detail ?? '' }}</p>
            <p class="note">{{ __('main.registration_progress.hint') }}</p>
            <noscript><meta http-equiv="refresh" content="5"></noscript>
            <script>
                function pollProgress() {
                    fetch(@json(route('register.pending.status')), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).then(function (response) {
                        return response.ok ? response.json() : null;
                    }).then(function (body) {
                        if (! body) {
                            return;
                        }
                        var detail = document.getElementById('progress-detail');
                        if (detail && body.detail) {
                            detail.textContent = body.detail;
                        }
                        if (body.status === 'ready' || body.status === 'failed') {
                            window.location.reload();
                        }
                    }).catch(function () {}).finally(function () {
                        setTimeout(pollProgress, 1000);
                    });
                }
                pollProgress();
            </script>
        @endif
    </div>
</body>
</html>
