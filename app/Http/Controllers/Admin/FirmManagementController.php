<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FirmManagementController extends Controller
{
    /**
     * Display a listing of law firms.
     */
    public function index(Request $request): View
    {
        $query = Firm::withCount(['users', 'matters', 'documents', 'clients']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $firms = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Firm::count(),
            'active' => Firm::where('status', 'active')->count(),
            'inactive' => Firm::where('status', 'inactive')->count(),
            'total_users' => User::whereNotNull('firm_id')->count(),
        ];

        return view('admin.firms.index', compact('firms', 'stats'));
    }

    /**
     * Show form to provision a new law firm.
     */
    public function create(): View
    {
        $defaultPracticeAreas = [
            'Civil Litigation',
            'Criminal Law',
            'Corporate & Commercial',
            'Constitutional Law',
            'Family & Matrimonial',
            'Real Estate & Property',
            'Intellectual Property',
            'Taxation & Revenue',
            'Banking & Insolvency',
            'Labour & Employment',
        ];

        return view('admin.firms.create', compact('defaultPracticeAreas'));
    }

    /**
     * Store a newly created law firm and optionally its managing partner/admin user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:firms,slug',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'required|string|max:10',
            'default_hourly_rate' => 'nullable|numeric|min:0',
            'practice_areas' => 'nullable|array',
            'notes' => 'nullable|string',
            // Optional initial admin user
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|email|max:255|unique:users,email',
            'admin_password' => 'nullable|string|min:8',
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (Firm::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        DB::transaction(function () use ($validated, $slug, $request) {
            $firm = Firm::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? 'India',
                'postal_code' => $validated['postal_code'] ?? null,
                'currency' => $validated['currency'] ?? 'INR',
                'default_hourly_rate' => $validated['default_hourly_rate'] ?? 0,
                'practice_areas' => $validated['practice_areas'] ?? [],
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // If initial admin details provided, create the managing partner user
            if ($request->filled('admin_name') && $request->filled('admin_email') && $request->filled('admin_password')) {
                User::create([
                    'firm_id' => $firm->id,
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => Hash::make($validated['admin_password']),
                    'role' => 'partner',
                    'title' => 'Managing Partner / Firm Administrator',
                ]);
            }
        });

        return redirect()->route('admin.firms.index')
            ->with('success', "Law firm '{$validated['name']}' was successfully provisioned.");
    }

    /**
     * Display firm overview, metrics, and associated personnel.
     */
    public function show(Firm $firm): View
    {
        $firm->loadCount(['users', 'matters', 'documents', 'clients']);
        $users = $firm->users()->latest()->take(10)->get();
        $matters = $firm->matters()->with('client')->latest()->take(10)->get();
        $clients = $firm->clients()->latest()->take(10)->get();

        return view('admin.firms.show', compact('firm', 'users', 'matters', 'clients'));
    }

    /**
     * Show form to edit law firm details.
     */
    public function edit(Firm $firm): View
    {
        $defaultPracticeAreas = [
            'Civil Litigation',
            'Criminal Law',
            'Corporate & Commercial',
            'Constitutional Law',
            'Family & Matrimonial',
            'Real Estate & Property',
            'Intellectual Property',
            'Taxation & Revenue',
            'Banking & Insolvency',
            'Labour & Employment',
        ];

        return view('admin.firms.edit', compact('firm', 'defaultPracticeAreas'));
    }

    /**
     * Update law firm details.
     */
    public function update(Request $request, Firm $firm): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:100|unique:firms,slug,{$firm->id}",
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:active,inactive,suspended',
            'practice_areas' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $firm->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'postal_code' => $validated['postal_code'] ?? null,
            'currency' => $validated['currency'] ?? 'INR',
            'status' => $validated['status'],
            'practice_areas' => $validated['practice_areas'] ?? [],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.firms.show', $firm)
            ->with('success', "Law firm '{$firm->name}' details updated successfully.");
    }

    /**
     * Toggle firm active/inactive status.
     */
    public function toggleStatus(Firm $firm): RedirectResponse
    {
        $newStatus = $firm->status === 'active' ? 'inactive' : 'active';
        $firm->update(['status' => $newStatus]);

        $statusLabel = ucfirst($newStatus);
        return back()->with('success', "Law firm '{$firm->name}' is now marked as {$statusLabel}.");
    }

    /**
     * Delete firm if safe.
     */
    public function destroy(Firm $firm): RedirectResponse
    {
        if ($firm->matters()->count() > 0) {
            return back()->with('error', "Cannot delete '{$firm->name}' because it has active legal matters. Mark it as Inactive instead.");
        }

        $name = $firm->name;
        $firm->delete();

        return redirect()->route('admin.firms.index')
            ->with('success', "Law firm '{$name}' was deleted.");
    }
}
