@extends('layouts.app')

@section('title', 'Litigation Tasks & Deadlines — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Litigation Tasks')

@section('content')
<div x-data="{ openCreateModal: false, filter: 'all', openTaskComments: null }" class="flex flex-col w-full text-[#1a1a1a]">
    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Chambers Tasks &amp; Productivity</h1>
            <p class="text-[13px] text-[#646864] mt-1">Court filings, interim replies, certified copies, and advocate assignments</p>
        </div>
        <button @click="openCreateModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add_task</span>
            <span>Log Litigation Task</span>
        </button>
    </div>

    <!-- Top 4 Connected Metric Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Total Tasks</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $tasks->count() }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">All logged litigation obligations</div>
        </div>
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Urgent Priority</div>
            <div class="text-[28px] font-medium text-[#ba1a1a] tracking-tight mt-1">{{ $tasks->where('priority', 'urgent')->where('status', 'todo')->count() }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Immediate attention required</div>
        </div>
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Pending / In Progress</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $tasks->where('status', 'todo')->count() }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Active team work items</div>
        </div>
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Completed</div>
            <div class="text-[28px] font-medium text-[#065f46] tracking-tight mt-1">{{ $tasks->where('status', 'completed')->count() }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Settled &amp; archived tasks</div>
        </div>
    </div>

    <!-- Tasks Table & Board -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden mb-6">
        <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between bg-[#faf8f5]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a]">checklist</span>
                <h3 class="text-sm font-semibold text-[#1a1a1a]">Active Litigation Obligations</h3>
            </div>
            <div class="flex items-center gap-1 bg-white p-1 rounded-md border border-[#e5e3dc] text-xs">
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">All</button>
                <button @click="filter = 'todo'" :class="filter === 'todo' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Pending</button>
                <button @click="filter = 'completed'" :class="filter === 'completed' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Done</button>
            </div>
        </div>

        <div class="divide-y divide-[#f0eee8]">
            @forelse($tasks as $task)
            <div x-show="filter === 'all' || (filter === 'todo' && '{{ $task->status }}' === 'todo') || (filter === 'completed' && '{{ $task->status }}' === 'completed')" class="p-4 flex items-center justify-between gap-4 hover:bg-[#faf9f5] transition-colors">
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Toggle Form -->
                    <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-5 h-5 rounded border border-[#c1c8c3] flex items-center justify-center transition-colors cursor-pointer {{ $task->status === 'completed' ? 'bg-[#23493a] border-[#23493a] text-white' : 'hover:border-[#23493a] bg-white' }}">
                            @if($task->status === 'completed')
                            <span class="material-symbols-outlined text-xs">check</span>
                            @endif
                        </button>
                    </form>

                    <div class="flex flex-col min-w-0">
                        <span class="text-[13px] font-medium text-[#1a1a1a] {{ $task->status === 'completed' ? 'line-through text-[#8a8a8a]' : '' }}">{{ $task->title }}</span>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-[#646864]">
                            @if($task->matter)
                            <span class="font-mono text-[#23493a] font-semibold">{{ $task->matter->case_number }}</span>
                            <span class="text-[#c1c8c3]">&middot;</span>
                            @endif
                            <span>Assigned to: <strong class="text-[#1a1a1a]">{{ $task->assignee->name ?? 'Unassigned' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <button @click="openTaskComments = (openTaskComments === {{ $task->id }} ? null : {{ $task->id }})" 
                            class="inline-flex items-center gap-1 text-xs text-[#646864] hover:text-[#23493a] px-2 py-1 rounded border border-[#e5e3dc] bg-white hover:bg-[#faf8f5] transition-colors"
                            title="Task Comments">
                        <span class="material-symbols-outlined text-sm">chat_bubble</span>
                        <span class="font-mono font-medium">{{ $task->comments->count() }}</span>
                    </button>
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded font-medium uppercase {{ $task->priority === 'urgent' ? 'bg-red-50 text-red-700 border border-red-200' : ($task->priority === 'high' ? 'bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]' : 'bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]') }}">
                        {{ $task->priority }}
                    </span>
                    <div class="flex items-center gap-1 text-xs text-[#8a8a8a]">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        <span class="font-mono text-[11px]">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No cutoff' }}</span>
                    </div>
                </div>
            </div>

            <!-- Task Comments Drawer/Accordion -->
            <div x-show="openTaskComments === {{ $task->id }}" x-cloak class="p-4 bg-[#faf8f5] border-t border-[#e5e3dc] space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493a]">chat</span>
                        <span>Task Discussion &amp; Updates ({{ $task->comments->count() }})</span>
                    </h4>
                </div>

                <!-- Existing Comments -->
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($task->comments as $comment)
                    <div class="p-2.5 rounded-lg bg-white border border-[#e5e3dc] text-xs">
                        <div class="flex items-center justify-between text-[11px] text-[#646864] mb-1">
                            <span class="font-semibold text-[#1a1a1a]">{{ $comment->user?->name ?? 'Team Member' }}</span>
                            <span class="font-mono text-[#8a8a8a]">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-[#1a1a1a] leading-relaxed">{{ $comment->comment }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-[#8a8a8a] italic">No updates logged yet on this task.</p>
                    @endforelse
                </div>

                <!-- Post Comment Form -->
                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="flex items-center gap-2 pt-1">
                    @csrf
                    <input type="text" name="comment" required placeholder="Add a comment or status update..."
                        class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"/>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-colors shrink-0">
                        Comment
                    </button>
                </form>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-[#8a8a8a]">No litigation tasks currently recorded.</div>
            @endforelse
        </div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="openCreateModal" 
         x-cloak 
         @click.away="openCreateModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">add_task</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">New Litigation Obligation</h3>
                </div>
                <button type="button" @click="openCreateModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Task Description / Procedural Step *</label>
                    <input name="title" required type="text" placeholder="e.g. Draft Rejoinder &amp; Prepare Compilation of Judgments" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Associated Case Matter</label>
                        <select name="matter_id" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="">General Chambers Task</option>
                            @foreach($matters as $matter)
                            <option value="{{ $matter->id }}">{{ $matter->case_number }} &middot; {{ $matter->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Assigned Advocate / Clerk</label>
                        <select name="assigned_to" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            @foreach($attorneys as $atty)
                            <option value="{{ $atty->id }}">{{ $atty->name }} ({{ ucfirst($atty->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Priority Level</label>
                        <select name="priority" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="urgent">Urgent (Statutory Rule 56/12)</option>
                            <option value="high">High Priority</option>
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Statutory Cutoff Date *</label>
                        <input name="due_date" type="date" value="{{ now()->addDays(5)->toDateString() }}" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openCreateModal = false" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Record Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
