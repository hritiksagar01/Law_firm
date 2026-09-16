<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(EnvironmentManager $envManager): View
    {
        $settings = $envManager->getCategorizedSettings();
        $backups = $envManager->listBackups();
        $rawEnv = file_exists(base_path('.env')) ? file_get_contents(base_path('.env')) : '';

        return view('admin.settings.environment', compact('settings', 'backups', 'rawEnv'));
    }

    public function update(Request $request, EnvironmentManager $envManager): RedirectResponse
    {
        if ($request->has('raw_env_content')) {
            $rawContent = $request->input('raw_env_content');
            $envManager->createBackup();
            file_put_contents(base_path('.env'), $rawContent, LOCK_EX);
            $envManager->clearCaches();

            return redirect()->route('admin.settings.environment')
                ->with('success', 'Environment file updated from raw editor and caches refreshed.');
        }

        $allowedKeys = [
            // Storage / S3
            'FILESYSTEM_DISK',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_ENDPOINT',
            'AWS_USE_PATH_STYLE_ENDPOINT',
            // Database
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            // Email
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
            // App
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'APP_URL',
            'LEGAL_APP_NAME',
            'LEGAL_CURRENCY_SYMBOL',
            'LEGAL_CURRENCY_CODE',
        ];

        $payload = [];
        foreach ($allowedKeys as $key) {
            if ($request->has($key)) {
                $payload[$key] = $request->input($key);
            }
        }

        // Handle checkboxes if not present in request
        if ($request->has('_form_section') && $request->input('_form_section') === 'app') {
            $payload['APP_DEBUG'] = $request->has('APP_DEBUG') ? 'true' : 'false';
        }

        // Proactive Safety Shield: Verify database connection BEFORE saving to .env
        if ($request->input('_form_section') === 'database' && ! $request->boolean('force_save')) {
            $testResult = $envManager->testDatabase([
                'driver' => $request->input('DB_CONNECTION', 'sqlite'),
                'host' => $request->input('DB_HOST', ''),
                'port' => $request->input('DB_PORT', ''),
                'database' => $request->input('DB_DATABASE', ''),
                'username' => $request->input('DB_USERNAME', ''),
                'password' => $request->input('DB_PASSWORD', ''),
            ]);

            if (! $testResult['success']) {
                return redirect()->route('admin.settings.environment')
                    ->withInput()
                    ->with('error', "Database Connection Test Failed! Active configuration was NOT changed to protect site availability. Details: " . $testResult['message']);
            }
        }

        $envManager->update($payload);

        return redirect()->route('admin.settings.environment')
            ->with('success', 'Environment variables updated and configuration caches cleared successfully.');
    }

    public function testDatabase(Request $request, EnvironmentManager $envManager): JsonResponse
    {
        $result = $envManager->testDatabase($request->all());
        return response()->json($result);
    }

    public function testS3(Request $request, EnvironmentManager $envManager): JsonResponse
    {
        $result = $envManager->testS3($request->all());
        return response()->json($result);
    }

    public function testMail(Request $request, EnvironmentManager $envManager): JsonResponse
    {
        $result = $envManager->testMail($request->all());
        return response()->json($result);
    }

    public function runMigrations(EnvironmentManager $envManager): JsonResponse
    {
        $result = $envManager->runMigrations();
        return response()->json($result);
    }

    public function seedDatabase(EnvironmentManager $envManager): JsonResponse
    {
        $result = $envManager->seedDatabase();
        return response()->json($result);
    }

    public function restoreBackup(Request $request, EnvironmentManager $envManager): RedirectResponse
    {
        $request->validate(['filename' => 'required|string']);
        $filename = $request->input('filename');

        $restored = $envManager->restoreBackup($filename);

        if ($restored) {
            return redirect()->route('admin.settings.environment')
                ->with('success', "Environment restored successfully from backup: {$filename}");
        }

        return redirect()->route('admin.settings.environment')
            ->with('error', "Failed to restore backup: {$filename}");
    }
}
