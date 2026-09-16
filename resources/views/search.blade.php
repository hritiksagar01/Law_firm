<x-app-layout>
    <x-slot name="title">Global Chambers Search — Quire Legal</x-slot>

    <!-- Header -->
    <div class="pb-6 border-b border-[#EFECE6] mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Unified LexisCore Index</span>
        </div>
        <h1 class="text-2xl font-serif font-bold text-[#222222]">Global Search Results</h1>
        <p class="text-sm text-[#766A5E]">Query: <span class="font-mono text-[#9F8349] font-semibold bg-[#F4EFEA] px-2 py-0.5 rounded">"{{ $q }}"</span></p>
    </div>

    <!-- Search Results Sections -->
    <div class="flex flex-col gap-6">

        <!-- Matching Matters -->
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">cases</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Matters &amp; Case Dossiers</h3>
                </div>
                <span class="font-mono text-xs text-[#766A5E]">{{ $matters->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#F4EFEA]">
                @forelse($matters as $m)
                <a href="{{ route('matters.show', $m->id) }}" class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors block group">
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-[#9F8349]">{{ $m->case_number }}</span>
                            <span class="text-xs font-semibold text-[#222222] group-hover:text-[#9F8349] transition-colors">{{ $m->title }}</span>
                        </div>
                        <span class="text-[11px] text-[#766A5E] mt-0.5">{{ $m->client->name }} · {{ $m->court_name ?? 'Chambers Arbitral Forum' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-[#F4ECE1]/50 text-[#9F8349] font-bold shrink-0">
                        {{ $m->stage }}
                    </span>
                </a>
                @empty
                <div class="p-4 text-xs text-[#766A5E] text-center">No matters matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Clients -->
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">group</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Clients &amp; Retainers</h3>
                </div>
                <span class="font-mono text-xs text-[#766A5E]">{{ $clients->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#F4EFEA]">
                @forelse($clients as $c)
                <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#222222]">{{ $c->name }}</span>
                        <span class="text-[11px] text-[#766A5E]">{{ $c->email }} · {{ $c->contact_person }}</span>
                    </div>
                    <span class="font-mono text-xs font-bold text-[#9F8349]">Trust: ${{ number_format($c->trust_balance, 2) }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#766A5E] text-center">No clients matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Filings & Evidence -->
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">description</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Vault Evidence &amp; Filings</h3>
                </div>
                <span class="font-mono text-xs text-[#766A5E]">{{ $documents->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#F4EFEA]">
                @forelse($documents as $doc)
                <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5]">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-700 text-lg">picture_as_pdf</span>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-[#222222]">{{ $doc->title }}</span>
                            <span class="font-mono text-[10px] text-[#766A5E]">{{ $doc->filename }} ({{ $doc->matter->case_number }})</span>
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#9F8349] hover:bg-[#F4EFEA] rounded flex items-center gap-1 text-xs">
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Download</span>
                    </a>
                </div>
                @empty
                <div class="p-4 text-xs text-[#766A5E] text-center">No documents matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Tasks -->
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">checklist</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Tasks &amp; Obligations</h3>
                </div>
                <span class="font-mono text-xs text-[#766A5E]">{{ $tasks->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#F4EFEA]">
                @forelse($tasks as $t)
                <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5]">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#222222] {{ $t->status === 'completed' ? 'line-through text-[#766A5E]' : '' }}">{{ $t->title }}</span>
                        <span class="text-[11px] text-[#766A5E]">Assigned to: {{ $t->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $t->priority === 'urgent' ? 'text-red-700' : 'text-[#554D45]' }}">{{ $t->priority }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#766A5E] text-center">No tasks matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
