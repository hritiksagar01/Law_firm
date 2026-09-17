@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Advocate Workload</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Advocate &amp; Team Workload Allocation</h1>
            <p class="text-sm text-[#766A5E] mt-1">Operational capacity planning, active matter distribution, and task throughput across chambers personnel.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Allocation</span>
            </button>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">people</span>
                <span>Staff Roster</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Advocate Workload</a>
    </div>

    <!-- Workload Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($advocates as $advocate)
        @php
            $totalTasks = $advocate->total_tasks_count ?? 0;
            $pendingTasks = $advocate->pending_tasks_count ?? 0;
            $completedTasks = $advocate->completed_tasks_count ?? 0;
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 100;
            
            // Workload load tag
            if ($advocate->active_matters_count >= 5 || $pendingTasks >= 8) {
                $loadStatus = 'High Capacity';
                $loadBadge = 'bg-rose-50 text-rose-800 border-rose-200';
            } elseif ($advocate->active_matters_count >= 2 || $pendingTasks >= 3) {
                $loadStatus = 'Optimal Capacity';
                $loadBadge = 'bg-amber-50 text-amber-800 border-amber-200';
            } else {
                $loadStatus = 'Available Capacity';
                $loadBadge = 'bg-emerald-50 text-emerald-800 border-emerald-200';
            }
        @endphp
        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-between font-serif font-bold text-[#9F8349] text-sm shrink-0">
                            <span class="w-full text-center">{{ substr($advocate->name, 0, 2) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm text-[#222222]">{{ $advocate->name }}</h3>
                            <div class="text-[11px] text-[#766A5E]">{{ $advocate->title ?? ucfirst($advocate->role) }}</div>
                        </div>
                    </div>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $loadBadge }}">
                        {{ $loadStatus }}
                    </span>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2 py-3 border-y border-[#FAF8F5] text-center my-3">
                    <div class="p-2 rounded-lg bg-[#FAF8F5]">
                        <div class="font-mono text-base font-bold text-[#9F8349]">{{ $advocate->active_matters_count }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#766A5E]">Active Cases</div>
                    </div>
                    <div class="p-2 rounded-lg bg-[#FAF8F5]">
                        <div class="font-mono text-base font-bold text-[#8B263E]">{{ $pendingTasks }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#766A5E]">Pending Tasks</div>
                    </div>
                    <div class="p-2 rounded-lg bg-[#FAF8F5]">
                        <div class="font-mono text-base font-bold text-emerald-700">{{ $completedTasks }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#766A5E]">Completed</div>
                    </div>
                </div>

                <!-- Completion Progress -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#766A5E]">Task Clearance Rate</span>
                        <span class="font-mono font-bold text-[#222222]">{{ $completionRate }}%</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-[#FAF8F5] border border-[#EFECE6] overflow-hidden">
                        <div class="h-full bg-[#9F8349] rounded-full" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#FAF8F5] flex items-center justify-between text-xs">
                <a href="{{ route('reports.cases', ['attorney_id' => $advocate->id]) }}" class="text-[#9F8349] font-semibold hover:underline">
                    Assigned Cases &rarr;
                </a>
                <a href="{{ route('tasks.index') }}" class="text-[#766A5E] hover:text-[#222222]">
                    Assign Task
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-10 text-center text-[#766A5E]">
            No advocate records logged.
        </div>
        @endforelse
    </div>
</div>
@endsection
