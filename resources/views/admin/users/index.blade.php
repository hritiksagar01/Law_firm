@extends('layouts.admin')

@section('title', 'Clients & Users — Platform Console')
@section('header_title', 'Clients')

@section('content')
@php
    $firms = $firms ?? collect();
    $attorneys = $attorneys ?? collect();
@endphp
<div x-data="clientOnboardingState({{ $firms->first()?->id ?? 1 }}, '')" class="space-y-6">

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

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Clients &amp; Directory</h1>
            <p class="text-xs text-[#5e625e] mt-1">Platform client registry and personnel directory</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md bg-[#23493a] text-white text-xs font-semibold hover:bg-[#1a382c] transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">person_add</span>
                <span>Add Client</span>
            </button>
        </div>
    </div>

    <!-- 1. Client Portal Accounts & Firm Users Table -->
    <div class="bg-white border border-[#e5e3dc] rounded-lg shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-white flex items-center justify-between">
            <div>
                <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Client Portal &amp; Firm Personnel</h2>
                <p class="text-xs text-[#5e625e] mt-0.5">Direct representation accounts and chambers advocates</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-3 bg-[#f4f2ed] border-b border-[#e5e3dc]">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[240px]">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Search name, email, username, phone, or job title..."
                           class="w-full px-3 py-1.5 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] transition-colors" />
                </div>

                <!-- Firm Filter -->
                <div class="min-w-[140px]">
                    <div class="relative">
                        <select name="firm_id"
                                class="w-full px-3 py-1.5 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">All firms</option>
                            @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ request('firm_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Role Filter -->
                <div class="min-w-[130px]">
                    <div class="relative">
                        <select name="role"
                                class="w-full px-3 py-1.5 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">All roles</option>
                            <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                            <option value="attorney" {{ request('role') === 'attorney' ? 'selected' : '' }}>Attorney</option>
                            <option value="firm_admin" {{ request('role') === 'firm_admin' ? 'selected' : '' }}>Firm administrator</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="min-w-[110px]">
                    <div class="relative">
                        <select name="status"
                                class="w-full px-3 py-1.5 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">Any status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="invited" {{ request('status') === 'invited' ? 'selected' : '' }}>Invited</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="px-4 py-1.5 bg-white border border-[#dcdad4] text-[#1b1c18] hover:bg-[#f5f3ed] text-xs font-medium rounded-md transition-colors shadow-none cursor-pointer">
                    Filter
                </button>
                @if(request('q') || request('firm_id') || request('role') || request('status'))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-[#5e625e] hover:underline px-1">
                    Clear
                </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-medium">
                        <th class="py-3 px-4 font-medium">Client / Name</th>
                        <th class="py-3 px-4 font-medium">Role</th>
                        <th class="py-3 px-4 font-medium">Status</th>
                        <th class="py-3 px-4 font-medium">Firm / Practice</th>
                        <th class="py-3 px-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($firmAccounts as $u)
                    @php
                        $roleLabel = match($u->role) {
                            'partner' => 'Firm administrator',
                            'associate' => 'Attorney',
                            'paralegal', 'staff' => 'Staff',
                            'client' => 'Client',
                            default => ucfirst($u->role),
                        };
                        $isClient = ($u->role === 'client');
                    @endphp
                    <tr onclick="window.location='{{ route('admin.users.show', $u) }}'"
                        class="hover:bg-[#f4f2ed] cursor-pointer transition-colors group">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-[#1b1c18] group-hover:text-[#23493a] group-hover:underline text-[13px] block">
                                {{ $u->full_display_name }}
                            </a>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[11px] text-[#5e625e] font-mono">{{ $u->email }}</span>
                                @if($u->username)
                                <span class="text-[11px] text-[#8e8e8e] font-mono">&#64;{{ $u->username }}</span>
                                @endif
                                @if($u->mobile)
                                <span class="text-[11px] text-[#8e8e8e] font-mono">&middot; {{ $u->mobile }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($isClient)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#e0f2fe] text-[#0369a1] border border-[#bae6fd]">
                                Client
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#f5f3ed] text-[#5e625e] border border-[#e5e3dc]">
                                {{ $roleLabel }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($u->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                Active
                            </span>
                            @elseif($u->status === 'suspended')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                                Suspended
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#f3f4f6] text-[#4b5563] border border-[#e5e7eb]">
                                {{ ucfirst($u->status ?? 'Invited') }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18] font-sans">
                            {{ $u->firm ? $u->firm->name : 'Platform' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.users.show', $u) }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                                View &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 px-4 text-center text-xs text-[#5e625e]">
                            No user accounts match the current filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($firmAccounts->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white">
            {{ $firmAccounts->links() }}
        </div>
        @endif
    </div>

    <!-- 2. Platform Administrators Card (Dedicated Section) -->
    <div class="bg-white border border-[#e5e3dc] rounded-lg shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Platform Administrators</h2>
            <p class="text-xs text-[#5e625e] mt-0.5">Super administrators overseeing multi-tenant platform infrastructure</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-medium">
                        <th class="py-2.5 px-4 font-medium">Name</th>
                        <th class="py-2.5 px-4 font-medium">Role</th>
                        <th class="py-2.5 px-4 font-medium">Status</th>
                        <th class="py-2.5 px-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($platformAdmins as $admin)
                    <tr onclick="window.location='{{ route('admin.users.show', $admin) }}'"
                        class="hover:bg-[#f4f2ed] cursor-pointer transition-colors group">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.users.show', $admin) }}" class="font-medium text-[#1b1c18] group-hover:text-[#23493a] group-hover:underline text-[13px] block">
                                {{ $admin->full_display_name }}
                            </a>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[11px] text-[#5e625e] font-mono">{{ $admin->email }}</span>
                                @if($admin->username)
                                <span class="text-[11px] text-[#8e8e8e] font-mono">&#64;{{ $admin->username }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18] font-sans">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#f5f3ed] text-[#23493a] border border-[#E7E4DC]">
                                Super Admin
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                Active
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.users.show', $admin) }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                                Manage &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 px-4 text-center text-xs text-[#5e625e]">
                            No platform administrators registered.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Invite Staff Member (Preserved for compatibility and operations) -->
    <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-none">
        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Invite a staff member</h2>
        <p class="text-xs text-[#5e625e] mt-1 mb-5">
            Creates the user account, assigns firm permissions, and sends an invitation link with onboarding instructions.
        </p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Firm -->
                <div>
                    <label for="inv_firm" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Firm</label>
                    <div class="relative">
                        <select name="firm_id" id="inv_firm" required
                                class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="">Select a firm</option>
                            @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ old('firm_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label for="inv_role" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Role</label>
                    <div class="relative">
                        <select name="role" id="inv_role" required
                                class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="associate" {{ old('role') === 'associate' ? 'selected' : '' }}>Attorney</option>
                            <option value="partner" {{ old('role') === 'partner' ? 'selected' : '' }}>Firm administrator</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label for="inv_name" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Full Name</label>
                    <input type="text" name="name" id="inv_name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                </div>

                <!-- Username -->
                <div>
                    <label for="inv_username" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Username</label>
                    <input type="text" name="username" id="inv_username" value="{{ old('username') }}"
                           placeholder="johndoe"
                           class="w-full px-3 py-2 text-xs font-mono rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                </div>

                <!-- Email -->
                <div>
                    <label for="inv_email" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Email</label>
                    <input type="email" name="email" id="inv_email" required value="{{ old('email') }}"
                           class="w-full px-3 py-2 text-xs rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                </div>

                <!-- Mobile Phone -->
                <div>
                    <label for="inv_mobile" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Mobile Phone</label>
                    <input type="text" name="mobile" id="inv_mobile" value="{{ old('mobile') }}"
                           placeholder="+91 98110 00000"
                           class="w-full px-3 py-2 text-xs font-mono rounded-md border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-[#23493a] text-white text-xs font-semibold rounded-md hover:bg-[#1a382c] transition-colors shadow-xs cursor-pointer">
                    Invite Member &amp; Send Instructions
                </button>
            </div>
        </form>
    </div>

    @include('clients.partials.onboarding-modal', [
        'firms' => $firms,
        'attorneys' => $attorneys,
        'redirectTo' => 'admin',
    ])

</div>
@endsection
