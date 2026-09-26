@php
    // Escape, then turn URLs into links and newlines into <br>.
    $html = nl2br(preg_replace('~(https?://[^\s<]+)~', '<a href="$1" style="color:#3b5bdb">$1</a>', e($body)));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#212529;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 28px;border-bottom:1px solid #eef1f6;">
                            <a href="{{ route('home') }}" style="text-decoration:none;color:#3b5bdb;font-weight:700;font-size:18px;">{{ setting('store_name') }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:15px;line-height:1.6;">
                            {!! $html !!}
                            @if ($buttonText && $buttonUrl)
                                <p style="margin:28px 0 0;">
                                    <a href="{{ $buttonUrl }}" style="display:inline-block;background:#3b5bdb;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{{ $buttonText }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;border-top:1px solid #eef1f6;font-size:12px;color:#6c757d;">
                            &copy; {{ date('Y') }} {{ setting('store_name') }}
                            @if ($unsubscribeUrl)
                                &middot; <a href="{{ $unsubscribeUrl }}" style="color:#6c757d;">Unsubscribe</a>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
