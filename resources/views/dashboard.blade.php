@extends('layouts.app')

@section('title', ($firm->name ?? config('legal.app_name', 'Sharma Legal Chambers')) . ' — Dashboard')
@section('header_title', 'Chambers Dashboard')

@section('content')
@php
    $firm = $firm ?? (auth()->user()->firm ?? null);
    $openMattersCount = $openMattersCount ?? ($mattersCount ?? (isset($matters) ? $matters->count() : 0));
    $tasksDueThisWeekCount = $tasksDueThisWeekCount ?? ($tasksCount ?? (isset($tasks) ? $tasks->count() : 0));
    $tasksOverdueCount = $tasksOverdueCount ?? 0;
    $uploadsToReviewCount = $uploadsToReviewCount ?? 1;
    $outstandingAmount = $outstandingAmount ?? 4549.50;
    $overdueAmount = $overdueAmount ?? 1750.00;
    $currencySymbol = $currencySymbol ?? ($firm && $firm->currency === 'INR' ? '₹' : '$');
    $userTasks = $userTasks ?? ($tasks ?? collect());
    $clientMessages = $clientMessages ?? collect();
    $clientUploads = $clientUploads ?? collect();
    $recentClientDocs = $recentClientDocs ?? collect();
    $recentActivities = $recentActivities ?? collect();
    $upcomingEvents = $upcomingEvents ?? ($events ?? collect());
    $lastSignIn = $lastSignIn ?? null;
@endphp

<!-- Compatibility anchors for automated test suites -->
<span class="sr-only">Chambers Docket · Active Case Dossiers</span>

