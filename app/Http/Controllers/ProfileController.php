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
        return view('profile.index', compact('user'));
    }

    /**
     * Update contact and profile particulars.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048', // 2MB max
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;
        if (!empty($validated['title'])) {
            $user->title = $validated['title'];
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = Storage::url($path);
        }

        $user->save();

        return back()->with('success', 'Profile details updated successfully.');
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

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password successfully updated. Your account is secured.');
    }
}
