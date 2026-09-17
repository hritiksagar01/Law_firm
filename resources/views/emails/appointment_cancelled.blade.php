<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Cancelled</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF8F5; margin: 0; padding: 24px; color: #222222; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #EAE4DC; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #C53030; padding-bottom: 16px; margin-bottom: 24px; }
        .firm-title { font-family: 'Georgia', serif; font-size: 20px; font-weight: bold; color: #1a1a1a; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: #FFEBEE; color: #C53030; }
        .details-box { background: #FAF8F5; border: 1px solid #EAE4DC; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #9F8349; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 16px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #EAE4DC; font-size: 12px; color: #766A5E; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Docket Notice</div>
            <h2 class="firm-title" style="margin: 12px 0 0 0;">{{ $appointment->firm->name ?? 'Legal Practice' }}</h2>
        </div>

        <p>Dear <strong>{{ $appointment->client->name ?? 'Valued Client' }}</strong>,</p>

        <p>Please be advised that the following scheduled legal consultation has been <strong>cancelled</strong> or adjourned from the chambers calendar:</p>

        <div class="details-box">
            <p style="margin: 4px 0;"><strong>Session Title:</strong> {{ $appointment->title }}</p>
            <p style="margin: 4px 0;"><strong>Originally Scheduled:</strong> {{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('l, d F Y \a\t h:i A') }}</p>
            <p style="margin: 4px 0;"><strong>Counsel Assigned:</strong> {{ $appointment->attorney->name ?? 'Legal Counsel' }}</p>
            <p style="margin: 4px 0;"><strong>Status:</strong> <span style="color: #C53030; font-weight: bold;">Cancelled</span></p>
        </div>

        <p>If you require a replacement consultation slot or wish to discuss urgency on your case, please communicate directly with counsel through the portal.</p>

        <p style="text-align: center;">
            <a href="{{ url('/portal/messages') }}" class="btn">Message Legal Counsel</a>
        </p>

        <div class="footer">
            <p>Chambers Administration &amp; Scheduling Desk.</p>
        </div>
    </div>
</body>
</html>
