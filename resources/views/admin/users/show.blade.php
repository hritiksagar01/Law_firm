@extends('layouts.admin')

@section('title', $user->name . ' — Users — Platform Console')
@section('header_title', 'User Details')

@section('content')
<div class="space-y-6" x-data="{ editModalOpen: false }">

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

    <!-- Breadcrumb & Header Bar -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <nav class="text-xs text-[#5e625e] mb-1 font-sans flex items-center gap-1.5">
                <a href="{{ route('admin.users.index') }}" class="hover:underline text-[#5e625e]">Users</a>
                <span class="text-[#a3a5a8]">&nbsp;/&nbsp;</span>
                <span class="text-[#1b1c18] font-medium">{{ $user->full_display_name }}</span>
            </nav>

            <div class="flex items-center gap-4 mt-2">
                <!-- Avatar -->
                @if($user->resolved_avatar)
                    <img src="{{ $user->resolved_avatar }}" alt="{{ $user->name }}" class="w-13 h-13 rounded-full object-cover border border-[#dcdad4] shadow-xs shrink-0" />
                @else
                    @php
                        $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->surname ?? '', 0, 1));
                        if (strlen($initials) < 2) {
                            $initials = strtoupper(substr($user->name, 0, 2));
                        }
                    @endphp
                    <div class="w-13 h-13 rounded-full bg-[#23493a] text-white flex items-center justify-center font-serif text-lg font-medium shadow-xs shrink-0">
                        {{ $initials }}
                    </div>
                @endif

                <div>
                    <h1 class="text-2xl sm:text-3xl font-serif text-[#1b1c18] tracking-tight">
                        {{ $user->full_display_name }}
                    </h1>

                    @php
                        $roleLabel = match($user->role) {
                            'partner' => 'Firm administrator',
                            'associate' => 'Attorney',
                            'paralegal', 'staff' => 'Paralegal / staff',
                            'client' => 'Client',
                            'superadmin' => 'Platform admin',
                            default => ucfirst($user->role),
                        };
                    @endphp

                    <!-- Meta Badges Line -->
                    <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs font-sans">
                        <span class="text-[#5e625e] font-mono text-[11px]">{{ $user->email }}</span>

                        @if($user->username)
                        <span class="text-[#8e8e8e] font-mono text-[11px]">&#64;{{ $user->username }}</span>
                        @endif

                        <!-- Status Badge -->
                        @if($user->status === 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                            Active
                        </span>
                        @elseif($user->status === 'suspended')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                            Suspended
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#f3f4f6] text-[#4b5563] border border-[#e5e7eb]">
                            {{ ucfirst($user->status ?? 'Inactive') }}
                        </span>
                        @endif

                        <!-- Email Verification Badge -->
                        @if($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                            <span class="material-symbols-outlined text-[13px]">verified</span> Verified
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fffbeb] text-[#92400e] border border-[#fde68a]">
                            <span class="material-symbols-outlined text-[13px]">pending</span> Unverified
                        </span>
                        @endif

                        <!-- MFA Status Badge -->
                        @if($user->two_factor_confirmed_at)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#f0fdf4] text-[#15803d] border border-[#bbf7d0]">
                            <span class="material-symbols-outlined text-[13px]">lock</span> MFA Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#f9fafb] text-[#6b7280] border border-[#e5e7eb]">
                            <span class="material-symbols-outlined text-[13px]">no_encryption</span> MFA Off
                        </span>
                        @endif

                        <span class="text-[#1b1c18] font-medium">&bull; {{ $roleLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5 pt-2 md:pt-0">
            <!-- Edit Button -->
            <button type="button" 
                    @click="editModalOpen = true"
                    class="h-8 px-3.5 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-base">edit</span>
                <span>Edit Profile</span>
            </button>

            <!-- Resend Invitation -->
            <form method="POST" action="{{ route('admin.users.resend-invitation', $user) }}">
                @csrf
                <button type="submit" 
                        class="h-8 px-3.5 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer"
                        title="Resend invitation onboarding email">
                    <span class="material-symbols-outlined text-base">outgoing_mail</span>
                    <span>Resend Invite</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Two Column Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Account, Active sessions, Sign-in attempts, Recent actions -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Account Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <h2 class="text-sm font-semibold text-[#1b1c18]">User Account &amp; Profile Details</h2>
                    <span class="text-xs text-[#5e625e] font-mono">ID: #{{ $user->id }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-6 text-xs">
                    <!-- User ID / UUID -->
                    <div class="sm:col-span-2 md:col-span-3 bg-[#faf8f5] p-3 rounded border border-[#f0eee8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <span class="text-[#5e625e] block text-[11px] font-sans">Unique Identifier (UUID)</span>
                            <span class="font-mono text-[12px] text-[#1b1c18] font-medium select-all block mt-0.5">
                                {{ $user->uuid ?? 'Not assigned' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[#5e625e] block text-[11px] font-sans">Database ID</span>
                            <span class="font-mono text-[12px] text-[#1b1c18] font-medium">
                                #{{ $user->id }}
                            </span>
                        </div>
                    </div>

                    <!-- Display Name -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Display Name</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->full_display_name }}
                        </span>
                    </div>

                    <!-- First Name -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">First Name</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->first_name ?? '—' }}
                        </span>
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Middle Name</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->middle_name ?? '—' }}
                        </span>
                    </div>

                    <!-- Last Name / Surname -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Last Name / Surname</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->surname ?? '—' }}
                        </span>
                    </div>

                    <!-- Username -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Username</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->username ? '@' . $user->username : '—' }}
                        </span>
                    </div>

                    <!-- Email -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Primary Email</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->email }}
                        </span>
                    </div>

                    <!-- Mobile -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Mobile Phone</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->mobile ?? '—' }}
                        </span>
                    </div>

                    <!-- Phone -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Office Phone</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->phone ?? '—' }}
                        </span>
                    </div>

                    <!-- Firm -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Firm</span>
                        @if($user->firm)
                        <a href="{{ route('admin.firms.show', $user->firm) }}" class="text-[#23493a] hover:underline font-medium block mt-0.5">
                            {{ $user->firm->name }}
                        </a>
                        @else
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">Platform Administration</span>
                        @endif
                    </div>

                    <!-- Job Title -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Job Title</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->title ?? 'Legal Practitioner' }}
                        </span>
                    </div>

                    <!-- Role -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">System Role</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $roleLabel }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Account Status</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium capitalize">
                            {{ $user->status ?? 'active' }}
                        </span>
                    </div>

                    <!-- Email Verified At -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Email Verification</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('M j, Y, g:i A') . ' UTC' : 'Pending verification' }}
                        </span>
                    </div>

                    <!-- Two-Step Sign-In -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">MFA Two-Step Auth</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->two_factor_confirmed_at ? 'Enabled (' . $user->two_factor_confirmed_at->format('M j, Y') . ')' : 'Disabled' }}
                        </span>
                    </div>

                    <!-- Last Sign-in -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Last Sign-in</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->last_login_at ? $user->last_login_at->format('M j, Y, g:i A') . ' UTC' : ($user->updated_at ? $user->updated_at->format('M j, Y, g:i A') . ' UTC' : 'Never') }}
                        </span>
                    </div>

                    <!-- Account Created -->
                    <div class="sm:col-span-2 md:col-span-3 pt-2 border-t border-[#f0eee8]">
                        <span class="text-[#5e625e] block text-[11px]">Member Since</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->created_at->format('M j, Y, g:i A') }} UTC ({{ $user->created_at->diffForHumans() }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-[#1b1c18]">Active Sessions</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">{{ count($activeSessions) }} signed-in devices</p>
                    </div>
                    @if(count($activeSessions) > 0)
                    <form method="POST" action="{{ route('admin.users.sign-out-everywhere', $user) }}">
                        @csrf
                        <button type="submit" class="text-xs text-[#ba1a1a] hover:underline cursor-pointer">
                            Terminate all
                        </button>
                    </form>
                    @endif
                </div>

                @if(count($activeSessions) > 0)
                <div class="overflow-x-auto border border-[#f0eee8] rounded-sm">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[11px] text-[#5e625e]">
                                <th class="py-2 px-3 font-normal">IP Address</th>
                                <th class="py-2 px-3 font-normal">Last Activity</th>
                                <th class="py-2 px-3 font-normal">User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @foreach($activeSessions as $s)
                            <tr>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-[#1b1c18]">{{ $s->ip_address ?? '—' }}</td>
                                <td class="py-2.5 px-3 text-[#5e625e] whitespace-nowrap">
                                    {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-3 text-[#414844] truncate max-w-[280px]">
                                    {{ $s->user_agent ?? 'Unknown browser' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-8 px-4 text-center text-xs text-[#5e625e]">
                    Not currently signed in on any device
                </div>
                @endif
            </div>

            <!-- Login History (Sign-in Attempts) Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-[#1b1c18]">Login History</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">Recent sign-in attempts and access logs</p>
                    </div>
                </div>

                @if($signInHistories->isNotEmpty())
                <div class="overflow-x-auto border border-[#f0eee8] rounded-sm">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[11px] text-[#5e625e]">
                                <th class="py-2 px-3 font-normal">Timestamp</th>
                                <th class="py-2 px-3 font-normal">Result</th>
                                <th class="py-2 px-3 font-normal">Device / Browser</th>
                                <th class="py-2 px-3 font-normal text-right">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @foreach($signInHistories as $attempt)
                            <tr>
                                <td class="py-2.5 px-3 text-[#5e625e] whitespace-nowrap">
                                    {{ $attempt->created_at->format('M j, Y, g:i A') }} UTC
                                </td>
                                <td class="py-2.5 px-3">
                                    @if($attempt->result === 'signed_in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#dcfce7] text-[#166534]">
                                        Success
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#fee2e2] text-[#991b1b]">
                                        Failed ({{ $attempt->failure_reason ?? 'Error' }})
                                    </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-[#414844]">{{ $attempt->device ?? 'Chrome on Windows' }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-[11px] text-[#5e625e]">{{ $attempt->ip_address ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-8 px-4 text-center text-xs text-[#5e625e]">
                    No recorded sign-in attempts for this user yet
                </div>
                @endif
            </div>

            <!-- Recent Actions by this account Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-[#1b1c18]">Recent Actions by this Account</h2>
                    <span class="text-xs text-[#5e625e]">Platform Audit Log</span>
                </div>

                @if($auditLogs->isNotEmpty())
                <div class="overflow-x-auto border border-[#f0eee8] rounded-sm">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[11px] text-[#5e625e]">
                                <th class="py-2 px-3 font-normal">Timestamp</th>
                                <th class="py-2 px-3 font-normal">Action</th>
                                <th class="py-2 px-3 font-normal">Record</th>
                                <th class="py-2 px-3 font-normal text-right">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @foreach($auditLogs as $log)
                            <tr>
                                <td class="py-2.5 px-3 text-[#5e625e] whitespace-nowrap">
                                    {{ $log->created_at->format('M j, Y, g:i A') }} UTC
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="font-medium text-[#1b1c18] block">{{ $log->action_label }}</span>
                                    <span class="font-mono text-[10.5px] text-[#717974] block">{{ $log->action }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-[#414844]">{{ $log->record_type }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-[11px] text-[#5e625e]">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-8 px-4 text-center text-xs text-[#5e625e]">
                    No recorded actions in platform audit trail
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Role, Status & Access Security -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Role Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18] mb-1.5">Role Management</h2>
                <p class="text-xs text-[#5e625e] mb-3">Adjust user RBAC permissions and capabilities</p>

                <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="space-y-3">
                    @csrf
                    <div class="relative">
                        <select name="role"
                                class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="firm_admin" {{ ($user->role === 'partner') ? 'selected' : '' }}>Firm administrator</option>
                            <option value="attorney" {{ ($user->role === 'associate') ? 'selected' : '' }}>Attorney</option>
                            <option value="paralegal" {{ in_array($user->role, ['paralegal', 'staff']) ? 'selected' : '' }}>Paralegal / staff</option>
                            <option value="client" {{ ($user->role === 'client') ? 'selected' : '' }}>Client</option>
                            <option value="platform_admin" {{ ($user->role === 'superadmin') ? 'selected' : '' }}>Platform admin</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none cursor-pointer">
                            Update role
                        </button>
                    </div>
                </form>
            </div>

            <!-- Status Switcher Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18] mb-1.5">Account Status</h2>
                <p class="text-xs text-[#5e625e] mb-3">Activate, deactivate, or suspend access</p>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-3">
                    @csrf
                    <div class="relative">
                        <select name="status"
                                class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                            <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none cursor-pointer">
                            Save status
                        </button>
                    </div>
                </form>
            </div>

            <!-- Access & Security Controls Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none space-y-4">
                <h2 class="text-sm font-semibold text-[#1b1c18]">Security Controls</h2>

                <!-- Send Password Reset -->
                <div>
                    <p class="text-xs text-[#5e625e] mb-2">
                        Emails a one-hour password reset link to <span class="font-mono text-[11px]">{{ $user->email }}</span>.
                    </p>
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none cursor-pointer">
                            Send password reset
                        </button>
                    </form>
                </div>

                <!-- Force Logout / End All Active Sessions -->
                <div class="pt-3 border-t border-[#f0eee8]">
                    <p class="text-xs text-[#5e625e] mb-2">
                        Invalidates remember tokens and removes all active device sessions immediately.
                    </p>
                    <form method="POST" action="{{ route('admin.users.sign-out-everywhere', $user) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none cursor-pointer">
                            Force logout all devices
                        </button>
                    </form>
                </div>

                <!-- Toggle / Suspend Quick Button -->
                <div class="pt-3 border-t border-[#f0eee8]">
                    <p class="text-xs text-[#5e625e] mb-2">
                        Blocks login access immediately while preserving all case files and audit history.
                    </p>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-3.5 py-1.5 border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5] text-xs font-medium rounded-sm transition-colors shadow-none cursor-pointer">
                            {{ $user->status === 'active' ? 'Suspend user account' : 'Reactivate user account' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="editModalOpen = false"
             class="bg-white border border-[#e5e3dc] rounded-md max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                <h3 class="font-serif text-lg font-medium text-[#1b1c18]">Edit User Profile</h3>
                <button @click="editModalOpen = false" class="text-[#8e8e8e] hover:text-[#1b1c18]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Full Name (Display)</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                               placeholder="e.g. jdoe"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- First Name -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Surname / Last Name -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Last Name / Surname</label>
                        <input type="text" name="surname" value="{{ old('surname', $user->surname) }}"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Mobile Phone -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Mobile Phone</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}"
                               placeholder="e.g. +1 (555) 234-5678"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Office Phone -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Office Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Job Title -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Job Title</label>
                        <input type="text" name="title" value="{{ old('title', $user->title) }}"
                               placeholder="e.g. Senior Partner"
                               class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a] focus:outline-none" />
                    </div>

                    <!-- Firm Select -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Firm</label>
                        <select name="firm_id" class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a]">
                            <option value="">None (Platform Administrator)</option>
                            @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ (string) old('firm_id', $user->firm_id) === (string) $f->id ? 'selected' : '' }}>
                                {{ $f->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Role</label>
                        <select name="role" class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a]">
                            <option value="partner" {{ old('role', $user->role) === 'partner' ? 'selected' : '' }}>Firm Administrator</option>
                            <option value="associate" {{ old('role', $user->role) === 'associate' ? 'selected' : '' }}>Attorney</option>
                            <option value="paralegal" {{ old('role', $user->role) === 'paralegal' ? 'selected' : '' }}>Paralegal</option>
                            <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Client</option>
                            <option value="superadmin" {{ old('role', $user->role) === 'superadmin' ? 'selected' : '' }}>Platform Admin</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-medium text-[#1b1c18] mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-1.5 border border-[#dcdad4] rounded-sm bg-white text-xs focus:border-[#23493a]">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#f0eee8]">
                    <button type="button" @click="editModalOpen = false" class="px-3.5 py-1.5 border border-[#c1c8c3] rounded-sm text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1.5 bg-[#23493a] hover:bg-[#1a382c] text-white rounded-sm text-xs font-medium shadow-none cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
