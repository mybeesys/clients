@php
    $rtl = app()->getLocale() === 'ar';
    $digits = str_split($code);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('main.wizard.email_verify_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#faf8f2;color:#1a1a1a;font-family:Cairo,Tahoma,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf8f2;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid rgba(235,184,30,.35);border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 8px;text-align:{{ $rtl ? 'right' : 'left' }};">
                            <div style="font-size:13px;font-weight:700;letter-spacing:.08em;color:#b88912;">MY BEE</div>
                            <h1 style="margin:10px 0 0;font-size:24px;line-height:1.35;font-weight:800;">{{ __('main.wizard.email_verify_mail_title') }}</h1>
                            <p style="margin:12px 0 0;font-size:15px;line-height:1.8;color:#4b5563;">{{ __('main.wizard.email_verify_mail_body') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 8px;" align="center">
                            <div style="font-size:12px;font-weight:700;color:#6b7280;letter-spacing:.04em;">{{ __('main.wizard.email_verify_code_label') }}</div>
                            <table role="presentation" dir="ltr" cellpadding="0" cellspacing="0" style="margin:12px auto 0;">
                                <tr>
                                    @foreach ($digits as $digit)
                                        <td style="width:46px;height:58px;background:#fffaf0;border:1px solid rgba(235,184,30,.55);border-radius:12px;text-align:center;font-size:28px;font-weight:800;color:#1a1a1a;font-family:Consolas,Menlo,monospace;">{{ $digit }}</td>
                                        @if (! $loop->last)
                                            <td style="width:8px;"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                            <p style="margin:14px 0 0;font-size:13px;line-height:1.7;color:#6b7280;">{{ __('main.wizard.email_verify_mail_expire') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:8px 28px 28px;">
                            <a href="{{ $confirmUrl }}" style="display:inline-block;background:#ebb81e;color:#111111;text-decoration:none;font-weight:800;border-radius:14px;padding:12px 22px;font-size:15px;">{{ __('main.wizard.email_verify_mail_button') }}</a>
                            <p style="margin:16px 0 0;font-size:12px;line-height:1.7;color:#9ca3af;">{{ __('main.wizard.email_verify_mail_ignore') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
