<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Request</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF8F5; margin: 0; padding: 24px; color: #222222; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #EAE4DC; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #9F8349; padding-bottom: 16px; margin-bottom: 24px; }
        .firm-title { font-family: 'Georgia', serif; font-size: 20px; font-weight: bold; color: #1a1a1a; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: #FAF3E6; color: #9F8349; }
        .details-box { background: #FAF8F5; border: 1px solid #EAE4DC; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #9F8349; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 16px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #EAE4DC; font-size: 12px; color: #766A5E; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Privileged &amp; Confidential</div>
            <h2 class="firm-title" style="margin: 12px 0 0 0;">{{ $documentRequest->firm->name ?? 'Legal Chambers' }}</h2>
        </div>

        <p>Dear <strong>{{ $documentRequest->client->name ?? 'Valued Client' }}</strong>,</p>

        <p>Your legal counsel <strong>{{ $documentRequest->requestedBy->name ?? 'Counsel' }}</strong> has requested the submission of an essential case document for your ongoing docket.</p>

        <div class="details-box">
            <p style="margin: 4px 0;"><strong>Document Requested:</strong> {{ $documentRequest->title }}</p>
            <p style="margin: 4px 0;"><strong>Associated Case:</strong> {{ $documentRequest->matter->title ?? 'Matter Dossier' }} ({{ $documentRequest->matter->case_number ?? 'N/A' }})</p>
            <p style="margin: 4px 0;"><strong>Category:</strong> {{ $documentRequest->category ?? 'General' }}</p>
            <p style="margin: 4px 0;"><strong>Priority:</strong> <span style="text-transform: capitalize;">{{ $documentRequest->priority ?? 'Normal' }}</span></p>
            @if($documentRequest->due_date)
            <p style="margin: 4px 0;"><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($documentRequest->due_date)->format('d M Y') }}</p>
            @endif
            @if($documentRequest->description)
            <p style="margin: 8px 0 0 0; color: #554D45;"><strong>Instructions:</strong> {{ $documentRequest->description }}</p>
            @endif
        </div>

        <p>Please log in to your secure Client Portal to upload this filing directly to the encrypted chambers vault:</p>

        <p style="text-align: center;">
            <a href="{{ url('/portal/requests') }}" class="btn">View &amp; Upload Document</a>
        </p>

        <div class="footer">
            <p>This communication and any attachments are intended solely for the addressee and may contain legally privileged, proprietary, or confidential attorney-client information.</p>
        </div>
    </div>
</body>
</html>
