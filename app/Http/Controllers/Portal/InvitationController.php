<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token): View
    {
        $client = Client::where('invitation_token', $token)->first();

        if (! $client || ! $client->hasActiveInvitation()) {
            abort(403, 'This portal invitation link is invalid, expired, or has already been used. Please contact your advocate to request a new invitation.');
        }

        return view('portal.invitation', compact('client', 'token'));
    }

    public function complete(Request $request, string $token): RedirectResponse
    {
        $client = Client::where('invitation_token', $token)->first();

        if (! $client || ! $client->hasActiveInvitation()) {
            abort(403, 'This portal invitation link is invalid, expired, or has already been used.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $client->user;

        if (! $user) {
            $user = User::firstOrCreate(
                ['email' => $client->email],
                [
                    'firm_id' => $client->firm_id,
                    'name' => $client->contact_person ?: $client->name,
                    'password' => Hash::make(str()->random(32)),
                    'role' => 'client',
                    'title' => 'Client Representative',
                    'phone' => $client->phone,
                ]
            );
        }

        $user->update([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'role' => 'client',
        ]);

        $client->update([
            'user_id' => $user->id,
            'portal_status' => 'active',
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ]);

        Auth::login($user);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Welcome to your Chambers Client Portal! Your login account is now active.');
    }
}
