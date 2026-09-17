@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Client Portfolio</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Client Engagement &amp; Portfolio Report</h1>
            <p class="text-sm text-[#766A5E] mt-1">Portfolio overview of corporate and individual retainers with active litigation counts and document status.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Report</span>
            </button>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">group</span>
                <span>Client Roster</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Advocate Workload</a>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
            <div class="text-xs font-medium text-[#554D45]">
                Total Retainers: <span class="font-bold text-[#222222]">{{ $clients->count() }}</span>
            </div>
            <span class="text-[11px] font-mono text-[#766A5E]">Client Directory</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Client Name &amp; Category</th>
                        <th class="py-3 px-4 font-semibold">Contact Person / Details</th>
                        <th class="py-3 px-4 font-semibold text-center">Active Matters</th>
                        <th class="py-3 px-4 font-semibold text-center">Document Requests</th>
                        <th class="py-3 px-4 font-semibold">Recent Case Files</th>
                        <th class="py-3 px-4 text-right font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($clients as $client)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-[#222222]">{{ $client->name }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-[#FAF8F5] text-[#766A5E] border border-[#EAE4DC]">
                                {{ ucfirst($client->type ?? 'Individual') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            @if($client->contact_person)
                            <div class="font-medium text-[#222222]">{{ $client->contact_person }}</div>
                            @endif
                            <div class="text-[11px] text-[#766A5E]">{{ $client->email }}</div>
                            @if($client->phone)
                            <div class="text-[11px] text-[#766A5E] font-mono">{{ $client->phone }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-mono text-sm font-bold text-[#9F8349] px-2.5 py-0.5 rounded-full bg-[#FAF8F5] border border-[#EAE4DC]">
                                {{ $client->matters_count }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono">
                            <span class="px-2 py-0.5 rounded text-[11px] {{ $client->document_requests_count > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-gray-50 text-gray-600' }}">
                                {{ $client->document_requests_count }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                @forelse($client->matters as $matter)
                                <a href="{{ route('matters.show', $matter->id) }}" class="block text-[11px] text-[#9F8349] hover:underline truncate max-w-xs">
                                    &bull; {{ $matter->case_number }}: {{ $matter->title }}
                                </a>
                                @empty
                                <span class="text-[11px] text-[#766A5E]">No matters open</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Active</span>
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-[#766A5E]">
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
