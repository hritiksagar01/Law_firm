<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DefaultDocumentCategory;
use App\Models\Permission;
use App\Models\PlatformSetting;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolesCategoriesController extends Controller
{
    /**
     * Standard ordered permission modules.
     */
    protected array $moduleOrder = [
        'Matters',
        'Clients',
        'Documents',
        'Communication',
        'Work',
        'Billing',
        'Administration',
    ];

    /**
     * Display Roles & Categories management.
     */
    public function index(): View
    {
        $allPermissions = Permission::all();

        // Group by module with prioritized ordering
        $groupedPermissions = [];
        foreach ($this->moduleOrder as $module) {
            $perms = $allPermissions->where('module', $module)->values();
            if ($perms->isNotEmpty()) {
                $groupedPermissions[$module] = $perms;
            }
        }
        // Include any additional modules not in the priority list
        foreach ($allPermissions->groupBy('module') as $module => $perms) {
            if (! isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = $perms;
            }
        }

        // Get system roles for Attorney and Paralegal
        $attorneyRole = Role::whereIn('slug', ['lawyer', 'attorney'])->whereNull('firm_id')->first();
        $paralegalRole = Role::where('slug', 'paralegal')->whereNull('firm_id')->first();

        $attorneyPermSlugs = $attorneyRole ? $attorneyRole->permissions->pluck('slug')->toArray() : [];
        $paralegalPermSlugs = $paralegalRole ? $paralegalRole->permissions->pluck('slug')->toArray() : [];

        $categories = DefaultDocumentCategory::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.roles.index', compact(
            'groupedPermissions',
            'attorneyPermSlugs',
            'paralegalPermSlugs',
            'categories'
        ));
    }

    /**
     * Update default role permissions for Attorney and Paralegal.
     */
    public function updatePermissions(Request $request): RedirectResponse
    {
        $attorneySlugs = (array) $request->input('attorney_permissions', []);
        $paralegalSlugs = (array) $request->input('paralegal_permissions', []);

        $attorneyRole = Role::firstOrCreate(
            ['slug' => 'lawyer', 'firm_id' => null],
            ['name' => 'Advocate / Attorney', 'is_system' => true]
        );

        $paralegalRole = Role::firstOrCreate(
            ['slug' => 'paralegal', 'firm_id' => null],
            ['name' => 'Paralegal / Research Associate', 'is_system' => true]
        );

        $attorneyPermIds = Permission::whereIn('slug', $attorneySlugs)->pluck('id')->toArray();
        $paralegalPermIds = Permission::whereIn('slug', $paralegalSlugs)->pluck('id')->toArray();

        $attorneyRole->permissions()->sync($attorneyPermIds);
        $paralegalRole->permissions()->sync($paralegalPermIds);

        PlatformSetting::set('default_role_permissions', [
            'attorney' => $attorneySlugs,
            'paralegal' => $paralegalSlugs,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Default role permissions updated successfully.');
    }

    /**
     * Store a new default document category.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        if (! isset($validated['sort_order']) || $validated['sort_order'] === null) {
            $maxOrder = DefaultDocumentCategory::max('sort_order');
            $validated['sort_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 0;
        }

        DefaultDocumentCategory::create($validated);

        return redirect()->route('admin.roles.index')->with('success', "Document category '{$validated['name']}' added successfully.");
    }

    /**
     * Update an existing default document category.
     */
    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'required|integer',
        ]);

        $category = DefaultDocumentCategory::findOrFail($id);
        $category->update($validated);

        return redirect()->route('admin.roles.index')->with('success', "Document category '{$category->name}' updated successfully.");
    }

    /**
     * Remove a default document category.
     */
    public function destroyCategory(int $id): RedirectResponse
    {
        $category = DefaultDocumentCategory::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.roles.index')->with('success', "Document category '{$name}' removed successfully.");
    }
}
