<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get a setting by key, with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting || $setting->value === null || $setting->value === '') {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): static
    {
        $encoded = is_null($value)
            ? null
            : (is_array($value) || is_object($value) || is_bool($value) ? json_encode($value) : (string) $value);

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded]
        );
    }

    /**
     * Get the resolved public URL for the platform logo.
     */
    public static function logoUrl(): string
    {
        $logo = static::get('platform_logo');

        if ($logo && is_string($logo) && trim($logo) !== '') {
            if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
                return $logo;
            }

            return asset(ltrim($logo, '/'));
        }

        return asset('logo.png');
    }

    /**
     * Get the configured platform name.
     */
    public static function platformName(): string
    {
        return (string) static::get('platform_name', config('legal.app_name', config('app.name', 'Vennamraj Associates')));
    }

    /**
     * Get the active storage driver ('local' or 's3').
     */
    public static function storageDriver(): string
    {
        return (string) static::get('storage_driver', config('filesystems.default', 'local'));
    }

    /**
     * Retrieve all public footer and CMS legal content values.
     *
     * @return array<string, mixed>
     */
    public static function footerData(): array
    {
        $appName = static::platformName();
        $supportEmail = (string) static::get('support_email', 'contact@vennamraj.com');

        return [
            'footer_headline' => (string) static::get('footer_headline', 'Enterprise Legal Chambers Practice Management Platform'),
            'footer_description' => (string) static::get('footer_description', 'High-security multi-tenant litigation dockets, client privilege isolation, and document repository for bar advocates and law practices.'),
            'footer_copyright' => (string) static::get('footer_copyright', '© '.date('Y').' '.$appName.'. All rights reserved.'),
            'about_headline' => (string) static::get('about_headline', 'About '.$appName),
            'about_content' => (string) static::get('about_content', "{$appName} is an institutional-grade legal practice and litigation management platform built for modern advocates, senior counsels, and premier law chambers.\n\nProviding strict data partition boundaries, end-to-end audit logging, Section 126/129 advocate-client privilege protection, court hearing calendar synchronization, certified legal document vaults, and transparent litigant portals."),
            'contact_headline' => (string) static::get('contact_headline', 'Chambers Registry & Grievance Redressal'),
            'contact_email' => (string) static::get('contact_email', $supportEmail),
            'contact_phone' => (string) static::get('contact_phone', '+91 11 2338 4567'),
            'contact_address' => (string) static::get('contact_address', "Chamber Block C-14, Lawyers Chambers Complex\nHigh Court of Delhi, Sher Shah Road\nNew Delhi, DL 110003, India"),
            'contact_hours' => (string) static::get('contact_hours', 'Monday – Saturday: 09:30 AM – 07:00 PM IST (Court Days)'),
            'privacy_headline' => (string) static::get('privacy_headline', 'Client Privilege & Legal Data Protection Policy'),
            'privacy_content' => (string) static::get('privacy_content', "1. ADVOCATE-CLIENT PRIVILEGE PROTECTION\nAll communications, client dockets, pleadings, work-product memos, and evidence documents uploaded to this platform are protected under Sections 126 through 129 of the Indian Evidence Act, 1872, and the Bar Council of India Standards of Professional Conduct. No privileged disclosure is shared with third parties without express client consent.\n\n2. TENANT ISOLATION & ACCESS CONTROL\nData between different law firms and client workspaces is segregated using cryptographically bound tenant isolation. Platform super administrators do not inspect confidential docket contents, case strategy memos, or unredacted client financial records.\n\n3. AUDIT TRAILS & INTEGRITY VERIFICATION\nEvery document access, download, status transition, and authentication event is immutably logged with client IP addresses, user agent telemetry, and ISO-8601 timestamps for regulatory compliance.\n\n4. STORAGE & CIPHER ENCRYPTION\nAll document payloads stored locally or in Amazon S3/Cloudflare R2 object storage are encrypted at rest with AES-256 and transmitted exclusively over Transport Layer Security (TLS 1.3)."),
            'terms_headline' => (string) static::get('terms_headline', 'Terms of Platform Service & Chambers Governance'),
            'terms_content' => (string) static::get('terms_content', "1. ACCEPTANCE OF ENGAGEMENT TERMS\nBy accessing this legal practice console or the litigant portal, advocates, staff, and clients agree to abide by these Terms of Service, applicable court rules, and lawful directions issued by registered practice chambers.\n\n2. AUTHENTIC COURT FILINGS & CERTIFICATION\nDocuments uploaded for court submissions, vakalatnamas, affidavits, and cause papers must represent authentic, un-tampered representations. Litigants uploading requested proofs are responsible for veracity and authenticity.\n\n3. ELECTRONIC SERVICE & NOTICE ACKNOWLEDGMENT\nNotices, hearing dates, court orders, and status updates delivered through this system are deemed officially received once delivered to the litigant's registered email or portal inbox.\n\n4. ACCOUNT SECURITY & CREDENTIAL SAFEGUARDING\nUsers are strictly responsible for safeguarding their login credentials and session tokens. Multi-factor authentication or single-device logins are strongly advised for all chamber personnel.\n\n5. JURISDICTION & GOVERNING LAW\nThese terms and platform usage agreements are governed exclusively by the laws of India. Any jurisdictional disputes shall be subject to the courts having territorial jurisdiction over the primary registered chambers."),
        ];
    }

    /**
     * Retrieve S3 and storage configuration settings.
     *
     * @return array<string, mixed>
     */
    public static function storageConfig(): array
    {
        return [
            'storage_driver' => (string) static::get('storage_driver', config('filesystems.default', 'local')),
            'aws_access_key_id' => (string) static::get('aws_access_key_id', config('filesystems.disks.s3.key', '')),
            'aws_secret_access_key' => (string) static::get('aws_secret_access_key', config('filesystems.disks.s3.secret', '')),
            'aws_default_region' => (string) static::get('aws_default_region', config('filesystems.disks.s3.region', 'ap-south-1')),
            'aws_bucket' => (string) static::get('aws_bucket', config('filesystems.disks.s3.bucket', '')),
            'aws_endpoint' => (string) static::get('aws_endpoint', config('filesystems.disks.s3.endpoint', '')),
            'aws_use_path_style_endpoint' => (bool) static::get('aws_use_path_style_endpoint', config('filesystems.disks.s3.use_path_style_endpoint', false)),
        ];
    }
}
