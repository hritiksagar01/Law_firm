@extends('portal.layout')

@section('title', 'Client Overview')

@section('content')
<div class="flex flex-col gap-8">
    
    <!-- Welcome Banner & Chambers Heading -->
    <div class="bg-gradient-to-r from-[#9F8349] to-[#856C36] rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium bg-[#B88B56] text-[#9F8349]">Active Client Account</span>
                    <span class="text-xs text-white/70">{{ $client->name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold tracking-tight">Namaste, {{ auth()->user()->name }}</h1>
                <p class="text-xs sm:text-sm text-white/80 max-w-xl">
                    Welcome to your private client portal. Review case stage updates, fulfill document requests, and track upcoming court proceedings.
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Matters -->
        <div class="bg-white rounded-xl p-5 border border-[#EFECE6] shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#766A5E]">Active Cases</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1">{{ $matters->count() }}</span>
                <a href="{{ route('portal.matters.index') }}" class="text-[11px] text-[#9F8349] hover:underline font-medium mt-1">View case files &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#F4ECE1]/50 text-[#9F8349] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">gavel</span>
            </div>
        </div>

        <!-- Pending Document Requests -->
        @php
            $pendingCount = $documentRequests->where('status', 'pending')->count();
        @endphp
        <div class="bg-white rounded-xl p-5 border border-[#EFECE6] shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#766A5E]">Document Requests</span>
                <span class="text-2xl font-serif font-bold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-[#222222]' }} mt-1">{{ $pendingCount }} Pending</span>
                <a href="{{ route('portal.requests.index') }}" class="text-[11px] text-[#9F8349] hover:underline font-medium mt-1">Fulfill requests &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">drive_folder_upload</span>
            </div>
        </div>

        <!-- Vault Documents -->
        <div class="bg-white rounded-xl p-5 border border-[#EFECE6] shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#766A5E]">Vault Documents</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1">{{ $documentsCount ?? $matters->flatMap->documents->count() }} Files</span>
                <a href="{{ route('portal.documents.index') }}" class="text-[11px] text-[#9F8349] hover:underline font-medium mt-1">Access vault &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#F8F4EE] text-[#9F8349] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">folder_shared</span>
            </div>
        </div>

        <!-- Next Hearing -->
        @php
            $nextHearing = $events->where('start_time', '>=', now())->sortBy('start_time')->first();
        @endphp
        <div class="bg-white rounded-xl p-5 border border-[#EFECE6] shadow-sm flex items-center justify-between">
            <div class="flex flex-col min-w-0">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#766A5E]">Next Court Date</span>
                <span class="text-base font-semibold text-[#222222] truncate mt-1">
                    {{ $nextHearing ? \Carbon\Carbon::parse($nextHearing->start_time)->format('d M, h:i A') : 'No upcoming date' }}
                </span>
                <span class="text-[11px] text-[#766A5E] truncate">{{ $nextHearing->location ?? 'Court hearing schedule' }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">calendar_month</span>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Section: Cases & Counsel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Active Cases Dossiers -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-serif font-bold text-[#222222]">Your Legal Matters</h2>
                <a href="{{ route('portal.matters.index') }}" class="text-xs text-[#9F8349] hover:underline font-semibold flex items-center gap-1">
                    <span>View All Cases ({{ $matters->count() }})</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="flex flex-col gap-4">
                @forelse($matters as $matter)
                <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm hover:shadow-md transition-all flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#FAF8F5] pb-3">
                        <div class="flex flex-col">
                            <span class="font-mono text-xs font-bold text-[#9F8349]">{{ $matter->case_number }}</span>
                            <h3 class="text-base font-semibold text-[#222222] mt-0.5">{{ $matter->title }}</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium self-start sm:self-auto bg-[#F8F4EE] text-[#856C36] border border-[#E8DAC8]">
                            {{ $matter->stage }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs text-[#766A5E]">
                        <div>
                            <span class="block text-[10px] uppercase font-mono tracking-wider">Judicial Venue</span>
                            <span class="font-medium text-[#222222] mt-0.5 block truncate">{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-mono tracking-wider">Lead Counsel</span>
                            <span class="font-medium text-[#222222] mt-0.5 block">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-mono tracking-wider">Practice Discipline</span>
                            <span class="font-medium text-[#222222] mt-0.5 block truncate">{{ $matter->practice_area }}</span>
                        </div>
                    </div>

                    <!-- Mini Visual Stage Tracker Preview -->
                    <div class="bg-[#FAF8F5] rounded-lg p-3 border border-[#f0eee9]">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-[#766A5E] block mb-2 font-semibold">Procedural Stage Progress</span>
                        <div class="flex items-center justify-between text-[11px]">
                            @php
                                $stages = ['Notice & Pleadings', 'Interim Relief', 'Evidence & Arguments', 'Final Hearing', 'Order'];
                                $currentStage = $matter->stage;
                            @endphp
                            @foreach($stages as $index => $stage)
                            <div class="flex items-center gap-1.5 {{ str_contains(strtolower($currentStage), strtolower(explode(' ', $stage)[0])) ? 'font-bold text-[#9F8349]' : 'text-[#766A5E]' }}">
                                <span class="w-2 h-2 rounded-full {{ str_contains(strtolower($currentStage), strtolower(explode(' ', $stage)[0])) ? 'bg-[#9F8349] ring-2 ring-[#B88B56]' : 'bg-[#EAE4DC]' }}"></span>
                                <span class="hidden sm:inline">{{ $stage }}</span>
                            </div>
                            @if(!$loop->last)
                            <span class="text-[#EAE4DC]">&rarr;</span>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs text-[#766A5E]">
                            {{ $matter->documents->count() }} Shared Documents &middot; {{ $matter->documentRequests->count() }} Requests
                        </span>
                        <a href="{{ route('portal.matters.show', $matter->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-colors">
                            <span>Open Case Dossier</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-[#EFECE6] p-8 text-center text-[#766A5E]">
                    <span class="material-symbols-outlined text-3xl mb-2 text-[#EAE4DC]">folder_off</span>
                    <p class="text-xs">No active court matters found under your client account.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right 1 Col: Priority Actions, Legal Counsel & Hearings -->
        <div class="flex flex-col gap-6">
            
            <!-- Priority Action Items (Counsel Document Requests) -->
            <div class="bg-white rounded-xl border {{ $pendingCount > 0 ? 'border-amber-300 shadow-sm' : 'border-[#EFECE6]' }} overflow-hidden">
                <div class="p-4 border-b {{ $pendingCount > 0 ? 'border-amber-100 bg-amber-50/60' : 'border-[#F4EFEA]' }} flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined {{ $pendingCount > 0 ? 'text-amber-700' : 'text-[#9F8349]' }} text-xl">
                            {{ $pendingCount > 0 ? 'priority_high' : 'checklist' }}
                        </span>
                        <h2 class="text-base font-semibold text-[#222222]">Priority Action Items</h2>
                    </div>
                    @if($pendingCount > 0)
                    <span class="font-mono text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 font-bold">
                        {{ $pendingCount }} Pending
                    </span>
                    @else
                    <span class="font-mono text-xs text-[#9F8349] font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span> Up to date
                    </span>
                    @endif
                </div>

                <div class="p-4 flex flex-col gap-3">
                    @if($pendingCount > 0)
                        <p class="text-xs text-[#554D45]">
                            Counsel requested the following documents to finalize upcoming court filings:
                        </p>
                        <div class="flex flex-col gap-2.5">
                            @foreach($documentRequests->where('status', 'pending') as $req)
                            <div class="p-3 rounded-lg bg-[#FAF8F5] border border-amber-200 hover:border-amber-300 transition-colors flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-[#222222] truncate">{{ $req->title }}</span>
                                    @if($req->due_date)
                                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold shrink-0">
                                        Due {{ \Carbon\Carbon::parse($req->due_date)->format('d M') }}
                                    </span>
                                    @endif
                                </div>
                                @if($req->description)
                                <p class="text-[11px] text-[#766A5E] line-clamp-2 leading-relaxed">{{ $req->description }}</p>
                                @endif
                                @if($req->matter)
                                <div class="flex items-center gap-1 text-[10px] text-[#9F8349] font-mono font-medium mt-0.5">
                                    <span class="material-symbols-outlined text-xs">folder</span>
                                    <span>{{ $req->matter->case_number }} · {{ $req->matter->title }}</span>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <a href="{{ route('portal.requests.index') }}" class="w-full py-2.5 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors mt-1 shadow-sm">
                            <span class="material-symbols-outlined text-base">upload_file</span>
                            <span>Upload Requested Documents</span>
                        </a>
                    @else
                        <div class="text-center py-4 text-[#766A5E] flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-[#9F8349] text-2xl">task_alt</span>
                            <span class="text-xs font-medium text-[#222222]">All Document Requests Complete</span>
                            <span class="text-[11px]">You have fulfilled all pending items requested by counsel.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lead Counsel Chambers Card -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm flex flex-col gap-4">
                <span class="text-xs font-mono uppercase tracking-wider text-[#766A5E] font-semibold">Your Legal Representation</span>
                
                <div class="flex items-center gap-3">
                    <img alt="Adv. Rajesh Sharma" class="w-12 h-12 rounded-xl object-cover ring-2 ring-[#F4ECE1]" src="https://images.unsplash.com/photo-1556157382-97eda2d62296?w=150&auto=format&fit=crop&q=80"/>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-[#222222]">Adv. Rajesh Sharma</span>
                        <span class="text-xs text-[#9F8349] font-medium">Senior Advocate &amp; Managing Partner</span>
                        <span class="text-[10px] font-mono text-[#766A5E] mt-0.5">Bar Council Enr: D/1420/2005</span>
                    </div>
                </div>

                <div class="text-xs text-[#554D45] flex flex-col gap-2 pt-2 border-t border-[#FAF8F5]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#9F8349]">location_on</span>
                        <span>Chambers: 412, Lawyers Chambers Block, Delhi High Court</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#9F8349]">mail</span>
                        <span>rajesh@sharmalegal.in</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#9F8349]">call</span>
                        <span>+91 98110 23411</span>
                    </div>
                </div>

                <a href="{{ route('portal.messages.index') }}" class="w-full py-2 bg-[#FAF8F5] hover:bg-[#EFECE6] text-[#222222] rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors mt-1">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">chat</span>
                    <span>Send Message to Counsel</span>
                </a>
            </div>

            <!-- Upcoming Court Hearings & Meetings -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono uppercase tracking-wider text-[#766A5E] font-semibold">Upcoming Court Dates</span>
                    <a href="{{ route('portal.calendar.index') }}" class="text-[11px] text-[#9F8349] hover:underline font-medium">View calendar &rarr;</a>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($events->take(3) as $event)
                    <div class="p-3 rounded-lg bg-[#FAF8F5] border border-[#f0eee9] flex items-start gap-3">
                        <div class="w-8 h-8 rounded bg-[#9F8349] text-[#B88B56] flex flex-col items-center justify-center font-mono text-[10px] font-bold shrink-0">
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                            <span class="text-[8px] uppercase text-white">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#222222] truncate">{{ $event->title }}</span>
                            <span class="text-[11px] text-[#766A5E] mt-0.5 truncate">{{ $event->location ?? 'Court Hearing' }}</span>
                            <span class="text-[10px] font-mono text-[#856C36] mt-0.5">
                                {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#766A5E] text-center py-4">No upcoming court hearings scheduled.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
