@extends('layouts.app')

@section('title', 'Conflict Check ' . ($conflictCheck->check_number ?? substr($conflictCheck->uuid, 0, 8)) . ' — Dossier')
@section('header_title', 'Conflict Check Record')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] max-w-5xl">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-[#8a8a8a] mb-2 font-mono">
        <a href="{{ route('conflict-checks.index') }}" class="hover:text-[#23493a]">Conflict Registry</a>
        <span>/</span>
        <span class="text-[#1a1a1a] font-semibold">{{ $conflictCheck->check_number ?? substr($conflictCheck->uuid, 0, 8) }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-[24px] sm:text-[28px] font-semibold text-[#1a1a1a] tracking-tight">
                    Conflict Check: {{ $conflictCheck->check_number ?? substr($conflictCheck->uuid, 0, 8) }}
                </h1>
                @php
                    $statusClasses = [
                        'clear' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'cleared_by_attorney' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'pending' => 'bg-amber-50 text-amber-800 border-amber-300',
                        'potential_conflict' => 'bg-orange-50 text-orange-800 border-orange-200',
                        'conflict_identified' => 'bg-red-50 text-red-700 border-red-200 font-semibold',
                        'waiver_required' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'rejected' => 'bg-zinc-100 text-zinc-700 border-zinc-200',
                    ];
                    $cls = $statusClasses[$conflictCheck->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                @endphp
                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border {{ $cls }}">
                    <span class="w-2 h-2 rounded-full {{ $conflictCheck->conflict_identified ? 'bg-red-600' : 'bg-emerald-600' }}"></span>
                    <span class="font-medium">{{ $conflictCheck->status_label }}</span>
                </span>
            </div>
            <p class="text-xs text-[#8a8a8a] mt-1 font-mono">
                Check ID: <span class="text-[#1a1a1a] font-bold">{{ $conflictCheck->check_number }}</span> &middot; UUID: {{ $conflictCheck->uuid }} &middot; Checked on {{ $conflictCheck->checked_at ? $conflictCheck->checked_at->format('d M Y \a\t H:i T') : $conflictCheck->created_at->format('d M Y') }}
            </p>
        </div>

        <a href="{{ route('conflict-checks.index') }}" class="btn-secondary h-8 px-3 text-xs inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Back to Registry</span>
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left 8 cols: Record Summary & Search Terms -->
        <div class="lg:col-span-8 flex flex-col gap-6">

            <!-- Check Parameters Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-6 shadow-xs">
                <h3 class="text-[14px] font-semibold text-[#1a1a1a] mb-4 pb-3 border-b border-[#f0eee8] flex items-center justify-between">
                    <span>Search Parameters &amp; Targets</span>
                    <span class="text-xs font-mono text-[#8a8a8a]">Bar Council Rule Compliant</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-[#8a8a8a] block font-mono text-[11px] uppercase mb-1">Search Terms Scanned</span>
                        <div class="p-3 bg-[#faf8f5] rounded border border-[#e5e3dc] font-mono text-[#1a1a1a] text-xs">
                            {{ $conflictCheck->search_terms }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[#f0eee8]">
                        <div>
                            <span class="text-[#8a8a8a] block font-mono text-[11px] uppercase mb-1">Target Client Dossier</span>
                            @if($conflictCheck->client)
                                <a href="{{ route('clients.show', $conflictCheck->client_id) }}" class="text-[#23493a] hover:underline font-semibold flex items-center gap-1">
                                    <span>{{ $conflictCheck->client->name }}</span>
                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                </a>
                                <span class="text-[11px] text-[#8a8a8a] block mt-0.5">{{ ucfirst($conflictCheck->client->category) }} &middot; Status: {{ $conflictCheck->client->status_label }}</span>
                            @else
                                <span class="text-[#8a8a8a] italic">None</span>
                            @endif
                        </div>

                        <div>
                            <span class="text-[#8a8a8a] block font-mono text-[11px] uppercase mb-1">Associated Case Matter</span>
                            @if($conflictCheck->matter)
                                <a href="{{ route('matters.show', $conflictCheck->matter_id) }}" class="text-[#23493a] hover:underline font-semibold flex items-center gap-1">
                                    <span>Case {{ $conflictCheck->matter->case_number }}: {{ $conflictCheck->matter->title }}</span>
                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                </a>
                                <span class="text-[11px] text-[#8a8a8a] block mt-0.5">Court: {{ $conflictCheck->matter->court_name ?? 'District Court' }}</span>
                            @else
                                <span class="text-[#8a8a8a] italic">General Client Engagement (No specific court matter)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conflict Analysis & Resolution -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-6 shadow-xs">
                <h3 class="text-[14px] font-semibold text-[#1a1a1a] mb-4 pb-3 border-b border-[#f0eee8]">
                    Conflict Findings &amp; Resolution Analysis
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-[#8a8a8a] block font-mono text-[11px] uppercase mb-1">Conflict Description &amp; Adverse Exposure</span>
                        <div class="p-3 rounded border text-xs leading-relaxed {{ $conflictCheck->conflict_identified ? 'bg-red-50/50 border-red-200 text-red-950' : 'bg-[#faf8f5] border-[#e5e3dc] text-[#646864]' }}">
                            {{ $conflictCheck->conflict_description ?: 'No adverse conflicts, conflicting representations, or concurrent adversary exposure identified.' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-[#8a8a8a] block font-mono text-[11px] uppercase mb-1">Clearance Determination / Waiver Terms</span>
                        <div class="p-3 bg-[#faf8f5] rounded border border-[#e5e3dc] text-[#1a1a1a] text-xs leading-relaxed">
                            {{ $conflictCheck->resolution ?: 'Clear for chamber representation and legal advisory.' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 4 cols: Sign-off & Status Update -->
        <div class="lg:col-span-4 flex flex-col gap-6">

            <!-- Fiduciary Sign-off Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-3 pb-2 border-b border-[#f0eee8]">
                    Fiduciary Audit Trail
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[#8a8a8a] block text-[11px]">Initiated &amp; Scanned By:</span>
                        <span class="font-medium text-[#1a1a1a]">{{ $conflictCheck->checker->name ?? 'Advocate' }}</span>
                        <span class="text-[11px] text-[#8a8a8a] block font-mono">{{ $conflictCheck->checked_at->format('d M Y, H:i') }}</span>
                    </div>

                    <div class="pt-2 border-t border-[#f0eee8]">
                        <span class="text-[#8a8a8a] block text-[11px]">Senior Partner Reviewer:</span>
                        @if($conflictCheck->reviewer)
                            <span class="font-medium text-[#1a1a1a]">{{ $conflictCheck->reviewer->name }}</span>
                            <span class="text-[11px] text-[#8a8a8a] block font-mono">{{ $conflictCheck->review_date ? $conflictCheck->review_date->format('d M Y, H:i') : 'Reviewed' }}</span>
                        @else
                            <span class="text-amber-800 italic">Pending Senior Sign-off</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Update Determination Form -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-3 pb-2 border-b border-[#f0eee8]">
                    Update Clearance Status
                </h3>

                <form action="{{ route('conflict-checks.update', $conflictCheck->id) }}" method="POST" class="flex flex-col gap-3 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Determination</label>
                        <select name="status" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="clear" {{ $conflictCheck->status === 'clear' ? 'selected' : '' }}>✓ Clear (No Conflict)</option>
                            <option value="potential_conflict" {{ $conflictCheck->status === 'potential_conflict' ? 'selected' : '' }}>⚠️ Potential Conflict</option>
                            <option value="conflict_identified" {{ $conflictCheck->status === 'conflict_identified' ? 'selected' : '' }}>✕ Conflict Identified</option>
                            <option value="waiver_required" {{ $conflictCheck->status === 'waiver_required' ? 'selected' : '' }}>⚖️ Waiver Required</option>
                            <option value="cleared_by_attorney" {{ $conflictCheck->status === 'cleared_by_attorney' ? 'selected' : '' }}>✓ Cleared by Attorney</option>
                            <option value="rejected" {{ $conflictCheck->status === 'rejected' ? 'selected' : '' }}>✕ Rejected / Declined</option>
                            <option value="pending" {{ $conflictCheck->status === 'pending' ? 'selected' : '' }}>⋯ Pending</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="conflict_identified" value="1" {{ $conflictCheck->conflict_identified ? 'checked' : '' }}
                                   class="rounded border-[#e5e3dc] text-red-600 focus:ring-red-500"/>
                            <span class="font-medium text-xs text-red-900">Flag Conflict Identified</span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Resolution / Clearance Notes</label>
                        <textarea name="resolution" rows="3" class="p-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">{{ $conflictCheck->resolution }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary h-8 px-3 text-xs mt-1 cursor-pointer">
                        Sign-off &amp; Save Determination
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
