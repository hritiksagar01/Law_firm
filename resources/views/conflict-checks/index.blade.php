@extends('layouts.app')

@section('title', 'Conflict of Interest Registry — Sharma Legal Chambers')
@section('header_title', 'Conflict of Interest Registry')

@section('content')
<div x-data="{
    openNewModal: false,
    searchTerm: '',
    searchResults: [],
    searchLoading: false,
    selectedClientId: '{{ request('client_id') ?? '' }}',
    selectedMatterId: '{{ request('matter_id') ?? '' }}',
    checkStatus: 'clear',
    conflictDescription: '',
    resolution: '',
    async runSearch() {
        if (this.searchTerm.trim().length < 2) {
            this.searchResults = [];
            return;
        }
        this.searchLoading = true;
        try {
            const res = await fetch('/conflict-checks/search?term=' + encodeURIComponent(this.searchTerm.trim()));
            const data = await res.json();
            this.searchResults = data.matches || [];
            if (this.searchResults.length > 0) {
                this.checkStatus = 'potential_conflict';
            } else {
                this.checkStatus = 'clear';
            }
        } catch (e) {
            console.error('Search error:', e);
        } finally {
            this.searchLoading = false;
        }
    }
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Page Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                Conflict of Interest Checks
            </h1>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Statutory Bar Council &amp; fiduciary clearance records to prevent representation conflicts across parties, adversaries &amp; witnesses.
            </p>
        </div>

        <button type="button" @click="openNewModal = true" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-2 cursor-pointer shadow-xs">
            <span class="material-symbols-outlined text-[17px]">policy</span>
            <span>Run New Conflict Check</span>
        </button>
    </div>

    <!-- Filter Tabs by Status -->
    <div class="border-b border-[#e5e3dc] flex items-center justify-between gap-4 overflow-x-auto mb-6 text-xs font-medium pb-2">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('conflict-checks.index') }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                All ({{ $statusCounts['all'] ?? 0 }})
            </a>
            <a href="{{ route('conflict-checks.index', ['status' => 'pending']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'pending' ? 'bg-[#23493a] text-white' : 'text-[#854d0e] bg-amber-50 hover:bg-amber-100 border border-amber-200' }}">
                Pending Review ({{ $statusCounts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('conflict-checks.index', ['status' => 'clear']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'clear' ? 'bg-[#23493a] text-white' : 'text-[#065f46] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Clear / Cleared ({{ $statusCounts['clear'] ?? 0 }})
            </a>
            <a href="{{ route('conflict-checks.index', ['status' => 'potential_conflict']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'potential_conflict' ? 'bg-[#23493a] text-white' : 'text-[#b91c1c] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Conflict Identified ({{ $statusCounts['conflict_identified'] ?? 0 }})
            </a>
            <a href="{{ route('conflict-checks.index', ['status' => 'waiver_required']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'waiver_required' ? 'bg-[#23493a] text-white' : 'text-purple-700 hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Waiver Required ({{ $statusCounts['waiver_required'] ?? 0 }})
            </a>
            <a href="{{ route('conflict-checks.index', ['status' => 'rejected']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'rejected' ? 'bg-[#23493a] text-white' : 'text-gray-600 hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Declined / Rejected ({{ $statusCounts['rejected'] ?? 0 }})
            </a>
        </div>

        <!-- Search input -->
        <form action="{{ route('conflict-checks.index') }}" method="GET" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}"/>
            @endif
            <div class="relative w-56">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[17px] text-[#8a8a8a]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search check terms..."
                       class="w-full h-8 pl-8 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] placeholder-[#8a8a8a] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
            </div>
            @if(request('q'))
                <a href="{{ route('conflict-checks.index', array_filter(['status' => request('status')])) }}" class="text-xs text-[#8a8a8a] hover:text-[#1a1a1a]">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table of Conflict Checks -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[11px] text-[#646864] uppercase font-mono tracking-wider">
                        <th class="py-3 px-4 font-semibold">Check ID</th>
                        <th class="py-3 px-4 font-semibold">Client &amp; Matter</th>
                        <th class="py-3 px-4 font-semibold">Search Terms Scanned</th>
                        <th class="py-3 px-4 font-semibold">Checked By / Date</th>
                        <th class="py-3 px-4 font-semibold">Status / Determination</th>
                        <th class="py-3 px-4 font-semibold">Reviewer</th>
                        <th class="py-3 px-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($checks as $chk)
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <!-- Check UUID / ID -->
                        <td class="py-3.5 px-4 font-mono text-[11px]">
                            <a href="{{ route('conflict-checks.show', $chk->id) }}" class="text-[#23493a] hover:underline font-semibold">
                                {{ $chk->check_number ?: substr($chk->uuid, 0, 8) . '…' }}
                            </a>
                        </td>

                        <!-- Client & Matter -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col min-w-0">
                                <a href="{{ route('clients.show', $chk->client_id) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a]">
                                    {{ $chk->client->name ?? 'Unknown Client' }}
                                </a>
                                @if($chk->matter)
                                    <a href="{{ route('matters.show', $chk->matter_id) }}" class="text-[11px] text-[#646864] hover:underline truncate max-w-[200px]">
                                        Case {{ $chk->matter->case_number }}: {{ $chk->matter->title }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-[#8a8a8a] italic">General Client Intake (No Matter)</span>
                                @endif
                            </div>
                        </td>

                        <!-- Search Terms -->
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-[#1a1a1a] line-clamp-1 max-w-[220px]" title="{{ $chk->search_terms }}">
                                {{ $chk->search_terms }}
                            </span>
                        </td>

                        <!-- Checked By -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col font-mono text-[11px]">
                                <span class="text-[#1a1a1a] font-medium">{{ $chk->checker->name ?? 'Advocate' }}</span>
                                <span class="text-[#8a8a8a]">{{ $chk->checked_at ? $chk->checked_at->format('d M Y, H:i') : $chk->created_at->format('d M Y') }}</span>
                            </div>
                        </td>

                        <!-- Status badge -->
                        <td class="py-3.5 px-4">
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
                                $cls = $statusClasses[$chk->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border {{ $cls }}">
                                @if($chk->conflict_identified)
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                @elseif(in_array($chk->status, ['clear', 'cleared_by_attorney']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                @endif
                                <span>{{ $chk->status_label }}</span>
                            </span>
                        </td>

                        <!-- Reviewer -->
                        <td class="py-3.5 px-4 text-[#646864]">
                            @if($chk->reviewer)
                                <div class="flex flex-col text-[11px]">
                                    <span class="font-medium text-[#1a1a1a]">{{ $chk->reviewer->name }}</span>
                                    <span class="text-[#8a8a8a]">{{ $chk->review_date ? $chk->review_date->format('d M Y') : 'Reviewed' }}</span>
                                </div>
                            @else
                                <span class="text-[#8a8a8a] italic">Pending Sign-off</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('conflict-checks.show', $chk->id) }}" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1 font-medium">
                                <span>View File</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#8a8a8a] text-xs">
                            <span class="material-symbols-outlined text-[36px] text-[#c1c8c3] block mb-2">policy</span>
                            No conflict checks matching this criteria. Run a conflict search to create a formal clearance record.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($checks->hasPages())
        <div class="p-4 border-t border-[#f0eee8] bg-[#faf8f5]">
            {{ $checks->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: RUN NEW CONFLICT CHECK WITH LIVE SCAN                          -->
    <!-- ===================================================================== -->
    <div x-show="openNewModal" x-cloak @click.away="openNewModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-2xl p-6 flex flex-col max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">policy</span>
                    <h3 class="text-[16px] font-semibold text-[#1a1a1a]">Run Conflict of Interest Search</h3>
                </div>
                <button type="button" @click="openNewModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('conflict-checks.store') }}" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Client Selector -->
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Prospective or Existing Client *</label>
                        <select name="client_id" required x-model="selectedClientId" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="">Select Client Dossier...</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->name }} ({{ ucfirst($cl->status) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Associated Matter Selector -->
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Associated Matter (Optional)</label>
                        <select name="matter_id" x-model="selectedMatterId" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="">None (General Client Intake / Pre-Engagement)</option>
                            @foreach($matters as $mt)
                                <option value="{{ $mt->id }}">Case {{ $mt->case_number }}: {{ $mt->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search Terms Input & Scan Button -->
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Names, Parties, Entities &amp; Opposing Counsel to Scan *</label>
                    <div class="flex items-center gap-2">
                        <input name="search_terms" required x-model="searchTerm" @keydown.enter.prevent="runSearch()"
                               type="text" placeholder="e.g. Ramesh Kumar, ABC Corp, Adv. Sharma, State Bank..."
                               class="flex-1 h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        <button type="button" @click="runSearch()" class="btn-secondary h-9 px-3 inline-flex items-center gap-1 cursor-pointer shrink-0">
                            <span class="material-symbols-outlined text-[16px]">manage_search</span>
                            <span>Scan Chambers Database</span>
                        </button>
                    </div>
                    <span class="text-[11px] text-[#8a8a8a]">Scans clients, former litigants, opposing parties, co-litigants, witnesses and corporate entities.</span>
                </div>

                <!-- Live Search Matches Display -->
                <div x-show="searchLoading" class="p-4 text-center text-xs text-[#8a8a8a] bg-[#faf8f5] rounded-md border border-[#e5e3dc]">
                    Scanning database for potential conflicts...
                </div>

                <div x-show="!searchLoading && searchResults.length > 0" class="p-3 bg-red-50/70 border border-red-200 rounded-md">
                    <div class="flex items-center gap-1.5 text-red-800 font-semibold mb-2">
                        <span class="material-symbols-outlined text-[18px]">warning</span>
                        <span>Potential Matches Identified (<span x-text="searchResults.length"></span>)</span>
                    </div>
                    <div class="max-h-40 overflow-y-auto space-y-1.5">
                        <template x-for="(match, idx) in searchResults" :key="idx">
                            <div class="p-2 bg-white rounded border border-red-200 flex items-start justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-semibold text-[#1a1a1a]" x-text="match.name"></div>
                                    <div class="text-[11px] text-[#646864]" x-text="match.details"></div>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-red-100 text-red-800" x-text="match.type"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="!searchLoading && searchTerm.trim().length >= 2 && searchResults.length === 0" class="p-3 bg-emerald-50 border border-emerald-200 rounded-md flex items-center gap-2 text-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>No conflicting representation records found for "<strong x-text="searchTerm"></strong>". Clear for representation.</span>
                </div>

                <input type="hidden" name="search_results" :value="JSON.stringify(searchResults)"/>

                <!-- Determination & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-[#f0eee8]">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Conflict Determination / Status *</label>
                        <select name="status" x-model="checkStatus" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="clear">✓ Clear (No Conflict Found)</option>
                            <option value="potential_conflict">⚠️ Potential Conflict (Requires Verification)</option>
                            <option value="conflict_identified">✕ Conflict Identified (Adverse Representation)</option>
                            <option value="waiver_required">⚖️ Waiver Required (Informed Written Consent)</option>
                            <option value="cleared_by_attorney">✓ Cleared by Partner / Senior Counsel</option>
                            <option value="rejected">✕ Rejected / Representation Declined</option>
                            <option value="pending">⋯ Pending Extended Inquiry</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="conflict_identified" value="1"
                                   :checked="checkStatus === 'conflict_identified' || checkStatus === 'potential_conflict'"
                                   class="rounded border-[#e5e3dc] text-red-600 focus:ring-red-500"/>
                            <span class="font-medium text-xs text-red-900">Flag as Conflict Identified</span>
                        </label>
                    </div>
                </div>

                <!-- Conflict Description Narrative -->
                <div class="flex flex-col gap-1" x-show="checkStatus !== 'clear'">
                    <label class="font-semibold text-[#1a1a1a]">Conflict Narrative &amp; Relationship Analysis</label>
                    <textarea name="conflict_description" rows="2" placeholder="Explain the nature of the conflicting relationship (e.g. Current client is opposing party in Case CC-2022-094)..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <!-- Resolution / Waiver Notes -->
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Clearance Resolution / Waiver Notes</label>
                    <textarea name="resolution" rows="2" placeholder="Specify waiver terms, ethical wall restrictions, or client consent letters on file..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openNewModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">File Formal Conflict Check</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
