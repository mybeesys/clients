@php
    $catalog = $catalog ?? [];
    $required = $required ?? true;
    $currency = $catalog['currency'] ?? 'SAR';
    $yearlyMonths = (int) ($catalog['yearly_months_charged'] ?? 12);
    $wirePath = $wirePath ?? 'data.plan_config';
    $initial = is_array($initial ?? null) ? $initial : [];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

<div
    class="plan-configurator"
    x-data="planConfigurator(@js($catalog), @js([
        'monthly' => __('general.monthly'),
        'annual' => __('general.annual'),
        'sar' => __('general.sar'),
        'included' => __('main.wizard.plan_included'),
        'per_month' => __('main.wizard.plan_per_month'),
        'invoice_title' => __('main.wizard.plan_invoice_title'),
        'due_now' => __('main.wizard.plan_due_now'),
        'invoice_brand' => 'My Bee',
        'invoice_meta' => __('main.wizard.plan_invoice_meta'),
        'invoice_desc' => __('main.wizard.plan_invoice_desc'),
        'invoice_amount' => __('main.wizard.plan_invoice_amount'),
        'invoice_subtotal' => __('main.wizard.plan_invoice_subtotal'),
        'invoice_discount' => __('main.wizard.plan_invoice_discount'),
        'invoice_period_month' => __('general.monthly'),
        'invoice_period_year' => __('general.annual'),
        'yearly_note' => __('main.wizard.plan_yearly_note', ['months' => $yearlyMonths]),
        'modules_hint' => __('main.wizard.plan_modules_hint'),
        'quotas_title' => __('main.wizard.plan_quotas_title'),
        'platform_badge' => __('main.wizard.plan_platform_badge'),
        'requires' => __('main.wizard.plan_requires'),
        'works_with' => __('main.wizard.plan_works_with'),
        'empty_modules' => __('main.wizard.plan_modules_required'),
        'select_at_least' => __('main.wizard.plan_select_at_least'),
        'reports_needs_data' => __('main.wizard.plan_reports_needs_data_module'),
        'recommend_title' => __('main.wizard.plan_recommend_title'),
        'recommend_hint' => __('main.wizard.plan_recommend_hint'),
        'recommend_apply' => __('main.wizard.plan_recommend_apply'),
        'recommend_active' => __('main.wizard.plan_recommend_active'),
        'recommend_customize' => __('main.wizard.plan_recommend_customize'),
        'recommend_modules' => __('main.wizard.plan_recommend_modules_count'),
        'coupon_label' => __('main.wizard.coupon_label'),
        'coupon_placeholder' => __('main.wizard.coupon_placeholder'),
        'coupon_apply' => __('main.wizard.coupon_apply'),
        'coupon_remove' => __('main.wizard.coupon_remove'),
    ]), @js([
        'wirePath' => $wirePath,
        'initial' => $initial,
    ]))"
    x-init="init()"
