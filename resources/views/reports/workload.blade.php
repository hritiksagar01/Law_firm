@extends('layouts.app')

@section('title', 'Advocate Workload Allocation — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Advocate Workload</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Advocate &amp; Team Workload Allocation</h1>
            <p class="text-xs text-[#646864] mt-0.5">Operational capacity planning, active matter distribution, and task throughput across chambers personnel.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Allocation</span>
            </button>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">people</span>
                <span>Staff Roster</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#23493a] text-white shadow-xs">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Bank Activity &amp; Reconciliation</a>
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
        <div class="p-5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#23493a]/10 border border-[#23493a]/20 flex items-center justify-center font-bold text-[#23493a] text-xs shrink-0">
                            <span>{{ strtoupper(substr($advocate->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xs text-[#1a1a1a]">{{ $advocate->name }}</h3>
                            <div class="text-[11px] text-[#646864]">{{ $advocate->title ?? ucfirst($advocate->role) }}</div>
                        </div>
                    </div>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium border {{ $loadBadge }}">
                        {{ $loadStatus }}
                    </span>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2 py-3 border-y border-[#e5e3dc] text-center my-3">
                    <div class="p-2 rounded-lg bg-[#faf8f5]">
                        <div class="font-mono text-base font-bold text-[#23493a]">{{ $advocate->active_matters_count }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#646864]">Active Cases</div>
                    </div>
                    <div class="p-2 rounded-lg bg-[#faf8f5]">
                        <div class="font-mono text-base font-bold text-rose-700">{{ $pendingTasks }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#646864]">Pending Tasks</div>
                    </div>
                    <div class="p-2 rounded-lg bg-[#faf8f5]">
                        <div class="font-mono text-base font-bold text-emerald-700">{{ $completedTasks }}</div>
                        <div class="text-[9px] font-mono uppercase text-[#646864]">Completed</div>
                    </div>
                </div>

                <!-- Completion Progress -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#646864]">Task Clearance Rate</span>
                        <span class="font-mono font-bold text-[#1a1a1a]">{{ $completionRate }}%</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-[#23493a] rounded-full" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#e5e3dc] flex items-center justify-between text-xs">
                <a href="{{ route('reports.cases', ['attorney_id' => $advocate->id]) }}" class="text-[#23493a] font-medium hover:underline">
                    Assigned Cases &rarr;
                </a>
                <a href="{{ route('tasks.index') }}" class="text-[#646864] hover:text-[#1a1a1a]">
                    Assign Task
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-10 text-center text-[#646864]">
            No advocate records logged.
        </div>
        @endforelse
    </div>
</div>
@endsection
