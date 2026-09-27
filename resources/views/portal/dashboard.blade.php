@extends('portal.layout')

@section('title', 'Client Dashboard')
@section('header_title', 'Litigation Dossier')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]" id="client-dashboard">
    
    <!-- Page Header -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Client Dashboard</h1>
            <p class="text-[13px] text-[#646864] mt-1">Privileged counsel communications, filings &amp; court proceedings &middot; Times in IST</p>
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

    <!-- Top Connected Metric Ribbon (Standard Grid) -->
    @php
        $pendingReqCount = $pendingDocumentRequests->count();
        $pendingTaskCount = $clientTasks->where('status', '!=', 'completed')->count();
        $activeMattersCount = $activeMatters->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-5 border border-[#e5e3dc] bg-white rounded-md divide-y lg:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- 1. Active matters -->
        <a href="#active-matters-section" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12px] text-[#646864]">Active Cases</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ str_pad($activeMattersCount, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5 truncate">High Court &amp; Tribunals</div>
        </a>

        <!-- 2. Recent Documents -->
        <a href="#recent-documents-section" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12px] text-[#646864]">Vault Documents</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ str_pad($recentDocuments->count(), 2, '0', STR_PAD_LEFT) }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5 truncate">{{ $documentsCount }} in vault total</div>
        </a>

        <!-- 3. Pending document requests -->
        <a href="#pending-requests-section" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12px] text-[#646864]">Pending requests</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1 flex items-center gap-1.5">
                <span>{{ str_pad($pendingReqCount, 2, '0', STR_PAD_LEFT) }}</span>
                @if($pendingReqCount > 0)
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">Action</span>
                @endif
            </div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5 truncate">Document uploads needed</div>
        </a>

        <!-- 4. Tasks requiring action -->
        <a href="#client-tasks-section" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12px] text-[#646864]">Client action tasks</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1 flex items-center gap-1.5">
                <span>{{ str_pad($pendingTaskCount, 2, '0', STR_PAD_LEFT) }}</span>
                @if($pendingTaskCount > 0)
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#fffbeb] text-[#92400e] border border-[#fef3c7]">Pending</span>
                @endif
            </div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5 truncate">Signatures &amp; reviews</div>
        </a>

        <!-- 5. Upcoming Hearings / Events -->
        <a href="#upcoming-events-section" class="p-4 sm:p-5 col-span-2 sm:col-span-1 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12px] text-[#646864]">Upcoming events</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1">{{ str_pad($upcomingEvents->count(), 2, '0', STR_PAD_LEFT) }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-0.5 truncate">Hearings &amp; listings</div>
        </a>
    </div>

    <!-- Main 2-Column Grid (Left ~65%, Right ~35%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- SECTION 1: ACTIVE MATTERS -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-5" id="active-matters-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[20px] text-[#23493a]">balance</span>
                        <div>
                            <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Active Matters</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Litigation dockets synchronized with High Court registries</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.matters.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span>View all dockets</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                @forelse($activeMatters as $matter)
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
                                    {{ $matter->stage ?? 'Pre-Trial' }}
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
                                    Scheduled Next Term
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
                                <span class="text-[10px] truncate max-w-full block leading-tight {{ $idx === $currentStageIdx ? 'font-semibold text-[#23493a]' : ($idx < $currentStageIdx ? 'text-[#1a1a1a]' : 'text-[#8a8a8a]') }}" title="{{ $stg['name'] }}">
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
                        <a href="{{ route('portal.matters.show', $matter->id) }}" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3 py-1.5 text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1 cursor-pointer">
                            <span>View Case File</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc]">
                    <span class="material-symbols-outlined text-3xl text-[#8a8a8a] mb-1">gavel</span>
                    <p class="font-medium text-[#1a1a1a]">No active litigation dockets currently linked to your profile.</p>
                    <p class="text-[11px] text-[#646864] mt-0.5">Please contact your assigned chambers attorney for new matter registration.</p>
                </div>
                @endforelse
            </div>

            <!-- SECTION 2: PENDING DOCUMENT REQUESTS -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-4" id="pending-requests-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[20px] text-[#ba1a1a]">assignment_late</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Pending Document Requests</h2>
                                @if($pendingDocumentRequests->count() > 0)
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">
                                        {{ $pendingDocumentRequests->count() }} Action {{ \Illuminate\Support\Str::plural('Item', $pendingDocumentRequests->count()) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Statutory filings, affidavits &amp; exhibits requested by legal counsel</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.requests.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span>All requests</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                @if($pendingDocumentRequests->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($pendingDocumentRequests as $req)
                    <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded font-mono text-[10.5px] font-medium {{ $req->status === 'rejected' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ $req->status === 'rejected' ? 'Correction Required' : 'Awaiting Upload' }}
                                </span>
                                @if($req->matter)
                                    <span class="text-[11.5px] font-mono text-[#23493a] bg-white border border-[#e5e3dc] px-1.5 py-0.5 rounded">
                                        {{ $req->matter->case_number }}
                                    </span>
                                @endif
                                @if($req->due_date)
                                    <span class="text-[11px] text-[#ba1a1a] font-medium flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                        <span>Due: {{ \Carbon\Carbon::parse($req->due_date)->format('d M Y') }}</span>
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-[13.5px] font-semibold text-[#1a1a1a] leading-snug">
                                {{ $req->title }}
                            </h4>
                            @if($req->description)
                            <p class="text-[12px] text-[#646864] line-clamp-2">
                                {{ $req->description }}
                            </p>
                            @endif
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('portal.requests.index') }}" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-3.5 py-1.5 text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                                <span class="material-symbols-outlined text-[15px]">upload_file</span>
                                <span>Upload Now</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-6 px-4 text-center rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-[#10b981] mb-1">check_circle</span>
                    <h4 class="text-[13px] font-semibold text-[#1a1a1a]">All Document Requests Completed</h4>
                    <p class="text-[11.5px] text-[#646864] mt-0.5">No outstanding document or filing uploads requested by your counsel team.</p>
                </div>
                @endif
            </div>

            <!-- SECTION 3: TASKS REQUIRING CLIENT ACTION -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-4" id="client-tasks-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[20px] text-[#23493a]">task_alt</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Tasks Requiring Client Action</h2>
                                @if($pendingTaskCount > 0)
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                        {{ $pendingTaskCount }} Pending
                                    </span>
                                @endif
                            </div>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Vakalatnama executions, depositions, affidavits &amp; statutory reviews</p>
                        </div>
                    </div>
                </div>

                @if($clientTasks->count() > 0)
                <div class="flex flex-col divide-y divide-[#f0eee8]">
                    @foreach($clientTasks as $task)
                    @php
                        $isCompleted = ($task->status === 'completed');
                        $priorityColor = match(strtolower($task->priority ?? 'medium')) {
                            'urgent' => 'bg-red-50 text-red-700 border-red-200',
                            'high' => 'bg-amber-50 text-amber-800 border-amber-200',
                            'medium' => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-stone-50 text-stone-700 border-stone-200'
                        };
                    @endphp
                    <div class="py-3 flex items-start gap-3 transition-colors {{ $isCompleted ? 'opacity-60' : '' }}">
                        <!-- Toggle Form / Checkbox -->
                        <form action="{{ route('portal.tasks.toggle', $task->id) }}" method="POST" class="shrink-0 mt-0.5">
                            @csrf
                            <button type="submit" class="w-5 h-5 rounded border {{ $isCompleted ? 'bg-[#23493a] border-[#23493a] text-white' : 'border-[#cbd5e1] hover:border-[#23493a] bg-white' }} flex items-center justify-center transition-all cursor-pointer" title="{{ $isCompleted ? 'Mark Pending' : 'Mark Completed' }}">
                                @if($isCompleted)
                                    <span class="material-symbols-outlined text-[15px]">check</span>
                                @endif
                            </button>
                        </form>

                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[13.5px] font-medium text-[#1a1a1a] {{ $isCompleted ? 'line-through text-[#8a8a8a]' : '' }}">
                                    {{ $task->title }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium border {{ $priorityColor }} uppercase tracking-wider">
                                    {{ $task->priority ?? 'Normal' }}
                                </span>
                                @if($task->matter)
                                <span class="px-1.5 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[10.5px] font-mono text-[#646864]">
                                    {{ $task->matter->case_number }}
                                </span>
                                @endif
                            </div>

                            @if($task->description)
                            <p class="text-[12px] text-[#646864] mt-0.5 leading-relaxed">
                                {{ $task->description }}
                            </p>
                            @endif

                            <div class="flex items-center gap-3 text-[11px] text-[#8a8a8a] mt-1">
                                @if($task->due_date)
                                <span class="flex items-center gap-1 font-mono {{ \Carbon\Carbon::parse($task->due_date)->isPast() && !$isCompleted ? 'text-red-700 font-semibold' : '' }}">
                                    <span class="material-symbols-outlined text-[13px]">event</span>
                                    <span>Due: {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</span>
                                </span>
                                @endif

                                @if($task->assignee)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">person</span>
                                    <span>Counsel: {{ $task->assignee->name }}</span>
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Status text -->
                        <div class="shrink-0 self-center">
                            @if($isCompleted)
                                <span class="text-[11px] text-[#065f46] font-medium bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded">Completed</span>
                            @else
                                <span class="text-[11px] text-[#92400e] font-medium bg-[#fffbeb] border border-[#fef3c7] px-2 py-0.5 rounded">Action Required</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-6 px-4 text-center rounded-md border border-[#e5e3dc] bg-[#faf9f5] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-[#10b981] mb-1">done_all</span>
                    <h4 class="text-[13px] font-semibold text-[#1a1a1a]">No Client Action Tasks</h4>
                    <p class="text-[11.5px] text-[#646864] mt-0.5">You have no pending tasks or document signatures requiring action at this moment.</p>
                </div>
                @endif
            </div>

            <!-- SECTION 4: RECENT DOCUMENTS -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs flex flex-col gap-4" id="recent-documents-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[20px] text-[#23493a]">folder_open</span>
                        <div>
                            <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Recent Documents &amp; Certified Filings</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Digital counsel authenticated &middot; Privileged under Section 126 Evidence Act</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span>All vault documents</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                @if($recentDocuments->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($recentDocuments as $doc)
                    <div class="border border-[#e5e3dc] bg-[#faf9f5] rounded-md p-4 flex flex-col justify-between hover:border-[#23493a]/40 transition-colors">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-2">
                                <span class="px-2 py-0.5 rounded bg-white border border-[#e5e3dc] font-mono text-[10.5px] text-[#23493a] font-medium truncate max-w-[65%]">
                                    {{ $doc->document_type ?? 'Legal Filing' }}
                                </span>
                                <span class="text-[10.5px] text-[#065f46] font-medium bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0] shrink-0">
                                    Privileged
                                </span>
                            </div>
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] leading-snug">
                                {{ $doc->title }}
                            </h3>
                            @if($doc->matter)
                            <p class="text-[11.5px] text-[#646864] mt-1 truncate">
                                Matter: {{ $doc->matter->case_number }} &middot; {{ $doc->matter->title }}
                            </p>
                            @endif
                        </div>
                        <div class="pt-3 mt-3 border-t border-[#e5e3dc] flex items-center justify-between text-xs">
                            <span class="text-[11px] font-mono text-[#8a8a8a]">
                                {{ $doc->formattedSize() }} &middot; {{ $doc->created_at->format('d M Y') }}
                            </span>
                            <a href="{{ route('portal.documents.download', $doc->id) }}" class="text-[12px] text-[#23493a] font-medium hover:underline flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-[15px]">download</span>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc]">
                    <span class="material-symbols-outlined text-3xl text-[#8a8a8a] mb-1">description</span>
                    <p class="font-medium text-[#1a1a1a]">No recent case documents uploaded yet.</p>
                    <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] font-medium hover:underline mt-1 inline-block">
                        Go to Case Document Vault &rarr;
                    </a>
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- SECTION 5: NOTIFICATIONS & LEGAL ALERTS HUB -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs flex flex-col gap-4" id="notifications-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-[#23493a]">notifications_active</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Notifications</h2>
                    </div>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                        {{ $notifications->count() }} Live
                    </span>
                </div>

                <div class="flex flex-col divide-y divide-[#f0eee8] max-h-[380px] overflow-y-auto pr-0.5">
                    @forelse($notifications as $notif)
                    @php
                        $iconColor = match($notif['color'] ?? 'emerald') {
                            'red' => 'text-red-700 bg-red-50 border-red-200',
                            'amber' => 'text-amber-800 bg-amber-50 border-amber-200',
                            'blue' => 'text-blue-700 bg-blue-50 border-blue-200',
                            default => 'text-[#23493a] bg-[#ecfdf5] border-[#a7f3d0]'
                        };
                    @endphp
                    <div class="py-3 flex items-start gap-2.5 first:pt-0 last:pb-0">
                        <div class="w-7 h-7 rounded-md border {{ $iconColor }} flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[15px]">{{ $notif['icon'] }}</span>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-medium uppercase tracking-wider text-[#646864]">{{ $notif['badge'] }}</span>
                                <span class="text-[10px] text-[#8a8a8a] font-mono">{{ $notif['time'] }}</span>
                            </div>
                            <h4 class="text-[12.5px] font-semibold text-[#1a1a1a] mt-0.5 leading-snug truncate">
                                {{ $notif['title'] }}
                            </h4>
                            <p class="text-[11.5px] text-[#646864] mt-0.5 leading-relaxed line-clamp-2">
                                {{ $notif['description'] }}
                            </p>
                            @if(isset($notif['action_url']))
                            <div class="mt-1.5">
                                <a href="{{ $notif['action_url'] }}" class="text-[11.5px] text-[#23493a] font-medium hover:underline inline-flex items-center gap-0.5">
                                    <span>{{ $notif['action_label'] ?? 'View details' }}</span>
                                    <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No active notifications at this time.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- SECTION 6: UPCOMING EVENTS & COURT DATES -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs flex flex-col gap-4" id="upcoming-events-section">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-[#ba1a1a]">event</span>
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Upcoming Events</h2>
                            <p class="text-[11.5px] text-[#8a8a8a]">Scheduled Court Dates &amp; Hearings</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.calendar.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Calendar &rarr;</a>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($upcomingEvents->take(4) as $event)
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
                            <span class="truncate max-w-[65%]">{{ $event->location ?? 'Courtroom #14, Main Bench' }}</span>
                            <span class="inline-flex items-center gap-1 text-[#065f46] shrink-0 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span> VC Hearing
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc]">
                        <span class="material-symbols-outlined text-2xl text-[#8a8a8a] mb-1">calendar_month</span>
                        <p>No upcoming court hearings or meetings scheduled.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- SECTION 7: RECENT MESSAGES & COUNSEL CHANNEL -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs flex flex-col gap-3" id="counsel-thread">
                <div class="flex items-center justify-between pb-2 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[19px] text-[#23493a]">forum</span>
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent Messages</h2>
                            <p class="text-[11px] text-[#8a8a8a]">Counsel Channel</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-[#065f46] bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Privileged</span>
                </div>

                <!-- Messages stream -->
                <div class="flex flex-col gap-2.5 max-h-56 overflow-y-auto pr-1 text-xs" id="chat-stream">
                    @forelse($recentMessages as $msg)
                    @php
                        $isSelf = (Auth::id() === $msg->sender_id);
                    @endphp
                    <div class="flex flex-col p-2.5 rounded-md max-w-[92%] {{ $isSelf ? 'bg-[#23493a] text-white self-end shadow-xs' : 'bg-[#faf9f5] border border-[#e5e3dc] self-start' }}">
                        <div class="flex items-center gap-1.5 mb-1 {{ $isSelf ? 'justify-between' : '' }}">
                            <span class="font-semibold {{ $isSelf ? 'text-white' : 'text-[#23493a]' }}">
                                {{ $isSelf ? 'You' : ($msg->sender?->name ?? 'Counsel') }}
                            </span>
                            <span class="text-[10px] {{ $isSelf ? 'text-[#a7f3d0]' : 'text-[#8a8a8a]' }} font-mono">
                                {{ $msg->created_at->format('g:i A') }}
                            </span>
                        </div>
                        <p class="{{ $isSelf ? 'text-white' : 'text-[#1a1a1a]' }} leading-relaxed">
                            {{ $msg->body }}
                        </p>
                        @if($msg->matter)
                        <div class="mt-1 pt-1 border-t {{ $isSelf ? 'border-[#33614e]' : 'border-[#e5e3dc]' }} text-[10px] {{ $isSelf ? 'text-[#a7f3d0]' : 'text-[#8a8a8a]' }} truncate">
                            Docket: {{ $msg->matter->case_number }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="py-4 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc]">
                        No recent messages in the counsel channel.
                    </div>
                    @endforelse
                </div>

                <!-- Fast message transmit form -->
                <form action="{{ route('portal.messages.store') }}" method="POST" class="flex flex-col gap-2 mt-1">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matters->first()?->id ?? 1 }}"/>
                    <textarea name="body" required rows="2" placeholder="Send privileged message to Chambers counsel..." class="w-full bg-[#faf9f5] border border-[#e5e3dc] rounded-md p-2.5 text-xs text-[#1a1a1a] placeholder:text-[#8a8a8a] resize-none focus:outline-none focus:border-[#23493a] focus:bg-white transition-all"></textarea>
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

            <!-- Assigned Lead Advocate Profile Card -->
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

            <!-- Privileged Chambers Vault Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-4 sm:p-5 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-[#23493a]/10 text-[#23493a] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">verified_user</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-semibold text-[#1a1a1a]">Privileged Chambers Vault</span>
                        <span class="text-[11.5px] text-[#8a8a8a]">Section 126 &amp; 129 Evidence Act</span>
                    </div>
                </div>
                <a href="{{ route('portal.documents.index') }}" class="text-[12px] text-[#23493a] font-medium hover:underline flex items-center gap-0.5">
                    <span>Open Vault &rarr;</span>
                </a>
            </div>

        </div>

    </div>

    <!-- Footer Note -->
    <div class="mt-8 mb-4 text-center">
        <p class="text-[12px] text-[#8a8a8a] font-sans">
            Client Portal &middot; Secured by Section 126 &amp; 129 Indian Evidence Act privileged infrastructure &middot; High Court registry synchronization active
        </p>
    </div>

</div>
@endsection
