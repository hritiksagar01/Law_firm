@extends('layouts.admin')

@section('title', 'Subscription Governance & Renewals')
@section('header_title', 'Subscription Governance & Renewals')

@section('content')
<div x-data="{ 
    renewModal: false, 
    actionUrl: '', 
    firmName: '', 
    currentPlan: '', 
    currentEnd: '' 
}">

    <!-- Page Header & Actions -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Subscription Lifecycles & Renewals</h1>
            <p class="text-sm text-[#766A5E] mt-1">Audit tenant subscriptions, monitor billing expirations, and process manual renewals</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.plans.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">subscriptions</span>
                <span>Manage Plans</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">corporate_fare</span>
                <span>Firms Directory</span>
            </a>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Tracked</span>
                <span class="material-symbols-outlined text-[#9F8349] text-xl">all_inclusive</span>
            </div>
            <span class="text-2xl font-serif font-bold text-[#222222] mt-2 block">{{ $stats['total'] }}</span>
            <span class="text-xs text-[#766A5E] mt-1 block">Active & Legacy Subscriptions</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] uppercase tracking-wider text-emerald-700">Active</span>
                <span class="material-symbols-outlined text-emerald-600 text-xl">verified</span>
            </div>
            <span class="text-2xl font-serif font-bold text-emerald-700 mt-2 block">{{ $stats['active'] }}</span>
            <span class="text-xs text-emerald-600 mt-1 block">Within Current Billing Cycle</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] uppercase tracking-wider text-amber-700">Expiring Soon</span>
                <span class="material-symbols-outlined text-amber-600 text-xl">alarm</span>
            </div>
            <span class="text-2xl font-serif font-bold text-amber-700 mt-2 block">{{ $stats['expiring_soon'] }}</span>
            <span class="text-xs text-amber-600 mt-1 block">Expiring within 30 days</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] uppercase tracking-wider text-rose-700">Expired / Lapsed</span>
                <span class="material-symbols-outlined text-rose-600 text-xl">warning</span>
            </div>
            <span class="text-2xl font-serif font-bold text-rose-700 mt-2 block">{{ $stats['expired'] }}</span>
            <span class="text-xs text-rose-600 mt-1 block">Requires Immediate Renewal</span>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#EFECE6] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-serif text-lg font-bold text-[#222222]">Tenant Subscriptions Registry</h2>
                <p class="text-xs text-[#766A5E] mt-0.5">Chronologically ordered by expiration urgency</p>
            </div>
            <span class="text-xs font-mono text-[#9F8349] bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-[#EFECE6]">
                {{ $subscriptions->count() }} Records Found
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#FAF8F5] text-[#766A5E] font-mono uppercase text-[10px] tracking-wider border-b border-[#EFECE6]">
                        <th class="py-3 px-4 font-semibold">#</th>
                        <th class="py-3 px-4 font-semibold">Law Firm</th>
                        <th class="py-3 px-4 font-semibold">Plan Tier</th>
                        <th class="py-3 px-4 font-semibold">Starts At</th>
                        <th class="py-3 px-4 font-semibold">Valid Upto</th>
                        <th class="py-3 px-4 font-semibold">Days Remaining</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($subscriptions as $index => $sub)
                    <tr class="hover:bg-[#FAF8F5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono text-[#A09587]">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-[#222222] text-sm">
                                <a href="{{ route('admin.firms.show', $sub->firm) }}" class="hover:text-[#9F8349] transition-colors">
                                    {{ $sub->firm->name }}
                                </a>
                            </div>
                            <span class="text-[11px] font-mono text-[#766A5E] block">
                                {{ $sub->firm->slug }} &bull; {{ $sub->firm->email ?? 'No email' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[#F8F4EE] text-[#9F8349] border border-[#EAE4DC]">
                                <span class="font-semibold">{{ $sub->plan->name }}</span>
                                <span class="text-[10px] font-mono opacity-80">({{ ucfirst($sub->plan->interval) }})</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[#554D45]">
                            {{ $sub->starts_at ? $sub->starts_at->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[#554D45]">
                            {{ $sub->ends_at ? $sub->ends_at->format('d M Y') : 'Lifetime' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            @if($sub->ends_at)
                                @if($sub->is_expired)
                                    <span class="text-rose-600 font-semibold inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        Expired {{ abs($sub->days_remaining) }}d ago
                                    </span>
                                @elseif($sub->is_expiring_soon)
                                    <span class="text-amber-600 font-semibold inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        {{ $sub->days_remaining }} days left
                                    </span>
                                @else
                                    <span class="text-emerald-700">
                                        {{ $sub->days_remaining }} days
                                    </span>
                                @endif
                            @else
                                <span class="text-[#766A5E]">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($sub->is_expired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-rose-100 text-rose-800">
                                    Expired
                                </span>
                            @elseif($sub->is_expiring_soon)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-amber-100 text-amber-800">
                                    Expiring Soon
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-100 text-emerald-800">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" 
                                    @click="
                                        actionUrl = '{{ route('admin.subscriptions.renew', $sub) }}';
                                        firmName = '{{ addslashes($sub->firm->name) }}';
                                        currentPlan = '{{ addslashes($sub->plan->name) }}';
                                        currentEnd = '{{ $sub->ends_at ? $sub->ends_at->format('d M Y') : 'Never' }}';
                                        renewModal = true;
                                    "
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-xs transition-all">
                                <span class="material-symbols-outlined text-sm">autorenew</span>
                                <span>Renew</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-[#766A5E]">
                            <span class="material-symbols-outlined text-4xl text-[#D4C4B5] mb-2 block">subscriptions</span>
                            <span class="text-sm font-semibold">No active firm subscriptions found</span>
                            <p class="text-xs text-[#A09587] mt-1">Assign a subscription to a firm from the Firms directory</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Renewal Modal (Alpine.js) -->
    <div x-show="renewModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="renewModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EFECE6] animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#FAF8F5] border border-[#EFECE6] flex items-center justify-center text-[#9F8349]">
                        <span class="material-symbols-outlined text-lg">autorenew</span>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#222222]">Extend Subscription</h3>
                        <p class="text-xs text-[#766A5E]" x-text="firmName"></p>
                    </div>
                </div>
                <button @click="renewModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mt-4 p-3 rounded-xl bg-[#FAF8F5] border border-[#EFECE6] text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-[#766A5E]">Current Plan:</span>
                    <span class="font-semibold text-[#222222]" x-text="currentPlan"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#766A5E]">Valid Until:</span>
                    <span class="font-mono text-[#222222]" x-text="currentEnd"></span>
                </div>
            </div>

            <form :action="actionUrl" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-2">Select Extension Term *</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="flex items-center justify-between p-3 rounded-xl border border-[#EAE4DC] hover:border-[#9F8349] cursor-pointer transition-all has-checked:border-[#9F8349] has-checked:bg-[#FAF8F5]">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="duration_months" value="1" class="text-[#9F8349] focus:ring-[#9F8349]">
                                <span class="text-xs font-semibold text-[#222222]">1 Month</span>
                            </div>
                            <span class="text-[10px] font-mono text-[#766A5E]">+30 Days</span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border border-[#EAE4DC] hover:border-[#9F8349] cursor-pointer transition-all has-checked:border-[#9F8349] has-checked:bg-[#FAF8F5]">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="duration_months" value="3" class="text-[#9F8349] focus:ring-[#9F8349]">
                                <span class="text-xs font-semibold text-[#222222]">3 Months</span>
                            </div>
                            <span class="text-[10px] font-mono text-[#766A5E]">+90 Days</span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border border-[#EAE4DC] hover:border-[#9F8349] cursor-pointer transition-all has-checked:border-[#9F8349] has-checked:bg-[#FAF8F5]">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="duration_months" value="6" class="text-[#9F8349] focus:ring-[#9F8349]">
                                <span class="text-xs font-semibold text-[#222222]">6 Months</span>
                            </div>
                            <span class="text-[10px] font-mono text-[#766A5E]">+180 Days</span>
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl border border-[#EAE4DC] hover:border-[#9F8349] cursor-pointer transition-all has-checked:border-[#9F8349] has-checked:bg-[#FAF8F5]">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="duration_months" value="12" checked class="text-[#9F8349] focus:ring-[#9F8349]">
                                <span class="text-xs font-semibold text-[#222222]">1 Year</span>
                            </div>
                            <span class="text-[10px] font-mono text-emerald-700 font-semibold">+365 Days</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-[#EFECE6]">
                    <button type="button" @click="renewModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#766A5E] hover:bg-[#FAF8F5] border border-[#EAE4DC]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-sm">
                        Confirm Renewal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
