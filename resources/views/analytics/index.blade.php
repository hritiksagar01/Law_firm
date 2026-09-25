@extends('layouts.app')

@section('title', 'Practice Analytics & Intelligence — Sharma Legal Chambers')
@section('header_title', 'Analytics')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
            Chambers Practice Analytics
        </h1>
        <p class="text-xs text-[#646864] mt-1 font-sans">
            Comprehensive business intelligence on matter velocity, client conversion funnels, conflict clearances, and team caseloads.
        </p>
    </div>

    <!-- Top Key Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <div class="p-5">
            <span class="text-[12px] text-[#646864] font-medium block">Active Matters</span>
            <div class="text-[28px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $activeMatters }}</div>
            <span class="text-[11px] text-[#8a8a8a] mt-0.5 block">of {{ $totalMatters }} total dossiers ({{ $closedMatters }} closed)</span>
        </div>

        <div class="p-5">
            <span class="text-[12px] text-[#646864] font-medium block">Active Clients</span>
            <div class="text-[28px] font-semibold text-[#065f46] tracking-tight mt-1">{{ $activeClientsCount }}</div>
            <span class="text-[11px] text-[#8a8a8a] mt-0.5 block">{{ $leadsCount }} leads awaiting intake</span>
        </div>

        <div class="p-5">
            <span class="text-[12px] text-[#646864] font-medium block">Task Completion Rate</span>
            @php $compRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 100; @endphp
            <div class="text-[28px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ $compRate }}%</div>
            <span class="text-[11px] {{ $overdueTasks > 0 ? 'text-red-700 font-medium' : 'text-[#8a8a8a]' }} mt-0.5 block">
                {{ $overdueTasks }} tasks overdue &middot; {{ $pendingTasks }} pending
            </span>
        </div>

        <div class="p-5">
            <span class="text-[12px] text-[#646864] font-medium block">Conflict Clearance Rate</span>
            @php $clearRate = $totalConflictChecks > 0 ? round(($clearedConflictChecks / $totalConflictChecks) * 100) : 100; @endphp
            <div class="text-[28px] font-semibold text-[#23493a] tracking-tight mt-1">{{ $clearRate }}%</div>
            <span class="text-[11px] text-[#8a8a8a] mt-0.5 block">{{ $flaggedConflictChecks }} flagged &middot; {{ $totalConflictChecks }} total checks</span>
        </div>
    </div>

    <!-- 2-Column Section: Client Acquisition Funnel + Matters Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Client Lifecycle Funnel -->
        <div class="border border-[#e5e3dc] bg-white rounded-md p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Client Acquisition &amp; Conversion Funnel</h3>
                <span class="text-xs font-mono text-[#8a8a8a]">{{ $totalClients }} Total Inquiries</span>
            </div>

            <div class="space-y-3">
                <!-- Lead -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-[#1a1a1a]">1. Inquiries &amp; Self-Reg Leads</span>
                        <span class="font-mono text-[#8a8a8a]">{{ $leadsCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-amber-400 rounded-full" style="width: {{ $totalClients > 0 ? max(5, ($leadsCount / $totalClients) * 100) : 0 }}%"></div>
                    </div>
                </div>

                <!-- Intake -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-[#1a1a1a]">2. Intake In Progress</span>
                        <span class="font-mono text-[#8a8a8a]">{{ $intakeCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full" style="width: {{ $totalClients > 0 ? max(5, ($intakeCount / $totalClients) * 100) : 0 }}%"></div>
                    </div>
                </div>

                <!-- Conflict Check -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-[#1a1a1a]">3. Conflict Check Pending</span>
                        <span class="font-mono text-[#8a8a8a]">{{ $conflictCheckCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full" style="width: {{ $totalClients > 0 ? max(5, ($conflictCheckCount / $totalClients) * 100) : 0 }}%"></div>
                    </div>
                </div>

                <!-- Prospective -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-[#1a1a1a]">4. Prospective (Fee Quote / Retainer)</span>
                        <span class="font-mono text-[#8a8a8a]">{{ $prospectiveCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $totalClients > 0 ? max(5, ($prospectiveCount / $totalClients) * 100) : 0 }}%"></div>
                    </div>
                </div>

                <!-- Active -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-[#1a1a1a]">5. Retained &amp; Active Representation</span>
                        <span class="font-mono text-[#065f46] font-semibold">{{ $activeClientsCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-[#faf8f5] border border-[#e5e3dc] overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: {{ $totalClients > 0 ? max(5, ($activeClientsCount / $totalClients) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matter Distribution -->
        <div class="border border-[#e5e3dc] bg-white rounded-md p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Matters Velocity &amp; Status Breakdown</h3>
                <span class="text-xs font-mono text-[#8a8a8a]">{{ $newMattersThisMonth }} New This Month</span>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center my-4">
                <div class="p-4 rounded-md bg-[#ecfdf5] border border-[#a7f3d0]">
                    <span class="text-2xl font-bold text-[#065f46]">{{ $activeMatters }}</span>
                    <span class="text-[11px] text-[#065f46] font-medium block uppercase tracking-wider mt-1">Active</span>
                </div>
                <div class="p-4 rounded-md bg-[#faf8f5] border border-[#e5e3dc]">
                    <span class="text-2xl font-bold text-[#646864]">{{ $pendingMatters }}</span>
                    <span class="text-[11px] text-[#646864] font-medium block uppercase tracking-wider mt-1">Intake / Open</span>
                </div>
                <div class="p-4 rounded-md bg-stone-50 border border-stone-200">
                    <span class="text-2xl font-bold text-stone-700">{{ $closedMatters }}</span>
                    <span class="text-[11px] text-stone-600 font-medium block uppercase tracking-wider mt-1">Disposed</span>
                </div>
            </div>

            <div class="pt-4 border-t border-[#f0eee8] space-y-2 text-xs text-[#646864]">
                <div class="flex items-center justify-between">
                    <span>Evidence filings in vault:</span>
                    <strong class="font-mono text-[#1a1a1a]">{{ $totalDocs }} files</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span>Client document requests completed:</span>
                    <strong class="font-mono text-[#065f46]">{{ $completedDocRequests }} ({{ $pendingDocRequests }} pending)</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Team Workload Distribution Table -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Chamber Team Caseload Distribution</h3>
            <span class="text-xs font-mono text-[#8a8a8a]">{{ $teamWorkload->count() }} Advocates &amp; Staff</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[11px] text-[#646864] uppercase font-mono">
                        <th class="py-3 px-4 font-semibold">Team Member</th>
                        <th class="py-3 px-4 font-semibold">Role</th>
                        <th class="py-3 px-4 font-semibold text-center">Active Matters Led</th>
                        <th class="py-3 px-4 font-semibold text-center">Pending Tasks</th>
                        <th class="py-3 px-4 text-right font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($teamWorkload as $mem)
                    <tr class="hover:bg-[#faf9f5]">
                        <td class="py-3 px-4 font-semibold text-[#1a1a1a]">{{ $mem->name }}</td>
                        <td class="py-3 px-4 text-[#646864] uppercase text-[10.5px] font-mono">{{ $mem->role }}</td>
                        <td class="py-3 px-4 text-center font-mono font-semibold text-[#23493a]">{{ $mem->active_cases_count }}</td>
                        <td class="py-3 px-4 text-center font-mono {{ $mem->pending_tasks_count > 5 ? 'text-amber-800 font-semibold' : 'text-[#646864]' }}">
                            {{ $mem->pending_tasks_count }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Active
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-xs text-[#8a8a8a]">No staff members recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
