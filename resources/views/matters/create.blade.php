<x-app-layout>
    <x-slot name="title">Open New Case Dossier — {{ config('legal.app_name', 'Vennamraj Associates') }}</x-slot>

    <div class="max-w-3xl mx-auto pb-12">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Back to Matters Directory</span>
                </a>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">Initiate New Case Dossier</h1>
                <p class="text-sm text-[#766A5E]">Enter court parameters, client relationship, judicial forum, and procedural stage</p>
            </div>
        </div>

        <form action="{{ route('matters.store') }}" method="POST" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 flex flex-col gap-5">
            @csrf

            <!-- Client & Title -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Client / Corporate Entity *</label>
                    <select name="client_id" required class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                        <option value="">Select an existing client...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Practice Discipline *</label>
                    <select name="practice_area" required class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                        @foreach(config('legal.practice_areas', ['Commercial Litigation & Arbitration', 'Corporate & Insolvency (IBC / NCLT)', 'Criminal Defense & Bail Matters']) as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Case Title (Caption) *</label>
                <input name="title" required placeholder="e.g. Malhotra Enterprises v. Apex Commercial Bank" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white"/>
            </div>

            <!-- Court Forum & Judge -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Court / Judicial Forum</label>
                    <input name="court_name" placeholder="e.g. High Court of Delhi, New Delhi" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Presiding Judge / Bench</label>
                    <input name="judge_name" placeholder="e.g. Hon. Justice C. Hari Shankar" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white"/>
                </div>
            </div>

            <!-- Lead Counsel & Procedural Stage -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Lead Advocate *</label>
                    <select name="lead_attorney_id" required class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                        @foreach($attorneys as $at)
                            <option value="{{ $at->id }}">{{ $at->name }} ({{ $at->title ?? $at->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">Procedural Stage</label>
                    <select name="stage" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                        <option value="Filing / Pre-Admission">Filing / Pre-Admission</option>
                        <option value="Notice & Pleadings" selected>Notice &amp; Pleadings</option>
                        <option value="Evidence & Arguments">Evidence &amp; Arguments</option>
                        <option value="Final Hearing & Order">Final Hearing &amp; Order</option>
                        <option value="Execution / Decree">Execution / Decree</option>
                    </select>
                </div>
            </div>

            <!-- System Matter Attributes -->
            <input type="hidden" name="billing_type" value="flat_fee"/>
            <input type="hidden" name="budget" value="0"/>

            <div class="pt-4 border-t border-[#F4EFEA] flex items-center justify-end gap-3">
                <a href="{{ route('matters.index') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-[#554D45] hover:bg-[#F4EFEA]">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-colors">
                    Create &amp; Open Case Dossier
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
