<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chambers Portal Invitation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #fbf9f5; margin: 0; padding: 24px; color: #1a1a1a; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e3dc; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .header { border-bottom: 2px solid #23493a; padding-bottom: 16px; margin-bottom: 24px; }
        .firm-title { font-size: 20px; font-weight: 700; color: #161718; margin: 8px 0 0 0; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .details-box { background: #faf8f5; border: 1px solid #e5e3dc; border-radius: 6px; padding: 18px; margin: 20px 0; font-size: 13px; line-height: 1.5; }
        .btn { display: inline-block; padding: 12px 28px; background: #23493a; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 16px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #f0eee8; font-size: 11.5px; color: #8a8a8a; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Privileged &amp; Confidential Client Communication</div>
            <h2 class="firm-title">{{ $client->firm->name ?? 'Legal Chambers' }}</h2>
        </div>

        <p>Dear <strong>{{ $client->name }}</strong>,</p>

        <p>You have been officially enrolled into the legal representation registry of <strong>{{ $client->firm->name ?? 'Sharma Legal Chambers' }}</strong> under consulting counsel <strong>{{ $client->primaryAttorney->name ?? 'Managing Chambers Counsel' }}</strong>.</p>

        <div class="details-box">
            <p style="margin: 3px 0;"><strong>Client Entity:</strong> {{ $client->name }} ({{ ucfirst($client->category ?? $client->type) }})</p>
            @if($client->contact_person && $client->contact_person !== $client->name)
            <p style="margin: 3px 0;"><strong>Authorized Contact:</strong> {{ $client->contact_person }}</p>
            @endif
            <p style="margin: 3px 0;"><strong>Lead Advocate:</strong> {{ $client->primaryAttorney->name ?? 'Chambers Counsel' }}</p>
            <p style="margin: 3px 0;"><strong>Portal Login Identifier:</strong> {{ $client->email }}</p>
            <p style="margin: 3px 0;"><strong>Invitation Validity:</strong> 7 Days (Single-Use Secure Token)</p>
        </div>

        <p>Through your private Client Portal, you can securely:</p>
        <ul style="font-size: 13px; color: #4a4a4a; line-height: 1.6;">
            <li>View your litigation dockets, stage progression, and court dates.</li>
            <li>Submit requested evidentiary documents directly to your advocate.</li>
            <li>Access privileged, confidential case files and filings.</li>
            <li>Review fee statements and retainer ledgers.</li>
        </ul>

        <p style="text-align: center; margin-top: 24px;">
            <a href="{{ $invitationUrl }}" class="btn">Activate Account &amp; Set Password</a>
        </p>

        <p style="font-size: 12px; color: #8a8a8a; text-align: center; margin-top: 12px;">
            If the button does not work, copy and paste this link in your browser:<br>
            <span style="font-family: monospace; font-size: 11px; word-break: break-all; color: #23493a;">{{ $invitationUrl }}</span>
        </p>

        <div class="footer">
            <p>This communication is protected by Section 126 &amp; 129 of the Indian Evidence Act (Attorney-Client Privilege). If you received this email in error, please notify the chambers immediately.</p>
        </div>
    </div>
</body>
</html>
