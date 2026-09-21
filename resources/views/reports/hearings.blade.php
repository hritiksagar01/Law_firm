@extends('layouts.app')

@section('title', 'Chambers Cause List & Hearings — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Cause List &amp; Court Schedule</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Chambers Cause List</h1>
            <p class="text-xs text-[#646864] mt-0.5">Daily judicial listings, bench appearances, hearing stages, and allocated courtroom counsel.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Daily Cause List</span>
            </button>
            <a href="{{ route('calendar.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">calendar_month</span>
                <span>Calendar View</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#23493a] text-white shadow-xs">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Bank Activity &amp; Reconciliation</a>
    </div>

    <!-- Filter & Range Selector Bar -->
    <div class="p-3.5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-[11px] font-semibold text-[#646864] mr-1">Time Horizon:</span>
            <a href="{{ route('reports.hearings', ['range' => 'today', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'today' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">Today</a>
            <a href="{{ route('reports.hearings', ['range' => 'tomorrow', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'tomorrow' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">Tomorrow</a>
            <a href="{{ route('reports.hearings', ['range' => 'week', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'week' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">This Week</a>
            <a href="{{ route('reports.hearings', ['range' => 'month', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'month' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">This Month</a>
            <a href="{{ route('reports.hearings', ['range' => 'all', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'all' ? 'bg-[#23493a] text-white' : 'bg-[#faf8f5] text-[#1a1a1a] hover:bg-[#f0eee8]' }}">All Upcoming</a>
        </div>

        <form action="{{ route('reports.hearings') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="range" value="{{ $dateFilter }}"/>
            <label class="text-xs font-semibold text-[#646864]">Court:</label>
            <select name="court" onchange="this.form.submit()" class="h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none text-[#1a1a1a]">
                <option value="all">All Judicial Forums</option>
                @foreach($allCourts as $court)
                <option value="{{ $court }}" {{ request('court') == $court ? 'selected' : '' }}>{{ $court }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Cause List Content Area -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden print:border-none print:shadow-none">
        <div class="p-5 border-b border-[#e5e3dc] bg-[#faf8f5] flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div>
                <h2 class="font-bold text-base text-[#1a1a1a] uppercase tracking-wide">
                    {{ config('legal.app_name', 'Lawyer Practice Management') }} &mdash; Daily Judicial Cause List
                </h2>
                <div class="text-xs text-[#646864] mt-0.5">
                    Listing Horizon: <span class="font-semibold text-[#1a1a1a] uppercase">{{ ucfirst($dateFilter) }}</span> &bull; 
                    Generated: <span class="font-mono text-[#1a1a1a]">{{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>
            <div class="text-xs font-mono text-[#23493a] font-bold">
                Total Matters Listed: {{ $hearings->count() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-32">Listing Date &amp; Time</th>
                        <th class="py-3 px-4">Forum &amp; Court Room</th>
                        <th class="py-3 px-4">Case Number &amp; Title</th>
                        <th class="py-3 px-4">Client / Petitioner</th>
                        <th class="py-3 px-4">Listing Purpose / Stage</th>
                        <th class="py-3 px-4">Appearing Counsel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($hearings as $hearing)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono">
                            <div class="font-bold text-[#1a1a1a]">{{ \Carbon\Carbon::parse($hearing->start_time)->format('d M, Y') }}</div>
                            <div class="text-[11px] text-[#23493a] font-semibold">{{ \Carbon\Carbon::parse($hearing->start_time)->format('h:i A') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-[#1a1a1a]">{{ $hearing->matter->court_name ?? $hearing->location ?? 'Court of Record' }}</div>
                            @if($hearing->matter && $hearing->matter->judge_name)
                            <div class="text-[10px] text-[#646864]">Bench: {{ $hearing->matter->judge_name }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($hearing->matter)
                            <div class="font-mono text-[11px] font-bold text-[#23493a]">{{ $hearing->matter->case_number }}</div>
                            <a href="{{ route('matters.show', $hearing->matter->id) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] transition-colors block mt-0.5">
                                {{ $hearing->title }}
                            </a>
                            @else
                            <div class="font-medium text-[#1a1a1a]">{{ $hearing->title }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            {{ $hearing->matter->client->name ?? 'General Hearing' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-800 border border-rose-200">
                                {{ $hearing->event_type ?? 'Hearing / Arguments' }}
                            </span>
                            @if($hearing->is_statutory_deadline)
                            <span class="block text-[10px] text-rose-600 font-bold mt-1">Statutory Limitation</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            <div class="font-medium text-[#1a1a1a]">{{ $hearing->matter->leadAttorney->name ?? 'Chambers Counsel' }}</div>
                            <div class="text-[10px] text-[#646864]">Vakalatnama on Record</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[#646864]">
                            <span class="material-symbols-outlined text-3xl text-[#23493a] mb-1">gavel</span>
                            <div class="font-medium text-[#1a1a1a]">No hearings listed for the selected period.</div>
                            <div class="text-xs text-[#646864] mt-0.5">Use the filter above to inspect upcoming weeks or register a new hearing in the Calendar docket.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
