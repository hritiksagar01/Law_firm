@extends('portal.layout')

@section('title', 'My Cases & Dockets')
@section('header_title', 'Cases & Dockets')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">My Legal Cases</h1>
            <p class="text-[13px] text-[#646864] mt-1">Active litigation, court dockets, and arbitration matters handled by your legal team.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs font-mono text-[#646864] shadow-xs">
                Total Dockets: <strong class="text-[#1a1a1a]">{{ $matters->count() }}</strong>
            </span>
        </div>
    </div>

    <!-- Connected Metrics Summary Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-3 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs">
        <div class="p-4">
            <div class="text-[12px] text-[#646864]">Active litigation</div>
            <div class="text-[24px] font-medium text-[#1a1a1a] tracking-tight mt-0.5">{{ $matters->whereNotIn('stage', ['Closed', 'Settled'])->count() }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5">Currently before judicial benches</div>
        </div>
        <div class="p-4">
            <div class="text-[12px] text-[#646864]">Certified pleadings</div>
            <div class="text-[24px] font-medium text-[#1a1a1a] tracking-tight mt-0.5">{{ $matters->sum(fn($m) => $m->documents->count()) }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5">Verified filings across all cases</div>
        </div>
        <div class="p-4">
            <div class="text-[12px] text-[#646864]">Upcoming appearances</div>
            <div class="text-[24px] font-medium text-[#1a1a1a] tracking-tight mt-0.5">{{ $matters->sum(fn($m) => $m->events->count()) }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5">Scheduled on electronic cause lists</div>
        </div>
    </div>

    <!-- Matters Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($matters as $matter)
        @php
            $stagePercent = match(strtolower($matter->stage ?? '')) {
                'notice & pleadings', 'pleadings' => '20%',
                'interim relief', 'discovery' => '35%',
                'evidence & arguments', 'evidence' => '55%',
                'framing of issues', 'issues' => '70%',
                'final hearing', 'oral arguments' => '85%',
                'settled', 'closed', 'judgment & decree' => '100%',
                default => '40%'
            };
        @endphp
        <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs hover:border-[#23493a]/40 transition-colors flex flex-col justify-between gap-5">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="font-mono text-xs font-semibold text-[#23493a] bg-[#faf9f5] border border-[#e5e3dc] px-2 py-0.5 rounded">
                        CNR: {{ $matter->case_number }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                        {{ $matter->stage }}
                    </span>
                </div>

                <h2 class="text-[16px] font-semibold text-[#1a1a1a] leading-snug">{{ $matter->title }}</h2>

                <div class="grid grid-cols-2 gap-3 text-xs pt-2.5 border-t border-[#f0eee8]">
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Judicial Venue</span>
                        <span class="font-medium text-[#1a1a1a] mt-0.5 block truncate">{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Lead Counsel</span>
                        <span class="font-medium text-[#1a1a1a] mt-0.5 block">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Practice Discipline</span>
                        <span class="font-medium text-[#1a1a1a] mt-0.5 block truncate">{{ $matter->practice_area }}</span>
                    </div>
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Opened Date</span>
                        <span class="font-medium text-[#1a1a1a] font-mono mt-0.5 block">{{ \Carbon\Carbon::parse($matter->opened_at ?? $matter->created_at)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Visual Stage Step Bar -->
            <div class="bg-[#faf9f5] rounded-md p-3 border border-[#e5e3dc]">
                <div class="flex items-center justify-between text-[11px] mb-1.5">
                    <span class="font-mono text-[10.5px] uppercase text-[#646864] font-medium">Procedural Progress</span>
                    <span class="text-[11.5px] font-semibold text-[#23493a]">{{ $matter->stage }} ({{ $stagePercent }})</span>
                </div>
                <div class="w-full bg-[#e5e3dc] rounded-full h-1.5 overflow-hidden flex">
                    <div class="bg-[#23493a] h-full rounded-full transition-all" style="width: {{ $stagePercent }};"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-[#f0eee8]">
                <div class="flex items-center gap-3 text-xs text-[#646864]">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#23493a]">folder</span>
                        <span>{{ $matter->documents->count() }} filings</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-amber-600">assignment</span>
                        <span>{{ $matter->documentRequests->count() }} requests</span>
                    </span>
                </div>

                <a href="{{ route('portal.matters.show', $matter->id) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] transition-colors shadow-xs">
                    <span>View Case File</span>
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-md border border-[#e5e3dc] p-12 text-center text-[#8a8a8a]">
            <span class="material-symbols-outlined text-4xl mb-3 text-[#e5e3dc]">folder_off</span>
            <h3 class="text-sm font-semibold text-[#1a1a1a]">No Cases Registered</h3>
            <p class="text-xs mt-1">There are no court matters currently filed under this account.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
