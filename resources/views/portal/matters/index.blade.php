@extends('portal.layout')

@section('title', 'My Cases')

@section('content')
<div class="flex flex-col gap-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#222222]">My Legal Cases</h1>
            <p class="text-xs text-[#766A5E] mt-1">Active litigation, court dockets, and arbitration matters handled by your legal team.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-white border border-[#EFECE6] rounded-lg text-xs font-mono text-[#766A5E]">
                Total Cases: <strong class="text-[#222222]">{{ $matters->count() }}</strong>
            </span>
        </div>
    </div>

    <!-- Matters Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($matters as $matter)
        <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-5">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs font-bold text-[#845D33] bg-[#F4ECE1]/40 px-2 py-0.5 rounded">
                        {{ $matter->case_number }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F8F4EE] text-[#6D4B27] border border-[#E8DAC8]">
                        {{ $matter->stage }}
                    </span>
                </div>

                <h2 class="text-base font-bold text-[#222222] leading-snug">{{ $matter->title }}</h2>

                <div class="grid grid-cols-2 gap-3 text-xs text-[#766A5E] pt-2 border-t border-[#FAF8F5]">
                    <div>
                        <span class="block text-[10px] uppercase font-mono tracking-wider">Judicial Venue</span>
                        <span class="font-medium text-[#222222] mt-0.5 block truncate">{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-mono tracking-wider">Lead Counsel</span>
                        <span class="font-medium text-[#222222] mt-0.5 block">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-mono tracking-wider">Practice Discipline</span>
                        <span class="font-medium text-[#222222] mt-0.5 block truncate">{{ $matter->practice_area }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-mono tracking-wider">Opened Date</span>
                        <span class="font-medium text-[#222222] mt-0.5 block">{{ \Carbon\Carbon::parse($matter->opened_at)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Visual Stage Step Bar -->
            <div class="bg-[#FAF8F5] rounded-lg p-3 border border-[#f0eee9]">
                <div class="flex items-center justify-between text-[11px] mb-1.5">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E] font-semibold">Progress Stage</span>
                    <span class="text-[11px] font-semibold text-[#845D33]">{{ $matter->stage }}</span>
                </div>
                <div class="w-full bg-[#EFECE6] rounded-full h-1.5 overflow-hidden flex">
                    @php
                        $stagePercent = match(strtolower($matter->stage)) {
                            'notice & pleadings', 'pleadings' => '25%',
                            'interim relief', 'discovery' => '45%',
                            'evidence & arguments', 'trial' => '70%',
                            'final hearing', 'final hearing & order' => '90%',
                            'settled', 'closed' => '100%',
                            default => '35%'
                        };
                    @endphp
                    <div class="bg-[#845D33] h-full rounded-full transition-all" style="width: {{ $stagePercent }};"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-[#FAF8F5]">
                <div class="flex items-center gap-3 text-xs text-[#766A5E]">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">folder</span>
                        <span>{{ $matter->documents->count() }} filings</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-amber-600">assignment</span>
                        <span>{{ $matter->documentRequests->count() }} requests</span>
                    </span>
                </div>

                <a href="{{ route('portal.matters.show', $matter->id) }}" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-lg bg-[#845D33] text-white text-xs font-semibold hover:bg-[#6D4B27] transition-colors shadow-sm">
                    <span>View Case File</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-xl border border-[#EFECE6] p-12 text-center text-[#766A5E]">
            <span class="material-symbols-outlined text-4xl mb-3 text-[#EAE4DC]">folder_off</span>
            <h3 class="text-sm font-semibold text-[#222222]">No Cases Registered</h3>
            <p class="text-xs mt-1">There are no court matters currently filed under this account.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
