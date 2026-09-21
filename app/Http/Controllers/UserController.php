<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display personnel & advocate directory.
     */
    public function index(Request $request): View
    {
        $firmId = Auth::user()->firm_id;

        $query = User::where('firm_id', $firmId)
            ->with(['roleRelation', 'groups'])
            ->withCount(['leadMatters', 'tasks']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $roleFilter = $request->role;
            $query->where(function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter)
                    ->orWhereHas('roleRelation', function ($sub) use ($roleFilter) {
                        $sub->where('slug', $roleFilter);
                    });
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        $roles = Role::where(function ($q) use ($firmId) {
            $q->whereNull('firm_id')->orWhere('firm_id', $firmId);
        })->get();

        $groups = UserGroup::where('firm_id', $firmId)->withCount('users')->get();

        return view('users.index', compact('users', 'roles', 'groups'));
    }

    /**
     * Show form to add an advocate or staff member.
     */
    public function create(): View
    {
        $firmId = Auth::user()->firm_id;

        $roles = Role::where(function ($q) use ($firmId) {
            $q->whereNull('firm_id')->orWhere('firm_id', $firmId);
        })->where('slug', '!=', 'superadmin')->get();

        $groups = UserGroup::where('firm_id', $firmId)->get();

        return view('users.create', compact('roles', 'groups'));
    }

    /**
     * Store new personnel.
     */
    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:user_groups,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user = User::create([
            'firm_id' => $firmId,
            'role_id' => $role->id,
            'role' => $role->slug,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'title' => $validated['title'] ?? $role->name,
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
        ]);

        if (! empty($validated['groups'])) {
            $user->groups()->sync($validated['groups']);
        }

        return redirect()->route('users.index')
            ->with('success', "Team member '{$user->name}' was successfully added to chambers.");
    }

    /**
     * Show form to edit user details.
     */
    public function edit(User $user): View
    {
        $firmId = Auth::user()->firm_id;

        if ($user->firm_id !== $firmId) {
            abort(403, 'Unauthorized access to user from another firm.');
        }

        $roles = Role::where(function ($q) use ($firmId) {
            $q->whereNull('firm_id')->orWhere('firm_id', $firmId);
        })->where('slug', '!=', 'superadmin')->get();

        $groups = UserGroup::where('firm_id', $firmId)->get();
        $assignedGroupIds = $user->groups()->pluck('user_groups.id')->toArray();

        return view('users.edit', compact('user', 'roles', 'groups', 'assignedGroupIds'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($user->firm_id !== $firmId) {
            abort(403, 'Unauthorized access to user from another firm.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'role_id' => 'required|exists:roles,id',
            'title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,suspended,inactive',
            'password' => 'nullable|string|min:8',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:user_groups,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $role->id,
            'role' => $role->slug,
            'title' => $validated['title'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (isset($validated['groups'])) {
            $user->groups()->sync($validated['groups']);
        } else {
            $user->groups()->detach();
        }

        return redirect()->route('users.index')
            ->with('success', "Team member '{$user->name}' was updated successfully.");
    }

    /**
     * Toggle user status (active/suspended).
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($user->firm_id !== $firmId) {
            abort(403);
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $label = ucfirst($newStatus);

        return back()->with('success', "User '{$user->name}' is now {$label}.");
    }
}
