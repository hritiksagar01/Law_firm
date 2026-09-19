@extends('layouts.admin')

@section('title', 'Subscription Plans — Platform Console')

@section('content')
<div class="space-y-6" x-data="{ 
    addPlanModal: false,
    editPlanModal: false,
    upgradeModal: false,
    selectedFirm: null,
    editingPlan: { id: null, name: '', price: 0, max_users: 1, max_matters: 1, max_storage_gb: 1 }
}">

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-3.5 bg-[#f0fdf4] border border-[#bbf7d0] text-[#166534] rounded-sm text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] rounded-sm text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#dc2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif text-[#1b1c18] tracking-tight">Subscription plans</h1>
            <p class="text-xs text-[#5e625e] mt-1 font-sans">
                Manage subscription tiers, quotas, feature provisioning, and tenant renewals across the platform
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="addPlanModal = true" 
                    class="inline-flex items-center px-4 py-2 bg-[#23493a] text-white text-xs font-medium rounded-sm hover:bg-[#1a382c] transition-colors shadow-none">
                + New Plan Tier
            </button>
            <a href="{{ route('admin.subscriptions.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-[#e5e3dc] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors">
                Renewals Registry
            </a>
        </div>
    </div>

    <!-- Plan Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="p-6 rounded-sm bg-white border {{ $plan->is_active ? 'border-[#e5e3dc]' : 'border-dashed border-gray-300 opacity-80' }} shadow-none flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-[11px] uppercase tracking-wider px-2 py-0.5 rounded-xs bg-[#f5f3ed] text-[#1b1c18] border border-[#e5e3dc]">
                        {{ $plan->slug }}
                    </span>
                    @if($plan->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                        Active
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-700">
                        Inactive
                    </span>
                    @endif
                </div>

                <h3 class="text-xl font-serif text-[#1b1c18]">{{ $plan->name }}</h3>
                <p class="text-xs text-[#5e625e] mt-1 mb-4">{{ $plan->description ?? 'Full legal practice operations suite' }}</p>

                <div class="flex items-baseline gap-1 mb-4 border-b border-[#f0eee8] pb-4">
                    <span class="text-3xl font-serif text-[#1b1c18]">${{ number_format($plan->price, 2) }}</span>
                    <span class="text-xs font-mono text-[#5e625e]">/ {{ ucfirst($plan->interval) }}</span>
                </div>

                <!-- Quotas -->
                <div class="space-y-2 text-xs mb-4">
                    <div class="flex items-center justify-between text-[#5e625e]">
                        <span>Maximum Personnel:</span>
                        <strong class="font-sans text-[#1b1c18]">{{ $plan->max_users }} Advocates</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#5e625e]">
                        <span>Case Files Quota:</span>
                        <strong class="font-sans text-[#1b1c18]">{{ $plan->max_matters }} Active Matters</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#5e625e]">
                        <span>Cloud Storage:</span>
                        <strong class="font-sans text-[#1b1c18]">{{ $plan->max_storage_gb }} GB Vault</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#5e625e]">
                        <span>Active Subscriptions:</span>
                        <strong class="font-sans text-[#23493a]">{{ $plan->subscriptions_count }} Tenants</strong>
                    </div>
                </div>

                <!-- Features Included -->
                <div class="pt-3 border-t border-[#f0eee8] space-y-1.5 text-xs text-[#5e625e]">
                    @if($plan->features)
                    @foreach($plan->features as $feature)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#166534] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

            <!-- Card Bottom Controls -->
            <div class="mt-6 pt-4 border-t border-[#e5e3dc] flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.plans.toggle-active', $plan) }}">
                        @csrf
                        <button type="submit" 
                                class="font-medium {{ $plan->is_active ? 'text-amber-800 hover:underline' : 'text-[#166534] hover:underline' }}">
                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <span class="text-gray-300">&middot;</span>
                    <button type="button" 
                            @click="editingPlan = { id: {{ $plan->id }}, name: '{{ addslashes($plan->name) }}', price: {{ $plan->price }}, max_users: {{ $plan->max_users }}, max_matters: {{ $plan->max_matters }}, max_storage_gb: {{ $plan->max_storage_gb }} }; editPlanModal = true"
                            class="text-[#23493a] hover:underline font-medium">
                        Edit
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" 
                      onsubmit="return confirm('Are you sure you want to delete plan \'{{ addslashes($plan->name) }}\'?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[#ba1a1a] hover:underline text-xs">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 p-8 bg-white border border-[#e5e3dc] rounded-sm text-center text-xs text-[#5e625e]">
            No subscription tiers configured.
        </div>
        @endforelse
    </div>

    <!-- Tenant Plan Subscriptions Table -->
    <div class="bg-white rounded-sm border border-[#e5e3dc] shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc]">
            <h2 class="text-sm font-semibold text-[#1b1c18]">Active Firm Subscriptions</h2>
            <p class="text-xs text-[#5e625e] mt-0.5">Real-time status of tenant billing contracts</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white">
                        <th class="py-2.5 px-4 font-normal">Law Firm</th>
                        <th class="py-2.5 px-4 font-normal">Current Tier</th>
                        <th class="py-2.5 px-4 font-normal">Billing Interval</th>
                        <th class="py-2.5 px-4 font-normal">Advocates Quota</th>
                        <th class="py-2.5 px-4 font-normal">Valid Until</th>
                        <th class="py-2.5 px-4 font-normal">Status</th>
                        <th class="py-2.5 px-4 font-normal text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($firms as $firm)
                    @php
                        $sub = $firm->currentSubscription;
                        $plan = $sub?->plan;
                    @endphp
                    <tr class="hover:bg-[#fbf9f5] transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.firms.show', $firm) }}" class="font-medium text-[#1b1c18] hover:underline">
                                {{ $firm->name }}
                            </a>
                            <span class="block text-[11px] text-[#5e625e] font-mono">{{ $firm->slug }}</span>
                        </td>
                        <td class="py-3 px-4 font-sans text-[#1b1c18]">
                            {{ $plan->name ?? 'Default Practice' }}
                        </td>
                        <td class="py-3 px-4 font-sans text-[#5e625e]">
                            {{ ucfirst($plan->interval ?? 'monthly') }}
                        </td>
                        <td class="py-3 px-4 font-sans text-[#1b1c18]">
                            {{ $firm->users->count() }} / {{ $plan->max_users ?? 15 }} seats
                        </td>
                        <td class="py-3 px-4 font-sans text-[#1b1c18]">
                            {{ $sub?->ends_at ? $sub->ends_at->format('d M Y') : 'Active' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                {{ ucfirst($sub->status ?? 'Active') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.firms.show', $firm) }}" 
                               class="text-xs text-[#23493a] hover:underline font-medium">
                                Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 px-4 text-center text-xs text-[#5e625e]">
                            No firm subscriptions recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Plan Modal -->
    <div x-show="addPlanModal" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-sm max-w-lg w-full p-6 shadow-xl"
             @click.away="addPlanModal = false">
            <h3 class="text-base font-serif text-[#1b1c18] mb-1">Provision New Plan Tier</h3>
            <p class="text-xs text-[#5e625e] mb-4">Define pricing, storage, user limits and interval</p>

            <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Plan Name</label>
                        <input type="text" name="name" required placeholder="e.g. Enterprise Chambers"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Slug Identifier</label>
                        <input type="text" name="slug" required placeholder="e.g. enterprise-chambers"
                               class="w-full px-3 py-2 text-xs font-mono rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Price (USD)</label>
                        <input type="number" step="0.01" name="price" required placeholder="499.00"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Billing Interval</label>
                        <select name="interval" class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]">
                            <option value="monthly">Monthly</option>
                            <option value="biannual">Biannual</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Max Advocates / Seats</label>
                        <input type="number" name="max_users" required value="10" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Max Matters</label>
                        <input type="number" name="max_matters" required value="100" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Cloud Storage (GB)</label>
                        <input type="number" name="max_storage_gb" required value="50" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Plan highlights &amp; service level..."
                                  class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="addPlanModal = false"
                            class="px-4 py-2 border border-[#dcdad4] rounded-sm text-xs text-[#5e625e] hover:bg-[#f5f3ed]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-[#23493a] text-white rounded-sm text-xs font-medium hover:bg-[#1a382c]">
                        Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Plan Modal -->
    <div x-show="editPlanModal" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-sm max-w-lg w-full p-6 shadow-xl"
             @click.away="editPlanModal = false">
            <h3 class="text-base font-serif text-[#1b1c18] mb-1">Modify Plan Parameters</h3>
            <p class="text-xs text-[#5e625e] mb-4">Update pricing and tier quotas</p>

            <form :action="'/admin/plans/' + editingPlan.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Plan Name</label>
                        <input type="text" name="name" required x-model="editingPlan.name"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Price (USD)</label>
                        <input type="number" step="0.01" name="price" required x-model="editingPlan.price"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Max Advocates / Seats</label>
                        <input type="number" name="max_users" required x-model="editingPlan.max_users" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Max Matters</label>
                        <input type="number" name="max_matters" required x-model="editingPlan.max_matters" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Storage (GB)</label>
                        <input type="number" name="max_storage_gb" required x-model="editingPlan.max_storage_gb" min="1"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] focus:outline-none focus:border-[#23493a]" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="editPlanModal = false"
                            class="px-4 py-2 border border-[#dcdad4] rounded-sm text-xs text-[#5e625e] hover:bg-[#f5f3ed]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-[#23493a] text-white rounded-sm text-xs font-medium hover:bg-[#1a382c]">
                        Update Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