>
    <style>
        .plan-configurator {
            --pc-ink: #1a1a1a;
            --pc-muted: #6b7280;
            --pc-line: rgba(26, 26, 26, 0.08);
            --pc-surface: #ffffff;
            --pc-soft: #faf8f2;
            --pc-accent: #ebb81e;
            --pc-accent-2: #b88912;
            --pc-accent-soft: rgba(235, 184, 30, 0.16);
            --pc-warn-soft: rgba(247, 231, 168, 0.65);
            --pc-shadow: 0 18px 48px rgba(26, 26, 26, 0.08);
            font-family: "Cairo", system-ui, sans-serif;
        }

        .dark .plan-configurator {
            --pc-ink: #f8fafc;
            --pc-muted: #94a3b8;
            --pc-line: #334155;
            --pc-surface: #0f172a;
            --pc-soft: #111827;
            --pc-accent-soft: rgba(235, 184, 30, 0.18);
            --pc-warn-soft: rgba(235, 184, 30, 0.14);
        }

        .pc-layout {
            display: grid;
            gap: 1.25rem;
            align-items: start;
        }

        @media (min-width: 1100px) {
            .pc-layout {
                grid-template-columns: minmax(0, 1fr) 320px;
            }
        }

        .pc-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .pc-hero h3 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--pc-ink);
        }

        .pc-hero p {
            margin: 0.4rem 0 0;
            color: var(--pc-muted);
            font-size: 0.925rem;
            line-height: 1.65;
            max-width: 42rem;
        }

        .pc-period {
            display: inline-flex;
            padding: 0.28rem;
            border-radius: 999px;
            background: var(--pc-soft);
            border: 1px solid var(--pc-line);
        }

        .pc-period button {
            border: 0;
            background: transparent;
            color: var(--pc-muted);
            font-weight: 700;
            font-size: 0.8125rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .pc-period button.is-active {
            background: var(--pc-surface);
            color: var(--pc-ink);
            box-shadow: var(--pc-shadow);
        }

        .pc-platform {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1rem 1.15rem;
            border-radius: 1rem;
            border: 1px solid var(--pc-line);
            background:
                linear-gradient(135deg, var(--pc-accent-soft), transparent 55%),
                var(--pc-surface);
            margin-bottom: 1.25rem;
        }

        .pc-platform-badge {
            flex-shrink: 0;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--pc-accent);
            background: var(--pc-accent-soft);
            padding: 0.3rem 0.55rem;
            border-radius: 999px;
        }

        .pc-platform h4 {
            margin: 0;
            color: var(--pc-ink);
            font-size: 1rem;
            font-weight: 800;
        }

        .pc-platform p {
            margin: 0.35rem 0 0;
            color: var(--pc-muted);
            font-size: 0.85rem;
            line-height: 1.55;
        }

        .pc-group {
            margin-bottom: 1.35rem;
        }

        .pc-group-title {
            margin: 0 0 0.75rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--pc-muted);
        }

        .pc-modules {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 720px) {
            .pc-modules {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .pc-module {
            position: relative;
            text-align: start;
            border: 1.5px solid var(--pc-line);
            background: var(--pc-surface);
            border-radius: 1rem;
            padding: 1rem 1.05rem;
            cursor: pointer;
            transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
            width: 100%;
        }

        .pc-module:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: var(--pc-shadow);
        }

        .pc-module.is-on {
            border-color: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft), var(--pc-shadow);
            background:
                linear-gradient(180deg, var(--pc-accent-soft), transparent 42%),
                var(--pc-surface);
        }

        .pc-module-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .pc-module h5 {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-module p {
            margin: 0.4rem 0 0;
            color: var(--pc-muted);
            font-size: 0.8rem;
            line-height: 1.55;
            min-height: 2.5rem;
        }

        .pc-price-tag {
            flex-shrink: 0;
            font-weight: 800;
            color: var(--pc-ink);
            font-size: 0.92rem;
            white-space: nowrap;
        }

        .pc-price-tag small {
            color: var(--pc-muted);
            font-weight: 600;
            font-size: 0.72rem;
        }

        .pc-check {
            position: absolute;
            top: 0.85rem;
            inset-inline-end: 0.85rem;
            width: 1.45rem;
            height: 1.45rem;
            border-radius: 999px;
            display: none;
            align-items: center;
            justify-content: center;
            background: var(--pc-accent);
            color: #111;
        }

        .pc-module.is-on .pc-check { display: flex; }
        .pc-module.is-on .pc-price-tag { padding-inline-end: 1.6rem; }

        .pc-req {
            margin-top: 0.55rem;
            font-size: 0.72rem;
            color: var(--pc-accent-2);
            font-weight: 600;
        }

        .pc-quotas {
            border: 1px solid var(--pc-line);
            border-radius: 1rem;
            background: var(--pc-surface);
            padding: 1rem 1.1rem;
        }

        .pc-quotas h4 {
            margin: 0 0 0.9rem;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-quota-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.7rem 0;
            border-top: 1px solid var(--pc-line);
        }

        .pc-quota-row:first-of-type { border-top: 0; padding-top: 0; }

        .pc-quota-meta {
            color: var(--pc-muted);
            font-size: 0.78rem;
            margin-top: 0.2rem;
        }

        .pc-stepper {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--pc-soft);
            border: 1px solid var(--pc-line);
            border-radius: 999px;
            padding: 0.2rem;
        }

        .pc-stepper button {
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: var(--pc-surface);
            color: var(--pc-ink);
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: var(--pc-shadow);
        }

        .pc-stepper span {
            min-width: 2.2rem;
            text-align: center;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-invoice {
            position: sticky;
            top: 1rem;
            border-radius: 1rem;
            border: 1px solid var(--pc-line);
            background: var(--pc-surface);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.7) inset,
                0 18px 48px rgba(26, 26, 26, 0.08);
            padding: 0;
            overflow: hidden;
        }

        .pc-invoice-head {
            padding: 1.05rem 1.15rem 0.95rem;
            background:
                linear-gradient(135deg, rgba(235, 184, 30, 0.18), transparent 58%),
                linear-gradient(180deg, #fffef8, #fff);
            border-bottom: 1px solid var(--pc-line);
        }

        .pc-invoice-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .pc-invoice-brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--pc-accent-2);
        }

        .pc-invoice-brand-mark::before {
            content: "";
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 999px;
            background: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft);
        }

        .pc-invoice-period {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--pc-ink);
            background: var(--pc-soft);
            border: 1px solid var(--pc-line);
            border-radius: 999px;
            padding: 0.28rem 0.6rem;
        }

        .pc-invoice-head h4 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--pc-ink);
            letter-spacing: -0.02em;
        }

        .pc-invoice-meta {
            margin: 0.35rem 0 0;
            font-size: 0.75rem;
            color: var(--pc-muted);
            line-height: 1.45;
        }

        .pc-invoice-body {
            padding: 0.85rem 1.15rem 1.15rem;
        }

        .pc-invoice-cols {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.75rem;
            padding: 0 0 0.45rem;
            margin-bottom: 0.35rem;
            border-bottom: 1px solid var(--pc-line);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--pc-muted);
        }

        .pc-invoice-lines {
            margin: 0;
            display: grid;
            gap: 0;
        }

        .pc-line {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.75rem;
            align-items: start;
            padding: 0.7rem 0;
            border-bottom: 1px dashed rgba(26, 26, 26, 0.08);
            font-size: 0.84rem;
            color: var(--pc-ink);
        }

        .pc-line:last-child {
            border-bottom: 0;
        }

        .pc-line-label {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .pc-line-label strong {
            font-weight: 700;
            color: var(--pc-ink);
            line-height: 1.35;
        }

        .pc-line-label small {
            color: var(--pc-muted);
            font-size: 0.72rem;
            font-weight: 600;
        }

        .pc-line-amount {
            font-weight: 800;
            color: var(--pc-ink);
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }

        .pc-line.is-discount .pc-line-label strong,
        .pc-line.is-discount .pc-line-amount {
            color: #15803d;
        }

        .pc-invoice-totals {
            margin-top: 0.35rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--pc-line);
            display: grid;
            gap: 0.45rem;
        }

        .pc-total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
            font-size: 0.82rem;
            color: var(--pc-muted);
            font-weight: 700;
        }

        .pc-total-row strong {
            color: var(--pc-ink);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        .pc-total-row.is-discount strong {
            color: #15803d;
        }

        .pc-invoice-total {
            margin-top: 0.35rem;
            padding: 0.85rem 0.95rem;
            border-radius: 0.85rem;
            background:
                linear-gradient(135deg, rgba(235, 184, 30, 0.22), rgba(235, 184, 30, 0.08)),
                var(--pc-soft);
            border: 1px solid rgba(184, 137, 18, 0.22);
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
        }

        .pc-invoice-total span {
            color: var(--pc-ink);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .pc-invoice-total strong {
            font-size: 1.55rem;
            font-weight: 900;
            color: var(--pc-ink);
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            display: inline-flex;
            align-items: baseline;
            gap: 0.3rem;
        }

        .pc-invoice-total strong small {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--pc-muted);
        }

        .pc-invoice-note {
            margin: 0.75rem 0 0;
            font-size: 0.74rem;
            color: var(--pc-muted);
            line-height: 1.5;
        }

        .pc-empty {
            margin-top: 0.75rem;
            padding: 0.65rem 0.75rem;
            border-radius: 0.75rem;
            background: var(--pc-warn-soft);
            color: var(--pc-accent-2);
            font-size: 0.78rem;
            font-weight: 700;
        }

        @keyframes pc-pop {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pc-line, .pc-invoice-total strong {
            animation: pc-pop 0.22s ease;
        }

        .pc-coupon {
            margin-top: 0.95rem;
            padding: 0.85rem;
            border-radius: 0.85rem;
            background: var(--pc-soft);
            border: 1px dashed rgba(26, 26, 26, 0.12);
        }

        .pc-coupon-label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--pc-muted);
        }

        .pc-coupon-row {
            display: flex;
            gap: 0.45rem;
        }

        .pc-coupon-row input {
            flex: 1;
            min-width: 0;
            border: 1px solid var(--pc-line);
            border-radius: 0.65rem;
            background: var(--pc-surface);
            color: var(--pc-ink);
            font-family: inherit;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 0.55rem 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .pc-coupon-row input:focus {
            outline: 2px solid var(--pc-accent-soft);
            border-color: var(--pc-accent);
        }

        .pc-coupon-row button {
            border: 0;
            border-radius: 0.65rem;
            background: var(--pc-accent);
            color: #111;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.78rem;
            padding: 0.55rem 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .pc-coupon-row button.is-muted {
            background: var(--pc-surface);
            color: var(--pc-muted);
            border: 1px solid var(--pc-line);
        }

        .pc-coupon-msg {
            margin: 0.45rem 0 0;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .pc-coupon-msg.is-ok { color: #15803d; }
        .pc-coupon-msg.is-err { color: #b45309; }

        .pc-recs {
            margin-bottom: 1.35rem;
            padding: 1.1rem 1.15rem;
            border-radius: 1.15rem;
            border: 1px solid var(--pc-line);
            background:
                linear-gradient(135deg, var(--pc-accent-soft), transparent 55%),
                var(--pc-surface);
        }

        .pc-recs-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.9rem;
        }

        .pc-recs-head h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-recs-head p {
            margin: 0.35rem 0 0;
            color: var(--pc-muted);
            font-size: 0.85rem;
            line-height: 1.55;
            max-width: 40rem;
        }

        .pc-recs-grid {
            display: grid;
            gap: 0.7rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 720px) {
            .pc-recs-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1100px) {
            .pc-recs-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .pc-rec {
            text-align: start;
            border: 1.5px solid var(--pc-line);
            background: var(--pc-surface);
            border-radius: 0.95rem;
            padding: 0.9rem 1rem;
            cursor: pointer;
            transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
            width: 100%;
        }

        .pc-rec:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: var(--pc-shadow);
        }

        .pc-rec.is-on {
            border-color: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft);
            background: linear-gradient(180deg, var(--pc-accent-soft), transparent 50%), var(--pc-surface);
        }

        .pc-rec h5 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-rec p {
            margin: 0.35rem 0 0;
            color: var(--pc-muted);
            font-size: 0.78rem;
            line-height: 1.5;
            min-height: 2.4rem;
        }

        .pc-rec-meta {
            margin-top: 0.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--pc-accent);
        }

        .pc-rec-clear {
            margin-top: 0.75rem;
            border: 0;
            background: transparent;
            color: var(--pc-muted);
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            font-family: inherit;
        }
    </style>

    <div class="pc-hero">
        <div>
            <h3>{{ __('main.wizard.plan_choose_title') }}</h3>
            <p>{{ __('main.wizard.plan_information_hint') }}</p>
        </div>
        <div class="pc-period">
            <button type="button" :class="{ 'is-active': period === 'Month' }" x-on:click="setPeriod('Month')" x-text="labels.monthly"></button>
            <button type="button" :class="{ 'is-active': period === 'Year' }" x-on:click="setPeriod('Year')" x-text="labels.annual"></button>
        </div>
    </div>

    <div class="pc-recs" x-show="(catalog.recommendations || []).length">
        <div class="pc-recs-head">
            <div>
                <h4 x-text="labels.recommend_title"></h4>
                <p x-text="labels.recommend_hint"></p>
            </div>
        </div>
        <div class="pc-recs-grid">
            <template x-for="rec in catalog.recommendations" :key="rec.key">
                <button
                    type="button"
                    class="pc-rec"
                    :class="{ 'is-on': activeRecommendation === rec.key }"
                    x-on:click="applyRecommendation(rec)"
                >
                    <h5 x-text="rec.name"></h5>
                    <p x-text="rec.description"></p>
                    <div class="pc-rec-meta">
                        <span x-text="labels.recommend_modules.replace(':count', rec.modules.length)"></span>
                        <span x-text="activeRecommendation === rec.key ? labels.recommend_active : labels.recommend_apply"></span>
                    </div>
                </button>
            </template>
        </div>
        <button
            type="button"
            class="pc-rec-clear"
            x-show="activeRecommendation"
            x-on:click="clearRecommendation()"
            x-text="labels.recommend_customize"
        ></button>
    </div>

    <div class="pc-layout">
        <div>
            <div class="pc-platform">
                <span class="pc-platform-badge" x-text="labels.platform_badge"></span>
                <div style="flex:1">
                    <div class="pc-module-top">
                        <h4 x-text="catalog.platform.name"></h4>
                        <div class="pc-price-tag">
                            <span x-text="format(catalog.platform.price_month)"></span>
                            <small> / <span x-text="labels.per_month"></span></small>
                        </div>
                    </div>
                    <p x-text="catalog.platform.description"></p>
                </div>
            </div>

            <template x-for="group in catalog.groups" :key="group.key">
                <div class="pc-group" x-show="modulesFor(group.key).length">
                    <h4 class="pc-group-title" x-text="group.name"></h4>
                    <div class="pc-modules">
                        <template x-for="mod in modulesFor(group.key)" :key="mod.key">
                            <button
                                type="button"
                                class="pc-module"
                                :class="{ 'is-on': isSelected(mod.key) }"
                                x-on:click="toggle(mod.key)"
                            >
                                <span class="pc-check">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <div class="pc-module-top">
                                    <h5 x-text="mod.name"></h5>
                                    <div class="pc-price-tag">
                                        <span x-text="format(mod.price_month)"></span>
                                        <small> / <span x-text="labels.per_month"></span></small>
                                    </div>
                                </div>
                                <p x-text="mod.description"></p>
                                <div class="pc-req" x-show="mod.requires?.length" x-text="labels.requires + ': ' + requiredNames(mod)"></div>
                                <div class="pc-req" x-show="mod.requires_any?.length" x-text="labels.works_with + ': ' + requiresAnyNames(mod)"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <div class="pc-quotas">
                <h4 x-text="labels.quotas_title"></h4>
                <template x-for="quota in visibleQuotas" :key="quota.key">
                    <div class="pc-quota-row">
                        <div>
                            <div style="font-weight:800;color:var(--pc-ink)" x-text="quota.name"></div>
                            <div class="pc-quota-meta">
                                <span x-text="quota.included + ' ' + labels.included"></span>
                                ·
                                <span x-text="format(quota.price_per_extra_month) + ' / +1'"></span>
                            </div>
                        </div>
                        <div class="pc-stepper">
                            <button type="button" x-on:click="bump(quota.key, -1)">−</button>
                            <span x-text="quotaValue(quota.key)"></span>
                            <button type="button" x-on:click="bump(quota.key, 1)">+</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <aside class="pc-invoice" aria-live="polite">
            <div class="pc-invoice-head">
                <div class="pc-invoice-brand">
                    <span class="pc-invoice-brand-mark" x-text="labels.invoice_brand"></span>
                    <span class="pc-invoice-period" x-text="period === 'Year' ? labels.invoice_period_year : labels.invoice_period_month"></span>
                </div>
                <h4 x-text="labels.invoice_title"></h4>
                <p class="pc-invoice-meta" x-text="labels.invoice_meta"></p>
            </div>

            <div class="pc-invoice-body">
                <div class="pc-invoice-cols">
                    <span x-text="labels.invoice_desc"></span>
                    <span x-text="labels.invoice_amount"></span>
                </div>

                <div class="pc-invoice-lines">
                    <template x-for="line in lineItems" :key="line.key">
                        <div class="pc-line" :class="{ 'is-discount': line.type === 'discount' }">
                            <div class="pc-line-label">
                                <strong x-text="line.label"></strong>
                                <small x-show="line.meta" x-text="line.meta"></small>
                            </div>
                            <div class="pc-line-amount" x-text="(line.total < 0 ? '−' : '') + format(Math.abs(line.total))"></div>
                        </div>
                    </template>
                </div>

                <div class="pc-invoice-totals">
                    <div class="pc-total-row">
                        <span x-text="labels.invoice_subtotal"></span>
                        <strong x-text="format(periodSubtotal)"></strong>
                    </div>
                    <div class="pc-total-row is-discount" x-show="coupon_discount > 0">
                        <span x-text="labels.invoice_discount"></span>
                        <strong x-text="'−' + format(coupon_discount)"></strong>
                    </div>
                </div>

                <div class="pc-invoice-total">
                    <span x-text="labels.due_now"></span>
                    <strong>
                        <span x-text="format(periodTotal)"></span>
                        <small x-text="labels.sar"></small>
                    </strong>
                </div>

                <div class="pc-coupon">
                    <label class="pc-coupon-label" x-text="labels.coupon_label"></label>
                    <div class="pc-coupon-row">
                        <input
                            type="text"
                            x-model="coupon_input"
                            :placeholder="labels.coupon_placeholder"
                            :disabled="!!coupon_code || coupon_loading"
                            x-on:keydown.enter.prevent="applyCoupon()"
                        >
                        <button
                            type="button"
                            x-show="!coupon_code"
                            x-on:click="applyCoupon()"
                            :disabled="coupon_loading"
                            x-text="labels.coupon_apply"
                        ></button>
                        <button
                            type="button"
                            class="is-muted"
                            x-show="coupon_code"
                            x-on:click="clearCoupon()"
                            x-text="labels.coupon_remove"
                        ></button>
                    </div>
                    <p
                        class="pc-coupon-msg"
                        :class="coupon_ok ? 'is-ok' : 'is-err'"
                        x-show="coupon_message"
                        x-text="coupon_message"
                    ></p>
                </div>

                <p class="pc-invoice-note" x-show="period === 'Year'" x-text="labels.yearly_note"></p>
                <p class="pc-invoice-note" x-show="period === 'Month'" x-text="labels.modules_hint"></p>
                <div class="pc-empty" x-show="modules.length === 0" x-text="labels.select_at_least"></div>
                <div class="pc-empty" x-show="modules.length > 0 && softDependencyBroken" x-text="labels.reports_needs_data"></div>
            </div>
        </aside>
    </div>
</div>

<script>
    function planConfigurator(catalog, labels, options = {}) {
        const initial = options.initial || {};
        return {
            catalog,
            labels,
            wirePath: options.wirePath || 'data.plan_config',
            period: initial.period || 'Month',
            modules: Array.isArray(initial.modules) ? [...initial.modules] : [],
            activeRecommendation: null,
            employees: initial.employees ?? catalog.quotas?.find(q => q.key === 'employees')?.included ?? 5,
            establishments: initial.establishments ?? catalog.quotas?.find(q => q.key === 'establishments')?.included ?? 1,
            screen_devices: initial.screen_devices ?? catalog.quotas?.find(q => q.key === 'screen_devices')?.included ?? 1,
            coupon_input: initial.coupon_code || '',
            coupon_code: '',
            coupon_discount: 0,
            coupon_label: '',
            coupon_message: '',
            coupon_ok: false,
            coupon_loading: false,
            init() {
                this.syncToWire();
                this.$watch('modules', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('employees', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('establishments', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('screen_devices', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('period', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                if (this.coupon_input) {
                    this.applyCoupon();
                }
            },
            applyRecommendation(rec) {
                const available = new Set((this.catalog.modules || []).map(m => m.key));
                const selected = [];
                (rec.modules || []).forEach(key => {
                    if (!available.has(key)) return;
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    selected.push(key);
                    (mod?.requires || []).forEach(r => {
                        if (available.has(r) && !selected.includes(r)) selected.push(r);
                    });
                });
                this.modules = Array.from(new Set(selected));
                this.activeRecommendation = rec.key;

                const quotas = rec.quotas || {};
                Object.keys(quotas).forEach(key => {
                    const def = (this.catalog.quotas || []).find(q => q.key === key);
                    if (!def) return;
                    const value = Math.min(def.max, Math.max(def.min, Number(quotas[key])));
                    this.setQuotaValue(key, value);
                });
            },
            clearRecommendation() {
                this.activeRecommendation = null;
                this.modules = [];
                (this.catalog.quotas || []).forEach(q => {
                    this.setQuotaValue(q.key, q.included);
                });
            },
            modulesFor(groupKey) {
                return (this.catalog.modules || []).filter(m => m.group === groupKey);
            },
            get visibleQuotas() {
                return (this.catalog.quotas || []).filter(quota => {
                    if (!quota.linked_module) return true;
                    return this.modules.includes(quota.linked_module);
                });
            },
            quotaValue(key) {
                if (key === 'employees') return this.employees;
                if (key === 'establishments') return this.establishments;
                if (key === 'screen_devices') return this.screen_devices;
                return 0;
            },
            setQuotaValue(key, value) {
                if (key === 'employees') this.employees = value;
                else if (key === 'establishments') this.establishments = value;
                else if (key === 'screen_devices') this.screen_devices = value;
            },
            isSelected(key) {
                return this.modules.includes(key);
            },
            requiredNames(mod) {
                return (mod.requires || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            requiresAnyNames(mod) {
                return (mod.requires_any || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            get softDependencyBroken() {
                return (this.catalog.modules || []).some(mod => {
                    if (!this.modules.includes(mod.key)) return false;
                    const any = mod.requires_any || [];
                    if (!any.length) return false;
                    return !any.some(k => this.modules.includes(k));
                });
            },
            toggle(key) {
                this.activeRecommendation = null;
                if (this.isSelected(key)) {
                    this.modules = this.modules.filter(k => k !== key);
                    this.modules = this.modules.filter(k => {
                        const mod = (this.catalog.modules || []).find(m => m.key === k);
                        return !(mod?.requires || []).includes(key);
                    });
                    if (key === 'digital_screens') {
                        const q = (this.catalog.quotas || []).find(item => item.key === 'screen_devices');
                        this.screen_devices = q?.included ?? 1;
                    }
                } else {
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    const next = new Set(this.modules);
                    next.add(key);
                    (mod?.requires || []).forEach(r => next.add(r));
                    this.modules = Array.from(next);
                }
            },
            bump(key, delta) {
                this.activeRecommendation = null;
                const quota = (this.catalog.quotas || []).find(q => q.key === key);
                if (!quota) return;
                const current = this.quotaValue(key);
                const next = Math.min(quota.max, Math.max(quota.min, current + delta));
                this.setQuotaValue(key, next);
            },
            setPeriod(period) {
                this.period = period;
            },
            format(value) {
                return Math.round(Number(value || 0)).toLocaleString();
            },
            get baseLineItems() {
                const items = [];
                const platform = this.catalog.platform;
                items.push({
                    key: 'platform',
                    label: platform.name,
                    total: platform.price_month,
                    type: 'platform',
                });
                (this.catalog.modules || []).forEach(mod => {
                    if (this.modules.includes(mod.key)) {
                        items.push({ key: mod.key, label: mod.name, total: mod.price_month, type: 'module' });
                    }
                });
                this.visibleQuotas.forEach(quota => {
                    const count = this.quotaValue(quota.key);
                    const extra = Math.max(0, count - quota.included);
                    if (extra > 0) {
                        items.push({
                            key: quota.key + '_extra',
                            label: quota.name + ' (+' + extra + ')',
                            total: extra * quota.price_per_extra_month,
                            type: 'quota',
                        });
                    }
                });
                return items;
            },
            get lineItems() {
                const months = this.period === 'Year' ? (this.catalog.yearly_months_charged || 12) : 1;
                return this.baseLineItems.map(line => {
                    const monthly = Number(line.total || 0);
                    return {
                        ...line,
                        total: monthly * months,
                        meta: this.period === 'Year'
                            ? `${this.format(monthly)} × ${months}`
                            : `/ ${this.labels.per_month}`,
                    };
                });
            },
            get monthlyTotal() {
                return this.baseLineItems.reduce((sum, line) => sum + Number(line.total || 0), 0);
            },
            get periodSubtotal() {
                if (this.period === 'Year') {
                    return this.monthlyTotal * (this.catalog.yearly_months_charged || 12);
                }
                return this.monthlyTotal;
            },
            get periodTotal() {
                return Math.max(0, this.periodSubtotal - Number(this.coupon_discount || 0));
            },
            async applyCoupon() {
                if (this.coupon_loading) return;
                this.coupon_loading = true;
                this.coupon_message = '';
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_input, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_code = result.code || '';
                        this.coupon_input = this.coupon_code;
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_message = result.message || '';
                        this.coupon_ok = true;
                    } else {
                        this.coupon_code = '';
                        this.coupon_discount = 0;
                        this.coupon_label = '';
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {
                    this.coupon_code = '';
                    this.coupon_discount = 0;
                    this.coupon_label = '';
                    this.coupon_message = '';
                    this.coupon_ok = false;
                    this.syncToWire();
                } finally {
                    this.coupon_loading = false;
                }
            },
            async refreshCouponDiscount() {
                if (!this.coupon_code) {
                    this.coupon_discount = 0;
                    return;
                }
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_code, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_ok = true;
                    } else {
                        this.clearCoupon(false);
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {}
            },
            clearCoupon(clearInput = true) {
                this.coupon_code = '';
                this.coupon_discount = 0;
                this.coupon_label = '';
                this.coupon_message = '';
                this.coupon_ok = false;
                if (clearInput) this.coupon_input = '';
                this.syncToWire();
            },
            syncToWire() {
                try {
                    this.$wire.set(this.wirePath, {
                        period: this.period,
                        modules: this.modules,
                        employees: this.employees,
                        establishments: this.establishments,
                        screen_devices: this.modules.includes('digital_screens') ? this.screen_devices : 0,
                        coupon_code: this.coupon_code || null,
                    });
                } catch (e) {}
            },
        };
    }
</script>
