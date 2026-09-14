<x-app-layout>
    <x-slot name="title">The Chambers Daily Broadsheet — Executive Legal Intelligence</x-slot>

    <!-- Newspaper Style Masthead (Stitch Spec) -->
    <div class="max-w-5xl mx-auto pb-12">
        <div class="border-b-4 border-double border-[#1a1c1a] pb-4 mb-6 text-center">
            <div class="flex items-center justify-between font-mono text-xs text-[#727973] border-b border-[#e9e8e5] pb-2 mb-3">
                <span>VOL. XIV · NO. 248</span>
                <span class="font-serif italic">The Daily Practice &amp; Docket Intelligence</span>
                <span>SAN FRANCISCO / NEW YORK</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-[#1a1c1a] tracking-tight uppercase">The Chambers Broadsheet</h1>
            <p class="text-xs font-mono text-[#727973] mt-2 uppercase tracking-widest">
                Wednesday, October 15, 2026 · Confidential Executive Briefing for Margaret Chen, Senior Partner
            </p>
        </div>

        <!-- 3-Column Broadsheet Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-[#e9e8e5]">
            
            <!-- Column 1: Lead Editorial / Urgent Court Filings -->
            <div class="pr-0 md:pr-4 flex flex-col gap-4">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-red-800 border-b border-red-200 pb-1">Critical Filing Deadlines Today</span>
                <div class="flex flex-col gap-2">
                    <h2 class="text-xl font-serif font-bold text-[#1a1c1a] leading-tight">
                        Marsh v. Castellan: Statutory Reply Brief Due by 5:00 PM PST
                    </h2>
                    <p class="text-xs text-[#424843] leading-relaxed">
                        Pursuant to Local Rule 7-3 and presiding Judge Sarah Vance's standing order, plaintiff must submit final rebuttal affidavits on electronic discovery spoliation before close of business.
                    </p>
                    <div class="p-3 bg-[#faf9f6] border border-[#efeeeb] rounded text-xs text-[#1a1c1a] mt-1 font-mono">
                        <span class="text-[#ba1a1a] font-bold">WARNING:</span> No stipulations for extension will be entertained without showing of emergency cause.
                    </div>
                </div>

                <div class="pt-4 border-t border-[#efeeeb]">
                    <span class="font-mono text-[10px] uppercase font-bold text-[#727973]">Upcoming Deposition</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a] mt-1">Defendant CFO Deposition — Castellan Logistics</h3>
                    <p class="text-xs text-[#727973] mt-0.5">Scheduled Friday 9:00 AM at Chambers Deposition Suite 4.</p>
                </div>
            </div>

            <!-- Column 2: Judicial Docket & Court Hearings -->
            <div class="px-0 md:px-4 flex flex-col gap-4 pt-4 md:pt-0">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-[#1a3c2a] border-b border-[#a9cfb6] pb-1">Chambers Docket &amp; Hearings</span>
                
                @foreach($events as $ev)
                <div class="p-3 rounded bg-white border border-[#e9e8e5] flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-[#1a3c2a]">{{ $ev->start_time->format('g:i A') }}</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-[#efeeeb] text-[#424843]">{{ $ev->event_type }}</span>
                    </div>
                    <h4 class="text-xs font-semibold text-[#1a1c1a] mt-0.5">{{ $ev->title }}</h4>
                    <span class="text-[11px] text-[#727973]">{{ $ev->location }}</span>
                </div>
                @endforeach

                <div class="p-4 bg-[#f0f9f4] border border-[#c5ecd2] rounded-lg mt-2">
                    <span class="font-mono text-[10px] font-bold uppercase text-emerald-800">Judicial Intelligence</span>
                    <p class="text-xs text-[#2c4e3a] mt-1 leading-relaxed">
                        Judge Vance issued a bench order in related case <span class="font-mono">NO-2025-1102</span> denying broad third-party subpoena motions. Counsel should refine exhibit breadth accordingly.
                    </p>
                </div>
            </div>

            <!-- Column 3: Practice Financials & High Priority Tasks -->
            <div class="pl-0 md:pl-4 flex flex-col gap-4 pt-4 md:pt-0">
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-[#755b00] border-b border-[#ffe08f] pb-1">Financials &amp; Action Matrix</span>
                
                <div class="p-3 bg-white border border-[#e9e8e5] rounded flex flex-col gap-1">
                    <span class="font-mono text-[10px] uppercase text-[#727973]">Unbilled WIP Hours</span>
                    <span class="text-2xl font-serif font-bold text-[#1a1c1a]">$28,450.00</span>
                    <span class="text-xs text-emerald-700">4 Active Matters ready for billing run</span>
                </div>

                <div class="flex flex-col gap-2 mt-2">
                    <span class="font-mono text-[11px] font-semibold text-[#1a1c1a]">Partner To-Do Protocol</span>
                    @foreach($tasks as $t)
                    <div class="text-xs p-2 rounded bg-[#faf9f6] border border-[#efeeeb] flex items-start gap-2">
                        <input type="checkbox" class="mt-0.5 rounded text-[#1a3c2a] accent-[#1a3c2a]"/>
                        <div>
                            <span class="font-medium text-[#1a1c1a]">{{ $t->title }}</span>
                            <span class="text-[10px] text-[#727973] block">Assigned to {{ $t->assignee->name }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
