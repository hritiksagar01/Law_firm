<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            /** @var User $user */
            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', "Welcome to Platform Super Administrator Console, {$user->name}.");
            }

            if ($user->isClient()) {
                return redirect()->intended(route('portal.dashboard'))
                    ->with('success', "Welcome to your Client Portal, {$user->name}.");
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back to Chambers, {$user->name}.");
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our chambers records.'),
        ]);
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
        } catch (\Throwable $e) {
            // Ignore DB errors during logout
        }

        $request->session()->forget('is_super_admin');
        $request->session()->forget('super_admin_email');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been securely signed out of Chambers.');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return Auth::user()->isClient()
                ? redirect()->route('portal.dashboard')
                : redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'firm_name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'practice_area' => 'required|string|max:100',
            'bar_number' => 'nullable|string|max:100',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['firm_name']);
        if (\App\Models\Firm::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $firm = \App\Models\Firm::create([
            'name' => $validated['firm_name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'default_hourly_rate' => config('legal.default_hourly_rate', 7500.00),
            'currency' => config('legal.currency_code', 'INR'),
            'practice_areas' => [$validated['practice_area'], 'Commercial Litigation & Arbitration'],
        ]);

        $user = User::create([
            'firm_id' => $firm->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'partner',
            'title' => 'Senior Advocate & Managing Partner',
            'hourly_rate' => config('legal.default_hourly_rate', 7500.00),
            'phone' => '+91 98110 00000',
            'avatar_url' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=100&auto=format&fit=crop&q=80',
        ]);

        // Seed initial inaugural client & matter for immediate operation
        $client = \App\Models\Client::create([
            'firm_id' => $firm->id,
            'type' => 'corporate',
            'name' => 'Inaugural Enterprise Holdings Pvt Ltd',
            'contact_person' => 'Sunil Singhal',
            'email' => 'singhal@inauguralholdings.in',
            'phone' => '+91 98100 22334',
            'tax_id' => '07AAACI9982E1Z4',
            'trust_balance' => 100000.00,
            'status' => 'active',
        ]);

        $matter = \App\Models\Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'CS (COMM) ' . rand(100, 999) . '/' . date('Y'),
            'title' => 'Inaugural Enterprise Holdings v. Global Tech Consortium',
            'practice_area' => $validated['practice_area'],
            'court_name' => 'High Court of Delhi, New Delhi',
            'judge_name' => 'Hon. Justice Pratibha M. Singh',
            'stage' => 'Notice & Pleadings',
            'status' => 'active',
            'lead_attorney_id' => $user->id,
            'billing_type' => 'hourly',
            'budget' => 500000.00,
            'opened_at' => now()->toDateString(),
        ]);

        \App\Models\Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'title' => 'Court Vakalatnama & Initial Registry Filing Protocol',
            'priority' => 'urgent',
            'status' => 'todo',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return redirect()->route('dashboard')
            ->with('success', "Chambers established for {$firm->name}. Welcome to " . config('legal.app_name', 'Vennamraj Associates') . ", {$user->name}!");
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        return back()->with('status', 'If an active representation or counsel account exists for this email, an encrypted password reset dispatch has been sent.');
    }
}
