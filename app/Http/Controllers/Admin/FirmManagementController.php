<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FirmManagementController extends Controller
{
    /**
     * Display a listing of law firms.
     */
    public function index(Request $request): View
    {
        $query = Firm::withCount(['users', 'matters', 'documents', 'clients'])
            ->with(['primaryAdmin']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
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
            'practice_type' => 'nullable|string|in:firm,individual',
            'display_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'registration_number' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100|unique:firms,slug',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'default_hourly_rate' => 'nullable|numeric|min:0',
            'practice_areas' => 'nullable|array',
            'notes' => 'nullable|string',
            // Optional initial admin user
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|email|max:255|unique:users,email',
            'admin_password' => 'nullable|string|min:8',
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Fallback for non-ascii names
        if (empty($slug)) {
            $slug = 'firm-'.rand(100, 999);
        }

        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (Firm::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $contactName = $validated['owner_name'] ?? ($validated['contact_name'] ?? null);
        $officialEmail = $validated['email'] ?? ($validated['owner_email'] ?? ($validated['admin_email'] ?? null));

        DB::transaction(function () use ($validated, $slug, $contactName, $officialEmail, $request) {
            $firm = Firm::create([
                'name' => $validated['name'],
                'practice_type' => $validated['practice_type'] ?? 'firm',
                'display_name' => $validated['display_name'] ?? $validated['name'],
                'contact_name' => $contactName,
                'registration_number' => $validated['registration_number'] ?? null,
                'slug' => $slug,
                'email' => $officialEmail,
                'phone' => $validated['phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? 'New Delhi',
                'state' => $validated['state'] ?? 'Delhi',
                'country' => $validated['country'] ?? 'India',
                'postal_code' => $validated['postal_code'] ?? null,
                'currency' => $validated['currency'] ?? 'INR',
                'timezone' => $validated['timezone'] ?? 'Asia/Kolkata',
                'accent_color' => '#24503f',
                'allow_client_signup' => true,
                'default_hourly_rate' => $validated['default_hourly_rate'] ?? 7500,
                'practice_areas' => $validated['practice_areas'] ?? ['Civil Litigation', 'Commercial & Corporate Law'],
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // If initial admin or owner details provided, create the managing partner/administrator user
            $adminEmail = $validated['admin_email'] ?? ($validated['owner_email'] ?? null);
            $adminName = $validated['admin_name'] ?? ($validated['owner_name'] ?? 'Managing Counsel');

            if ($adminEmail) {
                User::create([
                    'firm_id' => $firm->id,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($request->input('admin_password', '12345678')),
                    'role' => 'partner',
                    'title' => ($validated['practice_type'] ?? 'firm') === 'individual' ? 'Advocate / Sole Practitioner' : 'Senior Advocate & Managing Partner',
                ]);
            }
        });

        $typeLabel = ($validated['practice_type'] ?? 'firm') === 'individual' ? 'Advocate Practice' : 'Law firm';

        return redirect()->route('admin.firms.index')
            ->with('success', "{$typeLabel} '{$validated['name']}' was successfully provisioned.");
    }

    /**
     * Display firm overview, metrics, and associated personnel.
     */
    public function show(Firm $firm): View
    {
        $firm->loadCount(['users', 'matters', 'documents', 'clients', 'invoices', 'transactions']);
        $openMattersCount = $firm->matters()->where('status', '!=', 'closed')->count();
        $users = $firm->users()->latest()->get();
        $lawyers = $firm->users()->whereIn('role', ['partner', 'associate'])->latest()->get();
        $matters = $firm->matters()->with('client', 'leadAttorney')->latest()->take(10)->get();
        $clients = $firm->clients()->latest()->take(10)->get();

        // Count staff vs client portal accounts
        $staffCount = $firm->users()->whereIn('role', ['partner', 'associate', 'paralegal', 'staff'])->count();
        $clientPortalCount = $firm->users()->where('role', 'client')->count();

        // Calculate storage
        $storageBytes = (int) $firm->documents()->sum('file_size');
        $storageFormatted = $storageBytes > 1048576
            ? number_format($storageBytes / 1048576, 1).' MB'
            : ($storageBytes > 1024 ? number_format($storageBytes / 1024, 0).' KB' : '29 KB');

        return view('admin.firms.show', compact(
            'firm',
            'users',
            'lawyers',
            'matters',
            'clients',
            'openMattersCount',
            'staffCount',
            'clientPortalCount',
            'storageFormatted'
        ));
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
            'display_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'slug' => "nullable|string|max:100|unique:firms,slug,{$firm->id}",
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'accent_color' => 'nullable|string|max:20',
            'allow_client_signup' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,suspended',
            'practice_areas' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? $firm->display_name,
            'contact_name' => $validated['contact_name'] ?? $firm->contact_name,
            'email' => $validated['email'] ?? $firm->email,
            'phone' => $validated['phone'] ?? $firm->phone,
            'website' => $validated['website'] ?? $firm->website,
            'address' => $validated['address'] ?? $firm->address,
            'city' => $validated['city'] ?? $firm->city,
            'state' => $validated['state'] ?? $firm->state,
            'country' => $validated['country'] ?? $firm->country,
            'postal_code' => $validated['postal_code'] ?? $firm->postal_code,
            'currency' => $validated['currency'] ?? $firm->currency,
            'timezone' => $validated['timezone'] ?? $firm->timezone,
            'accent_color' => $validated['accent_color'] ?? $firm->accent_color ?? '#24503f',
            'allow_client_signup' => $request->has('allow_client_signup'),
        ];

        if (! empty($validated['slug'])) {
            $updateData['slug'] = Str::slug($validated['slug']);
        }
        if (! empty($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        if (isset($validated['practice_areas'])) {
            $updateData['practice_areas'] = $validated['practice_areas'];
        }
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $firm->update($updateData);

        return redirect()->route('admin.firms.show', $firm)
            ->with('success', "Law firm '{$firm->name}' profile updated successfully.");
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
     * Reset the password for a firm's primary admin/managing partner.
     * POST /admin/firms/{firm}/change-password
     */
    public function changePassword(Request $request, Firm $firm): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $admin = $firm->users()->where('role', 'partner')->oldest()->first();

        if (! $admin) {
            $admin = $firm->users()->oldest()->first();
        }

        if (! $admin) {
            return back()->with('error', "No user accounts found for '{$firm->name}'. Cannot change password.");
        }

        $admin->update(['password' => Hash::make($validated['new_password'])]);

        return back()->with('success', "Password for '{$admin->name}' ({$admin->email}) at '{$firm->name}' has been reset successfully.");
    }

    /**
     * Delete or permanently purge a law firm and its associated tenant data.
     */
    public function destroy(Request $request, Firm $firm): RedirectResponse
    {
        $name = $firm->name;

        DB::transaction(function () use ($firm) {
            // 1. Clean up physical documents from storage
            if (class_exists(Document::class)) {
                $documents = Document::where('firm_id', $firm->id)->get();
                foreach ($documents as $doc) {
                    if (! empty($doc->file_path)) {
                        try {
                            Storage::disk('public')->delete($doc->file_path);
                            Storage::delete($doc->file_path);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            // 2. Remove all related tenant data across all child tables to prevent foreign key errors
            $childTables = [
                'matter_user', 'time_entries', 'documents', 'document_requests',
                'events', 'tasks', 'messages', 'opinions', 'appointments',
                'case_notes', 'note_categories', 'invoices', 'expenses',
                'transactions', 'bank_accounts', 'matters', 'clients',
                'practice_area_tasks', 'practice_areas', 'holidays',
                'email_templates', 'letter_templates', 'notification_templates',
                'activity_categories', 'id_types', 'user_groups', 'roles',
                'subscriptions',
            ];

            foreach ($childTables as $table) {
                if (Schema::hasTable($table)) {
                    if (Schema::hasColumn($table, 'firm_id')) {
                        DB::table($table)->where('firm_id', $firm->id)->delete();
                    }
                }
            }

            // 3. Remove tenant-specific user accounts (preserving any superadmin)
            User::where('firm_id', $firm->id)
                ->where('role', '!=', 'superadmin')
                ->delete();

            User::where('firm_id', $firm->id)
                ->where('role', 'superadmin')
                ->update(['firm_id' => null]);

            // 4. Delete the firm itself
            $firm->delete();
        });

        return redirect()->route('admin.firms.index')
            ->with('success', "Law firm '{$name}' and all associated tenant records were permanently deleted.");
    }
}
