@php
    use Illuminate\Support\Number;

    $plans = ($plans ?? collect())->values();
    $localeIsAr = app()->getLocale() === 'ar';
    $planRequired = $planRequired ?? true;

    $planName = fn ($plan) => $localeIsAr ? ($plan->name_ar ?: $plan->name) : ($plan->name ?: $plan->name_ar);
    $planDescription = fn ($plan) => $localeIsAr
        ? ($plan->description_ar ?: $plan->description)
        : ($plan->description ?: $plan->description_ar);

    $monthlyPlans = $plans->where('periodicity_type', 'Month')->values();
    $yearlyPlans = $plans->where('periodicity_type', 'Year')->values();
    $defaultPeriod = $monthlyPlans->isNotEmpty() ? 'Month' : ($yearlyPlans->isNotEmpty() ? 'Year' : 'Month');
@endphp

<div
    class="subscription-wizard"
    x-data="{
        selected: $wire.$entangle('data.subscription.plan_id'),
        period: @js($defaultPeriod),
        plans: @js($plans->map(fn ($p) => [
            'id' => $p->id,
            'periodicity_type' => $p->periodicity_type,
            'name' => $planName($p),
            'description' => $planDescription($p),
            'price' => (float) $p->price,
            'price_after_discount' => filled($p->price_after_discount) ? (float) $p->price_after_discount : null,
            'grace_label' => (int) $p->grace_days > 0
                ? __('main.wizard.plan_grace_days', ['days' => $p->grace_days])
                : null,
            'features_count' => (int) ($p->feature_plans_count ?? 0),
        ])),
        get visiblePlans() {
            return this.plans.filter(p => p.periodicity_type === this.period);
        },
        get selectedPlan() {
            return this.plans.find(p => p.id == this.selected) ?? null;
        },
        select(id) {
            if (! @js($planRequired)) {
                this.selected = this.selected == id ? null : id;
            } else {
                this.selected = id;
            }
        },
        skip() {
            if (! @js($planRequired)) {
                this.selected = null;
            }
        },
        hasPeriod(type) {
            return this.plans.some(p => p.periodicity_type === type);
        },
    }"
