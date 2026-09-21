<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Review Status</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #fbf9f5; margin: 0; padding: 24px; color: #1a1a1a; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e3dc; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .header { border-bottom: 2px solid #23493a; padding-bottom: 16px; margin-bottom: 24px; }
        .firm-title { font-size: 20px; font-weight: 700; color: #161718; margin: 8px 0 0 0; }
        .details-box { background: #faf8f5; border: 1px solid #e5e3dc; border-radius: 6px; padding: 18px; margin: 20px 0; font-size: 13px; line-height: 1.5; }
        .btn { display: inline-block; padding: 12px 28px; background: #23493a; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 16px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #f0eee8; font-size: 11.5px; color: #8a8a8a; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 class="firm-title">{{ $documentRequest->firm->name ?? 'Legal Chambers' }}</h2>
        </div>

        <p>Dear <strong>{{ $documentRequest->client->name ?? 'Valued Client' }}</strong>,</p>

        @if($action === 'accepted')
            <div style="padding: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 13px; font-weight: 600; margin-bottom: 16px;">
                ✓ Your submitted document has been accepted and filed into the official Case Dossier.
            </div>
            <p>Advocate <strong>{{ $documentRequest->reviewedBy->name ?? 'Your Legal Counsel' }}</strong> has verified the evidentiary filing for <strong>{{ $documentRequest->title }}</strong>.</p>
        @else
            <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #991b1b; font-size: 13px; font-weight: 600; margin-bottom: 16px;">
                ⚠ Action Required: Re-submission requested for your uploaded document.
            </div>
            <p>Advocate <strong>{{ $documentRequest->reviewedBy->name ?? 'Your Legal Counsel' }}</strong> reviewed your submission and noted that adjustments or a clearer scan are required.</p>
        @endif

        <div class="details-box">
            <p style="margin: 3px 0;"><strong>Document:</strong> {{ $documentRequest->title }}</p>
            <p style="margin: 3px 0;"><strong>Matter:</strong> {{ $documentRequest->matter->title ?? 'Case File' }} ({{ $documentRequest->matter->case_number ?? 'N/A' }})</p>
            @if($documentRequest->rejection_reason || $documentRequest->review_notes)
            <p style="margin: 8px 0 0 0; color: #1a1a1a;"><strong>Advocate's Remarks:</strong></p>
            <p style="margin: 4px 0 0 0; font-style: italic; color: #4a4a4a;">"{{ $documentRequest->rejection_reason ?: $documentRequest->review_notes }}"</p>
            @endif
        </div>

        @if($action !== 'accepted')
        <p style="text-align: center; margin-top: 24px;">
            <a href="{{ url('/portal/requests') }}" class="btn">Upload Corrected Document</a>
        </p>
        @endif

        <div class="footer">
            <p>Protected by Section 126 &amp; 129 of the Indian Evidence Act. Privileged Communication.</p>
        </div>
    </div>
</body>
</html>
