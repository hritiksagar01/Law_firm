<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    /**
     * Display Platform Mail & Communications Settings.
     * In this production-hardened environment, database and storage keys are immutable
     * server configuration managed strictly via CI/CD and server environment variables.
     */
    public function mailSettings(): View
    {
        $mailConfig = [
            'mailer' => config('mail.default', 'log'),
            'host' => config('mail.mailers.smtp.host', '127.0.0.1'),
            'port' => config('mail.mailers.smtp.port', 587),
            'username' => config('mail.mailers.smtp.username', ''),
            'encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'from_address' => config('mail.from.address', 'contact@vennamraj.com'),
            'from_name' => config('mail.from.name', config('app.name')),
            'has_password' => !empty(config('mail.mailers.smtp.password')),
        ];

        return view('admin.settings.mail', compact('mailConfig'));
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
        $host = config('mail.mailers.smtp.host', 'smtp.brevo.com');
        $port = config('mail.mailers.smtp.port', 587);

        try {
            // 1. First probe TCP socket connectivity
            $errno = 0;
            $errstr = '';
            $fp = @fsockopen($host, (int) $port, $errno, $errstr, 5);
            if (! $fp) {
                return response()->json([
                    'success' => false,
                    'message' => "Socket probe failed to {$host}:{$port}. Error: {$errstr} ({$errno}).",
                ], 422);
            }
            fclose($fp);

            // 2. Dispatch a verification email
            Mail::raw(
                "This is a verified test email from " . config('app.name') . ".\n\nYour SMTP gateway is operating properly.\nTimestamp: " . now()->toIso8601String(),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject('Platform SMTP Gateway Test Verification — ' . config('app.name'));
                }
            );

            return response()->json([
                'success' => true,
                'message' => "Test email successfully dispatched to {$recipient} via {$host}:{$port}!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "SMTP Delivery Failed: " . $e->getMessage(),
            ], 500);
        }
    }
}
