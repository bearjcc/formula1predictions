<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? config('app.name') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #27272a; background-color: #f4f4f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f5;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding: 32px 32px 24px 32px; border-bottom: 1px solid #e4e4e7;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 600; color: #18181b;">
                                {{ config('app.name') }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 32px 32px 32px; border-top: 1px solid #e4e4e7; font-size: 13px; color: #71717a;">
                            This email was sent by {{ config('app.name') }}. If you did not expect this message, you can safely ignore it.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
