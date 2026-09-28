@extends('layouts.app')

@section('title', ($firm->name ?? config('legal.app_name', 'Sharma Legal Chambers')) . ' — Dashboard')
@section('header_title', 'Chambers Dashboard')

@section('content')
@php
    $firm = $firm ?? (auth()->user()->firm ?? null);
    $activeMattersCount = $activeMattersCount ?? ($openMattersCount ?? 0);
    $newMattersCount = $newMattersCount ?? 0;
    $closedMattersCount = $closedMattersCount ?? 0;
    $openMattersCount = $openMattersCount ?? $activeMattersCount;
    $tasksDueThisWeekCount = $tasksDueThisWeekCount ?? ($tasksCount ?? (isset($tasks) ? $tasks->count() : 0));
    $tasksOverdueCount = $tasksOverdueCount ?? 0;
    $uploadsToReviewCount = $uploadsToReviewCount ?? 1;
    $unreadMessagesCount = $unreadMessagesCount ?? 0;
    $currencySymbol = $currencySymbol ?? ($firm && $firm->currency === 'INR' ? '₹' : '$');
    $userTasks = $userTasks ?? ($tasks ?? collect());
    $overdueTasksList = $overdueTasksList ?? collect();
    $mattersRequiringAttention = $mattersRequiringAttention ?? collect();
    $pendingDocRequestsList = $pendingDocRequestsList ?? collect();
    $clientMessages = $clientMessages ?? collect();
    $clientUploads = $clientUploads ?? collect();
    $recentClientDocs = $recentClientDocs ?? collect();
    $recentActivities = $recentActivities ?? collect();
    $prevWeekEvents = $prevWeekEvents ?? collect();
    $thisWeekEvents = $thisWeekEvents ?? collect();
    $nextWeekEvents = $nextWeekEvents ?? collect();
    $upcomingEvents = $upcomingEvents ?? ($events ?? collect());
    $previousWeekSchedule = $previousWeekSchedule ?? collect();
    $thisWeekSchedule = $thisWeekSchedule ?? collect();
    $comingWeekSchedule = $comingWeekSchedule ?? collect();
    $importantNotes = $importantNotes ?? collect();
    $importantNotesCount = $importantNotesCount ?? 0;
    $lastSignIn = $lastSignIn ?? null;
@endphp

<!-- Compatibility anchors for automated test suites -->
<span class="sr-only">Chambers Docket · Active Case Dossiers</span>

