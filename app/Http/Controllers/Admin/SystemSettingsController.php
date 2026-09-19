<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    /**
     * Display Platform System Settings.
     */
    public function index(): View
    {
        $settings = [
            'platform_name' => PlatformSetting::get('platform_name', config('app.name', 'Quire')),
            'support_email' => PlatformSetting::get('support_email', 'support@quire.example'),
            'maintenance_notice' => PlatformSetting::get('maintenance_notice', ''),
            'idle_timeout' => (int) PlatformSetting::get('idle_timeout', 12),
        ];

        $deployment = [
            'public_url' => config('app.url', 'https://lawfirm.pllatinum.me'),
            'file_storage' => 'Local disk (encrypted at rest by the server)',
            'demo_mode' => 'On — demo accounts listed on the sign-in page',
            'scheduled_reminders' => 'Protected by CRON_SECRET',
            'encryption_key' => config('app.key') ? 'APP_KEY configured' : 'Not configured',
        ];

        return view('admin.system.index', compact('settings', 'deployment'));
    }

    /**
     * Update Platform System Settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_name' => 'required|string|max:100',
            'support_email' => 'required|email|max:255',
            'maintenance_notice' => 'nullable|string|max:300',
            'idle_timeout' => 'required|integer|between:1,72',
        ]);

        PlatformSetting::set('platform_name', $validated['platform_name']);
        PlatformSetting::set('support_email', $validated['support_email']);
        PlatformSetting::set('maintenance_notice', $validated['maintenance_notice'] ?? '');
        PlatformSetting::set('idle_timeout', (int) $validated['idle_timeout']);

        return redirect()->route('admin.system.index')->with('success', 'System settings saved successfully.');
    }
}
