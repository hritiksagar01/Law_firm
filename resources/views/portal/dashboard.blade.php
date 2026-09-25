@extends('portal.layout')

@section('title', 'Litigation Overview')
@section('header_title', 'Litigation Dossier')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">
    
    <!-- Page Header -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Litigation Overview</h1>
            <p class="text-[13px] text-[#646864] mt-1">Privileged counsel communications &amp; court proceedings &middot; Times in IST</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#646864] shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></span>
                <span>Ref: {{ $client->formatted_id ?? 'CL-'.$client->id }}</span>
            </span>
        </div>
    </div>

    <!-- Lead Intake / Attorney Assignment Pending Alert Banner -->
    @if($client->status === 'lead' || ! $client->primary_attorney_id)
    <div class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-6 shadow-xs flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-md bg-amber-500/10 text-amber-800 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl text-amber-700">manage_accounts</span>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-amber-900">Intake Protocol Pending — Lead Attorney Assignment in Progress</h3>
            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                Welcome to our Client Portal. Your representation request has been logged as an incoming client lead.
                Our managing partners are currently reviewing your intake profile to assign the optimal Lead Advocate for your matter. Once assigned, your lead counsel profile and active case dossier will update automatically.
            </p>
        </div>
    </div>
    @endif

    <!-- Urgent Action Alert Banner (Dynamic) -->
    @php
        $pendingActionCount = $documentRequests->whereIn('status', ['pending', 'rejected'])->count();
        $unpaidInvoicesCount = $invoices->where('status', '!=', 'paid')->count();
    @endphp
    @if($pendingActionCount > 0 || $unpaidInvoicesCount > 0)
    <div class="bg-[#fbf3db] border border-[#f0dfaa] rounded-md px-4 py-3 text-[13px] text-[#634812] mb-6 leading-relaxed flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-amber-700 text-lg">notification_important</span>
            <span>
                @if($pendingActionCount > 0)
                    <strong>Action required:</strong> You have {{ $pendingActionCount }} document filing {{ \Illuminate\Support\Str::plural('request', $pendingActionCount) }} awaiting your upload or correction.
                @else
                    <strong>Billing Notice:</strong> You have {{ $unpaidInvoicesCount }} fee {{ \Illuminate\Support\Str::plural('bill', $unpaidInvoicesCount) }} awaiting settlement.
                @endif
            </span>
        </div>
        <a href="{{ $pendingActionCount > 0 ? route('portal.requests.index') : route('portal.invoices.index') }}" class="underline font-medium hover:text-[#382606] ml-2 shrink-0">
            Review items &rarr;
        </a>
    </div>
    @endif

    <!-- Top 4 Connected Metric Ribbon (Super Admin Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- Active matters -->
        <a href="{{ route('portal.matters.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Active matters</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ str_pad($matters->count(), 2, '0', STR_PAD_LEFT) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Listed before High Court &amp; Tribunals</div>
        </a>

        <!-- Vault Documents -->
        <a href="{{ route('portal.documents.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Vault documents</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ str_pad($documentsCount ?? 0, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Certified filings &amp; court orders</div>
        </a>

        <!-- Pending requests -->
        <a href="{{ route('portal.requests.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Action items</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 flex items-center gap-2">
                <span>{{ str_pad($pendingActionCount, 2, '0', STR_PAD_LEFT) }}</span>
                @if($pendingActionCount > 0)
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">Pending</span>
                @endif
            </div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Document requests from counsel</div>
        </a>

        <!-- Retainer trust balance -->
        <a href="{{ route('portal.invoices.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Advance retainer</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ config('legal.currency.symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Available trust ledger balance</div>
        </a>
    </div>

    <!-- Main 2-Column Grid (Left ~66%, Right ~34%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Card 1: Active Cases & 7-Stage Procedural Trajectory -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Active Matters &amp; Hearing Status</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Litigation dockets synchronized with High Court registries</p>
                    </div>
                    <a href="{{ route('portal.matters.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">View all dockets &rarr;</a>
                </div>

                @forelse($matters as $matter)
                @php
                    $stageList = [
                        ['name' => 'Notice & Pleadings', 'desc' => 'Vakalatnama & Written Statements'],
                        ['name' => 'Interim Relief', 'desc' => 'Injunction & Stay Applications'],
                        ['name' => 'Discovery & Evidence', 'desc' => 'Affidavits & Cross-Examination'],
                        ['name' => 'Framing of Issues', 'desc' => 'Settlement of Issues by Bench'],
                        ['name' => 'Oral Arguments', 'desc' => 'Senior Counsel Submissions'],
                        ['name' => 'Final Hearing', 'desc' => 'Case Law Compilations'],
                        ['name' => 'Judgment & Decree', 'desc' => 'Pronouncement & Execution'],
                    ];
                    
                    $sLower = strtolower($matter->stage ?? '');
                    $currentStageIdx = 0;
                    if (str_contains($sLower, 'interim') || str_contains($sLower, 'injunction')) $currentStageIdx = 1;
                    elseif (str_contains($sLower, 'evidence') || str_contains($sLower, 'discovery')) $currentStageIdx = 2;
                    elseif (str_contains($sLower, 'issues') || str_contains($sLower, 'framing')) $currentStageIdx = 3;
                    elseif (str_contains($sLower, 'argument') || str_contains($sLower, 'hearing') && !str_contains($sLower, 'final')) $currentStageIdx = 4;
                    elseif (str_contains($sLower, 'final')) $currentStageIdx = 5;
                    elseif (str_contains($sLower, 'judgment') || str_contains($sLower, 'decree') || str_contains($sLower, 'settled') || str_contains($sLower, 'closed')) $currentStageIdx = 6;
                @endphp

                <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-4 sm:p-5 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded bg-white border border-[#e5e3dc] text-[11px] font-mono text-[#23493a] font-medium">
                                    {{ $matter->case_number }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                    {{ $matter->stage }}
                                </span>
                                <span class="text-[11.5px] text-[#646864]">{{ $matter->practice_area }}</span>
                            </div>
                            <h3 class="text-[15px] font-semibold text-[#1a1a1a] mt-1.5 leading-snug">
                                {{ $matter->title }}
                            </h3>
                            <span class="text-[12px] text-[#646864] mt-0.5">
                                {{ $matter->court_name ?? 'High Court of Delhi' }} &middot; {{ $matter->judge_name ?? "Hon'ble Presiding Bench" }}
                            </span>
                        </div>
                        <div class="flex sm:flex-col sm:items-end shrink-0 gap-1 text-xs">
                            <span class="text-[11px] text-[#8a8a8a] uppercase tracking-wider">Next Date</span>
                            <span class="font-medium text-[#ba1a1a] font-mono">
                                @if($matter->events->isNotEmpty())
                                    {{ \Carbon\Carbon::parse($matter->events->first()->start_time)->format('d M Y · h:i A') }}
                                @else
                                    Scheduled Term Q2
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- 7-Stage Procedural Trajectory -->
                    <div class="bg-white border border-[#e5e3dc] rounded-md p-3.5 flex flex-col gap-2">
                        <div class="flex items-center justify-between text-[11.5px]">
                            <span class="font-medium text-[#1a1a1a] uppercase tracking-wider text-[11px]">Procedural Trajectory (7-Stage Cycle)</span>
                            <span class="text-[#23493a] font-medium">Stage {{ $currentStageIdx + 1 }} of 7: {{ $matter->stage }}</span>
                        </div>
                        
                        <!-- Progress Steps Grid -->
                        <div class="grid grid-cols-7 gap-1 pt-1">
                            @foreach($stageList as $idx => $stg)
                            <div class="flex flex-col items-center text-center gap-1 group relative">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10.5px] font-mono transition-all {{ $idx < $currentStageIdx ? 'bg-[#23493a] text-white' : ($idx === $currentStageIdx ? 'bg-[#23493a] text-white ring-2 ring-[#23493a]/30 font-bold' : 'bg-[#faf9f5] border border-[#e5e3dc] text-[#8a8a8a]') }}">
                                    @if($idx < $currentStageIdx)
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <span class="text-[10px] truncate max-w-full block leading-tight {{ $idx === $currentStageIdx ? 'font-semibold text-[#23493a]' : ($idx < $currentStageIdx ? 'text-[#1a1a1a]' : 'text-[#8a8a8a]') }}">
                                    {{ explode(' ', $stg['name'])[0] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-[#e5e3dc]/70 text-xs">
                        <div class="flex items-center gap-1.5 text-[#646864]">
                            <span class="material-symbols-outlined text-base text-[#23493a]">person</span>
                            <span>Lead Arguing Counsel: <strong class="text-[#1a1a1a] font-medium">{{ $matter->leadAttorney?->name ?? 'Adv. A. Sharma' }}</strong></span>
                        </div>
                        <a href="{{ route('portal.matters.show', $matter->id) }}" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3 py-1.5 text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1">
                            <span>View Case File</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-10 text-center text-xs text-[#8a8a8a]">
                    No active litigation dockets currently linked to your enterprise client account.
                </div>
                @endforelse
            </div>

            <!-- Card 2: Legal Opinions & Advisory Memos -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent Case Opinions &amp; Advisory Memos</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Digital counsel signature authenticated &middot; Privileged under Section 126 Evidence Act</p>
                    </div>
                    <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">All opinions &rarr;</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Memo 1 -->
                    <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded bg-white border border-[#e5e3dc] font-mono text-[10.5px] text-[#646864]">Memo #OP-2026-089</span>
                                <span class="text-[11px] text-[#065f46] font-medium bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Privileged</span>
                            </div>
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] leading-snug">
                                Precedent Analysis: Carriage of Goods &amp; Statutory Demurrage Waivers
                            </h3>
                            <p class="text-[12px] text-[#646864] mt-1.5 leading-relaxed">
                                Detailed counsel advisory on section 43 with case law synthesis of the recent division bench appellate ruling.
                            </p>
                        </div>
                        <div class="pt-3 mt-3 border-t border-[#e5e3dc] flex items-center justify-between">
                            <span class="text-[11px] font-mono text-[#8a8a8a]">4.2 MB PDF</span>
                            <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] font-medium hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">download</span>
                                <span>Download Memo</span>
                            </a>
                        </div>
                    </div>

                    <!-- Memo 2 -->
                    <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded bg-white border border-[#e5e3dc] font-mono text-[10.5px] text-[#646864]">Memo #OP-2026-074</span>
                                <span class="text-[11px] text-[#065f46] font-medium bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Privileged</span>
                            </div>
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] leading-snug">
                                Risk Matrix: Proposed Joint Venture Agreement &amp; Indemnity Caps
                            </h3>
                            <p class="text-[12px] text-[#646864] mt-1.5 leading-relaxed">
                                Counsel review of dispute resolution clauses, seat designation risks, and indemnification caps with partner commentary.
                            </p>
                        </div>
                        <div class="pt-3 mt-3 border-t border-[#e5e3dc] flex items-center justify-between">
                            <span class="text-[11px] font-mono text-[#8a8a8a]">2.8 MB PDF</span>
                            <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] font-medium hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">download</span>
                                <span>Download Memo</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Card 0: Assigned Lead Advocate Profile Card -->
            @if($client->primaryAttorney)
            <div class="border border-[#e5e3dc] bg-white rounded-md p-4 sm:p-5 shadow-xs flex flex-col gap-3">
                <div class="flex items-center justify-between pb-2 border-b border-[#f0eee8]">
                    <span class="text-[11px] text-[#8a8a8a] uppercase font-mono tracking-wider font-semibold">Assigned Lead Advocate</span>
                    <span class="text-[11px] font-medium text-[#065f46] bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Primary Counsel</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#23493a] text-white font-semibold flex items-center justify-center text-sm shrink-0">
                        {{ strtoupper(substr($client->primaryAttorney->name, 0, 2)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h4 class="text-sm font-semibold text-[#1a1a1a] truncate">{{ $client->primaryAttorney->name }}</h4>
                        <span class="text-[11.5px] text-[#646864] truncate">{{ $client->primaryAttorney->title ?? 'Senior Advocate & Practice Counsel' }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-[#f0eee8] flex items-center justify-between text-xs">
                    <span class="text-[#646864] font-mono text-[11px] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">call</span>
                        <span>{{ $client->primaryAttorney->phone ?? 'Chambers Line' }}</span>
                    </span>
                    <a href="#counsel-thread" class="text-[#23493a] font-medium hover:underline flex items-center gap-1">
                        <span>Message Counsel</span>
                        <span class="material-symbols-outlined text-[13px]">arrow_downward</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- Card 1: Upcoming Hearings & Cause List Appearances -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Scheduled Court Dates</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">High Court cause list sync</p>
                    </div>
                    <a href="{{ route('portal.calendar.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Calendar &rarr;</a>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($events->take(3) as $event)
                    <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-3.5 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11.5px] font-semibold text-[#ba1a1a] font-mono tabular-nums">
                                {{ \Carbon\Carbon::parse($event->start_time)->format('D, d M · g:i A') }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-white border border-[#e5e3dc] text-[10px] font-mono text-[#646864]">
                                {{ $event->event_type ?? 'Court Hearing' }}
                            </span>
                        </div>
                        <span class="text-[13px] font-semibold text-[#1a1a1a] leading-snug">{{ $event->title }}</span>
                        @if($event->matter)
                        <span class="text-[11.5px] text-[#646864] truncate">Case: {{ $event->matter->case_number }} &middot; {{ $event->matter->title }}</span>
                        @endif
                        <div class="flex items-center justify-between mt-1 pt-1.5 border-t border-[#e5e3dc] text-[11px] text-[#646864]">
                            <span>{{ $event->location ?? 'Courtroom #14, Main Bench' }}</span>
                            <span class="inline-flex items-center gap-1 text-[#065f46]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span> VC Link
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No upcoming trial hearings listed for this week.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card 2: Direct Counsel Channel Box -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-3" id="counsel-thread">
                <div class="flex items-center justify-between pb-2 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[19px] text-[#23493a]">forum</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Counsel Channel</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#065f46] bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Encrypted</span>
                </div>

                <div class="flex flex-col gap-2.5 max-h-60 overflow-y-auto pr-1 text-xs" id="chat-stream">
                    <div class="flex flex-col bg-[#faf9f5] border border-[#e5e3dc] p-2.5 rounded-md self-start max-w-[90%]">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="font-semibold text-[#23493a]">Adv. Priya Mathur</span>
                            <span class="text-[10px] text-[#8a8a8a] font-mono">10:14 AM</span>
                        </div>
                        <p class="text-[#1a1a1a] leading-relaxed">
                            We have compiled the supplemental compilation for tomorrow’s High Court cause list. Please verify the annexed schedule.
                        </p>
                    </div>

                    <div class="flex flex-col bg-[#23493a] text-white p-2.5 rounded-md self-end max-w-[90%] shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-medium text-white">{{ Auth::user()?->name ?? 'Client' }}</span>
                            <span class="text-[10px] text-[#a7f3d0] font-mono">11:02 AM</span>
                        </div>
                        <p class="text-white leading-relaxed">
                            Accounts has cross-verified the figures. Signed authorization token uploaded to case folder.
                        </p>
                    </div>
                </div>

                <form action="{{ route('portal.messages.store') }}" method="POST" class="flex flex-col gap-2 mt-1">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matters->first()?->id ?? 1 }}"/>
                    <textarea name="body" required rows="2" placeholder="Send privileged message to Chambers associates..." class="w-full bg-[#faf9f5] border border-[#e5e3dc] rounded-md p-2.5 text-xs text-[#1a1a1a] placeholder:text-[#8a8a8a] resize-none focus:outline-none focus:border-[#23493a] focus:bg-white transition-all"></textarea>
                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ route('portal.messages.index') }}" class="text-[11.5px] text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                            <span>Full Chat</span>
                        </a>
                        <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-3.5 py-1.5 text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1 cursor-pointer">
                            <span>Transmit</span>
                            <span class="material-symbols-outlined text-[14px]">send</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 3: Enterprise Retainer Statement -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-4 sm:p-5 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-[#23493a]/10 text-[#23493a] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-semibold text-[#1a1a1a]">Retainer Account Ledger</span>
                        <span class="text-[11.5px] text-[#8a8a8a]">Q2 Statement &middot; Balance Verified</span>
                    </div>
                </div>
                <a href="{{ route('portal.invoices.index') }}" class="text-[12px] text-[#23493a] font-medium hover:underline flex items-center gap-0.5">
                    <span>Statements &rarr;</span>
                </a>
            </div>

        </div>

    </div>

    <!-- Footer Note -->
    <div class="mt-8 mb-4 text-center">
        <p class="text-[12px] text-[#8a8a8a] font-sans">
            Client Portal &middot; Secured by Section 126 &amp; 129 Indian Evidence Act privileged infrastructure &middot; Docket synchronization active
        </p>
    </div>

</div>
@endsection