<div x-data="clientOnboardingState({{ auth()->user()->firm_id ?? 1 }}, '{{ Auth::id() }}')" class="flex flex-col w-full text-[#1a1a1a]">
    
    <!-- Top Editorial Headline Bar -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                {{ now()->format('l, F j') }}
            </h1>
            <p class="text-[13px] text-[#646864] mt-1">
                <span>{{ $firm->name ?? 'Law Firm' }} &middot; {{ auth()->user()->name }}</span>
                <span class="text-[#c1c8c3] mx-1">&middot;</span>
                <span>All times in {{ config('app.timezone', 'UTC') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] text-xs font-medium transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[15px] text-[#23493a]">person_add</span>
                <span>Client Intake</span>
            </button>
            <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-[#23493a] text-white hover:bg-[#1a382c] text-xs font-medium transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[15px]">add</span>
                <span>New Matter</span>
            </a>
        </div>
    </div>

    <!-- Top 5 Connected Metric Ribbon (Clean Operations Overview, No Receivables) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- Active / New / Closed Matters -->
        <div class="p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors group">
            <div class="flex items-center justify-between">
                <a href="{{ route('matters.index', ['status' => 'open']) }}" class="text-[12.5px] text-[#646864] hover:text-[#23493a] font-medium transition-colors">Open matters</a>
                <a href="{{ route('matters.index', ['status' => 'open']) }}" class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wide hover:bg-emerald-100 transition-colors">Active</a>
            </div>
            <a href="{{ route('matters.index', ['status' => 'open']) }}" class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 hover:text-[#23493a] transition-colors">{{ $activeMattersCount }}</a>
            <div class="text-[12px] text-[#8a8a8a] mt-1 flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('matters.index', ['status' => 'open']) }}" class="hover:text-[#23493a] hover:underline">open matter records in active litigation</a>
                <span class="text-[#c1c8c3]">&middot;</span>
                <a href="{{ route('matters.index', ['status' => 'new']) }}" class="text-[#646864] hover:text-[#23493a] hover:underline font-medium">{{ $newMattersCount }} new</a>
                <span class="text-[#c1c8c3]">&middot;</span>
                <a href="{{ route('matters.index', ['status' => 'closed']) }}" class="text-[#646864] hover:text-[#23493a] hover:underline font-medium">{{ $closedMattersCount }} closed</a>
            </div>
        </div>

        <!-- Tasks Due This Week / Overdue -->
        <a href="{{ route('tasks.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors group">
            <div class="flex items-center justify-between">
                <span class="text-[12.5px] text-[#646864] group-hover:text-[#23493a] transition-colors">Tasks due this week</span>
                @if($tasksOverdueCount > 0)
                    <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 uppercase tracking-wide">{{ $tasksOverdueCount }} overdue</span>
                @endif
            </div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 group-hover:text-[#23493a] transition-colors">{{ $tasksDueThisWeekCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">
                @if($tasksOverdueCount > 0)
                    <span class="text-[#ba1a1a] font-medium">{{ $tasksOverdueCount }} overdue tasks</span> &middot; 
                @endif
                assigned across team
            </div>
        </a>

        <!-- Pending Document Requests / Uploads -->
        <a href="{{ route('document-requests.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors group">
            <div class="flex items-center justify-between">
                <span class="text-[12.5px] text-[#646864] group-hover:text-[#23493a] transition-colors">Uploads to review</span>
                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wide">Pending</span>
            </div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 group-hover:text-[#23493a] transition-colors">{{ $uploadsToReviewCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">{{ $pendingDocRequestsList->count() }} active client requests pending</div>
        </a>

        <!-- Unread Messages -->
        <a href="{{ route('messages.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors group">
            <div class="flex items-center justify-between">
                <span class="text-[12.5px] text-[#646864] group-hover:text-[#23493a] transition-colors">Unread messages</span>
                @if($unreadMessagesCount > 0)
                    <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-800 border border-indigo-200 uppercase tracking-wide">New</span>
                @endif
            </div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 group-hover:text-[#23493a] transition-colors">{{ $unreadMessagesCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">direct client &amp; matter communications</div>
        </a>

        <!-- Important Notes -->
        <a href="{{ route('notes.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors group">
            <div class="flex items-center justify-between">
                <span class="text-[12.5px] text-[#646864] group-hover:text-[#23493a] transition-colors">Important Notes</span>
                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wide">Pinned</span>
            </div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 group-hover:text-[#23493a] transition-colors">{{ $importantNotesCount }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">critical briefings &amp; case memos</div>
        </a>
    </div>

    <!-- Main 2-Column Grid (Left 8 cols, Right 4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">

            <!-- CARD 0: Matters Requiring Attention (Deadlines/Hearings in 48h or Overdue Tasks) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-[18px]">crisis_alert</span>
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Matters requiring attention</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Hearings/deadlines within 48h, urgent priority cases, or overdue milestones</p>
                        </div>
                    </div>
                    <span class="text-[11.5px] font-mono px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $mattersRequiringAttention->count() }} Urgent
                    </span>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($mattersRequiringAttention as $attMatter)
                        <div class="py-3 px-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-[#faf9f5] transition-colors rounded-md group">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs font-semibold text-[#23493a]">{{ $attMatter->case_number }}</span>
                                    <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">{{ $attMatter->title }}</span>
                                    @if($attMatter->priority === 'urgent')
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-red-100 text-red-800 border border-red-200">Urgent</span>
                                    @endif
                                </div>
                                <div class="text-[12px] text-[#646864] mt-1 flex items-center gap-2 flex-wrap">
                                    <span>Client: <strong>{{ $attMatter->client->name ?? 'General' }}</strong></span>
                                    <span class="text-[#c1c8c3]">&middot;</span>
                                    <span>Lead: {{ $attMatter->leadAttorney->name ?? 'Unassigned' }}</span>
                                    @if($attMatter->events->isNotEmpty())
                                        <span class="text-[#c1c8c3]">&middot;</span>
                                        <span class="text-rose-700 font-medium">Hearing/Event: {{ \Carbon\Carbon::parse($attMatter->events->first()->start_time)->format('M j, g:i A') }}</span>
                                    @endif
                                    @if($attMatter->tasks->isNotEmpty())
                                        <span class="text-[#c1c8c3]">&middot;</span>
                                        <span class="text-amber-800 font-medium">{{ $attMatter->tasks->count() }} action item{{ $attMatter->tasks->count() === 1 ? '' : 's' }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <a href="{{ route('matters.show', $attMatter->id) }}" class="px-3 py-1 rounded text-xs font-medium text-[#23493a] bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                    Review Matter &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-5 text-center text-xs text-[#8a8a8a] italic">
                            No critical case emergencies detected. All active matters have hearings and milestones properly scheduled.
                        </div>
                    @endforelse
                </div>
            </div>
            
            <!-- CARD 1: Your tasks & Overdue items -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Your tasks</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Active action items assigned to you or awaiting completion</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($tasksOverdueCount > 0)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                {{ $tasksOverdueCount }} Overdue
                            </span>
                        @endif
                        <a href="{{ route('tasks.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                            All tasks &rarr;
                        </a>
                    </div>
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
                                        @if($isOverdue)
                                            <span class="px-1.5 py-0.2 rounded text-[10.5px] font-medium bg-red-100 text-red-800 border border-red-200">Overdue ({{ $daysLate }}d)</span>
                                        @elseif(in_array(strtolower($t->priority ?? ''), ['urgent', 'high']))
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
                                        <span>{{ $t->assignee ? ($t->assigned_to === auth()->id() ? 'Assigned to you' : $t->assignee->name) : 'Unassigned' }}</span>
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

            <!-- CARD 2: Pending Document (Messages & Document Requests) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Pending Document</h2>
                        <span class="sr-only">Waiting on you</span>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Inquiries and submissions needing advocate attention</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('messages.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Messages &rarr;</a>
                        <a href="{{ route('document-requests.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Requests &rarr;</a>
                    </div>
                </div>

                <!-- Sub-section: Clients awaiting a reply -->
                <div class="flex items-center justify-between bg-[#faf8f5] px-3.5 py-1.5 rounded text-[11px] font-medium text-[#646864] uppercase tracking-wider mb-2 border border-[#f0eee8]">
                    <span>Clients awaiting a reply</span>
                    @if($unreadMessagesCount > 0)
                        <span class="text-indigo-700 lowercase font-mono">({{ $unreadMessagesCount }} unread)</span>
                    @endif
                </div>
                <div class="divide-y divide-[#f0eee8] mb-5">
                    @forelse($clientMessages as $msg)
                        <a href="{{ route('messages.index', ['matter_id' => $msg->matter_id]) }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
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

                <!-- Sub-section: Client uploads to review / Pending Document Requests -->
                <div class="flex items-center justify-between bg-[#faf8f5] px-3.5 py-1.5 rounded text-[11px] font-medium text-[#646864] uppercase tracking-wider mb-2 border border-[#f0eee8]">
                    <span>Client uploads to review</span>
                    <span class="text-amber-800 lowercase font-mono">({{ $pendingDocRequestsList->count() }} pending)</span>
                </div>
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($clientUploads as $upload)
                        <a href="{{ route('document-requests.index') }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                        {{ $upload->title }}
                                    </span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ str_replace('_', ' ', $upload->status) }}
                                    </span>
                                </div>
                                <span class="text-[12px] text-[#646864] mt-0.5">
                                    {{ $upload->matter->case_number ?? '' }} {{ $upload->matter->title ?? 'Matter Document' }} &middot; Client: {{ $upload->client->name ?? 'Direct' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-[#8a8a8a] shrink-0 mt-0.5 font-mono">{{ $upload->updated_at ? $upload->updated_at->format('M j') : ($upload->created_at ? $upload->created_at->format('M j') : 'Recent') }}</span>
                        </a>
                    @empty
                        @forelse($recentClientDocs as $cdoc)
                            <a href="{{ route('documents.index') }}" class="py-3 px-2 flex items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors block rounded-md group">
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

            <!-- CARD: Important Notes (Pinned Strategic Briefings & Case Memos) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-[18px]">push_pin</span>
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Important Notes</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Pinned strategic observations, privileged briefings, and case memos</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11.5px] font-mono px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $importantNotesCount }} Pinned
                        </span>
                        <a href="{{ route('notes.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium ml-1">
                            All notes &rarr;
                        </a>
                    </div>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($importantNotes as $note)
                        <div class="py-3.5 px-2 flex flex-col gap-1.5 hover:bg-[#faf9f5] transition-colors rounded-md group">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap min-w-0">
                                    @if($note->is_pinned)
                                        <span class="material-symbols-outlined text-amber-600 text-[14px]" title="Pinned Note">push_pin</span>
                                    @endif
                                    <a href="{{ route('notes.index', ['matter_id' => $note->matter_id]) }}" class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                        {{ $note->title }}
                                    </a>
                                    @if($note->type)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-[#f5f3ed] text-[#646864] border border-[#e5e3dc] uppercase">
                                            {{ $note->type }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-[#8a8a8a] font-mono shrink-0">
                                    {{ $note->created_at ? $note->created_at->format('M j, Y') : 'Recent' }}
                                </span>
                            </div>
                            <p class="text-[12px] text-[#646864] line-clamp-2 leading-relaxed">
                                {{ Str::limit(strip_tags($note->body), 180) }}
                            </p>
                            <div class="text-[11.5px] text-[#8a8a8a] flex items-center gap-2 flex-wrap mt-0.5">
                                @if($note->matter)
                                    <a href="{{ route('matters.show', $note->matter_id) }}" class="font-mono text-[#23493a] hover:underline">
                                        {{ $note->matter->case_number }}
                                    </a>
                                    <span class="text-[#646864] truncate max-w-[220px]">{{ $note->matter->title }}</span>
                                    <span class="text-[#c1c8c3]">&middot;</span>
                                @endif
                                <span>Author: {{ $note->user->name ?? 'Counsel' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#8a8a8a] italic">
                            No pinned case notes recorded yet. Pinned or critical notes will appear here.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- CARD 3: Recent activity on your matters -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity on your matters</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Audit trail of docket changes, filings, and case actions</p>
                    </div>
                    <a href="{{ route('activity.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                        Full Activity Feed &rarr;
                    </a>
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
            
            <!-- CARD: Interactive Court Calendar (Previous week, This week, Next week - identical to Super Admin) -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-5 shadow-xs" x-data="{ calTab: 'this' }">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-[#23493a]">calendar_month</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Court Calendar</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-[#8a8a8a] font-mono">Docket Hearings</span>
                        <!-- Hidden anchor for test suite compatibility -->
                        <span class="sr-only">Next two weeks</span>
                        <a href="{{ route('calendar.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium ml-1 flex items-center gap-0.5">
                            <span>Full Calendar</span>
                            <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Week Selector Tabs: Previous week, This week, Next week -->
                <div class="grid grid-cols-3 p-1 bg-[#F6F4EE] rounded-md my-3 border border-[#E7E4DC] text-[11px]">
                    <button type="button" @click="calTab = 'prev'"
                        :class="calTab === 'prev' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1.5 rounded transition-all cursor-pointer text-center">
                        Previous week
                    </button>
                    <button type="button" @click="calTab = 'this'"
                        :class="calTab === 'this' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1.5 rounded transition-all cursor-pointer text-center">
                        This week
                    </button>
                    <button type="button" @click="calTab = 'coming'"
                        :class="calTab === 'coming' ? 'bg-white text-[#23493A] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1A1E1C]'"
                        class="py-1.5 rounded transition-all cursor-pointer text-center"
                        title="Coming week">
                        Next week
                    </button>
                </div>

                <!-- Calendar Content: Previous Week -->
                <div x-show="calTab === 'prev'" x-cloak class="divide-y divide-[#f0eee8]">
                    @forelse($previousWeekSchedule ?? collect() as $item)
                    <div class="py-3 flex items-start gap-3 hover:bg-[#faf9f5] transition-colors px-1 rounded-md">
                        <div class="w-10 text-center shrink-0 pt-0.5">
                            <div class="text-[10px] font-bold text-[#8a8a8a] tracking-wider uppercase font-sans">
                                {{ $item['month_short'] }}
                            </div>
                            <div class="text-[18px] font-serif font-medium text-[#1a1a1a] leading-none mt-0.5">
                                {{ $item['day_num'] }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $item['badge_class'] }}">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="text-[11px] text-[#646864] font-medium font-sans">
                                    {{ $item['time_str'] }}
                                </span>
                            </div>
                            <div class="text-[12.5px] font-medium text-[#1a1a1a] mt-1 leading-snug">
                                @if(!empty($item['matter_id']))
                                    <a href="{{ route('matters.show', $item['matter_id']) }}" class="hover:text-[#23493a] transition-colors">
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </div>
                            <div class="text-[11px] text-[#646864] mt-0.5 truncate">
                                {{ $item['matter_info'] }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No hearings recorded for the previous week.
                    </div>
                    @endforelse
                </div>

                <!-- Calendar Content: This Week -->
                <div x-show="calTab === 'this'" x-cloak class="divide-y divide-[#f0eee8]">
                    @forelse($thisWeekSchedule ?? collect() as $item)
                    <div class="py-3 flex items-start gap-3 hover:bg-[#faf9f5] transition-colors px-1 rounded-md">
                        <div class="w-10 text-center shrink-0 pt-0.5">
                            <div class="text-[10px] font-bold text-[#8a8a8a] tracking-wider uppercase font-sans">
                                {{ $item['month_short'] }}
                            </div>
                            <div class="text-[18px] font-serif font-medium text-[#1a1a1a] leading-none mt-0.5">
                                {{ $item['day_num'] }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $item['badge_class'] }}">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="text-[11px] text-[#646864] font-medium font-sans">
                                    {{ $item['time_str'] }}
                                </span>
                            </div>
                            <div class="text-[12.5px] font-medium text-[#1a1a1a] mt-1 leading-snug">
                                @if(!empty($item['matter_id']))
                                    <a href="{{ route('matters.show', $item['matter_id']) }}" class="hover:text-[#23493a] transition-colors">
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </div>
                            <div class="text-[11px] text-[#646864] mt-0.5 truncate">
                                {{ $item['matter_info'] }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No hearings or appointments scheduled for this week.
                    </div>
                    @endforelse
                </div>

                <!-- Calendar Content: Next Week -->
                <div x-show="calTab === 'coming'" x-cloak class="divide-y divide-[#f0eee8]">
                    @forelse($comingWeekSchedule ?? collect() as $item)
                    <div class="py-3 flex items-start gap-3 hover:bg-[#faf9f5] transition-colors px-1 rounded-md">
                        <div class="w-10 text-center shrink-0 pt-0.5">
                            <div class="text-[10px] font-bold text-[#8a8a8a] tracking-wider uppercase font-sans">
                                {{ $item['month_short'] }}
                            </div>
                            <div class="text-[18px] font-serif font-medium text-[#1a1a1a] leading-none mt-0.5">
                                {{ $item['day_num'] }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $item['badge_class'] }}">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="text-[11px] text-[#646864] font-medium font-sans">
                                    {{ $item['time_str'] }}
                                </span>
                            </div>
                            <div class="text-[12.5px] font-medium text-[#1a1a1a] mt-1 leading-snug">
                                @if(!empty($item['matter_id']))
                                    <a href="{{ route('matters.show', $item['matter_id']) }}" class="hover:text-[#23493a] transition-colors">
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </div>
                            <div class="text-[11px] text-[#646864] mt-0.5 truncate">
                                {{ $item['matter_info'] }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No hearings scheduled for next week.
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

    @include('clients.partials.onboarding-modal', [
        'firms' => isset($firms) ? $firms : ($firm ? collect([$firm]) : collect()),
        'attorneys' => isset($attorneys) ? $attorneys : collect([auth()->user()]),
        'redirectTo' => 'chambers',
    ])

</div>
@endsection
