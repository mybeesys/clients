<div class="ms-page">
    <style>
        .ms-page {
            --ms-ink: #1a1a1a;
            --ms-muted: #6b7280;
            --ms-line: rgba(26, 26, 26, 0.09);
            --ms-surface: rgba(255, 253, 247, 0.92);
            --ms-soft: #faf8f2;
            --ms-accent: #ebb81e;
            --ms-accent-2: #b88912;
            --ms-accent-soft: rgba(235, 184, 30, 0.18);
            --ms-shadow: 0 14px 36px rgba(26, 26, 26, 0.07);
            font-family: "Cairo", system-ui, sans-serif;
            color: var(--ms-ink);
            min-height: 100vh;
            position: relative;
            overflow-x: clip;
        }

        .ms-page::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: min(42vw, 320px);
            height: min(42vw, 320px);
            background:
                radial-gradient(circle at 30% 30%, rgba(235, 184, 30, 0.35), transparent 55%),
                radial-gradient(circle at 70% 60%, rgba(184, 137, 18, 0.18), transparent 50%);
            filter: blur(2px);
            pointer-events: none;
            z-index: 0;
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
            opacity: 0.55;
            animation: ms-hex-drift 12s ease-in-out infinite alternate;
        }

        .ms-page::after {
            content: "";
            position: absolute;
            top: 8%;
            inset-inline-end: -4%;
            width: min(28vw, 200px);
            height: min(28vw, 200px);
            background: rgba(235, 184, 30, 0.12);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
            pointer-events: none;
            z-index: 0;
            animation: ms-hex-drift 9s ease-in-out infinite alternate-reverse;
        }

        @keyframes ms-hex-drift {
            from { transform: translateY(0) rotate(0deg); }
            to { transform: translateY(18px) rotate(4deg); }
        }

        .ms-shell {
            position: relative;
            z-index: 1;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0.85rem 1rem 2.25rem;
        }

        .ms-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
            padding: 0.55rem 0.75rem;
            border: 1px solid var(--ms-line);
            border-radius: 1rem;
            background: color-mix(in srgb, var(--ms-surface) 88%, white);
            backdrop-filter: blur(10px);
            box-shadow: var(--ms-shadow);
        }

        .ms-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .ms-brand-mark {
            width: 2.15rem;
            height: 2.15rem;
            display: grid;
            place-items: center;
            background: var(--ms-accent-soft);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
        }

        .ms-brand img {
            width: 1.35rem;
            height: 1.35rem;
            object-fit: contain;
        }

        .ms-topbar-actions {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .ms-topbar a,
        .ms-topbar button.linkish {
            border: 1px solid var(--ms-line);
            background: var(--ms-soft);
            color: var(--ms-ink);
            border-radius: 0.75rem;
            padding: 0.45rem 0.85rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .ms-topbar a:hover {
            border-color: var(--ms-accent);
            background: var(--ms-accent-soft);
            transform: translateY(-1px);
        }

        .ms-hero {
            display: grid;
            gap: 0.35rem;
            margin-bottom: 0.9rem;
            padding: 0.15rem 0.2rem 0;
        }

        .ms-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--ms-accent-2);
            letter-spacing: 0.04em;
        }

        .ms-hero-kicker::before {
            content: "";
            width: 0.7rem;
            height: 0.8rem;
            background: var(--ms-accent);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
        }

        .ms-hero h1 {
            margin: 0;
            font-size: clamp(1.35rem, 2.4vw, 1.85rem);
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.25;
        }

        .ms-hero p {
            margin: 0;
            color: var(--ms-muted);
            font-size: 0.9rem;
            line-height: 1.55;
            max-width: 40rem;
        }

        .ms-grid {
            display: grid;
            gap: 0.85rem;
        }

        @media (min-width: 800px) {
            .ms-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .ms-card {
            position: relative;
            background: var(--ms-surface);
            border: 1.5px solid var(--ms-line);
            border-radius: 1.1rem;
            padding: 1.1rem 1.15rem 1.15rem;
            box-shadow: var(--ms-shadow);
            text-align: start;
            overflow: hidden;
            backdrop-filter: blur(8px);
        }

        .ms-card::before {
            content: "";
            position: absolute;
            top: -18px;
            inset-inline-end: -12px;
            width: 78px;
            height: 78px;
            background: var(--ms-accent-soft);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
            transition: transform 0.35s ease, background 0.35s ease;
        }

        .ms-card h2 {
            position: relative;
            margin: 0;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .ms-card p {
            position: relative;
            margin: 0.4rem 0 0;
            color: var(--ms-muted);
            font-size: 0.86rem;
            line-height: 1.55;
        }

        .ms-card.choice {
            cursor: pointer;
            transition: border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
            width: 100%;
            font-family: inherit;
        }

        .ms-card.choice:hover {
            transform: translateY(-3px);
            border-color: var(--ms-accent);
            box-shadow: 0 18px 40px rgba(184, 137, 18, 0.16);
        }

        .ms-card.choice:hover::before {
            transform: scale(1.12) rotate(8deg);
            background: rgba(235, 184, 30, 0.32);
        }

        .ms-card.choice:active {
            transform: translateY(-1px) scale(0.995);
        }

        .ms-card.choice:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .ms-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 0.65rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ms-accent-2);
            background: var(--ms-accent-soft);
            padding: 0.28rem 0.55rem;
            border-radius: 0.55rem;
        }

        .ms-badge::before {
            content: "";
            width: 0.45rem;
            height: 0.5rem;
            background: var(--ms-accent);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
        }

        .ms-summary {
            margin-top: 0.9rem;
            background: var(--ms-surface);
            border: 1px solid var(--ms-line);
            border-radius: 1.1rem;
            padding: 1rem 1.15rem;
            box-shadow: var(--ms-shadow);
            backdrop-filter: blur(8px);
        }

        .ms-summary h3 {
            margin: 0 0 0.7rem;
            font-size: 0.95rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .ms-summary h3::before {
            content: "";
            width: 0.7rem;
            height: 0.8rem;
            background: var(--ms-accent);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
        }

        .ms-kv {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.42rem 0;
            border-top: 1px solid var(--ms-line);
            font-size: 0.88rem;
        }

        .ms-kv:first-of-type { border-top: 0; }
        .ms-kv span { color: var(--ms-muted); }
        .ms-kv strong { font-weight: 800; }

        .ms-modules {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.7rem;
        }

        .ms-modules span {
            background: var(--ms-soft);
            border: 1px solid var(--ms-line);
            border-radius: 0.55rem;
            padding: 0.3rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 700;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .ms-modules span:hover {
            border-color: var(--ms-accent);
            background: var(--ms-accent-soft);
        }

        .ms-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.95rem;
        }

        .ms-btn {
            border: 0;
            border-radius: 0.8rem;
            padding: 0.7rem 1.1rem;
            font-weight: 800;
            font-size: 0.88rem;
            cursor: pointer;
            font-family: inherit;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .ms-btn:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .ms-btn-primary {
            background: linear-gradient(135deg, #f0c43a, var(--ms-accent) 45%, var(--ms-accent-2));
            color: #111;
            box-shadow: 0 10px 24px rgba(184, 137, 18, 0.28);
        }

        .ms-btn-primary:hover:not(:disabled) {
            filter: brightness(1.03);
            box-shadow: 0 14px 28px rgba(184, 137, 18, 0.34);
        }

        .ms-btn-secondary {
            background: var(--ms-soft);
            color: var(--ms-ink);
            border: 1px solid var(--ms-line);
        }

        .ms-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .ms-input {
            width: 100%;
            border: 1px solid var(--ms-line);
            border-radius: 0.75rem;
            background: var(--ms-soft);
            padding: 0.65rem 0.8rem;
            font-family: inherit;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .ms-input:focus {
            outline: none;
            border-color: var(--ms-accent);
            box-shadow: 0 0 0 3px var(--ms-accent-soft);
        }

        .ms-error {
            margin-top: 0.65rem;
            color: #b45309;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .ms-note {
            margin-top: 0.65rem;
            color: var(--ms-muted);
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .ms-customize {
            margin-top: 0.75rem;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .ms-page::before,
            .ms-page::after,
            .ms-card.choice,
            .ms-btn {
                animation: none;
                transition: none;
            }
        }
    </style>

    <div class="ms-shell">
        <div class="ms-topbar">
            <div class="ms-brand">
                <span class="ms-brand-mark">
                    <img src="{{ asset('images/brand/mybee-mark.png') }}" alt="My Bee" onerror="this.style.display='none'">
                </span>
                <span>My Bee</span>
            </div>
            <div class="ms-topbar-actions">
                <a href="{{ url('set-locale/'.(app()->getLocale() === 'ar' ? 'en' : 'ar')) }}">
                    {{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}
                </a>
                @if ($tenantUrl)
                    <a href="{{ $tenantUrl }}/subscription">@lang('main.subscribe.back_to_system')</a>
                @endif
            </div>
        </div>

        <div class="ms-hero">
            <span class="ms-hero-kicker">@lang('main.subscribe.hive_kicker')</span>
            <h1>@lang('main.subscribe.title')</h1>
            <p>@lang('main.subscribe.subtitle', ['company' => $company?->name ?? ''])</p>
        </div>

        @if ($mode === 'choose')
            <div class="ms-grid">
                <button type="button" class="ms-card choice" wire:click="showRenew" @disabled(! $hasCustomPackage)>
                    <span class="ms-badge">@lang('main.subscribe.renew_badge')</span>
                    <h2>@lang('main.subscribe.renew_title')</h2>
                    <p>@lang('main.subscribe.renew_desc')</p>
                    @unless ($hasCustomPackage)
                        <p class="ms-note">@lang('main.subscribe.renew_requires_custom_package')</p>
                    @endunless
                </button>

                <button type="button" class="ms-card choice" wire:click="showCustomize">
                    <span class="ms-badge">@lang('main.subscribe.customize_badge')</span>
                    <h2>@lang('main.subscribe.customize_title')</h2>
                    <p>@lang('main.subscribe.customize_desc')</p>
                </button>
            </div>

            @if ($entitlement || $subscription)
                <div class="ms-summary">
                    <h3>@lang('main.subscribe.current_summary')</h3>
                    <div class="ms-kv">
                        <span>@lang('main.subscribe.package')</span>
                        <strong>{{ $hasCustomPackage ? __('main.subscribe.custom_package') : ($subscription?->plan?->name_ar ?: $subscription?->plan?->name) }}</strong>
                    </div>
                    <div class="ms-kv">
                        <span>@lang('main.subscribe.period')</span>
                        <strong>
                            {{ ($entitlement?->period === 'Year') ? __('general.annual') : __('general.monthly') }}
                        </strong>
                    </div>
                    <div class="ms-kv">
                        <span>@lang('main.subscribe.expires')</span>
                        <strong>{{ $subscription?->expired_at ? $subscription->expired_at->format('Y-m-d') : '—' }}</strong>
                    </div>
                    @if ($entitlement)
                        <div class="ms-kv">
                            <span>@lang('main.subscribe.price')</span>
                            <strong>{{ number_format((float) $entitlement->period_total, 0) }} {{ __('general.sar') }}</strong>
                        </div>
                        <div class="ms-modules">
                            @foreach ($moduleLabels as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        @endif

        @if ($mode === 'renew')
            <div class="ms-summary">
                <h3>@lang('main.subscribe.renew_title')</h3>
                <p class="ms-note" style="margin-top:0;">@lang('main.subscribe.renew_confirm_hint')</p>

                @if ($renewQuote)
                    <div class="ms-kv" style="margin-top:0.85rem;">
                        <span>@lang('main.subscribe.renewal_total')</span>
                        <strong>{{ number_format((float) $renewQuote['period_total'], 0) }} {{ __('general.sar') }}</strong>
                    </div>
                    <div class="ms-kv">
                        <span>@lang('main.subscribe.period')</span>
                        <strong>{{ $renewQuote['period'] === 'Year' ? __('general.annual') : __('general.monthly') }}</strong>
                    </div>
                @endif

                <div style="margin-top:0.85rem;">
                    <label class="ms-note" style="display:block;margin-bottom:0.3rem;font-weight:800;">
                        @lang('main.wizard.coupon_label')
                    </label>
                    <input type="text" class="ms-input" wire:model.live.debounce.400ms="renew_coupon" placeholder="{{ __('main.wizard.coupon_placeholder') }}">
                </div>

                @error('renew') <div class="ms-error">{{ $message }}</div> @enderror
                @error('renew_coupon') <div class="ms-error">{{ $message }}</div> @enderror

                <div class="ms-actions">
                    <button type="button" class="ms-btn ms-btn-secondary" wire:click="showChoose" @disabled($saving)>
                        @lang('main.subscribe.back')
                    </button>
                    <button type="button" class="ms-btn ms-btn-primary" wire:click="renewCurrent" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="renewCurrent">@lang('main.subscribe.confirm_renew')</span>
                        <span wire:loading wire:target="renewCurrent">@lang('main.wizard.saving')</span>
                    </button>
                </div>
            </div>
        @endif

        <div style="{{ $mode === 'customize' ? '' : 'display:none' }}">
            <div class="ms-actions" style="margin-top:0.35rem;margin-bottom:0.35rem;">
                <button type="button" class="ms-btn ms-btn-secondary" wire:click="showChoose" @disabled($saving)>
                    @lang('main.subscribe.back')
                </button>
            </div>

            <div class="ms-customize" wire:ignore>
                @include('filament.forms.plan-configurator', [
                    'catalog' => $catalog,
                    'required' => true,
                    'wirePath' => 'plan_config',
                    'initial' => $plan_config,
                ])
            </div>

            @error('plan_config') <div class="ms-error">{{ $message }}</div> @enderror
            @error('plan_config.modules') <div class="ms-error">{{ $message }}</div> @enderror
            @error('plan_config.coupon_code') <div class="ms-error">{{ $message }}</div> @enderror

            <div class="ms-actions">
                <button type="button" class="ms-btn ms-btn-primary" wire:click="saveNewPackage" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveNewPackage">@lang('main.subscribe.confirm_new_package')</span>
                    <span wire:loading wire:target="saveNewPackage">@lang('main.wizard.saving')</span>
                </button>
            </div>
        </div>
    </div>
</div>
