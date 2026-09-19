<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionManagementController extends Controller
{
    /**
     * GET /admin/subscriptions
     * Shows all firm subscriptions: active, expiring soon, and expired.
     */
    public function index(): View
    {
        $subscriptions = Subscription::with(['firm', 'plan'])
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->orderBy('ends_at', 'asc')
            ->get()
            ->map(function ($sub) {
                $sub->days_remaining = $sub->ends_at
                    ? (int) now()->diffInDays($sub->ends_at, false)
                    : null;
                $sub->is_expired = $sub->ends_at && $sub->ends_at->isPast();
                $sub->is_expiring_soon = $sub->days_remaining !== null && $sub->days_remaining >= 0 && $sub->days_remaining <= 30;
                return $sub;
            });

        $plans = Plan::where('is_active', true)->get();

        $stats = [
            'active' => $subscriptions->where('is_expired', false)->where('status', 'active')->count(),
            'expiring_soon' => $subscriptions->where('is_expiring_soon', true)->count(),
            'expired' => $subscriptions->where('is_expired', true)->count(),
            'total' => $subscriptions->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'stats'));
    }

    /**
     * POST /admin/subscriptions/{subscription}/renew
     * Quick-renew: extend by specified duration from current end date.
     */
    public function renew(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'duration_months' => 'required|integer|in:1,3,6,12',
        ]);

        $currentEnd = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : now();
        $newEnd = $currentEnd->copy()->addMonths((int) $validated['duration_months']);

        $subscription->update([
            'ends_at' => $newEnd,
            'status' => 'active',
        ]);

        return back()->with('success', "Subscription for '{$subscription->firm->name}' renewed until {$newEnd->format('d M Y')}.");
    }
}
