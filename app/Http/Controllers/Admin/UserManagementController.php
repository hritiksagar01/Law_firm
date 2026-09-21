<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Firm;
use App\Models\SignInHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
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
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'firm_id' => $validated['firm_id'],
            'role' => $validated['role'],
            'name' => $validated['name'],
            'first_name' => $validated['first_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'surname' => $validated['surname'] ?? null,
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'title' => $validated['title'] ?? null,
            'password' => Hash::make(Str::random(16)),
            'status' => 'active',
        ]);

        AuditLog::create([
            'firm_id' => $validated['firm_id'],
            'user_id' => auth()->id(),
            'action' => 'user.invited',
            'action_label' => 'Invited user',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$validated['name']}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Invited {$validated['name']} ({$validated['email']}) to firm",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Invitation sent to {$validated['name']} ({$validated['email']}).");
    }

    /**
     * Display user detail and configuration.
     */
    public function show(User $user): View
    {
        $user->load('firm', 'roleRelation');

        $signInHistories = SignInHistory::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->latest()
            ->take(15)
            ->get();

        $auditLogs = AuditLog::where('user_id', $user->id)
            ->orWhere('actor_email', $user->email)
            ->latest()
            ->take(15)
            ->get();

        $activeSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->get();

        $firms = Firm::orderBy('name')->get(['id', 'name']);

        return view('admin.users.show', compact('user', 'signInHistories', 'auditLogs', 'activeSessions', 'firms'));
    }

    /**
     * Update user details and profile info.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'firm_id' => 'nullable|exists:firms,id',
            'role' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended,invited',
        ]);

        $oldValues = $user->only(['name', 'first_name', 'middle_name', 'surname', 'username', 'email', 'phone', 'mobile', 'title', 'firm_id', 'role', 'status']);

        $user->update($validated);

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'user.updated',
            'action_label' => 'Updated user profile',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Updated user profile details for {$user->name}",
            'previous_value' => $oldValues,
            'new_value' => $user->only(array_keys($oldValues)),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Profile details for {$user->name} have been updated.");
    }

    /**
     * Explicitly update user status (activate / deactivate / suspend).
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $validated['status']]);

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'user.status_changed',
            'action_label' => 'Changed user status',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Changed status of {$user->name} from {$oldStatus} to {$validated['status']}",
            'previous_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $validated['status']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Account status for {$user->name} updated to {$validated['status']}.");
    }

    /**
     * Resend invitation link to user.
     */
    public function resendInvitation(Request $request, User $user): RedirectResponse
    {
        $user->touch();

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'user.invitation_resent',
            'action_label' => 'Resent invitation',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Resent onboarding invitation link to {$user->email}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Invitation link has been resent to {$user->email}.");
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
        $oldRole = $user->role;
        $user->update(['role' => $newRole]);

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'user.role_changed',
            'action_label' => 'Changed user role',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Changed role for {$user->name} from {$oldRole} to {$newRole}",
            'previous_value' => ['role' => $oldRole],
            'new_value' => ['role' => $newRole],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Role for {$user->name} updated successfully.");
    }

    /**
     * Send one-hour password reset link.
     */
    public function sendPasswordReset(Request $request, User $user): RedirectResponse
    {
        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'auth.password_reset_sent',
            'action_label' => 'Sent password reset link',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Sent password reset link to {$user->email}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Emails a one-hour reset link to {$user->email}. Link has been sent.");
    }

    /**
     * Sign out user from all devices (force logout).
     */
    public function signOutEverywhere(Request $request, User $user): RedirectResponse
    {
        $user->update(['remember_token' => Str::random(60)]);
        DB::table('sessions')->where('user_id', $user->id)->delete();

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'auth.force_logout',
            'action_label' => 'Force logged out sessions',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Terminated all active device sessions for {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "All active sessions for {$user->name} have been ended.");
    }

    /**
     * Toggle or disable user account.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $newStatus = ($user->status === 'active') ? 'suspended' : 'active';
        $oldStatus = $user->status;
        $user->update(['status' => $newStatus]);

        $label = ($newStatus === 'active') ? 'reactivated' : 'disabled';

        AuditLog::create([
            'firm_id' => $user->firm_id,
            'user_id' => auth()->id(),
            'action' => 'user.status_toggled',
            'action_label' => ($newStatus === 'active') ? 'Reactivated user account' : 'Disabled user account',
            'actor_name' => auth()->user()?->name ?? 'Platform Superadmin',
            'actor_email' => auth()->user()?->email,
            'record_type' => "User account · {$user->name}",
            'module' => 'Administration',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Toggled account for {$user->name} from {$oldStatus} to {$newStatus}",
            'previous_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $newStatus],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Account for {$user->name} has been {$label}.");
    }
}
