@extends('layouts.app')

@section('title', (auth()->user()->isParalegal() ? 'Paralegal Practice Hub' : 'Staff Operations Hub') . ' — ' . (auth()->user()->firm->name ?? 'Sharma Legal Chambers'))
@section('header_title', auth()->user()->isParalegal() ? 'Paralegal Operations Hub' : 'Legal Assistant & Staff Hub')

@section('content')
<div x-data="{
    activeTab: 'matters',
    editAdminModal: false,
    uploadDocModal: false,
    docRequestModal: false,
    scheduleEventModal: false,
    createTaskModal: false,
    addNoteModal: false,

    // Admin matter edit state
    editMatter: {
        id: '',
        case_number: '',
        docket_number: '',
        title: '',
        short_title: '',
        court_name: '',
        court_type: '',
        jurisdiction: '',
        county: '',
        state: '',
        judge_name: '',
        stage: 'Pleadings',
        priority: 'medium',
        filing_date: '',
        hearing_date: '',
        trial_date: '',
        statute_references: '',
        description: ''
    },

    openAdminEdit(m) {
        this.editMatter = {
            id: m.id,
            case_number: m.case_number || '',
            docket_number: m.docket_number || '',
            title: m.title || '',
            short_title: m.short_title || '',
            court_name: m.court_name || '',
            court_type: m.court_type || '',
            jurisdiction: m.jurisdiction || '',
            county: m.county || '',
            state: m.state || '',
            judge_name: m.judge_name || '',
            stage: m.stage || 'Pleadings',
            priority: m.priority || 'medium',
            filing_date: m.filing_date || '',
            hearing_date: m.hearing_date || '',
            trial_date: m.trial_date || '',
            statute_references: m.statute_references || '',
            description: m.description || ''
        };
        this.editAdminModal = true;
    }
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Editorial Header Banner -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#23493a]/10 text-[#23493a] border border-[#23493a]/20 uppercase tracking-wider">
                    {{ auth()->user()->isParalegal() ? 'Paralegal Practice Workspace' : 'Legal Assistant & Staff Workspace' }}
                </span>
                <span class="text-xs text-[#8a8a8a]">&middot;</span>
                <span class="text-xs text-[#646864] font-medium">{{ auth()->user()->name }}</span>
            </div>
            <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight mt-1">
                Assigned Matters &amp; Case Operations
            </h1>
            <p class="text-xs text-[#646864] mt-1 max-w-3xl leading-relaxed">
                Track assigned case dossiers, maintain procedural metadata and court schedules, coordinate client document submissions, monitor statutory deadlines, and execute task workflows.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="uploadDocModal = true" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-[17px] text-[#23493a]">upload_file</span>
                <span>Upload Document</span>
            </button>
            <button type="button" @click="docRequestModal = true" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-[17px] text-[#23493a]">fact_check</span>
                <span>Request Document</span>
            </button>
            <button type="button" @click="createTaskModal = true" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-[17px] text-[#23493a]">add_task</span>
                <span>Create Task</span>
            </button>
            <button type="button" @click="scheduleEventModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-[17px]">event</span>
                <span>Schedule Event</span>
            </button>
        </div>
    </div>

    <!-- 5-Stat Operations Ribbon -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-6">
        <!-- Assigned Matters -->
        <div class="p-4 sm:p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors cursor-pointer" @click="activeTab = 'matters'">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#646864] font-medium">Assigned Matters</span>
                <span class="material-symbols-outlined text-base text-[#23493a]">folder_shared</span>
            </div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $metrics['assigned_matters'] }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Active cases in your roster</div>
        </div>

        <!-- Open & Overdue Tasks -->
        <div class="p-4 sm:p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors cursor-pointer" @click="activeTab = 'tasks'">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#646864] font-medium">Open Tasks</span>
                @if($metrics['overdue_tasks'] > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                        {{ $metrics['overdue_tasks'] }} Overdue
                    </span>
                @else
                    <span class="material-symbols-outlined text-base text-emerald-700">checklist</span>
                @endif
            </div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $metrics['open_tasks'] }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">
                {{ $metrics['overdue_tasks'] > 0 ? $metrics['overdue_tasks'] . ' require urgent attention' : 'All scheduled on track' }}
            </div>
        </div>

        <!-- Pending Client Documents -->
        <div class="p-4 sm:p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors cursor-pointer" @click="activeTab = 'docs'">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#646864] font-medium">Pending Client Docs</span>
                @if($metrics['pending_docs'] > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $metrics['pending_docs'] }} Pending
                    </span>
                @else
                    <span class="material-symbols-outlined text-base text-emerald-700">verified</span>
                @endif
            </div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $metrics['pending_docs'] }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Awaiting submission / review</div>
        </div>

        <!-- Upcoming Deadlines & Hearings -->
        <div class="p-4 sm:p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors cursor-pointer" @click="activeTab = 'hearings'">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#646864] font-medium">Court Hearings &amp; Deadlines</span>
                <span class="material-symbols-outlined text-base text-purple-700">gavel</span>
            </div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $metrics['upcoming_hearings'] }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Docket events on assigned cases</div>
        </div>

        <!-- Unread Client Messages -->
        <div class="p-4 sm:p-5 flex flex-col justify-between hover:bg-[#faf9f5] transition-colors cursor-pointer" @click="activeTab = 'recent'">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#646864] font-medium">Unread Messages</span>
                @if($metrics['unread_messages'] > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                        {{ $metrics['unread_messages'] }} New
                    </span>
                @else
                    <span class="material-symbols-outlined text-base text-[#8a8a8a]">forum</span>
                @endif
            </div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $metrics['unread_messages'] }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Client &amp; attorney correspondence</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-[#e5e3dc] flex items-center gap-1 sm:gap-2 mb-6 overflow-x-auto text-xs font-medium pb-2">
        <button type="button" @click="activeTab = 'matters'"
                :class="activeTab === 'matters' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">folder_open</span>
            <span>Assigned Matters ({{ $assignedMatters->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'tasks'"
                :class="activeTab === 'tasks' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">checklist</span>
            <span>Open &amp; Overdue Tasks ({{ $openTasks->count() }})</span>
            @if($overdueTasks->count() > 0)
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            @endif
        </button>

        <button type="button" @click="activeTab = 'docs'"
                :class="activeTab === 'docs' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">fact_check</span>
            <span>Pending Client Documents ({{ $pendingClientDocs->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'hearings'"
                :class="activeTab === 'hearings' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
            <span>Upcoming Deadlines &amp; Hearings ({{ $upcomingHearingsAndDeadlines->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'recent'"
                :class="activeTab === 'recent' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">history_edu</span>
            <span>Recent Documents &amp; Messages</span>
        </button>

        <button type="button" @click="activeTab = 'notes_todos'"
                :class="activeTab === 'notes_todos' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-[#faf8f5]'"
                class="px-3.5 py-1.5 rounded-md transition-all flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">edit_note</span>
            <span>Case Notes &amp; Personal Todos</span>
        </button>
    </div>

    <!-- TAB 1: ASSIGNED MATTERS DASHBOARD -->
    <div x-show="activeTab === 'matters'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-[#1a1a1a]">Your Assigned Case Dossiers</h2>
            <span class="text-xs text-[#8a8a8a]">{{ $assignedMatters->count() }} total matters assigned</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($assignedMatters as $matter)
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs hover:border-[#23493a]/40 transition-all flex flex-col justify-between">
                    <div>
                        <!-- Header with Case Number & Status -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="font-mono text-[11px] text-[#23493a] font-semibold bg-[#23493a]/5 px-2 py-0.5 rounded border border-[#23493a]/15">
                                    {{ $matter->case_number }}
                                </span>
                                @if($matter->docket_number)
                                    <span class="text-[10px] text-[#8a8a8a] font-mono block mt-1">Docket: {{ $matter->docket_number }}</span>
                                @endif
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ strtolower($matter->status) === 'active' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-700' }}">
                                {{ $matter->status }}
                            </span>
                        </div>

                        <!-- Matter Title -->
                        <h3 class="text-sm font-semibold text-[#1a1a1a] line-clamp-2 mb-2">
                            <a href="{{ route('matters.show', $matter->id) }}" class="hover:text-[#23493a] transition-colors">
                                {{ $matter->title }}
                            </a>
                        </h3>

                        <!-- Meta details -->
                        <div class="space-y-1.5 text-xs text-[#646864] pt-2 border-t border-[#f0eee8]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#8a8a8a]">Client:</span>
                                <span class="font-medium text-[#1a1a1a] truncate max-w-[170px]">{{ $matter->client->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#8a8a8a]">Court:</span>
                                <span class="font-medium text-[#1a1a1a] truncate max-w-[170px]">{{ $matter->court_name ?: 'Tribunal / Chambers Desk' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#8a8a8a]">Stage / Priority:</span>
                                <span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a]">{{ $matter->stage ?: 'Pre-Trial' }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] capitalize {{ strtolower($matter->priority) === 'urgent' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-gray-100 text-gray-700' }}">{{ $matter->priority ?: 'Normal' }}</span>
                                </span>
                            </div>
                            @if($matter->events->isNotEmpty())
                                <div class="flex items-center justify-between text-purple-800 bg-purple-50/70 px-2 py-1 rounded">
                                    <span class="font-medium text-[11px]">Next Hearing:</span>
                                    <span class="font-mono text-[11px] font-semibold">{{ \Carbon\Carbon::parse($matter->events->first()->start_time)->format('M d, g:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-4 border-t border-[#f0eee8] mt-4 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 text-[11px] text-[#8a8a8a]">
                            <span title="Open Tasks" class="inline-flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-[13px]">checklist</span>
                                <span>{{ $matter->open_tasks_count }}</span>
                            </span>
                            <span>&middot;</span>
                            <span title="Documents" class="inline-flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-[13px]">description</span>
                                <span>{{ $matter->documents_count }}</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <!-- Update Administrative Metadata Button -->
                            <button type="button" @click="openAdminEdit({{ json_encode($matter) }})" class="p-1.5 rounded hover:bg-[#faf8f5] text-[#23493a] border border-[#e5e3dc] text-xs inline-flex items-center gap-1 cursor-pointer" title="Maintain Administrative Matter Fields">
                                <span class="material-symbols-outlined text-[15px]">edit_calendar</span>
                                <span class="text-[11px] font-medium hidden sm:inline">Admin Fields</span>
                            </button>

                            <a href="{{ route('matters.show', $matter->id) }}" class="p-1.5 rounded bg-[#23493a] text-white hover:bg-[#1a382c] text-xs inline-flex items-center gap-1" title="View Dossier">
                                <span class="text-[11px] font-medium">Dossier</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full border border-[#e5e3dc] bg-white rounded-md p-12 text-center text-xs text-[#8a8a8a]">
                    <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2 block">folder_off</span>
                    No active matters currently assigned to your roster. Cases will appear here as you are added to the legal team.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: OPEN & OVERDUE TASKS -->
    <div x-show="activeTab === 'tasks'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-[#1a1a1a]">Assigned Matter Tasks &amp; Deadlines</h2>
            <button type="button" @click="createTaskModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1 shadow-xs">
                <span class="material-symbols-outlined text-[15px]">add</span>
                <span>Create Task</span>
            </button>
        </div>

        @if($overdueTasks->isNotEmpty())
            <div class="border border-rose-200 bg-rose-50/60 rounded-md p-4 mb-4">
                <div class="flex items-center gap-2 text-rose-800 font-semibold text-xs mb-2">
                    <span class="material-symbols-outlined text-base">warning</span>
                    <span>Overdue Action Items ({{ $overdueTasks->count() }})</span>
                </div>
                <div class="divide-y divide-rose-200/60">
                    @foreach($overdueTasks as $task)
                        <div class="py-2.5 flex items-center justify-between gap-4 text-xs">
                            <div>
                                <span class="font-medium text-[#1a1a1a]">{{ $task->title }}</span>
                                <span class="text-rose-700 font-mono text-[11px] ml-2">Overdue since {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                                @if($task->matter)
                                    <span class="text-[#8a8a8a] text-[11px] block mt-0.5">Matter: {{ $task->matter->case_number }} &middot; {{ $task->matter->title }}</span>
                                @endif
                            </div>
                            <form action="{{ route('tasks.status.update', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed"/>
                                <button type="submit" class="px-2.5 py-1 rounded bg-rose-700 hover:bg-rose-800 text-white text-[11px] font-semibold cursor-pointer">
                                    Mark Done
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="border border-[#e5e3dc] bg-white rounded-md divide-y divide-[#f0eee8] shadow-xs">
            @forelse($openTasks as $task)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-[#faf9f5] transition-colors text-xs">
                    <div class="flex items-start gap-3 min-w-0">
                        <form action="{{ route('tasks.status.update', $task->id) }}" method="POST" class="mt-0.5">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed"/>
                            <button type="submit" class="w-4 h-4 rounded border border-[#8a8a8a] hover:border-[#23493a] flex items-center justify-center cursor-pointer transition-colors" title="Complete task">
                            </button>
                        </form>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-[#1a1a1a]">{{ $task->title }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] capitalize font-medium
                                    {{ strtolower($task->priority) === 'urgent' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (strtolower($task->priority) === 'high' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $task->priority }}
                                </span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-[#faf8f5] text-[#646864] border border-[#e5e3dc]">
                                    {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-[#8a8a8a] mt-1 flex-wrap">
                                @if($task->matter)
                                    <a href="{{ route('matters.show', $task->matter_id) }}" class="text-[#23493a] hover:underline font-mono">
                                        {{ $task->matter->case_number }}
                                    </a>
                                    <span class="truncate max-w-[200px]">{{ $task->matter->title }}</span>
                                    <span>&middot;</span>
                                @endif
                                <span>Due: <strong class="text-[#1a1a1a] font-mono">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No deadline' }}</strong></span>
                                <span>&middot;</span>
                                <span>Assignee: {{ $task->assignee->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('tasks.index') }}" class="text-[#23493a] hover:underline text-xs shrink-0 font-medium">
                        Task details &rarr;
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a]">
                    No open tasks found on your assigned matters. Click "Create Task" to assign work items.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 3: PENDING CLIENT DOCUMENTS -->
    <div x-show="activeTab === 'docs'" class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-[#1a1a1a]">Client Document Requests &amp; Submissions</h2>
                <p class="text-xs text-[#8a8a8a]">Evidence, KYC proofs, medical records, and contracts requested from clients</p>
            </div>
            <button type="button" @click="docRequestModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1 shadow-xs">
                <span class="material-symbols-outlined text-[15px]">add</span>
                <span>Request Document</span>
            </button>
        </div>

        <div class="border border-[#e5e3dc] bg-white rounded-md divide-y divide-[#f0eee8] shadow-xs">
            @forelse($pendingClientDocs as $docReq)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-[#faf9f5] transition-colors text-xs">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-semibold text-[#1a1a1a]">{{ $docReq->title }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                {{ $docReq->status === 'submitted' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($docReq->status === 'under_review' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                {{ $docReq->status === 'submitted' ? 'Submitted by Client' : ucwords(str_replace('_', ' ', $docReq->status)) }}
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-stone-100 text-stone-600 font-mono">
                                {{ $docReq->category ?: 'General Document' }}
                            </span>
                        </div>
                        <p class="text-xs text-[#646864] mt-1">{{ $docReq->description ?: 'Client document request dispatched via client portal.' }}</p>
                        <div class="flex items-center gap-3 text-[11px] text-[#8a8a8a] mt-1.5 flex-wrap">
                            <span>Client: <strong class="text-[#1a1a1a]">{{ $docReq->client->name ?? 'Unassigned' }}</strong></span>
                            <span>&middot;</span>
                            <span>Case: <a href="{{ route('matters.show', $docReq->matter_id) }}" class="text-[#23493a] font-mono hover:underline">{{ $docReq->matter->case_number ?? 'Dossier' }}</a></span>
                            <span>&middot;</span>
                            <span>Due Date: <strong class="text-[#1a1a1a] font-mono">{{ $docReq->due_date ? \Carbon\Carbon::parse($docReq->due_date)->format('M d, Y') : 'Standard' }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($docReq->document)
                            <a href="{{ route('documents.download', $docReq->document->id) }}" class="btn-secondary h-8 px-2.5 text-xs inline-flex items-center gap-1 text-[#23493a]">
                                <span class="material-symbols-outlined text-[15px]">download</span>
                                <span>Download</span>
                            </a>
                        @endif
                        <a href="{{ route('document-requests.index', ['status' => $docReq->status]) }}" class="btn-secondary h-8 px-3 text-xs inline-flex items-center gap-1">
                            <span>Review &amp; Manage</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a]">
                    No pending document requests on your assigned matters. Use "Request Document" to request files from clients.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: UPCOMING DEADLINES & HEARINGS -->
    <div x-show="activeTab === 'hearings'" class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-[#1a1a1a]">Statutory Deadlines &amp; Court Hearings</h2>
                <p class="text-xs text-[#8a8a8a]">Court docket schedule and statutory milestone deadlines on assigned cases</p>
            </div>
            <button type="button" @click="scheduleEventModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1 shadow-xs">
                <span class="material-symbols-outlined text-[15px]">event</span>
                <span>Schedule Event</span>
            </button>
        </div>

        <div class="border border-[#e5e3dc] bg-white rounded-md divide-y divide-[#f0eee8] shadow-xs">
            @forelse($upcomingHearingsAndDeadlines as $evt)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-[#faf9f5] transition-colors text-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded bg-purple-50 border border-purple-200 text-purple-900 flex flex-col items-center justify-center shrink-0">
                            <span class="text-[9px] font-semibold uppercase tracking-wider">{{ \Carbon\Carbon::parse($evt->start_time)->format('M') }}</span>
                            <span class="text-base font-bold leading-tight">{{ \Carbon\Carbon::parse($evt->start_time)->format('d') }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-semibold text-[#1a1a1a]">{{ $evt->title }}</h4>
                                <span class="px-1.5 py-0.2 rounded text-[10px] uppercase font-mono
                                    {{ $evt->is_statutory_deadline ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $evt->is_statutory_deadline ? 'Statutory Deadline' : ucwords(str_replace('_', ' ', $evt->event_type ?: 'Hearing')) }}
                                </span>
                            </div>
                            <div class="text-[11px] text-[#8a8a8a] mt-1 flex items-center gap-2 flex-wrap">
                                <span>Time: <strong class="text-[#1a1a1a] font-mono">{{ \Carbon\Carbon::parse($evt->start_time)->format('h:i A') }}</strong></span>
                                <span>&middot;</span>
                                @if($evt->matter)
                                    <span>Matter: <a href="{{ route('matters.show', $evt->matter_id) }}" class="text-[#23493a] font-mono hover:underline">{{ $evt->matter->case_number }} ({{ $evt->matter->title }})</a></span>
                                @endif
                                @if($evt->location)
                                    <span>&middot;</span>
                                    <span>Location: {{ $evt->location }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('calendar.index') }}" class="text-[#23493a] hover:underline text-xs shrink-0 font-medium">
                        Calendar Docket &rarr;
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a]">
                    No upcoming deadlines or court hearings scheduled for your assigned matters.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 5: RECENT DOCUMENTS & CLIENT COMMUNICATIONS -->
    <div x-show="activeTab === 'recent'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Documents -->
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">description</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Recent Case Documents</h3>
                </div>
                <button type="button" @click="uploadDocModal = true" class="text-xs text-[#23493a] hover:underline font-medium">
                    + Upload New
                </button>
            </div>

            <div class="divide-y divide-[#f0eee8]">
                @forelse($recentDocuments as $doc)
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <span class="font-medium text-[#1a1a1a] block truncate">{{ $doc->title }}</span>
                            <span class="text-[11px] text-[#8a8a8a] font-mono">
                                {{ $doc->matter ? $doc->matter->case_number . ' · ' : '' }}
                                By {{ $doc->uploader->name ?? 'Staff' }} &middot; {{ $doc->created_at->format('M d') }}
                            </span>
                        </div>
                        <a href="{{ route('documents.download', $doc->id) }}" class="p-1 rounded text-[#23493a] hover:bg-[#faf8f5]" title="Download document">
                            <span class="material-symbols-outlined text-[17px]">download</span>
                        </a>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">No recent documents uploaded.</div>
                @endforelse
            </div>
        </div>

        <!-- Unread Client Messages & Permitted Communications -->
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">forum</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Client Communication &amp; Messages</h3>
                </div>
                <a href="{{ route('messages.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">
                    View Inbox &rarr;
                </a>
            </div>

            <div class="divide-y divide-[#f0eee8]">
                @forelse($unreadMessages as $msg)
                    <div class="py-2.5 flex items-start justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-[#1a1a1a]">{{ $msg->sender->name ?? 'Client' }}</span>
                                @if($msg->matter)
                                    <span class="font-mono text-[10px] text-[#23493a]">{{ $msg->matter->case_number }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-[#646864] line-clamp-1 mt-0.5">{{ $msg->body }}</p>
                            <span class="text-[10.5px] text-[#8a8a8a] font-mono">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('messages.index') }}" class="px-2 py-1 rounded bg-[#23493a] text-white text-[11px] shrink-0 font-medium">
                            Reply
                        </a>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">No unread messages for your assigned matters.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 6: SAFE CASE NOTES & PERSONAL TODOS -->
    <div x-show="activeTab === 'notes_todos'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Safe Case Notes (Privileged Notes Shielded) -->
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">edit_note</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Case Notes &amp; Practice Memos</h3>
                </div>
                <button type="button" @click="addNoteModal = true" class="text-xs text-[#23493a] hover:underline font-medium">
                    + Add Note
                </button>
            </div>

            <div class="divide-y divide-[#f0eee8]">
                @forelse($recentNotes as $note)
                    <div class="py-3 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-[#1a1a1a]">{{ $note->title }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $note->type === 'client_visible' ? 'bg-blue-50 text-blue-700' : 'bg-stone-100 text-stone-700' }}">
                                {{ $note->type === 'client_visible' ? 'Client Visible' : 'Internal Memo' }}
                            </span>
                        </div>
                        <p class="text-xs text-[#646864] line-clamp-2 mt-1 leading-relaxed">{{ $note->body }}</p>
                        <div class="flex items-center gap-2 text-[10.5px] text-[#8a8a8a] mt-1 font-mono">
                            @if($note->matter)
                                <span>{{ $note->matter->case_number }}</span>
                                <span>&middot;</span>
                            @endif
                            <span>{{ $note->user->name ?? 'Staff' }}</span>
                            <span>&middot;</span>
                            <span>{{ $note->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">No internal notes recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Personal Todos -->
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">task_alt</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a]">Personal Todos &amp; Checklist</h3>
                </div>
                <a href="{{ route('todos.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">
                    All Todos &rarr;
                </a>
            </div>

            <!-- Quick Add Todo Form -->
            <form action="{{ route('todos.store') }}" method="POST" class="flex gap-2 mb-3">
                @csrf
                <input type="text" name="title" required placeholder="Quick todo (e.g. Call court clerk regarding certified copies)..."
                       class="flex-1 h-8 px-2.5 rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                <button type="submit" class="btn-primary h-8 px-3 text-xs">Add</button>
            </form>

            <div class="divide-y divide-[#f0eee8]">
                @forelse($todos as $todo)
                    <div class="py-2 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <form action="{{ route('todos.toggle', $todo->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-4 h-4 rounded border flex items-center justify-center cursor-pointer {{ $todo->is_completed ? 'bg-[#23493a] border-[#23493a] text-white' : 'border-[#8a8a8a]' }}">
                                    @if($todo->is_completed)
                                        <span class="material-symbols-outlined text-[12px]">check</span>
                                    @endif
                                </button>
                            </form>
                            <span class="{{ $todo->is_completed ? 'line-through text-[#8a8a8a]' : 'text-[#1a1a1a]' }} truncate">
                                {{ $todo->title }}
                            </span>
                        </div>
                        @if($todo->due_date)
                            <span class="text-[10.5px] font-mono text-[#8a8a8a] shrink-0">{{ \Carbon\Carbon::parse($todo->due_date)->format('M d') }}</span>
                        @endif
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">No personal todos added yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MODAL 1: EDIT ADMINISTRATIVE MATTER DETAILS (Permitted Metadata Maintenance) -->
    <div x-show="editAdminModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl border border-[#e5e3dc] max-h-[90vh] overflow-y-auto" @click.away="editAdminModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">edit_calendar</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Maintain Permitted Matter Metadata</h3>
                </div>
                <button type="button" @click="editAdminModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form :action="'{{ url('/matters') }}/' + editMatter.id + '/administrative'" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Case Number</label>
                        <input type="text" name="case_number" x-model="editMatter.case_number" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Court Docket Number</label>
                        <input type="text" name="docket_number" x-model="editMatter.docket_number" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Matter Title *</label>
                    <input type="text" name="title" x-model="editMatter.title" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Court Name</label>
                        <input type="text" name="court_name" x-model="editMatter.court_name" placeholder="e.g. High Court of Delhi" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Court Type / Forum</label>
                        <input type="text" name="court_type" x-model="editMatter.court_type" placeholder="e.g. District / Appellate / NCLT" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Presiding Judge</label>
                        <input type="text" name="judge_name" x-model="editMatter.judge_name" placeholder="e.g. Hon. Justice R. Sharma" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Jurisdiction / Bench</label>
                        <input type="text" name="jurisdiction" x-model="editMatter.jurisdiction" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">County / District</label>
                        <input type="text" name="county" x-model="editMatter.county" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">State</label>
                        <input type="text" name="state" x-model="editMatter.state" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Matter Stage *</label>
                        <select name="stage" x-model="editMatter.stage" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                            <option value="Intake">Intake</option>
                            <option value="Pleadings">Pleadings</option>
                            <option value="Discovery">Discovery / Evidence</option>
                            <option value="Pre-Trial">Pre-Trial Arguments</option>
                            <option value="Trial">Trial / Final Hearing</option>
                            <option value="Judgment">Reserved for Judgment</option>
                            <option value="Appellate">Appellate Review</option>
                            <option value="Execution">Execution / Decree</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Priority</label>
                        <select name="priority" x-model="editMatter.priority" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Filing Date</label>
                        <input type="date" name="filing_date" x-model="editMatter.filing_date" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Next Hearing Date</label>
                        <input type="date" name="hearing_date" x-model="editMatter.hearing_date" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Trial / Final Date</label>
                        <input type="date" name="trial_date" x-model="editMatter.trial_date" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Statutory Provisions &amp; Act References</label>
                    <input type="text" name="statute_references" x-model="editMatter.statute_references" placeholder="e.g. Section 138 NI Act, Order XXXVII CPC" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Procedural Notes / Description</label>
                    <textarea name="description" x-model="editMatter.description" rows="2" class="w-full p-2.5 rounded border border-[#e5e3dc]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="editAdminModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Save Metadata Updates</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: UPLOAD & ORGANIZE DOCUMENT -->
    <div x-show="uploadDocModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-[#e5e3dc]" @click.away="uploadDocModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">upload_file</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Upload &amp; Organize Case Document</h3>
                </div>
                <button type="button" @click="uploadDocModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Target Matter *</label>
                    <select name="matter_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select assigned matter...</option>
                        @foreach($assignedMatters as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Affidavit of Service, Medical Bill Receipts" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Category / Folder</label>
                        <select name="category" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                            <option value="Pleadings">Pleadings</option>
                            <option value="Evidence">Evidence &amp; Exhibits</option>
                            <option value="Court Orders">Court Orders</option>
                            <option value="Correspondence">Correspondence</option>
                            <option value="KYC & Verification">KYC &amp; Verification</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Document Type</label>
                        <input type="text" name="document_type" placeholder="e.g. Motion, Brief, Proof" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Attach File (PDF, DOCX, Images) *</label>
                    <input type="file" name="file" required class="w-full text-xs p-1.5 border border-[#e5e3dc] rounded bg-[#faf8f5]"/>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_client_visible" value="1" id="staff_client_vis" class="rounded text-[#23493a]"/>
                    <label for="staff_client_vis" class="text-[#646864]">Make visible to client on portal</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="uploadDocModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Upload Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CREATE DOCUMENT REQUEST -->
    <div x-show="docRequestModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-[#e5e3dc]" @click.away="docRequestModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">fact_check</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Request Document from Client</h3>
                </div>
                <button type="button" @click="docRequestModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form action="{{ route('document-requests.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Target Matter *</label>
                    <select name="matter_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select matter...</option>
                        @foreach($assignedMatters as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Client *</label>
                    <select name="client_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select client...</option>
                        @foreach($assignedMatters->pluck('client')->filter()->unique('id') as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email ?: 'No email' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Requested Document Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Original Title Deed, Certified Bank Statements" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Category</label>
                        <input type="text" name="category" value="Evidence & Exhibits" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(7)->toDateString() }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Instructions for Client</label>
                    <textarea name="description" rows="2" placeholder="Clear guidelines for the client regarding document format, signatures, or notarization..." class="w-full p-2.5 rounded border border-[#e5e3dc]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="docRequestModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Dispatch Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: SCHEDULE EVENT / DOCKET -->
    <div x-show="scheduleEventModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-[#e5e3dc]" @click.away="scheduleEventModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">event</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Schedule Court Hearing or Docket Event</h3>
                </div>
                <button type="button" @click="scheduleEventModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form action="{{ route('calendar.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Associated Matter *</label>
                    <select name="matter_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select matter...</option>
                        @foreach($assignedMatters as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Event Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Motion for Injunction Hearing, Case Management Conference" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Event Type *</label>
                        <select name="event_type" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                            <option value="hearing">Court Hearing</option>
                            <option value="trial">Trial Date</option>
                            <option value="statutory_deadline">Statutory Limitation / Filing Deadline</option>
                            <option value="conference">Case Conference</option>
                            <option value="meeting">Client Consultation</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Start Time *</label>
                        <input type="datetime-local" name="start_time" required value="{{ now()->addDays(2)->format('Y-m-d\T10:00') }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Location / Courtroom</label>
                        <input type="text" name="location" placeholder="Courtroom 4, High Court" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                    </div>
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" name="is_statutory_deadline" value="1" id="stat_dead" class="rounded text-rose-700"/>
                        <label for="stat_dead" class="text-rose-800 font-medium">Critical Statutory Deadline</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="scheduleEventModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Save to Docket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: CREATE TASK -->
    <div x-show="createTaskModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-[#e5e3dc]" @click.away="createTaskModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">add_task</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Create Case Task</h3>
                </div>
                <button type="button" @click="createTaskModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Matter *</label>
                    <select name="matter_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select matter...</option>
                        @foreach($assignedMatters as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Task Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Draft index of documents, serve notice on opposite counsel" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Priority</label>
                        <select name="priority" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-[#1a1a1a] mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(3)->toDateString() }}" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] font-mono"/>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Task Description</label>
                    <textarea name="description" rows="2" class="w-full p-2.5 rounded border border-[#e5e3dc]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="createTaskModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: ADD INTERNAL NOTE -->
    <div x-show="addNoteModal" x-cloak class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-[#e5e3dc]" @click.away="addNoteModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-xl">edit_note</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Record Internal Case Memo</h3>
                </div>
                <button type="button" @click="addNoteModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form action="{{ route('notes.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Matter *</label>
                    <select name="matter_id" required class="w-full h-8 px-2.5 rounded border border-[#e5e3dc] bg-white">
                        <option value="">Select matter...</option>
                        @foreach($assignedMatters as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Subject / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Registry defect clearance update, service status note" class="w-full h-8 px-2.5 rounded border border-[#e5e3dc]"/>
                </div>

                <div>
                    <label class="block font-medium text-[#1a1a1a] mb-1">Note Body *</label>
                    <textarea name="body" required rows="4" class="w-full p-2.5 rounded border border-[#e5e3dc]"></textarea>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-[#646864]">
                        <input type="radio" name="type" value="internal" checked class="text-[#23493a]"/>
                        <span>Internal Memo</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-[#646864]">
                        <input type="radio" name="type" value="client_visible" class="text-[#23493a]"/>
                        <span>Client Visible</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-[#646864]">
                        <input type="checkbox" name="is_pinned" value="1" class="rounded text-[#23493a]"/>
                        <span>Pin to Top</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="addNoteModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs font-semibold">Save Note</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
