@extends('layouts.app')

@section('title', 'Chambers Calendar & Court Docket — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Litigation Calendar')

@section('content')
@php
    $upcomingDeadline = $events->firstWhere('is_statutory_deadline', true) ?? $events->first();
    $statutoryCount = $events->where('is_statutory_deadline', true)->count();
    $hearingsCount = $events->where('event_type', 'Hearing')->count();
    $consultationsCount = $appointments->count();
    $currentMonthName = now()->format('F Y');
    $todayDay = (int) now()->format('j');
    $daysInMonth = now()->daysInMonth;
    $firstDayOfWeek = (int) now()->startOfMonth()->format('w'); // 0 (Sun) to 6 (Sat)
@endphp

<div x-data="{
    viewMode: 'agenda', // 'agenda' or 'month'
    openScheduleModal: false,
    openDetailModal: false,
    selectedEvent: {
        title: '',
        eventType: '',
        time: '',
        date: '',
        matter: '',
        matterNumber: '',
        location: '',
        judge: '',
        isStatutory: false
    },
    viewDetails(title, type, time, date, matter, matterNumber, location, judge, isStatutory) {
        this.selectedEvent = {
            title: title,
            eventType: type,
            time: time,
            date: date,
            matter: matter,
            matterNumber: matterNumber,
            location: location || 'Chambers Courtroom',
            judge: judge || 'Presiding Judicial Bench',
            isStatutory: !!isStatutory
        };
        this.openDetailModal = true;
    }
}" class="flex flex-col gap-6 text-[#1a1a1a]">

    <!-- Top Command & Docket Header -->
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-md border border-[#e5e3dc] shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-[#23493a] text-white flex items-center justify-center shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-xl">gavel</span>
                </div>
                <div>
                    <h1 class="text-[20px] sm:text-[24px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Chambers Calendar &amp; Court Docket</h1>
                    <p class="text-[12px] text-[#646864] mt-0.5 font-sans">Commercial &amp; Appellate Division judicial appearances</p>
                </div>
            </div>

            <!-- Temporal Controls -->
            <div class="flex items-center gap-1.5 sm:ml-4 bg-[#faf8f5] px-3 py-1.5 rounded-md border border-[#e5e3dc]">
                <span class="material-symbols-outlined text-base text-[#8a8a8a]">calendar_today</span>
                <span class="text-xs font-semibold text-[#1a1a1a] px-1">{{ $currentMonthName }}</span>
                <span class="text-[10.5px] font-mono text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full font-semibold ml-1">Live Term</span>
            </div>
        </div>

        <!-- Right Controls: View Switcher + Primary Actions -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- View Toggle -->
            <div class="inline-flex p-1 bg-[#faf8f5] border border-[#e5e3dc] rounded-md text-[#646864]">
                <button @click="viewMode = 'agenda'" 
                        :class="viewMode === 'agenda' ? 'bg-white text-[#23493a] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a]'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">format_list_bulleted</span>
                    <span>Daily Causelist</span>
                </button>
                <button @click="viewMode = 'month'" 
                        :class="viewMode === 'month' ? 'bg-white text-[#23493a] font-semibold shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a]'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">calendar_view_month</span>
                    <span>Month Grid</span>
                </button>
            </div>

            <!-- Action Cluster -->
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.hearings') }}" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5" title="Export Hearings Causelist">
                    <span class="material-symbols-outlined text-base text-[#8a8a8a]">file_download</span>
                    <span class="hidden sm:inline">Export Causelist</span>
                </a>
                <button @click="openScheduleModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-base">calendar_add_on</span>
                    <span>+ Schedule Appearance</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Surface: 12-Column Split -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT CONTROL COLUMN (3 Cols) -->
        <div class="xl:col-span-3 flex flex-col gap-5">
            
            <!-- Docket Emergency Alert Card -->
            <div class="border border-red-200 bg-red-50/40 p-5 rounded-md shadow-xs">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-1.5 text-red-700 text-xs font-semibold tracking-wide uppercase font-mono">
                        <span class="material-symbols-outlined text-base animate-pulse">crisis_alert</span>
                        <span>Statutory Filing Alert</span>
                    </div>
                    <span class="font-mono text-[10px] bg-red-100 text-red-800 px-2 py-0.5 rounded font-bold">Rule Cutoff</span>
                </div>

                @if($upcomingDeadline)
                <div class="text-[14px] font-semibold text-[#1a1a1a] mb-1">
                    {{ $upcomingDeadline->title }}
                </div>
                <p class="text-xs text-[#646864] mb-3 leading-relaxed">
                    @if($upcomingDeadline->matter)
                        Matter: <strong class="text-[#1a1a1a]">{{ $upcomingDeadline->matter->case_number }}</strong> — {{ Str::limit($upcomingDeadline->matter->title, 45) }}.
                    @else
                        Jurisdictional chamber compliance cutoff under statutory appellate rules.
                    @endif
                </p>
                <div class="flex items-center justify-between pt-2.5 border-t border-red-200/60 -mx-5 -mb-5 px-5 py-2.5 bg-red-50/70">
                    <span class="font-mono text-[11px] text-red-800 font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        {{ $upcomingDeadline->start_time->format('M d, Y · g:i A') }}
                    </span>
                    @if($upcomingDeadline->matter)
                    <a href="{{ route('matters.show', $upcomingDeadline->matter) }}" class="text-[11px] font-semibold text-red-700 hover:underline">View Brief &rarr;</a>
                    @endif
                </div>
                @else
                <div class="text-[14px] font-semibold text-[#1a1a1a] mb-1">
                    Docket Status Orderly
                </div>
                <p class="text-xs text-[#646864] leading-relaxed">
                    No critical limitation cutoffs pending within the immediate 48-hour judicial window.
                </p>
                @endif
            </div>

            <!-- Mini Monthly Date Picker Widget -->
            <div class="bg-white p-5 rounded-md border border-[#e5e3dc] shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-[#1a1a1a]">{{ $currentMonthName }}</span>
                    <span class="font-mono text-[10px] text-[#8a8a8a] uppercase">Day {{ $todayDay }}</span>
                </div>
                <!-- Mini days grid -->
                <div class="grid grid-cols-7 text-center font-mono text-[10px] text-[#8a8a8a] mb-2 font-medium">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center font-mono text-xs">
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <span class="text-[#c5c3bd] py-1">&middot;</span>
                    @endfor

                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $isToday = ($d === $todayDay);
                            $hasEvent = $events->contains(function($e) use ($d) {
                                return (int) $e->start_time->format('j') === $d;
                            });
                        @endphp
                        <span class="py-1 relative cursor-default rounded transition-colors
                            {{ $isToday ? 'bg-[#23493a] text-white font-bold shadow-xs' : ($hasEvent ? 'font-bold text-[#23493a] bg-[#ecfdf5] hover:bg-[#d1fae5]' : 'text-[#1a1a1a] hover:bg-[#faf8f5]') }}">
                            {{ $d }}
                            @if($hasEvent && !$isToday)
                                <span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 bg-[#23493a] rounded-full"></span>
                            @endif
                        </span>
                    @endfor
                </div>
            </div>

            <!-- Practice Area & Court Bench Filters -->
            <div class="bg-white p-5 rounded-md border border-[#e5e3dc] shadow-xs flex flex-col gap-2.5">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-[#1a1a1a]">Judicial Benches &amp; Fora</span>
                    <span class="font-mono text-[10px] text-[#23493a] font-semibold">Active Roster</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-md bg-[#faf8f5] text-xs border border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded bg-[#23493a]"></span>
                        <span class="font-medium text-[#1a1a1a]">Supreme Court / Appellate</span>
                    </div>
                    <span class="font-mono text-[10px] text-[#8a8a8a]">Commercial Part</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-md bg-[#faf8f5] text-xs border border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded bg-[#2563eb]"></span>
                        <span class="font-medium text-[#1a1a1a]">High Court of Judicature</span>
                    </div>
                    <span class="font-mono text-[10px] text-[#8a8a8a]">Division Bench</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-md bg-[#faf8f5] text-xs border border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded bg-[#d97706]"></span>
                        <span class="font-medium text-[#1a1a1a]">NCLT / Insolvency Bench</span>
                    </div>
                    <span class="font-mono text-[10px] text-[#8a8a8a]">IBC Matters</span>
                </div>
            </div>

            <!-- Visibility Security Scopes -->
            <div class="bg-white p-5 rounded-md border border-[#e5e3dc] shadow-xs flex flex-col gap-2.5">
                <span class="text-xs font-semibold text-[#1a1a1a] mb-1">Visibility Security Scopes</span>
                <div class="flex items-center justify-between p-2.5 bg-[#faf8f5] rounded-md border border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#23493a]"></span>
                        <div class="flex flex-col">
                            <span class="text-xs font-medium text-[#1a1a1a]">Client Visible</span>
                            <span class="text-[10px] font-mono text-[#8a8a8a]">Portal calendar sync</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs px-2 py-0.5 bg-white border border-[#e5e3dc] rounded font-semibold text-[#1a1a1a]">{{ $consultationsCount }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 bg-[#faf8f5] rounded-md border border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-sm text-[#ba1a1a]">lock</span>
                        <div class="flex flex-col">
                            <span class="text-xs font-medium text-[#1a1a1a]">Privileged Chambers</span>
                            <span class="text-[10px] font-mono text-[#8a8a8a]">Firm only strategic</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs px-2 py-0.5 bg-white border border-[#e5e3dc] rounded font-semibold text-[#1a1a1a]">{{ $events->count() }}</span>
                </div>
            </div>

        </div>

        <!-- RIGHT DOCKET WORK SURFACE (9 Cols) -->
        <div class="xl:col-span-9 flex flex-col gap-6">

            <!-- 1. AGENDA / CAUSELIST VIEW -->
            <div x-show="viewMode === 'agenda'" class="flex flex-col gap-6">
                
                <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-[#f0eee8] flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-[#faf8f5]">
                        <div>
                            <span class="font-mono text-[11px] text-[#23493a] uppercase font-bold tracking-wider">Judicial Causelist &middot; Daily Roster</span>
                            <h3 class="text-base font-semibold text-[#1a1a1a]">Court Appearances &amp; Statutory Deadlines</h3>
                        </div>
                        <span class="font-mono text-xs text-[#646864] bg-white px-3 py-1 rounded border border-[#e5e3dc]">
                            {{ $events->count() }} Scheduled Listings
                        </span>
                    </div>

                    <!-- Session Listing Rows -->
                    <div class="divide-y divide-[#f0eee8]">
                        @forelse($events as $event)
                        <div class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-[#faf9f5] transition-colors {{ $event->is_statutory_deadline ? 'border-l-4 border-l-red-600 bg-red-50/20' : '' }}">
                            <div class="flex items-start gap-4">
                                <!-- Date Badge -->
                                <div class="flex flex-col items-center justify-center w-12 h-12 rounded-md bg-[#faf8f5] border border-[#e5e3dc] shrink-0">
                                    <span class="font-mono text-[9.5px] uppercase font-bold text-[#23493a]">{{ $event->start_time->format('M') }}</span>
                                    <span class="text-base font-semibold text-[#1a1a1a] leading-tight">{{ $event->start_time->format('d') }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-semibold text-[#23493a]">{{ $event->start_time->format('g:i A') }}</span>
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded font-medium {{ $event->is_statutory_deadline ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]' }}">
                                            {{ $event->event_type }}
                                        </span>
                                        @if($event->is_statutory_deadline)
                                            <span class="font-mono text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded font-bold flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px]">warning</span>
                                                JURISDICTIONAL LIMITATION
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="text-[14px] font-semibold text-[#1a1a1a] hover:text-[#23493a] transition-colors cursor-pointer"
                                        @click="viewDetails('{{ addslashes($event->title) }}', '{{ addslashes($event->event_type) }}', '{{ $event->start_time->format('g:i A') }}', '{{ $event->start_time->format('l, F d, Y') }}', '{{ addslashes($event->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($event->matter->case_number ?? 'N/A') }}', '{{ addslashes($event->location ?? '') }}', '{{ addslashes($event->matter->judge_name ?? '') }}', {{ $event->is_statutory_deadline ? 'true' : 'false' }})">
                                        {{ $event->title }}
                                    </h4>
                                    @if($event->matter)
                                    <div class="flex items-center gap-2 mt-1 text-xs text-[#646864]">
                                        <a href="{{ route('matters.show', $event->matter) }}" class="font-mono text-[#23493a] hover:underline font-semibold">
                                            {{ $event->matter->case_number }}
                                        </a>
                                        <span>&middot;</span>
                                        <span class="truncate max-w-sm">{{ $event->matter->title }}</span>
                                    </div>
                                    @endif
                                    @if($event->location)
                                    <div class="flex items-center gap-1.5 text-xs text-[#8a8a8a] mt-1.5">
                                        <span class="material-symbols-outlined text-sm text-[#23493a]">location_on</span>
                                        <span class="font-medium text-[#1a1a1a]">{{ $event->location }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 shrink-0">
                                <span class="font-mono text-xs text-[#8a8a8a]">{{ $event->start_time->diffForHumans() }}</span>
                                <button type="button" 
                                        @click="viewDetails('{{ addslashes($event->title) }}', '{{ addslashes($event->event_type) }}', '{{ $event->start_time->format('g:i A') }}', '{{ $event->start_time->format('l, F d, Y') }}', '{{ addslashes($event->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($event->matter->case_number ?? 'N/A') }}', '{{ addslashes($event->location ?? '') }}', '{{ addslashes($event->matter->judge_name ?? '') }}', {{ $event->is_statutory_deadline ? 'true' : 'false' }})"
                                        class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                    <span>Dossier</span>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="p-12 text-center text-xs text-[#8a8a8a] flex flex-col items-center gap-2">
                            <span class="material-symbols-outlined text-3xl text-[#8a8a8a]">event_busy</span>
                            <span>No judicial hearings or statutory cutoffs currently on this roster.</span>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Chambers Consultations & Client Conferences -->
                <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-[#f0eee8] flex items-center justify-between bg-[#faf8f5]">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-[#23493a]">event_available</span>
                            <h3 class="text-sm sm:text-base font-semibold text-[#1a1a1a]">Chambers Consultations &amp; Client Conferences</h3>
                        </div>
                        <a href="{{ route('appointments.create') }}" class="text-xs text-[#23493a] font-medium hover:underline flex items-center gap-1">
                            <span>Book Consultation</span>
                            <span class="material-symbols-outlined text-sm">add</span>
                        </a>
                    </div>

                    <div class="divide-y divide-[#f0eee8]">
                        @forelse($appointments as $apt)
                        <div class="p-4 sm:p-5 flex items-start justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                            <div class="flex items-start gap-3.5">
                                <div class="w-12 h-12 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex flex-col items-center justify-center shrink-0">
                                    <span class="font-mono text-[9px] uppercase font-bold text-[#23493a]">{{ $apt->scheduled_at->format('M') }}</span>
                                    <span class="font-medium text-base text-[#1a1a1a]">{{ $apt->scheduled_at->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-semibold text-[#1a1a1a]">{{ $apt->scheduled_at->format('g:i A') }}</span>
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded capitalize bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]">
                                            {{ str_replace('_', ' ', $apt->type) }}
                                        </span>
                                        <span class="text-[10px] text-[#8a8a8a] font-mono">{{ $apt->duration_minutes }}m session</span>
                                    </div>
                                    <a href="{{ route('appointments.show', $apt) }}" class="text-xs sm:text-sm font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                        {{ $apt->title }}
                                    </a>
                                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-[#646864] mt-1">
                                        <span>Counsel: <strong class="text-[#1a1a1a]">{{ $apt->attorney->name ?? 'Lead Advocate' }}</strong></span>
                                        @if($apt->client)
                                        <span>&middot; Client: <strong class="text-[#1a1a1a]">{{ $apt->client->name }}</strong></span>
                                        @endif
                                        @if($apt->location)
                                        <span>&middot; {{ $apt->location }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize font-medium
                                @if($apt->status === 'completed') bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]
                                @elseif($apt->status === 'adjourned') bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]
                                @elseif($apt->status === 'cancelled') bg-red-50 text-red-700 border border-red-200
                                @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                                {{ $apt->status }}
                            </span>
                        </div>
                        @empty
                        <div class="p-8 text-center text-xs text-[#8a8a8a]">No client consultations scheduled on the calendar.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 2. MONTH GRID VIEW -->
            <div x-show="viewMode === 'month'" x-cloak class="flex flex-col bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden">
                <!-- Weekday Header -->
                <div class="grid grid-cols-7 bg-[#faf8f5] text-center text-xs font-semibold text-[#646864] py-3 border-b border-[#f0eee8]">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>

                <!-- Month Calendar Grid -->
                <div class="grid grid-cols-7 auto-rows-fr bg-[#e5e3dc] gap-px">
                    <!-- Preceding empty days -->
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="min-h-[110px] bg-[#faf8f5]/40 p-2 flex flex-col justify-between opacity-40">
                        <span class="font-mono text-xs text-[#8a8a8a]">&middot;</span>
                    </div>
                    @endfor

                    <!-- Active days in month -->
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $isToday = ($d === $todayDay);
                            $dayEvents = $events->filter(function($e) use ($d) {
                                return (int) $e->start_time->format('j') === $d;
                            });
                            $dayAppointments = $appointments->filter(function($a) use ($d) {
                                return (int) $a->scheduled_at->format('j') === $d;
                            });
                        @endphp
                        <div class="min-h-[110px] bg-white p-2 flex flex-col gap-1.5 hover:bg-[#faf8f5] transition-colors {{ $isToday ? 'ring-2 ring-[#23493a] z-10' : '' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-semibold {{ $isToday ? 'w-6 h-6 rounded-full bg-[#23493a] text-white flex items-center justify-center font-bold shadow-xs' : 'text-[#1a1a1a]' }}">
                                    {{ $d }}
                                </span>
                                @if($isToday)
                                    <span class="font-mono text-[9px] font-bold text-[#23493a] uppercase">Today</span>
                                @endif
                            </div>

                            <!-- Day events -->
                            <div class="flex flex-col gap-1 overflow-y-auto max-h-[80px]">
                                @foreach($dayEvents as $evt)
                                <div class="p-1 rounded text-[10px] font-medium leading-tight truncate cursor-pointer transition-transform hover:scale-[1.02]
                                    {{ $evt->is_statutory_deadline ? 'bg-red-50 text-red-800 font-bold border-l-2 border-red-600' : 'bg-[#ecfdf5] text-[#065f46] border-l-2 border-[#23493a]' }}"
                                     @click="viewDetails('{{ addslashes($evt->title) }}', '{{ addslashes($evt->event_type) }}', '{{ $evt->start_time->format('g:i A') }}', '{{ $evt->start_time->format('l, F d, Y') }}', '{{ addslashes($evt->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($evt->matter->case_number ?? 'N/A') }}', '{{ addslashes($evt->location ?? '') }}', '{{ addslashes($evt->matter->judge_name ?? '') }}', {{ $evt->is_statutory_deadline ? 'true' : 'false' }})"
                                     title="{{ $evt->title }} ({{ $evt->start_time->format('g:i A') }})">
                                    <span class="font-mono font-semibold">{{ $evt->start_time->format('g:i A') }}</span>
                                    <span>{{ $evt->title }}</span>
                                </div>
                                @endforeach

                                @foreach($dayAppointments as $apt)
                                <div class="p-1 rounded text-[10px] font-medium leading-tight truncate cursor-pointer bg-[#fbf3db] text-[#634812] border-l-2 border-[#d97706]"
                                     title="Consultation: {{ $apt->title }}">
                                    <span class="font-mono font-semibold">{{ $apt->scheduled_at->format('g:i A') }}</span>
                                    <span>{{ $apt->title }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

        </div>
    </div>

    <!-- SCHEDULE HEARING / APPEARANCE MODAL -->
    <div x-show="openScheduleModal" 
         x-cloak 
         @click.away="openScheduleModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-[#23493a] text-white flex items-center justify-center">
                        <span class="material-symbols-outlined text-base">calendar_month</span>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Schedule Docket Appearance / Cutoff</h3>
                    </div>
                </div>
                <button type="button" @click="openScheduleModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('calendar.store') }}" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="font-semibold text-[#1a1a1a]">Appearance / Deadline Title <span class="text-red-500">*</span></label>
                    <input name="title" required type="text" placeholder="e.g. Final Arguments in Commercial Suit" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-[#1a1a1a] outline-none focus:border-[#23493a]"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[#1a1a1a]">Docket Event Type <span class="text-red-500">*</span></label>
                        <select name="event_type" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-[#1a1a1a] outline-none focus:border-[#23493a]">
                            <option value="Hearing" selected>Court Hearing</option>
                            <option value="Trial">Trial / Proceeding</option>
                            <option value="Deposition">Witness Deposition</option>
                            <option value="Filing Cutoff">Statutory Filing Cutoff</option>
                            <option value="Conference">Status Conference</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[#1a1a1a]">Date &amp; Scheduled Time <span class="text-red-500">*</span></label>
                        <input name="start_time" type="datetime-local" value="{{ now()->addDays(2)->format('Y-m-d\T10:00') }}" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-[#1a1a1a] outline-none focus:border-[#23493a]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[#1a1a1a]">Associated Matter</label>
                        <select name="matter_id" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-[#1a1a1a] outline-none focus:border-[#23493a]">
                            <option value="">General Chambers Event</option>
                            @foreach($matters as $matter)
                            <option value="{{ $matter->id }}">{{ $matter->case_number }} &middot; {{ Str::limit($matter->title, 25) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[#1a1a1a]">Courtroom / Forum / Zoom</label>
                        <input name="location" type="text" placeholder="Courtroom 4B, Commercial Division" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-[#1a1a1a] outline-none focus:border-[#23493a]"/>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 p-3 rounded-md bg-red-50/80 border border-red-200">
                    <input type="checkbox" id="stat_deadline_modal" name="is_statutory_deadline" value="1" class="w-4 h-4 text-red-600 rounded cursor-pointer accent-red-600"/>
                    <label for="stat_deadline_modal" class="cursor-pointer text-red-900 font-medium text-xs">
                        Flag as Jurisdictional / Statutory Limitation Cutoff (Strict Non-Waivable)
                    </label>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2.5 mt-2">
                    <button type="button" @click="openScheduleModal = false" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Enter on Court Docket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EVENT DOSSIER DETAIL DRAWER / MODAL -->
    <div x-show="openDetailModal" 
         x-cloak 
         @click.away="openDetailModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-[#23493a] text-white flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-base">event_note</span>
                    </div>
                    <div>
                        <span class="font-mono text-[10px] text-[#23493a] font-semibold uppercase">Matter Docket Record</span>
                        <h3 class="text-[15px] font-semibold text-[#1a1a1a]" x-text="selectedEvent.title"></h3>
                    </div>
                </div>
                <button type="button" @click="openDetailModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <div class="flex flex-col gap-4 text-xs">
                <!-- Matter Association Link Card -->
                <div class="p-3.5 bg-[#faf8f5] rounded-md border border-[#e5e3dc] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">folder_open</span>
                        <div class="flex flex-col">
                            <span class="font-semibold text-[#1a1a1a]" x-text="selectedEvent.matter"></span>
                            <span class="font-mono text-[11px] text-[#646864]" x-text="'Case #' + selectedEvent.matterNumber"></span>
                        </div>
                    </div>
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-white border border-[#e5e3dc] text-[#23493a] font-semibold" x-text="selectedEvent.eventType"></span>
                </div>

                <!-- Courtroom & Presiding Bench -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-[#faf8f5] rounded-md border border-[#e5e3dc] flex flex-col">
                        <span class="font-mono text-[10px] text-[#8a8a8a] uppercase font-semibold">Forum / Location</span>
                        <span class="font-semibold text-[#1a1a1a] text-xs mt-1" x-text="selectedEvent.location"></span>
                    </div>
                    <div class="p-3 bg-[#faf8f5] rounded-md border border-[#e5e3dc] flex flex-col">
                        <span class="font-mono text-[10px] text-[#8a8a8a] uppercase font-semibold">Presiding Bench</span>
                        <span class="font-semibold text-[#1a1a1a] text-xs mt-1" x-text="selectedEvent.judge"></span>
                    </div>
                </div>

                <!-- Date & Time Row -->
                <div class="flex items-center justify-between p-3 bg-[#faf8f5] rounded-md border border-[#e5e3dc]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#8a8a8a] text-base">calendar_today</span>
                        <span class="font-medium text-[#1a1a1a]" x-text="selectedEvent.date"></span>
                    </div>
                    <span class="font-mono font-semibold text-[#065f46] bg-[#ecfdf5] px-2.5 py-1 rounded border border-[#a7f3d0]" x-text="selectedEvent.time"></span>
                </div>

                <!-- Statutory Limitation Flag -->
                <div x-show="selectedEvent.isStatutory" class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-md text-red-900 font-medium text-xs">
                    <span class="material-symbols-outlined text-base text-red-600">report</span>
                    <span>This event is flagged as a Jurisdictional / Non-Waivable Statutory Cutoff.</span>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2 mt-2">
                    <button type="button" @click="openDetailModal = false" class="btn-primary h-9 px-4 text-xs">Close Dossier</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
