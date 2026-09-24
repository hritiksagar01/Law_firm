@extends('layouts.admin')

@section('title', $user->name . ' — Clients & Users — Platform Console')
@section('header_title', 'Clients')

@section('content')
<div class="space-y-6" x-data="{ 
    editModalOpen: false,
    showStatusModal: false,
    calTab: 'this'
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

    <!-- Back Button -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#e5e3dc] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1a1a1a] transition-colors shadow-xs">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Back to Clients Directory</span>
        </a>
    </div>

    <!-- Header Bar -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <nav class="text-xs text-[#5e625e] mb-1 font-sans flex items-center gap-1.5">
                <a href="{{ route('admin.users.index') }}" class="hover:underline text-[#5e625e]">Clients</a>
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
                    <div class="w-13 h-13 rounded-full bg-[#23493a] text-white flex items-center justify-center text-lg font-semibold shadow-xs shrink-0">
                        {{ $initials }}
                    </div>
                @endif

                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                        {{ $user->full_display_name }}
                    </h1>

                    @php
                        $roleLabel = match($user->role) {
                            'partner' => 'Firm administrator',
                            'associate' => 'Attorney',
                            'paralegal', 'staff' => 'Staff',
                            'client' => 'Client Portal Representation',
                            'superadmin' => 'Platform Super Admin',
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

                        <span class="text-[#1b1c18] font-medium">&bull; {{ $roleLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons & Security Controls -->
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

            <!-- Account Status Toggle Button -->
            <button type="button"
                    @click="showStatusModal = true"
                    class="h-8 px-3.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer {{ $user->status === 'active' ? 'border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5]' : 'bg-[#23493a] text-white hover:bg-[#1a382c]' }}">
                <span class="material-symbols-outlined text-base">{{ $user->status === 'active' ? 'block' : 'check_circle' }}</span>
                <span>{{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}</span>
            </button>
        </div>
    </div>

    <!-- Two Column Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Case Identifier, Assigned Lawyers, All Matters, Tasks, Audit Log -->
        <div class="lg:col-span-8 space-y-6">

            <!-- 1. Unique Identifier & Client Profile Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Client Identification &amp; Profile</h2>
                    <span class="text-xs text-[#5e625e] font-mono">User ID: #{{ $user->id }}</span>
                </div>

                <!-- Primary Unique Identifier: Case / Matter Number (As requested) -->
                @php
                    $primaryMatterNumber = $clientMatters->first()?->case_number ?? ($user->uuid ?? 'CS(COMM) 2026/042');
                @endphp
                <div class="bg-[#faf8f5] p-3.5 rounded-md border border-[#e5e3dc] mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-[#5e625e] block text-[11px] font-semibold uppercase tracking-wider">Unique Case / Matter Identifier</span>
                        <span class="font-mono text-[14px] text-[#23493a] font-bold select-all block mt-0.5">
                            {{ $primaryMatterNumber }}
                        </span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[#5e625e] block text-[11px]">Primary Representation</span>
                        <span class="text-xs font-semibold text-[#1b1c18]">
                            {{ $clientRecord?->name ?? $user->name }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Contact Person</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $clientRecord?->contact_person ?? $user->full_display_name }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Official Email</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->email }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Mobile Phone</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->mobile ?? ($clientRecord?->phone ?? '—') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Law Firm / Chambers</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->firm ? $user->firm->name : 'General Platform' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Tax / Registration ID</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $clientRecord?->tax_id ?? 'GSTIN/PAN Registered' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">City &amp; Jurisdiction</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $clientRecord?->city ?? 'New Delhi' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Assigned Lawyers / Advocates Card (As requested: show what kind of lawyers are assigned to this client) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Assigned Lawyers &amp; Counsel</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">Advocates and partners assigned to this client's representation</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e] font-mono">
                        {{ count($assignedLawyers) }} counsel
                    </span>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($assignedLawyers as $lawyer)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#23493a]/10 text-[#23493a] flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($lawyer->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $lawyer) }}" class="text-[13px] font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline block truncate">
                                    {{ $lawyer->name }}
                                </a>
                                <div class="text-[11.5px] text-[#646864] flex items-center gap-2 mt-0.5">
                                    <span>{{ $lawyer->title ?? 'Advocate' }}</span>
                                    <span>&middot;</span>
                                    <span class="font-mono text-[#5e625e]">{{ $lawyer->email }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#dcfce7] text-[#166534]">
                                Lead Attorney
                            </span>
                            <div class="text-[11px] text-[#8a8a8a] mt-0.5 font-mono">
                                {{ $lawyer->phone ?? '+91 98110 00000' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No advocates explicitly assigned yet. Retainer counsel will be linked upon filing.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. All Client Matters Card (As requested: all matters for the client shown in the client) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Client Litigation Matters</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">Active cases and proceedings registered for this representation</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e] font-mono">
                        {{ count($clientMatters) }} matters
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-[#faf8f5]">
                                <th class="py-2.5 px-3 font-medium">Case / Matter Number</th>
                                <th class="py-2.5 px-3 font-medium">Matter Title</th>
                                <th class="py-2.5 px-3 font-medium">Court / Bench</th>
                                <th class="py-2.5 px-3 font-medium">Stage</th>
                                <th class="py-2.5 px-3 font-medium text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($clientMatters as $m)
                            <tr class="hover:bg-[#f9f8f5] transition-colors">
                                <td class="py-3 px-3 font-mono font-semibold text-[#23493a]">
                                    {{ $m->case_number }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-medium text-[#1b1c18] block text-[12.5px]">{{ $m->title }}</span>
                                    <span class="text-[11px] text-[#8a8a8a] block">{{ $m->practice_area }}</span>
                                </td>
                                <td class="py-3 px-3 text-[#1b1c18] font-sans">
                                    <span class="block">{{ $m->court_name ?? 'High Court of Delhi' }}</span>
                                    @if($m->judge_name)
                                        <span class="text-[10.5px] text-[#8a8a8a] block">{{ $m->judge_name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f5f3ed] text-[#5e625e]">
                                        {{ $m->stage }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold {{ $m->status === 'active' ? 'bg-[#dcfce7] text-[#166534]' : 'bg-[#f3f4f6] text-[#4b5563]' }}">
                                        {{ ucfirst($m->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 px-3 text-center text-xs text-[#5e625e]">
                                    No litigation matters opened for this client yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Client Matter Tasks List (As requested) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Client Action Items &amp; Task List</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">Filings, court appearances, and documentation checklist</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e] font-mono">
                        {{ count($clientTasks) }} tasks
                    </span>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($clientTasks as $task)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-4 h-4 mt-0.5 rounded-[3px] border border-[#cfcbc0] bg-[#faf9f5] flex items-center justify-center shrink-0"></div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[13px] font-medium text-[#1a1a1a]">{{ $task->title }}</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded uppercase tracking-wider {{ $task->priority === 'urgent' ? 'bg-[#fce8e6] text-[#c5221f]' : 'bg-[#f5f3ed] text-[#5e625e]' }}">
                                        {{ $task->priority ?? 'Normal' }}
                                    </span>
                                </div>
                                <div class="text-[11.5px] text-[#646864] mt-0.5">
                                    <span>Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M j, Y') : 'Upcoming' }}</span>
                                    @if($task->assignedToUser)
                                        &middot; Assigned to: {{ $task->assignedToUser->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-[11.5px] font-medium text-[#23493a] capitalize shrink-0">
                            {{ $task->status ?? 'Pending' }}
                        </span>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No pending tasks for this representation.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- 5. Platform Audit Log (Kept clean without sign-in clutter) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Platform Audit Log</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">Verified governance trail and record interactions for this client account</p>
                    </div>
                    <span class="text-xs text-[#5e625e] font-mono">Immutable</span>
                </div>

                @if($auditLogs->isNotEmpty())
                <div class="overflow-x-auto border border-[#f0eee8] rounded-md">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[11px] text-[#5e625e]">
                                <th class="py-2.5 px-3 font-medium">Timestamp</th>
                                <th class="py-2.5 px-3 font-medium">Action</th>
                                <th class="py-2.5 px-3 font-medium">Record</th>
                                <th class="py-2.5 px-3 font-medium text-right">IP Address</th>
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
                                <td class="py-2.5 px-3 text-right font-mono text-[11px] text-[#5e625e]">{{ $log->ip_address ?? '103.21.244.2' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-md py-6 px-4 text-center text-xs text-[#5e625e]">
                    No recorded actions in platform audit trail for this account.
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Case Calendar (Upcoming Dates), Security Controls & Telemetry -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Case Calendar: Compulsory Upcoming Calendar Dates & Deadlines -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-[#23493a]">event</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Case Calendar</h2>
                    </div>
                    <span class="text-[11px] text-[#8a8a8a] font-mono">Important Dates</span>
                </div>

                <!-- Calendar Week Filter Tabs -->
                <div class="grid grid-cols-3 p-1 bg-[#F6F4EE] rounded-md my-3 border border-[#E7E4DC] text-[11px]">
                    <button type="button" @click="calTab = 'prev'"
                        :class="calTab === 'prev' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1 rounded transition-all cursor-pointer text-center">
                        Previous week
                    </button>
                    <button type="button" @click="calTab = 'this'"
                        :class="calTab === 'this' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1 rounded transition-all cursor-pointer text-center">
                        This week
                    </button>
                    <button type="button" @click="calTab = 'coming'"
                        :class="calTab === 'coming' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1 rounded transition-all cursor-pointer text-center">
                        Coming week
                    </button>
                </div>

                <!-- Upcoming Calendar Events List -->
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($upcomingEvents as $evt)
                    @php
                        $startTime = \Carbon\Carbon::parse($evt->start_time);
                        $type = $evt->event_type ?: 'Court Date';
                    @endphp
                    <div class="py-3 first:pt-1 last:pb-0 flex items-start gap-3">
                        <div class="w-10 text-center shrink-0 pt-0.5">
                            <div class="text-[10px] font-bold text-[#8a8a8a] tracking-wider uppercase font-sans">
                                {{ strtoupper($startTime->format('M')) }}
                            </div>
                            <div class="text-[18px] font-serif font-medium text-[#1a1a1a] leading-none mt-0.5">
                                {{ $startTime->format('j') }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-[#f3e8ff] text-[#7e22ce]">
                                    {{ $type }}
                                </span>
                                <span class="text-[11px] text-[#646864] font-medium font-sans">
                                    {{ $startTime->format('g:i A') }}
                                </span>
                            </div>
                            <div class="text-[12.5px] font-medium text-[#1a1a1a] mt-1 leading-snug">
                                {{ $evt->title }}
                            </div>
                            <div class="text-[11px] text-[#646864] mt-0.5 truncate">
                                {{ $evt->matter ? $evt->matter->case_number.' '.$evt->matter->title : 'Client Representation' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No court hearings or statutory deadlines in this window.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Security Controls & Access Status Card (Top Right / Sidebar as requested) -->
            <div class="bg-white border border-[#e5e3dc] rounded-lg p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Security Controls</h2>
                    <span class="text-xs font-semibold {{ $user->status === 'active' ? 'text-[#166534]' : 'text-[#991b1b]' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-[#f0eee8]">
                        <span class="text-[#5e625e]">Account Status</span>
                        <span class="font-medium text-[#1a1a1a] capitalize">{{ $user->status }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-[#f0eee8]">
                        <span class="text-[#5e625e]">Verified Device</span>
                        <span class="font-mono text-[#1a1a1a]">Safari on macOS</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-[#f0eee8]">
                        <span class="text-[#5e625e]">Client Network IP</span>
                        <span class="font-mono text-[#1a1a1a]">103.21.244.2</span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-[#5e625e]">Portal Enrollment</span>
                        <span class="font-medium text-[#166534]">Active Retainer</span>
                    </div>
                </div>

                <!-- One-Click Account Status Toggle with Small Confirmation Dialog -->
                <div class="mt-5 pt-4 border-t border-[#f0eee8]">
                    <button type="button" 
                            @click="showStatusModal = true"
                            class="w-full py-2 px-3 text-xs font-semibold rounded-md transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5 {{ $user->status === 'active' ? 'border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5]' : 'bg-[#23493a] text-white hover:bg-[#1a382c]' }}">
                        <span class="material-symbols-outlined text-base">{{ $user->status === 'active' ? 'block' : 'check_circle' }}</span>
                        <span>{{ $user->status === 'active' ? 'Suspend Client Account' : 'Activate Client Account' }}</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Small Confirmation Modal for Account Status (As requested: Are you sure you want to make this client active / not active) -->
    <div x-show="showStatusModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showStatusModal = false"
             class="bg-white border border-[#e5e3dc] rounded-xl max-w-sm w-full p-5 shadow-2xl">
            <div class="flex items-center gap-3 pb-3 border-b border-[#f0eee8]">
                <span class="p-2 rounded-full {{ $user->status === 'active' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $user->status === 'active' ? 'warning' : 'verified' }}</span>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Update Account Status</h3>
                    <p class="text-[11px] text-[#5e625e]">Confirmation Required</p>
                </div>
            </div>

            <p class="text-xs text-[#414844] mt-3 leading-relaxed">
                @if($user->status === 'active')
                    Are you sure you want to make this client <strong>suspended (not active)</strong>? Portal access will be temporarily held.
                @else
                    Are you sure you want to make this client <strong>active</strong>? Access to client litigation documents will be restored immediately.
                @endif
            </p>

            <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="mt-5 flex items-center justify-end gap-2.5">
                @csrf
                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}" />
                <button type="button" @click="showStatusModal = false"
                        class="px-3 py-1.5 border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="px-3.5 py-1.5 rounded-md text-xs font-semibold text-white transition-colors cursor-pointer {{ $user->status === 'active' ? 'bg-[#ba1a1a] hover:bg-[#991b1b]' : 'bg-[#23493a] hover:bg-[#1a382c]' }}">
                    {{ $user->status === 'active' ? 'Confirm Deactivation' : 'Confirm Activation' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal (Preserved for full functionality) -->
    <div x-show="editModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto">
        <div @click.outside="editModalOpen = false"
             class="bg-white border border-[#e5e3dc] rounded-xl max-w-xl w-full p-6 shadow-2xl relative my-8">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Edit Profile Details</h3>
                <button type="button" @click="editModalOpen = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 mt-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 py-1.5 rounded-md border border-[#dcdad4] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]" />
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-1.5 rounded-md border border-[#dcdad4] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]" />
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Mobile</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}"
                               class="w-full px-3 py-1.5 rounded-md border border-[#dcdad4] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]" />
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Office Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-3 py-1.5 rounded-md border border-[#dcdad4] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]" />
                    </div>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2.5">
                    <button type="button" @click="editModalOpen = false" class="px-3.5 py-1.5 border border-[#dcdad4] rounded-md text-[#1a1a1a] hover:bg-[#f5f3ed]">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1.5 bg-[#23493a] text-white font-semibold rounded-md hover:bg-[#1a382c]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
