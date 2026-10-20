@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $brandList = array_values(array_filter(preg_split('/\s+/', strtoupper((string) $brands)) ?: []));
    $hasMada = in_array('MADA', $brandList, true);
    $hasVisa = in_array('VISA', $brandList, true);
    $hasMaster = in_array('MASTER', $brandList, true);
    $defaultBrand = $hasMada ? 'MADA' : ($hasVisa ? 'VISA' : ($hasMaster ? 'MASTER' : 'VISA'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('fields.hyperpay_vault_card_title') }}</title>
    <link href="https://fonts.bunny.net/css?family=cairo:500,600,700,800" rel="stylesheet">
    <style>
        :root {
            --bee: #EBB81E;
            --bee-deep: #B88912;
            --ink: #1a1a1a;
            --muted: #6b7280;
            --line: rgba(26, 26, 26, 0.12);
            --soft: #f7f4eb;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            background: #fff;
            font-family: Cairo, system-ui, sans-serif;
            color: var(--ink);
            overflow: hidden;
        }
        .wrap {
            padding: 0.35rem 0.15rem 0.15rem;
            max-width: 520px;
            margin: 0 auto;
        }
        .hp-tabs {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
        }
        .hp-tab {
            appearance: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.42rem 0.75rem;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 0.74rem;
            font-weight: 800;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .hp-tab.is-on {
            border-color: rgba(235, 184, 30, 0.7);
            background: rgba(235, 184, 30, 0.12);
            box-shadow: 0 0 0 3px rgba(235, 184, 30, 0.12);
        }
        .hp-tab svg { height: 0.95rem; width: auto; display: block; }

        .wpwl-container { max-width: 100% !important; margin: 0 !important; }
        .wpwl-form {
            max-width: 100% !important; margin: 0 !important; padding: 0 !important;
            background: transparent !important; border: 0 !important; box-shadow: none !important;
        }
        .wpwl-group {
            margin: 0 0 0.7rem !important;
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 0.28rem;
        }
        html[dir="rtl"] .wpwl-group {
            grid-template-columns: 1fr 7.2rem;
            align-items: center;
            gap: 0.65rem;
        }
        .wpwl-label {
            color: var(--ink) !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
            opacity: 0.9;
        }
        html[dir="rtl"] .wpwl-label {
            order: 2;
            text-align: right !important;
            direction: rtl !important;
            white-space: nowrap;
        }
        html[dir="rtl"] .wpwl-wrapper,
        html[dir="rtl"] .wpwl-wrapper-cardNumber,
        html[dir="rtl"] .wpwl-wrapper-expiry,
        html[dir="rtl"] .wpwl-wrapper-cardHolder,
        html[dir="rtl"] .wpwl-wrapper-cvv {
            order: 1;
            min-width: 0;
        }
        .wpwl-control, .wpwl-control-iframe, input.wpwl-control {
            min-height: 2.7rem !important;
            width: 100% !important;
            border-radius: 0.75rem !important;
            border: 1px solid var(--line) !important;
            background: #fff !important;
            color: var(--ink) !important;
            box-shadow: none !important;
            direction: ltr !important;
            text-align: left !important;
            padding-inline: 0.75rem !important;
        }
        .wpwl-control:focus, .wpwl-control-iframe:focus {
            outline: none !important;
            border-color: var(--bee) !important;
            box-shadow: 0 0 0 3px rgba(235, 184, 30, 0.2) !important;
        }
        .wpwl-form > .wpwl-group-brand,
        .wpwl-wrapper-brand,
        .wpwl-brand-card {
            position: absolute !important;
            width: 1px !important; height: 1px !important;
            overflow: hidden !important; clip: rect(0 0 0 0) !important;
        }
        .wpwl-button {
            width: 100% !important;
            min-height: 2.95rem !important;
            margin-top: 0.35rem !important;
            border: 0 !important;
            border-radius: 0.85rem !important;
            background: linear-gradient(135deg, #F0C43A, var(--bee) 50%, var(--bee-deep)) !important;
            color: #111 !important;
            font-weight: 800 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 10px 24px rgba(184, 137, 18, 0.22) !important;
        }
        html[dir="rtl"] .wpwl-button { direction: rtl !important; }
        html.hp-busy .wpwl-button { pointer-events: none; opacity: 0.78; }
    </style>
    <script>
        var wpwlOptions = {
            locale: @json($config->widgetLocale()),
            style: "plain",
            brandDetection: true,
            onReady: function () {
                var btn = document.querySelector(".wpwl-button");
                if (btn) btn.textContent = @json(__('fields.hyperpay_vault_card_submit'));
                bindBrandTabs();
                document.documentElement.classList.remove("hp-busy");
            },
            onBeforeSubmitCard: function () {
                document.documentElement.classList.add("hp-busy");
                return true;
            }
        };

        function bindBrandTabs() {
            document.querySelectorAll(".hp-tab").forEach(function (tab) {
                tab.addEventListener("click", function () {
                    var brand = tab.getAttribute("data-brand");
                    document.querySelectorAll(".hp-tab").forEach(function (t) {
                        var on = t === tab;
                        t.classList.toggle("is-on", on);
                        t.setAttribute("aria-selected", on ? "true" : "false");
                    });
                    try {
                        if (window.wpwl && typeof window.wpwl.executePayment === "function") {
                            /* brand hint only — COPYandPAY detects from PAN */
                        }
                        var brandInput = document.querySelector('input[name="paymentBrand"], select[name="paymentBrand"]');
                        if (brandInput) brandInput.value = brand;
                    } catch (e) {}
                });
            });
        }
    </script>
    <script src="{{ $widgetUrl }}"></script>
</head>
<body>
    <div class="wrap">
        <div class="hp-tabs" role="tablist" aria-label="{{ __('fields.hyperpay_checkout_title') }}">
            @if ($hasMada)
                <button type="button" class="hp-tab {{ $defaultBrand === 'MADA' ? 'is-on' : '' }}" data-brand="MADA" aria-selected="{{ $defaultBrand === 'MADA' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 64 18" width="36" height="12" aria-hidden="true">
                        <rect width="64" height="18" rx="3" fill="#00A651"/>
                        <text x="32" y="13" text-anchor="middle" fill="#fff" font-size="10" font-family="Cairo,Arial" font-weight="800">mada</text>
                    </svg>
                    {{ __('fields.hyperpay_checkout_tab_mada') }}
                </button>
            @endif
            @if ($hasVisa)
                <button type="button" class="hp-tab {{ $defaultBrand === 'VISA' ? 'is-on' : '' }}" data-brand="VISA" aria-selected="{{ $defaultBrand === 'VISA' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 40 18" width="24" height="12" aria-hidden="true">
                        <rect width="40" height="18" rx="3" fill="#1A1F71"/>
                        <text x="20" y="13" text-anchor="middle" fill="#fff" font-size="8" font-family="Cairo,Arial" font-weight="800">VISA</text>
                    </svg>
                    {{ __('fields.hyperpay_checkout_tab_visa') }}
                </button>
            @endif
            @if ($hasMaster)
                <button type="button" class="hp-tab {{ $defaultBrand === 'MASTER' ? 'is-on' : '' }}" data-brand="MASTER" aria-selected="{{ $defaultBrand === 'MASTER' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 28 18" width="18" height="12" aria-hidden="true">
                        <circle cx="11" cy="9" r="7" fill="#EB001B"/>
                        <circle cx="17" cy="9" r="7" fill="#F79E1B"/>
                    </svg>
                    {{ __('fields.hyperpay_checkout_tab_master') }}
                </button>
            @endif
        </div>

        <form
            action="{{ $resultUrl }}"
            class="paymentWidgets"
            data-brands="{{ $brands }}"
        ></form>
    </div>
</body>
</html>
