@extends('layouts.app')

@section('title', 'Case Progress & Stage Report — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Case Lifecycle &amp; Stages</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Case Progress &amp; Stage Report</h1>
            <p class="text-xs text-[#646864] mt-0.5">Cross-jurisdiction listing of all ongoing matters with procedural stage tracking and counsel allocation.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Dossier</span>
            </button>
            <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>New Matter</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#23493a] text-white shadow-xs">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Bank Activity &amp; Reconciliation</a>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('reports.cases') }}" method="GET" class="p-3.5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Search Keywords</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-sm text-[#646864]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Case #, title, or parties..." class="w-full h-8 pl-8 pr-3 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
            </div>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Stage</label>
            <select name="stage" class="w-full h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none">
                <option value="all">All Stages</option>
                @foreach($allStages as $st)
                <option value="{{ $st }}" {{ request('stage') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-44">
            <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Practice Area</label>
            <select name="practice_area" class="w-full h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none">
                <option value="all">All Practice Areas</option>
                @foreach($allPracticeAreas as $pa)
                <option value="{{ $pa }}" {{ request('practice_area') == $pa ? 'selected' : '' }}>{{ $pa }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Judicial Forum</label>
            <select name="court" class="w-full h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none">
                <option value="all">All Courts</option>
                @foreach($allCourts as $crt)
                <option value="{{ $crt }}" {{ request('court') == $crt ? 'selected' : '' }}>{{ $crt }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Lead Advocate</label>
            <select name="attorney_id" class="w-full h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none">
                <option value="all">All Advocates</option>
                @foreach($attorneys as $atty)
                <option value="{{ $atty->id }}" {{ request('attorney_id') == $atty->id ? 'selected' : '' }}>{{ $atty->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2 pt-4">
            <button type="submit" class="h-8 px-3 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all">Filter</button>
            <a href="{{ route('reports.cases') }}" class="h-8 px-2.5 rounded-lg border border-[#e5e3dc] text-xs font-medium text-[#646864] hover:bg-[#faf8f5] flex items-center">Reset</a>
        </div>
    </form>

    <!-- Table of Cases -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
        <div class="p-3.5 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
            <div class="text-xs font-medium text-[#646864]">
                Showing <span class="font-bold text-[#1a1a1a]">{{ $matters->count() }}</span> matched case files
            </div>
            <span class="text-[11px] font-mono text-[#23493a] font-semibold">Jurisdictional Dossier</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Case Number &amp; Title</th>
                        <th class="py-3 px-4">Client / Petitioner</th>
                        <th class="py-3 px-4">Court / Forum</th>
                        <th class="py-3 px-4">Practice Domain</th>
                        <th class="py-3 px-4">Stage of Proceeding</th>
                        <th class="py-3 px-4">Lead Counsel</th>
                        <th class="py-3 px-4">Date Instituted</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($matters as $matter)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-mono text-[11px] font-bold text-[#23493a]">{{ $matter->case_number }}</div>
                            <a href="{{ route('matters.show', $matter->id) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] transition-colors block mt-0.5">
                                {{ $matter->title }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            {{ $matter->client->name ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            <div class="font-medium text-[#1a1a1a]">{{ $matter->court_name ?? 'Not Listed' }}</div>
                            @if($matter->judge_name)
                            <div class="text-[10px] text-[#646864]">Bench: {{ $matter->judge_name }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#faf8f5] text-[#646864] border border-[#e5e3dc]">
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
                                    'closed' => 'bg-stone-50 text-stone-800 border-stone-200',
                                ];
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium border {{ $stageColors[$matter->stage] ?? 'bg-stone-50 text-stone-700 border-stone-200' }}">
                                {{ ucfirst(str_replace('_', ' ', $matter->stage)) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            {{ $matter->leadAttorney->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#646864]">
                            {{ $matter->opened_at ? \Carbon\Carbon::parse($matter->opened_at)->format('d M, Y') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('matters.show', $matter->id) }}" class="inline-flex items-center gap-1 text-[11px] font-medium text-[#23493a] hover:underline">
                                <span>Dossier</span>
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#646864]">
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