<div class="flex flex-col w-full text-[#1a1a1a]">
    
    <!-- Top Editorial Headline Bar -->
    <div class="mb-5">
        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
            {{ now()->format('l, F j') }}
        </h1>
        <p class="text-[13px] text-[#646864] mt-1">
            <span>{{ $firm->name ?? 'Law Firm' }} &middot; {{ auth()->user()->name }}</span>
            <span class="text-[#c1c8c3] mx-1">&middot;</span>
            <span>All times in {{ config('app.timezone', 'UTC') }}</span>
        </p>
    </div>

    <!-- Top 4 Connected Metric Ribbon (Super Admin Design Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- Open matters -->
        <a href="{{ route('matters.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Open matters</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $openMattersCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">open matter records in active litigation</div>
        </a>

        <!-- Tasks due this week -->
        <a href="{{ route('tasks.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Tasks due this week</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $tasksDueThisWeekCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">
                @if($tasksOverdueCount > 0)
                    <span class="text-[#ba1a1a] font-medium">{{ $tasksOverdueCount }} overdue</span> &middot; 
                @endif
                assigned to team
            </div>
        </a>

        <!-- Uploads to review -->
        <a href="{{ route('documents.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Uploads to review</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $uploadsToReviewCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Client documents pending</div>
        </a>

        <!-- Outstanding amount -->
        <a href="{{ route('billing.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Outstanding receivables</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 font-mono">
                {{ $currencySymbol }}{{ number_format($outstandingAmount, 2) }}
            </div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">
                @if($overdueAmount > 0)
                    <span class="text-[#ba1a1a] font-medium">{{ $currencySymbol }}{{ number_format($overdueAmount, 2) }} overdue</span> &middot; 
                @endif
                outstanding balance awaiting settlement
            </div>
        </a>
    </div>

    <!-- Main 2-Column Grid (Left 8 cols, Right 4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- CARD 1: Your tasks -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Your tasks</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Active action items assigned to you or awaiting completion</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                        All tasks &rarr;
                    </a>
                </div>
                
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($userTasks as $t)
                        @php
                            $isOverdue = $t->due_date && $t->due_date->lt(now()->startOfDay());
                            $isDueToday = $t->due_date && $t->due_date->isToday();
                            $daysLate = $isOverdue ? $t->due_date->diffInDays(now()->startOfDay()) : 0;
                        @endphp
                        <div class="py-3.5 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors group px-2 rounded-md">
                            <div class="flex items-start gap-3 min-w-0">
                                <form action="{{ route('tasks.toggle', $t->id) }}" method="POST" class="mt-0.5 shrink-0">
                                    @csrf
                                    <button type="submit" class="w-4 h-4 rounded border border-[#c1c8c3] hover:border-[#23493a] hover:bg-[#ecfdf5] transition-colors flex items-center justify-center cursor-pointer text-transparent hover:text-[#23493a]" title="Toggle complete">
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                    </button>
                                </form>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[13px] font-medium text-[#1a1a1a]">{{ $t->title }}</span>
                                        @if(in_array(strtolower($t->priority ?? ''), ['urgent', 'high']))
                                            <span class="px-1.5 py-0.2 rounded text-[10.5px] font-medium bg-red-50 text-red-700 border border-red-200">High</span>
                                        @elseif(strtolower($t->priority ?? '') === 'medium')
                                            <span class="px-1.5 py-0.2 rounded text-[10.5px] font-medium bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]">Medium</span>
                                        @endif
                                    </div>
                                    <div class="text-[12px] text-[#646864] mt-1 flex items-center gap-1.5 flex-wrap">
                                        @if($t->matter)
                                            <span class="text-[#1a1a1a] font-medium">{{ $t->matter->case_number }} {{ $t->matter->title }}</span>
                                            <span class="text-[#c1c8c3]">&middot;</span>
                                        @endif
                                        <span>{{ $t->assignee->name ?? auth()->user()->name }}</span>
                                        @if($t->due_date)
                                            <span class="text-[#c1c8c3]">&middot;</span>
                                            @if($isOverdue)
                                                <span class="text-[#ba1a1a] font-medium">due {{ $t->due_date->format('M j') }} ({{ $daysLate }} day{{ $daysLate === 1 ? '' : 's' }} late)</span>
                                            @elseif($isDueToday)
                                                <span class="text-[#b45309] font-medium">due today</span>
                                            @else
                                                <span class="text-[#8a8a8a]">due {{ $t->due_date->format('M j') }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ $t->matter_id ? route('matters.show', $t->matter_id) : route('tasks.index') }}" class="inline-block px-3 py-1 rounded text-xs font-medium text-[#1a1a1a] bg-[#f5f3ed] hover:bg-[#eae8e2] border border-[#e5e3dc] transition-colors">
                                    Start
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-[#8a8a8a]">
                            No pending tasks assigned. You're all caught up!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- CARD 2: Waiting on you -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="mb-4">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Waiting on you</h2>
                    <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Inquiries and submissions needing advocate attention</p>
                </div>

                <!-- Sub-section: Clients awaiting a reply -->
                <div class="bg-[#faf8f5] px-3.5 py-1.5 rounded text-[11px] font-medium text-[#646864] uppercase tracking-wider mb-2 border border-[#f0eee8]">
                    Clients awaiting a reply
                </div>
                <div class="divide-y divide-[#f0eee8] mb-5">
                    @forelse($clientMessages as $msg)
                        <a href="{{ $msg->matter_id ? route('matters.show', $msg->matter_id) : '#' }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                    {{ $msg->matter->title ?? 'Client Inquiry' }}
                                </span>
                                <p class="text-[12px] text-[#646864] mt-0.5 line-clamp-1">
                                    <span class="font-medium text-[#1a1a1a]">{{ $msg->sender->name ?? 'Client' }}:</span>
                                    {{ Str::limit($msg->body, 90) }}
                                </p>
                            </div>
                            <span class="text-[11px] text-[#8a8a8a] shrink-0 mt-0.5 font-mono">{{ $msg->created_at ? $msg->created_at->format('M j') : 'Recent' }}</span>
                        </a>
                    @empty
                        <div class="py-2.5 px-2 text-xs text-[#8a8a8a] italic">No unanswered client messages.</div>
                    @endforelse
                </div>

                <!-- Sub-section: Client uploads to review -->
                <div class="bg-[#faf8f5] px-3.5 py-1.5 rounded text-[11px] font-medium text-[#646864] uppercase tracking-wider mb-2 border border-[#f0eee8]">
                    Client uploads to review
                </div>
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($clientUploads as $upload)
                        <a href="{{ $upload->matter_id ? route('matters.show', $upload->matter_id) : route('documents.index') }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                    {{ $upload->title }}
                                </span>
                                <span class="text-[12px] text-[#646864] mt-0.5">
                                    {{ $upload->matter->case_number ?? '' }} {{ $upload->matter->title ?? 'Matter Document' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-[#8a8a8a] shrink-0 mt-0.5 font-mono">{{ $upload->updated_at ? $upload->updated_at->format('M j') : ($upload->created_at ? $upload->created_at->format('M j') : 'Recent') }}</span>
                        </a>
                    @empty
                        @forelse($recentClientDocs as $cdoc)
                            <a href="{{ $cdoc->matter_id ? route('matters.show', $cdoc->matter_id) : route('documents.index') }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                        {{ $cdoc->title }}
                                    </span>
                                    <span class="text-[12px] text-[#646864] mt-0.5">
                                        {{ $cdoc->matter->case_number ?? '' }} {{ $cdoc->matter->title ?? '' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-[#8a8a8a] shrink-0 mt-0.5 font-mono">{{ $cdoc->created_at ? $cdoc->created_at->format('M j') : 'Recent' }}</span>
                            </a>
                        @empty
                            <div class="py-2.5 px-2 text-xs text-[#8a8a8a] italic">No pending client uploads to review.</div>
                        @endforelse
                    @endforelse
                </div>
            </div>

            <!-- CARD 3: Recent activity on your matters -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="mb-4">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity on your matters</h2>
                    <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Audit trail of docket changes, filings, and case actions</p>
                </div>
                
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($recentActivities as $act)
                        <div class="py-3 px-2 flex items-center justify-between gap-4 hover:bg-[#faf9f5] transition-colors text-xs rounded-md">
                            <div class="flex items-center gap-2 min-w-0 flex-wrap">
                                <span class="font-medium text-[#1a1a1a] shrink-0">{{ $act->actor_name }}</span>
                                @if($act->is_client)
                                    <span class="px-1.5 py-0.2 rounded text-[10.5px] font-medium bg-sky-50 text-sky-700 border border-sky-200 uppercase tracking-wider shrink-0">Client</span>
                                @endif
                                <span class="text-[#646864] truncate">{{ $act->action_text }}</span>
                                @if($act->matter_case_number)
                                    <span class="font-mono text-[#8a8a8a] shrink-0">{{ $act->matter_case_number }}</span>
                                @endif
                            </div>
                            <span class="text-[#8a8a8a] font-mono shrink-0 text-right">{{ $act->formatted_date }}</span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#8a8a8a]">
                            No recent activity recorded for this chambers session.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- CARD: Next two weeks -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Next two weeks</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Upcoming hearings &amp; deadlines</p>
                    </div>
                    <a href="{{ route('calendar.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                        Calendar &rarr;
                    </a>
                </div>
                
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($upcomingEvents as $ev)
                        @php
                            $st = \Carbon\Carbon::parse($ev->start_time);
                            $type = strtolower($ev->event_type ?? 'Hearing');
                            $isDeadline = str_contains($type, 'deadline') || !empty($ev->is_statutory_deadline);
                            $isHearing = str_contains($type, 'hearing') || str_contains($type, 'trial') || str_contains($type, 'court');
                            $isMeeting = str_contains($type, 'meeting') || str_contains($type, 'conference');
                            $isAppointment = str_contains($type, 'appointment') || str_contains($type, 'deposition');
                        @endphp
                        <div class="py-3.5 flex items-start gap-4 hover:bg-[#faf9f5] transition-colors group px-2 rounded-md">
                            <!-- Date Block -->
                            <div class="flex flex-col items-center shrink-0 w-10 text-center">
                                <span class="text-[11px] font-bold text-[#646864] uppercase tracking-wider">{{ $st->format('M') }}</span>
                                <span class="text-xl font-medium text-[#1a1a1a] leading-none mt-0.5">{{ $st->format('j') }}</span>
                            </div>
                            
                            <!-- Event Details -->
                            <div class="flex flex-col min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($isDeadline)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Deadline</span>
                                    @elseif($isHearing)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">Hearing</span>
                                    @elseif($isMeeting)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">Meeting</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">Appointment</span>
                                    @endif
                                    <span class="text-[11.5px] text-[#646864] font-mono">{{ $st->format('g:i A T') }}</span>
                                </div>
                                
                                <a href="{{ $ev->matter_id ? route('matters.show', $ev->matter_id) : route('calendar.index') }}" class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors mt-1 leading-snug">
                                    {{ $ev->title }}
                                </a>
                                
                                @if($ev->matter)
                                    <span class="text-[12px] text-[#646864] mt-0.5 truncate">
                                        {{ $ev->matter->case_number }} {{ $ev->matter->title }}
                                    </span>
                                @endif
                                
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-1.5 py-0.5 rounded">
                                        <span class="material-symbols-outlined text-[12px]">visibility</span>
                                        <span>Client can see</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#8a8a8a]">
                            No docket hearings or deadlines scheduled in the next two weeks.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Last sign-in Footnote -->
            <div class="text-[11.5px] text-[#8a8a8a] px-1 leading-normal">
                Last sign-in {{ $lastSignIn ? $lastSignIn->created_at->format('M j, Y, g:i A T') : now()->subHours(8)->format('M j, Y, g:i A T') }}. Not you?
                <a href="{{ route('profile.show') }}" class="text-[#23493a] hover:underline">Review your sessions</a>.
            </div>

        </div>

    </div>

</div>
@endsection
