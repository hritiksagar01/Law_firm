@extends('layouts.app')

@section('title', 'Global Search Results — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8 space-y-6">

    <!-- Header -->
    <div class="pb-5 border-b border-[#e5e3dc]">
        <div class="flex items-center gap-2 mb-1">
            <span class="font-mono text-[11px] text-[#23493a] uppercase tracking-wider font-semibold">Unified Chambers Index</span>
        </div>
        <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Global Search Results</h1>
        <p class="text-xs text-[#646864] mt-0.5">Query: <span class="font-mono text-[#23493a] font-semibold bg-[#23493a]/10 px-2 py-0.5 rounded">"{{ $q }}"</span></p>
    </div>

    <!-- Search Results Sections -->
    <div class="flex flex-col gap-6">

        <!-- Matching Matters -->
        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">cases</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Matters &amp; Case Dossiers</h3>
                </div>
                <span class="font-mono text-xs text-[#646864] font-medium">{{ $matters->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#e5e3dc]">
                @forelse($matters as $m)
                <a href="{{ route('matters.show', $m->id) }}" class="p-4 flex items-center justify-between hover:bg-[#faf8f5] transition-colors block group">
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-[#23493a]">{{ $m->case_number }}</span>
                            <span class="text-xs font-semibold text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">{{ $m->title }}</span>
                        </div>
                        <span class="text-[11px] text-[#646864] mt-0.5">{{ $m->client->name }} · {{ $m->court_name ?? 'Chambers Forum' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-[#23493a]/10 text-[#23493a] font-semibold shrink-0">
                        {{ $m->stage }}
                    </span>
                </a>
                @empty
                <div class="p-4 text-xs text-[#646864] text-center">No matters matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Clients -->
        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">group</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Clients &amp; Retainers</h3>
                </div>
                <span class="font-mono text-xs text-[#646864] font-medium">{{ $clients->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#e5e3dc]">
                @forelse($clients as $c)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf8f5] transition-colors">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1a1a1a]">{{ $c->name }}</span>
                        <span class="text-[11px] text-[#646864]">{{ $c->email }} · {{ $c->contact_person }}</span>
                    </div>
                    <span class="font-mono text-xs font-semibold text-[#23493a]">Trust: ${{ number_format($c->trust_balance, 2) }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#646864] text-center">No clients matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Filings & Evidence -->
        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">description</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Vault Evidence &amp; Filings</h3>
                </div>
                <span class="font-mono text-xs text-[#646864] font-medium">{{ $documents->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#e5e3dc]">
                @forelse($documents as $doc)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf8f5] transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-rose-600 text-lg">picture_as_pdf</span>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-[#1a1a1a]">{{ $doc->title }}</span>
                            <span class="font-mono text-[10px] text-[#646864]">{{ $doc->filename }} ({{ $doc->matter->case_number }})</span>
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $doc->id) }}" class="px-2.5 py-1 text-[#23493a] hover:bg-[#23493a]/10 rounded-lg flex items-center gap-1 text-xs font-medium transition-colors">
                        <span class="material-symbols-outlined text-sm">download</span>
                        <span>Download</span>
                    </a>
                </div>
                @empty
                <div class="p-4 text-xs text-[#646864] text-center">No documents matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

        <!-- Matching Tasks -->
        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-lg">checklist</span>
                    <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Tasks &amp; Obligations</h3>
                </div>
                <span class="font-mono text-xs text-[#646864] font-medium">{{ $tasks->count() }} Matches</span>
            </div>

            <div class="divide-y divide-[#e5e3dc]">
                @forelse($tasks as $t)
                <div class="p-4 flex items-center justify-between hover:bg-[#faf8f5] transition-colors">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1a1a1a] {{ $t->status === 'completed' ? 'line-through text-[#646864]' : '' }}">{{ $t->title }}</span>
                        <span class="text-[11px] text-[#646864]">Assigned to: {{ $t->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $t->priority === 'urgent' ? 'text-rose-700' : 'text-[#646864]' }}">{{ $t->priority }}</span>
                </div>
                @empty
                <div class="p-4 text-xs text-[#646864] text-center">No tasks matching "{{ $q }}".</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
