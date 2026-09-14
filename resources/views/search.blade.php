<x-app-layout>
    <x-slot name="title">Global Chambers Search — Quire Legal</x-slot>

    <!-- Header -->
    <div class="pb-6 border-b border-[#e9e8e5] mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="font-mono text-xs text-[#727973] uppercase tracking-wider">Unified LexisCore Index</span>
        </div>
        <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Global Search Results</h1>
        <p class="text-sm text-[#727973]">Query: <span class="font-mono text-[#1a3c2a] font-semibold bg-[#efeeeb] px-2 py-0.5 rounded">"{{ $q }}"</span></p>
    </div>

    <!-- Search Results Sections -->
    <div class="flex flex-col gap-6">

        <!-- Matching Matters -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a]">cases</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a]">Matters &amp; Case Dossiers</h3>
                </div>
                <span class="font-mono text-xs text-[#727973]">{{ $matters->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#efeeeb]">
                @forelse($matters as $m)
                <a href="{{ route('matters.show', $m->id) }}" class="p-4 flex items-center justify-between hover:bg-[#faf9f6] transition-colors block group">
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-[#1a3c2a]">{{ $m->case_number }}</span>
                            <span class="text-xs font-semibold text-[#1a1c1a] group-hover:text-[#1a3c2a] transition-colors">{{ $m->title }}</span>
                        </div>
                        <span class="text-[11px] text-[#727973] mt-0.5">{{ $m->client->name }} · {{ $m->court_name ?? 'Chambers Arbitral Forum' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-[#c5ecd2]/50 text-[#1a3c2a] font-bold shrink-0">
                        {{ $m->stage }}
                    </span>
                </a>
                @empty
                <div class="p-4 text-xs text-[#727973] text-center">No matters matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Clients -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a]">group</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a]">Clients &amp; Retainers</h3>
                </div>
                <span class="font-mono text-xs text-[#727973]">{{ $clients->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#efeeeb]">
                @forelse($clients as $c)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf9f6] transition-colors">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1a1c1a]">{{ $c->name }}</span>
                        <span class="text-[11px] text-[#727973]">{{ $c->email }} · {{ $c->contact_person }}</span>
                    </div>
                    <span class="font-mono text-xs font-bold text-[#1a3c2a]">Trust: ${{ number_format($c->trust_balance, 2) }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#727973] text-center">No clients matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Filings & Evidence -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a]">description</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a]">Vault Evidence &amp; Filings</h3>
                </div>
                <span class="font-mono text-xs text-[#727973]">{{ $documents->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#efeeeb]">
                @forelse($documents as $doc)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf9f6]">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-700 text-lg">picture_as_pdf</span>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-[#1a1c1a]">{{ $doc->title }}</span>
                            <span class="font-mono text-[10px] text-[#727973]">{{ $doc->filename }} ({{ $doc->matter->case_number }})</span>
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#1a3c2a] hover:bg-[#efeeeb] rounded flex items-center gap-1 text-xs">
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Download</span>
                    </a>
                </div>
                @empty
                <div class="p-4 text-xs text-[#727973] text-center">No documents matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Tasks -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a]">checklist</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a]">Tasks &amp; Obligations</h3>
                </div>
                <span class="font-mono text-xs text-[#727973]">{{ $tasks->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#efeeeb]">
                @forelse($tasks as $t)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf9f6]">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1a1c1a] {{ $t->status === 'completed' ? 'line-through text-[#727973]' : '' }}">{{ $t->title }}</span>
                        <span class="text-[11px] text-[#727973]">Assigned to: {{ $t->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $t->priority === 'urgent' ? 'text-red-700' : 'text-[#424843]' }}">{{ $t->priority }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#727973] text-center">No tasks matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
