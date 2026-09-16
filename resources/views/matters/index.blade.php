<x-app-layout>
    <x-slot name="title">Case Dossiers — {{ config('legal.app_name', 'Law Firm Management') }}</x-slot>

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#EFECE6] mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Litigation &amp; Court Cause Lists</span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#222222]">Matter Dossiers</h1>
            <p class="text-sm text-[#766A5E]">Centralized case files, judicial forums, orders, and procedural schedules</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#845D33] text-white text-xs font-medium hover:bg-[#6D4B27] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Initiate Case File</span>
            </a>
        </div>
    </div>

    <!-- Filter Pills & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Stage Filters -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
            <a href="{{ route('matters.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('stage') || request('stage') == 'all' ? 'bg-[#845D33] text-white' : 'bg-white border border-[#EAE4DC] text-[#554D45] hover:bg-[#F4EFEA]' }}">
                All ({{ \App\Models\Matter::count() }})
            </a>
            <a href="{{ route('matters.index', ['stage' => 'Discovery']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('stage') == 'Discovery' ? 'bg-[#845D33] text-white' : 'bg-white border border-[#EAE4DC] text-[#554D45] hover:bg-[#F4EFEA]' }}">
                Discovery ({{ \App\Models\Matter::where('stage', 'Discovery')->count() }})
            </a>
            <a href="{{ route('matters.index', ['stage' => 'Pleadings']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('stage') == 'Pleadings' ? 'bg-[#845D33] text-white' : 'bg-white border border-[#EAE4DC] text-[#554D45] hover:bg-[#F4EFEA]' }}">
                Pleadings ({{ \App\Models\Matter::where('stage', 'Pleadings')->count() }})
            </a>
            <a href="{{ route('matters.index', ['stage' => 'Pre-Trial']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('stage') == 'Pre-Trial' ? 'bg-[#845D33] text-white' : 'bg-white border border-[#EAE4DC] text-[#554D45] hover:bg-[#F4EFEA]' }}">
                Pre-Trial ({{ \App\Models\Matter::where('stage', 'Pre-Trial')->count() }})
            </a>
        </div>

        <!-- Search Input -->
        <form action="{{ route('matters.index') }}" method="GET" class="relative w-full md:w-72">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#766A5E] text-lg">search</span>
            <input name="q" value="{{ request('q') }}" placeholder="Filter case or docket #..." class="w-full h-9 pl-9 pr-3 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] focus:outline-none focus:border-[#845D33]"/>
        </form>
    </div>

    <!-- Matters Table -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                    <th class="py-3 px-4">Docket Number</th>
                    <th class="py-3 px-4">Matter Title &amp; Forum</th>
                    <th class="py-3 px-4">Client</th>
                    <th class="py-3 px-4">Stage</th>
                    <th class="py-3 px-4">Lead Attorney</th>
                    <th class="py-3 px-4 text-right">Budget &amp; Billed</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F4EFEA] text-sm">
                @forelse($matters as $matter)
                <tr class="hover:bg-[#FAF8F5] transition-colors group">
                    <td class="py-3.5 px-4 font-mono text-xs font-medium text-[#222222]">
                        <span class="px-2 py-1 rounded bg-[#F4EFEA] text-[#845D33] font-semibold border border-[#EAE4DC]">
                            {{ $matter->case_number }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex flex-col">
                            <a href="{{ route('matters.show', $matter->id) }}" class="font-semibold text-[#222222] hover:text-[#845D33] transition-colors">
                                {{ $matter->title }}
                            </a>
                            <span class="text-xs text-[#766A5E] mt-0.5">{{ $matter->court_name }} <span class="mx-1">·</span> {{ $matter->judge_name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-[#554D45]">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#766A5E]">business</span>
                            <span class="font-medium text-xs">{{ $matter->client->name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#F8F4EE] text-[#845D33] border border-[#E8DAC8] font-semibold">
                            {{ $matter->stage }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2">
                            <img alt="{{ $matter->leadAttorney?->name }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-[#EAE4DC]" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                            <span class="text-xs text-[#554D45]">{{ $matter->leadAttorney?->name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="flex flex-col items-end">
                            <span class="font-mono text-xs font-semibold text-[#222222]">{{ config('legal.currency_symbol', '₹') }}{{ number_format($matter->totalBilledAmount(), 2) }}</span>
                            <span class="font-mono text-[10px] text-[#766A5E]">of {{ config('legal.currency_symbol', '₹') }}{{ number_format($matter->budget, 0) }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <a href="{{ route('matters.show', $matter->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#F4EFEA] text-[#222222] hover:bg-[#845D33] hover:text-white transition-colors text-xs font-medium">
                            <span>Open</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-[#766A5E] text-sm">
                        No matter dossiers found matching query.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
