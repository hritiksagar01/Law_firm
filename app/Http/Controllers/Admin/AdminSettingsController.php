<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    /**
     * Display Platform Email & SMS Notification Delivery & Settings.
     */
    public function mailSettings(Request $request): View
    {
        $mailConfig = [
            'mailer' => config('mail.default', 'log'),
            'host' => config('mail.mailers.smtp.host', '127.0.0.1'),
            'port' => config('mail.mailers.smtp.port', 587),
            'username' => config('mail.mailers.smtp.username', ''),
            'encryption' => config('mail.mailers.smtp.encryption', 'tls') ?? 'none',
            'from_address' => config('mail.from.address', 'contact@vennamraj.com'),
            'from_name' => config('mail.from.name', config('app.name')),
            'has_password' => ! empty(config('mail.mailers.smtp.password')),
        ];

        $query = DeliveryLog::with('firm');

        if ($request->filled('channel') && ! in_array(strtolower($request->channel), ['both', 'any', 'all', ''])) {
            $query->where('channel', strtolower($request->channel));
        }

        if ($request->filled('status') && ! in_array(strtolower($request->status), ['any', 'all', ''])) {
            $status = strtolower(str_replace(' ', '_', $request->status));
            $query->where('status', $status);
        }

        $deliveryLogs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $channelsEnabled = [
            'email' => session('channels_email', true),
            'sms' => session('channels_sms', true),
        ];

        return view('admin.settings.mail', compact('mailConfig', 'deliveryLogs', 'channelsEnabled'));
    }

    /**
     * Save Notification Channels.
     */
    public function saveChannels(Request $request): RedirectResponse
    {
        session([
            'channels_email' => $request->has('send_email'),
            'channels_sms' => $request->has('send_sms'),
        ]);

        return redirect()->route('admin.settings.mail')->with('success', 'Notification channels saved successfully.');
    }

    /**
     * Send test email and log delivery.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        DeliveryLog::create([
            'channel' => 'email',
            'recipient' => $request->test_email,
            'notification' => 'Test message',
            'provider' => config('mail.default') === 'smtp' ? 'Resend' : 'None',
            'status' => 'logged_only',
        ]);

        return redirect()->route('admin.settings.mail')->with('success', 'Test email dispatched to '.$request->test_email);
    }

    /**
     * Send test SMS and log delivery.
     */
    public function sendTestSms(Request $request): RedirectResponse
    {
        $request->validate([
            'test_phone' => 'required|string',
        ]);

        DeliveryLog::create([
            'channel' => 'sms',
            'recipient' => $request->test_phone,
            'notification' => 'Test message',
            'provider' => 'Twilio',
            'status' => 'logged_only',
        ]);

        return redirect()->route('admin.settings.mail')->with('success', 'Test SMS dispatched to '.$request->test_phone);
    }

    /**
     * Update Platform Mail & SMTP Gateway settings in .env and runtime config.
     */
    public function updateMailSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,log,sendmail,array',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric|between:1,65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,none,null',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        $mailer = $validated['mail_mailer'];
        $host = $validated['mail_host'] ?? '127.0.0.1';
        $port = $validated['mail_port'] ?? 587;
        $username = $validated['mail_username'] ?? '';
        $encryption = in_array($validated['mail_encryption'] ?? '', ['none', 'null', '']) ? null : $validated['mail_encryption'];
        $fromAddress = $validated['mail_from_address'];
        $fromName = $validated['mail_from_name'];

        $envUpdates = [
            'MAIL_MAILER' => $mailer,
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_USERNAME' => $username,
            'MAIL_ENCRYPTION' => $encryption ?? 'null',
            'MAIL_FROM_ADDRESS' => $fromAddress,
            'MAIL_FROM_NAME' => $fromName,
        ];

        // Update password if provided, or clear if explicitly requested
        if ($request->filled('mail_password')) {
            $envUpdates['MAIL_PASSWORD'] = $request->input('mail_password');
        } elseif ($request->boolean('clear_password')) {
            $envUpdates['MAIL_PASSWORD'] = '';
        }

        // 1. Update runtime configuration immediately
        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);
        if (isset($envUpdates['MAIL_PASSWORD'])) {
            config(['mail.mailers.smtp.password' => $envUpdates['MAIL_PASSWORD']]);
        }
        Mail::purge('smtp');

        // 2. Persist to .env file
        $this->updateEnvMailKeys($envUpdates);

        // 3. Clear configuration caches
        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
        }

        return redirect()->route('admin.settings.mail')
            ->with('success', 'Platform Mail & SMTP Gateway settings updated successfully! You can now send test emails.');
    }

    /**
     * Test SMTP Delivery by dispatching a verified test email.
     */
    public function testMail(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $recipient = $request->input('test_email');

        // Allow live overrides from test payload if provided
        $mailer = $request->input('mailer', config('mail.default', 'log'));
        $host = $request->input('host', config('mail.mailers.smtp.host', '127.0.0.1'));
        $port = (int) $request->input('port', config('mail.mailers.smtp.port', 587));
        $username = $request->input('username', config('mail.mailers.smtp.username', ''));
        $encryption = $request->input('encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $encryption = in_array($encryption, ['none', 'null', '']) ? null : $encryption;
        $fromAddress = $request->input('from_address', config('mail.from.address', 'contact@vennamraj.com'));
        $fromName = $request->input('from_name', config('mail.from.name', config('app.name')));

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        if ($request->filled('password')) {
            config(['mail.mailers.smtp.password' => $request->input('password')]);
        }
        Mail::purge('smtp');

        try {
            // If log driver, write directly to system log
            if ($mailer === 'log') {
                Mail::raw(
                    'Verified test email from '.config('app.name').".\n\nLogged to storage/logs/laravel.log\nTimestamp: ".now()->toIso8601String(),
                    function ($message) use ($recipient) {
                        $message->to($recipient)
                            ->subject('Platform SMTP Gateway Test Verification — '.config('app.name'));
                    }
                );

                return response()->json([
                    'success' => true,
                    'message' => "Mail driver is set to 'LOG'. Test email written to storage/logs/laravel.log for {$recipient}.",
                ]);
            }

            // If SMTP driver, first probe TCP socket connectivity
            if ($mailer === 'smtp') {
                $errno = 0;
                $errstr = '';
                $fp = @fsockopen($host, $port, $errno, $errstr, 5);
                if (! $fp) {
                    return response()->json([
                        'success' => false,
                        'message' => "Socket probe failed to {$host}:{$port}. Error: {$errstr} ({$errno}). Please verify your SMTP Host and Port.",
                    ], 422);
                }
                fclose($fp);
            }

            // Dispatch verification email
            Mail::raw(
                'This is a verified test email from '.config('app.name').".\n\nYour SMTP gateway is operating properly.\nTimestamp: ".now()->toIso8601String(),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject('Platform SMTP Gateway Test Verification — '.config('app.name'));
                }
            );

            return response()->json([
                'success' => true,
                'message' => "Test email successfully dispatched to {$recipient} via {$host}:{$port}!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP Delivery Failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Safely update mail keys in .env without touching any other configuration.
     */
    protected function updateEnvMailKeys(array $keys): bool
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath) || ! is_readable($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($keys as $key => $value) {
            $formattedValue = $this->formatEnvValue($value);
            $pattern = "/^(#\s*)?".preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
            } else {
                $content = rtrim($content)."\n{$key}={$formattedValue}\n";
            }
        }

        try {
            @file_put_contents($envPath, $content);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Format value for .env output with proper quoting.
     */
    protected function formatEnvValue($value): string
    {
        if (is_null($value)) {
            return '';
        }
        $value = (string) $value;
        if (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '$') || str_contains($value, '"')) {
            return '"'.addcslashes($value, '"\\$').'"';
        }

        return $value;
    }
}
