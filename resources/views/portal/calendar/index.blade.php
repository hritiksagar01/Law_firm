@extends('portal.layout')

@section('title', 'Court Dates & Hearings')
@section('header_title', 'Litigation Calendar')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Court Hearings &amp; Key Dates</h1>
            <p class="text-[13px] text-[#646864] mt-1">Procedural dates, cause list appearances, and filing milestones for your legal matters.</p>
        </div>
        <span class="px-3 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs font-mono text-[#646864] shadow-xs">
            Upcoming Appearances: <strong class="text-[#1a1a1a]">{{ $events->count() }}</strong>
        </span>
    </div>

    <!-- Hearings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($events as $event)
        <div class="bg-white rounded-md border border-[#e5e3dc] p-5 shadow-xs hover:border-[#23493a]/40 transition-all flex flex-col justify-between gap-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded bg-[#23493a] text-white flex flex-col items-center justify-center font-mono text-xs font-bold shrink-0 shadow-xs">
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                            <span class="text-[8.5px] uppercase text-[#a7f3d0]">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10.5px] font-mono text-[#8a8a8a]">{{ \Carbon\Carbon::parse($event->start_time)->format('l, Y') }}</span>
                            <span class="text-xs font-semibold text-[#ba1a1a] font-mono">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[10.5px] font-mono text-[#1a1a1a]">
                        {{ $event->event_type ?? 'Court Hearing' }}
                    </span>
                </div>

                <h3 class="text-sm font-semibold text-[#1a1a1a] leading-snug">{{ $event->title }}</h3>

                @if($event->matter)
                <div class="p-2.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-xs">
                    <span class="font-mono text-[10.5px] text-[#23493a] font-semibold block">CNR: {{ $event->matter->case_number }}</span>
                    <span class="text-[#1a1a1a] truncate block mt-0.5 font-medium">{{ $event->matter->title }}</span>
                </div>
                @endif

                @if($event->location)
                <div class="flex items-start gap-1.5 text-xs text-[#646864]">
                    <span class="material-symbols-outlined text-base text-[#23493a] shrink-0 mt-0.5">location_on</span>
                    <span>{{ $event->location }}</span>
                </div>
                @endif

                @if($event->notes)
                <p class="text-[11.5px] text-[#8a8a8a] italic">"{{ $event->notes }}"</p>
                @endif
            </div>

            @if($event->matter)
            <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-between">
                <span class="inline-flex items-center gap-1 text-[11px] text-[#065f46]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span> VC Active
                </span>
                <a href="{{ route('portal.matters.show', $event->matter->id) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                    <span>View Case File</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-md border border-[#e5e3dc] p-12 text-center text-[#8a8a8a]">
            <span class="material-symbols-outlined text-4xl mb-3 text-[#e5e3dc]">event_busy</span>
            <h3 class="text-sm font-semibold text-[#1a1a1a]">No Scheduled Dates</h3>
            <p class="text-xs mt-1">There are no upcoming court hearings or milestones currently scheduled on your docket.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
