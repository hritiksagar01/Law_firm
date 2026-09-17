@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Cause List &amp; Court Schedule</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Chambers Cause List</h1>
            <p class="text-sm text-[#766A5E] mt-1">Daily judicial listings, bench appearances, hearing stages, and allocated courtroom counsel.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Daily Cause List</span>
            </button>
            <a href="{{ route('calendar.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">calendar_month</span>
                <span>Calendar View</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Advocate Workload</a>
    </div>

    <!-- Filter & Range Selector Bar -->
    <div class="p-4 rounded-xl bg-white border border-[#EFECE6] shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono uppercase text-[#766A5E] mr-1">Time Horizon:</span>
            <a href="{{ route('reports.hearings', ['range' => 'today', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'today' ? 'bg-[#9F8349] text-white font-semibold' : 'bg-[#FAF8F5] text-[#554D45] hover:bg-[#F8F4EE]' }}">Today</a>
            <a href="{{ route('reports.hearings', ['range' => 'tomorrow', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'tomorrow' ? 'bg-[#9F8349] text-white font-semibold' : 'bg-[#FAF8F5] text-[#554D45] hover:bg-[#F8F4EE]' }}">Tomorrow</a>
            <a href="{{ route('reports.hearings', ['range' => 'week', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'week' ? 'bg-[#9F8349] text-white font-semibold' : 'bg-[#FAF8F5] text-[#554D45] hover:bg-[#F8F4EE]' }}">This Week</a>
            <a href="{{ route('reports.hearings', ['range' => 'month', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'month' ? 'bg-[#9F8349] text-white font-semibold' : 'bg-[#FAF8F5] text-[#554D45] hover:bg-[#F8F4EE]' }}">This Month</a>
            <a href="{{ route('reports.hearings', ['range' => 'all', 'court' => request('court')]) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $dateFilter == 'all' ? 'bg-[#9F8349] text-white font-semibold' : 'bg-[#FAF8F5] text-[#554D45] hover:bg-[#F8F4EE]' }}">All Upcoming</a>
        </div>

        <form action="{{ route('reports.hearings') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="range" value="{{ $dateFilter }}"/>
            <label class="text-xs font-mono uppercase text-[#766A5E]">Court:</label>
            <select name="court" onchange="this.form.submit()" class="h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none">
                <option value="all">All Judicial Forums</option>
                @foreach($allCourts as $court)
                <option value="{{ $court }}" {{ request('court') == $court ? 'selected' : '' }}>{{ $court }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Cause List Content Area -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden print:border-none print:shadow-none">
        <div class="p-6 border-b border-[#EFECE6] flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div>
                <h2 class="font-serif font-bold text-xl text-[#222222] uppercase tracking-wide">
                    {{ config('legal.app_name', 'Vennamraj Associates') }} &mdash; Daily Judicial Cause List
                </h2>
                <div class="text-xs text-[#766A5E] mt-0.5">
                    Listing Horizon: <span class="font-semibold text-[#222222] uppercase">{{ ucfirst($dateFilter) }}</span> &bull; 
                    Generated: <span class="font-mono">{{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>
            <div class="text-xs font-mono text-[#9F8349] font-bold">
                Total Matters Listed: {{ $hearings->count() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold w-32">Listing Date &amp; Time</th>
                        <th class="py-3 px-4 font-semibold">Forum &amp; Court Room</th>
                        <th class="py-3 px-4 font-semibold">Case Number &amp; Title</th>
                        <th class="py-3 px-4 font-semibold">Client / Petitioner</th>
                        <th class="py-3 px-4 font-semibold">Listing Purpose / Stage</th>
                        <th class="py-3 px-4 font-semibold">Appearing Counsel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($hearings as $hearing)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono">
                            <div class="font-bold text-[#222222]">{{ \Carbon\Carbon::parse($hearing->start_time)->format('d M, Y') }}</div>
                            <div class="text-[11px] text-[#9F8349]">{{ \Carbon\Carbon::parse($hearing->start_time)->format('h:i A') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-[#222222]">{{ $hearing->matter->court_name ?? $hearing->location ?? 'Court of Record' }}</div>
                            @if($hearing->matter && $hearing->matter->judge_name)
                            <div class="text-[10px] text-[#766A5E]">Bench: {{ $hearing->matter->judge_name }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($hearing->matter)
                            <div class="font-mono text-[11px] font-bold text-[#9F8349]">{{ $hearing->matter->case_number }}</div>
                            <a href="{{ route('matters.show', $hearing->matter->id) }}" class="font-medium text-[#222222] hover:text-[#9F8349] transition-colors block mt-0.5">
                                {{ $hearing->title }}
                            </a>
                            @else
                            <div class="font-medium text-[#222222]">{{ $hearing->title }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            {{ $hearing->matter->client->name ?? 'General Hearing' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#8B263E]/10 text-[#8B263E] border border-[#8B263E]/20">
                                {{ $hearing->event_type ?? 'Hearing / Arguments' }}
                            </span>
                            @if($hearing->is_statutory_deadline)
                            <span class="block text-[10px] text-red-600 font-bold mt-1">Statutory Limitation</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            <div class="font-medium">{{ $hearing->matter->leadAttorney->name ?? 'Chambers Counsel' }}</div>
                            <div class="text-[10px] text-[#766A5E]">Vakalatnama on Record</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[#766A5E]">
                            <span class="material-symbols-outlined text-3xl text-[#9F8349] mb-1">gavel</span>
                            <div class="font-medium text-[#222222]">No hearings listed for the selected period.</div>
                            <div class="text-xs text-[#766A5E] mt-0.5">Use the filter above to inspect upcoming weeks or register a new hearing in the Calendar docket.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
