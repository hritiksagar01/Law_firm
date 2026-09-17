<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Request</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF8F5; margin: 0; padding: 24px; color: #222222; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #EAE4DC; padding: 36px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .header { border-bottom: 2px solid #9F8349; padding-bottom: 18px; margin-bottom: 24px; }
        .firm-title { font-family: 'Georgia', serif; font-size: 22px; font-weight: bold; color: #222222; margin: 12px 0 0 0; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #FAF3E6; color: #9F8349; letter-spacing: 0.5px; }
        .details-box { background: #FAF8F5; border: 1px solid #EAE4DC; border-radius: 12px; padding: 18px; margin: 24px 0; }
        .btn-wrapper { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; padding: 14px 32px; background: #9F8349; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 14px; box-shadow: 0 2px 8px rgba(159,131,73,0.25); }
        .footer { margin-top: 36px; padding-top: 20px; border-top: 1px solid #EAE4DC; font-size: 11px; color: #766A5E; line-height: 1.6; }
        .alt-link { word-break: break-all; font-size: 11px; color: #9F8349; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Chambers Security &bull; Password Reset</div>
            <h2 class="firm-title">{{ $user->firm->name ?? config('legal.app_name', 'Vennamraj Associates') }}</h2>
        </div>

        <p>Dear <strong>{{ $user->name }}</strong>,</p>

        <p>A request was received to reset the authentication password for your account (<strong>{{ $user->email }}</strong>) on the chambers platform.</p>

        <p>Click the secure button below to choose a new password. This reset dispatch is cryptographically signed and will remain valid for <strong>60 minutes</strong>:</p>

        <div class="btn-wrapper">
            <a href="{{ $resetUrl }}" class="btn">Reset Chambers Password</a>
        </div>

        <div class="details-box">
            <p style="margin: 0 0 8px 0; font-size: 12px; color: #554D45;"><strong>Having trouble with the button?</strong></p>
            <p style="margin: 0; font-size: 12px;">Copy and paste the following URL into your web browser:</p>
            <p style="margin: 8px 0 0 0;"><a href="{{ $resetUrl }}" class="alt-link">{{ $resetUrl }}</a></p>
        </div>

        <p style="font-size: 12px; color: #766A5E;">If you did not initiate this request, no further action is required. Your current credentials remain secure and unchanged.</p>

        <div class="footer">
            <p>This communication and any links contain privileged, proprietary, or confidential authentication tokens. If received in error, please immediately notify platform security.</p>
            <p>&copy; {{ date('Y') }} {{ config('legal.app_name', 'Vennamraj Associates') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
