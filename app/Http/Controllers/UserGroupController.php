<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserGroupController extends Controller
{
    /**
     * List practice groups and teams.
     */
    public function index(): View
    {
        $firmId = Auth::user()->firm_id;

        $groups = UserGroup::where('firm_id', $firmId)
            ->with(['users', 'permissions'])
            ->withCount('users')
            ->latest()
            ->get();

        $allUsers = User::where('firm_id', $firmId)->get();
        $permissions = Permission::all()->groupBy('module');

        return view('users.groups', compact('groups', 'allUsers', 'permissions'));
    }

    /**
     * Store new practice group.
     */
    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $group = UserGroup::create([
            'firm_id' => $firmId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#9F8349',
        ]);

        if (!empty($validated['user_ids'])) {
            $group->users()->sync($validated['user_ids']);
        }

        if (!empty($validated['permission_ids'])) {
            $group->permissions()->sync($validated['permission_ids']);
        }

        return redirect()->route('user-groups.index')
            ->with('success', "Practice team '{$group->name}' was created successfully.");
    }

    /**
     * Update practice group.
     */
    public function update(Request $request, UserGroup $userGroup): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($userGroup->firm_id !== $firmId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $userGroup->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#9F8349',
        ]);

        if (isset($validated['user_ids'])) {
            $userGroup->users()->sync($validated['user_ids']);
        } else {
            $userGroup->users()->detach();
        }

        if (isset($validated['permission_ids'])) {
            $userGroup->permissions()->sync($validated['permission_ids']);
        } else {
            $userGroup->permissions()->detach();
        }

        return redirect()->route('user-groups.index')
            ->with('success', "Practice team '{$userGroup->name}' was updated.");
    }

    /**
     * Delete practice group.
     */
    public function destroy(UserGroup $userGroup): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($userGroup->firm_id !== $firmId) {
            abort(403);
        }

        $name = $userGroup->name;
        $userGroup->delete();

        return redirect()->route('user-groups.index')
            ->with('success', "Practice team '{$name}' was deleted.");
    }
}
