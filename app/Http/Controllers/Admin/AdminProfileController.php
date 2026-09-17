<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * GET /admin/profile
     * Display the User Profile Management form (PDF Page 4).
     */
    public function index(): View
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * PUT /admin/profile
     * Update personal details.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'secondary_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'phone_type' => 'nullable|string|in:Home,Mobile,Office,Fax',
            'id_type' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:100',
            'id_expiration' => 'nullable|date|after:today',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'street' => 'nullable|string|max:255',
            'user_city' => 'nullable|string|max:100',
            'user_state' => 'nullable|string|max:100',
            'user_country' => 'nullable|string|max:100',
            'user_postal_code' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('id_document')) {
            $path = $request->file('id_document')->store('admin_profiles/id_documents', 'public');
            $validated['id_document_path'] = $path;
        }
        unset($validated['id_document']);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * POST /admin/profile/change-password
     * Requires current password verification for security.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * POST /admin/profile/change-avatar
     * Handle profile picture upload.
     */
    public function changeAvatar(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('avatar')->store('admin_profiles/avatars', 'public');
        $user->update(['avatar_url' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }
}