>
    <style>
        .subscription-wizard {
            --sw-accent: #ca8a04;
            --sw-accent-soft: #fef9c3;
            --sw-surface: #ffffff;
            --sw-muted: #64748b;
            --sw-border: #e2e8f0;
            --sw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.07), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --sw-shadow-lg: 0 20px 40px -12px rgb(0 0 0 / 0.12);
        }

        .dark .subscription-wizard {
            --sw-surface: #111827;
            --sw-muted: #94a3b8;
            --sw-border: #334155;
            --sw-accent-soft: rgb(202 138 4 / 0.15);
        }

        .sw-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .sw-header-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--sw-accent-soft), var(--sw-surface));
            border: 1px solid var(--sw-border);
            color: var(--sw-accent);
        }

        .sw-header h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dark .sw-header h3 { color: #f8fafc; }

        .sw-header p {
            margin: 0.35rem 0 0;
            font-size: 0.875rem;
            line-height: 1.6;
            color: var(--sw-muted);
        }

        .sw-toggle {
            display: inline-flex;
            padding: 0.25rem;
            border-radius: 9999px;
            background: #f1f5f9;
            border: 1px solid var(--sw-border);
            margin-bottom: 1.5rem;
        }

        .dark .sw-toggle { background: #1e293b; }

        .sw-toggle button {
            padding: 0.5rem 1.25rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 9999px;
            border: none;
            background: transparent;
            color: var(--sw-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sw-toggle button.is-active {
            background: var(--sw-surface);
            color: #0f172a;
            box-shadow: var(--sw-shadow);
        }

        .dark .sw-toggle button.is-active { color: #f8fafc; }

        .sw-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        }

        @media (min-width: 1024px) {
            .sw-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        .sw-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding: 1.5rem;
            text-align: start;
            border-radius: 1rem;
            border: 2px solid var(--sw-border);
            background: var(--sw-surface);
            box-shadow: var(--sw-shadow);
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .sw-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: var(--sw-shadow-lg);
        }

        .sw-card.is-selected {
            border-color: var(--sw-accent);
            box-shadow: 0 0 0 4px rgb(202 138 4 / 0.15), var(--sw-shadow-lg);
        }

        .sw-card.is-skip {
            border-style: dashed;
            background: #f8fafc;
            min-height: auto;
        }

        .dark .sw-card.is-skip { background: #0f172a; }

        .sw-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.65rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-radius: 9999px;
            background: var(--sw-accent-soft);
            color: #92400e;
        }

        .sw-check {
            position: absolute;
            top: 1rem;
            inset-inline-end: 1rem;
            width: 1.75rem;
            height: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: var(--sw-accent);
            color: #fff;
        }

        .sw-price {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 0.35rem 0.5rem;
            margin-top: auto;
            padding-top: 1.25rem;
        }

        .sw-price-old {
            font-size: 0.875rem;
            color: #94a3b8;
            text-decoration: line-through;
        }

        .sw-price-value {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .dark .sw-price-value { color: #f8fafc; }

        .sw-price-unit {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--sw-muted);
        }

        .sw-features {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.75rem;
            font-size: 0.8125rem;
            color: var(--sw-muted);
        }

        .sw-summary {
            margin-top: 1.5rem;
            padding: 1rem 1.25rem;
            border-radius: 0.875rem;
            border: 1px solid var(--sw-border);
            background: linear-gradient(135deg, var(--sw-accent-soft) 0%, var(--sw-surface) 70%);
        }

        .sw-empty {
            grid-column: 1 / -1;
            padding: 3rem 1.5rem;
            text-align: center;
            border-radius: 1rem;
            border: 2px dashed var(--sw-border);
            color: var(--sw-muted);
        }
    </style>

    <div class="sw-header">
        <div class="sw-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h16.5m-16.5 2.25h16.5M3.75 6.75h16.5a1.5 1.5 0 0 0 1.5-1.5v-3A1.5 1.5 0 0 0 20.25 3.75H3.75A1.5 1.5 0 0 0 2.25 5.25v3A1.5 1.5 0 0 0 3.75 9Zm0 0h16.5" />
            </svg>
        </div>
        <div>
            <h3>{{ __('main.wizard.subscription_choose_title') }}</h3>
            <p>{{ __('main.wizard.subscription_information_hint') }}</p>
        </div>
    </div>

    <div class="sw-toggle" x-show="hasPeriod('Month') || hasPeriod('Year')">
        <button
            type="button"
            x-show="hasPeriod('Month')"
            x-on:click="period = 'Month'"
            :class="{ 'is-active': period === 'Month' }"
        >
            {{ __('general.monthly') }}
        </button>
        <button
            type="button"
            x-show="hasPeriod('Year')"
            x-on:click="period = 'Year'"
            :class="{ 'is-active': period === 'Year' }"
        >
            {{ __('general.annual') }}
        </button>
    </div>

    <div class="sw-grid">
        @unless ($planRequired)
            <button
                type="button"
                class="sw-card is-skip"
                :class="{ 'is-selected': selected === null || selected === '' }"
                x-on:click="skip()"
            >
                <span class="sw-badge">{{ __('main.wizard.subscription_skip_badge') }}</span>
                <h4 class="mt-3 text-base font-bold text-gray-900 dark:text-white">
                    {{ __('main.wizard.subscription_skip_title') }}
                </h4>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('main.wizard.subscription_plan_optional_hint') }}
                </p>
            </button>
        @endunless

        <template x-for="plan in visiblePlans" :key="plan.id">
            <button
                type="button"
                class="sw-card"
                :class="{ 'is-selected': selected == plan.id }"
                x-on:click="select(plan.id)"
            >
                <span class="sw-check" x-show="selected == plan.id" x-cloak>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                </span>

                <span class="sw-badge" x-text="period === 'Year' ? @js(__('general.annual')) : @js(__('general.monthly'))"></span>

                <h4 class="mt-3 pe-8 text-lg font-bold text-gray-900 dark:text-white" x-text="plan.name"></h4>

                <p
                    class="mt-2 flex-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400"
                    x-show="plan.description"
                    x-text="plan.description"
                ></p>

                <div class="sw-features" x-show="plan.features_count > 0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-emerald-600">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="plan.features_count + ' ' + @js(__('main.wizard.subscription_features_included'))"></span>
                </div>

                <div class="sw-price">
                    <span class="sw-price-old" x-show="plan.price_after_discount && plan.price_after_discount < plan.price" x-text="Math.round(plan.price).toLocaleString()"></span>
                    <span class="sw-price-value" x-text="Math.round(plan.price_after_discount && plan.price_after_discount < plan.price ? plan.price_after_discount : plan.price).toLocaleString()"></span>
                    <span class="sw-price-unit">{{ __('general.sar') }} / <span x-text="period === 'Year' ? @js(__('fields.year')) : @js(__('fields.month'))"></span></span>
                </div>

                <p class="mt-2 text-xs text-gray-500" x-show="plan.grace_label" x-text="plan.grace_label"></p>
            </button>
        </template>

        <div class="sw-empty" x-show="visiblePlans.length === 0">
            <p class="font-medium">{{ __('main.wizard.no_plans_available') }}</p>
            <p class="mt-1 text-sm">{{ __('main.wizard.no_plans_for_period') }}</p>
        </div>
    </div>

    <div class="sw-summary" x-show="selectedPlan" x-cloak>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-800 dark:text-amber-300">
                    {{ __('main.wizard.subscription_selected_label') }}
                </p>
                <p class="mt-1 text-base font-bold text-gray-900 dark:text-white" x-text="selectedPlan?.name"></p>
            </div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">
                <span x-text="selectedPlan ? Math.round(selectedPlan.price_after_discount && selectedPlan.price_after_discount < selectedPlan.price ? selectedPlan.price_after_discount : selectedPlan.price).toLocaleString() : ''"></span>
                <span class="text-sm font-medium text-gray-500"> {{ __('general.sar') }}</span>
            </p>
        </div>
    </div>
</div>
