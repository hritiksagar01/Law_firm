@extends('layouts.admin')

@section('title', 'Law Firm Tenants Directory')
@section('header_title', 'Law Firm Tenants Directory')

@section('content')
<div x-data="{ 
    changeFirmPasswordModal: false,
    changeFirmSubModal: false,
    selectedFirmName: '',
    selectedAdminEmail: '',
    selectedCurrentPlan: '',
    selectedValidUpto: '',
    passwordActionUrl: '',
    subActionUrl: ''
}">

    <!-- Page Title & Primary Actions -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Law Firm Tenants</h1>
            <p class="text-sm text-[#766A5E] mt-1">Manage tenant organizations, credentials, and SaaS subscription tiers (PDF Reference Page 3)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.subscriptions.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">autorenew</span>
                <span>Renewals Console</span>
            </a>
            <a href="{{ route('admin.firms.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">domain_add</span>
                <span>+ Add Firm</span>
            </a>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Firms</span>
            <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $stats['total'] }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Registered Chambers</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-emerald-700">Active Practices</span>
            <span class="text-2xl font-serif font-bold text-emerald-700 mt-1 block">{{ $stats['active'] }}</span>
            <span class="text-xs text-emerald-600 mt-2 block font-mono">Operational &amp; Live</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-amber-700">Inactive / Suspended</span>
            <span class="text-2xl font-serif font-bold text-amber-700 mt-1 block">{{ $stats['inactive'] }}</span>
            <span class="text-xs text-amber-600 mt-2 block font-mono">Access Restricted</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349]">Staff &amp; Advocates</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $stats['total_users'] }}</span>
            <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">Across All Firms</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-[#EFECE6] shadow-xs mb-6">
        <form method="GET" action="{{ route('admin.firms.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by login name, display name, email, or city..."
                    class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-[#EAE4DC] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] bg-[#FAF8F5]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="text-xs py-2.5 px-3 rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
                @if(request('q') || request('status'))
                <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#766A5E] hover:text-[#222222] px-2 font-semibold">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Law Firm Tenant Table (PDF Page 3 Columns & Requirements) -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-semibold">Login Name</th>
                        <th class="py-3.5 px-4 font-semibold">Display Name</th>
                        <th class="py-3.5 px-4 font-semibold">Admin / Contact</th>
                        <th class="py-3.5 px-4 font-semibold">Admin Email</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Clients</th>
                        <th class="py-3.5 px-4 font-semibold">Subscription Plan</th>
                        <th class="py-3.5 px-4 font-semibold">Valid Upto</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Status</th>
                        <th class="py-3.5 px-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6] text-xs">
                    @forelse($firms as $firm)
                    <tr class="hover:bg-[#FAF8F5]/80 transition-colors">
                        <!-- Login Name (Page 3) -->
                        <td class="py-3.5 px-4 font-mono font-semibold text-[#222222]">
                            <span class="bg-[#FAF8F5] px-2 py-1 rounded-md border border-[#EAE4DC] text-[11px]">
                                {{ $firm->slug }}
                            </span>
                        </td>

                        <!-- Display Name (Page 3) -->
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.firms.show', $firm) }}" class="font-bold text-[#222222] text-sm hover:text-[#9F8349] transition-colors block">
                                {{ $firm->display_title }}
                            </a>
                            @if($firm->display_name && $firm->display_name !== $firm->name)
                                <span class="text-[10px] text-[#8C7F72] block font-mono">Legal: {{ $firm->name }}</span>
                            @endif
                        </td>

                        <!-- Contact / Primary Admin User -->
                        <td class="py-3.5 px-4">
                            <span class="text-[#554D45] font-medium block">
                                {{ $firm->contact_name ?: ($firm->primaryAdmin?->name ?? 'Managing Partner') }}
                            </span>
                            <span class="text-[10px] font-mono text-[#8C7F72]">
                                {{ $firm->users_count }} users &bull; {{ $firm->matters_count }} cases
                            </span>
                        </td>

                        <!-- Admin Email (Page 3) -->
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#554D45]">
                            {{ $firm->primaryAdmin?->email ?? $firm->email ?? '—' }}
                        </td>

                        <!-- Clients Count (Page 3) -->
                        <td class="py-3.5 px-4 text-center font-mono font-semibold text-[#222222]">
                            <span class="inline-block bg-[#F8F4EE] text-[#9F8349] px-2 py-0.5 rounded-full font-bold">
                                {{ $firm->clients_count }}
                            </span>
                        </td>

                        <!-- Subscription Plan (Page 3) -->
                        <td class="py-3.5 px-4">
                            @if($firm->currentSubscription && $firm->currentSubscription->plan)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-[#F8F4EE] text-[#9F8349] border border-[#EAE4DC]">
                                    <span class="material-symbols-outlined text-xs">verified</span>
                                    <span>{{ $firm->currentSubscription->plan->name }}</span>
                                </span>
                            @else
                                <span class="text-[#A09587] font-mono text-[11px] italic">No Plan</span>
                            @endif
                        </td>

                        <!-- Valid Upto (Page 3) -->
                        <td class="py-3.5 px-4 font-mono text-[11px]">
                            @if($firm->valid_upto)
                                <span class="{{ $firm->valid_upto->isPast() ? 'text-rose-600 font-semibold' : 'text-[#554D45]' }}">
                                    {{ $firm->valid_upto->format('Y-m-d') }}
                                </span>
                            @else
                                <span class="text-[#A09587]">—</span>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4 text-center">
                            @if($firm->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Inactive
                            </span>
                            @endif
                        </td>

                        <!-- Action Buttons (Change Password, Change Subscription, View, Edit, Toggle, Delete) -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <!-- Change Password Button (PDF Page 2 & 3) -->
                                <button type="button" 
                                        @click="
                                            passwordActionUrl = '{{ route('admin.firms.change-password', $firm) }}';
                                            selectedFirmName = '{{ addslashes($firm->name) }}';
                                            selectedAdminEmail = '{{ addslashes($firm->primaryAdmin?->email ?? $firm->email ?? '') }}';
                                            changeFirmPasswordModal = true;
                                        "
                                        class="p-1.5 rounded-lg text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5] transition-colors" 
                                        title="Change Firm Password">
                                    <span class="material-symbols-outlined text-lg">key</span>
                                </button>

                                <!-- Change Subscription Button (PDF Page 3 & 20) -->
                                <button type="button" 
                                        @click="
                                            subActionUrl = '{{ route('admin.firms.change-subscription', $firm) }}';
                                            selectedFirmName = '{{ addslashes($firm->name) }}';
                                            selectedCurrentPlan = '{{ addslashes($firm->currentSubscription?->plan?->name ?? 'None') }}';
                                            selectedValidUpto = '{{ $firm->valid_upto ? $firm->valid_upto->format('d M Y') : 'None' }}';
                                            changeFirmSubModal = true;
                                        "
                                        class="p-1.5 rounded-lg text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5] transition-colors" 
                                        title="Upgrade / Change Subscription">
                                    <span class="material-symbols-outlined text-lg">upgrade</span>
                                </button>

                                <!-- View Firm -->
                                <a href="{{ route('admin.firms.show', $firm) }}" 
                                   class="p-1.5 rounded-lg text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5] transition-colors" 
                                   title="View Firm Overview">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </a>

                                <!-- Edit Firm -->
                                <a href="{{ route('admin.firms.edit', $firm) }}" 
                                   class="p-1.5 rounded-lg text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5] transition-colors" 
                                   title="Edit Firm Details">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>

                                <!-- Toggle Active Status -->
                                <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg text-[#766A5E] hover:text-amber-600 hover:bg-amber-50 transition-colors" 
                                            title="{{ $firm->status === 'active' ? 'Deactivate Firm' : 'Activate Firm' }}">
                                        <span class="material-symbols-outlined text-lg">{{ $firm->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                                    </button>
                                </form>

                                <!-- Delete Firm -->
                                <form method="POST" action="{{ route('admin.firms.destroy', $firm) }}" class="inline" 
                                      onsubmit="return confirm('Are you sure you want to delete law firm \'{{ addslashes($firm->name) }}\'? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg text-[#766A5E] hover:text-rose-600 hover:bg-rose-50 transition-colors" 
                                            title="Delete Firm">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-[#766A5E]">
                            <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">corporate_fare</span>
                            <p class="font-medium">No law firms found matching your criteria.</p>
                            <a href="{{ route('admin.firms.create') }}" class="inline-block mt-3 text-xs text-[#9F8349] font-semibold hover:underline">
                                Provision the first law firm &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($firms->hasPages())
        <div class="p-4 border-t border-[#EFECE6]">
            {{ $firms->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Change Firm Admin Password (PDF Page 2 & 3 Requirement) -->
    <div x-show="changeFirmPasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="changeFirmPasswordModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EFECE6] animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#FAF8F5] border border-[#EFECE6] flex items-center justify-center text-[#9F8349]">
                        <span class="material-symbols-outlined text-lg">key</span>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#222222]">Reset Firm Password</h3>
                        <p class="text-xs text-[#766A5E]" x-text="selectedFirmName"></p>
                    </div>
                </div>
                <button @click="changeFirmPasswordModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mt-4 p-3 rounded-xl bg-[#FAF8F5] border border-[#EFECE6] text-xs">
                <span class="text-[#766A5E]">Target Managing Partner / Admin User:</span>
                <span class="font-mono font-semibold text-[#222222] block mt-0.5" x-text="selectedAdminEmail || 'No user registered yet'"></span>
            </div>

            <form :action="passwordActionUrl" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">New Password (Min 8 chars) *</label>
                    <input type="password" name="new_password" required minlength="8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                           placeholder="Enter new password"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required minlength="8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                           placeholder="Confirm new password"/>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-[#EFECE6]">
                    <button type="button" @click="changeFirmPasswordModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#766A5E] hover:bg-[#FAF8F5] border border-[#EAE4DC]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-sm">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Change Firm Subscription Plan (PDF Page 3 & 20 Requirement) -->
    <div x-show="changeFirmSubModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="changeFirmSubModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EFECE6] animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#FAF8F5] border border-[#EFECE6] flex items-center justify-center text-[#9F8349]">
                        <span class="material-symbols-outlined text-lg">subscriptions</span>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#222222]">Update Subscription</h3>
                        <p class="text-xs text-[#766A5E]" x-text="selectedFirmName"></p>
                    </div>
                </div>
                <button @click="changeFirmSubModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mt-4 p-3 rounded-xl bg-[#FAF8F5] border border-[#EFECE6] text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-[#766A5E]">Current Tier:</span>
                    <span class="font-semibold text-[#222222]" x-text="selectedCurrentPlan"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#766A5E]">Expires On:</span>
                    <span class="font-mono text-[#222222]" x-text="selectedValidUpto"></span>
                </div>
            </div>

            <form :action="subActionUrl" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Assign Plan Tier *</label>
                    <select name="plan_id" required 
                            class="w-full px-3 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none bg-white">
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} (₹{{ number_format($plan->price) }} / {{ $plan->interval }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Duration (Months) *</label>
                    <select name="duration_months" required 
                            class="w-full px-3 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none bg-white">
                        <option value="1">1 Month (+30 Days)</option>
                        <option value="3">3 Months (+90 Days)</option>
                        <option value="6">6 Months (+180 Days)</option>
                        <option value="12" selected>12 Months (+365 Days / 1 Year)</option>
                        <option value="24">24 Months (2 Years)</option>
                        <option value="36">36 Months (3 Years)</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-[#EFECE6]">
                    <button type="button" @click="changeFirmSubModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#766A5E] hover:bg-[#FAF8F5] border border-[#EAE4DC]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-sm">
                        Apply Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
