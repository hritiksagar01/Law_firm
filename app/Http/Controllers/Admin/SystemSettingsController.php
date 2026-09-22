<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    /**
     * Display Platform System Settings.
     */
    public function index(): View
    {
        $settings = [
            'platform_name' => PlatformSetting::platformName(),
            'support_email' => (string) PlatformSetting::get('support_email', 'contact@vennamraj.com'),
            'maintenance_notice' => (string) PlatformSetting::get('maintenance_notice', ''),
            'idle_timeout' => (int) PlatformSetting::get('idle_timeout', 12),
            // Branding
            'platform_logo' => PlatformSetting::get('platform_logo'),
            'platform_logo_url' => PlatformSetting::logoUrl(),
        ];

        // Storage & AWS S3
        $storage = PlatformSetting::storageConfig();
        $storage['aws_has_secret'] = ! empty($storage['aws_secret_access_key']);
        $storage['aws_secret_access_key'] = $storage['aws_has_secret'] ? '••••••••••••••••' : '';

        // Footer & CMS Legal Content
        $footer = PlatformSetting::footerData();

        // Deployment Telemetry
        $activeDriver = $storage['storage_driver'] === 's3' ? 's3' : 'local';
        $bucketInfo = $storage['aws_bucket'] ? " (Bucket: {$storage['aws_bucket']}, Region: {$storage['aws_default_region']})" : '';

        $deployment = [
            'public_url' => config('app.url', 'https://lawfirm.pllatinum.me'),
            'file_storage' => $activeDriver === 's3'
                ? "Amazon S3 / R2 Cloud Object Storage{$bucketInfo}"
                : 'Local disk (encrypted at rest by server AES-256)',
            'storage_driver' => $activeDriver,
            'demo_mode' => 'On — demo accounts listed on the sign-in page',
            'scheduled_reminders' => 'Protected by CRON_SECRET',
            'encryption_key' => config('app.key') ? 'APP_KEY configured' : 'Not configured',
        ];

        return view('admin.system.index', compact('settings', 'storage', 'footer', 'deployment'));
    }

    /**
     * Update Platform System Settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General Settings
            'platform_name' => 'required|string|max:100',
            'support_email' => 'required|email|max:255',
            'maintenance_notice' => 'nullable|string|max:300',
            'idle_timeout' => 'required|integer|between:1,72',

            // Brand & Logo
            'logo' => 'nullable|file|image|mimes:png,jpg,jpeg,svg,webp|max:4096',
            'remove_logo' => 'nullable|boolean',

            // Cloud Storage (Amazon S3 / R2)
            'storage_driver' => 'nullable|in:local,s3',
            'aws_access_key_id' => 'nullable|string|max:255',
            'aws_secret_access_key' => 'nullable|string|max:255',
            'aws_default_region' => 'nullable|string|max:50',
            'aws_bucket' => 'nullable|string|max:255',
            'aws_endpoint' => 'nullable|string|max:255',
            'aws_use_path_style_endpoint' => 'nullable|boolean',

            // Public Footer & CMS Content
            'footer_headline' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
            'about_headline' => 'nullable|string|max:255',
            'about_content' => 'nullable|string|max:15000',
            'contact_headline' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'contact_hours' => 'nullable|string|max:255',
            'privacy_headline' => 'nullable|string|max:255',
            'privacy_content' => 'nullable|string|max:25000',
            'terms_headline' => 'nullable|string|max:255',
            'terms_content' => 'nullable|string|max:25000',
        ]);

        // 1. General Settings
        PlatformSetting::set('platform_name', $validated['platform_name']);
        PlatformSetting::set('support_email', $validated['support_email']);
        PlatformSetting::set('maintenance_notice', $validated['maintenance_notice'] ?? '');
        PlatformSetting::set('idle_timeout', (int) $validated['idle_timeout']);

        // 2. Logo Upload & Management
        if ($request->boolean('remove_logo')) {
            $currentLogo = PlatformSetting::get('platform_logo');
            if ($currentLogo && is_string($currentLogo) && file_exists(public_path($currentLogo))) {
                @unlink(public_path($currentLogo));
            }
            PlatformSetting::set('platform_logo', null);
        } elseif ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uploadDir = public_path('uploads/branding');
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $currentLogo = PlatformSetting::get('platform_logo');
            if ($currentLogo && is_string($currentLogo) && file_exists(public_path($currentLogo))) {
                @unlink(public_path($currentLogo));
            }

            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'logo_'.time().'_'.Str::random(8).'.'.$extension;
            $file->move($uploadDir, $filename);

            PlatformSetting::set('platform_logo', 'uploads/branding/'.$filename);
        }

        // 3. Storage & Amazon S3 Settings
        if ($request->has('storage_driver')) {
            PlatformSetting::set('storage_driver', $request->input('storage_driver', 'local'));
        }
        if ($request->has('aws_access_key_id')) {
            PlatformSetting::set('aws_access_key_id', trim((string) $request->input('aws_access_key_id')));
        }
        if ($request->filled('aws_secret_access_key')) {
            $secretInput = trim((string) $request->input('aws_secret_access_key'));
            // Do not overwrite with placeholder asterisks
            if (! str_starts_with($secretInput, '••••')) {
                PlatformSetting::set('aws_secret_access_key', $secretInput);
            }
        }
        if ($request->has('aws_default_region')) {
            PlatformSetting::set('aws_default_region', trim((string) $request->input('aws_default_region', 'ap-south-1')));
        }
        if ($request->has('aws_bucket')) {
            PlatformSetting::set('aws_bucket', trim((string) $request->input('aws_bucket')));
        }
        if ($request->has('aws_endpoint')) {
            PlatformSetting::set('aws_endpoint', trim((string) $request->input('aws_endpoint')));
        }
        PlatformSetting::set('aws_use_path_style_endpoint', $request->boolean('aws_use_path_style_endpoint'));

        // 4. Public Content & Footer Settings
        $footerKeys = [
            'footer_headline',
            'footer_description',
            'footer_copyright',
            'about_headline',
            'about_content',
            'contact_headline',
            'contact_email',
            'contact_phone',
            'contact_address',
            'contact_hours',
            'privacy_headline',
            'privacy_content',
            'terms_headline',
            'terms_content',
        ];

        foreach ($footerKeys as $key) {
            if ($request->has($key)) {
                PlatformSetting::set($key, $request->input($key));
            }
        }

        return redirect()->route('admin.system.index')->with('success', 'System settings saved successfully.');
    }

    /**
     * Reset Platform Logo back to default.
     */
    public function resetLogo(): RedirectResponse
    {
        $currentLogo = PlatformSetting::get('platform_logo');
        if ($currentLogo && is_string($currentLogo) && file_exists(public_path($currentLogo))) {
            @unlink(public_path($currentLogo));
        }

        PlatformSetting::set('platform_logo', null);

        return redirect()->route('admin.system.index')->with('success', 'Platform logo reverted to default.');
    }

    /**
     * Test Amazon S3 / Cloudflare R2 Connection via AWS SigV4 signed request.
     */
    public function testS3(Request $request): JsonResponse
    {
        $key = trim((string) ($request->input('aws_access_key_id') ?: PlatformSetting::get('aws_access_key_id', '')));
        $secretInput = trim((string) $request->input('aws_secret_access_key'));
        $secret = ($secretInput !== '' && ! str_starts_with($secretInput, '••••'))
            ? $secretInput
            : (string) PlatformSetting::get('aws_secret_access_key', '');
        $region = trim((string) ($request->input('aws_default_region') ?: PlatformSetting::get('aws_default_region', 'ap-south-1')));
        $bucket = trim((string) ($request->input('aws_bucket') ?: PlatformSetting::get('aws_bucket', '')));
        $endpoint = trim((string) ($request->input('aws_endpoint') ?: PlatformSetting::get('aws_endpoint', '')));
        $usePathStyle = $request->boolean('aws_use_path_style_endpoint', (bool) PlatformSetting::get('aws_use_path_style_endpoint', false));

        if (empty($key) || empty($secret) || empty($bucket)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required S3 credentials. Please provide Access Key ID, Secret Key, and Bucket Name.',
            ], 422);
        }

        $result = $this->verifyS3Connection($key, $secret, $region, $bucket, $endpoint ?: null, $usePathStyle);

        return response()->json($result);
    }

    /**
     * Execute a lightweight AWS SigV4 signed GET/HEAD check to test bucket connectivity.
     *
     * @return array{success: bool, message: string, latency_ms?: int, status?: int}
     */
    protected function verifyS3Connection(string $key, string $secret, string $region, string $bucket, ?string $endpoint = null, bool $usePathStyle = false): array
    {
        $startTime = microtime(true);

        try {
            $service = 's3';
            $method = 'GET';
            $queryString = 'max-keys=1';

            // Determine host and path
            if ($endpoint) {
                $parsedUrl = parse_url($endpoint);
                $scheme = $parsedUrl['scheme'] ?? 'https';
                $host = $parsedUrl['host'] ?? $endpoint;
                $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
                $canonicalUri = '/'.trim($bucket, '/').'/';
                $url = "{$scheme}://{$host}{$port}{$canonicalUri}?{$queryString}";
            } else {
                $scheme = 'https';
                if ($usePathStyle) {
                    $host = $region === 'us-east-1' ? 's3.amazonaws.com' : "s3.{$region}.amazonaws.com";
                    $canonicalUri = '/'.trim($bucket, '/').'/';
                } else {
                    $host = $region === 'us-east-1' ? "{$bucket}.s3.amazonaws.com" : "{$bucket}.s3.{$region}.amazonaws.com";
                    $canonicalUri = '/';
                }
                $url = "{$scheme}://{$host}{$canonicalUri}?{$queryString}";
            }

            // AWS SigV4 dates
            $amzDate = gmdate('Ymd\THis\Z');
            $dateStamp = gmdate('Ymd');

            // Hash empty payload
            $payloadHash = hash('sha256', '');

            // Canonical headers
            $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
            $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

            // Canonical Request
            $canonicalRequest = implode("\n", [
                $method,
                $canonicalUri,
                $queryString,
                $canonicalHeaders,
                $signedHeaders,
                $payloadHash,
            ]);

            // String to Sign
            $algorithm = 'AWS4-HMAC-SHA256';
            $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
            $stringToSign = implode("\n", [
                $algorithm,
                $amzDate,
                $credentialScope,
                hash('sha256', $canonicalRequest),
            ]);

            // Calculate signing key
            $kSecret = 'AWS4'.$secret;
            $kDate = hash_hmac('sha256', $dateStamp, $kSecret, true);
            $kRegion = hash_hmac('sha256', $region, $kDate, true);
            $kService = hash_hmac('sha256', $service, $kRegion, true);
            $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

            // Calculate Signature
            $signature = hash_hmac('sha256', $stringToSign, $kSigning);

            $authorizationHeader = "{$algorithm} Credential={$key}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

            $response = Http::withoutVerifying()->withHeaders([
                'Host' => $host,
                'x-amz-date' => $amzDate,
                'x-amz-content-sha256' => $payloadHash,
                'Authorization' => $authorizationHeader,
            ])->timeout(8)->get($url);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $status = $response->status();
            $body = $response->body();

            $errorCode = '';
            $errorMessage = '';
            if (! empty($body)) {
                try {
                    $xml = @simplexml_load_string($body);
                    if ($xml !== false) {
                        $errorCode = (string) ($xml->Code ?? '');
                        $errorMessage = (string) ($xml->Message ?? '');
                    }
                } catch (\Throwable) {
                    // ignore XML parsing errors
                }
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to Amazon S3 / R2! Bucket '{$bucket}' in region '{$region}' is accessible and authenticated.",
                    'latency_ms' => $latencyMs,
                    'status' => $status,
                ];
            }

            if ($status === 403) {
                $detail = $errorMessage ? " ({$errorMessage})" : '';
                if ($errorCode === 'SignatureDoesNotMatch') {
                    return [
                        'success' => false,
                        'message' => "Signature Mismatch (HTTP 403). Check that your AWS Secret Access Key and Region are correct.{$detail}",
                        'latency_ms' => $latencyMs,
                        'status' => $status,
                    ];
                }

                if ($errorCode === 'AccessDenied') {
                    return [
                        'success' => false,
                        'message' => "Access Denied (HTTP 403). Credentials are valid, but the IAM policy lacks 's3:ListBucket' permission on bucket '{$bucket}'.{$detail}",
                        'latency_ms' => $latencyMs,
                        'status' => $status,
                    ];
                }

                return [
                    'success' => false,
                    'message' => "Authentication Failed (HTTP 403 Access Denied). Check that your AWS Access Key ID, Secret Key, and IAM bucket permissions are valid.{$detail}",
                    'latency_ms' => $latencyMs,
                    'status' => $status,
                ];
            }

            if ($status === 404) {
                return [
                    'success' => false,
                    'message' => "Bucket Not Found (HTTP 404). Bucket '{$bucket}' does not exist or is not reachable in region '{$region}'.",
                    'latency_ms' => $latencyMs,
                    'status' => $status,
                ];
            }

            if ($status === 301) {
                $actualRegion = $response->header('x-amz-bucket-region');
                $hint = $actualRegion ? " Bucket resides in region '{$actualRegion}'." : '';

                return [
                    'success' => false,
                    'message' => "Region Mismatch (HTTP 301 Permanent Redirect).{$hint}",
                    'latency_ms' => $latencyMs,
                    'status' => $status,
                ];
            }

            return [
                'success' => false,
                'message' => "S3 Response HTTP {$status}".($errorCode ? " [{$errorCode}]" : '').': '.Str::limit(strip_tags($errorMessage ?: $body), 160),
                'latency_ms' => $latencyMs,
                'status' => $status,
            ];
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => 'Connection Exception: '.$e->getMessage(),
                'latency_ms' => $latencyMs,
            ];
        }
    }
}
