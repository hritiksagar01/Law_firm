@extends('layouts.app')

@section('title', 'Litigation Tasks & Deadlines — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Task Management')

@section('content')
<div x-data="{ 
    openCreateModal: false, 
    editTaskModal: false,
    editingTask: null,
    openTaskComments: null,
    editTask(task) {
        this.editingTask = task;
        this.editTaskModal = true;
    }
}" class="flex flex-col w-full text-[#1a1a1a] gap-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                    Litigation Tasks &amp; Deadlines
                </h1>
                @if($overdueCount > 0)
                    <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-mono font-semibold border border-rose-200">
                        {{ $overdueCount }} Overdue
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Procedural steps, statutory deadlines, advocate assignments, and matter milestones.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="openCreateModal = true" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-2 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-[18px]">add_task</span>
                <span>Log Litigation Task</span>
            </button>
        </div>
    </div>

    <!-- 6-Metric Ribbon -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs overflow-hidden">
        <a href="{{ route('tasks.index', ['status' => 'all']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-[#646864]">Total Tasks</div>
            <div class="text-[24px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $totalCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">All docket items</div>
        </a>
        <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-blue-700">In Progress</div>
            <div class="text-[24px] font-semibold text-blue-800 tracking-tight mt-1">{{ $inProgressCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Active assignments</div>
        </a>
        <a href="{{ route('tasks.index', ['status' => 'waiting']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-amber-700">Waiting / Blocked</div>
            <div class="text-[24px] font-semibold text-amber-800 tracking-tight mt-1">{{ $waitingBlockedCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">External dependencies</div>
        </a>
        <a href="{{ route('tasks.index', ['priority' => 'urgent']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-red-700">Urgent / Critical</div>
            <div class="text-[24px] font-semibold text-red-700 tracking-tight mt-1">{{ $urgentCriticalCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Statutory priority</div>
        </a>
        <a href="{{ route('tasks.index', ['status' => 'all']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-rose-700">Overdue Cutoffs</div>
            <div class="text-[24px] font-semibold text-rose-800 tracking-tight mt-1">{{ $overdueCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Action immediately</div>
        </a>
        <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="p-4 hover:bg-[#faf9f5] transition-colors block">
            <div class="text-[11px] font-mono uppercase tracking-wider text-[#065f46]">Completed</div>
            <div class="text-[24px] font-semibold text-[#065f46] tracking-tight mt-1">{{ $completedCount }}</div>
            <div class="text-[11px] text-[#8a8a8a] mt-0.5">Settled obligations</div>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border border-[#e5e3dc] rounded-md p-3.5 shadow-xs flex flex-col gap-3">
        <!-- Top Row: Search and Dropdowns -->
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5">
            <div class="lg:col-span-4 relative">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[#8a8a8a] text-[18px]">search</span>
                <input type="text" name="q" value="{{ $search }}" placeholder="Search Task ID, title, related party, matter..."
                       class="w-full pl-8 pr-3 h-9 rounded-md bg-[#faf9f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:bg-white focus:border-[#23493a] outline-none transition-colors"/>
            </div>

            <div class="lg:col-span-3">
                <select name="matter_id" onchange="this.form.submit()"
                        class="w-full h-9 px-2.5 rounded-md bg-[#faf9f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:bg-white focus:border-[#23493a] outline-none">
                    <option value="">All Matters</option>
                    @foreach($matters as $m)
                        <option value="{{ $m->id }}" {{ $matterId == $m->id ? 'selected' : '' }}>
                            {{ $m->case_number }} &middot; {{ $m->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="assigned_to" onchange="this.form.submit()"
                        class="w-full h-9 px-2.5 rounded-md bg-[#faf9f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:bg-white focus:border-[#23493a] outline-none">
                    <option value="">All Assignees</option>
                    @foreach($attorneys as $atty)
                        <option value="{{ $atty->id }}" {{ $assigneeId == $atty->id ? 'selected' : '' }}>
                            {{ $atty->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="priority" onchange="this.form.submit()"
                        class="w-full h-9 px-2.5 rounded-md bg-[#faf9f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:bg-white focus:border-[#23493a] outline-none">
                    <option value="all">All Priorities</option>
                    @foreach(\App\Models\Task::PRIORITIES as $pKey => $pLabel)
                        <option value="{{ $pKey }}" {{ $priorityFilter === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-1 flex items-center justify-end">
                @if($search || $statusFilter !== 'all' || $priorityFilter !== 'all' || $matterId || $assigneeId || $tagFilter)
                    <a href="{{ route('tasks.index') }}" class="text-xs text-[#ba1a1a] hover:underline font-mono inline-flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[14px]">close</span>
                        <span>Reset</span>
                    </a>
                @endif
            </div>

            <!-- Keep Status in query -->
            @if($statusFilter !== 'all')
                <input type="hidden" name="status" value="{{ $statusFilter }}"/>
            @endif
        </form>

        <!-- Bottom Row: Status Filter Tabs & Tag Pills -->
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-[#f0eee8] flex-wrap">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto text-[11px] pb-1">
                @php
                    $statusTabs = [
                        'all' => 'All Tasks (' . $totalCount . ')',
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'waiting' => 'Waiting',
                        'blocked' => 'Blocked',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ];
                @endphp
                @foreach($statusTabs as $sKey => $sTitle)
                    <a href="{{ route('tasks.index', array_merge(request()->query(), ['status' => $sKey])) }}"
                       class="px-2.5 py-1 rounded-full font-mono transition-colors whitespace-nowrap {{ $statusFilter === $sKey ? 'bg-[#23493a] text-white font-semibold shadow-xs' : 'bg-[#faf9f5] border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        {{ $sTitle }}
                    </a>
                @endforeach
            </div>

            <!-- Tag Filter Indicator if active -->
            @if($tagFilter)
                <div class="flex items-center gap-1.5 text-xs text-[#23493a] font-mono bg-[#ecfdf5] border border-[#a7f3d0] px-2.5 py-0.5 rounded-full">
                    <span>Tag: <strong>{{ $tagFilter }}</strong></span>
                    <a href="{{ route('tasks.index', request()->except('tag')) }}" class="hover:text-red-700">
                        <span class="material-symbols-outlined text-[14px]">cancel</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Tasks Table -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-[#f0eee8] flex items-center justify-between bg-[#faf8f5]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a]">checklist</span>
                <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase font-mono tracking-wider">
                    Tasks Dossier ({{ $tasks->count() }})
                </h3>
            </div>
            <span class="text-[11px] text-[#8a8a8a] font-mono">Sorted by urgency &amp; statutory cutoff</span>
        </div>

        <div class="divide-y divide-[#f0eee8]">
            @forelse($tasks as $task)
                @php
                    $isOverdue = $task->isOverdue();
                    $isDone = ($task->status === \App\Models\Task::STATUS_COMPLETED);
                @endphp
                <div class="p-4 flex flex-col gap-3 hover:bg-[#faf9f5]/50 transition-colors {{ $isDone ? 'bg-[#faf9f5]/30' : '' }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        
                        <!-- Left: Checkbox + Task ID + Title & Description -->
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <!-- Toggle Form -->
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="shrink-0 mt-0.5">
                                @csrf
                                <button type="submit" 
                                        class="w-5 h-5 rounded border flex items-center justify-center transition-colors cursor-pointer {{ $isDone ? 'bg-[#23493a] border-[#23493a] text-white' : 'border-[#c1c8c3] hover:border-[#23493a] bg-white' }}"
                                        title="{{ $isDone ? 'Mark In Progress' : 'Mark Completed' }}">
                                    @if($isDone)
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                    @endif
                                </button>
                            </form>

                            <div class="flex flex-col min-w-0 flex-1">
                                <!-- Top line: Task Number, Priority, Status, Overdue -->
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="font-mono text-[10.5px] font-semibold text-[#23493a] bg-[#f5f3ed] border border-[#e5e3dc] px-1.5 py-0.2 rounded">
                                        {{ $task->formatted_id }}
                                    </span>
                                    
                                    <!-- Priority Badge -->
                                    <span class="px-2 py-0.2 rounded text-[10px] font-mono uppercase border {{ $task->priority_badge_classes }}">
                                        {{ $task->priority_label }}
                                    </span>

                                    <!-- Status Badge -->
                                    <span class="px-2 py-0.2 rounded text-[10px] font-mono border {{ $task->status_badge_classes }}">
                                        {{ $task->status_label }}
                                    </span>

                                    @if($isOverdue)
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                            OVERDUE
                                        </span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h3 class="text-sm font-semibold text-[#1a1a1a] leading-snug {{ $isDone ? 'line-through text-[#8a8a8a]' : '' }}">
                                    {{ $task->title }}
                                </h3>

                                <!-- Description (if present) -->
                                @if($task->description)
                                    <p class="text-xs text-[#646864] mt-1 leading-relaxed line-clamp-2">
                                        {{ $task->description }}
                                    </p>
                                @endif

                                <!-- Meta: Matter, Client, Related Party, Document -->
                                <div class="flex items-center gap-3 mt-2 flex-wrap text-xs text-[#646864]">
                                    @if($task->matter)
                                        <a href="{{ route('matters.show', $task->matter->id) }}" class="inline-flex items-center gap-1 font-mono text-[11px] font-medium text-[#23493a] hover:underline">
                                            <span class="material-symbols-outlined text-[14px]">gavel</span>
                                            <span>{{ $task->matter->case_number }}</span>
                                        </a>
                                        <span class="text-[#e5e3dc]">&middot;</span>
                                    @endif

                                    @if($task->relatedClient)
                                        <span class="inline-flex items-center gap-1 text-[11.5px]">
                                            <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">person</span>
                                            <span>Client: <strong>{{ $task->relatedClient->name }}</strong></span>
                                        </span>
                                        <span class="text-[#e5e3dc]">&middot;</span>
                                    @endif

                                    @if($task->related_party_name)
                                        <span class="inline-flex items-center gap-1 text-[11.5px] bg-[#faf9f5] border border-[#e5e3dc] px-1.5 py-0.5 rounded">
                                            <span class="material-symbols-outlined text-[13px] text-[#8a8a8a]">groups</span>
                                            <span>Party: {{ $task->related_party_name }}</span>
                                        </span>
                                        <span class="text-[#e5e3dc]">&middot;</span>
                                    @endif

                                    @if($task->relatedDocument)
                                        <a href="{{ route('documents.download', $task->relatedDocument->id) }}" class="inline-flex items-center gap-1 text-[11.5px] text-[#23493a] hover:underline font-mono">
                                            <span class="material-symbols-outlined text-[14px]">description</span>
                                            <span>Doc: {{ $task->relatedDocument->title }}</span>
                                        </a>
                                        <span class="text-[#e5e3dc]">&middot;</span>
                                    @endif

                                    <!-- Assignee -->
                                    <span class="inline-flex items-center gap-1 text-[11.5px]">
                                        <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">badge</span>
                                        <span>Assigned: <strong class="text-[#1a1a1a]">{{ $task->assignee->name ?? 'Unassigned' }}</strong></span>
                                    </span>
                                </div>

                                <!-- Tags List -->
                                @if(!empty($task->tags) && is_array($task->tags))
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        @foreach($task->tags as $tTag)
                                            <a href="{{ route('tasks.index', ['tag' => $tTag]) }}" 
                                               class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] hover:bg-[#eae8e2] transition-colors">
                                                #{{ $tTag }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Dates, Status Quick Updater, Comments & Actions -->
                        <div class="flex flex-row lg:flex-col items-start lg:items-end justify-between gap-3 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-[#f0eee8]">
                            <!-- Dates Block -->
                            <div class="flex items-center lg:flex-col lg:items-end gap-2 lg:gap-0.5 text-xs text-[#8a8a8a] font-mono">
                                @if($task->start_date)
                                    <span class="text-[10.5px]">Start: {{ $task->start_date->format('M d, Y') }}</span>
                                @endif

                                @if($task->due_date)
                                    <span class="text-[11px] font-medium {{ $isOverdue ? 'text-rose-700 font-bold' : ($isDone ? 'text-[#8a8a8a]' : 'text-[#1a1a1a]') }}">
                                        Due: {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-[10.5px]">No cutoff date</span>
                                @endif

                                @if($task->completed_date)
                                    <span class="text-[10.5px] text-[#065f46] font-semibold">Done: {{ $task->completed_date->format('M d, Y') }}</span>
                                @endif
                            </div>

                            <!-- Quick Status Dropdown Form -->
                            <form action="{{ route('tasks.status.update', $task->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" 
                                        class="h-7 px-2 text-[11px] font-mono rounded border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:border-[#23493a] outline-none cursor-pointer">
                                    @foreach(\App\Models\Task::STATUSES as $stKey => $stLabel)
                                        <option value="{{ $stKey }}" {{ $task->status === $stKey ? 'selected' : '' }}>
                                            {{ $stLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            <!-- Actions Row -->
                            <div class="flex items-center gap-1.5">
                                <!-- Comments Drawer Button -->
                                <button @click="openTaskComments = (openTaskComments === {{ $task->id }} ? null : {{ $task->id }})" 
                                        class="inline-flex items-center gap-1 text-xs text-[#646864] hover:text-[#23493a] px-2 py-1 rounded border border-[#e5e3dc] bg-white hover:bg-[#faf8f5] transition-colors"
                                        title="Task updates & comments">
                                    <span class="material-symbols-outlined text-[14px]">chat_bubble</span>
                                    <span class="font-mono text-[10.5px] font-medium">{{ $task->comments->count() }}</span>
                                </button>

                                <!-- Edit Task Trigger -->
                                <button @click="editTask({{ json_encode($task) }})" 
                                        class="p-1 rounded text-[#646864] hover:text-[#23493a] border border-[#e5e3dc] bg-white hover:bg-[#faf8f5]"
                                        title="Edit Task">
                                    <span class="material-symbols-outlined text-[15px]">edit</span>
                                </button>

                                <!-- Delete Task Form -->
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Delete task {{ $task->formatted_id }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-rose-700 border border-[#e5e3dc] bg-white hover:bg-rose-50" title="Delete">
                                        <span class="material-symbols-outlined text-[15px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion: Comments & Discussions Drawer -->
                    <div x-show="openTaskComments === {{ $task->id }}" x-cloak class="p-3.5 rounded-md bg-[#faf9f5] border border-[#e5e3dc] space-y-3 mt-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#23493a]">chat</span>
                                <span>Progress Notes &amp; Updates ({{ $task->comments->count() }})</span>
                            </h4>
                            <span class="text-[10px] font-mono text-[#8a8a8a]">Logged by team advocates</span>
                        </div>

                        <!-- Existing Comments Stream -->
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @forelse($task->comments as $comment)
                                <div class="p-2.5 rounded bg-white border border-[#e5e3dc] text-xs">
                                    <div class="flex items-center justify-between text-[11px] text-[#646864] mb-1">
                                        <span class="font-semibold text-[#1a1a1a]">{{ $comment->user?->name ?? 'Team Member' }}</span>
                                        <span class="font-mono text-[#8a8a8a] text-[10px]">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-[#1a1a1a] leading-relaxed">{{ $comment->comment }}</p>
                                </div>
                            @empty
                                <p class="text-xs text-[#8a8a8a] italic p-1">No progress updates logged yet on this task.</p>
                            @endforelse
                        </div>

                        <!-- Post Comment Form -->
                        <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="flex items-center gap-2 pt-1">
                            @csrf
                            <input type="text" name="comment" required placeholder="Add procedural progress update or advocate note..."
                                   class="flex-1 px-3 py-1.5 text-xs rounded-md border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"/>
                            <button type="submit" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1 shrink-0">
                                <span>Add Note</span>
                                <span class="material-symbols-outlined text-[13px]">send</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-xs text-[#8a8a8a] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2">task_alt</span>
                    <p class="font-medium text-[#1a1a1a]">No litigation tasks found matching the selected filters.</p>
                    <button @click="openCreateModal = true" class="mt-3 btn-primary h-8 px-3.5 text-xs inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">add_task</span>
                        <span>Log New Task</span>
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="openCreateModal" 
         x-cloak 
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="openCreateModal = false" class="bg-white border border-[#e5e3dc] rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">add_task</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Schedule New Litigation Task</h3>
                </div>
                <button type="button" @click="openCreateModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf

                <!-- Title -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Task Title / Procedural Step *</label>
                    <input name="title" required type="text" placeholder="e.g. Draft Rejoinder &amp; Prepare Compilation of Precedents" 
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Detailed Description &amp; Instructions</label>
                    <textarea name="description" rows="3" placeholder="Provide background, specific judicial instructions, statutory points..." 
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none resize-none"></textarea>
                </div>

                <!-- Matter & Assignee -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Associated Case Matter</label>
                        <select name="matter_id" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">General Chambers Task (Unassigned Matter)</option>
                            @foreach($matters as $matter)
                                <option value="{{ $matter->id }}" {{ $matterId == $matter->id ? 'selected' : '' }}>
                                    {{ $matter->case_number }} &middot; {{ $matter->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Assigned Advocate / Staff *</label>
                        <select name="assigned_to" required class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach($attorneys as $atty)
                                <option value="{{ $atty->id }}" {{ Auth::id() === $atty->id ? 'selected' : '' }}>
                                    {{ $atty->name }} ({{ ucfirst($atty->role ?? 'Advocate') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Priority & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Priority Level *</label>
                        <select name="priority" required class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach(\App\Models\Task::PRIORITIES as $pKey => $pLabel)
                                <option value="{{ $pKey }}" {{ $pKey === 'normal' ? 'selected' : '' }}>{{ $pLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Initial Status *</label>
                        <select name="status" required class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach(\App\Models\Task::STATUSES as $sKey => $sLabel)
                                <option value="{{ $sKey }}">{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Start Date, Due Date, Completed Date -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Start Date</label>
                        <input name="start_date" type="date" value="{{ now()->toDateString() }}" 
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Statutory Due Date</label>
                        <input name="due_date" type="date" value="{{ now()->addDays(5)->toDateString() }}" 
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Completed Date</label>
                        <input name="completed_date" type="date" 
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>
                </div>

                <!-- Related Entities: Document, Client, Party -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Document</label>
                        <select name="related_document_id" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">None / Not Linked</option>
                            @foreach($documents as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->title }} ({{ $doc->filename }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Client</label>
                        <select name="related_client_id" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">Auto from Matter or Select</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Party</label>
                        <input name="related_party_name" type="text" placeholder="e.g. Opposing Party / Witness" 
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>
                </div>

                <!-- Tags -->
                <div class="flex flex-col gap-1.5" x-data="{ tagString: '' }">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Tags (comma-separated)</label>
                    <input name="tags" type="text" x-model="tagString" placeholder="e.g. Court Filing, Hearing Prep, Pleading Draft" 
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                        <span class="text-[10px] text-[#8a8a8a] font-mono">Recommended:</span>
                        @foreach($recommendedTags as $recTag)
                            <button type="button" @click="tagString = tagString ? (tagString + ', ' + '{{ $recTag }}') : '{{ $recTag }}'"
                                    class="px-1.5 py-0.2 rounded text-[9.5px] font-mono bg-[#faf9f5] border border-[#e5e3dc] text-[#646864] hover:text-[#23493a] hover:border-[#23493a]">
                                + {{ $recTag }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2.5">
                    <button type="button" @click="openCreateModal = false" class="btn-secondary h-9 px-4 text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary h-9 px-5 text-xs">
                        Schedule Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div x-show="editTaskModal" 
         x-cloak 
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="editTaskModal = false" class="bg-white border border-[#e5e3dc] rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">edit_calendar</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">
                        Edit Task <span x-text="editingTask ? editingTask.task_number : ''" class="font-mono text-[#23493a]"></span>
                    </h3>
                </div>
                <button type="button" @click="editTaskModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'{{ url('/tasks') }}/' + (editingTask ? editingTask.id : '')" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Task Title *</label>
                    <input name="title" required type="text" :value="editingTask ? editingTask.title : ''"
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Description</label>
                    <textarea name="description" rows="3" x-text="editingTask ? editingTask.description : ''"
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none resize-none"></textarea>
                </div>

                <!-- Matter & Assignee -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Associated Matter</label>
                        <select name="matter_id" :value="editingTask ? editingTask.matter_id : ''" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">General Chambers Task</option>
                            @foreach($matters as $matter)
                                <option value="{{ $matter->id }}">{{ $matter->case_number }} &middot; {{ $matter->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Assigned Advocate *</label>
                        <select name="assigned_to" required :value="editingTask ? editingTask.assigned_to : ''" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach($attorneys as $atty)
                                <option value="{{ $atty->id }}">{{ $atty->name }} ({{ ucfirst($atty->role ?? 'Advocate') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Priority & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Priority *</label>
                        <select name="priority" required :value="editingTask ? editingTask.priority : 'normal'" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach(\App\Models\Task::PRIORITIES as $pKey => $pLabel)
                                <option value="{{ $pKey }}">{{ $pLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Status *</label>
                        <select name="status" required :value="editingTask ? editingTask.status : 'not_started'" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            @foreach(\App\Models\Task::STATUSES as $sKey => $sLabel)
                                <option value="{{ $sKey }}">{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Start Date, Due Date, Completed Date -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Start Date</label>
                        <input name="start_date" type="date" :value="editingTask && editingTask.start_date ? editingTask.start_date.substring(0, 10) : ''"
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Due Date</label>
                        <input name="due_date" type="date" :value="editingTask && editingTask.due_date ? editingTask.due_date.substring(0, 10) : ''"
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Completed Date</label>
                        <input name="completed_date" type="date" :value="editingTask && editingTask.completed_date ? editingTask.completed_date.substring(0, 10) : ''"
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>
                </div>

                <!-- Related Entities -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Document</label>
                        <select name="related_document_id" :value="editingTask ? editingTask.related_document_id : ''" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">None</option>
                            @foreach($documents as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Client</label>
                        <select name="related_client_id" :value="editingTask ? editingTask.related_client_id : ''" class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                            <option value="">None</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Related Party</label>
                        <input name="related_party_name" type="text" :value="editingTask ? editingTask.related_party_name : ''"
                               class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                    </div>
                </div>

                <!-- Tags -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Tags (comma-separated)</label>
                    <input name="tags" type="text" :value="editingTask && editingTask.tags ? (Array.isArray(editingTask.tags) ? editingTask.tags.join(', ') : editingTask.tags) : ''"
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2.5">
                    <button type="button" @click="editTaskModal = false" class="btn-secondary h-9 px-4 text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary h-9 px-5 text-xs">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
