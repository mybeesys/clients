<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('fields.hyperpay_payment_success_save_card') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Cairo, system-ui, sans-serif;
            background: linear-gradient(160deg, #fffdf8, #f7f1e3);
            color: #1a1a1a;
        }
        .box {
            text-align: center;
            padding: 1.5rem;
        }
        .hex {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #F0C43A, #EBB81E);
            clip-path: polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0 50%);
        }
        p { font-weight: 700; margin: 0; }
    </style>
</head>
<body>
    <div class="box">
        <div class="hex" aria-hidden="true"></div>
        <p>{{ $message }}</p>
    </div>
    <script>
        (function () {
            var url = @json($url);
            try {
                if (window.top && window.top !== window) {
                    window.top.location.href = url;
                    return;
                }
            } catch (e) {}
            window.location.href = url;
        })();
    </script>
</body>
</html>
