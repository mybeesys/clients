<!DOCTYPE html>
@php
    $local = app()->getLocale();
    $dir = $local === 'ar' ? 'rtl' : 'ltr';
    $switchLocale = $local === 'ar' ? 'en' : 'ar';
@endphp
<html lang="{{ $local }}" dir="{{ $dir }}">

<head>
    <title>{{ __('main.company_portal.sign_in') }} — My Bee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light" />
    <link rel="icon" href="{{ asset('images/brand/mybee-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('auth.partials.guest-auth-styles')
    <style>
        body { font-family: "Cairo", sans-serif; }
        .btn-primary {
            --bs-btn-color: #1c1917;
            --bs-btn-bg: #ebb81e;
            --bs-btn-border-color: #ebb81e;
            --bs-btn-hover-color: #1c1917;
            --bs-btn-hover-bg: #d4a41a;
            --bs-btn-hover-border-color: #d4a41a;
            --bs-btn-active-color: #1c1917;
            --bs-btn-active-bg: #b88912;
            --bs-btn-active-border-color: #b88912;
        }
        .form-control-solid {
            background-color: #fff;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 0.65rem;
            min-height: 46px;
        }
        .form-control-solid:focus {
            background-color: #fff;
            border-color: rgba(235, 184, 30, 0.7);
            box-shadow: 0 0 0 0.2rem rgba(235, 184, 30, 0.2);
        }
        .login-auth-register {
            margin-top: 1.35rem;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--login-muted);
        }
        .login-auth-register a {
            color: var(--login-accent);
            text-decoration: none;
            font-weight: 700;
        }
        .login-auth-register a:hover {
            text-decoration: underline;
        }
        .login-password-toggle svg {
            width: 1.15rem;
            height: 1.15rem;
        }
        .indicator-progress { display: none; }
        [data-kt-indicator="on"] .indicator-label { display: none; }
        [data-kt-indicator="on"] .indicator-progress { display: inline-block; }
        .text-primary { color: var(--login-accent) !important; }
        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.28);
        }
    </style>
</head>

<body class="app-blank login-auth-page" data-bs-theme="light">
    <div id="initial-loader" class="page-loader">
        <span class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </span>
    </div>

    <div class="login-auth-wrap">
        <div class="login-auth-card">
            <div class="row g-0 align-items-stretch">
                <div class="col-12 col-lg-6 login-auth-panel login-auth-panel--brand">
                    <a href="{{ url('/') }}" class="login-auth-logo text-decoration-none d-inline-block mb-2" aria-label="My Bee">
                        <img src="{{ asset('images/brand/mybee-lockup.png') }}" alt="My Bee" />
                    </a>
                    <span class="login-auth-tenant">{{ __('main.wizard.register_tagline') }}</span>
                    <h1 class="login-auth-title">{{ __('main.company_portal.login_hero_title') }}</h1>
                    <p class="login-auth-sub">{{ __('main.company_portal.login_hero_subtitle') }}</p>

                    <div class="login-auth-aside-footer">
                        <a
                            href="{{ route('set_locale', ['locale' => $switchLocale, 'redirect' => url()->full()]) }}"
                            class="login-lang-toggle"
                            aria-label="{{ $switchLocale === 'ar' ? 'العربية' : 'English' }}"
                        >
                            @if ($switchLocale === 'ar')
                                <img src="{{ asset('assets/media/flags/saudi-arabia.svg') }}" alt="" />
                                <span>العربية</span>
                            @else
                                <img src="{{ asset('assets/media/flags/united-states.svg') }}" alt="" />
                                <span>English</span>
                            @endif
                        </a>
                    </div>
                </div>

                <div class="col-12 col-lg-6 login-auth-panel login-auth-panel--form">
                    @if ($errors->any() || session('error') || session('success'))
                        <div class="login-auth-notice login-auth-notice--{{ $errors->any() || session('error') ? 'danger' : 'warning' }} mb-4" role="alert" aria-live="polite">
                            <div class="login-auth-notice__content">
                                <p class="login-auth-notice__title mb-0">
                                    {{ $errors->first() ?: (session('error') ?: session('success')) }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="login-auth-divider">
                        <span>{{ __('main.company_portal.login_to_your_company') }}</span>
                    </div>

                    <form action="{{ route('login.store') }}" method="POST" class="form w-100" id="kt_sign_in_form" novalidate>
                        @csrf

                        <div class="fv-row mb-6">
                            <label for="company_code" class="form-label fw-semibold fs-7 mb-1">{{ __('main.company_portal.company_code') }}</label>
                            <input type="text" name="company_code" id="company_code" value="{{ old('company_code') }}"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                autocomplete="organization"
                                placeholder="{{ __('main.company_portal.company_code_placeholder') }}"
                                class="form-control form-control-solid @error('company_code') is-invalid @enderror" />
                            @error('company_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fv-row mb-6">
                            <label for="email" class="form-label fw-semibold fs-7 mb-1">{{ __('main.company_portal.email_or_user_name') }}</label>
                            <input type="text" name="email" id="email" value="{{ old('email') }}" required
                                autocomplete="username"
                                placeholder="{{ __('main.company_portal.email_or_user_name') }}"
                                class="form-control form-control-solid @error('email') is-invalid @enderror" />
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fv-row mb-2">
                            <label for="password" class="form-label fw-semibold fs-7 mb-1">{{ __('main.company_portal.password') }}</label>
                            <div class="login-password-field position-relative">
                                <input type="password" name="password" id="password" autocomplete="current-password" required
                                    placeholder="{{ __('main.company_portal.password') }}"
                                    class="form-control form-control-solid @error('password') is-invalid @enderror" />
                                <button type="button" class="login-password-toggle" id="login_pw_toggle"
                                    aria-pressed="false"
                                    aria-label="{{ __('main.company_portal.show_password') }}"
                                    title="{{ __('main.company_portal.show_password') }}">
                                    <svg id="login_pw_toggle_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="login-auth-actions">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="remember" id="login_remember" value="1"
                                    @checked(old('remember')) />
                                <label class="form-check-label" for="login_remember">{{ __('main.company_portal.remember_me') }}</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary login-auth-submit" data-kt-indicator="off">
                                <span class="indicator-label">{{ __('main.company_portal.sign_in') }}</span>
                                <span class="indicator-progress">{{ __('main.company_portal.please_wait') }}
                                    <span class="spinner-border spinner-border-sm align-middle ms-2" aria-hidden="true"></span>
                                </span>
                            </button>
                        </div>

                        <div class="login-auth-register">
                            {{ __('main.company_portal.no_account') }}
                            <a href="{{ route('filament.admin.auth.register') }}">{{ __('main.company_portal.create_account') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var loader = document.getElementById('initial-loader');
            if (loader) {
                loader.classList.add('login-loader--hide');
                setTimeout(function() { loader.style.display = 'none'; }, 300);
            }

            var pw = document.getElementById('password');
            var btn = document.getElementById('login_pw_toggle');
            if (pw && btn) {
                btn.addEventListener('click', function() {
                    var show = pw.getAttribute('type') === 'password';
                    pw.setAttribute('type', show ? 'text' : 'password');
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                });
            }

            var form = document.getElementById('kt_sign_in_form');
            var submit = document.getElementById('kt_sign_in_submit');
            if (form && submit) {
                form.addEventListener('submit', function() {
                    submit.setAttribute('data-kt-indicator', 'on');
                    submit.setAttribute('disabled', 'disabled');
                });
            }
        });
    </script>
</body>

</html>
