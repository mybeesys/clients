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
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            background: #fff;
            font-family: Cairo, system-ui, sans-serif;
            color: var(--ink);
            overflow: hidden;
            height: auto;
        }
        .wrap {
            padding: 0.55rem 0.85rem 0.75rem;
            max-width: 680px;
            margin: 0 auto;
            width: 100%;
        }
        .hp-tabs {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
        }
        .hp-tab {
            appearance: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 0.86rem;
            font-weight: 800;
        }
        .hp-tab.is-on {
            border-color: rgba(235, 184, 30, 0.75);
            background: rgba(235, 184, 30, 0.14);
            box-shadow: 0 0 0 3px rgba(235, 184, 30, 0.14);
        }
        .hp-tab svg { height: 1rem; width: auto; display: block; }

        .wpwl-container { max-width: 100% !important; margin: 0 !important; }
        .wpwl-form {
            max-width: 100% !important; margin: 0 !important; padding: 0 !important;
            background: transparent !important; border: 0 !important; box-shadow: none !important;
        }
        .wpwl-group {
            margin: 0 0 0.72rem !important;
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 0.3rem;
        }
        html[dir="rtl"] .wpwl-group {
            grid-template-columns: 1fr minmax(7rem, 9rem);
            align-items: center;
            gap: 0.75rem;
        }
        .wpwl-group.is-hidden-extra {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .wpwl-label {
            color: var(--ink) !important;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
        }
        html[dir="rtl"] .wpwl-label {
            order: 2;
            text-align: right !important;
            direction: rtl !important;
            white-space: nowrap;
        }
        html[dir="rtl"] .wpwl-wrapper,
        html[dir="rtl"] [class*="wpwl-wrapper-"] {
            order: 1;
            min-width: 0;
        }
        .wpwl-control, .wpwl-control-iframe, input.wpwl-control {
            min-height: 3.05rem !important;
            width: 100% !important;
            border-radius: 0.8rem !important;
            border: 1.5px solid var(--line) !important;
            background: #fff !important;
            color: var(--ink) !important;
            font-size: 1rem !important;
            box-shadow: none !important;
            direction: ltr !important;
            text-align: left !important;
            padding-inline: 0.9rem !important;
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
        .wpwl-button,
        button.wpwl-button {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            min-height: 3.2rem !important;
            margin: 0.55rem 0 0 !important;
            border: 0 !important;
            border-radius: 0.9rem !important;
            background: linear-gradient(135deg, #F0C43A, var(--bee) 50%, var(--bee-deep)) !important;
            color: #111 !important;
            font-weight: 800 !important;
            font-size: 1.05rem !important;
            box-shadow: 0 10px 24px rgba(184, 137, 18, 0.22) !important;
            position: relative !important;
            z-index: 5 !important;
        }
        html[dir="rtl"] .wpwl-button { direction: rtl !important; }
        html.hp-busy .wpwl-button { pointer-events: none; opacity: 0.78 !important; }

        /* Fallback save bar if widget button is missing/hidden */
        .vault-fallback-submit {
            display: none;
            width: 100%;
            min-height: 3.2rem;
            margin-top: 0.55rem;
            border: 0;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #F0C43A, var(--bee) 50%, var(--bee-deep));
            color: #111;
            font: inherit;
            font-weight: 800;
            font-size: 1.05rem;
            cursor: pointer;
        }
        .vault-fallback-submit.is-on { display: block; }
    </style>
    <script>
        var wpwlOptions = {
            locale: @json($config->widgetLocale()),
            style: "plain",
            brandDetection: true,
            onReady: function () {
                tidyVaultForm();
                var btn = document.querySelector(".wpwl-button");
                if (btn) {
                    btn.textContent = @json(__('fields.hyperpay_vault_card_submit'));
                    btn.style.display = "block";
                } else {
                    var fb = document.getElementById("vault-fallback-submit");
                    if (fb) fb.classList.add("is-on");
                }
                bindBrandTabs();
                document.documentElement.classList.remove("hp-busy");
                reportHeight();
                setTimeout(reportHeight, 250);
                setTimeout(reportHeight, 800);
            },
            onBeforeSubmitCard: function () {
                document.documentElement.classList.add("hp-busy");
                return true;
            }
        };

        function tidyVaultForm() {
            var keepRe = /(cardNumber|expiry|cardHolder|cvv|cardholder|securityCode)/i;
            var keepLabel = /(رقم البطاقة|تاريخ|حامل|CVV|رمز|card number|expiry|holder|security)/i;
            var dropLabel = /(دولة|country|شارع|street|مدينة|city|بريد|email|هاتف|phone|ولاية|state|رمز بريدي|post)/i;

            document.querySelectorAll(".wpwl-group").forEach(function (group) {
                if (group.querySelector(".wpwl-button")) {
                    group.classList.remove("is-hidden-extra");
                    return;
                }
                var cls = group.className || "";
                var label = ((group.querySelector(".wpwl-label") || {}).textContent || "").trim();
                var keep = keepRe.test(cls) || keepLabel.test(label);
                if (dropLabel.test(label) || dropLabel.test(cls)) keep = false;
                if (!keep) group.classList.add("is-hidden-extra");
                else group.classList.remove("is-hidden-extra");
            });
        }

        function reportHeight() {
            try {
                var h = Math.max(
                    document.documentElement.scrollHeight || 0,
                    document.body.scrollHeight || 0,
                    document.querySelector(".wrap") ? document.querySelector(".wrap").scrollHeight + 8 : 0,
                    420
                );
                window.parent.postMessage({ type: "vault-card-height", height: h }, "*");
            } catch (e) {}
        }

        function bindBrandTabs() {
            document.querySelectorAll(".hp-tab").forEach(function (tab) {
                tab.addEventListener("click", function () {
                    document.querySelectorAll(".hp-tab").forEach(function (t) {
                        var on = t === tab;
                        t.classList.toggle("is-on", on);
                        t.setAttribute("aria-selected", on ? "true" : "false");
                    });
                    try {
                        var brand = tab.getAttribute("data-brand");
                        var brandInput = document.querySelector('input[name="paymentBrand"], select[name="paymentBrand"]');
                        if (brandInput) brandInput.value = brand;
                    } catch (e) {}
                });
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            var fb = document.getElementById("vault-fallback-submit");
            if (fb) {
                fb.addEventListener("click", function () {
                    var real = document.querySelector(".wpwl-button");
                    if (real) real.click();
                });
            }
        });
    </script>
    <script src="{{ $widgetUrl }}"></script>
</head>
<body>
    <div class="wrap">
        <div class="hp-tabs" role="tablist">
            @if ($hasMada)
                <button type="button" class="hp-tab {{ $defaultBrand === 'MADA' ? 'is-on' : '' }}" data-brand="MADA" aria-selected="{{ $defaultBrand === 'MADA' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 64 18" width="40" height="13" aria-hidden="true">
                        <rect width="64" height="18" rx="3" fill="#00A651"/>
                        <text x="32" y="13" text-anchor="middle" fill="#fff" font-size="10" font-family="Cairo,Arial" font-weight="800">mada</text>
                    </svg>
                    {{ __('fields.hyperpay_checkout_tab_mada') }}
                </button>
            @endif
            @if ($hasVisa)
                <button type="button" class="hp-tab {{ $defaultBrand === 'VISA' ? 'is-on' : '' }}" data-brand="VISA" aria-selected="{{ $defaultBrand === 'VISA' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 40 18" width="26" height="13" aria-hidden="true">
                        <rect width="40" height="18" rx="3" fill="#1A1F71"/>
                        <text x="20" y="13" text-anchor="middle" fill="#fff" font-size="8" font-family="Cairo,Arial" font-weight="800">VISA</text>
                    </svg>
                    {{ __('fields.hyperpay_checkout_tab_visa') }}
                </button>
            @endif
            @if ($hasMaster)
                <button type="button" class="hp-tab {{ $defaultBrand === 'MASTER' ? 'is-on' : '' }}" data-brand="MASTER" aria-selected="{{ $defaultBrand === 'MASTER' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 28 18" width="20" height="13" aria-hidden="true">
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

        <button type="button" class="vault-fallback-submit" id="vault-fallback-submit">
            {{ __('fields.hyperpay_vault_card_submit') }}
        </button>
    </div>
</body>
</html>
