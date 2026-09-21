<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Client Portal — {{ $client->firm->name ?? 'Sharma Legal Chambers' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #fbf9f5;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .invitation-card {
            background: #ffffff;
            border: 1px solid #e5e3dc;
            border-radius: 8px;
            max-width: 480px;
            width: 100%;
            padding: 36px 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .firm-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .chambers-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            margin-bottom: 12px;
        }
        .firm-title {
            font-size: 20px;
            font-weight: 700;
            color: #161718;
            letter-spacing: -0.3px;
        }
        .welcome-desc {
            font-size: 13px;
            color: #646864;
            margin-top: 6px;
            line-height: 1.5;
        }
        .client-box {
            background: #faf8f5;
            border: 1px solid #e5e3dc;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 24px;
            font-size: 12.5px;
        }
        .client-box-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .client-box-row:last-child { margin-bottom: 0; }
        .label-muted { color: #8a8a8a; }
        .val-strong { font-weight: 600; color: #1a1a1a; }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1a1a1a;
        }
        .form-input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #e5e3dc;
            font-size: 13px;
            color: #1a1a1a;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .form-input:focus {
            border-color: #23493a;
            box-shadow: 0 0 0 1px #23493a;
        }
        .btn-submit {
            width: 100%;
            height: 42px;
            background: #23493a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            transition: background 0.15s ease;
        }
        .btn-submit:hover {
            background: #1b3b2f;
        }
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #8a8a8a;
            margin-top: 24px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="invitation-card">
        <div class="firm-header">
            <div class="chambers-badge">
                <span class="material-symbols-outlined" style="font-size: 14px;">verified_user</span>
                <span>Verified Representation</span>
            </div>
            <h1 class="firm-title">{{ $client->firm->name ?? 'Sharma Legal Chambers' }}</h1>
            <p class="welcome-desc">Welcome to your secure client legal portal. Set your private password below to activate your account.</p>
        </div>

        @if($errors->any())
            <div class="error-alert">
                <ul style="padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="client-box">
            <div class="client-box-row">
                <span class="label-muted">Client Entity:</span>
                <span class="val-strong">{{ $client->name }}</span>
            </div>
            <div class="client-box-row">
                <span class="label-muted">Login Email:</span>
                <span class="val-strong" style="font-family: monospace;">{{ $client->email }}</span>
            </div>
            <div class="client-box-row">
                <span class="label-muted">Consulting Counsel:</span>
                <span class="val-strong">{{ $client->primaryAttorney->name ?? 'Chambers Counsel' }}</span>
            </div>
        </div>

        <form action="{{ route('portal.invitation.complete', $token) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="password">Create Secure Password</label>
                <input type="password" id="password" name="password" required autofocus
                       placeholder="At least 8 characters"
                       class="form-input" />
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Re-enter your password"
                       class="form-input" />
            </div>

            <button type="submit" class="btn-submit">
                <span class="material-symbols-outlined" style="font-size: 18px;">lock_open</span>
                <span>Activate Account &amp; Access Portal</span>
            </button>
        </form>

        <div class="footer-note">
            Protected by Section 126 &amp; 129 of the Indian Evidence Act.<br>
            Privileged attorney-client communication platform.
        </div>
    </div>
</body>
</html>
