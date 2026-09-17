@extends('layouts.admin')

@section('title', 'Subscription Plans')
@section('header_title', 'Subscription Plans Governance')

@section('content')
<div class="space-y-8" x-data="{ 
    addPlanModal: false,
    upgradeModal: false,
    selectedFirm: null
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">SaaS Subscription &amp; Plan Governance</h1>
            <p class="text-sm text-[#766A5E] mt-1">Manage subscription tiers, quotas, feature provisioning, and tenant renewals (PDF Reference Page 4 & 20)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="addPlanModal = true" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-sm">
                <span class="material-symbols-outlined text-base">add_card</span>
                <span>+ New Plan Tier</span>
            </button>
            <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-[#EFECE6] text-xs font-semibold text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-base text-[#9F8349]">autorenew</span>
                <span>Renewals Registry</span>
            </a>
        </div>
    </div>

    <!-- Plan Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="p-6 rounded-3xl bg-white border {{ $plan->is_active ? 'border-[#EFECE6]' : 'border-dashed border-gray-300 opacity-80' }} shadow-xs flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-lg bg-[#FAF8F5] text-[#9F8349] border border-[#EAE4DC]">
                        {{ $plan->slug }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($plan->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-100 text-emerald-800">
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-gray-100 text-gray-700">
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>

                <h3 class="text-xl font-serif font-bold text-[#222222]">{{ $plan->name }}</h3>
                <p class="text-xs text-[#766A5E] mt-1 mb-4">{{ $plan->description ?? 'Full legal practice operations suite' }}</p>

                <div class="flex items-baseline gap-1 mb-4 border-b border-[#FAF8F5] pb-4">
                    <span class="text-3xl font-serif font-bold text-[#222222]">₹{{ number_format($plan->price, 0) }}</span>
                    <span class="text-xs font-mono text-[#766A5E]">/ {{ ucfirst($plan->interval) }}</span>
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
                    <div class="flex items-center justify-between text-[#554D45]">
                        <span>Active Subscriptions:</span>
                        <strong class="font-mono text-[#9F8349]">{{ $plan->subscriptions_count }} Tenants</strong>
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

            <!-- Card Bottom Controls -->
            <div class="mt-6 pt-4 border-t border-[#EFECE6] flex items-center justify-between">
                <!-- Toggle Active -->
                <form method="POST" action="{{ route('admin.plans.toggle-active', $plan) }}">
                    @csrf
                    <button type="submit" 
                            class="text-xs font-medium {{ $plan->is_active ? 'text-amber-700 hover:text-amber-800' : 'text-emerald-700 hover:text-emerald-800' }}">
                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>

                <!-- Delete Action (Page 4) -->
                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" 
                      onsubmit="return confirm('Are you sure you want to delete plan \'{{ addslashes($plan->name) }}\'?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="text-xs text-rose-600 hover:text-rose-800 font-medium inline-flex items-center gap-0.5"
                            title="Delete Plan Tier">
                        <span class="material-symbols-outlined text-sm">delete</span>
                        <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-[#766A5E] bg-white rounded-3xl border border-[#EFECE6]">
            <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">subscriptions</span>
            <p class="font-medium">No subscription tiers created yet.</p>
        </div>
        @endforelse
    </div>

    <!-- Active Law Firm Subscriptions Table -->
    <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#EFECE6] flex items-center justify-between">
            <div>
                <h3 class="font-serif font-bold text-lg text-[#222222]">Law Firm Tenant Subscription Status</h3>
                <p class="text-xs text-[#766A5E] mt-0.5">Summary of currently assigned plan tiers across all firms</p>
            </div>
            <span class="font-mono text-xs text-[#9F8349] bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-[#EAE4DC]">
                {{ $firms->count() }} Registered Tenants
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px] tracking-wider">
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
                    <tr class="hover:bg-[#FAF8F5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $firm->display_title }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#9F8349]">{{ $firm->slug }}.lexiscore.app</td>
                        <td class="py-3.5 px-4 font-medium text-[#222222]">
                            @if($plan)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#F8F4EE] text-[#9F8349] border border-[#EAE4DC] text-xs">
                                    {{ $plan->name }}
                                </span>
                            @else
                                <span class="text-gray-400 italic">Trial Provisioning</span>
                            @endif
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
                            @if($sub && $sub->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Trial / Pending</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="selectedFirm = { id: {{ $firm->id }}, name: '{{ addslashes($firm->name) }}' }; upgradeModal = true" 
                                    class="px-3 py-1.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-[#9F8349] text-xs font-semibold hover:bg-[#9F8349] hover:text-white transition-colors">
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
        <div @click.away="addPlanModal = false" class="bg-white rounded-3xl shadow-2xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#9F8349]">add_card</span>
                    <h3 class="font-serif font-bold text-lg text-[#222222]">Provision Subscription Plan Tier</h3>
                </div>
                <button @click="addPlanModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Plan Display Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Enterprise Chambers Suite" 
                           class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Unique Slug *</label>
                        <input type="text" name="slug" required placeholder="enterprise" 
                               class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Price (₹) *</label>
                        <input type="number" name="price" value="25000" required 
                               class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Max Users</label>
                        <input type="number" name="max_users" value="25" required class="w-full h-9 px-2 rounded-xl border border-[#EAE4DC] font-mono"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Max Matters</label>
                        <input type="number" name="max_matters" value="500" required class="w-full h-9 px-2 rounded-xl border border-[#EAE4DC] font-mono"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Storage (GB)</label>
                        <input type="number" name="max_storage_gb" value="100" required class="w-full h-9 px-2 rounded-xl border border-[#EAE4DC] font-mono"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Billing Interval</label>
                    <select name="interval" class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none bg-white">
                        <option value="monthly">Monthly Recurring</option>
                        <option value="biannual">Biannual (Half Yearly)</option>
                        <option value="yearly">Yearly Advance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Plan overview..." class="w-full p-2.5 rounded-xl border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="addPlanModal = false" class="px-4 py-2 rounded-xl border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Create Tier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: UPGRADE / RENEW FIRM -->
    <div x-show="upgradeModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="upgradeModal = false" class="bg-white rounded-3xl shadow-2xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#9F8349]">upgrade</span>
                    <h3 class="font-serif font-bold text-lg text-[#222222]">Upgrade / Renew Subscription</h3>
                </div>
                <button @click="upgradeModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form :action="'/admin/plans/firms/' + (selectedFirm ? selectedFirm.id : '') + '/assign'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#EAE4DC]">
                    <span class="text-[#766A5E] block text-xs">Law Firm Tenant:</span>
                    <strong class="text-[#222222] text-sm" x-text="selectedFirm ? selectedFirm.name : ''"></strong>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Select Subscription Tier</label>
                    <select name="plan_id" required class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none bg-white">
                        @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (₹{{ number_format($p->price, 0) }} / {{ $p->interval }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Validity Duration</label>
                    <select name="duration_months" class="w-full h-9 px-3 rounded-xl border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono bg-white">
                        <option value="1">1 Month</option>
                        <option value="3">3 Months (Quarterly)</option>
                        <option value="6">6 Months (Half Yearly)</option>
                        <option value="12" selected>12 Months (Annual Engagement)</option>
                    </select>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="upgradeModal = false" class="px-4 py-2 rounded-xl border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Authorize Renewal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
