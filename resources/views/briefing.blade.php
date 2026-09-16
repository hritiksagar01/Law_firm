<x-app-layout>
    <x-slot name="title">The Chambers Daily Broadsheet — Executive Legal Intelligence</x-slot>

    <!-- Newspaper Style Masthead (Stitch Spec) -->
    <div class="max-w-5xl mx-auto pb-12">
        <div class="border-b-4 border-double border-[#222222] pb-4 mb-6 text-center">
            <div class="flex items-center justify-between font-mono text-xs text-[#766A5E] border-b border-[#EFECE6] pb-2 mb-3">
                <span>VOL. XIV · NO. 248</span>
                <span class="font-serif italic">The Daily Practice &amp; Docket Intelligence</span>
                <span>SAN FRANCISCO / NEW YORK</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-[#222222] tracking-tight uppercase">The Chambers Broadsheet</h1>
            <p class="text-xs font-mono text-[#766A5E] mt-2 uppercase tracking-widest">
                Wednesday, October 15, 2026 · Confidential Executive Briefing for Margaret Chen, Senior Partner
            </p>
        </div>

        <!-- 3-Column Broadsheet Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-[#EFECE6]">
            
            <!-- Column 1: Lead Editorial / Urgent Court Filings -->
            <div class="pr-0 md:pr-4 flex flex-col gap-4">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-red-800 border-b border-red-200 pb-1">Critical Filing Deadlines Today</span>
                <div class="flex flex-col gap-2">
                    <h2 class="text-xl font-serif font-bold text-[#222222] leading-tight">
                        Marsh v. Castellan: Statutory Reply Brief Due by 5:00 PM PST
                    </h2>
                    <p class="text-xs text-[#554D45] leading-relaxed">
                        Pursuant to Local Rule 7-3 and presiding Judge Sarah Vance's standing order, plaintiff must submit final rebuttal affidavits on electronic discovery spoliation before close of business.
                    </p>
                    <div class="p-3 bg-[#FAF8F5] border border-[#F4EFEA] rounded text-xs text-[#222222] mt-1 font-mono">
                        <span class="text-[#ba1a1a] font-bold">WARNING:</span> No stipulations for extension will be entertained without showing of emergency cause.
                    </div>
                </div>

                <div class="pt-4 border-t border-[#F4EFEA]">
                    <span class="font-mono text-[10px] uppercase font-bold text-[#766A5E]">Upcoming Deposition</span>
                    <h3 class="text-sm font-semibold text-[#222222] mt-1">Defendant CFO Deposition — Castellan Logistics</h3>
                    <p class="text-xs text-[#766A5E] mt-0.5">Scheduled Friday 9:00 AM at Chambers Deposition Suite 4.</p>
                </div>
            </div>

            <!-- Column 2: Judicial Docket & Court Hearings -->
            <div class="px-0 md:px-4 flex flex-col gap-4 pt-4 md:pt-0">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-[#845D33] border-b border-[#E8DAC8] pb-1">Chambers Docket &amp; Hearings</span>
                
                @foreach($events as $ev)
                <div class="p-3 rounded bg-white border border-[#EFECE6] flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-[#845D33]">{{ $ev->start_time->format('g:i A') }}</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-[#F4EFEA] text-[#554D45]">{{ $ev->event_type }}</span>
                    </div>
                    <h4 class="text-xs font-semibold text-[#222222] mt-0.5">{{ $ev->title }}</h4>
                    <span class="text-[11px] text-[#766A5E]">{{ $ev->location }}</span>
                </div>
                @endforeach

                <div class="p-4 bg-[#F8F4EE] border border-[#F4ECE1] rounded-lg mt-2">
                    <span class="font-mono text-[10px] font-bold uppercase text-[#6D4B27]">Judicial Intelligence</span>
                    <p class="text-xs text-[#845D33] mt-1 leading-relaxed">
                        Judge Vance issued a bench order in related case <span class="font-mono">NO-2025-1102</span> denying broad third-party subpoena motions. Counsel should refine exhibit breadth accordingly.
                    </p>
                </div>
            </div>

            <!-- Column 3: Practice Financials & High Priority Tasks -->
            <div class="pl-0 md:pl-4 flex flex-col gap-4 pt-4 md:pt-0">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-[#845D33] border-b border-[#ffe08f] pb-1">Financials &amp; Action Matrix</span>
                
                <div class="p-3 bg-white border border-[#EFECE6] rounded flex flex-col gap-1">
                    <span class="font-mono text-[10px] uppercase text-[#766A5E]">Unbilled WIP Hours</span>
                    <span class="text-2xl font-serif font-bold text-[#222222]">$28,450.00</span>
                    <span class="text-xs text-[#845D33]">4 Active Matters ready for billing run</span>
                </div>

                <div class="flex flex-col gap-2 mt-2">
                    <span class="font-mono text-[11px] font-semibold text-[#222222]">Partner To-Do Protocol</span>
                    @foreach($tasks as $t)
                    <div class="text-xs p-2 rounded bg-[#FAF8F5] border border-[#F4EFEA] flex items-start gap-2">
                        <input type="checkbox" class="mt-0.5 rounded text-[#845D33] accent-[#845D33]"/>
                        <div>
                            <span class="font-medium text-[#222222]">{{ $t->title }}</span>
                            <span class="text-[10px] text-[#766A5E] block">Assigned to {{ $t->assignee->name }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
