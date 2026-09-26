@extends('layouts.app')

@section('title', 'Matter Dossiers — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Matter Dossiers')

@section('content')
    <div class="flex flex-col w-full text-[#1a1a1a]">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Matter Dossiers</h1>
                <p class="text-[13px] text-[#646864] mt-1">Centralized case files, judicial forums, orders, and procedural schedules</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('matters.create') }}"
                    class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Initiate Case File</span>
                </a>
            </div>
        </div>

        <!-- Filter Pills & Search Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <!-- Status & Stage Filters -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                <!-- Status Pills -->
                <a href="{{ route('matters.index', array_filter(['q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap {{ (!request('status') || request('status') == 'all') && !request('stage') ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    All ({{ $totalCount ?? $matters->count() }})
                </a>
                <a href="{{ route('matters.index', array_filter(['status' => 'open', 'q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap inline-flex items-center gap-1.5 {{ request('status') == 'open' && !request('stage') ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request('status') == 'open' && !request('stage') ? 'bg-emerald-300' : 'bg-emerald-500' }}"></span>
                    <span>Open ({{ $openCount ?? 0 }})</span>
                </a>
                <a href="{{ route('matters.index', array_filter(['status' => 'closed', 'q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap inline-flex items-center gap-1.5 {{ request('status') == 'closed' && !request('stage') ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request('status') == 'closed' && !request('stage') ? 'bg-slate-300' : 'bg-slate-400' }}"></span>
                    <span>Closed ({{ $closedCount ?? 0 }})</span>
                </a>

                <div class="h-4 w-px bg-[#e5e3dc] mx-1 shrink-0"></div>

                <!-- Stage Pills -->
                <a href="{{ route('matters.index', array_filter(['stage' => 'Discovery', 'status' => request('status'), 'q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap {{ request('stage') == 'Discovery' ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    Discovery ({{ $discoveryCount ?? 0 }})
                </a>
                <a href="{{ route('matters.index', array_filter(['stage' => 'Pleadings', 'status' => request('status'), 'q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap {{ request('stage') == 'Pleadings' ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    Pleadings ({{ $pleadingsCount ?? 0 }})
                </a>
                <a href="{{ route('matters.index', array_filter(['stage' => 'Pre-Trial', 'status' => request('status'), 'q' => request('q')])) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap {{ request('stage') == 'Pre-Trial' ? 'bg-[#23493a] text-white shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:bg-[#faf9f5]' }}">
                    Pre-Trial ({{ $preTrialCount ?? 0 }})
                </a>
            </div>

            <!-- Search Input -->
            <form action="{{ route('matters.index') }}" method="GET" class="relative w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('stage'))
                    <input type="hidden" name="stage" value="{{ request('stage') }}">
                @endif
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#8a8a8a] text-[18px]">search</span>
                <input name="q" value="{{ request('q') }}" placeholder="Filter case, client, or docket #..."
                    class="w-full h-9 pl-9 pr-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none" />
            </form>
        </div>

        <!-- Matters Table -->
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
            <table class="w-full text-left border-collapse text-[13px]">
                <thead>
                    <tr
                        class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider bg-[#faf8f5]">
                        <th class="py-3 px-4 font-medium">Docket Number</th>
                        <th class="py-3 px-4 font-medium">Matter Title &amp; Forum</th>
                        <th class="py-3 px-4 font-medium">Client</th>
                        <th class="py-3 px-4 font-medium">Status</th>
                        <th class="py-3 px-4 font-medium">Stage</th>
                        <th class="py-3 px-4 font-medium">Lead Attorney</th>
                        <th class="py-3 px-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($matters as $matter)
                        @php
                            $isClosed = in_array(strtolower($matter->status ?? ''), ['closed', 'settled', 'dismissed', 'archived']);
                        @endphp
                        <tr class="hover:bg-[#faf9f5] transition-colors group">
                            <td class="py-3.5 px-4 font-mono text-xs font-medium text-[#1a1a1a]">
                                <span
                                    class="px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]">
                                    {{ $matter->case_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <a href="{{ route('matters.show', $matter->id) }}"
                                        class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                        {{ $matter->title }}
                                    </a>
                                    <span class="text-[11.5px] text-[#8a8a8a] mt-0.5">{{ $matter->court_name }} <span
                                            class="mx-1">&middot;</span> {{ $matter->judge_name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-[#646864]">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#8a8a8a]">corporate_fare</span>
                                    <span class="font-medium text-xs text-[#1a1a1a]">{{ $matter->client->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($isClosed)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Closed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Open
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                    {{ $matter->stage }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    @if($matter->leadAttorney?->avatar_url)
                                        <img alt="{{ $matter->leadAttorney?->name }}"
                                            class="w-6 h-6 rounded-full object-cover ring-1 ring-[#e5e3dc]"
                                            src="{{ $matter->leadAttorney?->avatar_url }}" />
                                    @else
                                        <div
                                            class="w-6 h-6 rounded-full bg-[#5b4382] text-white flex items-center justify-center font-semibold text-[10px]">
                                            {{ strtoupper(substr($matter->leadAttorney?->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span
                                        class="text-xs text-[#1a1a1a]">{{ $matter->leadAttorney?->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    @if($isClosed)
                                        <form action="{{ route('matters.status.update', $matter->id) }}" method="POST" class="inline" onsubmit="return confirm('Reopen matter dossier {{ $matter->case_number }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit" class="px-2 py-1 rounded text-[11px] font-medium text-[#23493a] bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors inline-flex items-center gap-1" title="Reopen Matter">
                                                <span class="material-symbols-outlined text-[13px]">lock_open</span>
                                                <span>Reopen</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('matters.status.update', $matter->id) }}" method="POST" class="inline" onsubmit="return confirm('Close matter dossier {{ $matter->case_number }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="closed">
                                            <button type="submit" class="px-2 py-1 rounded text-[11px] font-medium text-[#646864] hover:text-rose-700 hover:bg-rose-50 border border-[#e5e3dc] hover:border-rose-200 transition-colors inline-flex items-center gap-1" title="Close Matter">
                                                <span class="material-symbols-outlined text-[13px]">lock</span>
                                                <span>Close</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('matters.show', $matter->id) }}"
                                        class="inline-flex items-center gap-1 text-[12px] font-medium text-[#23493a] hover:underline px-1 py-1">
                                        <span>Open</span>
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#8a8a8a] text-xs">
                                No matter dossiers found matching query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection