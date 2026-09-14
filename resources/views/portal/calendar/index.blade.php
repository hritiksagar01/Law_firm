@extends('portal.layout')

@section('title', 'Court Dates & Hearings')

@section('content')
<div class="flex flex-col gap-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Court Hearings &amp; Key Dates</h1>
            <p class="text-xs text-[#727973] mt-1">Procedural dates, cause list appearances, and filing milestones for your legal matters.</p>
        </div>
        <span class="px-3 py-1 bg-white border border-[#e9e8e5] rounded-lg text-xs font-mono text-[#727973]">
            Upcoming Events: <strong class="text-[#1a1c1a]">{{ $events->count() }}</strong>
        </span>
    </div>

    <!-- Hearings List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($events as $event)
        <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-[#1a3c2a] text-[#fed977] flex flex-col items-center justify-center font-mono text-xs font-bold shrink-0">
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                            <span class="text-[9px] uppercase text-white">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-mono text-[#727973]">{{ \Carbon\Carbon::parse($event->start_time)->format('l, Y') }}</span>
                            <span class="text-xs font-bold text-emerald-800">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#f4f3f1] text-[#424843]">
                        {{ $event->event_type ?? 'Court Hearing' }}
                    </span>
                </div>

                <h3 class="text-sm font-bold text-[#1a1c1a] leading-snug">{{ $event->title }}</h3>

                @if($event->matter)
                <div class="p-2.5 rounded-lg bg-[#faf9f6] border border-[#f0eee9] text-xs">
                    <span class="font-mono text-[10px] text-[#1a3c2a] font-bold block">{{ $event->matter->case_number }}</span>
                    <span class="text-[#424843] truncate block mt-0.5">{{ $event->matter->title }}</span>
                </div>
                @endif

                @if($event->location)
                <div class="flex items-start gap-1.5 text-xs text-[#727973]">
                    <span class="material-symbols-outlined text-base text-[#1a3c2a] shrink-0 mt-0.5">location_on</span>
                    <span>{{ $event->location }}</span>
                </div>
                @endif

                @if($event->notes)
                <p class="text-[11px] text-[#727973] italic">"{{ $event->notes }}"</p>
                @endif
            </div>

            @if($event->matter)
            <div class="pt-3 border-t border-[#f4f3f1] flex justify-end">
                <a href="{{ route('portal.matters.show', $event->matter->id) }}" class="text-xs text-[#1a3c2a] hover:underline font-semibold flex items-center gap-1">
                    <span>View Case File</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl border border-[#e9e8e5] p-12 text-center text-[#727973]">
            <span class="material-symbols-outlined text-4xl mb-3 text-[#c1c8c1]">event_busy</span>
            <h3 class="text-sm font-semibold text-[#1a1c1a]">No Scheduled Dates</h3>
            <p class="text-xs mt-1">There are no upcoming court hearings or meetings scheduled on your docket.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
