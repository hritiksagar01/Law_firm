@extends('layouts.app')

@section('title', $matter->case_number . ' — ' . $matter->title)
@section('header_title', 'Matter ' . $matter->case_number)

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">
    <!-- Dossier Breadcrumb & Top Bar -->
    <div class="flex flex-col gap-4 pb-6 border-b border-[#e5e3dc] mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#646864] hover:text-[#23493a] transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Matters Directory</span>
            </a>
            @php
                $isClosed = in_array(strtolower($matter->status ?? ''), ['closed', 'settled', 'dismissed', 'archived']);
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $statusStyles = [
                        'Active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Open' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Intake' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Prospective' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'Conflict Check' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'On Hold' => 'bg-orange-50 text-orange-700 border-orange-200',
                        'Settled' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'Resolved' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'Closed' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'Archived' => 'bg-stone-100 text-stone-600 border-stone-200',
                    ];
                    $currentStatusStyle = $statusStyles[ucfirst($matter->status ?? '')] ?? 'bg-[#f5f3ed] text-[#23493a] border-[#e5e3dc]';
                @endphp
                <span class="inline-flex items-center gap-1 font-mono text-xs px-2.5 py-0.5 rounded-full font-medium border {{ $currentStatusStyle }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isClosed ? 'bg-slate-400' : 'bg-emerald-500' }}"></span>
                    {{ ucfirst($matter->status ?? 'Active') }}
                </span>
                @if(!empty($matter->priority) && $matter->priority !== 'medium')
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full uppercase font-semibold border 
                        {{ $matter->priority === 'urgent' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($matter->priority === 'high' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-stone-100 text-stone-600 border-stone-200') }}">
                        {{ $matter->priority }}
                    </span>
                @endif
                <span class="font-mono text-xs px-2.5 py-0.5 rounded-full bg-[#f5f3ed] text-[#23493a] font-medium border border-[#e5e3dc]">
                    {{ $matter->stage }}
                </span>
                <span class="font-mono text-xs px-2.5 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]" title="Matter Case Number">
                    {{ $matter->case_number }}
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">{{ $matter->title }}</h1>
                <div class="flex items-center gap-2 flex-wrap text-[13px] text-[#646864] mt-1.5">
                    @if($matter->court_name)
                        <span>{{ $matter->court_name }}</span>
                        <span>&middot;</span>
                    @endif
                    @if($matter->docket_number)
                        <span class="font-mono font-medium text-[#1a1a1a]">Docket: {{ $matter->docket_number }}</span>
                        <span>&middot;</span>
                    @endif
                    <span>Presiding: {{ $matter->judge_name ?? 'Bench Pending' }}</span>
                    <span>&middot;</span>
                    <span>Opened {{ $matter->opened_at?->format('M d, Y') ?? 'N/A' }}</span>
                </div>
                @if(!empty($matter->matter_types) && is_array($matter->matter_types))
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        @foreach($matter->matter_types as $mType)
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc]">
                                {{ $mType }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="document.getElementById('editAdminModal').classList.remove('hidden')" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 text-[#23493a] border-[#e5e3dc] hover:bg-[#faf8f5]" title="Maintain Permitted Matter Administrative Metadata">
                    <span class="material-symbols-outlined text-[16px]">edit_calendar</span>
                    <span>Edit Details</span>
                </button>
                @if(auth()->user()->canCloseMatters())
                    @if($isClosed)
                        <form action="{{ route('matters.status.update', $matter->id) }}" method="POST" class="inline" onsubmit="return confirm('Reopen matter dossier {{ $matter->case_number }}?');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="open">
                            <button type="submit" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 text-[#23493a] border-emerald-300 hover:bg-emerald-50">
                                <span class="material-symbols-outlined text-[16px]">lock_open</span>
                                <span>Reopen Matter</span>
                            </button>
                        </form>
                    @else
                        <button type="button" onclick="document.getElementById('closeMatterModal').classList.remove('hidden')" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 text-rose-700 hover:text-rose-800 hover:border-rose-300">
                            <span class="material-symbols-outlined text-[16px]">lock</span>
                            <span>Close Matter</span>
                        </button>
                    @endif
                @endif
                <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">draw</span>
                    <span>Draft Opinion</span>
                </a>
                <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">event</span>
                    <span>Book Hearing</span>
                </a>
                <a href="{{ route('documents.index') }}" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">upload_file</span>
                    <span>Upload Filing</span>
                </a>
            </div>
        </div>

        <!-- Dossier Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-1 pt-2 -mb-6 border-b border-[#f0eee8]">
            <a href="#overview-section" class="px-3.5 py-2 text-xs font-semibold border-b-2 border-[#23493a] text-[#23493a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">dashboard</span>
                <span>Case Overview</span>
            </a>
            <a href="#team-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">group</span>
                <span>Team ({{ $matter->users->count() }})</span>
            </a>
            <a href="#opinions-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">draw</span>
                <span>Opinions ({{ $matter->opinions->count() }})</span>
            </a>
            <a href="#hearings-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">calendar_month</span>
                <span>Hearings &amp; Docket ({{ $matter->appointments->count() + $matter->events->count() }})</span>
            </a>
            <a href="#filings-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">description</span>
                <span>Filings &amp; Evidence ({{ $matter->documents->count() }})</span>
            </a>
            <a href="#dispatches-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">forum</span>
                <span>Privileged Messages ({{ $matter->messages->count() }})</span>
            </a>
            <a href="#tasks-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">checklist</span>
                <span>Tasks ({{ $matter->tasks->count() }})</span>
            </a>
            <a href="#notes-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">sticky_note_2</span>
                <span>Case Notes ({{ $matter->caseNotes->count() }})</span>
            </a>
            <a href="#conflicts-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">policy</span>
                <span>Conflict Checks ({{ $matter->conflictChecks->count() }})</span>
            </a>
            <a href="#timeline-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">history</span>
                <span>Activity Timeline ({{ $matter->activities->count() }})</span>
            </a>
        </div>
    </div>

    @if($isClosed)
        <div class="p-4 mb-6 rounded-md bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-amber-700 text-[22px]">lock</span>
                <div>
                    <p class="text-xs font-semibold text-amber-900">This matter dossier is Closed (archived on {{ $matter->closed_at ? $matter->closed_at->format('M d, Y') : 'record' }}).</p>
                    <p class="text-[11.5px] text-amber-800 mt-0.5">Filings, privileged communications, hearings, and orders remain preserved for reference.</p>
                </div>
            </div>
            <form action="{{ route('matters.status.update', $matter->id) }}" method="POST" onsubmit="return confirm('Reopen matter dossier {{ $matter->case_number }}?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="open">
                <button type="submit" class="px-3 py-1.5 rounded bg-white hover:bg-[#faf9f5] border border-amber-300 text-amber-900 text-xs font-semibold inline-flex items-center gap-1.5 transition-colors shadow-xs whitespace-nowrap">
                    <span class="material-symbols-outlined text-[16px]">lock_open</span>
                    <span>Reopen Matter</span>
                </button>
            </form>
        </div>
    @endif

    <!-- Dossier Content Columns -->
    <div id="overview-section" class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4 items-start">
        <!-- Left 2-Columns: Milestones, Filings & Time -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Procedural Stage Tracker -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Litigation Procedural Stages</h3>
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">01</span> Intake
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">02</span> Pleadings
                    </div>
                    <div class="p-2 rounded bg-[#23493a] text-white font-semibold shadow-xs">
                        <span class="font-mono text-[10px] block text-[#a7f3d0]">03 ACTIVE</span> Discovery
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">04</span> Pre-Trial
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">05</span> Trial
                    </div>
                </div>
            </div>

            <!-- Legal Opinions & Strategy Memoranda -->
            <div id="opinions-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">draw</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Legal Opinions &amp; Case Strategy</h3>
                    </div>
                    <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Draft Opinion</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->opinions as $op)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div>
                            <a href="{{ route('opinions.show', $op) }}" class="text-xs font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                {{ $op->title }}
                            </a>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#646864]">
                                <span class="font-mono text-[#23493a]">{{ $op->opinion_number }}</span>
                                <span>&middot;</span>
                                <span class="capitalize">{{ str_replace('_', ' ', $op->type) }}</span>
                                <span>&middot;</span>
                                <span>{{ $op->author->name ?? 'Advocate' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                                @if($op->status === 'published') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($op->status === 'under_review') bg-amber-50 text-amber-700 border border-amber-200
                                @elseif($op->status === 'approved') bg-blue-50 text-blue-700 border border-blue-200
                                @else bg-gray-50 text-gray-700 border border-gray-200 @endif">
                                {{ $op->status }}
                            </span>
                            <a href="{{ route('opinions.show', $op) }}" class="p-1 rounded text-[#8a8a8a] hover:text-[#23493a]">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#8a8a8a]">No formal legal opinions drafted for this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Hearings & Court Docket Appearances -->
            <div id="hearings-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">calendar_month</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Scheduled Hearings &amp; Appearances</h3>
                    </div>
                    <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Book Appearance</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->appointments as $apt)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex flex-col items-center justify-center shrink-0">
                                <span class="font-mono text-[9px] uppercase font-bold text-[#23493a]">{{ $apt->scheduled_at->format('M') }}</span>
                                <span class="font-medium text-sm text-[#1a1a1a] leading-none">{{ $apt->scheduled_at->format('d') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('appointments.show', $apt) }}" class="text-xs font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                    {{ $apt->title }}
                                </a>
                                <div class="text-[11px] text-[#646864] mt-0.5">
                                    <span>{{ $apt->scheduled_at->format('h:i A') }}</span>
                                    @if($apt->location) <span>&middot; {{ $apt->location }}</span> @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                            @if($apt->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @elseif($apt->status === 'adjourned') bg-amber-50 text-amber-700 border border-amber-200
                            @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                            {{ $apt->status }}
                        </span>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#8a8a8a]">No court appearances currently scheduled.</div>
                    @endforelse
                </div>
            </div>

            <!-- Documents & Evidence in Matter -->
            <div id="filings-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">folder</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Matter Evidence &amp; Filings</h3>
                    </div>
                    <a href="{{ route('documents.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">Vault View &rarr;</a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->documents as $doc)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-red-50 text-red-700 flex items-center justify-center font-mono text-[10px] font-bold shrink-0 border border-red-200">
                                PDF
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1a1a] hover:underline cursor-pointer">{{ $doc->title }}</span>
                                <span class="text-[11px] text-[#8a8a8a] font-mono mt-0.5">
                                    {{ $doc->filename }} <span class="mx-1">&middot;</span> {{ $doc->formattedSize() }} <span class="mx-1">&middot;</span> v{{ $doc->version }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]">{{ $doc->privilege }}</span>
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#8a8a8a] hover:text-[#23493a] rounded inline-flex" title="Download Filing">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No documents filed in this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Privileged Attorney-Client Communications -->
            <!-- Privileged Attorney-Client Communications & Matter Threads -->
            <div id="dispatches-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] bg-[#faf8f5] flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">forum</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Matter Message Threads &amp; Dispatches</h3>
                            <span class="text-[10px] font-mono text-[#23493a] bg-[#ecfdf5] border border-[#a7f3d0] px-1.5 py-0.5 rounded font-medium uppercase">Sec 126 &amp; 129 Evidence Act &middot; Database Separated</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('messages.index', ['matter_id' => $matter->id]) }}" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1">
                            <span>Open Thread Console</span>
                            <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                        </a>
                    </div>
                </div>

                <!-- Active Threads Pills Bar -->
                @if($matter->threads && $matter->threads->count() > 0)
                <div class="p-3 bg-[#faf9f5]/50 border-b border-[#f0eee8] flex items-center gap-2 overflow-x-auto text-xs">
                    <span class="font-mono text-[10.5px] uppercase tracking-wider text-[#8a8a8a] shrink-0 font-semibold">Threads:</span>
                    @foreach($matter->threads as $th)
                        @php
                            $thBadge = match($th->thread_type) {
                                'internal_team' => 'bg-amber-50 text-amber-900 border-amber-200',
                                'client_communication' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'document_request' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                default => 'bg-blue-50 text-blue-800 border-blue-200'
                            };
                        @endphp
                        <a href="{{ route('messages.index', ['matter_id' => $matter->id, 'thread_id' => $th->id]) }}" 
                           class="px-2.5 py-1 rounded-md bg-white border border-[#e5e3dc] text-xs hover:border-[#23493a] transition-colors flex items-center gap-1.5 shrink-0 shadow-2xs">
                            <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $th->thread_number ?? 'THR-'.$th->id }}</span>
                            <span class="font-medium text-[#1a1a1a] truncate max-w-[140px]">{{ $th->subject }}</span>
                            <span class="px-1 py-0.2 rounded text-[9px] font-mono border {{ $thBadge }}">
                                {{ $th->is_internal ? '🔒 Internal' : $th->type_label }}
                            </span>
                        </a>
                    @endforeach
                </div>
                @endif

                <!-- Messages Stream -->
                <div class="p-4 flex flex-col gap-4 max-h-[380px] overflow-y-auto bg-[#faf8f5]">
                    @forelse($matter->messages as $msg)
                    @php
                        $isMe = ($msg->sender_id === auth()->id());
                        $senderName = $msg->sender?->name ?? 'Participant';
                        $senderRole = $msg->sender?->role ?? 'advocate';
                    @endphp
                    <div class="flex items-start gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-semibold text-[10px] shrink-0 shadow-xs {{ $isMe ? 'bg-[#23493a] text-white' : ($senderRole === 'client' ? 'bg-sky-700 text-white' : 'bg-[#161718] text-white') }}">
                            {{ strtoupper(substr($senderName, 0, 1)) }}
                        </div>
                        <div class="flex flex-col max-w-lg {{ $isMe ? 'items-end' : '' }}">
                            <div class="flex items-center gap-2 mb-1 text-[11px] flex-wrap">
                                <span class="font-mono text-[9px] bg-[#f5f3ed] border border-[#e5e3dc] px-1 py-0.2 rounded text-[#8a8a8a]">
                                    {{ $msg->formatted_id }}
                                </span>
                                <span class="font-semibold text-[#1a1a1a]">{{ $isMe ? 'You' : $senderName }}</span>
                                @if($msg->recipient)
                                    <span class="text-[10px] text-[#8a8a8a]">&rarr; {{ $msg->recipient->name }}</span>
                                @endif
                                <span class="text-[#8a8a8a] font-mono text-[10px]">{{ ($msg->sent_at ?? $msg->created_at)->format('M d, g:i A') }}</span>
                                @if($msg->thread)
                                    <span class="font-mono text-[9px] px-1 py-0.2 rounded bg-stone-100 text-stone-600">
                                        {{ $msg->thread->type_label }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-3 rounded-md text-xs leading-relaxed {{ $isMe ? 'bg-[#23493a] text-white rounded-tr-none shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#1a1a1a] rounded-tl-none shadow-xs' }}">
                                <p class="whitespace-pre-line">{{ $msg->body }}</p>

                                @if($msg->attachments && $msg->attachments->count() > 0)
                                <div class="mt-2 pt-2 border-t {{ $isMe ? 'border-[#3a6352]' : 'border-[#e5e3dc]' }} flex flex-col gap-1">
                                    @foreach($msg->attachments as $att)
                                    <a href="{{ route('messages.attachments.download', $att->id) }}" 
                                       class="inline-flex items-center justify-between gap-2 p-1 rounded text-[11px] {{ $isMe ? 'bg-[#1a382b] text-white hover:bg-[#152e23]' : 'bg-[#faf9f5] border border-[#e5e3dc] text-[#23493a] hover:bg-white' }}">
                                        <span class="flex items-center gap-1 truncate">
                                            <span class="material-symbols-outlined text-[13px]">attach_file</span>
                                            <span class="truncate">{{ $att->file_name }}</span>
                                        </span>
                                        <span class="font-mono text-[9px] opacity-75 shrink-0">{{ $att->formattedSize() }}</span>
                                    </a>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 mt-0.5 text-[9.5px] font-mono text-[#8a8a8a]">
                                @if($msg->is_read)
                                    <span class="text-[#065f46] inline-flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[12px]">done_all</span>
                                        <span>Read</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[12px]">check</span>
                                        <span>Sent</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No dispatches recorded under this matter dossier yet.</div>
                    @endforelse
                </div>

                <!-- Message Composer -->
                <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="p-3 bg-white border-t border-[#f0eee8] flex flex-col gap-2">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                    @if($matter->threads && $matter->threads->first())
                        <input type="hidden" name="thread_id" value="{{ $matter->threads->first()->id }}"/>
                    @endif
                    <div class="flex gap-2">
                        <input name="body" required placeholder="Type privileged counsel communication or reply..." class="flex-1 px-3 py-2 text-xs rounded-md border border-[#e5e3dc] outline-none focus:border-[#23493a]"/>
                        <button type="submit" class="btn-primary h-9 px-4 text-xs flex items-center gap-1 shrink-0 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">send</span>
                            <span>Send</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-[#646864]">
                        <label class="inline-flex items-center gap-1 cursor-pointer hover:text-[#1a1a1a]">
                            <span class="material-symbols-outlined text-sm text-[#23493a]">attach_file</span>
                            <span>Attach file</span>
                            <input type="file" name="attachments[]" multiple class="hidden" onchange="document.getElementById('matter-inline-attach').innerText = this.files.length + ' file(s)'"/>
                        </label>
                        <span id="matter-inline-attach" class="font-mono text-[10px]"></span>
                        <a href="{{ route('messages.index', ['matter_id' => $matter->id]) }}" class="hover:underline text-[#23493a] font-medium">Start new thread &rarr;</a>
                    </div>
                </form>
            </div>

            <!-- Matter Tasks & Deadlines (F-18) -->
            <div id="tasks-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] bg-[#faf8f5] flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">checklist</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Case Tasks &amp; Procedural Steps</h3>
                            <span class="text-[10px] font-mono text-[#646864]">{{ $matter->tasks->where('status', 'completed')->count() }} of {{ $matter->tasks->count() }} completed</span>
                        </div>
                    </div>
                    <a href="{{ route('tasks.index', ['matter_id' => $matter->id]) }}" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1">
                        <span>Task Hub</span>
                        <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->tasks as $mtask)
                        <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-[#faf9f5] transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <form action="{{ route('tasks.toggle', $mtask->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-4.5 h-4.5 rounded border flex items-center justify-center transition-colors cursor-pointer {{ $mtask->status === 'completed' ? 'bg-[#23493a] border-[#23493a] text-white' : 'border-[#c1c8c3] hover:border-[#23493a] bg-white' }}">
                                        @if($mtask->status === 'completed')
                                            <span class="material-symbols-outlined text-[12px]">check</span>
                                        @endif
                                    </button>
                                </form>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-mono text-[10px] text-[#23493a] font-semibold">{{ $mtask->formatted_id }}</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono border {{ $mtask->priority_badge_classes }}">{{ $mtask->priority_label }}</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono border {{ $mtask->status_badge_classes }}">{{ $mtask->status_label }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-[#1a1a1a] truncate mt-0.5 {{ $mtask->status === 'completed' ? 'line-through text-[#8a8a8a]' : '' }}">
                                        {{ $mtask->title }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 text-[11px] font-mono text-[#8a8a8a]">
                                <span>Due: {{ $mtask->due_date ? $mtask->due_date->format('d M') : 'None' }}</span>
                                <span class="text-[#1a1a1a]">&middot; {{ $mtask->assignee->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-[#8a8a8a]">
                            No litigation tasks scheduled for this matter dossier yet.
                            <a href="{{ route('tasks.index', ['matter_id' => $matter->id]) }}" class="text-[#23493a] underline ml-1">Schedule task</a>.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Case Notes & Strategy Memoranda (F-12) -->
            <div id="notes-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden" x-data="{ addNote: false }">
                <div class="p-4 border-b border-[#f0eee8] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">sticky_note_2</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Case Notes &amp; Strategy Memoranda</h3>
                    </div>
                    <button @click="addNote = !addNote" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span x-text="addNote ? 'Cancel' : 'Add Note'">Add Note</span>
                    </button>
                </div>

                <!-- Add Note Inline Form -->
                <div x-show="addNote" x-cloak class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5]/50">
                    <form method="POST" action="{{ route('notes.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                        <div>
                            <input type="text" name="title" required placeholder="Note Title (e.g. Witness Interview Summary, Discovery Strategy)"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"/>
                        </div>
                        <div>
                            <textarea name="body" rows="3" required placeholder="Substantive case notes, findings, observations, or action points..."
                                class="w-full p-3 text-xs rounded-lg border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"></textarea>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-4 flex-wrap">
                                <label class="flex items-center gap-1.5 text-[#646864] cursor-pointer">
                                    <input type="radio" name="type" value="internal" checked class="text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Internal Only</span>
                                </label>
                                <label class="flex items-center gap-1.5 text-[#646864] cursor-pointer">
                                    <input type="radio" name="type" value="client_visible" class="text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Client Visible</span>
                                </label>
                                @if(auth()->user()->canAccessPrivilegedNotes())
                                <label class="flex items-center gap-1.5 text-purple-800 font-medium cursor-pointer">
                                    <input type="radio" name="type" value="privileged" class="text-purple-700 focus:ring-purple-700"/>
                                    <span>Privileged Attorney Note</span>
                                </label>
                                @endif
                                <label class="flex items-center gap-1.5 text-[#646864] cursor-pointer">
                                    <input type="checkbox" name="is_pinned" value="1" class="rounded text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Pin to Top</span>
                                </label>
                            </div>
                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-colors">
                                Save Note
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notes List -->
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->caseNotes->sortByDesc('is_pinned')->sortByDesc('created_at') as $note)
                    <div class="p-4 hover:bg-[#faf9f5] transition-colors {{ $note->is_pinned ? 'bg-[#23493a]/[0.02]' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($note->is_pinned)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#23493a]/10 text-[#23493a]">
                                        <span class="material-symbols-outlined text-xs">push_pin</span>
                                        <span>Pinned</span>
                                    </span>
                                    @endif
                                    <h4 class="text-xs font-semibold text-[#1a1a1a]">{{ $note->title }}</h4>
                                    @if($note->is_privileged || in_array($note->type, ['privileged', 'attorney_only']))
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-purple-50 text-purple-700 border border-purple-200">
                                            Privileged Attorney Memo
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono {{ $note->type === 'client_visible' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $note->type === 'client_visible' ? 'Client Visible' : 'Internal Memo' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#646864] mt-1.5 leading-relaxed whitespace-pre-line">{{ $note->body }}</p>
                                <div class="flex items-center gap-3 mt-2 text-[11px] text-[#8a8a8a]">
                                    <span>By {{ $note->user?->name ?? 'Advocate' }}</span>
                                    <span>&middot;</span>
                                    <span class="font-mono">{{ $note->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <form method="POST" action="{{ route('notes.toggle-pin', $note) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-[#23493a] transition-colors" title="{{ $note->is_pinned ? 'Unpin' : 'Pin to top' }}">
                                        <span class="material-symbols-outlined text-base">{{ $note->is_pinned ? 'keep_off' : 'push_pin' }}</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Delete this note?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-rose-600 transition-colors" title="Delete note">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No notes recorded for this matter yet. Click "Add Note" to author internal memos.</div>
                    @endforelse
                </div>
            </div>

            <!-- Conflict Checks & Opposing Party Clearances (F-14) -->
            <div id="conflicts-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">policy</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Conflict Checks &amp; Ethics Clearance</h3>
                    </div>
                    <a href="{{ route('conflict-checks.index', ['matter_id' => $matter->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add_moderator</span>
                        <span>New Conflict Check</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->conflictChecks as $check)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('conflict-checks.show', $check) }}" class="text-xs font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline font-mono">
                                    {{ $check->check_number }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase {{ $check->status === 'clear' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($check->status === 'conflict_found' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                    {{ $check->status_label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#646864]">
                                <span>Checked by {{ $check->checker?->name ?? 'Ethics Officer' }}</span>
                                <span>&middot;</span>
                                <span class="font-mono">{{ $check->checked_at?->format('d M Y') ?? 'Recent' }}</span>
                                @if($check->reviewer)
                                    <span>&middot;</span>
                                    <span>Signed by {{ $check->reviewer->name }}</span>
                                @endif
                            </div>
                            @if($check->conflict_description)
                                <p class="text-xs text-[#8a8a8a] mt-1 line-clamp-1 italic">{{ $check->conflict_description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('conflict-checks.show', $check) }}" class="px-2.5 py-1 rounded text-xs font-medium text-[#23493a] bg-stone-100 hover:bg-stone-200 transition-colors">
                            Dossier &rarr;
                        </a>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">
                        No formal conflict checks recorded for this matter yet.
                        <a href="{{ route('conflict-checks.index', ['matter_id' => $matter->id]) }}" class="text-[#23493a] underline ml-1">Run initial ethical check</a>.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Matter Activity Timeline & Case Chronology (Feature 28) -->
            <div id="timeline-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">history</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Case Chronology &amp; Procedural Timeline</h3>
                            <p class="text-xs text-[#646864] mt-0.5">Chronological record of evidentiary filings, tasks, court hearings, and client interactions</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[#8a8a8a] font-mono">{{ $matter->activities->count() }} Recorded</span>
                        <a href="{{ route('matters.chronology', $matter) }}" class="btn-primary h-7 px-2.5 text-xs inline-flex items-center gap-1 shadow-xs">
                            <span class="material-symbols-outlined text-[15px]">timeline</span>
                            <span>Full Chronology</span>
                        </a>
                    </div>
                </div>

                <!-- Illustrative Progression Tracker -->
                <div class="px-4 py-2.5 bg-[#faf9f5] border-b border-[#f0eee8] flex items-center gap-1.5 overflow-x-auto text-[11px] text-[#646864]">
                    <span class="font-mono text-[#8a8a8a] text-[10px] uppercase whitespace-nowrap">Progress:</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-medium whitespace-nowrap">Client Records Uploaded</span>
                    <span class="text-[#8a8a8a]">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-medium whitespace-nowrap">Attorney Review</span>
                    <span class="text-[#8a8a8a]">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-violet-100 text-violet-800 font-medium whitespace-nowrap">Complaint Drafted</span>
                    <span class="text-[#8a8a8a]">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-medium whitespace-nowrap">Complaint Filed</span>
                    <span class="text-[#8a8a8a]">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-medium whitespace-nowrap">Hearing Scheduled</span>
                </div>

                <div class="p-4">
                    <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#f0eee8]">
                        @forelse($matter->activities->sortByDesc(fn($a) => $a->occurred_at ?? $a->created_at)->take(12) as $activity)
                        @php
                            $actIcon = $activity->icon;
                            $isClientSafe = (bool) $activity->is_client_safe;
                        @endphp
                        <div class="relative flex items-start gap-3">
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-white border-2 border-[#23493a] flex items-center justify-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></div>
                            </div>
                            <div class="flex-1 min-w-0 bg-[#faf9f5] border border-[#e5e3dc] rounded-md p-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[14px] text-[#23493a]">{{ $actIcon }}</span>
                                        <span class="font-semibold text-[#1a1a1a]">{{ $activity->user?->name ?? ($activity->client?->name ?? 'Chambers') }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[9.5px] font-mono uppercase bg-stone-100 text-stone-700 border border-stone-200">
                                            {{ $activity->type_label }}
                                        </span>
                                        @if($isClientSafe)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-50 text-emerald-700 border border-emerald-200" title="Visible on Client Portal">Client Safe</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-amber-50 text-amber-800 border border-amber-200" title="Internal Work Product Only">Internal</span>
                                        @endif
                                    </div>
                                    <span class="text-[#8a8a8a] font-mono text-[11px]">{{ ($activity->occurred_at ?? $activity->created_at)->format('M d, Y h:i A') }}</span>
                                </div>
                                <p class="text-xs text-[#1a1a1a] mt-1.5 leading-relaxed">{{ $activity->description }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-6 text-xs text-[#8a8a8a] bg-[#faf9f5] rounded border border-[#e5e3dc]">
                            <p class="font-medium text-[#1a1a1a]">No chronological activities logged for this matter yet.</p>
                            <a href="{{ route('matters.chronology', $matter) }}" class="text-[#23493a] underline text-xs mt-1 inline-block">Record the first procedural milestone &rarr;</a>
                        </div>
                        @endforelse
                    </div>

                    @if($matter->activities->count() > 12)
                        <div class="mt-4 pt-3 border-t border-[#f0eee8] text-center">
                            <a href="{{ route('matters.chronology', $matter) }}" class="text-xs font-semibold text-[#23493a] hover:underline inline-flex items-center gap-1">
                                <span>View all {{ $matter->activities->count() }} activities in full Case Chronology</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right 1-Column: Judicial Information & Team -->
        <div class="flex flex-col gap-6" x-data="{ openAddTeamModal: false }">

            <!-- Forum, Bench & Judicial Assignment Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-[#f0eee8]">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a]">Judicial Forum &amp; Bench</h3>
                    @if($matter->docket_number)
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-stone-100 text-stone-700 border border-stone-200">
                            {{ $matter->docket_number }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-col gap-3.5 text-xs">
                    <!-- Court Forum & Type -->
                    <div>
                        <span class="text-[#8a8a8a] block text-[11px]">Court / Judicial Forum</span>
                        <span class="font-medium text-[#1a1a1a] block">{{ $matter->court_name ?? 'Forum Not Assigned' }}</span>
                        @if($matter->court_type)
                            <span class="text-[11px] text-[#23493a] font-mono mt-0.5 inline-block">{{ $matter->court_type }}</span>
                        @endif
                    </div>

                    <!-- Jurisdiction, State & County -->
                    @if($matter->jurisdiction || $matter->state || $matter->county)
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-[#f0eee8]">
                            @if($matter->jurisdiction)
                                <div>
                                    <span class="text-[#8a8a8a] block text-[10px]">Jurisdiction</span>
                                    <span class="text-[#1a1a1a] text-[11.5px] font-medium">{{ $matter->jurisdiction }}</span>
                                </div>
                            @endif
                            @if($matter->state || $matter->county)
                                <div>
                                    <span class="text-[#8a8a8a] block text-[10px]">Territory / County</span>
                                    <span class="text-[#1a1a1a] text-[11.5px]">{{ $matter->county ? $matter->county . ', ' : '' }}{{ $matter->state ?? '' }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Bench & Judges (Current, Previous, New) -->
                    <div class="pt-1 border-t border-[#f0eee8]">
                        <span class="text-[#8a8a8a] block text-[11px] mb-1.5">Bench &amp; Judicial Officers</span>
                        @if(!empty($matter->judges) && is_array($matter->judges))
                            <div class="space-y-2">
                                @foreach($matter->judges as $judgeItem)
                                    <div class="p-2 rounded bg-[#faf8f5] border border-[#f0eee8]">
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="font-medium text-[#1a1a1a]">{{ $judgeItem['name'] ?? 'Judicial Officer' }}</span>
                                            @php
                                                $jType = $judgeItem['type'] ?? 'current';
                                                $jBadge = match($jType) {
                                                    'current' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'previous' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    'new' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'magistrate' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                    default => 'bg-stone-100 text-stone-600 border-stone-200',
                                                };
                                                $jText = match($jType) {
                                                    'current' => 'Presiding',
                                                    'previous' => 'Previous Judge',
                                                    'new' => 'New Judge',
                                                    'magistrate' => 'Magistrate',
                                                    default => ucfirst(str_replace('_', ' ', $jType)),
                                                };
                                            @endphp
                                            <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono uppercase border {{ $jBadge }}">
                                                {{ $jText }}
                                            </span>
                                        </div>
                                        @if(!empty($judgeItem['courtroom']))
                                            <div class="text-[10.5px] text-[#646864] font-mono">{{ $judgeItem['courtroom'] }}</div>
                                        @endif
                                        @if(!empty($judgeItem['notes']))
                                            <div class="text-[10.5px] text-[#8a8a8a] mt-0.5 italic">{{ $judgeItem['notes'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2 rounded bg-[#faf8f5] border border-[#f0eee8]">
                                <span class="font-medium text-[#1a1a1a] block">{{ $matter->judge_name ?? 'Magistrate Pending' }}</span>
                                <span class="text-[10.5px] text-[#8a8a8a]">Presiding Bench</span>
                            </div>
                        @endif
                    </div>

                    <!-- Appearance & Schedule Dates -->
                    @if($matter->filing_date || $matter->hearing_date || $matter->trial_date)
                        <div class="pt-1 border-t border-[#f0eee8] space-y-1.5">
                            <span class="text-[#8a8a8a] block text-[11px]">Procedural Schedule</span>
                            <div class="grid grid-cols-3 gap-1.5 text-center">
                                @if($matter->filing_date)
                                    <div class="p-1.5 rounded bg-stone-50 border border-stone-200">
                                        <span class="text-[9px] uppercase tracking-wider text-[#8a8a8a] block font-mono">Filing</span>
                                        <span class="font-mono text-[11px] font-semibold text-[#1a1a1a]">{{ $matter->filing_date->format('d M y') }}</span>
                                    </div>
                                @endif
                                @if($matter->hearing_date)
                                    <div class="p-1.5 rounded bg-blue-50 border border-blue-200">
                                        <span class="text-[9px] uppercase tracking-wider text-blue-700 block font-mono font-medium">Hearing</span>
                                        <span class="font-mono text-[11px] font-semibold text-blue-900">{{ $matter->hearing_date->format('d M y') }}</span>
                                    </div>
                                @endif
                                @if($matter->trial_date)
                                    <div class="p-1.5 rounded bg-purple-50 border border-purple-200">
                                        <span class="text-[9px] uppercase tracking-wider text-purple-700 block font-mono font-medium">Trial</span>
                                        <span class="font-mono text-[11px] font-semibold text-purple-900">{{ $matter->trial_date->format('d M y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Statute & Rule References -->
                    @if($matter->statute_references)
                        <div class="pt-1 border-t border-[#f0eee8]">
                            <span class="text-[#8a8a8a] block text-[11px] mb-1">Statute &amp; Rule References</span>
                            <div class="p-2 rounded bg-[#faf8f5] border border-[#f0eee8] text-[11px] text-[#646864] leading-relaxed">
                                {{ $matter->statute_references }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Client & Trust Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Client Information</h3>
                <div class="flex flex-col gap-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[#23493a]">corporate_fare</span>
                            <a href="{{ $matter->client ? route('clients.show', $matter->client->id) : '#' }}" class="font-semibold text-sm text-[#1a1a1a] hover:text-[#23493a] transition-colors">
                                {{ $matter->client->name ?? 'Unassigned' }}
                            </a>
                        </div>
                        @if($matter->client)
                            <a href="{{ route('clients.show', $matter->client->id) }}" class="text-[11px] text-[#23493a] hover:underline font-medium">Dossier →</a>
                        @endif
                    </div>
                    <div class="text-[#646864]">
                        <span class="text-[#8a8a8a] block text-[11px]">Primary Contact</span>
                        <span class="text-[#1a1a1a]">{{ $matter->client->contact_person ?? 'N/A' }}</span>
                    </div>
                    <div class="text-[#646864]">
                        <span class="text-[#8a8a8a] block text-[11px]">Email &amp; Direct Phone</span>
                        <span class="text-[#1a1a1a]">{{ $matter->client->email ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Conflict-Check Status Summary Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-[#f0eee8]">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a]">Ethics &amp; Conflicts</h3>
                    @php
                        $latestCheck = $matter->conflictChecks->sortByDesc('checked_at')->first();
                    @endphp
                    @if($latestCheck)
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase {{ $latestCheck->status === 'clear' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ $latestCheck->status_label }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-amber-50 text-amber-700 border border-amber-200">
                            Check Pending
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#646864] mb-3">
                    Screen all related parties, opposing counsel, and witnesses against firm conflict records.
                </p>
                <a href="{{ route('conflict-checks.index', ['matter_id' => $matter->id]) }}" class="w-full flex items-center justify-center gap-1.5 py-1.5 px-3 rounded bg-[#f5f3ed] hover:bg-[#eae8e2] border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] transition-colors">
                    <span class="material-symbols-outlined text-[15px] text-[#23493a]">policy</span>
                    <span>Launch Conflict Check</span>
                </a>
            </div>

            <!-- Assigned Legal Team & Roles (F-13) -->
            <div id="team-section" class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-[#f0eee8]">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a]">Litigation Team</h3>
                    @if(in_array(auth()->user()->role, ['superadmin', 'partner']) || auth()->id() === $matter->lead_attorney_id)
                        <button type="button" @click="openAddTeamModal = true" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-sm">person_add</span>
                            <span>Add Counsel</span>
                        </button>
                    @endif
                </div>
                
                <div class="divide-y divide-[#f0eee8]">
                    <!-- Supervising Partner / Senior Advocate (if assigned) -->
                    @if($matter->supervisingAttorney)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($matter->supervisingAttorney->avatar_url)
                                <img alt="{{ $matter->supervisingAttorney->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#e5e3dc] shrink-0" src="{{ $matter->supervisingAttorney->avatar_url }}"/>
                            @else
                                <div class="w-8 h-8 rounded-full bg-[#1b3a2e] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($matter->supervisingAttorney->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $matter->supervisingAttorney->name }}</span>
                                <span class="text-[11px] text-[#646864]">Supervising Partner &middot; Oversight</span>
                            </div>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-amber-50 text-amber-800 border border-amber-200 font-medium shrink-0">
                            Supervising
                        </span>
                    </div>
                    @endif

                    <!-- Lead Attorney -->
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($matter->leadAttorney?->avatar_url)
                                <img alt="{{ $matter->leadAttorney?->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#e5e3dc] shrink-0" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                            @else
                                <div class="w-8 h-8 rounded-full bg-[#23493a] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($matter->leadAttorney?->name ?? 'L', 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $matter->leadAttorney?->name ?? 'Unassigned' }}</span>
                                <span class="text-[11px] text-[#646864]">Lead Trial Counsel &middot; Full Admin</span>
                            </div>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-[#23493a]/10 text-[#23493a] font-medium shrink-0">
                            Lead
                        </span>
                    </div>

                    <!-- Assigned Paralegal / Staff (if assigned) -->
                    @if($matter->assignedParalegal)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($matter->assignedParalegal->avatar_url)
                                <img alt="{{ $matter->assignedParalegal->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#e5e3dc] shrink-0" src="{{ $matter->assignedParalegal->avatar_url }}"/>
                            @else
                                <div class="w-8 h-8 rounded-full bg-[#3b5998] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($matter->assignedParalegal->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $matter->assignedParalegal->name }}</span>
                                <span class="text-[11px] text-[#646864]">Assigned Paralegal / Staff &middot; Filings</span>
                            </div>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-blue-50 text-blue-700 border border-blue-200 font-medium shrink-0">
                            Paralegal
                        </span>
                    </div>
                    @endif

                    <!-- Assigned Team Members -->
                    @forelse($matter->users->where('id', '!=', $matter->lead_attorney_id) as $member)
                    <div class="py-2.5 flex items-center justify-between group">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($member->avatar_url)
                                <img alt="{{ $member->name }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#e5e3dc] shrink-0" src="{{ $member->avatar_url }}"/>
                            @else
                                <div class="w-7 h-7 rounded-full bg-[#5b4382] text-white flex items-center justify-center font-semibold text-[10px] shrink-0 shadow-xs">
                                    {{ strtoupper(substr($member->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-medium text-[#1a1a1a] truncate">{{ $member->name }}</span>
                                <div class="flex items-center gap-1.5 text-[10.5px] text-[#8a8a8a]">
                                    <span class="capitalize">{{ str_replace('_', ' ', $member->pivot->role ?? $member->role) }}</span>
                                    <span>&middot;</span>
                                    <span class="font-mono uppercase">{{ $member->pivot->access_level ?? 'read' }}</span>
                                </div>
                            </div>
                        </div>

                        @if(in_array(auth()->user()->role, ['superadmin', 'partner']) || auth()->id() === $matter->lead_attorney_id)
                        <form method="POST" action="{{ route('matters.team.destroy', ['matter' => $matter, 'user' => $member]) }}" onsubmit="return confirm('Remove {{ $member->name }} from active team?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-[#8a8a8a] hover:text-rose-600 transition-colors opacity-0 group-hover:opacity-100" title="Remove counsel">
                                <span class="material-symbols-outlined text-sm">remove_circle_outline</span>
                            </button>
                        </form>
                        @endif
                    </div>
                    @empty
                    @if($matter->users->count() <= 1 && $matter->lead_attorney_id)
                        <div class="py-2 text-[11.5px] text-[#8a8a8a] italic">
                            No additional associate counsel or paralegals assigned yet.
                        </div>
                    @endif
                    @endforelse
                </div>
            </div>

            <!-- Modal: Add Team Member -->
            <div x-show="openAddTeamModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                <div @click.outside="openAddTeamModal = false" class="bg-white border border-[#e5e3dc] rounded-md max-w-md w-full p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Assign Team Counsel</h3>
                        <button type="button" @click="openAddTeamModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('matters.team.store', $matter) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#1a1a1a] mb-1">Select Counsel / Staff *</label>
                            <select name="user_id" required class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                                <option value="">-- Choose Advocate / Paralegal --</option>
                                @foreach($firmStaff ?? [] as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ ucfirst($staff->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1a1a1a] mb-1">Matter Role *</label>
                            <select name="role" required class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                                <option value="associate">Associate Counsel</option>
                                <option value="lead_attorney">Lead Trial Counsel</option>
                                <option value="paralegal">Paralegal / Legal Assistant</option>
                                <option value="consulting_counsel">Consulting Counsel</option>
                                <option value="of_counsel">Of Counsel</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1a1a1a] mb-1">Access Level *</label>
                            <select name="access_level" required class="w-full h-9 px-3 text-xs rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                                <option value="admin">Full Admin (Edit, Delete, Assign)</option>
                                <option value="write" selected>Standard Read/Write (Draft, Upload, Message)</option>
                                <option value="read">Read Only (Review Filings &amp; Notes)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f0eee8]">
                            <button type="button" @click="openAddTeamModal = false" class="btn-secondary h-9 px-3 text-xs">Cancel</button>
                            <button type="submit" class="btn-primary h-9 px-4 text-xs">Assign Member</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Close Matter Modal -->
<div id="closeMatterModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl border border-[#e5e3dc]" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
            <div class="flex items-center gap-2 text-rose-800">
                <span class="material-symbols-outlined text-xl">lock</span>
                <h3 class="text-sm font-semibold text-[#1a1a1a]">Close Matter Dossier</h3>
            </div>
            <button type="button" onclick="document.getElementById('closeMatterModal').classList.add('hidden')" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form action="{{ route('matters.status.update', $matter->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="closed">

            <p class="text-xs text-[#646864] leading-relaxed">
                Closing matter <strong>{{ $matter->case_number }} ({{ $matter->title }})</strong> will mark this case file as Closed. All historical filings, notes, orders, and privileged communications remain preserved for legal compliance.
            </p>

            <div>
                <label class="block text-xs font-medium text-[#1a1a1a] mb-1">Disposition / Closure Notes (Optional)</label>
                <textarea name="closing_notes" rows="3" placeholder="e.g. Decree finalized; judgment executed; certified copies received..." class="w-full text-xs p-2.5 rounded border border-[#e5e3dc] focus:border-[#23493a] focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                <button type="button" onclick="document.getElementById('closeMatterModal').classList.add('hidden')" class="btn-secondary h-9 px-3.5 text-xs">
                    Cancel
                </button>
                <button type="submit" class="h-9 px-4 rounded bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold inline-flex items-center gap-1.5 transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    <span>Confirm &amp; Close Matter</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Administrative Details Modal (Permitted Matter Metadata Maintenance) -->
<div id="editAdminModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl border border-[#e5e3dc] max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-xl">edit_calendar</span>
                <h3 class="text-sm font-semibold text-[#1a1a1a]">Update Administrative Matter Fields</h3>
            </div>
            <button type="button" onclick="document.getElementById('editAdminModal').classList.add('hidden')" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form action="{{ route('matters.administrative.update', $matter->id) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Case Number</label>
                    <input type="text" name="case_number" value="{{ $matter->case_number }}" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Court Docket Number</label>
                    <input type="text" name="docket_number" value="{{ $matter->docket_number }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                </div>
            </div>

            <div>
                <label class="block font-medium text-[#1a1a1a] mb-1">Matter Title *</label>
                <input type="text" name="title" value="{{ $matter->title }}" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Court Name</label>
                    <input type="text" name="court_name" value="{{ $matter->court_name }}" placeholder="e.g. High Court of Delhi" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Court Type / Forum</label>
                    <input type="text" name="court_type" value="{{ $matter->court_type }}" placeholder="e.g. District / Appellate / NCLT" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Presiding Judge</label>
                    <input type="text" name="judge_name" value="{{ $matter->judge_name }}" placeholder="e.g. Hon. Justice R. Sharma" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Jurisdiction / Bench</label>
                    <input type="text" name="jurisdiction" value="{{ $matter->jurisdiction }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">County / District</label>
                    <input type="text" name="county" value="{{ $matter->county }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">State</label>
                    <input type="text" name="state" value="{{ $matter->state }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Matter Stage *</label>
                    <select name="stage" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        @foreach(['Intake', 'Pleadings', 'Discovery', 'Pre-Trial', 'Trial', 'Judgment', 'Appellate', 'Execution'] as $stg)
                            <option value="{{ $stg }}" {{ (strtolower($matter->stage ?? '') === strtolower($stg)) ? 'selected' : '' }}>{{ $stg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Priority</label>
                    <select name="priority" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="low" {{ $matter->priority === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ in_array($matter->priority, ['medium', 'normal', '']) ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $matter->priority === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ $matter->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Filing Date</label>
                    <input type="date" name="filing_date" value="{{ $matter->filing_date ? \Carbon\Carbon::parse($matter->filing_date)->toDateString() : '' }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Next Hearing Date</label>
                    <input type="date" name="hearing_date" value="{{ $matter->hearing_date ? \Carbon\Carbon::parse($matter->hearing_date)->toDateString() : '' }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                </div>
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Trial / Final Date</label>
                    <input type="date" name="trial_date" value="{{ $matter->trial_date ? \Carbon\Carbon::parse($matter->trial_date)->toDateString() : '' }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                </div>
            </div>

            <div>
                <label class="block font-medium text-[#1a1a1a] mb-1">Statutory Provisions &amp; Act References</label>
                <input type="text" name="statute_references" value="{{ $matter->statute_references }}" placeholder="e.g. Section 138 NI Act, Order XXXVII CPC" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
            </div>

            <div>
                <label class="block font-medium text-[#1a1a1a] mb-1">Procedural Notes / Description</label>
                <textarea name="description" rows="2" class="w-full p-2.5 rounded border border-[#e5e3dc]">{{ $matter->description }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                <button type="button" onclick="document.getElementById('editAdminModal').classList.add('hidden')" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Save Administrative Updates</button>
            </div>
        </form>
    </div>
</div>
@endsection
