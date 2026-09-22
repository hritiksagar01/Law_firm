@extends('layouts.admin')

@section('title', 'Audit log — Platform Console')
@section('header_title', 'Audit Logs')

@section('content')
<div class="space-y-6">

    <!-- Page Header with Export CSV -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Audit log</h1>
            <p class="text-[13px] text-[#646864] mt-1">
                Append-only record of sign-ins, changes, document access and payments · UTC
            </p>
        </div>

        <div>
            <a href="{{ route('admin.audit.export', request()->query()) }}" 
               class="h-8 px-3.5 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] flex items-center gap-1.5 transition-colors shadow-xs">
                <svg class="w-3.5 h-3.5 text-[#5e625e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Tenant Privacy Notice Banner -->
    <div class="bg-[#f5f3ed] border border-[#e5e3dc] rounded-md p-4 text-[13px] text-[#414844] leading-relaxed">
        Platform staff see record types, counts, statuses, and matter numbers. Client identities, matter titles, document and message content, and individual amounts stay with each firm. Exports and administrative actions are logged to this audit trail.
    </div>

    <!-- Filter Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm p-4 shadow-none">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 items-end">
            <!-- Firm Filter -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Firm</label>
                <select name="firm_id" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                    <option value="all">All Firms</option>
                    <option value="platform" {{ request('firm_id') === 'platform' ? 'selected' : '' }}>Platform</option>
                    @foreach($firms as $f)
                        <option value="{{ $f->id }}" {{ request('firm_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Module Filter -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Module</label>
                <select name="module" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                    <option value="all">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Action Type</label>
                <select name="action" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                    <option value="all">Any Action</option>
                    <optgroup label="Authentication & Access">
                        <option value="auth.login" {{ request('action') === 'auth.login' ? 'selected' : '' }}>Sign in (auth.login)</option>
                        <option value="auth.force_logout" {{ request('action') === 'auth.force_logout' ? 'selected' : '' }}>Force logout (auth.force_logout)</option>
                        <option value="auth.password_reset_sent" {{ request('action') === 'auth.password_reset_sent' ? 'selected' : '' }}>Password reset (auth.password_reset_sent)</option>
                    </optgroup>
                    <optgroup label="User & Permission Management">
                        <option value="user.invited" {{ request('action') === 'user.invited' ? 'selected' : '' }}>User invited (user.invited)</option>
                        <option value="user.updated" {{ request('action') === 'user.updated' ? 'selected' : '' }}>User updated (user.updated)</option>
                        <option value="user.status_changed" {{ request('action') === 'user.status_changed' ? 'selected' : '' }}>Status changed (user.status_changed)</option>
                        <option value="user.role_changed" {{ request('action') === 'user.role_changed' ? 'selected' : '' }}>Role changed (user.role_changed)</option>
                        <option value="user.invitation_resent" {{ request('action') === 'user.invitation_resent' ? 'selected' : '' }}>Invite resent (user.invitation_resent)</option>
                        <option value="role.permissions_updated" {{ request('action') === 'role.permissions_updated' ? 'selected' : '' }}>Permissions updated (role.permissions_updated)</option>
                    </optgroup>
                    <optgroup label="Matters & Cases">
                        <option value="matter.created" {{ request('action') === 'matter.created' ? 'selected' : '' }}>Matter opened (matter.created)</option>
                        <option value="matter.update_posted" {{ request('action') === 'matter.update_posted' ? 'selected' : '' }}>Case update posted (matter.update_posted)</option>
                        <option value="matter.assigned" {{ request('action') === 'matter.assigned' ? 'selected' : '' }}>Attorney assigned (matter.assigned)</option>
                    </optgroup>
                    <optgroup label="Documents">
                        <option value="document.uploaded" {{ request('action') === 'document.uploaded' ? 'selected' : '' }}>Document uploaded (document.uploaded)</option>
                        <option value="document.downloaded" {{ request('action') === 'document.downloaded' ? 'selected' : '' }}>Document downloaded (document.downloaded)</option>
                        <option value="document.viewed" {{ request('action') === 'document.viewed' ? 'selected' : '' }}>Document viewed (document.viewed)</option>
                        <option value="document.shared" {{ request('action') === 'document.shared' ? 'selected' : '' }}>Document shared (document.shared)</option>
                        <option value="request.accepted" {{ request('action') === 'request.accepted' ? 'selected' : '' }}>Submission accepted (request.accepted)</option>
                    </optgroup>
                    <optgroup label="Communication & Work">
                        <option value="message.sent" {{ request('action') === 'message.sent' ? 'selected' : '' }}>Message sent (message.sent)</option>
                        <option value="thread.created" {{ request('action') === 'thread.created' ? 'selected' : '' }}>Thread created (thread.created)</option>
                        <option value="task.assigned" {{ request('action') === 'task.assigned' ? 'selected' : '' }}>Task assigned (task.assigned)</option>
                        <option value="task.completed" {{ request('action') === 'task.completed' ? 'selected' : '' }}>Task completed (task.completed)</option>
                    </optgroup>
                    <optgroup label="Billing & Payments">
                        <option value="payment.recorded" {{ request('action') === 'payment.recorded' ? 'selected' : '' }}>Payment recorded (payment.recorded)</option>
                        <option value="invoice.sent" {{ request('action') === 'invoice.sent' ? 'selected' : '' }}>Invoice sent (invoice.sent)</option>
                        <option value="payment.succeeded" {{ request('action') === 'payment.succeeded' ? 'selected' : '' }}>Payment received (payment.succeeded)</option>
                    </optgroup>
                </select>
            </div>

            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Actor, Email or Description</label>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="e.g. Rowan Blake or user profile..."
                       class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
            </div>

            <!-- From Date -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">From</label>
                <input type="date" 
                       name="from" 
                       value="{{ request('from') }}"
                       class="w-full h-8 px-2 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
            </div>

            <!-- To Date + Apply -->
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">To</label>
                    <input type="date" 
                           name="to" 
                           value="{{ request('to') }}"
                           class="w-full h-8 px-2 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>
                <button type="submit" 
                        class="h-8 px-3 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors self-end shadow-xs cursor-pointer">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Log Table Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-2.5 px-4 font-normal w-16">Audit ID</th>
                        <th class="py-2.5 px-4 font-normal whitespace-nowrap">Time (UTC)</th>
                        <th class="py-2.5 px-4 font-normal">Action &amp; Module</th>
                        <th class="py-2.5 px-4 font-normal">Actor</th>
                        <th class="py-2.5 px-4 font-normal">Firm</th>
                        <th class="py-2.5 px-4 font-normal">Record / Entity</th>
                        <th class="py-2.5 px-4 font-normal text-right">IP &amp; Client</th>
                        <th class="py-2.5 px-4 font-normal text-center w-16">Diff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($logs as $log)
                    @php
                        $hasDiff = !empty($log->description) || !empty($log->previous_value) || !empty($log->new_value) || !empty($log->user_agent);
                    @endphp
                    <tr class="hover:bg-[#f4f2ed] transition-colors" x-data="{ expanded: false }">
                        <td class="py-3 px-4 font-mono text-[11px] text-[#5e625e]">
                            #{{ $log->id }}
                        </td>

                        <td class="py-3 px-4 text-[#5e625e] font-sans whitespace-nowrap">
                            {{ $log->created_at->format('M j, Y, g:i A') }}
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-medium text-[#1b1c18] block">
                                {{ $log->action_label }}
                            </span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-[10.5px] text-[#717974]">{{ $log->action }}</span>
                                @if($log->module)
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-[#f5f3ed] text-[#23493a] font-medium">
                                    {{ $log->module }}
                                </span>
                                @endif
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-medium text-[#1b1c18] block">
                                {{ $log->actor_name }}
                            </span>
                            @if($log->actor_email)
                                <span class="font-mono text-[11px] text-[#717974] block mt-0.5">
                                    {{ $log->actor_email }}
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            @if($log->firm)
                                <a href="{{ route('admin.firms.show', $log->firm) }}" class="text-[#1b1c18] hover:underline font-normal">
                                    {{ $log->firm->name }}
                                </a>
                            @else
                                <span class="text-[#5e625e]">Platform</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-[#414844]">
                            <div>{{ $log->record_type }}</div>
                            @if($log->entity_id)
                            <span class="font-mono text-[10px] text-[#8e8e8e]">Entity ID: #{{ $log->entity_id }}</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-right">
                            <div class="font-mono text-[11px] text-[#5e625e]">{{ $log->ip_address ?? '—' }}</div>
                            @if($log->user_agent)
                            <div class="text-[10px] text-[#8e8e8e] truncate max-w-[140px] ml-auto" title="{{ $log->user_agent }}">
                                {{ Str::limit($log->user_agent, 20) }}
                            </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center">
                            @if($hasDiff)
                            <button type="button" 
                                    @click="expanded = !expanded" 
                                    class="p-1 text-[#5e625e] hover:text-[#1b1c18] hover:bg-[#f5f3ed] rounded transition-colors cursor-pointer"
                                    :title="expanded ? 'Hide details' : 'View audit diff and details'">
                                <span class="material-symbols-outlined text-[18px]" x-text="expanded ? 'expand_less' : 'tune'"></span>
                            </button>
                            @else
                            <span class="text-[#c1c8c3]">&mdash;</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Expandable Row for Details & Diff -->
                    @if($hasDiff)
                    <tr x-show="expanded" x-cloak class="bg-[#faf8f5] border-t border-b border-[#e5e3dc]">
                        <td colspan="8" class="p-4">
                            <div class="space-y-3 text-xs">
                                @if($log->description)
                                <div>
                                    <span class="font-semibold text-[#1b1c18] block text-[11.5px] mb-0.5">Description:</span>
                                    <p class="text-[#414844] font-sans">{{ $log->description }}</p>
                                </div>
                                @endif

                                @if($log->user_agent)
                                <div>
                                    <span class="font-semibold text-[#1b1c18] block text-[11.5px] mb-0.5">Client User Agent:</span>
                                    <p class="text-[#5e625e] font-mono text-[11px]">{{ $log->user_agent }}</p>
                                </div>
                                @endif

                                @if($log->previous_value || $log->new_value)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                                    <div class="bg-white p-3 rounded border border-[#e5e3dc]">
                                        <span class="font-semibold text-[#ba1a1a] block text-[11.5px] mb-1.5 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">remove_circle</span> Previous Value
                                        </span>
                                        <pre class="bg-[#fafafa] p-2.5 rounded font-mono text-[11px] text-[#414844] overflow-x-auto whitespace-pre-wrap">{{ is_array($log->previous_value) ? json_encode($log->previous_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($log->previous_value ?? 'None recorded') }}</pre>
                                    </div>
                                    <div class="bg-white p-3 rounded border border-[#e5e3dc]">
                                        <span class="font-semibold text-[#166534] block text-[11.5px] mb-1.5 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">add_circle</span> New Value
                                        </span>
                                        <pre class="bg-[#fafafa] p-2.5 rounded font-mono text-[11px] text-[#414844] overflow-x-auto whitespace-pre-wrap">{{ is_array($log->new_value) ? json_encode($log->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($log->new_value ?? 'None recorded') }}</pre>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-xs text-[#5e625e]">
                            No audit log entries matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white flex items-center justify-between text-xs text-[#5e625e]">
            <span>Page {{ $logs->currentPage() }} &middot; {{ $logs->total() }} rows</span>
            <div class="flex gap-1">
                @if($logs->onFirstPage())
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Previous</a>
                @endif

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Next</a>
                @else
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
