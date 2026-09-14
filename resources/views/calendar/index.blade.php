<x-app-layout>
    <x-slot name="title">Chambers Calendar &amp; Court Docket — Quire Legal</x-slot>

    <div x-data="{ openEventModal: false }">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e9e8e5] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#727973] uppercase tracking-wider">Judicial Hearings &amp; Statutory Deadlines</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Chambers Calendar &amp; Docket</h1>
                <p class="text-sm text-[#727973]">Trial dates, statutory filing cutoffs, and deposition schedules</p>
            </div>
            <button @click="openEventModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">add_alarm</span>
                <span>Schedule Hearing / Deadline</span>
            </button>
        </div>

        <!-- Calendar View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upcoming Hearings List (2 cols) -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Upcoming Judicial Docket &amp; Hearings</h3>
                        <span class="font-mono text-xs text-[#727973]">Chronological Order</span>
                    </div>

                    <div class="divide-y divide-[#efeeeb]">
                        @forelse($events as $event)
                        <div class="p-5 flex items-start justify-between gap-4 hover:bg-[#faf9f6] transition-colors {{ $event->is_statutory_deadline ? 'border-l-4 border-l-red-600 bg-red-50/20' : '' }}">
                            <div class="flex items-start gap-4">
                                <div class="flex flex-col items-center justify-center w-14 h-14 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] shrink-0">
                                    <span class="font-mono text-[10px] uppercase font-bold text-[#727973]">{{ $event->start_time->format('M') }}</span>
                                    <span class="font-serif text-lg font-bold text-[#1a1c1a]">{{ $event->start_time->format('d') }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-semibold text-[#1a3c2a]">{{ $event->start_time->format('g:i A') }}</span>
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded {{ $event->is_statutory_deadline ? 'bg-red-100 text-red-800 font-bold' : 'bg-[#efeeeb] text-[#424843]' }}">
                                            {{ $event->event_type }}
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-semibold text-[#1a1c1a] mt-1">{{ $event->title }}</h4>
                                    @if($event->matter)
                                    <p class="text-xs text-[#727973] font-medium mt-0.5">{{ $event->matter->case_number }} — {{ $event->matter->title }}</p>
                                    @endif
                                    @if($event->location)
                                    <div class="flex items-center gap-1.5 text-xs text-[#424843] mt-2">
                                        <span class="material-symbols-outlined text-sm text-[#727973]">location_on</span>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-end shrink-0">
                                <span class="text-xs text-[#727973]">{{ $event->start_time->diffForHumans() }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-xs text-[#727973]">No hearings or statutory cutoffs currently on the docket.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Side: Statutory Rules Guide -->
            <div class="flex flex-col gap-6">
                <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-3">Statutory Calculation Guide</h3>
                    <div class="flex flex-col gap-3 text-xs text-[#424843]">
                        <div class="p-3 rounded bg-[#faf9f6] border border-[#efeeeb]">
                            <span class="font-semibold text-[#1a1c1a] block">Fed. R. Civ. P. 56 (Summary Judgment)</span>
                            <p class="text-[11px] text-[#727973] mt-1">Opposition response strictly due 30 days after motion filing unless local court orders specify otherwise.</p>
                        </div>
                        <div class="p-3 rounded bg-[#faf9f6] border border-[#efeeeb]">
                            <span class="font-semibold text-[#1a1c1a] block">Fed. R. Civ. P. 26(a)(1) Initial Disclosures</span>
                            <p class="text-[11px] text-[#727973] mt-1">Due within 14 days after the Rule 26(f) discovery conference.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Hearing Modal -->
        <div x-show="openEventModal" @click.away="openEventModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#e9e8e5] w-full max-w-lg p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#efeeeb] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a]">calendar_month</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Schedule Docket Appearance / Cutoff</h3>
                    </div>
                    <button type="button" @click="openEventModal = false" class="text-[#727973] hover:text-[#1a1c1a]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('calendar.store') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Appearance / Deadline Title</label>
                        <input name="title" required type="text" placeholder="e.g. Oral Argument on Motion for Summary Judgment" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Docket Event Type</label>
                            <select name="event_type" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                                <option value="Hearing" selected>Court Hearing</option>
                                <option value="Trial">Trial / Proceeding</option>
                                <option value="Deposition">Witness Deposition</option>
                                <option value="Filing Cutoff">Statutory Filing Cutoff</option>
                                <option value="Conference">Status Conference</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Date &amp; Scheduled Time</label>
                            <input name="start_time" type="datetime-local" value="{{ now()->addDays(3)->format('Y-m-d\T10:00') }}" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Associated Matter</label>
                            <select name="matter_id" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                                <option value="">General Chambers Event</option>
                                @foreach($matters as $matter)
                                <option value="{{ $matter->id }}">{{ $matter->case_number }} · {{ $matter->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Courtroom / Location / Zoom</label>
                            <input name="location" type="text" placeholder="Courtroom 14B, SDNY / Virtual Zoom" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-2.5 rounded bg-red-50 border border-red-200">
                        <input type="checkbox" id="stat_deadline" name="is_statutory_deadline" value="1" class="w-4 h-4 text-red-600 rounded cursor-pointer accent-red-600"/>
                        <label for="stat_deadline" class="cursor-pointer text-red-900 font-medium">
                            Flag as Jurisdictional / Statutory Rule Cutoff (Strict Non-Waivable)
                        </label>
                    </div>

                    <div class="pt-3 border-t border-[#efeeeb] flex items-center justify-end gap-2">
                        <button type="button" @click="openEventModal = false" class="px-4 py-2 rounded-lg border border-[#c1c8c1] text-[#424843] hover:bg-[#faf9f6]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#1a3c2a] text-white font-semibold hover:bg-[#022616]">Enter on Court Docket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
