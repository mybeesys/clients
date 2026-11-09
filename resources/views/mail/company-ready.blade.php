@php
    $rtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('main.wizard.company_ready_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#faf8f2;color:#1a1a1a;font-family:Cairo,Tahoma,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf8f2;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid rgba(235,184,30,.35);border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 8px;text-align:{{ $rtl ? 'right' : 'left' }};">
                            <div style="font-size:13px;font-weight:700;letter-spacing:.08em;color:#b88912;">MY BEE</div>
                            <h1 style="margin:10px 0 0;font-size:24px;line-height:1.35;font-weight:800;">{{ __('main.wizard.company_ready_title') }}</h1>
                            <p style="margin:12px 0 0;font-size:15px;line-height:1.8;color:#4b5563;">{{ __('main.wizard.company_ready_hello', ['name' => $ownerName]) }}</p>
                            <p style="margin:8px 0 0;font-size:15px;line-height:1.8;color:#4b5563;">{{ __('main.wizard.company_ready_body', ['company' => $companyName]) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fffaf0;border:1px solid rgba(235,184,30,.45);border-radius:16px;">
                                <tr>
                                    <td style="padding:16px 18px;text-align:{{ $rtl ? 'right' : 'left' }};">
                                        <div style="font-size:12px;font-weight:700;color:#b88912;">{{ __('main.wizard.company_ready_link') }}</div>
                                        @if ($tenantUrl)
                                            <div dir="ltr" style="margin-top:6px;font-size:14px;font-weight:700;word-break:break-all;">{{ $tenantUrl }}</div>
                                        @else
                                            <div style="margin-top:6px;font-size:14px;color:#6b7280;">{{ __('main.wizard.company_ready_missing_link') }}</div>
                                        @endif

                                        <div style="margin-top:14px;font-size:12px;font-weight:700;color:#b88912;">{{ __('main.wizard.company_ready_username') }}</div>
                                        <div dir="ltr" style="margin-top:6px;font-size:16px;font-weight:800;">{{ $username }}</div>

                                        <div style="margin-top:14px;font-size:12px;font-weight:700;color:#b88912;">{{ __('main.wizard.company_ready_password') }}</div>
                                        @if (filled($password))
                                            <div dir="ltr" style="margin-top:6px;font-size:16px;font-weight:800;">{{ $password }}</div>
                                        @else
                                            <div style="margin-top:6px;font-size:14px;line-height:1.7;color:#4b5563;">{{ __('main.wizard.company_ready_existing_password') }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @if ($tenantUrl)
                                <div style="text-align:center;padding-top:18px;">
                                    <a href="{{ $tenantUrl }}" style="display:inline-block;background:#ebb81e;color:#111111;text-decoration:none;font-weight:800;border-radius:14px;padding:12px 22px;font-size:15px;">{{ __('main.wizard.company_ready_open') }}</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
