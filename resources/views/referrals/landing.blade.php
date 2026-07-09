<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('referrals.landing_title') }} — My Bee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #f5e902;
            --accent-dark: #ca8a04;
            --ink: #181c32;
            --muted: #7e8299;
            --card: #ffffff;
            --line: #eef1f7;
            --hero: linear-gradient(135deg, #fffef0 0%, #ffffff 45%, #f8f9fc 100%);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Cairo", sans-serif;
            color: var(--ink);
            background: #f8f9fc;
        }

        .container { width: min(1120px, calc(100% - 2rem)); margin: 0 auto; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-weight: 800;
            font-size: 1.25rem;
        }

        .brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--accent);
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: 12px;
            padding: .85rem 1.25rem;
            font-weight: 700;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: var(--accent);
            color: #111;
            box-shadow: 0 8px 24px rgba(245, 233, 2, .35);
        }

        .btn-outline {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .hero {
            background: var(--hero);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 3rem 2rem;
            margin: 1rem 0 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: -60px;
            inset-inline-end: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(245, 233, 2, .18);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 2rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-block;
            background: #fff8d6;
            color: var(--accent-dark);
            padding: .35rem .75rem;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        h1 {
            margin: 0 0 1rem;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.2;
        }

        .lead {
            color: var(--muted);
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 0 0 1.5rem;
        }

        .hero-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(24, 28, 50, .06);
        }

        .referrer {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef1f7;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .section-title {
            text-align: center;
            margin: 0 0 .5rem;
            font-size: 2rem;
        }

        .section-sub {
            text-align: center;
            color: var(--muted);
            margin: 0 auto 2rem;
            max-width: 640px;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .module-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.25rem;
        }

        .module-card h3 { margin: .75rem 0 .35rem; font-size: 1.05rem; }
        .module-card p { margin: 0; color: var(--muted); font-size: .92rem; line-height: 1.7; }

        .icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #fff8d6;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
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
            border-radius: 18px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .plan-card.featured {
            border-color: rgba(245, 233, 2, .8);
            box-shadow: 0 12px 30px rgba(245, 233, 2, .18);
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
        }

        .price small {
            font-size: .95rem;
            color: var(--muted);
            font-weight: 600;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 0;
            color: var(--muted);
            font-size: .92rem;
        }

        .features li { padding: .35rem 0; }

        .cta-band {
            background: #111;
            color: #fff;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .cta-band h2 { margin: 0 0 .75rem; }
        .cta-band p { color: #d1d5db; margin: 0 0 1.25rem; }

        footer {
            text-align: center;
            color: var(--muted);
            padding: 2rem 0 3rem;
            font-size: .9rem;
        }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="brand">
                <span class="brand-badge">MB</span>
                <span>My Bee</span>
            </div>
            <div style="display:flex; gap:.5rem;">
                <a class="btn btn-outline" href="{{ request()->url() }}?set_lang={{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                    {{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}
                </a>
                <a class="btn btn-primary" href="{{ $registerUrl }}">{{ __('referrals.cta_register') }}</a>
            </div>
        </div>

        <section class="hero">
            <div class="hero-grid">
                <div>
                    <span class="eyebrow">{{ __('referrals.invited_by', ['name' => $referralCode->employee_name ?: __('referrals.default_referrer_name')]) }}</span>
                    <h1>{{ __('referrals.hero_title') }}</h1>
                    <p class="lead">{{ __('referrals.hero_subtitle') }}</p>
                    <div style="display:flex; flex-wrap:wrap; gap:.75rem;">
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

        <h2 class="section-title">{{ __('referrals.modules_title') }}</h2>
        <p class="section-sub">{{ __('referrals.modules_subtitle') }}</p>

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

        <h2 class="section-title" id="plans">{{ __('referrals.plans_title') }}</h2>
        <p class="section-sub">{{ __('referrals.plans_subtitle') }}</p>

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

        <footer>My Bee — {{ __('referrals.footer') }}</footer>
    </div>
</body>
</html>
