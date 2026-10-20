@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $currency = strtoupper((string) ($quote['currency'] ?? $payment->currency ?? 'SAR'));
    $brandsList = array_values(array_filter(preg_split('/\s+/', strtoupper((string) $brands)) ?: []));
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
            --ink: #1a1a1a;
            --muted: #6b7280;
            --line: rgba(26, 26, 26, 0.12);
            --soft: #faf6eb;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; background: #fff; font-family: Cairo, system-ui, sans-serif; color: var(--ink); }
        .wrap { padding: 0.85rem 1rem 1.25rem; }
        .head { margin-bottom: 0.85rem; }
        .head h1 { margin: 0; font-size: 1.15rem; font-weight: 800; }
        .head p { margin: 0.35rem 0 0; color: var(--muted); font-size: 0.86rem; line-height: 1.5; }
        .note {
            display: flex; gap: 0.45rem; align-items: flex-start;
            padding: 0.65rem 0.75rem; border-radius: 0.75rem;
            background: rgba(235, 184, 30, 0.14); color: #8a6a10;
            font-size: 0.8rem; font-weight: 700; margin-bottom: 0.9rem; line-height: 1.45;
        }
        .brands { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
        .brands span {
            font-size: 0.68rem; font-weight: 800; letter-spacing: .04em;
            border: 1px solid var(--line); border-radius: 999px; padding: 0.2rem 0.55rem; color: var(--muted);
        }
        .wpwl-form { max-width: 100% !important; }
        .wpwl-group { margin-bottom: 0.65rem !important; }
        .wpwl-label { font-weight: 700 !important; color: var(--ink) !important; }
        .wpwl-control {
            border: 2px solid rgba(26,26,26,.22) !important;
            border-radius: 0.7rem !important;
            min-height: 2.7rem !important;
            box-shadow: none !important;
        }
        .wpwl-control:focus { border-color: var(--bee) !important; }
        .wpwl-button {
            width: 100% !important;
            border: 0 !important;
            border-radius: 0.85rem !important;
            background: linear-gradient(135deg, #F0C43A, var(--bee)) !important;
            color: #111 !important;
            font-weight: 800 !important;
            min-height: 2.9rem !important;
            margin-top: 0.5rem !important;
        }
        .secure {
            margin-top: 0.85rem; text-align: center; font-size: 0.75rem; color: var(--muted); font-weight: 600;
        }
    </style>
    <script>
        var wpwlOptions = {
            locale: @json($config->widgetLocale()),
            style: "card",
            brandDetection: true,
            onReady: function () {
                var btn = document.querySelector(".wpwl-button");
                if (btn) btn.textContent = @json(__('fields.hyperpay_vault_card_submit'));
            }
        };
    </script>
    <script src="{{ $widgetUrl }}"></script>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <h1>{{ __('fields.hyperpay_vault_card_title') }}</h1>
            <p>{{ __('fields.hyperpay_vault_card_hint') }}</p>
        </div>
        <div class="note">🔒 {{ __('fields.hyperpay_vault_card_secure') }}</div>
        @if ($brandsList)
            <div class="brands">
                @foreach ($brandsList as $brand)
                    <span>{{ $brand }}</span>
                @endforeach
            </div>
        @endif
        <form
            action="{{ $resultUrl }}"
            class="paymentWidgets"
            data-brands="{{ $brands }}"
        ></form>
        <div class="secure">{{ __('fields.hyperpay_checkout_secure') }} · {{ $currency }} 0.00</div>
    </div>
</body>
</html>
