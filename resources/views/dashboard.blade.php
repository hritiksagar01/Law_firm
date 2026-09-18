<x-app-layout>
    <x-slot name="title">{{ config('legal.app_name', 'Sharma Legal Chambers') }} — Litigation Briefs &amp; Chambers Docket</x-slot>

    <div class="flex flex-col w-full gap-space-lg text-text-primary">
        
        <!-- Top Executive Overview Bar with Editorial Layout -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md pb-space-sm border-b border-border-hairline/60">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-space-xs text-text-muted font-label-sm text-label-sm uppercase tracking-wider">
                    <span>Chambers of {{ auth()->user()->firm->name ?? 'Sharma Legal' }}</span>
                    <span>•</span>
                    <span>Executive Practice Roster</span>
                    <span>•</span>
                    <span class="text-pine-primary font-medium">Session Term Q2</span>
                </div>
                <h1 class="font-headline-xl text-headline-xl text-primary font-serif font-normal tracking-tight">
                    Litigation Briefs &amp; Chambers Docket
                </h1>
            </div>
            <div class="flex items-center gap-space-sm">
                <div class="hidden sm:flex items-center gap-space-xs px-space-sm py-1.5 rounded bg-surface-card border border-border-hairline shadow-sm text-text-secondary font-label-sm text-label-sm">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-700 animate-pulse"></span>
                    <span>High Court Cause List Live</span>
                </div>
                <a href="{{ route('matters.create') }}" class="flex items-center gap-space-xs px-space-md py-2 rounded bg-pine-primary text-on-primary font-title-sm text-title-sm hover:bg-pine-hover shadow-sm transition-all duration-150">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>File New Matter</span>
                </a>
            </div>
        </div>

        <!-- Key Metrics Ledger Panel (Tone-on-Tone Stack) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
            <!-- Stat 1: Active Case Dossiers -->
            <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between relative overflow-hidden group">
                <div class="flex items-start justify-between">
                    <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Active Case Dossiers</span>
                    <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">gavel</span>
                </div>
                <div class="mt-space-sm">
                    <div class="flex items-baseline gap-2">
                        <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal">{{ $mattersCount ?? $matters->count() }}</span>
                        <span class="text-pine-primary font-label-sm text-label-sm font-medium">+4 this month</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-text-secondary mt-1">High Court, NCLT &amp; District Tribunals</p>
                </div>
                <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                    <span>31 High Court · 17 Tribunals</span>
                    <a href="{{ route('matters.index') }}" class="text-pine-primary font-medium hover:underline cursor-pointer">View registry →</a>
                </div>
            </div>

            <!-- Stat 2: Upcoming Hearings -->
            <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
                <div class="flex items-start justify-between">
                    <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Upcoming Hearings</span>
                    <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">event_upcoming</span>
                </div>
                <div class="mt-space-sm">
                    <div class="flex items-baseline gap-2">
                        <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal">{{ $eventsCount ?? $events->count() }}</span>
                        <span class="px-2 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-800 font-label-sm text-label-sm font-medium">2 Priority Arguments</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-text-secondary mt-1">Scheduled next 5 days across benches</p>
                </div>
                <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                    <span>Earliest: Today 10:30 AM</span>
                    <a href="{{ route('calendar.index') }}" class="text-pine-primary font-medium hover:underline cursor-pointer">Cause list →</a>
                </div>
            </div>

            <!-- Stat 3: Pending Filings -->
            <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
                <div class="flex items-start justify-between">
                    <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Pending Filings</span>
                    <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">assignment_late</span>
                </div>
                <div class="mt-space-sm">
                    <div class="flex items-baseline gap-2">
                        <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal">{{ $tasksCount ?? $tasks->count() }}</span>
                        <span class="text-error font-label-sm text-label-sm font-medium">Due ≤ 5 Days</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-text-secondary mt-1">Caveats, Rejoinders &amp; Submissions</p>
                </div>
                <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                    <span>3 awaiting Senior Counsel clearance</span>
                    <a href="{{ route('tasks.index') }}" class="text-pine-primary font-medium hover:underline cursor-pointer">Review queue →</a>
                </div>
            </div>

            <!-- Stat 4: Realized Billings -->
            <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
                <div class="flex items-start justify-between">
                    <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Realized Billings</span>
                    <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">account_balance_wallet</span>
                </div>
                <div class="mt-space-sm">
                    <div class="flex items-baseline justify-between">
                        <div class="flex items-baseline gap-1">
                            <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal">₹42.8L</span>
                            <span class="font-label-sm text-label-sm text-text-muted">/ ₹58.0L</span>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 font-title-sm text-title-sm">82%</span>
                    </div>
                    <div class="w-full bg-surface-container-high h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-pine-primary h-full rounded-full" style="width: 82%;"></div>
                    </div>
                </div>
                <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                    <span>₹15.2L uncollected across clients</span>
                    <a href="{{ route('billing.index') }}" class="text-pine-primary font-medium hover:underline cursor-pointer">Chambers ledger →</a>
                </div>
            </div>
        </div>

        <!-- Primary Asymmetric Workspace: 70% Litigation Engine & 30% Daily Operations -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
            
            <!-- LEFT COLUMN (70% - Col Span 8) -->
            <div class="lg:col-span-8 flex flex-col gap-space-lg">
                
                <!-- Critical Litigation Dockets & Upcoming Hearings Table Card -->
                <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm overflow-hidden">
                    <!-- Card Header with Subtle Tone Shift -->
                    <div class="px-space-lg py-space-md bg-surface-subtle border-b border-border-hairline flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div>
                            <div class="flex items-center gap-space-xs">
                                <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Priority Docket Matrix</span>
                                <span class="text-text-muted">·</span>
                                <span class="font-caption text-caption text-text-muted">Updated real-time</span>
                            </div>
                            <h2 class="font-headline-md text-headline-md text-primary font-serif font-medium">Critical Litigation Dockets &amp; Upcoming Hearings</h2>
                        </div>
                        <div class="flex items-center gap-space-xs">
                            <a href="{{ route('matters.index') }}" class="px-space-sm py-1 rounded bg-surface-card border border-border-hairline text-text-secondary text-label-sm font-label-sm hover:text-text-primary shadow-sm">
                                All Matters ({{ $matters->count() }})
                            </a>
                            <span class="px-space-sm py-1 rounded bg-pine-primary text-white text-label-sm font-label-sm">
                                Active Hearings
                            </span>
                        </div>
                    </div>

                    <!-- Docket Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-border-hairline text-text-muted font-caption text-caption uppercase tracking-wider">
                                    <th class="py-space-sm px-space-md">Case Title &amp; Registry ID</th>
                                    <th class="py-space-sm px-space-md">Forum / Bench</th>
                                    <th class="py-space-sm px-space-md">Hearing &amp; Status</th>
                                    <th class="py-space-sm px-space-md">Bench &amp; Counsel</th>
                                    <th class="py-space-sm px-space-md text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-subtle font-body-sm text-body-sm">
                                @forelse($matters as $matter)
                                <tr class="hover:bg-surface-subtle transition-colors group">
                                    <td class="py-space-md px-space-md align-top">
                                        <div class="flex flex-col">
                                            <a href="{{ route('matters.show', $matter->id) }}" class="font-title-sm text-title-sm text-text-primary group-hover:text-pine-primary transition-colors">
                                                {{ $matter->title }}
                                            </a>
                                            <span class="font-caption text-caption text-text-muted font-mono mt-0.5">
                                                {{ $matter->case_number }} · {{ $matter->practice_area }}
                                            </span>
                                            @if($matter->client)
                                            <span class="inline-flex items-center gap-1 mt-1 text-text-secondary font-caption text-caption">
                                                <span class="material-symbols-outlined text-[14px] text-text-muted">business</span>
                                                {{ $matter->client->name }}
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-space-md px-space-md align-top">
                                        <div class="flex flex-col">
                                            <span class="font-label-md text-label-md text-text-primary">{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                                            <span class="text-text-muted font-caption text-caption">Courtroom 14 · Commercial Bench</span>
                                        </div>
                                    </td>
                                    <td class="py-space-md px-space-md align-top">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="font-label-sm text-label-sm text-text-primary">Scheduled Term Q2</span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-label-sm text-label-sm inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                {{ $matter->stage }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-space-md px-space-md align-top">
                                        <div class="flex flex-col">
                                            <span class="text-text-primary font-medium">{{ $matter->judge_name ?? "Hon'ble Presiding Bench" }}</span>
                                            <span class="text-pine-primary font-label-sm text-label-sm">{{ $matter->leadAttorney?->name ?? 'Adv. A. Sharma (Lead)' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-space-md px-space-md align-top text-right whitespace-nowrap">
                                        <a href="{{ route('matters.show', $matter->id) }}" class="inline-flex items-center gap-1 font-title-sm text-title-sm text-pine-primary hover:text-pine-hover">
                                            <span>Open Brief</span>
                                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-text-muted text-xs">
                                        No active litigation dockets currently listed for this practice term.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer Context -->
                    <div class="px-space-md py-space-sm bg-surface-subtle border-t border-border-hairline flex items-center justify-between font-caption text-caption text-text-muted">
                        <span>Showing {{ $matters->count() }} active matters requiring direct chamber representation</span>
                        <a href="{{ route('matters.index') }}" class="text-pine-primary font-title-sm text-title-sm hover:underline flex items-center gap-1">
                            <span>View Full Chamber Cause Register</span>
                            <span class="material-symbols-outlined text-[16px]">navigate_next</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Drafts & Filings Tracker (Drafting & Approval Ledger) -->
                <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Chamber Drafting Desk</span>
                            <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Quick Drafts &amp; Filings Tracker</h3>
                        </div>
                        <div class="flex items-center gap-space-sm">
                            <span class="font-caption text-caption text-text-muted">{{ $tasks->count() }} Active items in queue</span>
                            <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded bg-surface-subtle border border-border-hairline text-text-primary text-label-sm font-label-sm hover:bg-surface-container-high transition-colors">
                                + New Pleading Draft
                            </a>
                        </div>
                    </div>

                    <!-- Drafts Itemized Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md pt-space-xs">
                        <!-- Item 1 -->
                        <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm relative">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">AFFIDAVIT</span>
                                    <span class="text-text-muted font-caption text-caption">Due 4:00 PM</span>
                                </div>
                                <h4 class="font-title-sm text-title-sm text-text-primary mt-1">Affidavit in Reply to Contempt Notice</h4>
                                <p class="font-caption text-caption text-text-secondary">Ready for client deponent attestation &amp; notary stamp.</p>
                            </div>
                            <div class="flex flex-col gap-2 pt-2">
                                <div class="flex items-center justify-between text-text-muted font-caption text-caption">
                                    <span>Drafted by Chambers Team</span>
                                    <span class="text-pine-primary font-medium">90% Complete</span>
                                </div>
                                <div class="w-full bg-surface-container-high h-1 rounded-full overflow-hidden">
                                    <div class="bg-pine-primary h-full rounded-full" style="width: 90%;"></div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 text-text-secondary font-label-sm text-label-sm cursor-pointer">
                                        <input checked type="checkbox" class="accent-pine-primary rounded"/>
                                        <span>Advocate Approved</span>
                                    </label>
                                    <span class="text-pine-primary font-title-sm text-title-sm text-[12px]">Sign brief →</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm relative">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">CAVEAT PETITION</span>
                                    <span class="text-error font-caption text-caption font-medium">Urgent Today</span>
                                </div>
                                <h4 class="font-title-sm text-title-sm text-text-primary mt-1">Caveat u/S 148A CPC (NCLT Bench II)</h4>
                                <p class="font-caption text-caption text-text-secondary">Prevents ex-parte interim injunction against debt rollover.</p>
                            </div>
                            <div class="flex flex-col gap-2 pt-2">
                                <div class="flex items-center justify-between text-text-muted font-caption text-caption">
                                    <span>Drafted by Lead Associate</span>
                                    <span class="text-amber-700 font-medium">Requires Vakalatnama</span>
                                </div>
                                <div class="w-full bg-surface-container-high h-1 rounded-full overflow-hidden">
                                    <div class="bg-amber-600 h-full rounded-full" style="width: 60%;"></div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 text-text-secondary font-label-sm text-label-sm cursor-pointer">
                                        <input type="checkbox" class="accent-pine-primary rounded"/>
                                        <span>Vakalatnama Stamped</span>
                                    </label>
                                    <span class="text-pine-primary font-title-sm text-title-sm text-[12px]">E-File →</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item 3 -->
                        <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm relative">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">REJOINDER</span>
                                    <span class="text-text-muted font-caption text-caption">Due Friday</span>
                                </div>
                                <h4 class="font-title-sm text-title-sm text-text-primary mt-1">Rejoinder to Written Statement</h4>
                                <p class="font-caption text-caption text-text-secondary">Citing Hon'ble SC precedent on holographic codicil validity.</p>
                            </div>
                            <div class="flex flex-col gap-2 pt-2">
                                <div class="flex items-center justify-between text-text-muted font-caption text-caption">
                                    <span>Under Senior Review</span>
                                    <span class="text-pine-primary font-medium">Final Proofing</span>
                                </div>
                                <div class="w-full bg-surface-container-high h-1 rounded-full overflow-hidden">
                                    <div class="bg-pine-primary h-full rounded-full" style="width: 80%;"></div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 text-text-secondary font-label-sm text-label-sm cursor-pointer">
                                        <input checked type="checkbox" class="accent-pine-primary rounded"/>
                                        <span>Citation Verified</span>
                                    </label>
                                    <span class="text-pine-primary font-title-sm text-title-sm text-[12px]">Print docket →</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (30% - Col Span 4) -->
            <div class="lg:col-span-4 flex flex-col gap-space-lg">
                
                <!-- Chambers Quick Actions (Deep Pine Accents) -->
                <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                    <div class="flex items-center justify-between">
                        <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Advocate Desk</span>
                        <span class="material-symbols-outlined text-[18px] text-text-muted">bolt</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Chambers Quick Actions</h3>
                    <div class="grid grid-cols-1 gap-2.5">
                        <a href="{{ route('matters.create') }}" class="w-full py-2.5 px-space-md rounded bg-pine-primary text-on-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-pine-hover transition-colors shadow-sm text-left">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">post_add</span>
                                <span>+ File New Matter</span>
                            </span>
                            <span class="text-caption font-caption text-on-primary-container">E-Filing Portal</span>
                        </a>

                        <a href="{{ route('calendar.index') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-pine-primary">timer</span>
                                <span>Log Hearing Appearance</span>
                            </span>
                            <span class="text-caption font-caption text-text-muted">Hourly / Daily</span>
                        </a>

                        <a href="{{ route('billing.index') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-pine-primary">receipt_long</span>
                                <span>Generate Retainer Note</span>
                            </span>
                            <span class="text-caption font-caption text-text-muted">Pro-forma Bill</span>
                        </a>

                        <a href="{{ route('opinions.create') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-pine-primary">draw</span>
                                <span>Record Case Opinion</span>
                            </span>
                            <span class="text-caption font-caption text-text-muted">Confidential Memo</span>
                        </a>
                    </div>
                </div>

                <!-- Court Calendar Today & Tomorrow (Chronological Timetable) -->
                <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Real-Time Registry</span>
                            <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Court Schedule</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-surface-subtle border border-border-hairline text-text-muted font-caption text-caption">IST (UTC+5:30)</span>
                    </div>

                    <div class="flex flex-col gap-space-md relative">
                        @forelse($events->take(3) as $event)
                        <div class="flex gap-space-sm items-start relative">
                            <div class="flex flex-col items-center">
                                <span class="w-2.5 h-2.5 rounded-full {{ $event->is_statutory_deadline ? 'bg-error' : 'bg-pine-primary' }} ring-4 ring-emerald-100 mt-1"></span>
                                <span class="w-0.5 h-16 bg-surface-container-high mt-1"></span>
                            </div>
                            <div class="flex-1 bg-surface-subtle border border-border-hairline rounded p-space-sm flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-title-sm text-title-sm text-text-primary">{{ $event->start_time->format('g:i A') }}</span>
                                    <span class="px-1.5 py-0.5 rounded {{ $event->is_statutory_deadline ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }} font-caption text-caption font-medium">
                                        {{ $event->event_type }}
                                    </span>
                                </div>
                                <p class="font-label-sm text-label-sm text-text-primary">{{ $event->title }}</p>
                                <div class="flex items-center justify-between font-caption text-caption text-text-muted">
                                    <span>{{ $event->location ?? 'Court hearing' }}</span>
                                    <span class="text-pine-primary font-medium">{{ $event->matter?->case_number }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-xs text-text-muted">No hearing items scheduled for today.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Assigned Associates & Bench Status -->
                <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Chambers Roster</span>
                            <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Assigned Associates</h3>
                        </div>
                        <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[18px]">group</span>
                    </div>

                    <div class="flex flex-col gap-space-sm">
                        <!-- Associate 1 -->
                        <div class="flex items-center justify-between p-space-sm rounded border border-border-hairline bg-surface-subtle">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-title-sm text-title-sm">
                                    AS
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-title-sm text-title-sm text-text-primary">Adv. Ananya Sharma</span>
                                    <span class="font-caption text-caption text-text-muted">Managing Counsel · High Court</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-label-sm text-label-sm flex items-center gap-1 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                In Court 14
                            </span>
                        </div>

                        <!-- Associate 2 -->
                        <div class="flex items-center justify-between p-space-sm rounded border border-border-hairline bg-surface-subtle">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-8 h-8 rounded-full bg-surface-container-high text-primary flex items-center justify-center font-title-sm text-title-sm font-semibold">
                                    DO
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-title-sm text-title-sm text-text-primary">Adv. Daniel Okafor</span>
                                    <span class="font-caption text-caption text-text-muted">Senior Retained Counsel</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 font-label-sm text-label-sm flex items-center gap-1 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Briefing Session
                            </span>
                        </div>

                        <!-- Associate 3 -->
                        <div class="flex items-center justify-between p-space-sm rounded border border-border-hairline bg-surface-subtle">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-8 h-8 rounded-full bg-surface-container-high text-primary flex items-center justify-center font-title-sm text-title-sm font-semibold">
                                    LO
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-title-sm text-title-sm text-text-primary">Luis Ortega</span>
                                    <span class="font-caption text-caption text-text-muted">Senior Paralegal · Docket Registry</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-surface-card border border-border-hairline text-text-secondary font-label-sm text-label-sm">
                                Chambers (Drafting)
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between text-text-muted font-caption text-caption">
                        <span>Chamber roster active</span>
                        <a href="{{ route('users.index') }}" class="text-pine-primary hover:underline font-title-sm text-title-sm">Duty roster →</a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
