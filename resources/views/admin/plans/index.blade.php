@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    addPlanModal: false,
    upgradeModal: false,
    selectedFirm: null
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Platform Console</a>
                <span>/</span>
                <span>Subscriptions &amp; Plans</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">SaaS Subscription &amp; Plan Governance</h1>
            <p class="text-sm text-[#766A5E] mt-1">Manage platform subscription plans, firm renewals, usage quotas, and multi-tenant feature provisioning.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="addPlanModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">add_card</span>
                <span>New Plan Tier</span>
            </button>
            <a href="{{ route('admin.firms.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">domain</span>
                <span>Manage Law Firms</span>
            </a>
        </div>
    </div>

    <!-- Plan Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="p-6 rounded-xl bg-white border border-[#EFECE6] shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-[#FAF8F5] text-[#9F8349] border border-[#EAE4DC]">
                        {{ $plan->slug }}
                    </span>
                    <span class="text-xs text-[#766A5E] font-medium">{{ $plan->subscriptions_count }} Active Firms</span>
                </div>
                <h3 class="text-xl font-serif font-bold text-[#222222]">{{ $plan->name }}</h3>
                <p class="text-xs text-[#766A5E] mt-1 mb-4">{{ $plan->description ?? 'Full legal operations suite' }}</p>

                <div class="flex items-baseline gap-1 mb-4 border-b border-[#FAF8F5] pb-4">
                    <span class="text-3xl font-serif font-bold text-[#222222]">₹{{ number_format($plan->price, 0) }}</span>
                    <span class="text-xs text-[#766A5E]">/ {{ $plan->interval }}</span>
                </div>

                <!-- Quotas -->
                <div class="space-y-2 text-xs mb-4">
                    <div class="flex items-center justify-between text-[#554D45]">
                        <span>Maximum Personnel:</span>
                        <strong class="font-mono text-[#222222]">{{ $plan->max_users }} Advocates</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#554D45]">
                        <span>Case Files Quota:</span>
                        <strong class="font-mono text-[#222222]">{{ $plan->max_matters }} Active Matters</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#554D45]">
                        <span>Cloud Storage:</span>
                        <strong class="font-mono text-[#222222]">{{ $plan->max_storage_gb }} GB Vault</strong>
                    </div>
                </div>

                <!-- Features Included -->
                <div class="pt-3 border-t border-[#FAF8F5] space-y-1.5 text-xs text-[#766A5E]">
                    @if($plan->features)
                    @foreach($plan->features as $feature)
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-emerald-600">check</span>
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-[#FAF8F5] flex justify-end">
                <span class="text-[11px] font-mono text-[#9F8349] font-bold">Standard Platform Tier</span>
            </div>
        </div>
        @empty
        <div class="col-span-full py-8 text-center text-[#766A5E]">
            No subscription tiers created yet.
        </div>
        @endforelse
    </div>

    <!-- Active Law Firm Subscriptions Table -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
            <h3 class="font-serif font-semibold text-base text-[#222222]">Law Firm Tenant Subscription Status</h3>
            <span class="font-mono text-xs text-[#766A5E]">{{ $firms->count() }} Registered Tenants</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Law Firm Tenant</th>
                        <th class="py-3 px-4 font-semibold">Subdomain</th>
                        <th class="py-3 px-4 font-semibold">Assigned Plan</th>
                        <th class="py-3 px-4 font-semibold">Personnel Usage</th>
                        <th class="py-3 px-4 font-semibold">Matters Handled</th>
                        <th class="py-3 px-4 font-semibold">Renewal / Expiry</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($firms as $firm)
                    @php
                        $sub = $firm->currentSubscription;
                        $plan = $sub ? $sub->plan : null;
                    @endphp
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $firm->name }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#9F8349]">{{ $firm->slug }}.jurislegal.io</td>
                        <td class="py-3.5 px-4 font-medium text-[#222222]">
                            {{ $plan ? $plan->name : 'Trial Provisioning' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            {{ $firm->users->count() }} / {{ $plan ? $plan->max_users : 10 }}
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            {{ $firm->matters->count() }} / {{ $plan ? $plan->max_matters : 100 }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#766A5E]">
                            {{ $sub && $sub->ends_at ? $sub->ends_at->format('d M Y') : 'Ongoing Renewal' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($sub && $sub->isActive())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Trial / Pending</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="selectedFirm = { id: {{ $firm->id }}, name: '{{ addslashes($firm->name) }}' }; upgradeModal = true" class="px-2.5 py-1 rounded bg-[#FAF8F5] border border-[#EAE4DC] text-[#9F8349] font-semibold hover:bg-[#9F8349] hover:text-white transition-colors">
                                Upgrade / Renew
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#766A5E]">No law firm tenants registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD PLAN TIER -->
    <div x-show="addPlanModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addPlanModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Provision Subscription Plan Tier</h3>
                <button @click="addPlanModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Plan Display Name</label>
                    <input type="text" name="name" required placeholder="e.g. Enterprise Chambers Suite" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Unique Slug</label>
                        <input type="text" name="slug" required placeholder="enterprise" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Price (₹)</label>
                        <input type="number" name="price" value="25000" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Max Users</label>
                        <input type="number" name="max_users" value="25" required class="w-full h-9 px-2 rounded-lg border border-[#EAE4DC] font-mono"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Max Matters</label>
                        <input type="number" name="max_matters" value="500" required class="w-full h-9 px-2 rounded-lg border border-[#EAE4DC] font-mono"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Storage (GB)</label>
                        <input type="number" name="max_storage_gb" value="100" required class="w-full h-9 px-2 rounded-lg border border-[#EAE4DC] font-mono"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Billing Interval</label>
                    <select name="interval" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        <option value="monthly">Monthly Recurring</option>
                        <option value="yearly">Yearly Advance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Plan overview..." class="w-full p-2.5 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="addPlanModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Create Tier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: UPGRADE / RENEW FIRM -->
    <div x-show="upgradeModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="upgradeModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Upgrade / Renew Subscription</h3>
                <button @click="upgradeModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form :action="'/admin/plans/firms/' + (selectedFirm ? selectedFirm.id : '') + '/assign'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="p-3 bg-[#FAF8F5] rounded-lg border border-[#EAE4DC]">
                    <span class="text-[#766A5E] block">Law Firm Tenant:</span>
                    <strong class="text-[#222222] text-sm" x-text="selectedFirm ? selectedFirm.name : ''"></strong>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Select Subscription Tier</label>
                    <select name="plan_id" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (₹{{ number_format($p->price, 0) }} / {{ $p->interval }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Validity Duration</label>
                    <select name="duration_months" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono">
                        <option value="1">1 Month</option>
                        <option value="3">3 Months (Quarterly)</option>
                        <option value="6">6 Months</option>
                        <option value="12" selected>12 Months (Annual Engagement)</option>
                    </select>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="upgradeModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Authorize Renewal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
