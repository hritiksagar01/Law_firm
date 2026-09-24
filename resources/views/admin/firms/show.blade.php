@extends('layouts.admin')

@section('title', $firm->name . ' — Firms — Platform Console')

@section('content')
<div class="space-y-6" x-data="{ 
    copied: false,
    showSuspendConfirmModal: false,
    showDeleteConfirmModal: false,
    copyLink() {
        const copyText = document.getElementById('signupLink');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    }
}">

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-3.5 bg-[#f0fdf4] border border-[#bbf7d0] text-[#166534] rounded-md text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] rounded-md text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#dc2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Back Button & Breadcrumbs -->
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.firms.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#e5e3dc] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1a1a1a] transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Back to Firms &amp; Advocates</span>
            </a>
        </div>

        <nav class="text-xs text-[#5e625e] mb-1 font-sans flex items-center gap-1.5">
            <a href="{{ route('admin.firms.index') }}" class="hover:underline text-[#5e625e]">Firms</a>
            <span class="text-[#a3a5a8]">&nbsp;/&nbsp;</span>
            <span class="text-[#1b1c18] font-medium">{{ $firm->name }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">{{ $firm->name }}</h1>
            
            <!-- Quick Actions on Top Right -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if($firm->status === 'active')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Practice Active
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Suspended
                </span>
                @endif

                <!-- Suspend / Reactivate Action Button -->
                <button type="button" 
                        @click="showSuspendConfirmModal = true"
                        class="h-8 px-3 rounded-md text-xs font-medium border flex items-center gap-1.5 transition-colors cursor-pointer {{ $firm->status === 'active' ? 'border-[#ba1a1a]/40 text-[#ba1a1a] bg-white hover:bg-red-50' : 'bg-[#23493a] text-white hover:bg-[#1a382c]' }}">
                    <span class="material-symbols-outlined text-[15px]">{{ $firm->status === 'active' ? 'block' : 'check_circle' }}</span>
                    <span>{{ $firm->status === 'active' ? 'Suspend Firm' : 'Reactivate Firm' }}</span>
                </button>

                <!-- Delete Law Firm & Advocates Button -->
                <button type="button" 
                        @click="showDeleteConfirmModal = true"
                        class="h-8 px-3 rounded-md text-xs font-medium border border-red-200 text-[#ba1a1a] bg-white hover:bg-red-50 flex items-center gap-1.5 transition-colors cursor-pointer"
                        title="Delete firm and its advocates">
                    <span class="material-symbols-outlined text-[15px]">delete</span>
                    <span>Remove Firm</span>
                </button>
            </div>
        </div>

        <!-- Badges Row -->
        <div class="flex flex-wrap items-center gap-2.5 mt-2 text-xs">
            <span class="px-2.5 py-0.5 rounded font-mono text-[11.5px] bg-[#e4e2dd] text-[#1b1c18]">
                {{ $firm->slug }}
            </span>

            @if($firm->practice_type === 'individual')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#f3e8ff] text-[#7e22ce] border border-[#e9d5ff]">
                Individual Advocate
            </span>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#e0f2fe] text-[#0369a1] border border-[#bae6fd]">
                Law Firm / Chambers
            </span>
            @endif

            @if($firm->registration_number)
            <span class="text-[11px] text-[#5e625e] font-mono px-2 py-0.5 rounded bg-white border border-[#e5e3dc]">
                Reg: {{ $firm->registration_number }}
            </span>
            @endif

            <span class="text-[#5e625e] text-xs font-sans">
                Registered on {{ $firm->created_at->format('M j, Y') }}
            </span>
        </div>
    </div>

    <!-- Two Column Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Detailed Lawyers List, Profile, Recent Activity -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Dedicated Lawyers & Advocates Details Card (As requested: show every single detail of lawyers present in the firm) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[15px] font-semibold text-[#1a1a1a] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-[#23493a]">gavel</span>
                            <span>Lawyers &amp; Advocates in Practice</span>
                        </h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">
                            {{ count($lawyers) }} active advocates &middot; Full counsel roster and court assignments
                        </p>
                    </div>
                    <a href="{{ route('admin.users.index', ['firm_id' => $firm->id, 'role' => 'attorney']) }}" class="text-xs text-[#23493a] hover:underline font-semibold flex items-center gap-1">
                        <span>Manage in Users Directory</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] font-medium bg-[#faf8f5]">
                                <th class="py-2.5 px-3 font-medium">Advocate Name</th>
                                <th class="py-2.5 px-3 font-medium">Designation &amp; Role</th>
                                <th class="py-2.5 px-3 font-medium">Bar Reg / Contact</th>
                                <th class="py-2.5 px-3 font-medium">Status</th>
                                <th class="py-2.5 px-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($lawyers as $lawyer)
                            @php
                                $roleDisplay = match($lawyer->role) {
                                    'partner' => 'Senior Advocate & Managing Partner',
                                    'associate' => 'Advocate / Associate Counsel',
                                    default => ucfirst($lawyer->role),
                                };
                            @endphp
                            <tr class="hover:bg-[#f9f8f5] transition-colors">
                                <td class="py-3 px-3">
                                    <a href="{{ route('admin.users.show', $lawyer) }}" class="font-semibold text-[#1b1c18] hover:text-[#23493a] hover:underline text-[13px] block">
                                        {{ $lawyer->name }}
                                    </a>
                                    <span class="text-[11px] text-[#5e625e] font-mono block mt-0.5">
                                        {{ $lawyer->email }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-[#1b1c18] font-sans">
                                    <span class="font-medium text-[12px] block">{{ $lawyer->title ?? $roleDisplay }}</span>
                                    <span class="text-[11px] text-[#8a8a8a] block">{{ $roleDisplay }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11.5px] text-[#5e625e]">
                                    <div>{{ $lawyer->phone ?? ($lawyer->mobile ?? '—') }}</div>
                                    @if($lawyer->id_number)
                                        <div class="text-[10.5px] text-[#8a8a8a]">ID: {{ $lawyer->id_number }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($lawyer->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                        Active Counsel
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                                        Suspended
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('admin.users.show', $lawyer) }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                                        Details &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 px-3 text-center text-xs text-[#5e625e]">
                                    No lawyers or advocates assigned to this firm yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Profile & Practice Information Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a] mb-4">Practice Profile &amp; Contact Details</h2>

                <form method="POST" action="{{ route('admin.firms.update', $firm) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Firm name -->
                        <div>
                            <label for="prof_name" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Practice / Firm Name</label>
                            <input type="text" name="name" id="prof_name" required value="{{ old('name', $firm->name) }}"
                                   class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Bar Registration Number -->
                        <div>
                            <label for="prof_reg" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Bar Registration / License No.</label>
                            <input type="text" name="registration_number" id="prof_reg" value="{{ old('registration_number', $firm->registration_number) }}"
                                   placeholder="e.g. D/1429/2012"
                                   class="w-full px-3 py-2 text-xs font-mono rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="prof_email" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Official Chambers Email</label>
                            <input type="email" name="email" id="prof_email" value="{{ old('email', $firm->email) }}"
                                   placeholder="office@{{ $firm->slug }}.example"
                                   class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="prof_phone" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Phone Number</label>
                            <input type="text" name="phone" id="prof_phone" value="{{ old('phone', $firm->phone) }}"
                                   placeholder="+91 98110 00000"
                                   class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Website -->
                        <div>
                            <label for="prof_website" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Website</label>
                            <input type="text" name="website" id="prof_website" value="{{ old('website', $firm->website) }}"
                                   placeholder="https://chambers.example.in"
                                   class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- City & State -->
                        <div>
                            <label for="prof_city" class="block text-xs font-medium text-[#1b1c18] mb-1.5">City &amp; State</label>
                            <input type="text" name="city" id="prof_city" value="{{ old('city', $firm->city ?? 'New Delhi') }}"
                                   placeholder="New Delhi, Delhi"
                                   class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="prof_address" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Chambers / Office Address</label>
                            <textarea name="address" id="prof_address" rows="2"
                                      class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]">{{ old('address', $firm->address) }}</textarea>
                        </div>

                        <!-- Client registration checkbox -->
                        <div class="md:col-span-2 pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="allow_client_signup" value="1" 
                                       {{ old('allow_client_signup', $firm->allow_client_signup ?? true) ? 'checked' : '' }}
                                       class="rounded border-[#717974] text-[#23493a] focus:ring-[#23493a]" />
                                <span class="text-xs text-[#1b1c18]">Clients can register directly from practice client portal</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-[#23493a] text-white text-xs font-semibold rounded-md hover:bg-[#1a382c] transition-colors shadow-xs cursor-pointer">
                            Save Practice Profile
                        </button>
                    </div>
                </form>

                <!-- Client sign-up link Box -->
                <div class="mt-6 pt-5 border-t border-[#e5e3dc]">
                    <label class="block text-xs font-medium text-[#1b1c18] mb-1.5">Client Portal Direct Access Link</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="signupLink" readonly
                               value="{{ url('/join/' . $firm->slug) }}"
                               class="flex-1 px-3 py-2 text-xs font-mono rounded-md border border-[#dcdad4] bg-[#faf8f5] text-[#1b1c18] select-all" />
                        <button type="button" @click="copyLink()"
                                class="px-4 py-2 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors cursor-pointer">
                            <span x-show="!copied">Copy Link</span>
                            <span x-show="copied" class="text-[#166534] font-semibold">Copied!</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- All People Card (Staff & Client Accounts) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">All Associated Users &amp; Client Accounts</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">
                            {{ $staffCount }} staff accounts &middot; {{ $clientPortalCount }} client portal accounts
                        </p>
                    </div>
                    <a href="{{ route('admin.users.index', ['firm_id' => $firm->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium">
                        View in Users &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] font-normal">
                                <th class="pb-2 px-3 font-normal">Name</th>
                                <th class="pb-2 px-3 font-normal">Role</th>
                                <th class="pb-2 px-3 font-normal">Status</th>
                                <th class="pb-2 px-3 font-normal">Last Updated</th>
                                <th class="pb-2 px-3 font-normal text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($users as $u)
                            @php
                                $roleDisplay = match($u->role) {
                                    'partner' => 'Firm administrator / Partner',
                                    'associate' => 'Attorney',
                                    'paralegal', 'staff' => 'Staff',
                                    'client' => 'Client',
                                    default => ucfirst($u->role),
                                };
                                $isClient = ($u->role === 'client');
                            @endphp
                            <tr class="hover:bg-[#f4f2ed] transition-colors">
                                <td class="py-2.5 px-3">
                                    <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-[#1b1c18] hover:text-[#23493a] hover:underline block">
                                        {{ $isClient ? 'Client portal user' : $u->name }}
                                    </a>
                                    <span class="text-[11px] text-[#5e625e] font-mono block">
                                        {{ $isClient ? substr($u->email, 0, 1) . '***@' . explode('@', $u->email)[1] : $u->email }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-[#1b1c18] font-sans">
                                    {{ $roleDisplay }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                        Active
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-[#5e625e] font-sans">
                                    {{ $u->updated_at ? $u->updated_at->diffForHumans(null, true) : 'Never' }}
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a href="{{ route('admin.users.show', $u) }}" class="text-[11.5px] text-[#23493a] hover:underline font-medium">
                                        Manage &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 px-3 text-center text-xs text-[#5e625e]">
                                    No personnel assigned to this firm yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Usage & Firm Access (One-click Toggle Action) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Practice Usage Telemetry Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Usage Telemetry</h2>
                <p class="text-xs text-[#5e625e] mt-0.5 mb-4">
                    Active counts across practice docket
                </p>

                <div class="grid grid-cols-2 gap-y-4 gap-x-3 text-xs font-sans">
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Matters</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->matters_count }} <span class="text-xs font-normal text-[#5e625e]">({{ $openMattersCount }} open)</span>
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Clients</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->clients_count }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Documents</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->documents_count }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Vault Storage</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $storageFormatted }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Staff &amp; Counsel</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $staffCount }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Client Accounts</span>
                        <strong class="font-semibold text-[#1b1c18] text-sm block mt-0.5">
                            {{ $clientPortalCount }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Firm Access Action (One-Click Button with Confirmation Modal instead of typing name) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Firm Access Status</h2>
                <p class="text-xs text-[#5e625e] mt-1 mb-4 leading-relaxed">
                    @if($firm->status === 'active')
                        This firm is currently <strong>active</strong>. Suspending will immediately restrict access for {{ $staffCount }} staff and {{ $clientPortalCount }} clients without deleting any case data.
                    @else
                        This firm is currently <strong>suspended</strong>. Reactivating will immediately restore access for all staff and clients.
                    @endif
                </p>

                <!-- One-Click Toggle Action Button -->
                <button type="button" 
                        @click="showSuspendConfirmModal = true"
                        class="w-full py-2.5 px-4 rounded-md text-xs font-semibold transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2 {{ $firm->status === 'active' ? 'border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5]' : 'bg-[#23493a] text-white hover:bg-[#1a382c]' }}">
                    <span class="material-symbols-outlined text-[16px]">{{ $firm->status === 'active' ? 'block' : 'check_circle' }}</span>
                    <span>{{ $firm->status === 'active' ? 'Suspend Law Firm' : 'Reactivate Law Firm' }}</span>
                </button>
            </div>

        </div>

    </div>

    <!-- Confirmation Modal for Status Toggle -->
    <div x-show="showSuspendConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showSuspendConfirmModal = false"
             class="bg-white border border-[#e5e3dc] rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center gap-3 pb-3 border-b border-[#f0eee8]">
                <span class="p-2 rounded-full {{ $firm->status === 'active' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $firm->status === 'active' ? 'warning' : 'verified' }}</span>
                </span>
                <div>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">
                        {{ $firm->status === 'active' ? 'Suspend Practice Access?' : 'Reactivate Practice Access?' }}
                    </h3>
                    <p class="text-xs text-[#5e625e]">Confirmation required</p>
                </div>
            </div>

            <p class="text-xs text-[#414844] mt-4 leading-relaxed">
                @if($firm->status === 'active')
                    Are you sure you want to suspend <strong>{{ $firm->name }}</strong>? Staff and clients will be temporarily unable to sign in to this chambers workspace until reactivated.
                @else
                    Are you sure you want to reactivate <strong>{{ $firm->name }}</strong>? Workspace and client portal access will be immediately restored.
                @endif
            </p>

            <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                <button type="button" @click="showSuspendConfirmModal = false"
                        class="px-4 py-2 border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-md text-xs font-semibold text-white transition-colors cursor-pointer {{ $firm->status === 'active' ? 'bg-[#ba1a1a] hover:bg-[#991b1b]' : 'bg-[#23493a] hover:bg-[#1a382c]' }}">
                    {{ $firm->status === 'active' ? 'Yes, Suspend Firm' : 'Yes, Reactivate Firm' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal for Delete Firm -->
    <div x-show="showDeleteConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showDeleteConfirmModal = false"
             class="bg-white border border-[#e5e3dc] rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center gap-3 pb-3 border-b border-[#f0eee8]">
                <span class="p-2 rounded-full bg-red-100 text-red-700">
                    <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                </span>
                <div>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Delete Law Firm &amp; Advocates?</h3>
                    <p class="text-xs text-red-600 font-medium">Permanent action cannot be undone</p>
                </div>
            </div>

            <p class="text-xs text-[#414844] mt-4 leading-relaxed">
                Are you sure you want to permanently delete <strong>{{ $firm->name }}</strong>? This will remove the firm, all {{ $staffCount }} advocate accounts, {{ $clientPortalCount }} client dossiers, and associated case files from the platform.
            </p>

            <form method="POST" action="{{ route('admin.firms.destroy', $firm) }}" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteConfirmModal = false"
                        class="px-4 py-2 border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-md text-xs font-semibold text-white bg-[#ba1a1a] hover:bg-[#991b1b] transition-colors cursor-pointer">
                    Yes, Delete Firm &amp; Advocates
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
