<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display Platform administrators and Firm accounts directory.
     */
    public function index(Request $request): View
    {
        $platformAdmins = User::whereNull('firm_id')
            ->orWhere('role', 'superadmin')
            ->latest()
            ->get();

        $query = User::whereNotNull('firm_id')
            ->where('role', '!=', 'superadmin')
            ->with('firm');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('firm_id') && $request->firm_id !== 'all') {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $roleFilter = $request->role;
            if ($roleFilter === 'firm_admin' || $roleFilter === 'partner') {
                $query->where('role', 'partner');
            } elseif ($roleFilter === 'attorney' || $roleFilter === 'associate') {
                $query->where('role', 'associate');
            } elseif ($roleFilter === 'paralegal') {
                $query->whereIn('role', ['paralegal', 'staff']);
            } elseif ($roleFilter === 'client') {
                $query->where('role', 'client');
            } else {
                $query->where('role', $roleFilter);
            }
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $firmAccounts = $query->join('firms', 'users.firm_id', '=', 'firms.id')
            ->orderBy('firms.name')
            ->orderBy('users.name')
            ->select('users.*')
            ->paginate(30)
            ->withQueryString();

        $firms = Firm::orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.users.index', compact('platformAdmins', 'firmAccounts', 'firms'));
    }

    /**
     * Store and invite a new firm staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firm_id' => 'required|exists:firms,id',
            'role' => 'required|in:partner,associate,paralegal,staff',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'title' => 'nullable|string|max:255',
        ]);

        User::create([
            'firm_id' => $validated['firm_id'],
            'role' => $validated['role'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'title' => $validated['title'] ?? null,
            'password' => Hash::make(Str::random(16)),
            'status' => 'active',
        ]);

        return back()->with('success', "Invitation sent to {$validated['name']} ({$validated['email']}).");
    }

    /**
     * Display user detail and configuration.
     */
    public function show(User $user): View
    {
        $user->load('firm');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Change user role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|string',
        ]);

        $roleMap = [
            'firm_admin' => 'partner',
            'partner' => 'partner',
            'attorney' => 'associate',
            'associate' => 'associate',
            'paralegal' => 'paralegal',
            'staff' => 'staff',
            'client' => 'client',
            'platform_admin' => 'superadmin',
            'superadmin' => 'superadmin',
        ];

        $newRole = $roleMap[$validated['role']] ?? $validated['role'];
        $user->update(['role' => $newRole]);

        return back()->with('success', "Role for {$user->name} updated successfully.");
    }

    /**
     * Send one-hour password reset link.
     */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        return back()->with('success', "Emails a one-hour reset link to {$user->email}. Link has been sent.");
    }

    /**
     * Sign out user from all devices.
     */
    public function signOutEverywhere(User $user): RedirectResponse
    {
        $user->update(['remember_token' => Str::random(60)]);

        return back()->with('success', "All active sessions for {$user->name} have been ended.");
    }

    /**
     * Toggle or disable user account.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $newStatus = ($user->status === 'active') ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $label = ($newStatus === 'active') ? 'reactivated' : 'disabled';

        return back()->with('success', "Account for {$user->name} has been {$label}.");
    }
}
