@extends('layouts.app')

@section('title', 'My Todos & Personal Productivity — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ createModal: false, editModal: false, editingTodo: null }">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        <div class="font-semibold mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-rose-600 text-sm">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-rose-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <span>Personal Productivity</span>
                <span>•</span>
                <span>F-11 Task Organization</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">My Personal Todos</h1>
            <p class="text-xs text-[#646864] mt-0.5">Track personal follow-ups, case prep reminders, call-backs, and urgent self-assigned items.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">add_task</span>
                <span>New Todo</span>
            </button>
        </div>
    </div>

    <!-- Connected Top Metrics Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-4 bg-white rounded-xl border border-[#e5e3dc] shadow-sm divide-y lg:divide-y-0 lg:divide-x divide-[#e5e3dc] overflow-hidden">
        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Total Todos</span>
            <div class="text-2xl font-bold text-[#1a1a1a] mt-1 tracking-tight">{{ $totalCount }}</div>
            <div class="text-[11px] text-[#646864] mt-1">Personal backlog items</div>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Completed</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1 tracking-tight">{{ $completedCount }}</div>
            <div class="text-[11px] text-emerald-700 mt-1 font-medium">Cleared obligations</div>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Due Today</span>
            <div class="text-2xl font-bold text-[#23493a] mt-1 tracking-tight">{{ $dueTodayCount }}</div>
            <div class="text-[11px] text-[#23493a] mt-1 font-medium">Scheduled for resolution today</div>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Overdue</span>
            <div class="text-2xl font-bold {{ $overdueCount > 0 ? 'text-rose-700' : 'text-[#1a1a1a]' }} mt-1 tracking-tight">{{ $overdueCount }}</div>
            <div class="text-[11px] {{ $overdueCount > 0 ? 'text-rose-600 font-semibold' : 'text-[#646864]' }} mt-1">Passed target deadline</div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-[#e5e3dc] shadow-sm">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('todos.index', ['status' => 'all', 'matter_id' => request('matter_id')]) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $status === 'all' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">
                All ({{ $totalCount }})
            </a>
            <a href="{{ route('todos.index', ['status' => 'pending', 'matter_id' => request('matter_id')]) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $status === 'pending' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">
                Pending ({{ $totalCount - $completedCount }})
            </a>
            <a href="{{ route('todos.index', ['status' => 'completed', 'matter_id' => request('matter_id')]) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $status === 'completed' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">
                Completed ({{ $completedCount }})
            </a>
        </div>

        @if($matters->count() > 0)
        <form method="GET" action="{{ route('todos.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}"/>
            <label class="text-[11px] font-semibold text-[#646864] shrink-0">Filter by Matter:</label>
            <select name="matter_id" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]">
                <option value="">All Matters</option>
                @foreach($matters as $m)
                <option value="{{ $m->id }}" {{ request('matter_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->case_number }} — {{ Str::limit($m->title, 30) }}
                </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    <!-- Todo List Container -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm divide-y divide-[#e5e3dc] overflow-hidden">
        @forelse($todos as $todo)
        @php
            $isOverdue = ! $todo->is_completed && $todo->due_date && $todo->due_date->isPast() && ! $todo->due_date->isToday();
            $isDueToday = ! $todo->is_completed && $todo->due_date && $todo->due_date->isToday();
        @endphp
        <div class="p-4 flex items-start justify-between gap-4 hover:bg-[#faf8f5]/80 transition-colors {{ $todo->is_completed ? 'bg-[#faf8f5]/40' : '' }}">
            <div class="flex items-start gap-3 min-w-0">
                <!-- Checkbox toggle form -->
                <form method="POST" action="{{ route('todos.toggle', $todo) }}" class="mt-0.5 shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-5 h-5 rounded border flex items-center justify-center transition-colors {{ $todo->is_completed ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-[#e5e3dc] bg-white hover:border-[#23493a]' }}">
                        @if($todo->is_completed)
                        <span class="material-symbols-outlined text-sm font-bold">check</span>
                        @endif
                    </button>
                </form>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold {{ $todo->is_completed ? 'line-through text-[#8a8a8a]' : 'text-[#1a1a1a]' }}">
                            {{ $todo->title }}
                        </span>

                        @if($todo->matter)
                        <a href="{{ route('matters.show', $todo->matter) }}" class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded bg-[#23493a]/10 text-[#23493a] hover:underline font-semibold shrink-0">
                            <span>{{ $todo->matter->case_number }}</span>
                        </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 mt-1.5 text-[11px] text-[#646864] flex-wrap">
                        @if($todo->due_date)
                        <span class="inline-flex items-center gap-1 font-mono {{ $isOverdue ? 'text-rose-700 font-bold' : ($isDueToday ? 'text-amber-700 font-semibold' : 'text-[#646864]') }}">
                            <span class="material-symbols-outlined text-xs">event</span>
                            <span>{{ $todo->due_date->format('d M, Y') }}</span>
                            @if($isOverdue)
                            <span class="px-1.5 py-0.2 rounded text-[9px] uppercase bg-rose-50 border border-rose-200 text-rose-700">Overdue</span>
                            @elseif($isDueToday)
                            <span class="px-1.5 py-0.2 rounded text-[9px] uppercase bg-amber-50 border border-amber-200 text-amber-700">Due Today</span>
                            @endif
                        </span>
                        @endif

                        @if($todo->is_completed && $todo->completed_at)
                        <span class="text-emerald-700 text-[10px]">
                            Completed {{ $todo->completed_at->diffForHumans() }}
                        </span>
                        @endif

                        <span class="text-[10px] text-[#8a8a8a]">
                            Added {{ $todo->created_at->format('d M') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <button @click="editingTodo = {{ json_encode($todo) }}; editModal = true" class="p-1 rounded-md text-[#646864] hover:text-[#23493a] hover:bg-[#faf8f5] transition-colors" title="Edit Todo">
                    <span class="material-symbols-outlined text-base">edit</span>
                </button>
                <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm('Delete this todo item?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 rounded-md text-[#646864] hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete Todo">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-14 text-center text-[#646864]">
            <span class="material-symbols-outlined text-4xl text-[#8a8a8a] block mb-2">checklist</span>
            <p class="font-medium text-xs">No todo items found.</p>
            <p class="text-[11px] text-[#8a8a8a] mt-0.5">Stay on top of your caseload by adding personal action items and reminders.</p>
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 mt-3 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c]">
                <span class="material-symbols-outlined text-sm">add_task</span>
                <span>Add Your First Todo</span>
            </button>
        </div>
        @endforelse
    </div>

    @if($todos->hasPages())
    <div class="p-4 bg-white rounded-xl border border-[#e5e3dc] shadow-sm">
        {{ $todos->links() }}
    </div>
    @endif

    <!-- Create Todo Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModal = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-[#e5e3dc]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-base text-[#1a1a1a]">Create Personal Todo</h3>
                <button @click="createModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('todos.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Task / Todo Description <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Review interim relief citations for hearing"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Associated Matter (Optional)</label>
                    <select name="matter_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]">
                        <option value="">-- None (Personal / General) --</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Target Due Date (Optional)</label>
                    <input type="date" name="due_date"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#23493a] text-white rounded-lg hover:bg-[#1a382c] shadow-sm">
                        Create Todo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Todo Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="editModal = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-[#e5e3dc]" x-show="editingTodo">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-base text-[#1a1a1a]">Edit Todo</h3>
                <button @click="editModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="'/todos/' + (editingTodo ? editingTodo.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Task Description <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required :value="editingTodo ? editingTodo.title : ''"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Associated Matter</label>
                    <select name="matter_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]">
                        <option value="">-- None (Personal / General) --</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}" :selected="editingTodo && editingTodo.matter_id == {{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Target Due Date</label>
                    <input type="date" name="due_date" :value="editingTodo && editingTodo.due_date ? editingTodo.due_date.substring(0, 10) : ''"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#23493a] text-white rounded-lg hover:bg-[#1a382c] shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
