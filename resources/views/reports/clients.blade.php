@extends('layouts.app')

@section('title', 'Client Engagement & Portfolio Report — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Client Portfolio</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Client Engagement &amp; Portfolio Report</h1>
            <p class="text-xs text-[#646864] mt-0.5">Portfolio overview of corporate and individual retainers with active litigation counts and document status.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Report</span>
            </button>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">group</span>
                <span>Client Roster</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#23493a] text-white shadow-xs">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Bank Activity &amp; Reconciliation</a>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
        <div class="p-3.5 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
            <div class="text-xs font-medium text-[#646864]">
                Total Retainers: <span class="font-bold text-[#1a1a1a]">{{ $clients->count() }}</span>
            </div>
            <span class="text-[11px] font-mono text-[#23493a] font-semibold">Client Directory</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Client Name &amp; Category</th>
                        <th class="py-3 px-4">Contact Person / Details</th>
                        <th class="py-3 px-4 text-center">Active Matters</th>
                        <th class="py-3 px-4 text-center">Document Requests</th>
                        <th class="py-3 px-4">Recent Case Files</th>
                        <th class="py-3 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($clients as $client)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-[#1a1a1a]">{{ $client->name }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-[#faf8f5] text-[#646864] border border-[#e5e3dc]">
                                {{ ucfirst($client->type ?? 'Individual') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            @if($client->contact_person)
                            <div class="font-medium text-[#1a1a1a]">{{ $client->contact_person }}</div>
                            @endif
                            <div class="text-[11px] text-[#646864]">{{ $client->email }}</div>
                            @if($client->phone)
                            <div class="text-[11px] text-[#646864] font-mono">{{ $client->phone }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-mono text-xs font-bold text-[#23493a] px-2.5 py-0.5 rounded-full bg-[#23493a]/10 border border-[#23493a]/20">
                                {{ $client->matters_count }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono">
                            <span class="px-2 py-0.5 rounded text-[11px] {{ $client->document_requests_count > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-stone-50 text-stone-600' }}">
                                {{ $client->document_requests_count }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                @forelse($client->matters as $matter)
                                <a href="{{ route('matters.show', $matter->id) }}" class="block text-[11px] text-[#23493a] hover:underline truncate max-w-xs">
                                    &bull; {{ $matter->case_number }}: {{ $matter->title }}
                                </a>
                                @empty
                                <span class="text-[11px] text-[#8a8a8a]">No matters open</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Active</span>
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-[#646864]">
                            No client records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
