<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('referrals.landing_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #ebb81e;
            --accent-soft: #f7e7a8;
            --accent-deep: #b88912;
            --ink: #1a1a1a;
            --muted: #6b7280;
            --card: #ffffff;
            --line: rgba(26, 26, 26, 0.08);
            --page: #faf8f2;
            --shadow: 0 18px 48px rgba(26, 26, 26, 0.08);
            /* سداسي منتظم — رأس مسطّح (خلية نحل كلاسيكية) */
            --hex: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Cairo", sans-serif;
            color: var(--ink);
            background: var(--page);
            overflow-x: hidden;
        }

        .page {
            position: relative;
            min-height: 100vh;
        }

        .honeycomb-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.5;
            background-color: var(--page);
            /* تبليط خلايا سداسية منتظمة (flat-top) */
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='84' viewBox='0 0 96 84'%3E%3Cpath fill='none' stroke='%23ebb81e' stroke-opacity='0.16' stroke-width='1.25' d='M24 2 L72 2 L96 42 L72 82 L24 82 L0 42 Z'/%3E%3Cpath fill='none' stroke='%23ebb81e' stroke-opacity='0.1' stroke-width='1' d='M72 2 L96 42 L72 82 M24 2 L0 42 L24 82'/%3E%3C/svg%3E");
            background-size: 96px 84px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 20%, #000 20%, transparent 75%);
        }

        .honeycomb-glow {
            position: fixed;
            inset: auto;
            top: -12%;
            left: 50%;
            width: min(920px, 120vw);
            height: 520px;
            transform: translateX(-50%);
            z-index: 0;
            pointer-events: none;
            background: radial-gradient(ellipse at center, rgba(235, 184, 30, 0.28), transparent 68%);
        }

        .container {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.15rem 0 0.5rem;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: inherit;
        }

        .brand-mark {
            height: 64px;
            width: auto;
            max-width: min(168px, 42vw);
            object-fit: contain;
            border-radius: 14px;
            filter: drop-shadow(0 8px 18px rgba(235, 184, 30, 0.35));
            animation: markFloat 4.5s ease-in-out infinite;
        }

        .top-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 14px;
            padding: 0.85rem 1.3rem;
            font-weight: 700;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn:hover { transform: translateY(-2px); }

        .btn-primary {
            background: var(--accent);
            color: #111;
            box-shadow: 0 10px 28px rgba(235, 184, 30, 0.38);
        }

        .btn-primary:hover {
            box-shadow: 0 14px 34px rgba(235, 184, 30, 0.48);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.88);
            color: var(--ink);
            border: 1px solid var(--line);
            backdrop-filter: blur(8px);
        }

        .hero {
            margin: 0.75rem 0 2.75rem;
            position: relative;
            padding: 2.75rem 2rem 2.5rem;
            border-radius: 28px;
            background:
                linear-gradient(155deg, rgba(255, 255, 255, 0.96) 0%, rgba(255, 250, 235, 0.92) 48%, rgba(255, 255, 255, 0.96) 100%);
            border: 1px solid rgba(235, 184, 30, 0.22);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: heroIn 0.9s ease both;
        }

        .hero-hex {
            position: absolute;
            pointer-events: none;
            opacity: 0.55;
        }

        .hero-hex--a {
            width: 240px;
            height: 208px;
            top: -60px;
            inset-inline-end: -48px;
            animation: hexDrift 9s ease-in-out infinite;
        }

        .hero-hex--b {
            width: 150px;
            height: 130px;
            bottom: -36px;
            inset-inline-start: -28px;
            opacity: 0.35;
            animation: hexDrift 11s ease-in-out infinite reverse;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 2rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1.35rem;
        }

        .hero-logo-frame {
            /* نسبة السداسي المنتظم: العرض أكبر من الارتفاع قليلاً */
            width: clamp(180px, 30vw, 248px);
            aspect-ratio: 1 / 0.866;
            height: auto;
            display: grid;
            place-items: center;
            position: relative;
            clip-path: var(--hex);
            background: #000;
            box-shadow: 0 22px 50px rgba(235, 184, 30, 0.35);
            animation: markIn 1.1s cubic-bezier(0.34, 1.15, 0.48, 1) both;
        }

        .hero-logo-frame::before {
            content: '';
            position: absolute;
            inset: 3px;
            clip-path: var(--hex);
            background: #000;
        }

        .hero-logo {
            position: relative;
            z-index: 1;
            width: 78%;
            height: auto;
            max-height: 78%;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(235, 184, 30, 0.25));
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(235, 184, 30, 0.14);
            color: var(--accent-deep);
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border: 1px solid rgba(235, 184, 30, 0.28);
        }

        h1 {
            margin: 0 0 1rem;
            font-size: clamp(1.9rem, 4vw, 2.85rem);
            line-height: 1.22;
            letter-spacing: -0.02em;
        }

        .lead {
            color: var(--muted);
            font-size: 1.08rem;
            line-height: 1.85;
            margin: 0 0 1.5rem;
            max-width: 36rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .hero-card {
            background: #fff;
            border: 1px solid rgba(235, 184, 30, 0.18);
            border-radius: 22px;
            padding: 1.35rem;
            box-shadow: 0 14px 36px rgba(26, 26, 26, 0.06);
            position: relative;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(145deg, rgba(235, 184, 30, 0.45), transparent 40%, rgba(235, 184, 30, 0.2));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .referrer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .avatar {
            width: 48px;
            aspect-ratio: 1 / 0.866;
            height: auto;
            clip-path: var(--hex);
            background: linear-gradient(145deg, var(--accent), #f0d06a);
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #111;
            flex-shrink: 0;
        }

        .section-head {
            text-align: center;
            margin: 0 auto 2rem;
            max-width: 640px;
        }

        .section-title {
            margin: 0 0 0.5rem;
            font-size: clamp(1.55rem, 3vw, 2rem);
            letter-spacing: -0.02em;
        }

        .section-sub {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
        }

        .hex-divider {
            display: flex;
            justify-content: center;
            gap: 0.55rem;
            margin: 0 0 1.25rem;
        }

        .hex-divider span {
            width: 12px;
            aspect-ratio: 1 / 0.866;
            height: auto;
            background: var(--accent);
            clip-path: var(--hex);
            opacity: 0.85;
        }

        .hex-divider span:nth-child(2) { opacity: 0.45; transform: translateY(3px); }
        .hex-divider span:nth-child(3) { opacity: 0.7; }

        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 3.25rem;
        }

        .module-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.3rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .module-card:hover {
            transform: translateY(-3px);
            border-color: rgba(235, 184, 30, 0.4);
            box-shadow: 0 14px 30px rgba(235, 184, 30, 0.12);
        }

        .module-card h3 { margin: 0.75rem 0 0.35rem; font-size: 1.05rem; }
        .module-card p { margin: 0; color: var(--muted); font-size: 0.92rem; line-height: 1.7; }

        .icon {
            width: 46px;
            aspect-ratio: 1 / 0.866;
            height: auto;
            clip-path: var(--hex);
            background: rgba(235, 184, 30, 0.16);
            display: grid;
            place-items: center;
            font-size: 1.15rem;
        }

        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .plan-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .plan-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(26, 26, 26, 0.07);
        }

        .plan-card.featured {
            border-color: rgba(235, 184, 30, 0.7);
            box-shadow: 0 16px 40px rgba(235, 184, 30, 0.18);
            background: linear-gradient(180deg, #fffdf5 0%, #ffffff 55%);
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ink);
        }

        .price small {
            font-size: 0.95rem;
            color: var(--muted);
            font-weight: 600;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .features li { padding: 0.35rem 0; }

        .cta-band {
            position: relative;
            overflow: hidden;
            background: #111;
            color: #fff;
            border-radius: 24px;
            padding: 2.4rem 2rem;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .cta-band::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.35;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='84' viewBox='0 0 96 84'%3E%3Cpath fill='none' stroke='%23ebb81e' stroke-opacity='0.35' stroke-width='1.1' d='M24 2 L72 2 L96 42 L72 82 L24 82 L0 42 Z'/%3E%3C/svg%3E");
            background-size: 96px 84px;
            pointer-events: none;
        }

        .cta-band > * { position: relative; z-index: 1; }
        .cta-band h2 { margin: 0 0 0.75rem; }
        .cta-band p { color: #d1d5db; margin: 0 0 1.25rem; }

        .site-footer {
            text-align: center;
            padding: 1.5rem 0 3rem;
        }

        .footer-logo {
            width: clamp(72px, 14vw, 96px);
            height: auto;
            margin: 0 auto 0.85rem;
            display: block;
            filter: drop-shadow(0 10px 22px rgba(235, 184, 30, 0.28));
            animation: markFloat 5s ease-in-out infinite;
        }

        .footer-copy {
            color: var(--muted);
            font-size: 0.9rem;
            margin: 0;
        }

        @keyframes markIn {
            from {
                opacity: 0;
                transform: scale(0.72) rotate(-8deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes markFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes heroIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes hexDrift {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(10px) rotate(4deg); }
        }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; }
            .hero { padding: 2rem 1.25rem; }
            .hero-logo-wrap { margin-bottom: 1rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .brand-mark,
            .hero-logo-frame,
            .footer-logo,
            .hero,
            .hero-hex--a,
            .hero-hex--b {
                animation: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="honeycomb-bg" aria-hidden="true"></div>
        <div class="honeycomb-glow" aria-hidden="true"></div>

        <div class="container">
            <header class="topbar">
                <a class="brand" href="{{ request()->url() }}">
                    <img
                        class="brand-mark"
                        src="{{ asset('images/brand/mybee-lockup.png') }}"
                        alt="My Bee"
                    >
                </a>
                <div class="top-actions">
                    <a class="btn btn-outline" href="{{ request()->url() }}?set_lang={{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                        {{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}
                    </a>
                    <a class="btn btn-primary" href="{{ $registerUrl }}">{{ __('referrals.cta_register') }}</a>
                </div>
            </header>

            <section class="hero">
                <svg class="hero-hex hero-hex--a" viewBox="0 0 120 104" fill="none" aria-hidden="true">
                    <path d="M30 2 L90 2 L118 52 L90 102 L30 102 L2 52 Z" stroke="#ebb81e" stroke-width="2.2" opacity="0.55"/>
                    <path d="M40 16 L80 16 L100 52 L80 88 L40 88 L20 52 Z" stroke="#ebb81e" stroke-width="1.3" opacity="0.28"/>
                </svg>
                <svg class="hero-hex hero-hex--b" viewBox="0 0 120 104" fill="none" aria-hidden="true">
                    <path d="M30 2 L90 2 L118 52 L90 102 L30 102 L2 52 Z" stroke="#ebb81e" stroke-width="2.2" opacity="0.4"/>
                </svg>

                <div class="hero-grid">
                    <div>
                        <div class="hero-logo-wrap">
                            <div class="hero-logo-frame">
                                <img
                                    class="hero-logo"
                                    src="{{ asset('images/brand/mybee-lockup.png') }}"
                                    alt="My Bee"
                                >
                            </div>
                        </div>
                        <span class="eyebrow">{{ __('referrals.invited_by', ['name' => $referralCode->employee_name ?: __('referrals.default_referrer_name')]) }}</span>
                        <h1>{{ __('referrals.hero_title') }}</h1>
                        <p class="lead">{{ __('referrals.hero_subtitle') }}</p>
                        <div class="hero-actions">
                            <a class="btn btn-primary" href="{{ $registerUrl }}">{{ __('referrals.cta_start') }}</a>
                            <a class="btn btn-outline" href="#plans">{{ __('referrals.cta_view_plans') }}</a>
                        </div>
                    </div>
                    <div class="hero-card">
                        <div class="referrer">
                            <div class="avatar">{{ mb_substr($referralCode->employee_name ?: 'M', 0, 1) }}</div>
                            <div>
                                <div style="font-weight:700;">{{ $referralCode->employee_name ?: __('referrals.default_referrer_name') }}</div>
                                <div style="color:var(--muted); font-size:.9rem;">{{ __('referrals.personal_invite') }}</div>
                            </div>
                        </div>
                        <p style="white-space:pre-line; color:var(--muted); line-height:1.8; margin:0 0 1rem;">{{ $promotionalText }}</p>
                        <a class="btn btn-primary" style="width:100%;" href="{{ $registerUrl }}">{{ __('referrals.cta_register') }}</a>
                    </div>
                </div>
            </section>

            <div class="section-head">
                <div class="hex-divider" aria-hidden="true"><span></span><span></span><span></span></div>
                <h2 class="section-title">{{ __('referrals.modules_title') }}</h2>
                <p class="section-sub">{{ __('referrals.modules_subtitle') }}</p>
            </div>

            <div class="modules">
                @foreach ([
                    ['icon' => '🛒', 'title' => __('referrals.modules.pos'), 'desc' => __('referrals.modules.pos_desc')],
                    ['icon' => '📦', 'title' => __('referrals.modules.inventory'), 'desc' => __('referrals.modules.inventory_desc')],
                    ['icon' => '📊', 'title' => __('referrals.modules.accounting'), 'desc' => __('referrals.modules.accounting_desc')],
                    ['icon' => '👥', 'title' => __('referrals.modules.hr'), 'desc' => __('referrals.modules.hr_desc')],
                    ['icon' => '📈', 'title' => __('referrals.modules.reports'), 'desc' => __('referrals.modules.reports_desc')],
                    ['icon' => '📱', 'title' => __('referrals.modules.apps'), 'desc' => __('referrals.modules.apps_desc')],
                ] as $module)
                    <article class="module-card">
                        <div class="icon">{{ $module['icon'] }}</div>
                        <h3>{{ $module['title'] }}</h3>
                        <p>{{ $module['desc'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="section-head" id="plans">
                <div class="hex-divider" aria-hidden="true"><span></span><span></span><span></span></div>
                <h2 class="section-title">{{ __('referrals.plans_title') }}</h2>
                <p class="section-sub">{{ __('referrals.plans_subtitle') }}</p>
            </div>

            <div class="plans">
                @php
                    $localeIsAr = app()->getLocale() === 'ar';
                    $planName = fn ($plan) => $localeIsAr ? ($plan->name_ar ?: $plan->name) : ($plan->name ?: $plan->name_ar);
                    $planDescription = fn ($plan) => $localeIsAr ? ($plan->description_ar ?: $plan->description) : ($plan->description ?: $plan->description_ar);
                @endphp
                @forelse ($plans as $index => $plan)
                    <article @class(['plan-card', 'featured' => $index === 1])>
                        <div>
                            <h3 style="margin:0 0 .35rem;">{{ $planName($plan) }}</h3>
                            <p style="margin:0; color:var(--muted); font-size:.92rem;">{{ $planDescription($plan) }}</p>
                        </div>
                        <div class="price">
                            {{ number_format((float) ($plan->price_after_discount ?: $plan->price), 0) }}
                            <small>{{ __('referrals.currency') }} / {{ $plan->periodicity_type === 'Year' ? __('referrals.per_year') : __('referrals.per_month') }}</small>
                        </div>
                        <ul class="features">
                            <li>✓ {{ __('referrals.plan_feature_cloud') }}</li>
                            <li>✓ {{ __('referrals.plan_feature_support') }}</li>
                            <li>✓ {{ __('referrals.plan_feature_updates') }}</li>
                        </ul>
                        <a class="btn btn-primary" href="{{ $registerUrl }}&plan={{ $plan->id }}">{{ __('referrals.choose_plan') }}</a>
                    </article>
                @empty
                    <p class="section-sub">{{ __('referrals.no_plans') }}</p>
                @endforelse
            </div>

            <section class="cta-band">
                <h2>{{ __('referrals.final_cta_title') }}</h2>
                <p>{{ __('referrals.final_cta_subtitle') }}</p>
                <a class="btn btn-primary" href="{{ $registerUrl }}">{{ __('referrals.cta_register_now') }}</a>
            </section>

            <footer class="site-footer">
                <img
                    class="footer-logo"
                    src="{{ asset('images/brand/mybee-mark.png') }}"
                    alt="My Bee"
                    width="96"
                    height="96"
                >
                <p class="footer-copy">My Bee — {{ __('referrals.footer') }}</p>
            </footer>
        </div>
    </div>
</body>
</html>
