<x-app-layout>
    <x-slot name="title">{{ config('legal.app_name', 'Vennamraj Associates') }} — Chambers Dashboard</x-slot>

    <!-- Top Greeting & Header Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 mb-2">
        <div class="flex flex-col">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F4EFEA] font-mono text-[11px] text-[#554D45] border border-[#EAE4DC]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#B88B56]"></span>
                    ACTIVE ADVOCATE SESSION · {{ strtoupper(auth()->user()->name ?? 'Counsel') }}
                </span>
                <span class="text-[#766A5E] text-xs">/</span>
                <span class="text-xs font-semibold text-[#554D45] uppercase tracking-wider">{{ auth()->user()->title ?? 'Senior Advocate & Managing Partner' }}</span>
            </div>
            <h1 class="text-3xl font-serif text-[#222222] font-bold tracking-tight">Good afternoon, {{ auth()->user()->name ?? 'Counsel' }}</h1>
            <p class="text-sm text-[#766A5E]">{{ now()->format('l, F j, Y') }} <span class="mx-1.5">·</span> {{ auth()->user()->firm->name ?? 'Vennamraj Associates, Advocates' }} <span class="mx-1.5">·</span> Chambers</p>
        </div>

        <!-- Quick Action Toolbar -->
        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <button @click="openTimeModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#9F8349] text-white text-xs font-medium hover:bg-[#856C36] shadow-sm transition-all group">
                <span class="material-symbols-outlined text-[#B88B56] text-base group-hover:rotate-12 transition-transform">timer</span>
                <span>Log Professional Hours</span>
                <span class="font-mono text-[10px] bg-black/30 text-[#F4ECE1] px-1.5 py-0.5 rounded ml-1">⌘T</span>
            </button>
            <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-1.5 px-3.5 h-10 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-medium hover:bg-[#F4EFEA] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[#766A5E] text-base">add_box</span>
                <span>New Case Dossier</span>
            </a>
            <a href="{{ route('briefing') }}" class="inline-flex items-center gap-1.5 px-3.5 h-10 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-medium hover:bg-[#F4EFEA] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[#766A5E] text-base">print</span>
                <span>Morning Broadsheet</span>
            </a>
        </div>
    </div>

    <!-- Row 1: Executive KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Active Matters -->
        <div class="p-5 bg-white rounded-xl border border-[#EFECE6] shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between relative overflow-hidden group hover:border-[#8C7F72] transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="font-mono text-[11px] uppercase tracking-wider text-[#766A5E]">Active Case Dossiers</span>
                    <div class="text-3xl font-serif font-bold text-[#222222] mt-1">{{ \App\Models\Matter::count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#F4EFEA] flex items-center justify-center text-[#9F8349]">
                    <span class="material-symbols-outlined text-xl">gavel</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4EFEA] flex items-center justify-between text-xs">
                <span class="text-[#9F8349] font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">trending_up</span> Active Practice
                </span>
                <span class="font-mono text-[11px] text-[#766A5E]">High Court &amp; Tribunals</span>
            </div>
        </div>

        <!-- Card 2: Billable Hours MTD -->
        <div class="p-5 bg-white rounded-xl border border-[#EFECE6] shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between relative overflow-hidden group hover:border-[#8C7F72] transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="font-mono text-[11px] uppercase tracking-wider text-[#766A5E]">Professional Hours (MTD)</span>
                    <div class="text-3xl font-mono font-bold text-[#222222] mt-1">142.5 <span class="text-xs text-[#766A5E] font-normal">hrs</span></div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#B88B56]/30 flex items-center justify-center text-[#9F8349]">
                    <span class="material-symbols-outlined text-xl">timelapse</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4EFEA] flex items-center justify-between text-xs">
                <span class="text-[#9F8349] font-medium font-mono">{{ config('legal.currency_symbol', '₹') }}1,92,625 Realized</span>
                <span class="font-mono text-[11px] text-[#766A5E]">94.2% Realization</span>
            </div>
        </div>

        <!-- Card 3: Trust Funds & Retainers -->
        <div class="p-5 bg-white rounded-xl border border-[#EFECE6] shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between relative overflow-hidden group hover:border-[#8C7F72] transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="font-mono text-[11px] uppercase tracking-wider text-[#766A5E]">Client Retainer &amp; Trust Balance</span>
                    <div class="text-3xl font-mono font-bold text-[#222222] mt-1">{{ config('legal.currency_symbol', '₹') }}{{ number_format(\App\Models\Client::sum('trust_balance'), 2) }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#9F8349]/10 flex items-center justify-center text-[#9F8349]">
                    <span class="material-symbols-outlined text-xl">account_balance</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4EFEA] flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 text-[#9F8349] font-medium">
                    <span class="w-2 h-2 rounded-full bg-[#F8F4EE]0"></span> Verified Ledger
                </span>
                <span class="font-mono text-[11px] text-[#766A5E]">{{ \App\Models\Client::count() }} Retained Clients</span>
            </div>
        </div>

        <!-- Card 4: Court Deadlines -->
        <div class="p-5 bg-white rounded-xl border border-[#EFECE6] shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between relative overflow-hidden group hover:border-[#8C7F72] transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="font-mono text-[11px] uppercase tracking-wider text-[#766A5E]">Filings &amp; Hearings</span>
                    <div class="text-3xl font-serif font-bold text-[#222222] mt-1">{{ \App\Models\Event::count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-700">
                    <span class="material-symbols-outlined text-xl">calendar_clock</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4EFEA] flex items-center justify-between text-xs">
                <span class="text-red-700 font-medium font-mono">1 Statutory Due 5PM</span>
                <span class="font-mono text-[11px] text-[#766A5E]">Next: Thu 10:00 AM</span>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Workflow Canvas -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Left 2-Column Section: Active Matters & Recent Time Ledger -->
        <div class="xl:col-span-2 flex flex-col gap-6">
            
            <!-- Active Matters Dossier Table -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">cases</span>
                        <h2 class="text-base font-semibold text-[#222222]">Active Litigation &amp; Transaction Matters</h2>
                    </div>
                    <a href="{{ route('matters.index') }}" class="text-xs font-medium text-[#9F8349] hover:underline flex items-center gap-1">
                        <span>View All Matters</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                                <th class="py-3 px-4">Docket / Matter</th>
                                <th class="py-3 px-4">Client</th>
                                <th class="py-3 px-4">Stage</th>
                                <th class="py-3 px-4">Lead Counsel</th>
                                <th class="py-3 px-4 text-right">Budget Realized</th>
                                <th class="py-3 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4EFEA] text-sm">
                            @foreach(\App\Models\Matter::with(['client', 'leadAttorney'])->get() as $matter)
                            <tr class="hover:bg-[#FAF8F5] transition-colors group">
                                <td class="py-3 px-4">
                                    <div class="flex flex-col">
                                        <a href="{{ route('matters.show', $matter->id) }}" class="font-medium text-[#222222] group-hover:text-[#9F8349] transition-colors">
                                            {{ $matter->title }}
                                        </a>
                                        <span class="font-mono text-xs text-[#766A5E]">{{ $matter->case_number }} <span class="mx-1">·</span> {{ $matter->practice_area }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-[#554D45]">
                                    {{ $matter->client->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#F8F4EE] text-[#9F8349] border border-[#E8DAC8] font-semibold">
                                        {{ $matter->stage }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <img alt="{{ $matter->leadAttorney?->name }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-[#EAE4DC]" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                                        <span class="text-xs text-[#554D45]">{{ $matter->leadAttorney?->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                     <div class="flex flex-col items-end">
                                         <span class="font-mono text-xs font-medium text-[#222222]">
                                             {{ config('legal.currency_symbol', '₹') }}{{ number_format($matter->totalBilledAmount(), 2) }}
                                         </span>
                                         <span class="text-[10px] text-[#766A5E] font-mono">
                                             of {{ config('legal.currency_symbol', '₹') }}{{ number_format($matter->budget, 0) }}
                                         </span>
                                     </div>
                                 </td>
                                 <td class="py-3 px-4 text-center">
                                     <a href="{{ route('matters.show', $matter->id) }}" class="p-1.5 text-[#766A5E] hover:text-[#9F8349] hover:bg-[#F4EFEA] rounded inline-flex" title="Open Dossier">
                                         <span class="material-symbols-outlined text-lg">folder_open</span>
                                     </a>
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Billable Time Entries -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">timer</span>
                        <h2 class="text-base font-semibold text-[#222222]">Recent Professional Time Entries</h2>
                    </div>
                    <button @click="openTimeModal = true" class="text-xs font-medium text-[#9F8349] hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Log Hours</span>
                    </button>
                </div>

                <div class="divide-y divide-[#F4EFEA]">
                    @foreach(\App\Models\TimeEntry::with(['matter', 'user'])->latest()->take(4)->get() as $entry)
                    <div class="p-4 hover:bg-[#FAF8F5] transition-colors flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#FAF8F5] flex items-center justify-center text-[#9F8349] shrink-0 font-mono text-xs font-semibold">
                                {{ $entry->hours }}h
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm text-[#222222]">{{ $entry->matter->title }}</span>
                                    <span class="font-mono text-[11px] px-1.5 py-0.5 rounded bg-[#B88B56]/60 text-[#9F8349] font-semibold">{{ $entry->activity_code }}</span>
                                    <span class="text-xs text-[#766A5E]">{{ $entry->activity_name }}</span>
                                </div>
                                <p class="text-xs text-[#554D45] mt-1 leading-relaxed">{{ $entry->narrative }}</p>
                                <div class="flex items-center gap-3 mt-1.5 text-[11px] text-[#766A5E]">
                                    <span>Logged by {{ $entry->user->name }}</span>
                                    <span>·</span>
                                    <span>{{ $entry->entry_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="font-mono text-sm font-semibold text-[#222222]">{{ config('legal.currency_symbol', '₹') }}{{ number_format($entry->total_amount, 2) }}</span>
                            <span class="text-[10px] text-[#9F8349] font-medium uppercase tracking-wider mt-0.5">Unbilled</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right 1-Column Section: Court Docket Calendar & Tasks -->
        <div class="flex flex-col gap-6">
            
            <!-- Court Docket Ribbon -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#ba1a1a] text-xl">event_upcoming</span>
                        <h2 class="text-base font-semibold text-[#222222]">Chambers Docket</h2>
                    </div>
                    <span class="font-mono text-xs text-[#766A5E]">Indian Standard Time (IST)</span>
                </div>

                <div class="p-4 flex flex-col gap-3">
                    @foreach(\App\Models\Event::with('matter')->orderBy('start_time')->get() as $event)
                    <div class="p-3.5 rounded-lg border {{ $event->is_statutory_deadline ? 'border-red-200 bg-red-50/50' : 'border-[#EFECE6] bg-[#FAF8F5]' }} flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold {{ $event->is_statutory_deadline ? 'text-red-700' : 'text-[#9F8349]' }}">
                                {{ $event->start_time->format('D, M d · g:i A') }}
                            </span>
                            @if($event->is_statutory_deadline)
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-red-100 text-red-800 font-bold uppercase tracking-wider">Statutory Due</span>
                            @else
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-[#F4EFEA] text-[#554D45]">{{ $event->event_type }}</span>
                            @endif
                        </div>
                        <h4 class="text-sm font-semibold text-[#222222]">{{ $event->title }}</h4>
                        @if($event->matter)
                        <span class="text-xs text-[#766A5E] font-medium">{{ $event->matter->case_number }} — {{ $event->matter->title }}</span>
                        @endif
                        @if($event->location)
                        <div class="flex items-center gap-1.5 text-xs text-[#554D45] mt-1">
                            <span class="material-symbols-outlined text-sm text-[#766A5E]">pin_drop</span>
                            <span>{{ $event->location }}</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Priority Litigation Tasks -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">check_box</span>
                        <h2 class="text-base font-semibold text-[#222222]">Priority Action Items</h2>
                    </div>
                    <span class="font-mono text-xs text-[#766A5E]">2 Pending</span>
                </div>

                <div class="p-4 flex flex-col gap-2.5">
                    @foreach(\App\Models\Task::with(['assignee', 'matter'])->get() as $task)
                    <div class="p-3 rounded-lg border border-[#EFECE6] hover:border-[#EAE4DC] transition-colors flex items-start gap-3">
                        <input type="checkbox" class="w-4 h-4 rounded text-[#9F8349] accent-[#9F8349] mt-0.5 cursor-pointer"/>
                        <div class="flex flex-col min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-[#222222] truncate">{{ $task->title }}</span>
                                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded font-bold uppercase {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700' : 'bg-[#B88B56]/60 text-[#9F8349]' }}">
                                    {{ $task->priority }}
                                </span>
                            </div>
                            <p class="text-[11px] text-[#554D45] mt-0.5 line-clamp-2">{{ $task->description }}</p>
                            <div class="flex items-center justify-between mt-2 text-[10px] text-[#766A5E]">
                                <span>Assigned: {{ $task->assignee->name }}</span>
                                <span class="font-mono text-red-700 font-medium">Due {{ $task->due_date?->format('M d') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
