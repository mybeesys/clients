<div class="ms-page">
    <style>
        .ms-page {
            --ms-ink: #1a1a1a;
            --ms-muted: #6b7280;
            --ms-line: rgba(26, 26, 26, 0.08);
            --ms-surface: #ffffff;
            --ms-soft: #faf8f2;
            --ms-accent: #ebb81e;
            --ms-accent-2: #b88912;
            --ms-accent-soft: rgba(235, 184, 30, 0.16);
            font-family: "Cairo", system-ui, sans-serif;
            color: var(--ms-ink);
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 8%, rgba(235, 184, 30, 0.18), transparent 42%),
                radial-gradient(circle at 88% 0%, rgba(184, 137, 18, 0.12), transparent 36%),
                linear-gradient(180deg, #fffdf7 0%, #f7f4eb 100%);
        }

        .ms-shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 1.5rem 1.15rem 3rem;
        }

        .ms-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
        }

        .ms-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.15rem;
        }

        .ms-brand img {
            width: 2.4rem;
            height: 2.4rem;
            object-fit: contain;
        }

        .ms-topbar a, .ms-topbar button.linkish {
            border: 1px solid var(--ms-line);
            background: var(--ms-surface);
            color: var(--ms-ink);
            border-radius: 999px;
            padding: 0.55rem 1rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
        }

        .ms-hero h1 {
            margin: 0;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .ms-hero p {
            margin: 0.55rem 0 0;
            color: var(--ms-muted);
            font-size: 1rem;
            line-height: 1.65;
            max-width: 42rem;
        }

        .ms-grid {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 800px) {
            .ms-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .ms-card {
            background: var(--ms-surface);
            border: 1px solid var(--ms-line);
            border-radius: 1.15rem;
            padding: 1.25rem 1.3rem;
            box-shadow: 0 18px 48px rgba(26, 26, 26, 0.06);
            text-align: start;
        }

        .ms-card h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .ms-card p {
            margin: 0.45rem 0 0;
            color: var(--ms-muted);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .ms-card.choice {
            cursor: pointer;
            transition: border-color 0.18s ease, transform 0.18s ease;
            width: 100%;
            font-family: inherit;
        }

        .ms-card.choice:hover {
            transform: translateY(-2px);
            border-color: var(--ms-accent);
        }

        .ms-card.choice:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        .ms-badge {
            display: inline-flex;
            margin-bottom: 0.75rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ms-accent-2);
            background: var(--ms-accent-soft);
            padding: 0.3rem 0.55rem;
            border-radius: 999px;
        }

        .ms-summary {
            margin-top: 1.25rem;
            background: var(--ms-surface);
            border: 1px solid var(--ms-line);
            border-radius: 1.15rem;
            padding: 1.25rem 1.3rem;
        }

        .ms-summary h3 {
            margin: 0 0 0.85rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .ms-kv {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.45rem 0;
            border-top: 1px solid var(--ms-line);
            font-size: 0.9rem;
        }

        .ms-kv:first-of-type { border-top: 0; }

        .ms-kv span { color: var(--ms-muted); }
        .ms-kv strong { font-weight: 800; }

        .ms-modules {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.75rem;
        }

        .ms-modules span {
            background: var(--ms-soft);
            border: 1px solid var(--ms-line);
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .ms-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1.25rem;
        }

        .ms-btn {
            border: 0;
            border-radius: 0.85rem;
            padding: 0.75rem 1.15rem;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            font-family: inherit;
        }

        .ms-btn-primary {
            background: var(--ms-accent);
            color: #111;
        }

        .ms-btn-secondary {
            background: var(--ms-soft);
            color: var(--ms-ink);
            border: 1px solid var(--ms-line);
        }

        .ms-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .ms-input {
            width: 100%;
            border: 1px solid var(--ms-line);
            border-radius: 0.75rem;
            background: var(--ms-soft);
            padding: 0.7rem 0.85rem;
            font-family: inherit;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .ms-error {
            margin-top: 0.75rem;
            color: #b45309;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .ms-note {
            margin-top: 0.75rem;
            color: var(--ms-muted);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .ms-customize {
            margin-top: 1.25rem;
            background: var(--ms-surface);
            border: 1px solid var(--ms-line);
            border-radius: 1.15rem;
            padding: 1rem;
        }
    </style>

    <div class="ms-shell">
        <div class="ms-topbar">
            <div class="ms-brand">
                <img src="{{ asset('images/brand/mybee-mark.png') }}" alt="My Bee" onerror="this.style.display='none'">
                <span>My Bee</span>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <a href="{{ url('set-locale/'.(app()->getLocale() === 'ar' ? 'en' : 'ar')) }}">
                    {{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}
                </a>
                @if ($tenantUrl)
                    <a href="{{ $tenantUrl }}/subscription">@lang('main.subscribe.back_to_system')</a>
                @endif
            </div>
        </div>

        <div class="ms-hero">
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
                    <div class="ms-kv" style="margin-top:1rem;">
                        <span>@lang('main.subscribe.renewal_total')</span>
                        <strong>{{ number_format((float) $renewQuote['period_total'], 0) }} {{ __('general.sar') }}</strong>
                    </div>
                    <div class="ms-kv">
                        <span>@lang('main.subscribe.period')</span>
                        <strong>{{ $renewQuote['period'] === 'Year' ? __('general.annual') : __('general.monthly') }}</strong>
                    </div>
                @endif

                <div style="margin-top:1rem;">
                    <label class="ms-note" style="display:block;margin-bottom:0.35rem;font-weight:800;">
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
            <div class="ms-actions" style="margin-top:1.25rem;">
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
