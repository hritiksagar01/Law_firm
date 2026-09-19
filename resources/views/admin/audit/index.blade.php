@extends('layouts.admin')

@section('title', 'Audit log — Quire Legal Multi-Tenant Cloud')

@section('content')
<div class="space-y-6">

    <!-- Page Header (Exact Quire styling with Export CSV) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif text-[#1b1c18] tracking-tight">Audit log</h1>
            <p class="text-xs text-[#5e625e] mt-1 font-sans">
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
        Platform staff see record types, counts, statuses and matter numbers. Client identities, matter titles, document and message content, and individual amounts stay with each firm. Exports are logged.
    </div>

    <!-- Filter Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm p-4 shadow-none">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Firm Filter -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Firm</label>
                <select name="firm_id" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                    <option value="all">All</option>
                    <option value="platform" {{ request('firm_id') === 'platform' ? 'selected' : '' }}>Platform</option>
                    @foreach($firms as $f)
                        <option value="{{ $f->id }}" {{ request('firm_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div>
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Action</label>
                <select name="action" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                    <option value="all">Any action</option>
                    <option value="auth.login" {{ request('action') === 'auth.login' ? 'selected' : '' }}>Signed in (auth.login)</option>
                    <option value="document.downloaded" {{ request('action') === 'document.downloaded' ? 'selected' : '' }}>Downloaded document</option>
                    <option value="document.viewed" {{ request('action') === 'document.viewed' ? 'selected' : '' }}>Previewed document</option>
                    <option value="document.uploaded" {{ request('action') === 'document.uploaded' ? 'selected' : '' }}>Uploaded document</option>
                    <option value="message.sent" {{ request('action') === 'message.sent' ? 'selected' : '' }}>Sent message</option>
                    <option value="thread.created" {{ request('action') === 'thread.created' ? 'selected' : '' }}>Started thread</option>
                    <option value="user.invited" {{ request('action') === 'user.invited' ? 'selected' : '' }}>Invited user</option>
                    <option value="matter.update_posted" {{ request('action') === 'matter.update_posted' ? 'selected' : '' }}>Posted case update</option>
                    <option value="payment.recorded" {{ request('action') === 'payment.recorded' ? 'selected' : '' }}>Recorded payment</option>
                    <option value="request.accepted" {{ request('action') === 'request.accepted' ? 'selected' : '' }}>Accepted submission</option>
                    <option value="invoice.sent" {{ request('action') === 'invoice.sent' ? 'selected' : '' }}>Sent invoice</option>
                    <option value="payment.succeeded" {{ request('action') === 'payment.succeeded' ? 'selected' : '' }}>Payment received</option>
                    <option value="matter.created" {{ request('action') === 'matter.created' ? 'selected' : '' }}>Opened matter</option>
                </select>
            </div>

            <!-- Staff / Email Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1">Staff name or email</label>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder=""
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
                        class="h-8 px-3 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors self-end shadow-xs">
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
                        <th class="py-2.5 px-4 font-normal">Time</th>
                        <th class="py-2.5 px-4 font-normal">Action</th>
                        <th class="py-2.5 px-4 font-normal">Actor</th>
                        <th class="py-2.5 px-4 font-normal">Firm</th>
                        <th class="py-2.5 px-4 font-normal">Record</th>
                        <th class="py-2.5 px-4 font-normal text-right">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-[#fbf9f5] transition-colors">
                        <td class="py-3 px-4 text-[#5e625e] font-sans whitespace-nowrap">
                            {{ $log->created_at->format('M j, Y, g:i A') }} UTC
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-medium text-[#1b1c18] block">
                                {{ $log->action_label }}
                            </span>
                            <span class="font-mono text-[11px] text-[#717974] block mt-0.5">
                                {{ $log->action }}
                            </span>
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
                            {{ $log->record_type }}
                        </td>

                        <td class="py-3 px-4 text-right font-mono text-[11px] text-[#5e625e]">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-[#5e625e]">
                            No audit log entries matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white flex items-center justify-between text-xs text-[#5e625e]">
            <span>Page {{ $logs->currentPage() }} · {{ $logs->total() }} rows</span>
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
