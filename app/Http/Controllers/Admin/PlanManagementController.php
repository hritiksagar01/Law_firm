<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firm;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class PlanManagementController extends Controller
{
    /**
     * Display subscription plans and active tenant subscriptions.
     */
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->get();
        $firms = Firm::with(['currentSubscription.plan', 'users', 'matters'])->get();

        return view('admin.plans.index', compact('plans', 'firms'));
    }

    /**
     * Store a new subscription plan tier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:monthly,yearly',
            'max_users' => 'required|integer|min:1',
            'max_matters' => 'required|integer|min:1',
            'max_storage_gb' => 'required|integer|min:1',
        ]);

        Plan::create([
            'name' => $validated['name'],
            'slug' => strtolower($validated['slug']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'interval' => $validated['interval'],
            'max_users' => $validated['max_users'],
            'max_matters' => $validated['max_matters'],
            'max_storage_gb' => $validated['max_storage_gb'],
            'features' => [
                'Case Management & Dossiers',
                'Legal Opinions 4-Stage Workflow',
                'Daily Court Cause List & Hearings',
                'Automated Invoicing & Expenses',
                'Encrypted Client Portal',
                'Custom Numbering Formats',
            ],
            'is_active' => true,
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', "Subscription tier '{$validated['name']}' provisioned.");
    }

    /**
     * Update an existing plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_matters' => 'required|integer|min:1',
            'max_storage_gb' => 'required|integer|min:1',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan '{$plan->name}' updated.");
    }

    /**
     * Assign, renew, or upgrade a law firm's subscription.
     */
    public function assignPlan(Request $request, Firm $firm)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:36',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $startsAt = now();
        $endsAt = now()->addMonths((int) $validated['duration_months']);

        Subscription::create([
            'firm_id' => $firm->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', "Firm '{$firm->name}' upgraded to {$plan->name} valid through {$endsAt->format('d M Y')}.");
    }
}
