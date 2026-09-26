<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user profile and security preferences.
     */
    public function show(): View
    {
        $user = Auth::user()->load(['roleRelation', 'groups', 'firm']);

        $availablePracticeAreas = config('legal.practice_areas', [
            'Commercial Litigation & Arbitration',
            'Corporate & Insolvency (IBC / NCLT)',
            'Criminal Defense & Bail Applications',
            'Banking, Cheque Bounce (Sec 138 NI Act) & DRT',
            'Constitutional & Writ Petitions (Art 226/32)',
            'Intellectual Property & Trademark Disputes',
            'Taxation, Customs & GST Appeals',
            'Real Estate, RERA & Property Disputes',
            'Family Law & Matrimonial Matters',
            'Labor & Industrial Disputes',
        ]);

        $roles = [
            'managing_attorney' => 'Managing Attorney',
            'attorney' => 'Attorney',
            'associate' => 'Associate',
            'paralegal' => 'Paralegal',
            'legal_assistant' => 'Legal Assistant',
            'staff' => 'Staff',
        ];

        $timezones = [
            'Asia/Kolkata' => 'Asia/Kolkata (IST, UTC+05:30)',
            'UTC' => 'UTC (Coordinated Universal Time)',
            'Asia/Dubai' => 'Asia/Dubai (GST, UTC+04:00)',
            'Europe/London' => 'Europe/London (GMT/BST, UTC+00:00)',
            'America/New_York' => 'America/New_York (EST/EDT, UTC-05:00)',
            'America/Chicago' => 'America/Chicago (CST/CDT, UTC-06:00)',
            'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT, UTC-08:00)',
            'Asia/Singapore' => 'Asia/Singapore (SGT, UTC+08:00)',
        ];

        return view('profile.index', compact('user', 'availablePracticeAreas', 'roles', 'timezones'));
    }

    /**
     * Update contact and profile particulars.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'secondary_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:managing_attorney,attorney,associate,paralegal,legal_assistant,staff,partner,superadmin',
            'department' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:100',
            'bar_number' => 'nullable|string|max:100',
            'jurisdiction' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'practice_areas' => 'nullable',
            'avatar' => 'nullable|image|max:2048', // 2MB max
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->secondary_email = $validated['secondary_email'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->title = $validated['title'] ?? null;

        if (! empty($validated['role'])) {
            $user->role = $validated['role'];
        }

        $user->department = $validated['department'] ?? null;
        $user->office_location = $validated['office_location'] ?? null;
        $user->timezone = $validated['timezone'] ?? ($user->timezone ?? 'Asia/Kolkata');
        $user->bar_number = $validated['bar_number'] ?? null;
        $user->jurisdiction = $validated['jurisdiction'] ?? null;
        $user->admission_date = $validated['admission_date'] ?? null;

        if (isset($validated['practice_areas'])) {
            $areas = is_array($validated['practice_areas'])
                ? $validated['practice_areas']
                : array_map('trim', explode(',', (string) $validated['practice_areas']));
            $user->practice_areas = array_values(array_filter($areas));
        } else {
            $user->practice_areas = [];
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = Storage::url($path);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile and professional credentials updated successfully.');
    }

    /**
     * Change account password with current password verification.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password successfully updated. Your account is secured.');
    }

    /**
     * Handle profile picture upload.
     */
    public function changeAvatar(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => Storage::url($path)]);

        return back()->with('success', 'Profile picture updated successfully.');
    }
}
