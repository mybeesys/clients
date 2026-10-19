@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $periodLabel = in_array($payment->billing_period, ['Year', 'yearly'], true) ? __('general.annual') : __('general.monthly');
    $planName = $payment->plan?->name
        ?? (is_array($payment->quote) ? (string) data_get($payment->quote, 'line_items.0.label', 'My Bee') : 'My Bee');
    $currency = strtoupper((string) ($quote['currency'] ?? $payment->currency ?? 'SAR'));
    $subtotal = (float) ($quote['subtotal_ex_tax'] ?? $payment->amount);
    $taxAmount = (float) ($quote['tax_amount'] ?? 0);
    $taxPercent = (float) ($quote['tax_percent'] ?? 0);
    $total = (float) ($quote['total_inc_tax'] ?? $payment->amount);
    $money = fn (float $value) => number_format($value, 2);
    $brandList = array_values(array_filter(preg_split('/\s+/', strtoupper((string) $brands)) ?: []));
    $hasMada = in_array('MADA', $brandList, true);
    $hasVisa = in_array('VISA', $brandList, true);
    $hasMaster = in_array('MASTER', $brandList, true);
    $hasApple = in_array('APPLEPAY', $brandList, true);
    $defaultBrand = $hasMada ? 'MADA' : ($hasVisa ? 'VISA' : ($hasMaster ? 'MASTER' : 'APPLEPAY'));
    $initialName = $cardholder !== '' ? $cardholder : __('fields.hyperpay_card_holder_placeholder');
    $extraJs = $config->extraWidgetJs();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('fields.hyperpay_checkout_title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:500,600,700,800|dm-sans:500,600,700" rel="stylesheet">
    <style>
        :root {
            --bee: #EBB81E;
            --bee-deep: #B88912;
            --ink: #1A1A1A;
            --muted: #6B7280;
            --line: rgba(26, 26, 26, 0.09);
            --surface: #FFFDF8;
            --soft: #FAF8F2;
            --ok: #15803D;
            --shadow: 0 18px 40px rgba(26, 26, 26, 0.07);
            --radius: 1.25rem;
            --font: "{{ $isRtl ? 'Cairo' : 'DM Sans' }}", system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            min-height: 100vh;
            font-family: var(--font);
            color: var(--ink);
            background: var(--soft);
        }
        .hp-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            height: 64px;
            padding: 0 1.25rem;
            background: color-mix(in srgb, var(--surface) 88%, white);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .hp-bar img { height: 32px; display: block; }
        .hp-bar__secure {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--ok);
        }
        .hp-bar__secure i {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 50%;
            background: var(--ok);
            box-shadow: 0 0 0 4px rgba(21, 128, 61, 0.14);
        }
        .hp-cancel {
            appearance: none;
            background: var(--soft);
            border: 1px solid var(--line);
            color: var(--ink);
            border-radius: 999px;
            padding: 0.45rem 0.95rem;
            font: inherit;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }
        .hp-cancel:hover { border-color: var(--bee); background: rgba(235, 184, 30, 0.14); }
        .hp-wrap { width: min(1040px, calc(100% - 2rem)); margin: 1.25rem auto 2rem; }
        .hp-steps {
            display: flex;
            justify-content: center;
            gap: 0.4rem;
            margin: 0 0 1rem;
            flex-wrap: wrap;
        }
        .hp-steps span {
            font-size: 0.7rem;
            color: var(--muted);
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--surface);
            font-weight: 600;
        }
        .hp-steps span.is-on {
            color: #111;
            background: linear-gradient(135deg, #F0C43A, var(--bee));
            border-color: transparent;
            font-weight: 800;
        }
        .hp-shell {
            display: grid;
            grid-template-columns: minmax(260px, 340px) minmax(0, 1fr);
            gap: 1.1rem;
            align-items: start;
        }
        [dir="rtl"] .hp-shell { direction: rtl; }
        [dir="ltr"] .hp-shell { direction: ltr; }
        .hp-summary, .hp-pay {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .hp-summary {
            padding: 1.25rem 1.2rem 1.15rem;
            position: sticky;
            top: 5rem;
        }
        .hp-summary__kicker {
            margin: 0 0 0.55rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: var(--bee-deep);
        }
        .hp-summary h1 {
            position: relative;
            margin: 0;
            font-size: 1.15rem;
            line-height: 1.4;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .hp-summary__period {
            position: relative;
            margin: 0.35rem 0 1.1rem;
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.45;
        }
        .hp-lines { display: grid; gap: 0.55rem; }
        .hp-line {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.84rem;
            color: var(--muted);
            font-weight: 600;
        }
        .hp-line strong { color: var(--ink); font-weight: 800; font-variant-numeric: tabular-nums; }
        .hp-total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
            margin-top: 0.95rem;
            padding-top: 0.85rem;
            border-top: 1px dashed rgba(184, 137, 18, 0.35);
        }
        .hp-total span { color: var(--muted); font-size: 0.84rem; font-weight: 700; }
        .hp-total b {
            font-size: 1.45rem;
            color: var(--bee-deep);
            font-weight: 800;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }
        .hp-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 1.1rem;
        }
        .hp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--muted);
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.35rem 0.65rem;
        }
        .hp-badge svg { width: 0.85rem; height: 0.85rem; color: var(--bee-deep); flex: none; }
        .hp-pay { padding: 1.25rem 1.2rem 1.1rem; min-width: 0; }
        .hp-pay h2 {
            margin: 0 0 0.2rem;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .hp-pay > .hp-lead {
            margin: 0 0 1rem;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.45;
        }
        .hp-tabs { display: flex; gap: 0.45rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .hp-tab {
            appearance: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            padding: 0.5rem 0.8rem;
            border: 1px solid var(--line);
            background: var(--soft);
            color: var(--ink);
            font: inherit;
            font-size: 0.75rem;
            font-weight: 800;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .hp-tab.is-on {
            border-color: rgba(235, 184, 30, 0.65);
            background: rgba(235, 184, 30, 0.14);
            box-shadow: 0 0 0 3px rgba(235, 184, 30, 0.12);
        }
        .hp-tab svg { height: 1rem; width: auto; display: block; }

        .hp-stage {
            perspective: 1100px;
            margin-bottom: 1rem;
        }
        .hp-flip {
            position: relative;
            height: 11.5rem;
            transform-style: preserve-3d;
            transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .hp-flip.is-back { transform: rotateY(180deg); }
        [dir="rtl"] .hp-flip.is-back { transform: rotateY(-180deg); }
        .hp-face {
            position: absolute;
            inset: 0;
            border-radius: 1.05rem;
            padding: 1.05rem 1.15rem;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            overflow: hidden;
            color: #fff;
            box-shadow: 0 14px 32px rgba(26, 26, 26, 0.14);
        }
        .hp-face::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, rgba(255,255,255,.2), transparent 45%, rgba(255,255,255,.05));
            pointer-events: none;
        }
        .hp-face--back { transform: rotateY(180deg); }
        [dir="rtl"] .hp-face--back { transform: rotateY(-180deg); }
        .hp-face[data-brand="MADA"] { background: linear-gradient(135deg, #0B6B4F 0%, #0F8A66 55%, #0A5C44 100%); }
        .hp-face[data-brand="VISA"] { background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 55%, #1E293B 100%); }
        .hp-face[data-brand="MASTER"] { background: linear-gradient(135deg, #1F2937 0%, #374151 45%, #92400E 100%); }
        .hp-face[data-brand="APPLEPAY"] { background: linear-gradient(135deg, #18181B 0%, #3F3F46 100%); }
        .hp-face__top, .hp-face__bottom {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .hp-chip {
            width: 2.5rem;
            height: 1.85rem;
            border-radius: 0.35rem;
            background: linear-gradient(180deg, #FDE68A, #D97706);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
        }
        .hp-face__logo { font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; opacity: 0.95; }
        .hp-pan {
            position: relative;
            z-index: 1;
            margin: 1.5rem 0 1.1rem;
            font-family: "DM Sans", ui-monospace, monospace;
            font-size: 1.2rem;
            letter-spacing: 0.18em;
            direction: ltr;
            unicode-bidi: isolate;
            text-align: start;
            font-variant-numeric: tabular-nums;
        }
        .hp-meta { font-size: 0.65rem; color: rgba(255,255,255,.72); font-weight: 600; }
        .hp-meta b {
            display: block;
            margin-top: 0.2rem;
            color: #fff;
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            font-weight: 700;
        }
        .hp-meta b[dir="ltr"] {
            direction: ltr;
            unicode-bidi: isolate;
            text-align: start;
            letter-spacing: 0.1em;
            font-variant-numeric: tabular-nums;
        }
        .hp-magstripe {
            position: relative;
            z-index: 1;
            margin: 1.1rem -1.15rem 1rem;
            height: 2.4rem;
            background: #111;
        }
        .hp-cvv-panel {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            background: #F8FAFC;
            color: #111;
            border-radius: 0.4rem;
            padding: 0.55rem 0.7rem;
            margin-inline-start: auto;
            width: min(9.5rem, 55%);
        }
        .hp-cvv-panel span {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .hp-cvv-panel b {
            font-family: "DM Sans", ui-monospace, monospace;
            font-size: 1rem;
            letter-spacing: 0.22em;
            direction: ltr;
            unicode-bidi: isolate;
        }
        .hp-widget {
            min-height: 12rem;
            direction: ltr;
            unicode-bidi: isolate;
            text-align: left;
        }
        .hp-note {
            margin: 0.85rem 0 0;
            font-size: 0.75rem;
            line-height: 1.45;
            color: var(--bee-deep);
            background: rgba(235, 184, 30, 0.1);
            border: 1px solid rgba(235, 184, 30, 0.22);
            border-radius: 0.75rem;
            padding: 0.65rem 0.8rem;
            font-weight: 600;
        }
        .wpwl-container { max-width: 100% !important; margin: 0 !important; direction: ltr !important; }
        .wpwl-form {
            max-width: 100% !important; margin: 0 !important; padding: 0 !important;
            background: transparent !important; border: 0 !important; box-shadow: none !important;
            direction: ltr !important;
        }
        .wpwl-group { margin-bottom: 0.75rem !important; direction: ltr !important; }
        .wpwl-label {
            color: var(--ink) !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            margin-bottom: 0.35rem !important;
            opacity: 0.78;
        }
        html[dir="rtl"] .wpwl-label {
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: isolate;
        }
        .wpwl-control, .wpwl-control-iframe, input.wpwl-control {
            min-height: 2.85rem !important;
            border-radius: 0.8rem !important;
            border: 1px solid var(--line) !important;
            background: #fff !important;
            color: var(--ink) !important;
            direction: ltr !important;
            unicode-bidi: isolate !important;
            text-align: left !important;
            box-shadow: none !important;
        }
        .wpwl-control:focus, .wpwl-control-iframe:focus {
            outline: none !important;
            border-color: var(--bee) !important;
            box-shadow: 0 0 0 3px rgba(235, 184, 30, 0.22) !important;
        }
        .wpwl-button {
            width: 100% !important;
            min-height: 3.05rem !important;
            margin-top: 0.35rem !important;
            border: 0 !important;
            border-radius: 0.9rem !important;
            background: linear-gradient(135deg, #F0C43A, var(--bee) 45%, var(--bee-deep)) !important;
            color: #111 !important;
            font-weight: 800 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 12px 28px rgba(184, 137, 18, 0.28) !important;
            letter-spacing: 0 !important;
        }
        html[dir="rtl"] .wpwl-button { direction: rtl !important; }
        .wpwl-form > .wpwl-group-brand,
        .wpwl-wrapper-brand {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            clip: rect(0 0 0 0) !important;
        }
        html.hp-busy .wpwl-button { pointer-events: none; opacity: 0.78; }
        html.hp-busy .wpwl-button::after {
            content: "";
            display: inline-block;
            width: 0.85rem; height: 0.85rem;
            margin-inline-start: 0.45rem;
            border: 2px solid #111; border-right-color: transparent; border-radius: 50%;
            vertical-align: -2px;
            animation: hp-spin .7s linear infinite;
        }
        @keyframes hp-spin { to { transform: rotate(360deg); } }
        .hp-modal {
            display: none; position: fixed; inset: 0; z-index: 50;
            background: rgba(26, 26, 26, 0.35); place-items: center; padding: 1rem;
            backdrop-filter: blur(4px);
        }
        .hp-modal.is-open { display: grid; }
        .hp-modal__box {
            width: min(26rem, 100%);
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 1.1rem;
            padding: 1.25rem;
            box-shadow: var(--shadow);
        }
        .hp-modal__box p { margin: 0 0 1rem; color: var(--ink); line-height: 1.55; font-weight: 600; }
        .hp-modal__actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .hp-modal__actions a, .hp-modal__actions button {
            flex: 1; min-width: 8rem; text-align: center; text-decoration: none;
            border-radius: 0.8rem; padding: 0.7rem 0.85rem; font: inherit; font-weight: 800; cursor: pointer;
        }
        .hp-modal__stay { background: linear-gradient(135deg, #F0C43A, var(--bee)); color: #111; border: 0; }
        .hp-modal__leave { background: var(--soft); color: var(--ink); border: 1px solid var(--line); }
        @media (max-width: 860px) {
            .hp-shell { grid-template-columns: 1fr; }
            .hp-summary { position: static; }
            .hp-flip { height: 10.5rem; }
            .hp-wrap { width: calc(100% - 1.25rem); margin-top: 0.85rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .hp-flip { transition: none; }
        }
    </style>
    <script>
        var wpwlOptions = {
            paymentTarget: "_top",
            locale: @json($config->widgetLocale()),
            style: "plain",
            brandDetection: true,
            numberFormatting: true,
            labels: {
                cardHolder: @json(__('fields.hyperpay_label_card_holder')),
                cardNumber: @json(__('fields.hyperpay_label_card_number')),
                expiryDate: @json(__('fields.hyperpay_label_expiry')),
                cvv: @json(__('fields.hyperpay_label_cvv'))
            },
            iframeStyles: {
                "card-number-placeholder": {
                    "color": "#9CA3AF",
                    "font-size": "16px",
                    "font-family": "DM Sans,monospace",
                    "direction": "ltr",
                    "text-align": "left"
                },
                "card-number-input": {
                    "color": "#1A1A1A",
                    "font-size": "16px",
                    "font-family": "DM Sans,monospace",
                    "direction": "ltr",
                    "text-align": "left",
                    "letter-spacing": "0.12em"
                },
                "cvv-placeholder": {
                    "color": "#9CA3AF",
                    "font-size": "16px",
                    "font-family": "DM Sans,monospace",
                    "direction": "ltr",
                    "text-align": "left"
                },
                "cvv-input": {
                    "color": "#1A1A1A",
                    "font-size": "16px",
                    "direction": "ltr",
                    "text-align": "left"
                }
            },
            onReady: function () {
                var button = document.querySelector(".wpwl-button");
                if (button) {
                    button.textContent = @json(__('fields.hyperpay_checkout_pay'));
                }
                if (window.MyBeePay) {
                    window.MyBeePay.bindWidget();
                } else {
                    window.MyBeePayQueue = window.MyBeePayQueue || [];
                    window.MyBeePayQueue.push(["bindWidget"]);
                }
            },
            onChangeBrand: function (brand) {
                if (window.MyBeePay) {
                    window.MyBeePay.setBrand(brand);
                } else {
                    window.MyBeePayQueue = window.MyBeePayQueue || [];
                    window.MyBeePayQueue.push(["setBrand", brand]);
                }
            },
            onBeforeSubmitCard: function () {
                var holderEl = document.querySelector(".wpwl-control-cardHolder, input[name='card.holder']");
                var holder = holderEl && holderEl.value ? String(holderEl.value).trim() : "";
                if (!holder || /[^\u0000-\u007F]/.test(holder) || !/[A-Za-z]/.test(holder)) {
                    alert(@json(__('fields.hyperpay_card_holder_latin')));
                    document.documentElement.classList.remove("hp-busy");
                    return false;
                }
                document.documentElement.classList.add("hp-busy");
                return true;
            }
        };
    </script>
    <script
        src="{{ $widgetUrl }}"
        @if (filled($payment->integrity)) integrity="{{ $payment->integrity }}" crossorigin="anonymous" @endif
    ></script>
    <script>
        // If checkout opened inside the tenant iframe, break out immediately.
        if (window.top && window.top !== window.self) {
            window.top.location.replace(window.location.href);
        }
    </script>
</head>
<body>
    <header class="hp-bar">
        <img src="{{ asset('images/brand/mybee-lockup.png') }}" alt="My Bee" onerror="this.src='{{ asset('images/brand/mybee-mark.png') }}'">
        <span class="hp-bar__secure"><i></i>{{ __('fields.hyperpay_checkout_secure') }}</span>
        <button type="button" class="hp-cancel" data-open-cancel>{{ __('fields.hyperpay_checkout_cancel') }}</button>
    </header>

    <main class="hp-wrap">
        @if (! empty($isRegistration))
            <div class="hp-steps">
                <span>{{ __('main.wizard.plan_information') }}</span>
                <span class="is-on">{{ __('main.wizard.payment_information') }}</span>
                <span>{{ __('main.wizard.company_information') }}</span>
            </div>
        @elseif (! empty($isSubscription))
            <div class="hp-steps">
                <span>{{ __('main.subscribe.title') }}</span>
                <span class="is-on">{{ __('main.wizard.payment_information') }}</span>
            </div>
        @endif

        <div class="hp-shell">
            <aside class="hp-summary">
                <p class="hp-summary__kicker">{{ __('fields.hyperpay_order_title') }}</p>
                <h1>{{ $planName }}</h1>
                <p class="hp-summary__period">{{ __('fields.hyperpay_plan_line', ['plan' => $planName, 'period' => $periodLabel]) }}</p>

                <div class="hp-lines">
                    <div class="hp-line">
                        <span>{{ __('fields.subscription_subtotal_ex_tax') }}</span>
                        <strong>{{ $currency }} {{ $money($subtotal) }}</strong>
                    </div>
                    @if (! empty($quote['discount_amount']) && (float) $quote['discount_amount'] > 0)
                        <div class="hp-line">
                            <span>{{ __('fields.discount') }}</span>
                            <strong>− {{ $currency }} {{ $money((float) $quote['discount_amount']) }}</strong>
                        </div>
                    @endif
                    @if ($taxAmount > 0)
                        <div class="hp-line">
                            <span>{{ __('fields.subscription_tax_amount', ['vat' => rtrim(rtrim(number_format($taxPercent, 2, '.', ''), '0'), '.')]) }}</span>
                            <strong>{{ $currency }} {{ $money($taxAmount) }}</strong>
                        </div>
                    @endif
                </div>

                <div class="hp-total">
                    <span>{{ __('fields.total') }}</span>
                    <b>{{ $currency }} {{ $money($total) }}</b>
                </div>

                <div class="hp-badges">
                    <div class="hp-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4v6c0 5-3.4 8.4-8 9-4.6-.6-8-4-8-9V7z"/><path d="M9 12l2 2 4-4"/></svg>
                        {{ __('fields.hyperpay_checkout_ssl') }}
                    </div>
                    <div class="hp-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M7 7V5a5 5 0 0 1 10 0v2"/></svg>
                        {{ __('fields.hyperpay_checkout_pci') }}
                    </div>
                    <div class="hp-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg>
                        {{ __('fields.hyperpay_checkout_hp_secured') }}
                    </div>
                </div>
            </aside>

            <section class="hp-pay">
                <h2>{{ __('fields.hyperpay_checkout_title') }}</h2>
                <p class="hp-lead">{{ __('fields.hyperpay_checkout_subtitle', ['plan' => $planName]) }}</p>

                <div class="hp-tabs" role="tablist">
                    @if ($hasMada)
                        <button type="button" class="hp-tab {{ $defaultBrand === 'MADA' ? 'is-on' : '' }}" data-brand="MADA" aria-selected="{{ $defaultBrand === 'MADA' ? 'true' : 'false' }}">
                            <svg viewBox="0 0 64 18" width="42" height="14" aria-hidden="true">
                                <rect width="64" height="18" rx="3" fill="#00A651"/>
                                <text x="32" y="13" text-anchor="middle" fill="#fff" font-size="10" font-family="DM Sans,Arial" font-weight="800">mada</text>
                            </svg>
                            {{ __('fields.hyperpay_checkout_tab_mada') }}
                        </button>
                    @endif
                    @if ($hasVisa)
                        <button type="button" class="hp-tab {{ $defaultBrand === 'VISA' ? 'is-on' : '' }}" data-brand="VISA" aria-selected="{{ $defaultBrand === 'VISA' ? 'true' : 'false' }}">
                            <svg viewBox="0 0 40 18" width="26" height="14" aria-hidden="true">
                                <rect width="40" height="18" rx="3" fill="#1A1F71"/>
                                <text x="20" y="13" text-anchor="middle" fill="#fff" font-size="8" font-family="DM Sans,Arial" font-weight="800">VISA</text>
                            </svg>
                            {{ __('fields.hyperpay_checkout_tab_visa') }}
                        </button>
                    @endif
                    @if ($hasMaster)
                        <button type="button" class="hp-tab {{ $defaultBrand === 'MASTER' ? 'is-on' : '' }}" data-brand="MASTER" aria-selected="{{ $defaultBrand === 'MASTER' ? 'true' : 'false' }}">
                            <svg viewBox="0 0 28 18" width="20" height="14" aria-hidden="true">
                                <circle cx="11" cy="9" r="7" fill="#EB001B"/>
                                <circle cx="17" cy="9" r="7" fill="#F79E1B"/>
                            </svg>
                            {{ __('fields.hyperpay_checkout_tab_master') }}
                        </button>
                    @endif
                    @if ($hasApple)
                        <button type="button" class="hp-tab {{ $defaultBrand === 'APPLEPAY' ? 'is-on' : '' }}" data-brand="APPLEPAY" aria-selected="{{ $defaultBrand === 'APPLEPAY' ? 'true' : 'false' }}">
                            {{ __('fields.hyperpay_checkout_tab_apple') }}
                        </button>
                    @endif
                </div>

                <div class="hp-stage">
                    <div class="hp-flip" id="hp-flip">
                        <div class="hp-face hp-face--front" data-brand="{{ $defaultBrand }}" id="hp-cardface">
                            <div class="hp-face__top">
                                <span class="hp-chip" aria-hidden="true"></span>
                                <span class="hp-face__logo" id="hp-card-logo">{{ $defaultBrand === 'MADA' ? 'mada' : ($defaultBrand === 'MASTER' ? 'mastercard' : $defaultBrand) }}</span>
                            </div>
                            <div class="hp-pan" id="hp-card-pan" dir="ltr">•••• •••• •••• ••••</div>
                            <div class="hp-face__bottom">
                                <div class="hp-meta">{{ __('fields.hyperpay_label_card_holder') }}<b id="hp-card-name">{{ $initialName }}</b></div>
                                <div class="hp-meta">{{ __('fields.hyperpay_label_expiry') }}<b id="hp-card-exp" dir="ltr">MM/YY</b></div>
                            </div>
                        </div>
                        <div class="hp-face hp-face--back" data-brand="{{ $defaultBrand }}" id="hp-cardback">
                            <div class="hp-magstripe" aria-hidden="true"></div>
                            <div class="hp-cvv-panel">
                                <span>{{ __('fields.hyperpay_card_cvv_hint') }}</span>
                                <b id="hp-card-cvv" dir="ltr">•••</b>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hp-widget">
                    <form action="{{ $resultUrl }}" class="paymentWidgets" data-brands="{{ $brands }}"></form>
                </div>

                <p class="hp-note">{{ __('fields.hyperpay_checkout_note') }}</p>
            </section>
        </div>
    </main>

    <div class="hp-modal" id="hp-cancel-modal" role="dialog" aria-modal="true">
        <div class="hp-modal__box">
            <p>{{ __('fields.hyperpay_checkout_cancel_confirm') }}</p>
            <div class="hp-modal__actions">
                <button type="button" class="hp-modal__stay" data-close-cancel>{{ __('fields.hyperpay_checkout_stay') }}</button>
                <a class="hp-modal__leave" href="{{ $cancelUrl }}">{{ __('fields.hyperpay_checkout_cancel') }}</a>
            </div>
        </div>
    </div>

    @if ($extraJs !== '' && ! str_contains($extraJs, 'wpwlOptions'))
        {!! $extraJs !!}
    @endif

    <script>
        window.MyBeePay = (function () {
            var face = document.getElementById("hp-cardface");
            var back = document.getElementById("hp-cardback");
            var flip = document.getElementById("hp-flip");
            var panEl = document.getElementById("hp-card-pan");
            var nameEl = document.getElementById("hp-card-name");
            var expEl = document.getElementById("hp-card-exp");
            var cvvEl = document.getElementById("hp-card-cvv");
            var logoEl = document.getElementById("hp-card-logo");
            var placeholderName = @json($initialName);
            var placeholderHolder = @json(__('fields.hyperpay_card_holder_placeholder'));
            var logos = { MADA: "mada", VISA: "VISA", MASTER: "mastercard", APPLEPAY: "Apple Pay" };
            var bound = false;
            var state = { pan: "", name: "", exp: "", expMonth: "", expYear: "", cvv: "" };

            function brandFromValue(value) {
                if (!value) return null;
                if (typeof value === "object") {
                    value = value.brand || value.name || value.code || "";
                }
                value = String(value).toUpperCase();
                if (value.indexOf("MADA") !== -1) return "MADA";
                if (value.indexOf("VISA") !== -1) return "VISA";
                if (value.indexOf("MASTER") !== -1) return "MASTER";
                if (value.indexOf("APPLE") !== -1) return "APPLEPAY";
                return null;
            }

            function detectBrandFromBin(digits) {
                if (!digits || digits.length < 2) return null;
                if (/^(5[1-5]|2[2-7])/.test(digits)) return "MASTER";
                if (digits.charAt(0) === "4") return null;
                return null;
            }

            function setBrand(brand) {
                brand = brandFromValue(brand);
                if (!brand) return;
                face.setAttribute("data-brand", brand);
                back.setAttribute("data-brand", brand);
                logoEl.textContent = logos[brand] || brand;
                document.querySelectorAll(".hp-tab").forEach(function (tab) {
                    var on = tab.getAttribute("data-brand") === brand;
                    tab.classList.toggle("is-on", on);
                    tab.setAttribute("aria-selected", on ? "true" : "false");
                });
            }

            function maskPan(raw) {
                var digits = String(raw || "").replace(/\D/g, "").slice(0, 19);
                var first = "••••";
                var last = "••••";
                if (digits.length) {
                    first = (digits.slice(0, 4) + "••••").slice(0, 4);
                }
                if (digits.length >= 13) {
                    last = digits.length > 16 ? digits.slice(-4) : (digits.slice(12, 16) + "••••").slice(0, 4);
                }
                return first + " •••• •••• " + last;
            }

            function maskCvv(raw) {
                var digits = String(raw || "").replace(/\D/g, "").slice(0, 4);
                if (!digits) return "•••";
                return digits.replace(/./g, "•");
            }

            function formatExpiry() {
                var month = String(state.expMonth || "").replace(/\D/g, "");
                var year = String(state.expYear || "").replace(/\D/g, "");
                var combined = String(state.exp || "");

                if (combined) {
                    var parts = combined.split(/[\s\/\-]+/).filter(Boolean);
                    if (parts.length >= 2) {
                        if (!month) month = String(parts[0]).replace(/\D/g, "");
                        if (!year) year = String(parts[1]).replace(/\D/g, "");
                    } else {
                        var digits = combined.replace(/\D/g, "");
                        if (digits.length === 4 && parseInt(digits, 10) >= 2000) {
                            if (!year) year = digits;
                        } else if (digits.length >= 3 && parseInt(digits.slice(0, 2), 10) >= 1 && parseInt(digits.slice(0, 2), 10) <= 12) {
                            if (!month) month = digits.slice(0, 2);
                            if (!year) year = digits.slice(2);
                        } else if (digits.length && !month) {
                            month = digits.slice(0, 2);
                        }
                    }
                }

                if (year.length >= 4) year = year.slice(-2);
                year = year.slice(0, 2);

                var monthNum = parseInt(month.slice(0, 2), 10);
                var mmOut = "MM";
                if (monthNum >= 1 && monthNum <= 12) {
                    mmOut = (monthNum < 10 ? "0" : "") + monthNum;
                }

                var yyOut = year ? (year + "YY").slice(0, 2) : "YY";
                if (mmOut === "MM" && yyOut === "YY") return "MM/YY";
                return mmOut + "/" + yyOut;
            }

            function paint() {
                panEl.textContent = maskPan(state.pan);
                nameEl.textContent = state.name || placeholderName || placeholderHolder;
                expEl.textContent = formatExpiry();
                cvvEl.textContent = maskCvv(state.cvv);
                var detected = detectBrandFromBin(String(state.pan).replace(/\D/g, ""));
                if (detected) setBrand(detected);
            }

            function showBack(on) {
                flip.classList.toggle("is-back", !!on);
            }

            function classify(el) {
                if (!el) return "";
                var hay = (
                    (el.className && el.className.toString ? el.className.toString() : "") +
                    " " + (el.name || "") + " " + (el.id || "") +
                    " " + (el.getAttribute("data-action") || "")
                ).toLowerCase();
                if (hay.indexOf("cardnumber") !== -1 || hay.indexOf("card.number") !== -1 || hay.indexOf("card-number") !== -1) {
                    return "pan";
                }
                if (hay.indexOf("cardholder") !== -1 || hay.indexOf("card.holder") !== -1) {
                    return "name";
                }
                if (hay.indexOf("cvv") !== -1 || hay.indexOf("cvc") !== -1 || hay.indexOf("securitycode") !== -1) {
                    return "cvv";
                }
                if (hay.indexOf("expirymonth") !== -1 || hay.indexOf("expiry-month") !== -1 || hay.indexOf("card.expirymonth") !== -1) {
                    return "expMonth";
                }
                if (hay.indexOf("expiryyear") !== -1 || hay.indexOf("expiry-year") !== -1 || hay.indexOf("card.expiryyear") !== -1) {
                    return "expYear";
                }
                if (hay.indexOf("expiry") !== -1 || hay.indexOf("expir") !== -1) {
                    return "exp";
                }
                return "";
            }

            function readNode(el) {
                if (!el) return "";
                if (el.tagName === "IFRAME") {
                    try {
                        var doc = el.contentDocument || (el.contentWindow && el.contentWindow.document);
                        var inner = doc && doc.querySelector("input, [contenteditable='true']");
                        if (inner) return String(inner.value || inner.textContent || "");
                    } catch (err) {}
                    return "";
                }
                return String(el.value || "");
            }

            function assignExpiry(kind, value) {
                var digits = String(value || "").replace(/\D/g, "");
                if (kind === "expMonth") {
                    state.expMonth = value;
                    return;
                }
                if (kind === "expYear") {
                    state.expYear = value;
                    return;
                }
                if (kind !== "exp" || !value) return;
                if (/^20\d{2}$/.test(digits)) {
                    state.expYear = digits;
                    return;
                }
                state.exp = value;
                if (value.indexOf("/") !== -1 || value.indexOf("-") !== -1) {
                    var parts = String(value).split(/[\s\/\-]+/).filter(Boolean);
                    if (parts[0]) state.expMonth = parts[0];
                    if (parts[1]) state.expYear = parts[1];
                }
            }

            function applyField(el) {
                var kind = classify(el);
                var value = readNode(el);
                if (!kind || value === "") {
                    var group = el.closest && el.closest(".wpwl-group");
                    if (group) {
                        if (group.className.indexOf("cardNumber") !== -1) kind = "pan";
                        else if (group.className.indexOf("cardHolder") !== -1) kind = "name";
                        else if (group.className.indexOf("cvv") !== -1) kind = "cvv";
                        else if (group.className.indexOf("expiry") !== -1) kind = "exp";
                    }
                }
                if (kind === "pan") state.pan = value || state.pan;
                if (kind === "name" && value) state.name = value;
                if (kind === "cvv") state.cvv = value || state.cvv;
                assignExpiry(kind, value);
                if (kind === "cvv") showBack(true);
                else if (kind === "pan" || kind === "name" || kind === "exp" || kind === "expMonth" || kind === "expYear") showBack(false);
                if (kind) paint();
            }

            function fromEvent(e) {
                var el = e.target;
                if (!el) return;
                if (el.tagName === "INPUT" || el.tagName === "SELECT" || el.isContentEditable) {
                    applyField(el);
                }
            }

            function onFocus(e) {
                var el = e.target;
                var kind = classify(el);
                if (!kind && el.closest) {
                    var group = el.closest(".wpwl-group");
                    if (group && group.className.indexOf("cvv") !== -1) kind = "cvv";
                    if (group && (group.className.indexOf("cardNumber") !== -1 || group.className.indexOf("cardHolder") !== -1 || group.className.indexOf("expiry") !== -1)) {
                        kind = "front";
                    }
                }
                if (kind === "cvv") showBack(true);
                else if (kind) showBack(false);
            }

            function scanWidget() {
                var root = document.querySelector(".wpwl-form") || document.querySelector(".hp-widget");
                if (!root) return;
                root.querySelectorAll("input, select, iframe").forEach(function (el) {
                    var value = readNode(el);
                    if (!value) return;
                    var kind = classify(el);
                    if (!kind && el.closest) {
                        var group = el.closest(".wpwl-group");
                        if (group && group.className.indexOf("cardNumber") !== -1) kind = "pan";
                        if (group && group.className.indexOf("cardHolder") !== -1) kind = "name";
                        if (group && group.className.indexOf("cvv") !== -1) kind = "cvv";
                        if (group && group.className.indexOf("expiry") !== -1) kind = "exp";
                    }
                    if (kind === "pan") state.pan = value;
                    if (kind === "name") state.name = value;
                    if (kind === "cvv") state.cvv = value;
                    assignExpiry(kind, value);
                });
                paint();
            }

            function clickWidgetBrand(brand) {
                var variants = [
                    ".wpwl-brand-" + brand,
                    ".wpwl-brand-" + brand.toLowerCase(),
                    '[data-action="' + brand + '"]'
                ];
                for (var i = 0; i < variants.length; i++) {
                    var el = document.querySelector(variants[i]);
                    if (el) { el.click(); return; }
                }
            }

            function bindWidget() {
                if (!bound) {
                    bound = true;
                    ["input", "keyup", "change", "paste"].forEach(function (evt) {
                        document.addEventListener(evt, fromEvent, true);
                    });
                    document.addEventListener("focusin", onFocus, true);
                    window.addEventListener("message", function (event) {
                        if (!event.origin || event.origin.indexOf("oppwa.com") === -1) return;
                        var data = event.data;
                        if (typeof data === "string") {
                            try { data = JSON.parse(data); } catch (err) { return; }
                        }
                        if (!data || typeof data !== "object") return;
                        var pan = data.cardNumber || data.number || data.pan || data.value;
                        if (pan) {
                            state.pan = String(pan);
                            paint();
                        }
                    });
                    setInterval(scanWidget, 250);
                }
                scanWidget();
            }

            document.querySelectorAll(".hp-tab").forEach(function (tab) {
                tab.addEventListener("click", function () {
                    var brand = tab.getAttribute("data-brand");
                    setBrand(brand);
                    clickWidgetBrand(brand);
                });
            });

            var modal = document.getElementById("hp-cancel-modal");
            document.querySelectorAll("[data-open-cancel]").forEach(function (btn) {
                btn.addEventListener("click", function () { modal.classList.add("is-open"); });
            });
            document.querySelectorAll("[data-close-cancel]").forEach(function (btn) {
                btn.addEventListener("click", function () { modal.classList.remove("is-open"); });
            });
            modal.addEventListener("click", function (e) {
                if (e.target === modal) modal.classList.remove("is-open");
            });

            bindWidget();

            (window.MyBeePayQueue || []).forEach(function (item) {
                var fn = item[0];
                if (fn === "bindWidget") bindWidget();
                if (fn === "setBrand") setBrand(item[1]);
            });
            window.MyBeePayQueue = [];

            return { setBrand: setBrand, bindWidget: bindWidget, applyField: applyField, showBack: showBack };
        })();
    </script>
</body>
</html>
