<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PDO;
use PDOException;

class EnvironmentManager
{
    protected string $envPath;
    protected string $backupDir;

    public const MASKED_VALUE = '••••••••';

    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->backupDir = storage_path('app/env-backups');
    }

    /**
     * Read the raw .env file into key-value pairs.
     */
    public function getRawVariables(): array
    {
        if (!File::exists($this->envPath)) {
            return [];
        }

        $lines = file($this->envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $variables = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Strip surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $variables[$key] = $value;
            }
        }

        return $variables;
    }

    /**
     * Get categorized and safely masked configuration.
     */
    public function getCategorizedSettings(): array
    {
        $vars = $this->getRawVariables();

        $maskIfSet = function (string $key) use ($vars): string {
            return !empty($vars[$key]) ? self::MASKED_VALUE : '';
        };

        return [
            'storage' => [
                'FILESYSTEM_DISK' => $vars['FILESYSTEM_DISK'] ?? 'local',
                'AWS_ACCESS_KEY_ID' => $vars['AWS_ACCESS_KEY_ID'] ?? '',
                'AWS_SECRET_ACCESS_KEY' => $maskIfSet('AWS_SECRET_ACCESS_KEY'),
                'AWS_HAS_SECRET' => !empty($vars['AWS_SECRET_ACCESS_KEY']),
                'AWS_DEFAULT_REGION' => $vars['AWS_DEFAULT_REGION'] ?? 'ap-south-1',
                'AWS_BUCKET' => $vars['AWS_BUCKET'] ?? '',
                'AWS_ENDPOINT' => $vars['AWS_ENDPOINT'] ?? '',
                'AWS_USE_PATH_STYLE_ENDPOINT' => $vars['AWS_USE_PATH_STYLE_ENDPOINT'] ?? 'false',
            ],
            'database' => [
                'DB_CONNECTION' => $vars['DB_CONNECTION'] ?? 'sqlite',
                'DB_HOST' => $vars['DB_HOST'] ?? '127.0.0.1',
                'DB_PORT' => $vars['DB_PORT'] ?? '3306',
                'DB_DATABASE' => $vars['DB_DATABASE'] ?? database_path('database.sqlite'),
                'DB_USERNAME' => $vars['DB_USERNAME'] ?? '',
                'DB_PASSWORD' => $maskIfSet('DB_PASSWORD'),
                'DB_HAS_PASSWORD' => !empty($vars['DB_PASSWORD']),
            ],
            'mail' => [
                'MAIL_MAILER' => $vars['MAIL_MAILER'] ?? 'log',
                'MAIL_HOST' => $vars['MAIL_HOST'] ?? 'smtp.brevo.com',
                'MAIL_PORT' => $vars['MAIL_PORT'] ?? '587',
                'MAIL_USERNAME' => $vars['MAIL_USERNAME'] ?? '',
                'MAIL_PASSWORD' => $maskIfSet('MAIL_PASSWORD'),
                'MAIL_HAS_PASSWORD' => !empty($vars['MAIL_PASSWORD']),
                'MAIL_ENCRYPTION' => $vars['MAIL_ENCRYPTION'] ?? 'tls',
                'MAIL_FROM_ADDRESS' => $vars['MAIL_FROM_ADDRESS'] ?? 'chambers@sharmalegal.in',
                'MAIL_FROM_NAME' => $vars['MAIL_FROM_NAME'] ?? 'Sharma & Associates',
            ],
            'app' => [
                'APP_NAME' => $vars['APP_NAME'] ?? 'Law Firm Management',
                'APP_ENV' => $vars['APP_ENV'] ?? 'local',
                'APP_DEBUG' => $vars['APP_DEBUG'] ?? 'true',
                'APP_URL' => $vars['APP_URL'] ?? 'http://localhost:8000',
                'LEGAL_APP_NAME' => $vars['LEGAL_APP_NAME'] ?? 'Sharma & Associates, Advocates',
                'LEGAL_CURRENCY_SYMBOL' => $vars['LEGAL_CURRENCY_SYMBOL'] ?? '₹',
                'LEGAL_CURRENCY_CODE' => $vars['LEGAL_CURRENCY_CODE'] ?? 'INR',
                'APP_TIMEZONE' => $vars['APP_TIMEZONE'] ?? 'Asia/Kolkata',
            ],
        ];
    }

    /**
     * Create an automated backup of .env.
     */
    public function createBackup(): ?string
    {
        if (!File::exists($this->envPath)) {
            return null;
        }

        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }

        $filename = '.env.backup_' . date('Ymd_His');
        $dest = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        File::copy($this->envPath, $dest);

        // Keep last 20 backups
        $this->pruneBackups();

        return $filename;
    }

    /**
     * Prune backups older than the most recent 20.
     */
    protected function pruneBackups(): void
    {
        $files = File::glob($this->backupDir . DIRECTORY_SEPARATOR . '.env.backup_*');
        if (count($files) > 20) {
            usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
            $toDelete = array_slice($files, 20);
            foreach ($toDelete as $oldFile) {
                File::delete($oldFile);
            }
        }
    }

    /**
     * List all available .env backups.
     */
    public function listBackups(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::glob($this->backupDir . DIRECTORY_SEPARATOR . '.env.backup_*');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'created_at' => date('d M Y, h:i:s A', filemtime($file)),
                'timestamp' => filemtime($file),
                'size' => round(filesize($file) / 1024, 2) . ' KB',
            ];
        }

        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Restore a specific backup.
     */
    public function restoreBackup(string $filename): bool
    {
        $sanitized = basename($filename);
        $source = $this->backupDir . DIRECTORY_SEPARATOR . $sanitized;

        if (!File::exists($source)) {
            return false;
        }

        // Backup current before restoring
        $this->createBackup();

        File::copy($source, $this->envPath);
        $this->clearCaches();

        return true;
    }

    /**
     * Update environment variables safely.
     */
    public function update(array $newValues): bool
    {
        if (!File::exists($this->envPath)) {
            return false;
        }

        // Always create a backup first
        $this->createBackup();

        $content = File::get($this->envPath);

        // Filter out masked values so we don't overwrite real secrets with bullets
        $filteredValues = [];
        foreach ($newValues as $key => $val) {
            if ($val === self::MASKED_VALUE || (is_null($val) && str_ends_with($key, 'PASSWORD'))) {
                continue; // Do not overwrite existing secret
            }
            $filteredValues[$key] = $val;
        }

        foreach ($filteredValues as $key => $value) {
            $key = trim($key);
            $formattedValue = $this->formatEnvValue($value);

            // Check if key already exists in content (active or commented)
            $pattern = "/^(#\s*)?" . preg_quote($key, '/') . "=.*$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
            } else {
                // Append key to end
                $content = rtrim($content) . "\n{$key}={$formattedValue}\n";
            }
        }

        File::put($this->envPath, $content, true);

        $this->clearCaches();

        return true;
    }

    /**
     * Format environment value with quotes if needed.
     */
    protected function formatEnvValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return '';
        }

        $value = (string) $value;

        // Quote string if it contains spaces, hashes, quotes, or special characters
        if (preg_match('/\s|#|\$|"|\'/', $value) || empty($value)) {
            $escaped = str_replace('"', '\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }

    /**
     * Clear Laravel configuration and application cache.
     */
    public function clearCaches(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (Exception $e) {
            // Silently ignore if running in restricted mode
        }
    }

    /**
     * Standalone test connection for Database.
     */
    public function testDatabase(array $params): array
    {
        $driver = $params['driver'] ?? 'sqlite';
        $host = $params['host'] ?? '127.0.0.1';
        $port = $params['port'] ?? ($driver === 'pgsql' ? '5432' : '3306');
        $database = $params['database'] ?? '';
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        // If password is placeholder or empty, fallback to existing stored password
        if ($password === self::MASKED_VALUE || empty($password)) {
            $raw = $this->getRawVariables();
            $password = $raw['DB_PASSWORD'] ?? '';
        }

        try {
            if ($driver === 'sqlite') {
                $path = empty($database) ? database_path('database.sqlite') : $database;
                if (!File::exists($path)) {
                    $dir = dirname($path);
                    if (!File::isWritable($dir)) {
                        return [
                            'success' => false,
                            'message' => "SQLite directory is not writable: {$dir}",
                        ];
                    }
                }
                $pdo = new PDO("sqlite:" . $path);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->query('SELECT sqlite_version() as version');
                $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'] ?? 'Unknown';

                return [
                    'success' => true,
                    'message' => "SQLite connected successfully! Engine Version: {$version}. File: {$path}",
                ];
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                $options = [
                    PDO::ATTR_TIMEOUT => 4,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ];
                $pdo = new PDO($dsn, $username, $password, $options);
                $stmt = $pdo->query('SELECT VERSION() as version');
                $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'] ?? 'Unknown';

                return [
                    'success' => true,
                    'message' => "MySQL connected successfully! Server Version: {$version}. Database: {$database}",
                ];
            }

            if ($driver === 'pgsql') {
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
                $options = [
                    PDO::ATTR_TIMEOUT => 4,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ];
                $pdo = new PDO($dsn, $username, $password, $options);
                $stmt = $pdo->query('SELECT version()');
                $version = $stmt->fetchColumn();

                return [
                    'success' => true,
                    'message' => "PostgreSQL (Supabase/RDS) connected successfully! " . substr($version, 0, 40) . "...",
                ];
            }

            return [
                'success' => false,
                'message' => "Unsupported database driver: {$driver}",
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => "Connection Failed: " . $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Standalone test connection for Cloud Storage / AWS S3.
     */
    public function testS3(array $params): array
    {
        $key = $params['key'] ?? '';
        $secret = $params['secret'] ?? '';
        $region = $params['region'] ?? 'ap-south-1';
        $bucket = $params['bucket'] ?? '';
        $endpoint = $params['endpoint'] ?? '';

        if ($secret === self::MASKED_VALUE || empty($secret)) {
            $raw = $this->getRawVariables();
            $secret = $raw['AWS_SECRET_ACCESS_KEY'] ?? '';
        }

        if (empty($key) || empty($secret) || empty($bucket)) {
            return [
                'success' => false,
                'message' => 'AWS Access Key ID, Secret Access Key, and Bucket Name are required.',
            ];
        }

        $host = empty($endpoint) ? "s3.{$region}.amazonaws.com" : (parse_url($endpoint, PHP_URL_HOST) ?? $endpoint);

        $fp = @fsockopen($host, 443, $errno, $errstr, 4);
        if (!$fp) {
            return [
                'success' => false,
                'message' => "Unable to reach S3 host {$host}: {$errstr} ({$errno})",
            ];
        }
        fclose($fp);

        return [
            'success' => true,
            'message' => "S3 Endpoint ({$host}) is reachable! Bucket: {$bucket}, Region: {$region}.",
        ];
    }

    /**
     * Standalone test connection for SMTP Email.
     */
    public function testMail(array $params): array
    {
        $host = $params['host'] ?? 'smtp.brevo.com';
        $port = (int) ($params['port'] ?? 587);

        if (empty($host)) {
            return [
                'success' => false,
                'message' => 'SMTP Host is required.',
            ];
        }

        $fp = @fsockopen($host, $port, $errno, $errstr, 4);
        if (!$fp) {
            return [
                'success' => false,
                'message' => "Cannot connect to SMTP server {$host}:{$port} - {$errstr} ({$errno})",
            ];
        }

        $response = fgets($fp, 512);
        fclose($fp);

        return [
            'success' => true,
            'message' => "SMTP server connected! Banner: " . trim($response),
        ];
    }

    /**
     * Run database migrations safely.
     */
    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Migrations executed successfully!',
                'output' => trim($output),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'output' => $e->getTraceAsString(),
            ];
        }
    }
}
