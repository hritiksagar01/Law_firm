@extends('layouts.app')

@section('title', 'Case Chronology: ' . $matter->case_number . ' — ' . config('app.name', 'Sharma Legal Chambers'))
@section('header_title', 'Case Chronology')

@section('content')
<div x-data="{
    openRecordModal: false,
    selectedTemplate: '',
    applyTemplate(title, type, isSafe) {
        $refs.titleInput.value = title;
        $refs.typeSelect.value = type;
        $refs.safeCheckbox.checked = isSafe;
    }
}" class="flex flex-col w-full text-[#1a1a1a] pb-12">

    <!-- Top Breadcrumb & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#8a8a8a] mb-1">
                <a href="{{ route('matters.index') }}" class="hover:text-[#1a1a1a] transition-colors">Matters</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('matters.show', $matter) }}" class="hover:text-[#1a1a1a] transition-colors font-mono">{{ $matter->case_number }}</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-[#1a1a1a] font-medium">Case Chronology</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-semibold text-[#1a1a1a] tracking-tight">
                    Case Chronology &amp; Procedural Timeline
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-medium border {{ $matter->status === 'closed' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' }}">
                    {{ ucfirst($matter->status) }}
                </span>
            </div>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Matter: <strong class="text-[#1a1a1a]">{{ $matter->title }}</strong> &bull; Client: <strong>{{ $matter->client->name ?? 'Unassigned' }}</strong> &bull; Lead Counsel: <strong>{{ $matter->leadAttorney->name ?? 'Unassigned' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <a href="{{ route('matters.show', $matter) }}" class="btn-secondary h-9 px-3.5 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">folder_open</span>
                <span>Dossier Overview</span>
            </a>

            <button type="button" @click="openRecordModal = true" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">add_circle</span>
                <span>Record Milestone</span>
            </button>
        </div>
    </div>

    <!-- Case Progression Pipeline Tracker -->
    <div class="bg-white border border-[#e5e3dc] rounded-md p-5 mb-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">timeline</span>
                    <span>Illustrative Case Chronology Pipeline</span>
                </h2>
                <p class="text-xs text-[#646864] mt-0.5">
                    Sequential milestones reflecting evidentiary review, complaint drafting, court filing, and docketed hearings.
                </p>
            </div>
            <span class="text-[11px] font-mono text-[#8a8a8a]">
                {{ $stats['total_events'] }} recorded events across dossier history
            </span>
        </div>

        <!-- Pipeline Step Chain -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 pt-2">
            <!-- Step 1: Intake & Records Upload -->
            <div class="p-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">STAGE 01</span>
                        <span class="material-symbols-outlined text-[18px] text-emerald-700">upload_file</span>
                    </div>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Client Uploads Records</h3>
                    <p class="text-[11px] text-[#646864] mt-1 leading-snug">
                        Medical records, dispute contracts, and evidentiary exhibits provided.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[10px] font-mono text-[#8a8a8a]">
                    <span>Status</span>
                    <span class="text-emerald-700 font-medium">Logged</span>
                </div>
            </div>

            <!-- Step 2: Attorney Review -->
            <div class="p-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-sky-100 text-sky-800">STAGE 02</span>
                        <span class="material-symbols-outlined text-[18px] text-sky-700">visibility</span>
                    </div>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Counsel Review</h3>
                    <p class="text-[11px] text-[#646864] mt-1 leading-snug">
                        Advising counsel reviews documents, legal precedents, and damages.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[10px] font-mono text-[#8a8a8a]">
                    <span>Status</span>
                    <span class="text-sky-700 font-medium">In Progress</span>
                </div>
            </div>

            <!-- Step 3: Complaint Drafted -->
            <div class="p-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-violet-100 text-violet-800">STAGE 03</span>
                        <span class="material-symbols-outlined text-[18px] text-violet-700">description</span>
                    </div>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Complaint Drafted</h3>
                    <p class="text-[11px] text-[#646864] mt-1 leading-snug">
                        Primary pleading, affidavit, and prayer for relief formulated.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[10px] font-mono text-[#8a8a8a]">
                    <span>Status</span>
                    <span class="text-violet-700 font-medium">Drafted</span>
                </div>
            </div>

            <!-- Step 4: Complaint Filed -->
            <div class="p-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">STAGE 04</span>
                        <span class="material-symbols-outlined text-[18px] text-amber-700">gavel</span>
                    </div>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Complaint Filed</h3>
                    <p class="text-[11px] text-[#646864] mt-1 leading-snug">
                        Substantive filing lodged with registry; case number issued.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[10px] font-mono text-[#8a8a8a]">
                    <span>Status</span>
                    <span class="text-amber-700 font-medium">Filed</span>
                </div>
            </div>

            <!-- Step 5: Hearing Scheduled -->
            <div class="p-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">STAGE 05</span>
                        <span class="material-symbols-outlined text-[18px] text-rose-700">calendar_month</span>
                    </div>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Hearing Scheduled</h3>
                    <p class="text-[11px] text-[#646864] mt-1 leading-snug">
                        Court summons, preliminary hearing, or motion on docket.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[10px] font-mono text-[#8a8a8a]">
                    <span>Status</span>
                    <span class="text-rose-700 font-medium">Docketed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">TOTAL ACTIONS</span>
            <span class="text-lg font-semibold text-[#1a1a1a] mt-0.5 block">{{ $stats['total_events'] }}</span>
            <span class="text-[10.5px] text-[#646864] mt-1 block">Full audit log</span>
        </div>

        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">CLIENT-SAFE</span>
            <span class="text-lg font-semibold text-emerald-800 mt-0.5 block">{{ $stats['client_safe_events'] }}</span>
            <span class="text-[10.5px] text-emerald-700 mt-1 block">Portal visible</span>
        </div>

        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">INTERNAL PRIVILEGED</span>
            <span class="text-lg font-semibold text-[#646864] mt-0.5 block">{{ $stats['internal_events'] }}</span>
            <span class="text-[10.5px] text-[#8a8a8a] mt-1 block">Chambers only</span>
        </div>

        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">FILINGS &amp; DOCS</span>
            <span class="text-lg font-semibold text-sky-800 mt-0.5 block">{{ $stats['document_events'] }}</span>
            <span class="text-[10.5px] text-sky-700 mt-1 block">Uploaded &amp; viewed</span>
        </div>

        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">DOCKET &amp; HEARINGS</span>
            <span class="text-lg font-semibold text-purple-800 mt-0.5 block">{{ $stats['hearing_events'] }}</span>
            <span class="text-[10.5px] text-purple-700 mt-1 block">Court dates</span>
        </div>

        <div class="p-3.5 bg-white border border-[#e5e3dc] rounded-md shadow-xs">
            <span class="text-[11px] font-mono text-[#8a8a8a] block">TASKS RESOLVED</span>
            <span class="text-lg font-semibold text-amber-800 mt-0.5 block">{{ $stats['tasks_events'] }}</span>
            <span class="text-[10.5px] text-amber-700 mt-1 block">Directives logged</span>
        </div>
    </div>

    <!-- Filters, Ordering & Search Bar -->
    <div class="bg-white border border-[#e5e3dc] rounded-md p-4 mb-6 shadow-xs">
        <form method="GET" action="{{ route('matters.chronology', $matter) }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
            <!-- Left Filter Pills / Selects -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Event Type Filter -->
                <div class="flex items-center gap-1.5">
                    <label class="font-medium text-[#646864] whitespace-nowrap">Event Type:</label>
                    <select name="type" onchange="this.form.submit()" class="h-8 px-2.5 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>All Event Types</option>
                        <option value="milestones" {{ $filterType === 'milestones' ? 'selected' : '' }}>Milestones &amp; Procedural</option>
                        <option value="documents" {{ $filterType === 'documents' ? 'selected' : '' }}>Documents &amp; Filings</option>
                        <option value="hearings" {{ $filterType === 'hearings' ? 'selected' : '' }}>Hearings &amp; Docket</option>
                        <option value="tasks" {{ $filterType === 'tasks' ? 'selected' : '' }}>Tasks &amp; Directives</option>
                        <option value="communications" {{ $filterType === 'communications' ? 'selected' : '' }}>Messages &amp; Communications</option>
                        <option value="notes" {{ $filterType === 'notes' ? 'selected' : '' }}>Case Notes</option>
                        <option value="team" {{ $filterType === 'team' ? 'selected' : '' }}>Legal Team Assignments</option>
                    </select>
                </div>

                <!-- Visibility Filter -->
                <div class="flex items-center gap-1.5">
                    <label class="font-medium text-[#646864] whitespace-nowrap">Portal Safety:</label>
                    <select name="visibility" onchange="this.form.submit()" class="h-8 px-2.5 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="all" {{ $filterVisibility === 'all' ? 'selected' : '' }}>All Activities (Full Dossier)</option>
                        <option value="client_safe" {{ $filterVisibility === 'client_safe' ? 'selected' : '' }}>Client Portal Safe Only</option>
                        <option value="internal" {{ $filterVisibility === 'internal' ? 'selected' : '' }}>Privileged Internal Work Product Only</option>
                    </select>
                </div>

                <!-- Chronological Order -->
                <div class="flex items-center gap-1.5">
                    <label class="font-medium text-[#646864] whitespace-nowrap">Sort Order:</label>
                    <select name="order" onchange="this.form.submit()" class="h-8 px-2.5 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>Chronological (Oldest First &rarr; Story)</option>
                        <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>Reverse Chronological (Newest First)</option>
                    </select>
                </div>
            </div>

            <!-- Right: Search Box & Reset -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search chronology..." class="w-full h-8 pl-8 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                    <span class="material-symbols-outlined text-[16px] text-[#8a8a8a] absolute left-2.5 top-2">search</span>
                </div>

                <button type="submit" class="btn-secondary h-8 px-3 text-xs">Filter</button>
                @if(request()->hasAny(['type', 'visibility', 'order', 'q']))
                    <a href="{{ route('matters.chronology', $matter) }}" class="h-8 px-2.5 inline-flex items-center text-xs text-[#8a8a8a] hover:text-[#1a1a1a]">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Chronological Vertical Timeline View -->
    <div class="bg-white border border-[#e5e3dc] rounded-md p-6 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-6">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-xl text-[#23493a]">history_edu</span>
                <h2 class="text-sm font-semibold text-[#1a1a1a]">
                    Chronological Event Record ({{ $activities->count() }} Entries)
                </h2>
            </div>
            <span class="text-[11px] font-mono text-[#8a8a8a]">
                Showing: {{ $direction === 'asc' ? 'Earliest to Latest' : 'Latest to Earliest' }}
            </span>
        </div>

        @if($activities->count() > 0)
        <div class="relative pl-6 sm:pl-8 space-y-6 before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-[#e5e3dc]">
            @foreach($activities as $act)
            @php
                $isSafe = (bool) $act->is_client_safe;
                $icon = $act->icon;

                // Color schemes based on activity type
                $badgeStyle = match($act->activity_type) {
                    'matter_created' => 'bg-emerald-50 text-emerald-800 border-emerald-200 ring-emerald-500/20',
                    'complaint_drafted', 'complaint_filed', 'milestone', 'procedural_milestone' => 'bg-amber-50 text-amber-800 border-amber-200 ring-amber-500/20',
                    'calendar_event', 'hearing_scheduled' => 'bg-purple-50 text-purple-800 border-purple-200 ring-purple-500/20',
                    'document_uploaded', 'document_viewed', 'document_downloaded', 'document_shared', 'medical_records_reviewed' => 'bg-sky-50 text-sky-800 border-sky-200 ring-sky-500/20',
                    'message_sent', 'client_activity' => 'bg-teal-50 text-teal-800 border-teal-200 ring-teal-500/20',
                    'task_created', 'task_completed' => 'bg-indigo-50 text-indigo-800 border-indigo-200 ring-indigo-500/20',
                    'note_added' => 'bg-rose-50 text-rose-800 border-rose-200 ring-rose-500/20',
                    'status_changed' => 'bg-amber-50 text-amber-900 border-amber-300 ring-amber-600/20',
                    default => 'bg-[#faf9f5] text-[#1a1a1a] border-[#e5e3dc] ring-black/5'
                };

                $dotColor = match($act->activity_type) {
                    'matter_created' => 'bg-emerald-600',
                    'complaint_drafted', 'complaint_filed', 'milestone' => 'bg-amber-600',
                    'calendar_event', 'hearing_scheduled' => 'bg-purple-600',
                    'document_uploaded', 'document_viewed', 'document_downloaded', 'document_shared' => 'bg-sky-600',
                    'message_sent', 'client_activity' => 'bg-teal-600',
                    'task_created', 'task_completed' => 'bg-indigo-600',
                    'note_added' => 'bg-rose-600',
                    'status_changed' => 'bg-amber-700',
                    default => 'bg-[#23493a]'
                };
            @endphp

            <div class="relative flex items-start gap-4 group">
                <!-- Timeline Dot Indicator -->
                <div class="absolute -left-6 sm:-left-8 top-1.5 w-5 h-5 rounded-full {{ $dotColor }} border-2 border-white ring-4 ring-black/5 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[11px]">{{ $icon }}</span>
                </div>

                <!-- Event Content Card -->
                <div class="flex-1 bg-[#faf9f5] hover:bg-[#f6f5ef] transition-colors border border-[#e5e3dc] rounded-md p-4 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-[#f0eee8]">
                        <!-- Left: Type Badge & Title -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 text-[10.5px] font-semibold rounded border {{ $badgeStyle }} inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">{{ $icon }}</span>
                                <span>{{ $act->type_label }}</span>
                            </span>

                            @if($isSafe)
                                <span class="px-2 py-0.5 text-[10px] font-mono rounded bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1" title="Visible on client portal timeline">
                                    <span class="material-symbols-outlined text-[12px]">visibility</span>
                                    <span>Client Safe</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-mono rounded bg-amber-100 text-amber-900 border border-amber-200 inline-flex items-center gap-1" title="Privileged internal chambers record only">
                                    <span class="material-symbols-outlined text-[12px]">lock</span>
                                    <span>Internal Privileged</span>
                                </span>
                            @endif
                        </div>

                        <!-- Right: Timestamp -->
                        <div class="flex items-center gap-2 text-xs font-mono text-[#8a8a8a]">
                            <span class="text-[#1a1a1a] font-medium">{{ $act->effective_occurred_at->format('M d, Y h:i A') }}</span>
                            <span class="text-[11px]">({{ $act->effective_occurred_at->diffForHumans() }})</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-[#1a1a1a] mt-2.5 leading-relaxed font-sans font-normal">
                        {{ $act->description }}
                    </p>

                    <!-- Footer: Actor & Context Links -->
                    <div class="flex items-center justify-between gap-2 mt-3 pt-2 border-t border-[#f0eee8] text-[11px] text-[#646864]">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">account_circle</span>
                            <span>Recorded by: <strong>{{ $act->user->name ?? ($act->client->name ?? 'System Docket') }}</strong></span>
                        </div>

                        <!-- Deep Link to Subject if available -->
                        @if($act->subject)
                            <div class="flex items-center gap-1.5 font-medium">
                                @if($act->activity_type === 'document_uploaded' || $act->activity_type === 'document_viewed' || $act->activity_type === 'document_downloaded')
                                    @if($act->subject_type === 'App\Models\Document')
                                        <a href="{{ route('documents.view', $act->subject_id) }}" target="_blank" class="text-[#23493a] hover:underline inline-flex items-center gap-1">
                                            <span>View Filing</span>
                                            <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                        </a>
                                    @endif
                                @elseif($act->activity_type === 'note_added')
                                    <a href="{{ route('matters.show', $matter) }}#notes-section" class="text-[#23493a] hover:underline">
                                        View Note &rarr;
                                    </a>
                                @elseif($act->activity_type === 'task_created' || $act->activity_type === 'task_completed')
                                    <a href="{{ route('matters.show', $matter) }}#tasks-section" class="text-[#23493a] hover:underline">
                                        View Task &rarr;
                                    </a>
                                @elseif($act->activity_type === 'calendar_event' || $act->activity_type === 'hearing_scheduled')
                                    <a href="{{ route('matters.show', $matter) }}#hearings-section" class="text-[#23493a] hover:underline">
                                        View Hearing &rarr;
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="py-16 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
            <span class="material-symbols-outlined text-5xl text-[#8a8a8a] mb-3">timeline</span>
            <p class="font-medium text-[#1a1a1a] text-sm">No Chronology Entries Match Filter Criteria</p>
            <p class="text-[12px] text-[#646864] mt-1 max-w-md">
                No events or filings match your current filter settings. Click below to record a procedural milestone or reset filters.
            </p>
            <div class="flex items-center gap-2 mt-4">
                <a href="{{ route('matters.chronology', $matter) }}" class="btn-secondary h-8 px-3 text-xs">Reset All Filters</a>
                <button type="button" @click="openRecordModal = true" class="btn-primary h-8 px-3 text-xs">Record Procedural Milestone</button>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal: Record Procedural Milestone / Case Chronology Event -->
    <div x-show="openRecordModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="openRecordModal = false" class="bg-white border border-[#e5e3dc] rounded-md max-w-lg w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">flag</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Record Case Chronology Milestone</h3>
                </div>
                <button type="button" @click="openRecordModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Quick Template Chips -->
            <div class="mt-3.5">
                <span class="text-[11px] font-mono uppercase text-[#8a8a8a] block mb-1.5">Quick Milestone Templates</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="applyTemplate('Client uploaded medical records', 'client_activity', true)" class="px-2 py-1 text-[11px] rounded bg-[#faf9f5] hover:bg-[#e5e3dc] border border-[#e5e3dc] text-[#1a1a1a]">
                        Medical Records Uploaded
                    </button>
                    <button type="button" @click="applyTemplate('Attorney reviewed documents', 'medical_records_reviewed', false)" class="px-2 py-1 text-[11px] rounded bg-[#faf9f5] hover:bg-[#e5e3dc] border border-[#e5e3dc] text-[#1a1a1a]">
                        Attorney Reviewed Docs
                    </button>
                    <button type="button" @click="applyTemplate('Complaint drafted and verified', 'complaint_drafted', false)" class="px-2 py-1 text-[11px] rounded bg-[#faf9f5] hover:bg-[#e5e3dc] border border-[#e5e3dc] text-[#1a1a1a]">
                        Complaint Drafted
                    </button>
                    <button type="button" @click="applyTemplate('Complaint filed with Court Registry', 'complaint_filed', true)" class="px-2 py-1 text-[11px] rounded bg-[#faf9f5] hover:bg-[#e5e3dc] border border-[#e5e3dc] text-[#1a1a1a]">
                        Complaint Filed
                    </button>
                    <button type="button" @click="applyTemplate('Preliminary Hearing scheduled', 'hearing_scheduled', true)" class="px-2 py-1 text-[11px] rounded bg-[#faf9f5] hover:bg-[#e5e3dc] border border-[#e5e3dc] text-[#1a1a1a]">
                        Hearing Scheduled
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('matters.chronology.store', $matter) }}" class="mt-4 space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Milestone Title *</label>
                    <input type="text" name="title" x-ref="titleInput" required placeholder="e.g. Complaint drafted and settled by senior advocate" class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Activity Classification *</label>
                        <select name="activity_type" x-ref="typeSelect" required class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="milestone">General Procedural Milestone</option>
                            <option value="complaint_drafted">Complaint Drafted</option>
                            <option value="complaint_filed">Complaint Filed</option>
                            <option value="hearing_scheduled">Hearing Scheduled</option>
                            <option value="medical_records_reviewed">Document / Record Review</option>
                            <option value="document_uploaded">Document Uploaded</option>
                            <option value="client_activity">Client Activity</option>
                            <option value="calendar_event">Court Docket / Calendar</option>
                            <option value="status_changed">Status Changed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Date &amp; Time Occurred *</label>
                        <input type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Description / Evidentiary Notes</label>
                    <textarea name="description" rows="3" placeholder="Contextual procedural details, filing numbers, bench information, or counsel notes..." class="w-full p-2.5 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"></textarea>
                </div>

                <div class="p-3 bg-[#faf9f5] border border-[#e5e3dc] rounded-md">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_client_safe" value="1" x-ref="safeCheckbox" class="mt-0.5 rounded text-[#23493a] focus:ring-[#23493a]">
                        <div>
                            <span class="font-semibold text-[#1a1a1a] block">Make Visible on Client Portal Timeline</span>
                            <span class="text-[11px] text-[#646864] block mt-0.5">
                                If checked, this milestone will appear in the client's case portal timeline. Leave unchecked for confidential work product.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="openRecordModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Record Milestone</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
