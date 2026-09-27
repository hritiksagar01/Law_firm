@extends($isClient ? 'portal.layout' : 'layouts.app')

@section('title', ($isClient ? 'Search Case Files & Dossiers' : 'Global Search & Unified Practice Index') . ' — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('header_title', $isClient ? 'Global Case Search' : 'Unified Practice Search Engine')

@section('content')
<div class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8 space-y-6">

    <!-- Top Header Bar & Search Metrics -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#f0eee8]">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-[11px] text-[#23493a] uppercase tracking-wider font-semibold">
                        {{ $isClient ? 'Client Confidential File Index' : 'Unified Chambers Index & Practice Records' }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                        Authorization Guard Active
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">
                    {{ $isClient ? 'Search My Cases, Documents & Communications' : 'Global Practice Search Engine' }}
                </h1>
                <p class="text-xs text-[#646864] mt-0.5">
                    Multi-entity retrieval across Clients, Matters, Documents, Parties, Messages, Tasks, Notes, and Calendar Events.
                </p>
            </div>

            <!-- Total Results Badge -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="px-3.5 py-2 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-right">
                    <span class="block text-[10px] font-mono uppercase text-[#8e8e8e] font-semibold">Indexed Matches</span>
                    <span class="text-lg font-bold font-mono text-[#23493a]">{{ $totalMatches }}</span>
                </div>
                @if($hasFilters)
                <a href="{{ route('search') }}" class="px-3 py-2 text-xs font-medium text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
                    <span>Clear Filters</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Main Search Input & Filter Toggle Form -->
        <form method="GET" action="{{ route('search') }}" id="searchForm" class="mt-5 space-y-4" x-data="{ filtersExpanded: {{ $hasFilters && !request()->has('q') || request()->has('filter_open') ? 'true' : 'false' }} }">
            <input type="hidden" name="type" value="{{ $type }}" id="searchTypeInput">

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8e8e8e]">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </span>
                    <input type="text" 
                           name="q" 
                           value="{{ $q }}" 
                           placeholder="Search names, matter numbers (e.g. MAT-2024), document titles, task subjects, notes, parties, metadata..." 
                           class="w-full pl-10 pr-24 py-2.5 bg-[#faf8f5] border border-[#e5e3dc] rounded-lg text-sm text-[#1a1a1a] placeholder-[#8e8e8e] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#23493a]/20 focus:border-[#23493a] transition-all">
                    
                    @if($q !== '')
                    <a href="{{ route('search', array_filter(request()->except('q'))) }}" class="absolute inset-y-0 right-10 pr-2 flex items-center text-[#8e8e8e] hover:text-[#1a1a1a]" title="Clear search text">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </a>
                    @endif
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-[#8e8e8e] bg-white border border-[#e5e3dc] rounded shadow-2xs">/</kbd>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" 
                            @click="filtersExpanded = !filtersExpanded" 
                            :class="filtersExpanded ? 'bg-[#23493a] text-white' : 'bg-white text-[#414844] hover:bg-[#faf8f5]'"
                            class="px-3.5 py-2.5 border border-[#e5e3dc] rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 cursor-pointer shrink-0">
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                        <span>Filters</span>
                        @php
                            $activeCount = 0;
                            foreach(['client', 'matter', 'matter_number', 'document', 'document_type', 'category', 'attorney', 'paralegal', 'status', 'tag', 'date', 'date_from', 'date_to'] as $k) {
                                if (request()->filled($k)) $activeCount++;
                            }
                        @endphp
                        @if($activeCount > 0)
                        <span class="ml-1 w-4 h-4 rounded-full bg-amber-400 text-[#1a1a1a] text-[10px] font-bold flex items-center justify-center font-mono">{{ $activeCount }}</span>
                        @endif
                    </button>

                    <button type="submit" 
                            class="px-5 py-2.5 bg-[#23493a] hover:bg-[#1b382d] text-white rounded-lg text-xs font-semibold tracking-wide transition-colors flex items-center gap-1.5 shadow-xs shrink-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                        <span>Search</span>
                    </button>
                </div>
            </div>

            <!-- Active Filter Badges Bar -->
            @if($hasFilters)
            <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                <span class="text-[#8e8e8e] text-[11px] font-medium mr-1">Active filters:</span>
                
                @if($q !== '')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#23493a]/10 text-[#23493a] border border-[#23493a]/20 font-medium text-[11px]">
                    Query: <strong class="font-semibold">"{{ $q }}"</strong>
                    <a href="{{ route('search', request()->except('q')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('client'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-[11px]">
                    Client: {{ $availableClients->firstWhere('id', request('client'))?->name ?? request('client') }}
                    <a href="{{ route('search', request()->except('client')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('matter'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px]">
                    Matter: {{ $availableMatters->firstWhere('id', request('matter'))?->title ?? request('matter') }}
                    <a href="{{ route('search', request()->except('matter')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('matter_number'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-800 border border-purple-200 font-mono text-[11px]">
                    No: {{ request('matter_number') }}
                    <a href="{{ route('search', request()->except('matter_number')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('document'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-800 border border-sky-200 text-[11px]">
                    Doc: {{ request('document') }}
                    <a href="{{ route('search', request()->except('document')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('document_type'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px]">
                    Category: {{ request('document_type') }}
                    <a href="{{ route('search', request()->except(['document_type', 'category'])) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('attorney'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-800 border border-indigo-200 text-[11px]">
                    Attorney: {{ $availableAttorneys->firstWhere('id', request('attorney'))?->name ?? request('attorney') }}
                    <a href="{{ route('search', request()->except('attorney')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('paralegal'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-800 border border-teal-200 text-[11px]">
                    Paralegal: {{ $availableParalegals->firstWhere('id', request('paralegal'))?->name ?? request('paralegal') }}
                    <a href="{{ route('search', request()->except('paralegal')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('status'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-zinc-100 text-zinc-800 border border-zinc-300 text-[11px] capitalize">
                    Status: {{ str_replace('_', ' ', request('status')) }}
                    <a href="{{ route('search', request()->except('status')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('tag'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-800 border border-rose-200 text-[11px]">
                    Tag: #{{ request('tag') }}
                    <a href="{{ route('search', request()->except('tag')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('date'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-mono text-[11px]">
                    Date: {{ request('date') }}
                    <a href="{{ route('search', request()->except('date')) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif

                @if(request()->filled('date_from') || request()->filled('date_to'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 border border-slate-300 font-mono text-[11px]">
                    Span: {{ request('date_from') ?: 'Any' }} &rarr; {{ request('date_to') ?: 'Present' }}
                    <a href="{{ route('search', request()->except(['date_from', 'date_to'])) }}" class="hover:text-rose-700 ml-0.5">&times;</a>
                </span>
                @endif
            </div>
            @endif

            <!-- Collapsible Advanced Filters Panel -->
            <div x-show="filtersExpanded" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="p-4 bg-[#faf8f5] border border-[#e5e3dc] rounded-lg space-y-4">
                
                <div class="flex items-center justify-between border-b border-[#e5e3dc] pb-2">
                    <span class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#23493a]">filter_alt</span>
                        Precision Criteria &amp; Filter Attributes
                    </span>
                    <button type="button" @click="filtersExpanded = false" class="text-xs text-[#8e8e8e] hover:text-[#1a1a1a]">
                        Collapse &times;
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <!-- Client Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Client</label>
                        <select name="client" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">All Clients</option>
                            @foreach($availableClients as $cl)
                                <option value="{{ $cl->id }}" {{ (string)request('client') === (string)$cl->id ? 'selected' : '' }}>
                                    {{ $cl->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Matter Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Matter / Dossier</label>
                        <select name="matter" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">All Matters</option>
                            @foreach($availableMatters as $mOption)
                                <option value="{{ $mOption->id }}" {{ (string)request('matter') === (string)$mOption->id ? 'selected' : '' }}>
                                    {{ $mOption->case_number }} - {{ Str::limit($mOption->title, 25) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Matter Number -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Matter Number</label>
                        <input type="text" 
                               name="matter_number" 
                               value="{{ request('matter_number') }}" 
                               placeholder="e.g. MAT-2024-001" 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] placeholder-[#8e8e8e] focus:outline-none focus:border-[#23493a] font-mono">
                    </div>

                    <!-- Document Title / File -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Document Title / File</label>
                        <input type="text" 
                               name="document" 
                               value="{{ request('document') }}" 
                               placeholder="e.g. Petition, Bail, KYC..." 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] placeholder-[#8e8e8e] focus:outline-none focus:border-[#23493a]">
                    </div>

                    <!-- Document Type / Category -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Doc Type / Category</label>
                        <select name="document_type" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">All Types &amp; Categories</option>
                            @foreach($documentTypes as $dtKey => $dtLabel)
                                <option value="{{ $dtKey }}" {{ request('document_type') === $dtKey ? 'selected' : '' }}>
                                    {{ $dtLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(! $isClient)
                    <!-- Attorney Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Attorney / Counsel</label>
                        <select name="attorney" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">All Attorneys</option>
                            @foreach($availableAttorneys as $at)
                                <option value="{{ $at->id }}" {{ (string)request('attorney') === (string)$at->id ? 'selected' : '' }}>
                                    {{ $at->name }} ({{ ucfirst($at->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Paralegal Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Paralegal / Staff</label>
                        <select name="paralegal" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">All Support Staff</option>
                            @foreach($availableParalegals as $para)
                                <option value="{{ $para->id }}" {{ (string)request('paralegal') === (string)$para->id ? 'selected' : '' }}>
                                    {{ $para->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Status Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Status</label>
                        <select name="status" class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">Any Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active / Open</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Waiting / Pending</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed / Closed</option>
                            <option value="lead" {{ request('status') === 'lead' ? 'selected' : '' }}>Lead / Prospective</option>
                        </select>
                    </div>

                    <!-- Tag Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Tag / Label</label>
                        <input type="text" 
                               name="tag" 
                               value="{{ request('tag') }}" 
                               placeholder="e.g. urgent, litigation, kyc..." 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] placeholder-[#8e8e8e] focus:outline-none focus:border-[#23493a]">
                    </div>

                    <!-- Exact Date Filter -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Specific Date</label>
                        <input type="date" 
                               name="date" 
                               value="{{ request('date') }}" 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] font-mono">
                    </div>

                    <!-- Date Range From -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Date Range: From</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] font-mono">
                    </div>

                    <!-- Date Range To -->
                    <div>
                        <label class="block font-medium text-[#414844] mb-1">Date Range: To</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="w-full px-2.5 py-1.5 bg-white border border-[#e5e3dc] rounded-md text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] font-mono">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#e5e3dc]">
                    <a href="{{ route('search') }}" class="px-3 py-1.5 rounded text-xs text-[#646864] hover:text-[#1a1a1a] hover:bg-white transition-colors">
                        Reset Filters
                    </a>
                    <button type="submit" class="px-4 py-1.5 bg-[#23493a] hover:bg-[#1b382d] text-white rounded text-xs font-semibold tracking-wide transition-colors">
                        Apply Precision Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Entity Navigation Tabs Bar -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] p-1.5 shadow-xs overflow-x-auto">
        <nav class="flex items-center gap-1 min-w-max text-xs font-medium">
            @php
                $tabs = [
                    'all' => ['label' => 'All Entities', 'count' => $totalMatches, 'icon' => 'grid_view'],
                    'matters' => ['label' => 'Matters', 'count' => $mattersCount, 'icon' => 'cases'],
                    'clients' => ['label' => 'Clients', 'count' => $clientsCount, 'icon' => 'group'],
                    'documents' => ['label' => 'Documents', 'count' => $documentsCount, 'icon' => 'description'],
                    'parties' => ['label' => 'Parties', 'count' => $partiesCount, 'icon' => 'balance'],
                    'messages' => ['label' => 'Messages', 'count' => $messagesCount, 'icon' => 'forum'],
                    'tasks' => ['label' => 'Tasks', 'count' => $tasksCount, 'icon' => 'checklist'],
                    'notes' => ['label' => 'Notes', 'count' => $notesCount, 'icon' => 'edit_note'],
                    'events' => ['label' => 'Calendar Events', 'count' => $eventsCount, 'icon' => 'calendar_month'],
                ];
            @endphp

            @foreach($tabs as $tabKey => $tabInfo)
                @php
                    $isActive = ($type === $tabKey);
                    $url = route('search', array_merge(request()->query(), ['type' => $tabKey]));
                @endphp
                <a href="{{ $url }}" 
                   class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors {{ $isActive ? 'bg-[#23493a] text-white shadow-xs font-semibold' : 'text-[#5e625e] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    <span class="material-symbols-outlined text-[17px]">{{ $tabInfo['icon'] }}</span>
                    <span>{{ $tabInfo['label'] }}</span>
                    <span class="font-mono text-[11px] px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#faf8f5] border border-[#e5e3dc] text-[#646864]' }}">
                        {{ $tabInfo['count'] }}
                    </span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Main Results Container -->
    <div class="space-y-6">

        @if($totalMatches === 0)
            <!-- Zero State / Empty Notice -->
            <div class="bg-white rounded-xl border border-[#e5e3dc] p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-[#23493a]/10 text-[#23493a] flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-2xl">search_off</span>
                </div>
                <h3 class="text-base font-semibold text-[#1a1a1a]">No authorized records found</h3>
                <p class="text-xs text-[#646864] max-w-md mx-auto mt-1">
                    @if($hasFilters)
                        Your query and selected filters did not match any accessible records. Try broadening your terms or removing specific filters.
                    @else
                        Enter a keyword, case number, party name, or document title above to explore the unified chambers database.
                    @endif
                </p>
                <div class="flex items-center justify-center gap-2 mt-4">
                    @if($hasFilters)
                    <a href="{{ route('search') }}" class="px-4 py-2 bg-[#23493a] text-white rounded-lg text-xs font-medium hover:bg-[#1b382d] transition-colors">
                        Clear all filters
                    </a>
                    @endif
                    <a href="{{ $isClient ? route('portal.dashboard') : route('dashboard') }}" class="px-4 py-2 bg-white border border-[#e5e3dc] text-[#414844] rounded-lg text-xs font-medium hover:bg-[#faf8f5] transition-colors">
                        Return to Dashboard
                    </a>
                </div>
            </div>
        @else

            <!-- 1. MATTERS SECTION -->
            @if(($type === 'all' || $type === 'matters') && $matters->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">cases</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Matters &amp; Case Dossiers</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $mattersCount }} {{ Str::plural('Match', $mattersCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($matters as $m)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-[#23493a] bg-[#23493a]/10 px-2 py-0.5 rounded">
                                    {{ $m->case_number }}
                                </span>
                                <a href="{{ $isClient ? route('portal.matters.show', $m->id) : route('matters.show', $m->id) }}" class="text-sm font-semibold text-[#1a1a1a] hover:text-[#23493a] transition-colors">
                                    {{ $m->title }}
                                </a>
                                <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-full font-semibold {{ in_array($m->status, ['closed', 'resolved']) ? 'bg-zinc-100 text-zinc-600' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $m->stage ?: ($m->status ?: 'Active') }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                @if($m->client)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">group</span>
                                    <span>Client: <strong>{{ $m->client->name }}</strong></span>
                                </span>
                                @endif

                                @if($m->court_name)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">gavel</span>
                                    <span>Court: {{ $m->court_name }}</span>
                                </span>
                                @endif

                                @if($m->opposing_party)
                                <span class="flex items-center gap-1 text-rose-700 font-medium">
                                    <span class="material-symbols-outlined text-[15px]">balance</span>
                                    <span>Opposing: {{ $m->opposing_party }}</span>
                                </span>
                                @endif

                                @if(! $isClient && $m->leadAttorney)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">person</span>
                                    <span>Lead: {{ $m->leadAttorney->name }}</span>
                                </span>
                                @endif

                                @if($m->filing_date)
                                <span class="flex items-center gap-1 font-mono text-[11px]">
                                    <span class="material-symbols-outlined text-[14px] text-[#8e8e8e]">event</span>
                                    <span>Filed: {{ $m->filing_date->format('d M Y') }}</span>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ $isClient ? route('portal.matters.show', $m->id) : route('matters.show', $m->id) }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>Open Matter Dossier</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 2. CLIENTS SECTION -->
            @if(($type === 'all' || $type === 'clients') && $clients->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">group</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Clients &amp; Retainers</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $clientsCount }} {{ Str::plural('Match', $clientsCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($clients as $c)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-[#1a1a1a]">
                                    {{ $c->name }}
                                </span>
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
                                    {{ $c->category ?: ($c->type ?: 'Individual') }}
                                </span>
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full {{ $c->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $c->status }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                @if($c->email)
                                <span class="flex items-center gap-1 font-mono text-[11px]">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">mail</span>
                                    <span>{{ $c->email }}</span>
                                </span>
                                @endif

                                @if($c->phone)
                                <span class="flex items-center gap-1 font-mono text-[11px]">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">call</span>
                                    <span>{{ $c->phone }}</span>
                                </span>
                                @endif

                                @if($c->contact_person)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">badge</span>
                                    <span>Rep: {{ $c->contact_person }}</span>
                                </span>
                                @endif

                                @if(! $isClient)
                                <span class="font-mono font-semibold text-[#23493a]">
                                    Trust Balance: {{ config('legal.currency.symbol', '₹') }}{{ number_format($c->trust_balance, 2) }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if(! $isClient)
                            <a href="{{ route('clients.show', $c->id) }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>Client Profile</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                            @else
                            <a href="{{ route('portal.settings') }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>My Account</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 3. DOCUMENTS SECTION -->
            @if(($type === 'all' || $type === 'documents') && $documents->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">description</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Vault Documents &amp; Evidence</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $documentsCount }} {{ Str::plural('Match', $documentsCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($documents as $doc)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="material-symbols-outlined text-rose-600 text-2xl mt-0.5 shrink-0">
                                @if(Str::endsWith(strtolower($doc->filename), '.pdf')) picture_as_pdf
                                @elseif(Str::endsWith(strtolower($doc->filename), ['.doc', '.docx'])) description
                                @else draft
                                @endif
                            </span>

                            <div class="flex flex-col min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-[#1a1a1a]">
                                        {{ $doc->title }}
                                    </span>
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $doc->document_type ?: ($doc->category ?: 'Document') }}
                                    </span>
                                    @if($doc->is_client_visible)
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Client Portal Visible
                                    </span>
                                    @else
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                        Internal Chambers Only
                                    </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                    <span class="font-mono text-[11px] text-[#8e8e8e]">{{ $doc->filename }}</span>
                                    
                                    @if($doc->matter)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">folder</span>
                                        <span>Matter: <strong class="text-[#23493a]">{{ $doc->matter->case_number }}</strong> — {{ Str::limit($doc->matter->title, 28) }}</span>
                                    </span>
                                    @endif

                                    @if($doc->client)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">group</span>
                                        <span>{{ $doc->client->name }}</span>
                                    </span>
                                    @endif

                                    @if($doc->formattedSize())
                                    <span class="font-mono text-[11px] text-[#8e8e8e]">Size: {{ $doc->formattedSize() }}</span>
                                    @endif

                                    @if($doc->created_at)
                                    <span class="font-mono text-[11px] text-[#8e8e8e]">Uploaded: {{ $doc->created_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @php
                                $downloadRoute = $isClient 
                                    ? route('portal.documents.download', $doc->id) 
                                    : route('documents.download', $doc->id);
                            @endphp
                            <a href="{{ $downloadRoute }}" 
                               class="px-3 py-1.5 bg-[#23493a] hover:bg-[#1b382d] text-white rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shadow-2xs">
                                <span class="material-symbols-outlined text-[16px]">download</span>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 4. PARTIES SECTION -->
            @if(($type === 'all' || $type === 'parties') && $parties->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">balance</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Parties, Counsel &amp; Litigants</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $partiesCount }} {{ Str::plural('Match', $partiesCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($parties as $p)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-[#1a1a1a]">
                                    {{ $p->party_name }}
                                </span>
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full font-semibold {{ str_contains(strtolower($p->party_role), 'opposing') ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $p->party_role }}
                                </span>
                                @if($p->counsel)
                                <span class="text-xs text-[#646864] font-medium">({{ $p->counsel }})</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                @if($p->matter_number)
                                <span class="font-mono text-xs font-semibold text-[#23493a]">
                                    Docket: {{ $p->matter_number }}
                                </span>
                                @endif

                                @if($p->matter_title)
                                <span>Matter: {{ Str::limit($p->matter_title, 40) }}</span>
                                @endif

                                @if($p->client_name)
                                <span>Represented Entity: <strong>{{ $p->client_name }}</strong></span>
                                @endif

                                @if($p->context)
                                <span class="text-[#8e8e8e]">{{ $p->context }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($p->matter_id)
                            <a href="{{ $isClient ? route('portal.matters.show', $p->matter_id) : route('matters.show', $p->matter_id) }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>View Case Dossier</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 5. MESSAGES SECTION -->
            @if(($type === 'all' || $type === 'messages') && $messages->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">forum</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Counsel Communications &amp; Messages</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $messagesCount }} {{ Str::plural('Match', $messagesCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($messages as $msg)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-[#23493a] bg-[#23493a]/10 px-2 py-0.5 rounded">
                                    {{ $msg->formatted_id }}
                                </span>
                                <span class="text-sm font-semibold text-[#1a1a1a]">
                                    {{ $msg->subject ?: 'Counsel Advisory Communication' }}
                                </span>
                                @if($msg->is_internal)
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                    Internal Chambers Thread
                                </span>
                                @else
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    Client Communication
                                </span>
                                @endif
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full {{ $msg->status === 'read' ? 'bg-zinc-100 text-zinc-600' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $msg->status ?: 'Sent' }}
                                </span>
                            </div>

                            <p class="text-xs text-[#414844] line-clamp-2">
                                {{ Str::limit(strip_tags($msg->body), 180) }}
                            </p>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-[11px] text-[#8e8e8e]">
                                <span>From: <strong>{{ $msg->sender->name ?? 'Chambers Counsel' }}</strong></span>
                                @if($msg->recipient)
                                <span>To: <strong>{{ $msg->recipient->name }}</strong></span>
                                @endif

                                @if($msg->matter)
                                <span class="font-mono text-[#23493a]">Matter: {{ $msg->matter->case_number }}</span>
                                @endif

                                @if($msg->sent_at)
                                <span class="font-mono">Date: {{ $msg->sent_at->format('d M Y, h:i A') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @php
                                $msgRoute = $isClient ? route('portal.messages.index') : route('messages.index');
                            @endphp
                            <a href="{{ $msgRoute }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>Open Communications</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 6. TASKS SECTION -->
            @if(($type === 'all' || $type === 'tasks') && $tasks->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">checklist</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Litigation Tasks &amp; Obligations</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $tasksCount }} {{ Str::plural('Match', $tasksCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($tasks as $t)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-[#23493a] bg-[#23493a]/10 px-2 py-0.5 rounded">
                                    {{ $t->formatted_id }}
                                </span>
                                <span class="text-sm font-semibold text-[#1a1a1a] {{ $t->status === 'completed' ? 'line-through text-[#8e8e8e]' : '' }}">
                                    {{ $t->title }}
                                </span>
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full font-bold {{ in_array($t->priority, ['urgent', 'critical']) ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $t->priority }}
                                </span>
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full {{ $t->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ str_replace('_', ' ', $t->status) }}
                                </span>
                            </div>

                            @if($t->description)
                            <p class="text-xs text-[#414844] line-clamp-1">
                                {{ $t->description }}
                            </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                @if($t->matter)
                                <span class="flex items-center gap-1 font-mono text-[#23493a]">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">folder</span>
                                    <span>Matter: {{ $t->matter->case_number }}</span>
                                </span>
                                @endif

                                @if($t->assignee)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">person</span>
                                    <span>Assigned: {{ $t->assignee->name }}</span>
                                </span>
                                @endif

                                @if($t->related_party_name)
                                <span class="flex items-center gap-1 text-rose-700 font-medium">
                                    <span class="material-symbols-outlined text-[15px]">balance</span>
                                    <span>Party: {{ $t->related_party_name }}</span>
                                </span>
                                @endif

                                @if($t->due_date)
                                <span class="flex items-center gap-1 font-mono text-[11px] {{ $t->due_date->isPast() && $t->status !== 'completed' ? 'text-rose-600 font-bold' : '' }}">
                                    <span class="material-symbols-outlined text-[14px]">alarm</span>
                                    <span>Due: {{ $t->due_date->format('d M Y') }}</span>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if(! $isClient)
                            <a href="{{ route('tasks.index') }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>View Task Board</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 7. CASE NOTES SECTION -->
            @if(($type === 'all' || $type === 'notes') && $notes->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">edit_note</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Case Notes &amp; Legal Strategy</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $notesCount }} {{ Str::plural('Match', $notesCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($notes as $n)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex flex-col min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-[#1a1a1a]">
                                    {{ $n->title }}
                                </span>
                                @if($n->type === 'client_visible')
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Client Portal Visible
                                </span>
                                @else
                                <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                    Internal Chambers Strategy
                                </span>
                                @endif

                                @if($n->is_pinned)
                                <span class="material-symbols-outlined text-amber-500 text-sm" title="Pinned Note">push_pin</span>
                                @endif
                            </div>

                            <p class="text-xs text-[#414844] line-clamp-2">
                                {{ Str::limit(strip_tags($n->body), 180) }}
                            </p>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                @if($n->matter)
                                <span class="font-mono text-[#23493a] font-medium">Matter: {{ $n->matter->case_number }}</span>
                                @endif

                                @if($n->user)
                                <span>Author: {{ $n->user->name }}</span>
                                @endif

                                @if($n->created_at)
                                <span class="font-mono text-[11px] text-[#8e8e8e]">Logged: {{ $n->created_at->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if(! $isClient)
                            <a href="{{ route('notes.index') }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>View Notes</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 8. CALENDAR EVENTS & APPOINTMENTS SECTION -->
            @if(($type === 'all' || $type === 'events') && $calendarItems->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">calendar_month</span>
                        <h3 class="text-xs font-semibold text-[#1a1a1a] uppercase tracking-wider">Calendar Events &amp; Hearings</h3>
                    </div>
                    <span class="font-mono text-xs text-[#646864] font-medium">{{ $eventsCount }} {{ Str::plural('Match', $eventsCount) }}</span>
                </div>

                <div class="divide-y divide-[#e5e3dc]">
                    @foreach($calendarItems as $cal)
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#faf8f5] transition-colors">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] flex flex-col items-center justify-center shrink-0">
                                @if($cal->date)
                                <span class="font-mono text-[9px] uppercase font-bold text-rose-700 leading-none">{{ \Carbon\Carbon::parse($cal->date)->format('M') }}</span>
                                <span class="font-mono text-sm font-bold text-[#1a1a1a] leading-none mt-0.5">{{ \Carbon\Carbon::parse($cal->date)->format('d') }}</span>
                                @else
                                <span class="material-symbols-outlined text-sm text-[#8e8e8e]">event</span>
                                @endif
                            </div>

                            <div class="flex flex-col min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-[#1a1a1a]">
                                        {{ $cal->title }}
                                    </span>
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $cal->category }}
                                    </span>
                                    @if($cal->is_deadline)
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                                        Statutory Deadline
                                    </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#646864]">
                                    @if($cal->matter)
                                    <span class="flex items-center gap-1 font-mono text-[#23493a]">
                                        <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">folder</span>
                                        <span>Matter: {{ $cal->matter->case_number }}</span>
                                    </span>
                                    @endif

                                    @if($cal->location)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] text-[#8e8e8e]">location_on</span>
                                        <span>{{ $cal->location }}</span>
                                    </span>
                                    @endif

                                    @if($cal->date)
                                    <span class="font-mono text-[11px] text-[#8e8e8e]">
                                        Time: {{ \Carbon\Carbon::parse($cal->date)->format('h:i A') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @php
                                $calRoute = $isClient ? route('portal.calendar.index') : route('calendar.index');
                            @endphp
                            <a href="{{ $calRoute }}" 
                               class="px-3 py-1.5 bg-white hover:bg-[#23493a] text-[#23493a] hover:text-white border border-[#23493a]/30 hover:border-[#23493a] rounded-lg text-xs font-medium transition-all flex items-center gap-1 shadow-2xs">
                                <span>Chambers Calendar</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        @endif

    </div>

    <!-- Security & Isolation Attestation Footer Note -->
    <div class="p-4 rounded-xl bg-[#faf8f5] border border-[#e5e3dc] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#646864]">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#23493a] text-lg">verified_user</span>
            <span><strong>Technical Boundary Isolation:</strong> Cryptographic and database scopes guarantee zero data leakage between law firms and client accounts.</span>
        </div>
        <span class="font-mono text-[11px] text-[#8e8e8e]">Chambers Engine v25</span>
    </div>

</div>
@endsection
