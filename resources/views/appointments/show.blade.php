@extends('layouts.app')

@section('title', $appointment->title . ' — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Header Navigation -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('appointments.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349]">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Appointments</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Status Transition Buttons -->
            @if($appointment->status !== 'completed')
            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-xs">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Mark Completed</span>
                </button>
            </form>
            @endif

            @if($appointment->status !== 'adjourned' && $appointment->status !== 'completed')
            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="adjourned">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 shadow-xs">
                    <span class="material-symbols-outlined text-base">schedule</span>
                    <span>Adjourn Session</span>
                </button>
            </form>
            @endif

            @if($appointment->status !== 'cancelled')
            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 text-rose-700 bg-rose-50 text-xs font-semibold hover:bg-rose-100 shadow-xs">
                    <span class="material-symbols-outlined text-base">cancel</span>
                    <span>Cancel</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Appointment Card -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] p-8 shadow-xs space-y-8">
        
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#EFECE6] pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-semibold 
                        @if($appointment->type === 'court_appearance') bg-rose-50 text-rose-700 border border-rose-200
                        @elseif($appointment->type === 'client_consultation') bg-blue-50 text-blue-700 border border-blue-200
                        @elseif($appointment->type === 'mediation') bg-purple-50 text-purple-700 border border-purple-200
                        @else bg-[#FAF8F5] text-[#766A5E] border border-[#EAE4DC] @endif">
                        {{ str_replace('_', ' ', $appointment->type) }}
                    </span>
                    <span class="text-xs font-mono text-[#766A5E]">{{ $appointment->duration_minutes }} Minutes</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">{{ $appointment->title }}</h1>
            </div>

            <div>
                @if($appointment->status === 'completed')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Completed
                </span>
                @elseif($appointment->status === 'adjourned')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Adjourned
                </span>
                @elseif($appointment->status === 'cancelled')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                    Cancelled
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Scheduled
                </span>
                @endif
            </div>
        </div>

        <!-- Schedule & Location Particulars -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC]">
                <span class="text-[10px] font-mono uppercase text-[#766A5E] block mb-1">Scheduled Date &amp; Time</span>
                <span class="font-serif font-bold text-base text-[#222222] block">{{ $appointment->scheduled_at->format('l, d F Y') }}</span>
                <span class="font-mono text-[#9F8349] font-semibold mt-0.5 block">{{ $appointment->scheduled_at->format('h:i A') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC]">
                <span class="text-[10px] font-mono uppercase text-[#766A5E] block mb-1">Venue / Courtroom</span>
                <span class="font-semibold text-sm text-[#222222] block">{{ $appointment->location ?? 'Chambers Conference Room' }}</span>
                <span class="text-[#766A5E] mt-0.5 block">Physical / In-Person</span>
            </div>

            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC]">
                <span class="text-[10px] font-mono uppercase text-[#766A5E] block mb-1">Assigned Advocate</span>
                <span class="font-semibold text-sm text-[#222222] block">{{ $appointment->attorney->name ?? 'Advocate' }}</span>
                <span class="text-[#766A5E] mt-0.5 block">{{ $appointment->attorney->title ?? 'Chambers Counsel' }}</span>
            </div>
        </div>

        <!-- Associated Matter & Client -->
        @if($appointment->matter || $appointment->client)
        <div class="p-4 rounded-xl border border-[#EAE4DC] bg-white flex flex-wrap items-center justify-between gap-4 text-xs">
            @if($appointment->matter)
            <div>
                <span class="text-[10px] font-mono uppercase text-[#766A5E] block">Linked Case File</span>
                <a href="{{ route('matters.show', $appointment->matter) }}" class="font-semibold text-[#9F8349] hover:underline text-sm">
                    {{ $appointment->matter->case_number }} — {{ $appointment->matter->title }}
                </a>
            </div>
            @endif
            @if($appointment->client)
            <div>
                <span class="text-[10px] font-mono uppercase text-[#766A5E] block">Client Attendee</span>
                <span class="font-semibold text-[#222222] text-sm">{{ $appointment->client->name }}</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Agenda & Notes -->
        @if($appointment->notes)
        <div>
            <h3 class="font-serif font-bold text-sm text-[#222222] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349] text-base">notes</span>
                <span>Session Agenda &amp; Notes</span>
            </h3>
            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] leading-relaxed whitespace-pre-line">
                {{ $appointment->notes }}
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
