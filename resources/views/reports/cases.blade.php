@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Case Lifecycle &amp; Stages</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Case Progress &amp; Stage Report</h1>
            <p class="text-sm text-[#766A5E] mt-1">Cross-jurisdiction listing of all ongoing matters with procedural stage tracking and counsel allocation.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Dossier</span>
            </button>
            <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>New Matter</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Advocate Workload</a>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('reports.cases') }}" method="GET" class="p-4 rounded-xl bg-white border border-[#EFECE6] shadow-xs flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Search Keywords</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-sm text-[#766A5E]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Case #, title, or parties..." class="w-full h-8 pl-8 pr-3 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none"/>
            </div>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Stage</label>
            <select name="stage" class="w-full h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none">
                <option value="all">All Stages</option>
                @foreach($allStages as $st)
                <option value="{{ $st }}" {{ request('stage') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-44">
            <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Practice Area</label>
            <select name="practice_area" class="w-full h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none">
                <option value="all">All Practice Areas</option>
                @foreach($allPracticeAreas as $pa)
                <option value="{{ $pa }}" {{ request('practice_area') == $pa ? 'selected' : '' }}>{{ $pa }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Judicial Forum</label>
            <select name="court" class="w-full h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none">
                <option value="all">All Courts</option>
                @foreach($allCourts as $crt)
                <option value="{{ $crt }}" {{ request('court') == $crt ? 'selected' : '' }}>{{ $crt }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Lead Advocate</label>
            <select name="attorney_id" class="w-full h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#9F8349] focus:outline-none">
                <option value="all">All Advocates</option>
                @foreach($attorneys as $atty)
                <option value="{{ $atty->id }}" {{ request('attorney_id') == $atty->id ? 'selected' : '' }}>{{ $atty->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2 pt-4">
            <button type="submit" class="h-8 px-3 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all">Filter</button>
            <a href="{{ route('reports.cases') }}" class="h-8 px-2.5 rounded-lg border border-[#EAE4DC] text-xs font-medium text-[#766A5E] hover:bg-[#FAF8F5] flex items-center">Reset</a>
        </div>
    </form>

    <!-- Table of Cases -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
            <div class="text-xs font-medium text-[#554D45]">
                Showing <span class="font-bold text-[#222222]">{{ $matters->count() }}</span> matched case files
            </div>
            <span class="text-[11px] font-mono text-[#766A5E]">Jurisdictional Dossier</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Case Number &amp; Title</th>
                        <th class="py-3 px-4 font-semibold">Client / Petitioner</th>
                        <th class="py-3 px-4 font-semibold">Court / Forum</th>
                        <th class="py-3 px-4 font-semibold">Practice Domain</th>
                        <th class="py-3 px-4 font-semibold">Stage of Proceeding</th>
                        <th class="py-3 px-4 font-semibold">Lead Counsel</th>
                        <th class="py-3 px-4 font-semibold">Date Instituted</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($matters as $matter)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-mono text-[11px] font-bold text-[#9F8349]">{{ $matter->case_number }}</div>
                            <a href="{{ route('matters.show', $matter->id) }}" class="font-medium text-[#222222] hover:text-[#9F8349] transition-colors block mt-0.5">
                                {{ $matter->title }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            {{ $matter->client->name ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            <div class="font-medium">{{ $matter->court_name ?? 'Not Listed' }}</div>
                            @if($matter->judge_name)
                            <div class="text-[10px] text-[#766A5E]">Bench: {{ $matter->judge_name }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#FAF8F5] text-[#554D45] border border-[#EAE4DC]">
                                {{ $matter->practice_area }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $stageColors = [
                                    'pre_filing' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'pleadings' => 'bg-blue-50 text-blue-800 border-blue-200',
                                    'discovery' => 'bg-purple-50 text-purple-800 border-purple-200',
                                    'trial' => 'bg-rose-50 text-rose-800 border-rose-200',
                                    'settlement' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'closed' => 'bg-gray-50 text-gray-800 border-gray-200',
                                ];
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $stageColors[$matter->stage] ?? 'bg-stone-50 text-stone-700 border-stone-200' }}">
                                {{ ucfirst(str_replace('_', ' ', $matter->stage)) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            {{ $matter->leadAttorney->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#766A5E]">
                            {{ $matter->opened_at ? \Carbon\Carbon::parse($matter->opened_at)->format('d M, Y') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('matters.show', $matter->id) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#9F8349] hover:underline">
                                <span>Dossier</span>
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#766A5E]">
                            No case files match the selected filter parameters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
