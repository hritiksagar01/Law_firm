@extends('layouts.app')

@section('title', 'Appointments & Court Hearings — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-rose-600">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span>Chambers Operations</span>
                <span>•</span>
                <span>Consultations &amp; Hearings</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Appointments &amp; Court Appearances</h1>
            <p class="text-sm text-[#766A5E] mt-1">Client consultations, bench appearances, mediation sessions, and strategy briefings</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('calendar.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">calendar_month</span>
                <span>Calendar View</span>
            </a>
            <a href="{{ route('appointments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">add_circle</span>
                <span>Book Appointment</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Today's Docket</span>
            <span class="text-2xl font-serif font-bold text-[#9F8349] mt-1 block">{{ $stats['today'] }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Sessions Scheduled Today</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Upcoming Sessions</span>
            <span class="text-2xl font-serif font-bold text-emerald-700 mt-1 block">{{ $stats['upcoming'] }}</span>
            <span class="text-xs text-emerald-600 mt-2 block font-mono">Ahead on Calendar</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Court Appearances</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $stats['hearings'] }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">High Court &amp; District Benches</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">All Scheduled</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $stats['total'] }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Lifetime Bookings</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-[#EFECE6] shadow-xs mb-6">
        <form method="GET" action="{{ route('appointments.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search appointments, locations, courtroom numbers..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-[#EAE4DC] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] bg-[#FAF8F5]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="time_filter" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="" {{ request('time_filter') == '' ? 'selected' : '' }}>Upcoming First</option>
                    <option value="today" {{ request('time_filter') == 'today' ? 'selected' : '' }}>Today Only</option>
                    <option value="past" {{ request('time_filter') == 'past' ? 'selected' : '' }}>Past Sessions</option>
                </select>
                <select name="type" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Session Types</option>
                    <option value="court_appearance" {{ request('type') == 'court_appearance' ? 'selected' : '' }}>Court Appearance</option>
                    <option value="client_consultation" {{ request('type') == 'client_consultation' ? 'selected' : '' }}>Client Consultation</option>
                    <option value="case_conference" {{ request('type') == 'case_conference' ? 'selected' : '' }}>Case Conference</option>
                    <option value="mediation" {{ request('type') == 'mediation' ? 'selected' : '' }}>Mediation</option>
                    <option value="briefing" {{ request('type') == 'briefing' ? 'selected' : '' }}>Client Briefing</option>
                </select>
                @if(request('q') || request('type') || request('time_filter'))
                <a href="{{ route('appointments.index') }}" class="text-xs text-[#766A5E] hover:text-[#222222] px-2">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Appointments List -->
    <div class="space-y-4">
        @forelse($appointments as $apt)
        <div class="bg-white rounded-2xl border border-[#EFECE6] p-5 shadow-xs hover:border-[#9F8349]/40 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <!-- Date / Time Pill -->
                <div class="w-16 h-16 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC] flex flex-col items-center justify-center text-center shrink-0">
                    <span class="text-[10px] font-mono uppercase text-[#9F8349] font-bold">{{ $apt->scheduled_at->format('M') }}</span>
                    <span class="font-serif font-bold text-xl text-[#222222] leading-none">{{ $apt->scheduled_at->format('d') }}</span>
                    <span class="text-[9px] font-mono text-[#766A5E] mt-0.5">{{ $apt->scheduled_at->format('h:i A') }}</span>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold 
                            @if($apt->type === 'court_appearance') bg-rose-50 text-rose-700 border border-rose-200
                            @elseif($apt->type === 'client_consultation') bg-blue-50 text-blue-700 border border-blue-200
                            @elseif($apt->type === 'mediation') bg-purple-50 text-purple-700 border border-purple-200
                            @else bg-[#FAF8F5] text-[#766A5E] border border-[#EAE4DC] @endif">
                            {{ str_replace('_', ' ', $apt->type) }}
                        </span>
                        <span class="text-xs font-mono text-[#766A5E]">{{ $apt->duration_minutes }} mins</span>
                        @if($apt->scheduled_at->isToday())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#9F8349] text-white">TODAY</span>
                        @endif
                    </div>

                    <a href="{{ route('appointments.show', $apt) }}" class="font-semibold text-base text-[#222222] hover:text-[#9F8349] hover:underline">
                        {{ $apt->title }}
                    </a>

                    <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-[#766A5E]">
                        @if($apt->location)
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-[#9F8349]">location_on</span>
                            <span>{{ $apt->location }}</span>
                        </span>
                        @endif

                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-[#9F8349]">person</span>
                            <span>{{ $apt->attorney->name ?? 'Advocate' }}</span>
                        </span>

                        @if($apt->client)
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-[#766A5E]">account_circle</span>
                            <span>{{ $apt->client->name }}</span>
                        </span>
                        @endif

                        @if($apt->matter)
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-[#766A5E]">gavel</span>
                            <span class="font-mono text-[11px]">{{ $apt->matter->case_number }}</span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                <!-- Status Badge -->
                @if($apt->status === 'completed')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Completed
                </span>
                @elseif($apt->status === 'adjourned')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Adjourned
                </span>
                @elseif($apt->status === 'cancelled')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                    Cancelled
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Scheduled
                </span>
                @endif

                <a href="{{ route('appointments.show', $apt) }}" class="p-2 rounded-lg border border-[#EAE4DC] text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="View Details">
                    <span class="material-symbols-outlined text-base">visibility</span>
                </a>
            </div>
        </div>
        @empty
        <div class="py-16 text-center bg-white rounded-2xl border border-[#EFECE6]">
            <span class="material-symbols-outlined text-5xl text-gray-300 block mb-2">event_busy</span>
            <h3 class="font-serif font-bold text-lg text-[#222222]">No Appointments Found</h3>
            <p class="text-xs text-[#766A5E] mt-1 max-w-md mx-auto">No scheduled court appearances or consultations match your selected filter criteria.</p>
            <a href="{{ route('appointments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36]">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Schedule an Appointment</span>
            </a>
        </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
    <div class="mt-6">
        {{ $appointments->links() }}
    </div>
    @endif

</div>
@endsection
