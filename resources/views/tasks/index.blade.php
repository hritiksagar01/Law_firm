<x-app-layout>
    <x-slot name="title">Litigation Tasks &amp; Deadlines — {{ config('legal.app_name', 'Vennamraj Associates') }}</x-slot>

    <div x-data="{ openCreateModal: false, filter: 'all' }">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#EFECE6] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Litigation Deadlines &amp; Registry Protocols</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">Chambers Tasks &amp; Productivity</h1>
                <p class="text-sm text-[#766A5E]">Court filings, interim replies, certified copies, and advocate assignments</p>
            </div>
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#9F8349] text-white text-xs font-medium hover:bg-[#856C36] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">add_task</span>
                <span>Log Litigation Task</span>
            </button>
        </div>

        <!-- Task Metrics Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-[#EFECE6] shadow-sm flex flex-col">
                <span class="text-[11px] font-mono uppercase text-[#766A5E]">Total Tasks</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1">{{ $tasks->count() }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#EFECE6] shadow-sm flex flex-col">
                <span class="text-[11px] font-mono uppercase text-red-700 font-semibold">Urgent Priority</span>
                <span class="text-2xl font-serif font-bold text-red-700 mt-1">{{ $tasks->where('priority', 'urgent')->where('status', 'todo')->count() }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#EFECE6] shadow-sm flex flex-col">
                <span class="text-[11px] font-mono uppercase text-[#9F8349]">In Progress / Todo</span>
                <span class="text-2xl font-serif font-bold text-[#9F8349] mt-1">{{ $tasks->where('status', 'todo')->count() }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#EFECE6] shadow-sm flex flex-col">
                <span class="text-[11px] font-mono uppercase text-[#9F8349]">Completed</span>
                <span class="text-2xl font-serif font-bold text-[#9F8349] mt-1">{{ $tasks->where('status', 'completed')->count() }}</span>
            </div>
        </div>

        <!-- Tasks Table & Board -->
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#9F8349]">checklist</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Active Litigation Obligations</h3>
                </div>
                <div class="flex items-center gap-1 bg-[#FAF8F5] p-1 rounded-lg border border-[#EFECE6] text-xs">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-white font-semibold text-[#9F8349] shadow-xs' : 'text-[#766A5E]'" class="px-2.5 py-1 rounded">All</button>
                    <button @click="filter = 'todo'" :class="filter === 'todo' ? 'bg-white font-semibold text-[#9F8349] shadow-xs' : 'text-[#766A5E]'" class="px-2.5 py-1 rounded">Pending</button>
                    <button @click="filter = 'completed'" :class="filter === 'completed' ? 'bg-white font-semibold text-[#9F8349] shadow-xs' : 'text-[#766A5E]'" class="px-2.5 py-1 rounded">Done</button>
                </div>
            </div>

            <div class="divide-y divide-[#F4EFEA]">
                @forelse($tasks as $task)
                <div x-show="filter === 'all' || (filter === 'todo' && '{{ $task->status }}' === 'todo') || (filter === 'completed' && '{{ $task->status }}' === 'completed')" class="p-4 flex items-center justify-between gap-4 hover:bg-[#FAF8F5] transition-colors">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <!-- Toggle Form -->
                        <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-5 h-5 rounded border border-[#EAE4DC] flex items-center justify-center transition-colors {{ $task->status === 'completed' ? 'bg-[#9F8349] border-[#9F8349] text-white' : 'hover:border-[#9F8349] bg-white' }}">
                                @if($task->status === 'completed')
                                <span class="material-symbols-outlined text-xs">check</span>
                                @endif
                            </button>
                        </form>

                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#222222] {{ $task->status === 'completed' ? 'line-through text-[#766A5E]' : '' }}">{{ $task->title }}</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($task->matter)
                                <span class="text-[10px] font-mono text-[#766A5E]">{{ $task->matter->case_number }}</span>
                                <span class="text-[#EAE4DC]">·</span>
                                @endif
                                <span class="text-[10px] text-[#554D45]">Assigned to: <strong class="text-[#222222]">{{ $task->assignee->name ?? 'Unassigned' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($task->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-[#F4EFEA] text-[#554D45]') }}">
                            {{ $task->priority }}
                        </span>
                        <div class="flex items-center gap-1 text-xs text-[#766A5E]">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span class="font-mono text-[11px]">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No cutoff' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-[#766A5E]">No litigation tasks currently recorded.</div>
                @endforelse
            </div>
        </div>

        <!-- Create Task Modal -->
        <div x-show="openCreateModal" @click.away="openCreateModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#EFECE6] w-full max-w-lg p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349]">add_task</span>
                        <h3 class="text-sm font-semibold text-[#222222]">New Litigation Obligation</h3>
                    </div>
                    <button type="button" @click="openCreateModal = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('tasks.store') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Task Description / Procedural Step</label>
                        <input name="title" required type="text" placeholder="e.g. Draft Rejoinder &amp; Prepare Compilation of Judgments" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#9F8349]"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Associated Case Matter</label>
                            <select name="matter_id" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#9F8349]">
                                <option value="">General Chambers Task</option>
                                @foreach($matters as $matter)
                                <option value="{{ $matter->id }}">{{ $matter->case_number }} · {{ $matter->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Assigned Advocate / Clerk</label>
                            <select name="assigned_to" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#9F8349]">
                                @foreach($attorneys as $atty)
                                <option value="{{ $atty->id }}">{{ $atty->name }} ({{ ucfirst($atty->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Priority Level</label>
                            <select name="priority" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#9F8349]">
                                <option value="urgent">Urgent (Statutory Rule 56/12)</option>
                                <option value="high">High Priority</option>
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Statutory Cutoff Date</label>
                            <input name="due_date" type="date" value="{{ now()->addDays(5)->toDateString() }}" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#9F8349]"/>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#F4EFEA] flex items-center justify-end gap-2">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-lg border border-[#EAE4DC] text-[#554D45] hover:bg-[#FAF8F5]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Record Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
