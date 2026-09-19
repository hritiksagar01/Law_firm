<x-app-layout>
    <x-slot name="title">Chambers Calendar &amp; Court Docket — Quire Legal</x-slot>

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
    }" class="flex flex-col gap-6">

        <!-- Top Command & Docket Header (Template 3) -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-xl border border-border-hairline shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg bg-pine-primary text-white flex items-center justify-center shadow-xs shrink-0">
                        <span class="material-symbols-outlined text-2xl">gavel</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[11px] text-gold-accent font-semibold tracking-wide uppercase">Chambers Docket</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-accent"></span>
                            <span class="font-mono text-[11px] text-text-secondary">Commercial &amp; Appellate Division</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-serif font-bold text-text-primary tracking-tight">Chambers Calendar &amp; Court Docket</h1>
                    </div>
                </div>

                <!-- Temporal Controls -->
                <div class="flex items-center gap-1.5 sm:ml-4 bg-surface-subtle px-3 py-1.5 rounded-lg border border-border-hairline/60">
                    <span class="material-symbols-outlined text-base text-text-muted">calendar_today</span>
                    <span class="font-serif text-sm font-semibold text-text-primary px-1">{{ $currentMonthName }}</span>
                    <span class="text-xs font-mono text-pine-primary bg-emerald-50 px-2 py-0.5 rounded font-semibold ml-1">Live Term</span>
                </div>
            </div>

            <!-- Right Controls: View Switcher + Primary Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- View Toggle (Agenda Causelist vs. Month Grid) -->
                <div class="inline-flex p-1 bg-surface-subtle border border-border-hairline rounded-lg text-text-secondary">
                    <button @click="viewMode = 'agenda'" 
                            :class="viewMode === 'agenda' ? 'bg-white text-pine-primary font-semibold shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs transition-all">
                        <span class="material-symbols-outlined text-base">format_list_bulleted</span>
                        <span>Daily Causelist</span>
                    </button>
                    <button @click="viewMode = 'month'" 
                            :class="viewMode === 'month' ? 'bg-white text-pine-primary font-semibold shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs transition-all">
                        <span class="material-symbols-outlined text-base">calendar_view_month</span>
                        <span>Month Grid</span>
                    </button>
                </div>

                <!-- Action Cluster -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.hearings') }}" class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary hover:bg-surface-container-high transition-colors text-xs font-medium" title="Export Hearings Causelist">
                        <span class="material-symbols-outlined text-base text-text-muted">file_download</span>
                        <span class="hidden sm:inline">Export Causelist</span>
                    </a>
                    <button @click="openScheduleModal = true" class="inline-flex items-center gap-1.5 px-4 h-9 rounded-lg bg-pine-primary text-white hover:bg-pine-hover transition-all text-xs font-semibold shadow-xs">
                        <span class="material-symbols-outlined text-base">calendar_add_on</span>
                        <span>+ Schedule Appearance</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Surface: 12-Column Split (Template 3) -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT CONTROL COLUMN (3 Cols) -->
            <div class="xl:col-span-3 flex flex-col gap-5">
                
                <!-- Docket Emergency Alert Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-red-50/80 via-white to-white border border-red-200/80 p-5 rounded-xl shadow-xs">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-1.5 text-red-700 text-xs font-semibold tracking-wide uppercase font-mono">
                            <span class="material-symbols-outlined text-base animate-pulse">crisis_alert</span>
                            <span>Statutory Filing Alert</span>
                        </div>
                        <span class="font-mono text-[10px] bg-red-100 text-red-800 px-2 py-0.5 rounded font-bold">Rule Cutoff</span>
                    </div>

                    @if($upcomingDeadline)
                    <div class="font-serif text-base font-bold text-text-primary mb-1">
                        {{ $upcomingDeadline->title }}
                    </div>
                    <p class="text-xs text-text-secondary mb-3 leading-relaxed">
                        @if($upcomingDeadline->matter)
                            Matter: <strong class="text-text-primary">{{ $upcomingDeadline->matter->case_number }}</strong> — {{ Str::limit($upcomingDeadline->matter->title, 45) }}.
                        @else
                            Jurisdictional chamber compliance cutoff under statutory appellate rules.
                        @endif
                    </p>
                    <div class="flex items-center justify-between pt-2.5 border-t border-red-100 bg-red-50/40 -mx-5 -mb-5 px-5 py-2.5">
                        <span class="font-mono text-[11px] text-red-800 font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span>
                            {{ $upcomingDeadline->start_time->format('M d, Y · g:i A') }}
                        </span>
                        @if($upcomingDeadline->matter)
                        <a href="{{ route('matters.show', $upcomingDeadline->matter) }}" class="text-[11px] font-semibold text-red-700 hover:underline">View Brief →</a>
                        @endif
                    </div>
                    @else
                    <div class="font-serif text-base font-bold text-text-primary mb-1">
                        Docket Status Orderly
                    </div>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        No critical limitation cutoffs pending within the immediate 48-hour judicial window.
                    </p>
                    @endif
                </div>

                <!-- Mini Monthly Date Picker Widget -->
                <div class="bg-white p-5 rounded-xl border border-border-hairline shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-serif text-sm font-bold text-text-primary">{{ $currentMonthName }}</span>
                        <div class="flex items-center gap-1">
                            <span class="font-mono text-[10px] text-text-muted uppercase">Today: Day {{ $todayDay }}</span>
                        </div>
                    </div>
                    <!-- Mini days grid -->
                    <div class="grid grid-cols-7 text-center font-mono text-[10px] text-text-muted mb-2 font-medium">
                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center font-mono text-xs">
                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <span class="text-[#c5c3bd] py-1">·</span>
                        @endfor

                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $isToday = ($d === $todayDay);
                                $hasEvent = $events->contains(function($e) use ($d) {
                                    return (int) $e->start_time->format('j') === $d;
                                });
                            @endphp
                            <span class="py-1 relative cursor-default rounded transition-colors
                                {{ $isToday ? 'bg-pine-primary text-white font-bold shadow-xs' : ($hasEvent ? 'font-bold text-pine-primary bg-emerald-50/60 hover:bg-emerald-100/60' : 'text-text-primary hover:bg-surface-subtle') }}">
                                {{ $d }}
                                @if($hasEvent && !$isToday)
                                    <span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 bg-gold-accent rounded-full"></span>
                                @endif
                            </span>
                        @endfor
                    </div>
                </div>

                <!-- Practice Area & Court Bench Filters -->
                <div class="bg-white p-5 rounded-xl border border-border-hairline shadow-xs flex flex-col gap-2.5">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-serif text-sm font-bold text-text-primary">Judicial Benches &amp; Fora</span>
                        <span class="font-mono text-[10px] text-gold-accent font-semibold">Active Roster</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-subtle text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded bg-pine-primary"></span>
                            <span class="font-medium text-text-primary">Supreme Court / Appellate</span>
                        </div>
                        <span class="font-mono text-[10px] text-text-muted">Commercial Part</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-subtle text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded bg-[#2563EB]"></span>
                            <span class="font-medium text-text-primary">High Court of Judicature</span>
                        </div>
                        <span class="font-mono text-[10px] text-text-muted">Division Bench</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-subtle text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded bg-[#D97706]"></span>
                            <span class="font-medium text-text-primary">National Company Law Tribunal</span>
                        </div>
                        <span class="font-mono text-[10px] text-text-muted">Insolvency Bench</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-subtle text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded bg-[#7C3AED]"></span>
                            <span class="font-medium text-text-primary">District &amp; Sessions Chambers</span>
                        </div>
                        <span class="font-mono text-[10px] text-text-muted">Courtroom 14</span>
                    </div>
                </div>

                <!-- Visibility Security Scopes (Template 3) -->
                <div class="bg-white p-5 rounded-xl border border-border-hairline shadow-xs flex flex-col gap-2.5">
                    <span class="font-serif text-sm font-bold text-text-primary mb-1">Visibility Security Scopes</span>
                    <div class="flex items-center justify-between p-3 bg-surface-subtle rounded-lg">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-pine-primary ring-2 ring-emerald-200"></span>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-text-primary">Visible to Client Portal</span>
                                <span class="text-[10px] font-mono text-text-muted">Client consultations &amp; updates</span>
                            </div>
                        </div>
                        <span class="font-mono text-xs px-2 py-0.5 bg-white border border-border-hairline rounded font-semibold text-text-primary">{{ $consultationsCount }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-subtle rounded-lg">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-sm text-red-600">lock</span>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-text-primary">Privileged Chambers Docket</span>
                                <span class="text-[10px] font-mono text-text-muted">Firm Only judicial strategies</span>
                            </div>
                        </div>
                        <span class="font-mono text-xs px-2 py-0.5 bg-white border border-border-hairline rounded font-semibold text-text-primary">{{ $events->count() }}</span>
                    </div>
                </div>

                <!-- Docket Density (Template 3) -->
                <div class="bg-white p-5 rounded-xl border border-border-hairline shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-serif text-sm font-bold text-text-primary">Docket Load Density</span>
                        <span class="font-mono text-xs text-gold-accent font-semibold">{{ $events->count() + $appointments->count() }} Listings</span>
                    </div>
                    <div class="flex items-end gap-1.5 h-12 pt-2">
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-emerald-200 transition-colors h-[40%]" title="Week 1: 4 Events"></div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-emerald-200 transition-colors h-[60%]" title="Week 2: 7 Events"></div>
                        <div class="flex-1 bg-pine-primary rounded-t h-[90%]" title="Week 3 (Current): Active Docket"></div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-emerald-200 transition-colors h-[75%]" title="Week 4: 9 Events"></div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-emerald-200 transition-colors h-[45%]" title="Week 5: 5 Events"></div>
                    </div>
                    <div class="flex justify-between font-mono text-[10px] text-text-muted mt-2">
                        <span>W1</span><span>W2</span><span class="text-pine-primary font-bold">W3</span><span>W4</span><span>W5</span>
                    </div>
                </div>

            </div>

            <!-- RIGHT DOCKET WORK SURFACE (9 Cols) -->
            <div class="xl:col-span-9 flex flex-col gap-6">

                <!-- 1. AGENDA / CAUSELIST VIEW (Default) -->
                <div x-show="viewMode === 'agenda'" class="flex flex-col gap-6">
                    
                    <!-- Morning & Afternoon Sessions Split View -->
                    <div class="bg-white rounded-xl border border-border-hairline shadow-xs overflow-hidden">
                        <div class="p-4 sm:p-5 border-b border-border-hairline flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-surface-subtle/50">
                            <div>
                                <span class="font-mono text-[11px] text-gold-accent uppercase font-bold tracking-wider">Judicial Causelist · Daily Roster</span>
                                <h3 class="text-base sm:text-lg font-serif font-bold text-text-primary">Court Appearances &amp; Statutory Deadlines</h3>
                            </div>
                            <span class="font-mono text-xs text-text-secondary bg-white px-3 py-1 rounded border border-border-hairline">
                                {{ $events->count() }} Total Scheduled Listings
                            </span>
                        </div>

                        <!-- Session Listing Rows -->
                        <div class="divide-y divide-border-hairline">
                            @forelse($events as $event)
                            <div class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-surface-subtle transition-colors {{ $event->is_statutory_deadline ? 'border-l-4 border-l-red-600 bg-red-50/20' : '' }}">
                                <div class="flex items-start gap-4">
                                    <!-- Date Badge -->
                                    <div class="flex flex-col items-center justify-center w-14 h-14 rounded-lg bg-surface-subtle border border-border-hairline shrink-0">
                                        <span class="font-mono text-[10px] uppercase font-bold text-gold-accent">{{ $event->start_time->format('M') }}</span>
                                        <span class="font-serif text-lg font-bold text-text-primary">{{ $event->start_time->format('d') }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="font-mono text-xs font-semibold text-pine-primary">{{ $event->start_time->format('g:i A') }}</span>
                                            <span class="font-mono text-[10px] px-2 py-0.5 rounded font-semibold {{ $event->is_statutory_deadline ? 'bg-red-100 text-red-800' : 'bg-surface-container-high text-text-secondary' }}">
                                                {{ $event->event_type }}
                                            </span>
                                            @if($event->is_statutory_deadline)
                                                <span class="font-mono text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded font-bold flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[10px]">warning</span>
                                                    JURISDICTIONAL LIMITATION
                                                </span>
                                            @endif
                                        </div>
                                        <h4 class="text-sm sm:text-base font-serif font-bold text-text-primary hover:text-pine-primary transition-colors cursor-pointer"
                                            @click="viewDetails('{{ addslashes($event->title) }}', '{{ addslashes($event->event_type) }}', '{{ $event->start_time->format('g:i A') }}', '{{ $event->start_time->format('l, F d, Y') }}', '{{ addslashes($event->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($event->matter->case_number ?? 'N/A') }}', '{{ addslashes($event->location ?? '') }}', '{{ addslashes($event->matter->judge_name ?? '') }}', {{ $event->is_statutory_deadline ? 'true' : 'false' }})">
                                            {{ $event->title }}
                                        </h4>
                                        @if($event->matter)
                                        <div class="flex items-center gap-2 mt-1 text-xs text-text-secondary">
                                            <a href="{{ route('matters.show', $event->matter) }}" class="font-mono text-pine-primary hover:underline font-semibold">
                                                {{ $event->matter->case_number }}
                                            </a>
                                            <span>—</span>
                                            <span class="truncate max-w-sm">{{ $event->matter->title }}</span>
                                        </div>
                                        @endif
                                        @if($event->location)
                                        <div class="flex items-center gap-1.5 text-xs text-text-secondary mt-2">
                                            <span class="material-symbols-outlined text-sm text-gold-accent">location_on</span>
                                            <span class="font-medium">{{ $event->location }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 shrink-0">
                                    <span class="font-mono text-xs text-text-muted">{{ $event->start_time->diffForHumans() }}</span>
                                    <button type="button" 
                                            @click="viewDetails('{{ addslashes($event->title) }}', '{{ addslashes($event->event_type) }}', '{{ $event->start_time->format('g:i A') }}', '{{ $event->start_time->format('l, F d, Y') }}', '{{ addslashes($event->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($event->matter->case_number ?? 'N/A') }}', '{{ addslashes($event->location ?? '') }}', '{{ addslashes($event->matter->judge_name ?? '') }}', {{ $event->is_statutory_deadline ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-surface-subtle hover:bg-surface-container-high border border-border-hairline text-text-primary text-[11px] font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        <span>Dossier</span>
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="p-12 text-center text-xs text-text-muted flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-3xl text-text-muted">event_busy</span>
                                <span>No judicial hearings or statutory cutoffs currently on this roster.</span>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Chambers Consultations & Client Conferences (Template 3 Companion) -->
                    <div class="bg-white rounded-xl border border-border-hairline shadow-xs overflow-hidden">
                        <div class="p-4 sm:p-5 border-b border-border-hairline flex items-center justify-between bg-surface-subtle/50">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-pine-primary">event_available</span>
                                <h3 class="text-sm sm:text-base font-serif font-bold text-text-primary">Chambers Consultations &amp; Client Conferences</h3>
                            </div>
                            <a href="{{ route('appointments.create') }}" class="text-xs text-pine-primary font-semibold hover:underline flex items-center gap-1">
                                <span>Book Consultation</span>
                                <span class="material-symbols-outlined text-sm">add</span>
                            </a>
                        </div>

                        <div class="divide-y divide-border-hairline">
                            @forelse($appointments as $apt)
                            <div class="p-4 sm:p-5 flex items-start justify-between gap-4 hover:bg-surface-subtle transition-colors">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-12 h-12 rounded-lg bg-surface-subtle border border-border-hairline flex flex-col items-center justify-center shrink-0">
                                        <span class="font-mono text-[9px] uppercase font-bold text-gold-accent">{{ $apt->scheduled_at->format('M') }}</span>
                                        <span class="font-serif font-bold text-base text-text-primary">{{ $apt->scheduled_at->format('d') }}</span>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="font-mono text-xs font-semibold text-text-primary">{{ $apt->scheduled_at->format('g:i A') }}</span>
                                            <span class="text-[10px] font-mono px-2 py-0.5 rounded capitalize bg-surface-container-high text-text-secondary">
                                                {{ str_replace('_', ' ', $apt->type) }}
                                            </span>
                                            <span class="text-[10px] text-text-muted font-mono">{{ $apt->duration_minutes }}m session</span>
                                        </div>
                                        <a href="{{ route('appointments.show', $apt) }}" class="text-xs sm:text-sm font-serif font-bold text-text-primary hover:text-pine-primary hover:underline">
                                            {{ $apt->title }}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-text-secondary mt-1">
                                            <span>Counsel: <strong class="text-text-primary">{{ $apt->attorney->name ?? 'Lead Advocate' }}</strong></span>
                                            @if($apt->client)
                                            <span>• Client: <strong class="text-text-primary">{{ $apt->client->name }}</strong></span>
                                            @endif
                                            @if($apt->location)
                                            <span>• {{ $apt->location }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono capitalize font-medium
                                    @if($apt->status === 'completed') bg-emerald-50 text-emerald-800 border border-emerald-200
                                    @elseif($apt->status === 'adjourned') bg-amber-50 text-amber-800 border border-amber-200
                                    @elseif($apt->status === 'cancelled') bg-rose-50 text-rose-800 border border-rose-200
                                    @else bg-blue-50 text-blue-800 border border-blue-200 @endif">
                                    {{ $apt->status }}
                                </span>
                            </div>
                            @empty
                            <div class="p-8 text-center text-xs text-text-muted">No client consultations scheduled on the calendar.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- 2. MONTH GRID VIEW (Template 3) -->
                <div x-show="viewMode === 'month'" x-cloak class="flex flex-col bg-white rounded-xl border border-border-hairline shadow-xs overflow-hidden">
                    <!-- Weekday Header -->
                    <div class="grid grid-cols-7 bg-surface-subtle text-center font-serif text-xs font-bold text-text-secondary py-3 border-b border-border-hairline">
                        <div>Sunday</div>
                        <div>Monday</div>
                        <div>Tuesday</div>
                        <div>Wednesday</div>
                        <div>Thursday</div>
                        <div>Friday</div>
                        <div>Saturday</div>
                    </div>

                    <!-- Month Calendar Grid -->
                    <div class="grid grid-cols-7 auto-rows-fr bg-border-hairline gap-px">
                        <!-- Preceding empty days -->
                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="min-h-[110px] bg-surface-subtle/40 p-2 flex flex-col justify-between opacity-40">
                            <span class="font-mono text-xs text-text-muted">·</span>
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
                            <div class="min-h-[110px] bg-white p-2 flex flex-col gap-1.5 hover:bg-surface-subtle transition-colors {{ $isToday ? 'ring-2 ring-pine-primary shadow-xs z-10' : '' }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-semibold {{ $isToday ? 'w-6 h-6 rounded-full bg-pine-primary text-white flex items-center justify-center font-bold shadow-xs' : 'text-text-primary' }}">
                                        {{ $d }}
                                    </span>
                                    @if($isToday)
                                        <span class="font-mono text-[9px] font-bold text-pine-primary uppercase">Today</span>
                                    @endif
                                </div>

                                <!-- Day events -->
                                <div class="flex flex-col gap-1 overflow-y-auto max-h-[80px]">
                                    @foreach($dayEvents as $evt)
                                    <div class="p-1 rounded text-[10px] font-medium leading-tight truncate cursor-pointer transition-transform hover:scale-[1.02]
                                        {{ $evt->is_statutory_deadline ? 'bg-red-100 text-red-800 font-bold border-l-2 border-red-600' : 'bg-emerald-50 text-pine-primary border-l-2 border-pine-primary' }}"
                                         @click="viewDetails('{{ addslashes($evt->title) }}', '{{ addslashes($evt->event_type) }}', '{{ $evt->start_time->format('g:i A') }}', '{{ $evt->start_time->format('l, F d, Y') }}', '{{ addslashes($evt->matter->title ?? 'General Chambers Matter') }}', '{{ addslashes($evt->matter->case_number ?? 'N/A') }}', '{{ addslashes($evt->location ?? '') }}', '{{ addslashes($evt->matter->judge_name ?? '') }}', {{ $evt->is_statutory_deadline ? 'true' : 'false' }})"
                                         title="{{ $evt->title }} ({{ $evt->start_time->format('g:i A') }})">
                                        <span class="font-mono font-semibold">{{ $evt->start_time->format('g:i A') }}</span>
                                        <span>{{ $evt->title }}</span>
                                    </div>
                                    @endforeach

                                    @foreach($dayAppointments as $apt)
                                    <div class="p-1 rounded text-[10px] font-medium leading-tight truncate cursor-pointer bg-amber-50 text-amber-800 border-l-2 border-amber-600"
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

                <!-- Statutory Calculation Guide (Template 3 Footer Reference) -->
                <div class="bg-white rounded-xl border border-border-hairline p-5 shadow-xs flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs text-gold-accent font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">calculate</span>
                            Statutory Limitation &amp; Procedural Rules Engine
                        </span>
                        <span class="font-mono text-[10px] text-text-muted">Civil Procedure Code &amp; Appellate Guidelines</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-lg bg-surface-subtle border border-border-hairline">
                            <span class="font-serif font-bold text-text-primary block text-sm mb-1">Written Statement / Defense Cutoff</span>
                            <p class="text-text-secondary leading-relaxed text-[11px]">Strictly due within 30 days of service of summons, extendable up to 90 days with exceptional chamber leave.</p>
                        </div>
                        <div class="p-3.5 rounded-lg bg-surface-subtle border border-border-hairline">
                            <span class="font-serif font-bold text-text-primary block text-sm mb-1">Commercial Summary Judgment</span>
                            <p class="text-text-secondary leading-relaxed text-[11px]">Affidavit in reply due 14 days following receipt of chamber notice, calculated excluding court vacations.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SCHEDULE HEARING / APPEARANCE MODAL (Preserves all backend inputs & CSRF) -->
        <div x-show="openScheduleModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="openScheduleModal = false" 
             class="fixed inset-0 bg-[#111714]/50 backdrop-blur-xs z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-xl shadow-2xl border border-border-hairline w-full max-w-lg p-6 flex flex-col max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-border-hairline mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-pine-primary text-white flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">calendar_month</span>
                        </div>
                        <div>
                            <span class="font-mono text-[10px] text-gold-accent font-semibold uppercase">Chambers Docket</span>
                            <h3 class="text-base font-serif font-bold text-text-primary">Schedule Docket Appearance / Cutoff</h3>
                        </div>
                    </div>
                    <button type="button" @click="openScheduleModal = false" class="text-text-muted hover:text-text-primary transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('calendar.store') }}" method="POST" class="flex flex-col gap-4 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-text-primary">Appearance / Deadline Title <span class="text-red-500">*</span></label>
                        <input name="title" required type="text" placeholder="e.g. Final Arguments in Commercial Suit" class="w-full px-3.5 py-2.5 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary outline-none focus:border-pine-primary focus:bg-white transition-all"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-semibold text-text-primary">Docket Event Type <span class="text-red-500">*</span></label>
                            <select name="event_type" class="w-full px-3.5 py-2.5 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary outline-none focus:border-pine-primary focus:bg-white transition-all">
                                <option value="Hearing" selected>Court Hearing</option>
                                <option value="Trial">Trial / Proceeding</option>
                                <option value="Deposition">Witness Deposition</option>
                                <option value="Filing Cutoff">Statutory Filing Cutoff</option>
                                <option value="Conference">Status Conference</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-semibold text-text-primary">Date &amp; Scheduled Time <span class="text-red-500">*</span></label>
                            <input name="start_time" type="datetime-local" value="{{ now()->addDays(2)->format('Y-m-d\T10:00') }}" required class="w-full px-3.5 py-2.5 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary outline-none focus:border-pine-primary focus:bg-white transition-all"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-semibold text-text-primary">Associated Matter</label>
                            <select name="matter_id" class="w-full px-3.5 py-2.5 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary outline-none focus:border-pine-primary focus:bg-white transition-all">
                                <option value="">General Chambers Event</option>
                                @foreach($matters as $matter)
                                <option value="{{ $matter->id }}">{{ $matter->case_number }} · {{ Str::limit($matter->title, 25) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-semibold text-text-primary">Courtroom / Forum / Zoom</label>
                            <input name="location" type="text" placeholder="Courtroom 4B, Commercial Division" class="w-full px-3.5 py-2.5 rounded-lg bg-surface-subtle border border-border-hairline text-text-primary outline-none focus:border-pine-primary focus:bg-white transition-all"/>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 p-3 rounded-lg bg-red-50/80 border border-red-200">
                        <input type="checkbox" id="stat_deadline_modal" name="is_statutory_deadline" value="1" class="w-4 h-4 text-red-600 rounded cursor-pointer accent-red-600"/>
                        <label for="stat_deadline_modal" class="cursor-pointer text-red-900 font-semibold text-xs">
                            Flag as Jurisdictional / Statutory Limitation Cutoff (Strict Non-Waivable)
                        </label>
                    </div>

                    <div class="pt-3 border-t border-border-hairline flex items-center justify-end gap-2.5 mt-2">
                        <button type="button" @click="openScheduleModal = false" class="px-4 py-2 rounded-lg border border-border-hairline text-text-secondary hover:bg-surface-subtle transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-pine-primary text-white font-semibold hover:bg-pine-hover shadow-xs transition-colors">Enter on Court Docket</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EVENT DOSSIER DETAIL DRAWER / MODAL (Template 3 Inspection) -->
        <div x-show="openDetailModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="openDetailModal = false" 
             class="fixed inset-0 bg-[#111714]/50 backdrop-blur-xs z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-xl shadow-2xl border border-border-hairline w-full max-w-lg p-6 flex flex-col max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-border-hairline mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pine-primary text-white flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">event_note</span>
                        </div>
                        <div>
                            <span class="font-mono text-[10px] text-gold-accent font-semibold uppercase">Matter Docket Record</span>
                            <h3 class="text-base font-serif font-bold text-text-primary" x-text="selectedEvent.title"></h3>
                        </div>
                    </div>
                    <button type="button" @click="openDetailModal = false" class="text-text-muted hover:text-text-primary transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-4 text-xs">
                    <!-- Matter Association Link Card -->
                    <div class="p-3.5 bg-surface-subtle rounded-lg border border-border-hairline flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-gold-accent text-lg">folder_open</span>
                            <div class="flex flex-col">
                                <span class="font-serif font-bold text-text-primary" x-text="selectedEvent.matter"></span>
                                <span class="font-mono text-[11px] text-text-secondary" x-text="'Case #' + selectedEvent.matterNumber"></span>
                            </div>
                        </div>
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-white border border-border-hairline text-pine-primary font-semibold" x-text="selectedEvent.eventType"></span>
                    </div>

                    <!-- Courtroom & Presiding Bench -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-surface-subtle rounded-lg border border-border-hairline flex flex-col">
                            <span class="font-mono text-[10px] text-text-muted uppercase font-semibold">Forum / Location</span>
                            <span class="font-serif font-bold text-text-primary text-sm mt-1" x-text="selectedEvent.location"></span>
                        </div>
                        <div class="p-3 bg-surface-subtle rounded-lg border border-border-hairline flex flex-col">
                            <span class="font-mono text-[10px] text-text-muted uppercase font-semibold">Presiding Judicial Bench</span>
                            <span class="font-serif font-bold text-text-primary text-sm mt-1" x-text="selectedEvent.judge"></span>
                        </div>
                    </div>

                    <!-- Date & Time Row -->
                    <div class="flex items-center justify-between p-3 bg-surface-subtle rounded-lg border border-border-hairline">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-text-muted text-base">calendar_today</span>
                            <span class="font-medium text-text-primary" x-text="selectedEvent.date"></span>
                        </div>
                        <span class="font-mono font-bold text-pine-primary bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200" x-text="selectedEvent.time"></span>
                    </div>

                    <!-- Statutory Limitation Flag -->
                    <div x-show="selectedEvent.isStatutory" class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-red-900 font-semibold text-xs">
                        <span class="material-symbols-outlined text-base text-red-600">report</span>
                        <span>This event is flagged as a Jurisdictional / Non-Waivable Statutory Cutoff.</span>
                    </div>

                    <div class="pt-3 border-t border-border-hairline flex items-center justify-end gap-2 mt-2">
                        <button type="button" @click="openDetailModal = false" class="px-5 py-2 rounded-lg bg-pine-primary text-white font-semibold hover:bg-pine-hover transition-colors">Close Dossier</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
