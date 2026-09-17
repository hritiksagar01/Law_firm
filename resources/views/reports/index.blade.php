@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span class="material-symbols-outlined text-sm">analytics</span>
                <span>Chambers Executive Intelligence</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Legal Operations &amp; Cause Lists</h1>
            <p class="text-sm text-[#766A5E] mt-1">Cross-sectional intelligence on active litigation, court listings, case lifecycles, and advocacy allocation.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.hearings', ['range' => 'today']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">gavel</span>
                <span>Today's Cause List</span>
            </a>
            <a href="{{ route('reports.cases') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">filter_alt</span>
                <span>Case Filters</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Advocate Workload</a>
    </div>

    <!-- Core Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Active Matters</span>
                <span class="material-symbols-outlined text-base text-[#9F8349]">folder_open</span>
            </div>
            <div class="text-3xl font-serif font-bold text-[#222222]">{{ $activeMatters }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">out of {{ $totalMatters }} total case files opened</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Upcoming Hearings</span>
                <span class="material-symbols-outlined text-base text-[#8B263E]">gavel</span>
            </div>
            <div class="text-3xl font-serif font-bold text-[#222222]">{{ $upcomingHearings }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Court cause list appearances scheduled</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Active Clients</span>
                <span class="material-symbols-outlined text-base text-[#2C5E7A]">people</span>
            </div>
            <div class="text-3xl font-serif font-bold text-[#222222]">{{ $totalClients }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Corporate &amp; individual retainers</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Pending Tasks</span>
                <span class="material-symbols-outlined text-base text-[#9F8349]">task_alt</span>
            </div>
            <div class="text-3xl font-serif font-bold text-[#222222]">{{ $pendingTasks }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Pleadings, evidence &amp; filings in progress</div>
        </div>
    </div>

    <!-- Case Lifecycle & Stage Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stages Breakdown -->
        <div class="lg:col-span-2 p-6 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <div>
                    <h3 class="font-serif font-semibold text-base text-[#222222]">Case Stage Distribution</h3>
                    <p class="text-xs text-[#766A5E]">Current stage of all open proceedings across judicial forums</p>
                </div>
                <a href="{{ route('reports.cases') }}" class="text-xs text-[#9F8349] font-medium hover:underline">View Detailed List &rarr;</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @php
                    $stageLabels = [
                        'pre_filing' => ['name' => 'Pre-Filing / Drafting', 'icon' => 'edit_document', 'color' => 'text-amber-700 bg-amber-50 border-amber-200'],
                        'pleadings' => ['name' => 'Pleadings Complete', 'icon' => 'article', 'color' => 'text-blue-700 bg-blue-50 border-blue-200'],
                        'discovery' => ['name' => 'Evidence & Discovery', 'icon' => 'find_in_page', 'color' => 'text-purple-700 bg-purple-50 border-purple-200'],
                        'trial' => ['name' => 'Arguments & Trial', 'icon' => 'gavel', 'color' => 'text-rose-700 bg-rose-50 border-rose-200'],
                        'settlement' => ['name' => 'Reserved / Orders', 'icon' => 'hourglass_empty', 'color' => 'text-emerald-700 bg-emerald-50 border-emerald-200'],
                        'closed' => ['name' => 'Disposed / Closed', 'icon' => 'check_circle', 'color' => 'text-gray-700 bg-gray-50 border-gray-200'],
                    ];
                @endphp

                @foreach($stageLabels as $stageKey => $meta)
                <div class="p-3.5 rounded-lg border {{ $meta['color'] }} flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-1">
                        <span class="material-symbols-outlined text-base">{{ $meta['icon'] }}</span>
                        <span class="font-mono text-sm font-bold">{{ $stages[$stageKey] ?? 0 }}</span>
                    </div>
                    <span class="text-xs font-medium">{{ $meta['name'] }}</span>
                </div>
                @endforeach
            </div>

            <!-- Practice Areas Breakdown -->
            <div class="mt-6 pt-4 border-t border-[#EFECE6]">
                <h4 class="text-xs font-mono uppercase tracking-wider text-[#9F8349] font-semibold mb-3">Matters by Practice Domain</h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($practiceAreas as $paName => $paCount)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs">
                        <span class="font-medium text-[#222222]">{{ $paName }}</span>
                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#9F8349] border border-[#EAE4DC]">{{ $paCount }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-[#766A5E]">No practice area records logged.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Court Distribution & Recent Cause List -->
        <div class="space-y-6">
            <!-- Court Distribution -->
            <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
                <h3 class="font-serif font-semibold text-base text-[#222222] mb-1">Courts &amp; Tribunals</h3>
                <p class="text-xs text-[#766A5E] mb-3">Forum listing across active cases</p>
                <div class="space-y-2">
                    @forelse($courts as $courtName => $cCount)
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-[#FAF8F5] last:border-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-xs text-[#9F8349]">account_balance</span>
                            <span class="text-[#222222] font-medium truncate">{{ $courtName }}</span>
                        </div>
                        <span class="font-mono font-bold text-[#9F8349] shrink-0">{{ $cCount }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-[#766A5E]">No court assignments recorded.</p>
                    @endforelse
                </div>
            </div>

            <!-- Next Scheduled Hearings -->
            <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-serif font-semibold text-base text-[#222222]">Immediate Cause List</h3>
                    <a href="{{ route('reports.hearings') }}" class="text-[11px] text-[#9F8349] font-medium hover:underline">Full Docket &rarr;</a>
                </div>
                <div class="space-y-2.5">
                    @forelse($recentHearings as $hearing)
                    <div class="p-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs">
                        <div class="flex items-center justify-between text-[11px] text-[#766A5E] font-mono mb-1">
                            <span>{{ \Carbon\Carbon::parse($hearing->start_time)->format('d M, h:i A') }}</span>
                            <span class="text-[#8B263E] font-bold">{{ $hearing->event_type ?? 'Hearing' }}</span>
                        </div>
                        <div class="font-semibold text-[#222222] truncate">{{ $hearing->title }}</div>
                        @if($hearing->matter)
                        <div class="text-[11px] text-[#766A5E] truncate mt-0.5">
                            {{ $hearing->matter->case_number }} &bull; {{ $hearing->matter->court_name ?? 'Chambers' }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-[#766A5E]">No upcoming hearings scheduled.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
