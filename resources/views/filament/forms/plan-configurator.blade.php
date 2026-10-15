@php
    $catalog = $catalog ?? [];
    $required = $required ?? true;
    $currency = $catalog['currency'] ?? 'SAR';
    $yearlyMonths = (int) ($catalog['yearly_months_charged'] ?? 10);
    $yearlyGifted = (int) ($catalog['yearly_months_gifted'] ?? max(0, 12 - $yearlyMonths));
    $wirePath = $wirePath ?? 'data.plan_config';
    $initial = is_array($initial ?? null) ? $initial : [];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

<div
    class="plan-configurator"
    x-data="window.planConfigurator(@js($catalog), @js([
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
        'yearly_note' => __('main.wizard.plan_yearly_note', ['months' => $yearlyMonths, 'gifted' => $yearlyGifted]),
        'yearly_badge' => __('main.wizard.plan_yearly_badge'),
        'yearly_hero' => __('main.wizard.plan_yearly_hero'),
        'yearly_value' => __('main.wizard.plan_yearly_value'),
        'yearly_gift' => __('main.wizard.plan_yearly_gift'),
        'yearly_price_gift' => __('main.wizard.plan_yearly_price_gift'),
        'yearly_was' => __('main.wizard.plan_yearly_was'),
        'yearly_effective' => __('main.wizard.plan_yearly_effective'),
        'yearly_effective_hint' => __('main.wizard.plan_yearly_effective_hint'),
        'gift_banner_title' => __('main.wizard.plan_gift_banner_title'),
        'gift_banner_body' => __('main.wizard.plan_gift_banner_body'),
        'modules_hint' => __('main.wizard.plan_modules_hint'),
        'quotas_title' => __('main.wizard.plan_quotas_title'),
        'platform_badge' => __('main.wizard.plan_platform_badge'),
        'platform_locked' => __('main.wizard.plan_platform_locked'),
        'depends_on_platform' => __('main.wizard.plan_depends_on_platform'),
        'foundation_note' => __('main.wizard.plan_foundation_note'),
        'employees_count' => __('main.wizard.plan_employees_count'),
        'employees_quota_hint' => __('main.wizard.plan_employees_quota_hint'),
        'pos_devices_count' => __('main.wizard.plan_pos_devices_count'),
        'pos_devices_hint' => __('main.wizard.plan_pos_devices_hint'),
        'waiter_seats_count' => __('main.wizard.plan_waiter_seats_count'),
        'waiter_seats_hint' => __('main.wizard.plan_waiter_seats_hint'),
        'kitchen_devices_count' => __('main.wizard.plan_kitchen_devices_count'),
        'kitchen_devices_hint' => __('main.wizard.plan_kitchen_devices_hint'),
        'quota_cap_hint' => __('main.wizard.plan_quota_cap_hint'),
        'requires' => __('main.wizard.plan_requires'),
        'includes' => __('main.wizard.plan_includes'),
        'module_employees' => __('main.wizard.plan_module_employees'),
        'works_with' => __('main.wizard.plan_works_with'),
        'empty_modules' => __('main.wizard.plan_modules_required'),
        'select_at_least' => __('main.wizard.plan_select_at_least'),
        'module_select' => __('main.wizard.plan_module_select'),
        'module_selected' => __('main.wizard.plan_module_selected'),
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
            --pc-line: rgba(26, 26, 26, 0.09);
            --pc-surface: rgba(255, 253, 247, 0.94);
            --pc-soft: #faf8f2;
            --pc-accent: #ebb81e;
            --pc-accent-2: #b88912;
            --pc-accent-soft: rgba(235, 184, 30, 0.18);
            --pc-warn-soft: rgba(247, 231, 168, 0.65);
            --pc-shadow: 0 14px 36px rgba(26, 26, 26, 0.07);
            --pc-hex: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
            font-family: "Cairo", system-ui, sans-serif;
            position: relative;
        }

        .plan-configurator [x-cloak] {
            display: none !important;
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
            gap: 1rem;
            align-items: start;
        }

        @media (min-width: 1100px) {
            .pc-layout {
                grid-template-columns: minmax(0, 1fr) 300px;
            }
        }

        .pc-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
            padding: 0.7rem 0.85rem;
            border: 1px solid var(--pc-line);
            border-radius: 1rem;
            background:
                linear-gradient(120deg, var(--pc-accent-soft), transparent 42%),
                var(--pc-surface);
            box-shadow: var(--pc-shadow);
        }

        .pc-hero h3 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--pc-ink);
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .pc-hero h3::before {
            content: "";
            width: 0.75rem;
            height: 0.85rem;
            background: var(--pc-accent);
            clip-path: var(--pc-hex);
            flex-shrink: 0;
        }

        .pc-hero p {
            margin: 0.25rem 0 0;
            color: var(--pc-muted);
            font-size: 0.8rem;
            line-height: 1.5;
            max-width: 36rem;
        }

        .pc-period-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.35rem;
        }

        .pc-period {
            display: inline-flex;
            padding: 0.2rem;
            border-radius: 0.95rem;
            background: var(--pc-soft);
            border: 1px solid var(--pc-line);
        }

        .pc-period button {
            border: 0;
            background: transparent;
            color: var(--pc-muted);
            font-weight: 800;
            font-size: 0.95rem;
            padding: 0.58rem 1.05rem;
            border-radius: 0.8rem;
            cursor: pointer;
            transition: 0.2s ease;
            font-family: inherit;
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            gap: 0.4rem;
            line-height: 1;
            white-space: nowrap;
        }

        .pc-period button.is-active {
            background: linear-gradient(135deg, #f0c43a, var(--pc-accent));
            color: #111;
            box-shadow: 0 8px 18px rgba(184, 137, 18, 0.25);
        }

        .pc-period-hint {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--pc-accent-2);
            max-width: 17rem;
            text-align: end;
            line-height: 1.4;
        }

        .pc-gift-banner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin: 0 0 0.95rem;
            padding: 0.85rem 1rem;
            border-radius: 1.05rem;
            background:
                linear-gradient(120deg, rgba(22, 163, 74, 0.12), rgba(235, 184, 30, 0.16) 55%, transparent);
            border: 1px solid rgba(22, 163, 74, 0.22);
            box-shadow: 0 10px 24px rgba(22, 163, 74, 0.06);
        }

        .pc-gift-icon {
            flex-shrink: 0;
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.85rem;
            display: grid;
            place-items: center;
            background: #166534;
            color: #ecfdf3;
            box-shadow: 0 8px 16px rgba(22, 101, 52, 0.22);
        }

        .pc-gift-banner h4 {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 800;
            color: #166534;
        }

        .pc-gift-banner p {
            margin: 0.2rem 0 0;
            font-size: 0.78rem;
            line-height: 1.55;
            color: var(--pc-ink);
            font-weight: 600;
        }

        .pc-price-tag {
            flex-shrink: 0;
            font-weight: 800;
            color: var(--pc-ink);
            font-size: 0.98rem;
            text-align: end;
        }

        .pc-price-row {
            display: inline-flex;
            align-items: baseline;
            gap: 0.35rem;
            white-space: nowrap;
            line-height: 1.15;
            flex-wrap: nowrap;
        }

        .pc-price-was {
            text-decoration: line-through;
            text-decoration-thickness: 1.5px;
            text-decoration-color: rgba(156, 163, 175, 0.9);
            color: #9ca3af;
            font-size: inherit;
            font-weight: 700;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .pc-price-now {
            color: #15803d;
            font-size: inherit;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .pc-price-row small {
            color: var(--pc-muted);
            font-weight: 700;
            font-size: 0.68rem;
            line-height: 1;
        }

        .pc-group {
            margin-bottom: 1rem;
        }

        .pc-group-title {
            margin: 0 0 0.6rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--pc-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pc-group-title::before {
            content: "";
            width: 0.55rem;
            height: 0.62rem;
            background: var(--pc-accent-2);
            clip-path: var(--pc-hex);
            opacity: 0.85;
        }

        .pc-modules {
            display: grid;
            gap: 0.65rem;
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
            border-radius: 1.15rem;
            padding: 0;
            transition: border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
            width: 100%;
            overflow: hidden;
            font-family: inherit;
            display: flex;
            flex-direction: column;
        }

        .pc-module::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: transparent;
            transition: background 0.22s ease;
            z-index: 2;
        }

        .pc-module:hover {
            transform: translateY(-2px);
            border-color: rgba(235, 184, 30, 0.55);
            box-shadow: var(--pc-shadow);
        }

        .pc-module.is-on {
            border-color: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft), var(--pc-shadow);
            background:
                linear-gradient(165deg, rgba(235, 184, 30, 0.14), transparent 46%),
                var(--pc-surface);
        }

        .pc-module.is-on::before {
            background: linear-gradient(180deg, #f0c43a, var(--pc-accent-2));
        }

        .pc-module-main {
            all: unset;
            box-sizing: border-box;
            display: block;
            width: 100%;
            padding: 0.9rem 1rem 0.7rem;
            cursor: pointer;
            font-family: inherit;
            color: inherit;
            text-align: start;
            flex: 1 1 auto;
        }

        .pc-module-main:focus-visible {
            outline: 2px solid var(--pc-accent);
            outline-offset: -2px;
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
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .pc-module-main > p {
            margin: 0.35rem 0 0;
            color: var(--pc-muted);
            font-size: 0.74rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Soft dock: counter + select affordance */
        .pc-module-dock {
            margin-top: auto;
            border-top: 1px solid var(--pc-line);
            background: rgba(250, 248, 242, 0.88);
            padding: 0.55rem 0.7rem 0.65rem;
            display: grid;
            gap: 0.45rem;
        }

        .pc-module.is-on .pc-module-dock {
            background: linear-gradient(180deg, rgba(235, 184, 30, 0.1), rgba(250, 248, 242, 0.95));
            border-top-color: rgba(235, 184, 30, 0.28);
        }

        .pc-module-quota {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            min-width: 0;
        }

        .pc-module-quota-copy {
            min-width: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            gap: 0.18rem;
        }

        .pc-module-quota-copy strong {
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--pc-ink);
            line-height: 1.2;
        }

        .pc-module-quota-copy .pc-quota-sub {
            font-size: 0.66rem;
            color: var(--pc-muted);
            line-height: 1.35;
            font-weight: 600;
        }

        .pc-quota-price {
            margin-top: 0.05rem;
            font-size: 0.84rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-quota-price small {
            font-size: 0.62rem;
        }

        .pc-module-foot {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.55rem;
            padding-top: 0.1rem;
        }

        .pc-module.has-inline-quota .pc-module-foot {
            border-top: 1px dashed rgba(26, 26, 26, 0.08);
            padding-top: 0.4rem;
        }

        .pc-module-quota-cap {
            margin: 0;
            padding: 0.35rem 0.5rem;
            border-radius: 0.55rem;
            font-size: 0.66rem;
            font-weight: 700;
            line-height: 1.4;
            color: #8a5a00;
            background: rgba(235, 184, 30, 0.14);
            border: 1px solid rgba(235, 184, 30, 0.28);
        }

        .pc-module-select {
            all: unset;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
            font-family: inherit;
            color: var(--pc-muted);
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.1rem;
        }

        .pc-module-select:focus-visible {
            outline: 2px solid var(--pc-accent);
            outline-offset: 2px;
            border-radius: 0.4rem;
        }

        .pc-module.is-on .pc-module-select {
            color: var(--pc-ink);
        }

        .pc-check {
            width: 1.35rem;
            height: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(26, 26, 26, 0.06);
            color: transparent;
            clip-path: var(--pc-hex);
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            flex-shrink: 0;
        }

        .pc-module.is-on .pc-check {
            background: var(--pc-accent);
            color: #111;
            box-shadow: 0 4px 10px rgba(184, 137, 18, 0.35);
            animation: pc-pop 0.28s ease;
        }

        .pc-module.quota-preview .pc-module-quota {
            opacity: 0.78;
        }

        .pc-stepper.is-capped button:last-of-type {
            opacity: 0.45;
            cursor: not-allowed;
        }

        @keyframes pc-pop {
            from { transform: scale(0.55); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .pc-req {
            margin-top: 0.45rem;
            font-size: 0.7rem;
            color: var(--pc-accent-2);
            font-weight: 600;
        }

        .pc-includes {
            margin: 0.35rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.16rem;
        }

        .pc-includes li {
            position: relative;
            padding-inline-start: 0.85rem;
            font-size: 0.7rem;
            line-height: 1.35;
            color: var(--pc-muted);
            font-weight: 600;
        }

        .pc-includes li::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0.35rem;
            width: 0.38rem;
            height: 0.42rem;
            background: var(--pc-accent);
            clip-path: var(--pc-hex);
        }

        .pc-includes-label {
            margin-top: 0.45rem;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: var(--pc-ink);
            opacity: 0.72;
        }

        .pc-quotas {
            border: 1px solid var(--pc-line);
            border-radius: 1rem;
            background: var(--pc-surface);
            padding: 0.85rem 1rem;
            box-shadow: var(--pc-shadow);
        }

        .pc-quotas h4 {
            margin: 0 0 0.7rem;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--pc-ink);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pc-quotas h4::before {
            content: "";
            width: 0.6rem;
            height: 0.68rem;
            background: var(--pc-accent);
            clip-path: var(--pc-hex);
        }

        .pc-quota-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.6rem 0;
            border-top: 1px solid var(--pc-line);
        }

        .pc-quota-row:first-of-type { border-top: 0; padding-top: 0; }

        .pc-quota-meta {
            color: var(--pc-muted);
            font-size: 0.74rem;
            margin-top: 0.15rem;
        }

        .pc-stepper {
            display: inline-flex;
            align-items: center;
            gap: 0.12rem;
            background: var(--pc-surface);
            border: 1px solid var(--pc-line);
            border-radius: 999px;
            padding: 0.12rem;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
        }

        .pc-module.is-on .pc-stepper {
            border-color: rgba(235, 184, 30, 0.45);
            background: rgba(255, 255, 255, 0.92);
        }

        .pc-stepper button {
            width: 1.7rem;
            height: 1.7rem;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--pc-ink);
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
            font-family: inherit;
            line-height: 1;
        }

        .pc-stepper button:hover {
            background: var(--pc-accent-soft);
            color: #111;
        }

        .pc-stepper span {
            min-width: 1.7rem;
            text-align: center;
            font-weight: 800;
            font-size: 0.88rem;
            color: var(--pc-ink);
            font-variant-numeric: tabular-nums;
        }

        .pc-invoice {
            position: sticky;
            top: 0.75rem;
            border-radius: 1.1rem;
            border: 1px solid var(--pc-line);
            background:
                linear-gradient(165deg, var(--pc-accent-soft), transparent 34%),
                var(--pc-surface);
            box-shadow: var(--pc-shadow);
            overflow: hidden;
        }

        .pc-invoice::before {
            content: "";
            position: absolute;
            top: -20px;
            inset-inline-end: -14px;
            width: 90px;
            height: 90px;
            background: rgba(235, 184, 30, 0.16);
            clip-path: var(--pc-hex);
            pointer-events: none;
        }

        .pc-invoice-head {
            padding: 0.95rem 1rem 0.7rem;
            border-bottom: 1px solid var(--pc-line);
            position: relative;
            z-index: 1;
        }

        .pc-invoice-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.45rem;
        }

        .pc-invoice-brand-mark {
            font-weight: 800;
            font-size: 0.82rem;
            color: var(--pc-accent-2);
        }

        .pc-invoice-period {
            font-size: 0.68rem;
            font-weight: 800;
            background: var(--pc-accent);
            color: #111;
            padding: 0.22rem 0.5rem;
            border-radius: 0.45rem;
        }

        .pc-invoice-head h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-invoice-meta {
            margin: 0.25rem 0 0;
            color: var(--pc-muted);
            font-size: 0.72rem;
        }

        .pc-invoice-body {
            padding: 0.7rem 1rem 0.85rem;
            position: relative;
            z-index: 1;
        }

        .pc-invoice-cols {
            display: flex;
            justify-content: space-between;
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--pc-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.45rem;
        }

        .pc-invoice-lines {
            display: grid;
            gap: 0.35rem;
        }

        .pc-line {
            display: flex;
            justify-content: space-between;
            gap: 0.65rem;
            font-size: 0.78rem;
            padding: 0.35rem 0;
            border-top: 1px dashed var(--pc-line);
            animation: pc-pop 0.22s ease;
        }

        .pc-line:first-child { border-top: 0; }

        .pc-line-label strong {
            display: block;
            font-weight: 800;
            color: var(--pc-ink);
        }

        .pc-line-label small {
            color: var(--pc-muted);
            font-size: 0.68rem;
        }

        .pc-line-amount {
            font-weight: 800;
            color: var(--pc-ink);
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }

        .pc-line.is-discount,
        .pc-line.is-discount .pc-line-amount {
            color: #15803d;
        }

        .pc-invoice-totals {
            margin-top: 0.35rem;
            padding-top: 0.7rem;
            border-top: 1px solid var(--pc-line);
            display: grid;
            gap: 0.4rem;
        }

        .pc-total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--pc-muted);
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
            margin-top: 0.45rem;
            padding: 0.8rem 0.9rem;
            border-radius: 0.85rem;
            background:
                linear-gradient(135deg, rgba(235, 184, 30, 0.24), rgba(235, 184, 30, 0.08)),
                var(--pc-soft);
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.5rem;
            border: 1px solid rgba(235, 184, 30, 0.28);
        }

        .pc-invoice-total span {
            color: var(--pc-ink);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .pc-invoice-total strong {
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--pc-ink);
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            display: inline-flex;
            align-items: baseline;
            gap: 0.28rem;
            animation: pc-pop 0.22s ease;
        }

        .pc-invoice-total strong small {
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--pc-muted);
        }

        .pc-invoice-note {
            margin: 0.45rem 0 0;
            font-size: 0.68rem;
            color: var(--pc-muted);
            line-height: 1.45;
        }

        .pc-eq-line {
            margin-top: 0.45rem;
            display: flex;
            justify-content: flex-end;
        }

        .pc-empty {
            margin-top: 0.65rem;
            padding: 0.6rem 0.7rem;
            border-radius: 0.7rem;
            background: var(--pc-warn-soft);
            color: var(--pc-accent-2);
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .pc-warn {
            margin-top: 0.65rem;
            padding: 0.55rem 0.7rem;
            border-radius: 0.7rem;
            background: var(--pc-warn-soft);
            color: #92400e;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .pc-coupon {
            margin-top: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.85rem;
            border: 1px solid var(--pc-line);
            background: rgba(255, 253, 247, 0.7);
        }

        .pc-coupon-label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--pc-ink);
        }

        .pc-coupon-row {
            display: flex;
            gap: 0.4rem;
        }

        .pc-coupon-row input {
            flex: 1;
            min-width: 0;
            border: 1px solid var(--pc-line);
            border-radius: 0.65rem;
            background: var(--pc-soft);
            padding: 0.5rem 0.65rem;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .pc-coupon-row input:focus {
            outline: none;
            border-color: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft);
        }

        .pc-coupon-row button {
            border: 0;
            border-radius: 0.65rem;
            background: var(--pc-accent);
            color: #111;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.74rem;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            white-space: nowrap;
            transition: filter 0.15s ease, transform 0.15s ease;
        }

        .pc-coupon-row button:hover {
            filter: brightness(1.04);
            transform: translateY(-1px);
        }

        .pc-coupon-row button.is-muted {
            background: var(--pc-surface);
            color: var(--pc-muted);
            border: 1px solid var(--pc-line);
        }

        .pc-coupon-msg {
            margin: 0.4rem 0 0;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .pc-coupon-msg.is-ok { color: #15803d; }
        .pc-coupon-msg.is-err { color: #b45309; }

        .pc-recs {
            margin-bottom: 0.95rem;
            padding: 0.85rem 0.95rem;
            border-radius: 1.1rem;
            border: 1px solid var(--pc-line);
            background:
                linear-gradient(135deg, var(--pc-accent-soft), transparent 50%),
                var(--pc-surface);
            box-shadow: var(--pc-shadow);
            position: relative;
            overflow: hidden;
        }

        .pc-recs::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='32' viewBox='0 0 56 32'%3E%3Cpath fill='none' stroke='%23ebb81e' stroke-opacity='0.14' stroke-width='1' d='M14 0 L28 8 L28 24 L14 32 L0 24 L0 8 Z M42 0 L56 8 L56 24 L42 32 L28 24 L28 8 Z'/%3E%3C/svg%3E");
            background-size: 56px 32px;
            opacity: 0.7;
            pointer-events: none;
        }

        .pc-recs-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 0.7rem;
            position: relative;
            z-index: 1;
        }

        .pc-recs-head h4 {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--pc-ink);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pc-recs-head h4::before {
            content: "";
            width: 0.7rem;
            height: 0.8rem;
            background: var(--pc-accent);
            clip-path: var(--pc-hex);
        }

        .pc-recs-head p {
            margin: 0.25rem 0 0;
            color: var(--pc-muted);
            font-size: 0.78rem;
            line-height: 1.5;
            max-width: 38rem;
        }

        .pc-recs-grid {
            display: grid;
            gap: 0.6rem;
            grid-template-columns: 1fr;
            position: relative;
            z-index: 1;
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
            position: relative;
            text-align: start;
            border: 1.5px solid var(--pc-line);
            background: rgba(255, 253, 247, 0.92);
            border-radius: 0.9rem;
            padding: 0.8rem 0.85rem;
            cursor: pointer;
            transition: border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
            width: 100%;
            overflow: hidden;
            font-family: inherit;
        }

        .pc-rec::after {
            content: "";
            position: absolute;
            top: -12px;
            inset-inline-end: -10px;
            width: 46px;
            height: 46px;
            background: rgba(235, 184, 30, 0.12);
            clip-path: var(--pc-hex);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .pc-rec:hover {
            transform: translateY(-2px);
            border-color: rgba(235, 184, 30, 0.65);
            box-shadow: 0 12px 28px rgba(184, 137, 18, 0.14);
        }

        .pc-rec:hover::after {
            transform: scale(1.15) rotate(10deg);
            background: rgba(235, 184, 30, 0.28);
        }

        .pc-rec.is-on {
            border-color: var(--pc-accent);
            box-shadow: 0 0 0 3px var(--pc-accent-soft), 0 12px 28px rgba(184, 137, 18, 0.16);
            background: linear-gradient(165deg, var(--pc-accent-soft), transparent 55%), #fffdf7;
        }

        .pc-rec.is-on::after {
            background: var(--pc-accent);
            transform: scale(1.05);
        }

        .pc-rec h5 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--pc-ink);
            position: relative;
            z-index: 1;
            padding-inline-end: 1rem;
        }

        .pc-rec p {
            margin: 0.3rem 0 0;
            color: var(--pc-muted);
            font-size: 0.74rem;
            line-height: 1.45;
            min-height: 0;
            position: relative;
            z-index: 1;
        }

        .pc-rec-meta {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.45rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--pc-accent-2);
            position: relative;
            z-index: 1;
        }

        .pc-rec.is-on .pc-rec-meta {
            color: #8a6a0d;
        }

        .pc-rec-clear {
            margin-top: 0.6rem;
            border: 0;
            background: transparent;
            color: var(--pc-muted);
            font-weight: 700;
            font-size: 0.76rem;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            font-family: inherit;
            position: relative;
            z-index: 1;
        }

        @media (prefers-reduced-motion: reduce) {
            .pc-module,
            .pc-rec,
            .pc-check,
            .pc-stepper button,
            .pc-coupon-row button {
                transition: none;
                animation: none;
            }
        }
    </style>

    <div class="pc-hero">
        <div>
            <h3>{{ __('main.wizard.plan_choose_title') }}</h3>
            <p>{{ __('main.wizard.plan_information_hint') }}</p>
        </div>
        <div class="pc-period-wrap">
            <div class="pc-period" role="group" aria-label="{{ __('general.monthly') }} / {{ __('general.annual') }}">
                <button type="button" :class="{ 'is-active': period === 'Year' }" x-on:click="setPeriod('Year')" x-text="labels.annual"></button>
                <button type="button" :class="{ 'is-active': period === 'Month' }" x-on:click="setPeriod('Month')" x-text="labels.monthly"></button>
            </div>
        </div>
    </div>

    <div class="pc-gift-banner" x-show="isYearlyDeal()" x-cloak>
        <span class="pc-gift-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path d="M9.375 3a1.875 1.875 0 0 0 0 3.75h1.875v4.5H3.375A1.125 1.125 0 0 1 2.25 10.125V9.75c0-1.036.84-1.875 1.875-1.875h4.5A1.875 1.875 0 0 1 9.375 3Z" />
                <path d="M12.75 6.75h1.875a1.875 1.875 0 1 0-1.875-1.875V6.75ZM12.75 8.25v4.5h7.875c.621 0 1.125-.504 1.125-1.125V9.75a1.875 1.875 0 0 0-1.875-1.875h-7.125ZM12.75 15v6.75h6.375A1.875 1.875 0 0 0 21 19.875V15H12.75ZM11.25 21.75V15H3v4.875c0 1.036.84 1.875 1.875 1.875H11.25Z" />
            </svg>
        </span>
        <div>
            <h4 x-text="labels.gift_banner_title"></h4>
            <p x-text="labels.gift_banner_body"></p>
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
            <template x-for="group in catalog.groups" :key="group.key">
                <div class="pc-group" x-show="modulesFor(group.key).length">
                    <h4 class="pc-group-title" x-text="group.name"></h4>
                    <div class="pc-modules">
                        <template x-for="mod in modulesFor(group.key)" :key="mod.key">
                            <article
                                class="pc-module"
                                :class="{
                                    'is-on': isSelected(mod.key),
                                    'has-inline-quota': Boolean(resolveInlineQuotaKey(mod) && inlineQuota(mod)),
                                    'quota-preview': Boolean(resolveInlineQuotaKey(mod) && inlineQuota(mod) && !isSelected(mod.key)),
                                }"
                            >
                                <button
                                    type="button"
                                    class="pc-module-main"
                                    :aria-pressed="isSelected(mod.key)"
                                    x-on:click="toggle(mod.key)"
                                >
                                    <div class="pc-module-top">
                                        <h5 x-text="mod.name"></h5>
                                        <div class="pc-price-tag">
                                            <span class="pc-price-row">
                                                <span class="pc-price-was" x-show="isYearlyDeal()" x-text="format(mod.price_month)"></span>
                                                <span :class="isYearlyDeal() ? 'pc-price-now' : ''" x-text="displayMonthly(mod.price_month)"></span>
                                                <small x-text="' / ' + labels.per_month"></small>
                                            </span>
                                        </div>
                                    </div>
                                    <p x-text="mod.description"></p>
                                    <template x-if="Number(mod.included_employees) > 0 || mod.includes?.length">
                                        <div>
                                            <div class="pc-includes-label" x-text="labels.includes"></div>
                                            <ul class="pc-includes">
                                                <li x-show="Number(mod.included_employees) > 0" x-text="employeeSeatLabel(mod.included_employees)"></li>
                                                <template x-for="(item, idx) in (mod.includes || [])" :key="mod.key + '-inc-' + idx">
                                                    <li x-text="item"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>
                                    <div class="pc-req" x-show="mod.requires?.length" x-text="labels.requires + ': ' + requiredNames(mod)"></div>
                                    <div class="pc-req" x-show="mod.requires_any?.length" x-text="labels.works_with + ': ' + requiresAnyNames(mod)"></div>
                                </button>

                                <div class="pc-module-dock">
                                    <div
                                        class="pc-module-quota"
                                        x-show="resolveInlineQuotaKey(mod) && inlineQuota(mod)"
                                        x-cloak
                                        x-on:click.stop
                                    >
                                        <div class="pc-module-quota-copy">
                                            <strong x-text="inlineQuotaLabel(mod)"></strong>
                                            <span class="pc-quota-sub">
                                                <span x-text="quotaIncluded(inlineQuota(mod)) + ' ' + labels.included"></span>
                                            </span>
                                            <span class="pc-price-row pc-quota-price">
                                                <span
                                                    class="pc-price-was"
                                                    x-show="isYearlyDeal()"
                                                    x-text="format(inlineQuota(mod)?.price_per_extra_month)"
                                                ></span>
                                                <span
                                                    :class="isYearlyDeal() ? 'pc-price-now' : ''"
                                                    x-text="isYearlyDeal()
                                                        ? formatDeal(effectiveMonthly(inlineQuota(mod)?.price_per_extra_month))
                                                        : format(inlineQuota(mod)?.price_per_extra_month)"
                                                ></span>
                                                <small>/ +1</small>
                                            </span>
                                        </div>
                                        <div
                                            class="pc-stepper"
                                            :class="{ 'is-capped': isSelected(mod.key) && isQuotaAtMax(resolveInlineQuotaKey(mod)) }"
                                        >
                                            <button type="button" x-on:click="bumpInline(mod, -1)">−</button>
                                            <span x-text="quotaValue(resolveInlineQuotaKey(mod))"></span>
                                            <button
                                                type="button"
                                                x-on:click="bumpInline(mod, 1)"
                                                :disabled="isSelected(mod.key) && isQuotaAtMax(resolveInlineQuotaKey(mod))"
                                            >+</button>
                                        </div>
                                    </div>

                                    <p
                                        class="pc-module-quota-cap"
                                        x-show="isSelected(mod.key) && resolveInlineQuotaKey(mod) && quotaCapKey === resolveInlineQuotaKey(mod)"
                                        x-text="labels.quota_cap_hint"
                                        x-transition
                                    ></p>

                                    <div class="pc-module-foot">
                                        <button
                                            type="button"
                                            class="pc-module-select"
                                            :aria-pressed="isSelected(mod.key)"
                                            x-on:click="toggle(mod.key)"
                                        >
                                            <span
                                                class="pc-check"
                                                aria-hidden="true"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <span x-text="isSelected(mod.key) ? labels.module_selected : labels.module_select"></span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </template>

            <div class="pc-quotas" x-show="visibleQuotas.length">
                <h4 x-text="labels.quotas_title"></h4>
                <template x-for="quota in visibleQuotas" :key="quota.key">
                    <div class="pc-quota-row">
                        <div>
                            <div style="font-weight:800;color:var(--pc-ink)" x-text="quota.name"></div>
                            <div class="pc-quota-meta">
                                <span x-text="quotaIncluded(quota) + ' ' + labels.included"></span>
                                ·
                                <span class="pc-price-row pc-quota-price">
                                    <span
                                        class="pc-price-was"
                                        x-show="isYearlyDeal()"
                                        x-text="format(quota.price_per_extra_month)"
                                    ></span>
                                    <span
                                        :class="isYearlyDeal() ? 'pc-price-now' : ''"
                                        x-text="isYearlyDeal()
                                            ? formatDeal(effectiveMonthly(quota.price_per_extra_month))
                                            : format(quota.price_per_extra_month)"
                                    ></span>
                                    <small>/ +1</small>
                                </span>
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
                <div class="pc-eq-line" x-show="isYearlyDeal() && monthlyTotal > 0">
                    <span class="pc-price-row">
                        <span class="pc-price-was" x-text="format(monthlyTotal)"></span>
                        <span class="pc-price-now" x-text="formatDeal(effectiveMonthly(monthlyTotal))"></span>
                        <small x-text="' / ' + labels.per_month"></small>
                    </span>
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

                <p class="pc-invoice-note" x-show="period === 'Month'" x-text="labels.modules_hint"></p>
                <div class="pc-empty" x-show="modules.length === 0" x-text="labels.select_at_least"></div>
                <div class="pc-empty" x-show="modules.length > 0 && softDependencyBroken" x-text="labels.reports_needs_data"></div>
            </div>
        </aside>
    </div>
</div>

@include('partials.plan-configurator-script')
