@extends('portal.layout')

@section('title', $matter->title)
@section('header_title', 'Case Dossier: ' . $matter->case_number)

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" x-data="{ activeTab: 'overview', newThreadModal: false, activeThreadId: {{ $matter->threads->first()?->id ?? 0 }} }">
    
    <!-- Top Bar Navigation Back & Docket Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.matters.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#23493a] hover:underline font-medium transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Back to All Cases</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="font-mono text-xs font-semibold text-[#23493a] bg-white border border-[#e5e3dc] px-2.5 py-1 rounded-md shadow-xs">
                Docket Ref: {{ $matter->case_number }}
            </span>
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-medium {{ strtolower($matter->status ?? '') === 'closed' ? 'bg-[#f4f2ed] text-[#646864] border border-[#e5e3dc]' : 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' }}">
                {{ ucfirst($matter->status ?? 'Active') }}
            </span>
        </div>
    </div>

    <!-- Case Header Banner Card with 7-Stage Visual Procedural Tracker -->
    <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                        {{ $matter->stage ?? 'Notice & Pleadings' }}
                    </span>
                    <span class="text-xs text-[#646864] font-mono">{{ $matter->practice_area ?? 'Commercial Litigation' }}</span>
                    @if($matter->priority)
                    @php
                        $pColor = match(strtolower($matter->priority)) {
                            'urgent' => 'bg-red-50 text-red-700 border-red-200',
                            'high' => 'bg-amber-50 text-amber-800 border-amber-200',
                            default => 'bg-blue-50 text-blue-700 border-blue-200'
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-medium border {{ $pColor }} uppercase">
                        {{ $matter->priority }} Priority
                    </span>
                    @endif
                </div>
                <h1 class="text-[22px] sm:text-[26px] font-semibold text-[#1a1a1a] tracking-tight leading-snug">{{ $matter->title }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-xs text-[#646864] mt-0.5">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493a]">account_balance</span>
                        <span>{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                    </span>
                    @if($matter->judge_name)
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493a]">person</span>
                        <span>Bench: {{ $matter->judge_name }}</span>
                    </span>
                    @endif
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493a]">calendar_today</span>
                        <span>Filing Date: {{ \Carbon\Carbon::parse($matter->filing_date ?? $matter->opened_at ?? $matter->created_at)->format('d M Y') }}</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button @click="activeTab = 'messages'" class="px-4 py-2 bg-[#23493a] hover:bg-[#1a382b] text-white text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">chat</span>
                    <span>Message Counsel</span>
                </button>
            </div>
        </div>

        <!-- 7-Stage Visual Case Progress Tracker (F-08 Specification) -->
        <div class="pt-5 border-t border-[#f0eee8] flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs uppercase tracking-wider text-[#646864] font-semibold">Procedural Stage Tracker</span>
                <span class="text-xs font-semibold text-[#23493a] bg-[#faf9f5] border border-[#e5e3dc] px-2.5 py-0.5 rounded">
                    Active Stage: {{ $matter->stage ?? 'Notice & Pleadings' }}
                </span>
            </div>

            @php
                $stages = [
                    ['name' => 'Notice & Pleadings', 'desc' => 'Vakalatnama & Written Statements'],
                    ['name' => 'Interim Relief', 'desc' => 'Injunction & Interim Orders'],
                    ['name' => 'Discovery & Evidence', 'desc' => 'Affidavits & Cross-Examination'],
                    ['name' => 'Framing of Issues', 'desc' => 'Settlement of Issues by Bench'],
                    ['name' => 'Oral Arguments', 'desc' => 'Senior Counsel Submissions'],
                    ['name' => 'Final Hearing', 'desc' => 'Authorities & Compilations'],
                    ['name' => 'Judgment & Decree', 'desc' => 'Pronouncement & Compliance'],
                ];

                $sLower = strtolower($matter->stage ?? '');
                $activeIndex = 0;
                if (str_contains($sLower, 'interim') || str_contains($sLower, 'injunction')) $activeIndex = 1;
                elseif (str_contains($sLower, 'evidence') || str_contains($sLower, 'discovery')) $activeIndex = 2;
                elseif (str_contains($sLower, 'issues') || str_contains($sLower, 'framing')) $activeIndex = 3;
                elseif (str_contains($sLower, 'argument') || str_contains($sLower, 'hearing') && !str_contains($sLower, 'final')) $activeIndex = 4;
                elseif (str_contains($sLower, 'final')) $activeIndex = 5;
                elseif (str_contains($sLower, 'judgment') || str_contains($sLower, 'decree') || str_contains($sLower, 'settled') || str_contains($sLower, 'closed')) $activeIndex = 6;
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 pt-1">
                @foreach($stages as $idx => $s)
                <div class="flex flex-col gap-1.5 p-3 rounded-md border transition-all {{ $idx === $activeIndex ? 'bg-[#23493a] text-white border-[#23493a] shadow-xs' : ($idx < $activeIndex ? 'bg-[#faf9f5] text-[#1a1a1a] border-[#e5e3dc]' : 'bg-white text-[#8a8a8a] border-[#e5e3dc]/70') }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10px] font-semibold {{ $idx === $activeIndex ? 'text-[#a7f3d0]' : ($idx < $activeIndex ? 'text-[#23493a]' : 'text-[#8a8a8a]') }}">
                            STAGE 0{{ $idx + 1 }}
                        </span>
                        @if($idx < $activeIndex)
                        <span class="material-symbols-outlined text-[15px] text-[#23493a]">check_circle</span>
                        @elseif($idx === $activeIndex)
                        <span class="material-symbols-outlined text-[15px] text-[#a7f3d0] animate-pulse">radio_button_checked</span>
                        @else
                        <span class="material-symbols-outlined text-[15px] text-[#8a8a8a]">schedule</span>
                        @endif
                    </div>
                    <span class="text-xs font-semibold leading-tight">{{ $s['name'] }}</span>
                    <span class="text-[10px] leading-tight {{ $idx === $activeIndex ? 'text-white/80' : 'text-[#8a8a8a]' }}">{{ $s['desc'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 8-Tab Navigation Container -->
    <div class="flex flex-col gap-0">
        
        <!-- Tab Header Bar -->
        @php
            $docsCount = $matter->documents->count();
            $reqsCount = $matter->documentRequests->count();
            $pendingReqs = $matter->documentRequests->whereIn('status', ['pending', 'rejected'])->count();
            $msgsCount = $matter->messages->count();
            $tasksCount = $matter->tasks->count();
            $pendingTasks = $matter->tasks->where('status', '!=', 'completed')->count();
            $eventsCount = $matter->events->count();
            $notesCount = $notes->count();
            $actsCount = $activities->count();
        @endphp
        <div class="flex items-center border border-[#e5e3dc] bg-white rounded-t-md px-2 sm:px-4 overflow-x-auto shadow-xs select-none">
            
            <!-- Tab 1: Overview -->
            <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">dashboard</span>
                <span>Overview</span>
            </button>

            <!-- Tab 2: Documents -->
            <button @click="activeTab = 'documents'" 
                    :class="activeTab === 'documents' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">folder</span>
                <span>Documents</span>
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full {{ $docsCount > 0 ? 'bg-[#ecfdf5] text-[#065f46]' : 'bg-[#faf9f5] text-[#8a8a8a]' }}">
                    {{ $docsCount }}
                </span>
            </button>

            <!-- Tab 3: Requests -->
            <button @click="activeTab = 'requests'" 
                    :class="activeTab === 'requests' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">drive_folder_upload</span>
                <span>Requests</span>
                @if($pendingReqs > 0)
                <span class="text-[10px] font-mono font-semibold px-1.5 py-0.2 rounded-full bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">
                    {{ $pendingReqs }}
                </span>
                @elseif($reqsCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#faf9f5] text-[#646864]">
                    {{ $reqsCount }}
                </span>
                @endif
            </button>

            <!-- Tab 4: Messages -->
            <button @click="activeTab = 'messages'" 
                    :class="activeTab === 'messages' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">forum</span>
                <span>Messages</span>
                @if($msgsCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#faf9f5] text-[#646864]">
                    {{ $msgsCount }}
                </span>
                @endif
            </button>

            <!-- Tab 5: Tasks -->
            <button @click="activeTab = 'tasks'" 
                    :class="activeTab === 'tasks' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">task_alt</span>
                <span>Tasks</span>
                @if($pendingTasks > 0)
                <span class="text-[10px] font-mono font-semibold px-1.5 py-0.2 rounded-full bg-[#fffbeb] text-[#92400e] border border-[#fef3c7]">
                    {{ $pendingTasks }}
                </span>
                @elseif($tasksCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#faf9f5] text-[#646864]">
                    {{ $tasksCount }}
                </span>
                @endif
            </button>

            <!-- Tab 6: Calendar -->
            <button @click="activeTab = 'calendar'" 
                    :class="activeTab === 'calendar' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">calendar_month</span>
                <span>Calendar</span>
                @if($eventsCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#faf9f5] text-[#646864]">
                    {{ $eventsCount }}
                </span>
                @endif
            </button>

            <!-- Tab 7: Permitted Notes -->
            <button @click="activeTab = 'notes'" 
                    :class="activeTab === 'notes' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">description</span>
                <span>Permitted Notes</span>
                @if($notesCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#ecfdf5] text-[#065f46]">
                    {{ $notesCount }}
                </span>
                @endif
            </button>

            <!-- Tab 8: Safe Activity -->
            <button @click="activeTab = 'activity'" 
                    :class="activeTab === 'activity' ? 'border-b-2 border-[#23493a] text-[#23493a] font-semibold bg-white' : 'text-[#646864] hover:text-[#1a1a1a] border-b-2 border-transparent'"
                    class="px-3.5 py-3 text-xs transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">history</span>
                <span>Activity</span>
                @if($actsCount > 0)
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-[#faf9f5] text-[#646864]">
                    {{ $actsCount }}
                </span>
                @endif
            </button>
        </div>

        <!-- Tab Content Panes -->
        <div class="border-x border-b border-[#e5e3dc] bg-white rounded-b-md p-5 sm:p-6 shadow-xs">
            
            <!-- ========================================== -->
            <!-- TAB 1: OVERVIEW -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'overview'" class="flex flex-col gap-6">
                
                <!-- 2-Column Overview Layout: Case Info + Legal Team -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left Column: Case Metadata & Description (8 cols) -->
                    <div class="lg:col-span-8 flex flex-col gap-6">
                        
                        <!-- Metadata Grid Table Card -->
                        <div class="border border-[#e5e3dc] rounded-md bg-white p-5 flex flex-col gap-4">
                            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#23493a] text-[20px]">docket</span>
                                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Official Docket Specifications</h3>
                                </div>
                                <span class="text-[11px] font-mono text-[#646864]">Verified Court Record</span>
                            </div>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-xs">
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Case Number</dt>
                                    <dd class="font-mono font-semibold text-[#1a1a1a] mt-0.5 text-[13px]">{{ $matter->case_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Docket / Registration No.</dt>
                                    <dd class="font-mono text-[#1a1a1a] mt-0.5">{{ $matter->docket_number ?? 'REG/' . $matter->case_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Court / Forum</dt>
                                    <dd class="font-medium text-[#1a1a1a] mt-0.5">{{ $matter->court_name ?? 'High Court of Delhi' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Court Type</dt>
                                    <dd class="text-[#1a1a1a] mt-0.5">{{ $matter->court_type ?? 'Constitutional High Court' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Jurisdiction</dt>
                                    <dd class="text-[#1a1a1a] mt-0.5">{{ $matter->jurisdiction ?? 'Original Civil Jurisdiction' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Presiding Bench / Judge</dt>
                                    <dd class="font-medium text-[#1a1a1a] mt-0.5">{{ $matter->judge_name ?? "Hon'ble Presiding Judge" }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Territorial Jurisdiction</dt>
                                    <dd class="text-[#1a1a1a] mt-0.5">{{ trim(($matter->county ? $matter->county . ', ' : '') . ($matter->state ?? 'New Delhi'), ', ') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Practice Domain</dt>
                                    <dd class="text-[#1a1a1a] mt-0.5">{{ $matter->practice_area ?? 'Commercial Dispute' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Official Filing Date</dt>
                                    <dd class="font-mono text-[#1a1a1a] mt-0.5">{{ \Carbon\Carbon::parse($matter->filing_date ?? $matter->opened_at ?? $matter->created_at)->format('d M Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Next Hearing / Returnable Date</dt>
                                    <dd class="font-mono font-medium text-[#ba1a1a] mt-0.5">
                                        @if($matter->hearing_date)
                                            {{ \Carbon\Carbon::parse($matter->hearing_date)->format('d M Y') }}
                                        @elseif($matter->events->isNotEmpty())
                                            {{ \Carbon\Carbon::parse($matter->events->first()->start_time)->format('d M Y · h:i A') }}
                                        @else
                                            Cause List Listing Pending
                                        @endif
                                    </dd>
                                </div>
                                @if($matter->trial_date)
                                <div>
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Scheduled Trial Date</dt>
                                    <dd class="font-mono text-[#1a1a1a] mt-0.5">{{ \Carbon\Carbon::parse($matter->trial_date)->format('d M Y') }}</dd>
                                </div>
                                @endif
                                @if($matter->statute_references)
                                <div class="sm:col-span-2">
                                    <dt class="text-[#8a8a8a] text-[11px] font-mono uppercase tracking-wider">Statutory Provisions &amp; Act References</dt>
                                    <dd class="text-[#23493a] font-mono text-[11.5px] mt-0.5 leading-relaxed">{{ $matter->statute_references }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Case Description & Objectives Block -->
                        @if($matter->description)
                        <div class="border border-[#e5e3dc] rounded-md bg-[#faf9f5] p-5 flex flex-col gap-2.5">
                            <div class="flex items-center gap-2 text-[#23493a]">
                                <span class="material-symbols-outlined text-[19px]">article</span>
                                <h3 class="text-xs font-semibold uppercase tracking-wider font-mono">Case Synopsis &amp; Objectives</h3>
                            </div>
                            <div class="text-xs text-[#1a1a1a] leading-relaxed whitespace-pre-line">
                                {{ $matter->description }}
                            </div>
                        </div>
                        @endif

                    </div>

                    <!-- Right Column: Responsible Counsel & Team (4 cols) -->
                    <div class="lg:col-span-4 flex flex-col gap-6">
                        
                        <!-- Lead Attorney Card -->
                        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs flex flex-col gap-4">
                            <div class="flex items-center justify-between pb-2 border-b border-[#f0eee8]">
                                <span class="text-[11px] text-[#8a8a8a] uppercase font-mono tracking-wider font-semibold">Lead Arguing Counsel</span>
                                <span class="text-[10px] font-medium text-[#065f46] bg-[#ecfdf5] px-2 py-0.5 rounded border border-[#a7f3d0]">Principal Advocate</span>
                            </div>

                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-full bg-[#23493a] text-white font-medium flex items-center justify-center text-sm shrink-0 shadow-xs">
                                    {{ strtoupper(substr($matter->leadAttorney->name ?? 'Advocate', 0, 2)) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <h4 class="text-sm font-semibold text-[#1a1a1a] truncate">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</h4>
                                    <span class="text-xs text-[#23493a]">{{ $matter->leadAttorney->title ?? 'Senior Advocate & Practice Counsel' }}</span>
                                    <span class="text-[10.5px] font-mono text-[#8a8a8a] truncate mt-0.5">{{ $matter->leadAttorney->email ?? 'chambers@firmlegal.in' }}</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-[#f0eee8] flex flex-col gap-2">
                                <button @click="activeTab = 'messages'" class="w-full py-2 bg-[#23493a] text-white hover:bg-[#1a382b] rounded-md text-xs font-medium flex items-center justify-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">chat</span>
                                    <span>Send Privileged Message</span>
                                </button>
                            </div>
                        </div>

                        <!-- Assigned Chambers Team -->
                        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs flex flex-col gap-3.5">
                            <div class="flex items-center justify-between pb-2 border-b border-[#f0eee8]">
                                <span class="text-[11px] text-[#8a8a8a] uppercase font-mono tracking-wider font-semibold">Chambers Team Assigned</span>
                                <span class="text-[10px] font-mono text-[#646864]">{{ 1 + ($matter->supervisingAttorney ? 1 : 0) + ($matter->assignedParalegal ? 1 : 0) + $matter->teamMembers->count() }} Members</span>
                            </div>

                            <div class="flex flex-col divide-y divide-[#f0eee8]">
                                @if($matter->supervisingAttorney)
                                <div class="py-2.5 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#faf9f5] border border-[#e5e3dc] text-[#23493a] text-xs font-medium flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($matter->supervisingAttorney->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $matter->supervisingAttorney->name }}</span>
                                        <span class="text-[10.5px] text-[#646864]">Supervising Partner</span>
                                    </div>
                                </div>
                                @endif

                                @if($matter->assignedParalegal)
                                <div class="py-2.5 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#faf9f5] border border-[#e5e3dc] text-[#646864] text-xs font-medium flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($matter->assignedParalegal->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $matter->assignedParalegal->name }}</span>
                                        <span class="text-[10.5px] text-[#646864]">Case Officer &amp; Paralegal</span>
                                    </div>
                                </div>
                                @endif

                                @foreach($matter->teamMembers as $member)
                                <div class="py-2.5 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#faf9f5] border border-[#e5e3dc] text-[#646864] text-xs font-medium flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $member->name }}</span>
                                        <span class="text-[10.5px] text-[#646864]">{{ $member->title ?? 'Associate Counsel' }}</span>
                                    </div>
                                </div>
                                @endforeach

                                @if(!$matter->supervisingAttorney && !$matter->assignedParalegal && $matter->teamMembers->isEmpty())
                                <p class="text-[11.5px] text-[#8a8a8a] py-2">Sole counsel representation under Lead Advocate.</p>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 2: DOCUMENTS -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'documents'" class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#f0eee8] gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">folder_open</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Client-Authorized Document Vault</h3>
                            <p class="text-xs text-[#8a8a8a]">Pleadings, applications, exhibits &amp; authenticated filings</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-[#065f46] bg-[#ecfdf5] px-2.5 py-1 rounded border border-[#a7f3d0] self-start sm:self-auto">
                        Privileged under Section 126 Evidence Act
                    </span>
                </div>

                @if($matter->documents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#f0eee8] text-[#8a8a8a] font-mono text-[10.5px] uppercase tracking-wider">
                                <th class="pb-2.5 font-medium">Document Title</th>
                                <th class="pb-2.5 font-medium">Classification</th>
                                <th class="pb-2.5 font-medium">Category</th>
                                <th class="pb-2.5 font-medium">Filing Date</th>
                                <th class="pb-2.5 font-medium">Size</th>
                                <th class="pb-2.5 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @foreach($matter->documents as $doc)
                            <tr class="hover:bg-[#faf9f5] transition-colors">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="material-symbols-outlined text-lg text-red-600 shrink-0">picture_as_pdf</span>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-[#1a1a1a] truncate">{{ $doc->title }}</span>
                                            <span class="text-[10px] font-mono text-[#8a8a8a] truncate">{{ $doc->filename }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                        {{ ucfirst($doc->classification ?? $doc->privilege ?? 'Confidential') }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-[#646864]">
                                    <span class="px-2 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[#1a1a1a] font-mono text-[10px]">
                                        {{ $doc->category ?? 'Pleading' }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-[#646864] font-mono text-[11px] whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                                </td>
                                <td class="py-3 pr-4 text-[#8a8a8a] font-mono text-[11px] whitespace-nowrap">
                                    {{ $doc->formattedSize() }}
                                </td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('portal.documents.download', $doc->id) }}" class="inline-flex items-center gap-1 text-xs text-[#23493a] hover:underline font-medium">
                                        <span class="material-symbols-outlined text-[15px]">download</span>
                                        <span>Download</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2">folder_off</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No client-visible documents shared yet.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">Once counsel uploads and designates case files for client view, they will appear here.</p>
                </div>
                @endif
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: REQUESTS -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'requests'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-700 text-xl">assignment</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Document Requests for this Case</h3>
                            <p class="text-xs text-[#8a8a8a]">Exhibits, proofs, authorizations and filings requested by counsel</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.requests.index') }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span>All Portal Requests &rarr;</span>
                    </a>
                </div>

                @if($matter->documentRequests->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($matter->documentRequests as $req)
                    @php
                        $isPending = in_array($req->status, ['pending', 'rejected']);
                        $statusBadgeClass = match($req->status) {
                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            default => 'bg-stone-100 text-stone-800 border-stone-200'
                        };
                    @endphp
                    <div class="p-4 rounded-md border {{ $isPending ? 'bg-[#fbf3db]/40 border-[#f0dfaa]' : 'bg-[#faf9f5] border-[#e5e3dc]' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-xs font-semibold text-[#1a1a1a]">{{ $req->title }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium border {{ $statusBadgeClass }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                                @if($req->due_date)
                                <span class="text-[11px] font-mono text-[#ba1a1a] flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[13px]">event</span>
                                    <span>Due: {{ \Carbon\Carbon::parse($req->due_date)->format('d M Y') }}</span>
                                </span>
                                @endif
                            </div>
                            @if($req->description)
                            <p class="text-[11.5px] text-[#646864] mt-0.5 leading-relaxed">{{ $req->description }}</p>
                            @endif
                            @if($req->requestedBy)
                            <span class="text-[10.5px] text-[#8a8a8a] mt-0.5">Requested by: {{ $req->requestedBy->name }}</span>
                            @endif
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            @if($isPending)
                            <a href="{{ route('portal.requests.index') }}" class="px-3.5 py-1.5 bg-[#23493a] hover:bg-[#1a382b] text-white rounded-md text-xs font-medium transition-colors shadow-xs flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">upload</span>
                                <span>Upload Now</span>
                            </a>
                            @else
                            <span class="text-xs text-[#065f46] font-medium flex items-center gap-1 bg-[#ecfdf5] border border-[#a7f3d0] px-2.5 py-1 rounded">
                                <span class="material-symbols-outlined text-sm">done</span>
                                <span>Submitted</span>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#10b981] mb-2">task</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No pending document requests.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">All evidentiary submissions for this docket are currently up to date.</p>
                </div>
                @endif
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: MESSAGES & MATTER THREADS -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'messages'" class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#f0eee8] gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">forum</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Matter-Based Communication Threads</h3>
                            <p class="text-xs text-[#8a8a8a]">Privileged case threads, document requests &amp; client dispatches</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono text-[#065f46] bg-[#ecfdf5] px-2.5 py-1 rounded border border-[#a7f3d0]">
                            Section 126 Protected
                        </span>
                        <button @click="newThreadModal = true" class="px-3 py-1 bg-[#23493a] text-white hover:bg-[#1a382b] text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[15px]">add</span>
                            <span>New Thread</span>
                        </button>
                    </div>
                </div>

                @php
                    $matterThreads = $matter->threads;
                @endphp

                @if($matterThreads->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-12 border border-[#e5e3dc] rounded-md overflow-hidden min-h-[460px]">
                    <!-- Left: Thread List Selector (4 cols) -->
                    <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-[#faf9f5] flex flex-col">
                        <div class="p-3 border-b border-[#e5e3dc] bg-white flex items-center justify-between">
                            <span class="text-[11px] font-mono font-semibold uppercase text-[#646864]">Threads ({{ $matterThreads->count() }})</span>
                            <span class="text-[10px] text-[#8a8a8a] font-mono">Case CNR: {{ $matter->case_number }}</span>
                        </div>

                        <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[460px] flex-1">
                            @foreach($matterThreads as $mThread)
                            @php
                                $mTypeClass = match($mThread->thread_type) {
                                    'client_communication' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'document_request' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    default => 'bg-blue-50 text-blue-800 border-blue-200'
                                };
                            @endphp
                            <button type="button" 
                                    @click="activeThreadId = {{ $mThread->id }}" 
                                    :class="activeThreadId === {{ $mThread->id }} ? 'bg-white border-l-4 border-[#23493a] shadow-xs' : 'hover:bg-white/80'"
                                    class="w-full text-left p-3 transition-colors block cursor-pointer">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $mThread->thread_number ?? 'THR-'.$mThread->id }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium border {{ $mTypeClass }}">
                                        {{ $mThread->type_label }}
                                    </span>
                                </div>
                                <h4 class="text-xs font-semibold text-[#1a1a1a] truncate mt-1">{{ $mThread->subject }}</h4>
                                <div class="flex items-center justify-between text-[10.5px] text-[#8a8a8a] mt-1 font-mono">
                                    <span>{{ $mThread->messages->count() }} messages</span>
                                    <span>{{ $mThread->last_message_at ? $mThread->last_message_at->format('d M') : '' }}</span>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right: Thread Messages & Reply Console (8 cols) -->
                    <div class="lg:col-span-8 flex flex-col justify-between bg-white">
                        @foreach($matterThreads as $mThread)
                        <div x-show="activeThreadId === {{ $mThread->id }}" class="flex flex-col h-full justify-between">
                            <!-- Thread Header -->
                            <div class="p-3.5 border-b border-[#e5e3dc] bg-[#faf9f5] flex items-center justify-between">
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-semibold text-[#23493a] bg-white border border-[#e5e3dc] px-1.5 py-0.5 rounded">
                                            {{ $mThread->thread_number ?? 'THR-'.$mThread->id }}
                                        </span>
                                        <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $mThread->subject }}</span>
                                    </div>
                                    <span class="text-[11px] text-[#8a8a8a] font-mono mt-0.5">Thread Type: {{ $mThread->type_label }} &middot; Status: {{ ucfirst($mThread->status) }}</span>
                                </div>
                            </div>

                            <!-- Messages in this thread -->
                            <div class="p-4 flex-1 overflow-y-auto flex flex-col gap-3.5 max-h-[380px]">
                                @forelse($mThread->messages as $msg)
                                @php
                                    $isSenderClient = ($msg->sender_id === auth()->id());
                                    $statusColor = match($msg->status) {
                                        'read' => 'text-[#065f46]',
                                        'delivered' => 'text-blue-700',
                                        default => 'text-[#8a8a8a]'
                                    };
                                @endphp
                                <div class="flex items-start gap-2.5 {{ $isSenderClient ? 'flex-row-reverse' : '' }}">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white' : 'bg-[#161718] text-white' }}">
                                        {{ strtoupper(substr($msg->sender->name ?? 'User', 0, 2)) }}
                                    </div>

                                    <div class="flex flex-col max-w-[85%] {{ $isSenderClient ? 'items-end' : '' }}">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span class="font-mono text-[9px] text-[#8a8a8a]">{{ $msg->formatted_id }}</span>
                                            <span class="text-xs font-semibold text-[#1a1a1a]">{{ $msg->sender->name ?? 'Counsel' }}</span>
                                            @if($msg->recipient)
                                                <span class="text-[10px] text-[#8a8a8a]">&rarr; {{ $msg->recipient->name }}</span>
                                            @endif
                                            <span class="font-mono text-[10px] text-[#8a8a8a]">{{ ($msg->sent_at ?? $msg->created_at)->format('d M, h:i A') }}</span>
                                        </div>

                                        <div class="p-3 rounded-md text-xs leading-relaxed shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white rounded-tr-none' : 'bg-[#faf9f5] text-[#1a1a1a] rounded-tl-none border border-[#e5e3dc]' }}">
                                            <p class="whitespace-pre-line">{{ $msg->body }}</p>

                                            <!-- Attachments -->
                                            @if($msg->attachments->count() > 0)
                                            <div class="mt-2 pt-2 border-t {{ $isSenderClient ? 'border-[#3a6352]' : 'border-[#e5e3dc]' }} flex flex-col gap-1">
                                                <span class="text-[9.5px] uppercase font-mono tracking-wider {{ $isSenderClient ? 'text-[#a7f3d0]' : 'text-[#8a8a8a]' }}">
                                                    Attachments ({{ $msg->attachments->count() }})
                                                </span>
                                                @foreach($msg->attachments as $att)
                                                <a href="{{ route('portal.messages.attachments.download', $att->id) }}" 
                                                   class="inline-flex items-center justify-between gap-2 p-1.5 rounded text-[10.5px] transition-colors {{ $isSenderClient ? 'bg-[#1a382b] text-white hover:bg-[#152e23]' : 'bg-white border border-[#e5e3dc] text-[#23493a] hover:bg-[#faf9f5]' }}">
                                                    <span class="flex items-center gap-1 truncate">
                                                        <span class="material-symbols-outlined text-[13px]">attach_file</span>
                                                        <span class="truncate">{{ $att->file_name }}</span>
                                                    </span>
                                                    <span class="font-mono text-[9px] opacity-75 shrink-0">{{ $att->formattedSize() }}</span>
                                                </a>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1 mt-0.5 text-[9.5px] font-mono {{ $statusColor }}">
                                            @if($msg->is_read)
                                                <span class="material-symbols-outlined text-[12px]">done_all</span>
                                                <span>Read {{ $msg->read_at ? $msg->read_at->format('d M, h:i A') : '' }}</span>
                                            @else
                                                <span class="material-symbols-outlined text-[12px]">check</span>
                                                <span>{{ ucfirst($msg->status ?? 'Sent') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-12 text-center text-xs text-[#8a8a8a]">No messages in this thread yet.</div>
                                @endforelse
                            </div>

                            <!-- Reply Box -->
                            <div class="p-3 border-t border-[#e5e3dc] bg-[#faf9f5]">
                                <form action="{{ route('portal.messages.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                    @csrf
                                    <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                                    <input type="hidden" name="thread_id" value="{{ $mThread->id }}"/>
                                    <textarea name="body" required rows="2" placeholder="Send reply on this thread to counsel..." class="w-full p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] resize-none shadow-xs"></textarea>

                                    <div class="flex items-center justify-between">
                                        <label class="inline-flex items-center gap-1 text-[11px] text-[#646864] hover:text-[#1a1a1a] cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px] text-[#23493a]">attach_file</span>
                                            <span>Attach files</span>
                                            <input type="file" name="attachments[]" multiple class="hidden"/>
                                        </label>
                                        <button type="submit" class="px-3.5 py-1.5 bg-[#23493a] text-white hover:bg-[#1a382b] rounded-md text-xs font-medium shadow-xs flex items-center gap-1 cursor-pointer">
                                            <span>Reply</span>
                                            <span class="material-symbols-outlined text-xs">send</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <!-- No threads yet -->
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2">chat_bubble_outline</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No communication threads created for this matter yet.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">Start a dedicated client thread below to dispatch inquiries to your lead counsel.</p>
                    <button @click="newThreadModal = true" class="mt-3 px-3.5 py-2 bg-[#23493a] hover:bg-[#1a382b] text-white text-xs font-medium rounded-md shadow-xs">
                        Start First Thread
                    </button>
                </div>
                @endif

                <!-- New Thread Modal inside Case View -->
                <div x-show="newThreadModal" 
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div @click.outside="newThreadModal = false" class="bg-white border border-[#e5e3dc] rounded-lg max-w-lg w-full p-6 shadow-xl">
                        <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#23493a]">add_comment</span>
                                <h3 class="text-sm font-semibold text-[#1a1a1a]">New Communication Thread</h3>
                            </div>
                            <button @click="newThreadModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                                <span class="material-symbols-outlined text-xl">close</span>
                            </button>
                        </div>

                        <form action="{{ route('portal.messages.threads.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                            @csrf
                            <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Thread Classification *</label>
                                <select name="thread_type" required class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                                    <option value="client_communication">Client Communication</option>
                                    <option value="document_request">Document Request</option>
                                    <option value="general_matter_communication">General Matter Communication</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Subject *</label>
                                <input type="text" name="subject" required placeholder="Subject of query or dispatch..." class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Message Body *</label>
                                <textarea name="body" required rows="4" placeholder="Detail your communication to lead counsel..." class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none resize-none"></textarea>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" multiple class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#faf9f5] file:text-[#23493a] file:font-semibold hover:file:bg-[#e5e3dc]"/>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#f0eee8]">
                                <button type="button" @click="newThreadModal = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs hover:bg-[#faf9f5]">
                                    Cancel
                                </button>
                                <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs">
                                    Open Thread
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 5: TASKS -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'tasks'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">task_alt</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Client Action Tasks</h3>
                            <p class="text-xs text-[#8a8a8a]">Signatures, depositions, verifications &amp; procedural actions</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-[#646864]">
                        {{ $matter->tasks->where('status', 'completed')->count() }} of {{ $matter->tasks->count() }} Completed
                    </span>
                </div>

                @if($matter->tasks->count() > 0)
                <div class="flex flex-col divide-y divide-[#f0eee8]">
                    @foreach($matter->tasks as $task)
                    @php
                        $isDone = ($task->status === 'completed');
                        $tPriorityClass = match(strtolower($task->priority ?? 'medium')) {
                            'urgent' => 'bg-red-50 text-red-700 border-red-200',
                            'high' => 'bg-amber-50 text-amber-800 border-amber-200',
                            default => 'bg-blue-50 text-blue-700 border-blue-200'
                        };
                    @endphp
                    <div class="py-3 flex items-start gap-3 transition-colors {{ $isDone ? 'opacity-60' : '' }}">
                        <!-- Task Toggle Form -->
                        <form action="{{ route('portal.tasks.toggle', $task->id) }}" method="POST" class="shrink-0 mt-0.5">
                            @csrf
                            <button type="submit" class="w-5 h-5 rounded border {{ $isDone ? 'bg-[#23493a] border-[#23493a] text-white' : 'border-[#cbd5e1] hover:border-[#23493a] bg-white' }} flex items-center justify-center transition-all cursor-pointer" title="{{ $isDone ? 'Mark Pending' : 'Mark Completed' }}">
                                @if($isDone)
                                    <span class="material-symbols-outlined text-[15px]">check</span>
                                @endif
                            </button>
                        </form>

                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[13.5px] font-medium text-[#1a1a1a] {{ $isDone ? 'line-through text-[#8a8a8a]' : '' }}">
                                    {{ $task->title }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium border {{ $tPriorityClass }} uppercase tracking-wider">
                                    {{ $task->priority ?? 'Normal' }}
                                </span>
                            </div>

                            @if($task->description)
                            <p class="text-[12px] text-[#646864] mt-0.5 leading-relaxed">{{ $task->description }}</p>
                            @endif

                            <div class="flex items-center gap-3 text-[11px] text-[#8a8a8a] mt-1">
                                @if($task->due_date)
                                <span class="flex items-center gap-1 font-mono {{ \Carbon\Carbon::parse($task->due_date)->isPast() && !$isDone ? 'text-red-700 font-semibold' : '' }}">
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

                        <div class="shrink-0 self-center">
                            @if($isDone)
                                <span class="text-[11px] text-[#065f46] font-medium bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded">Completed</span>
                            @else
                                <span class="text-[11px] text-[#92400e] font-medium bg-[#fffbeb] border border-[#fef3c7] px-2 py-0.5 rounded">Action Required</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#10b981] mb-2">task_alt</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No action items required.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">There are no client signatures or reviews pending for this case.</p>
                </div>
                @endif
            </div>

            <!-- ========================================== -->
            <!-- TAB 6: CALENDAR -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'calendar'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">calendar_month</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Case Hearing Schedule &amp; Listings</h3>
                            <p class="text-xs text-[#8a8a8a]">Bench appearances, returnable dates &amp; procedural milestones</p>
                        </div>
                    </div>
                    <a href="{{ route('portal.calendar.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">
                        Full Calendar &rarr;
                    </a>
                </div>

                @if($matter->events->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($matter->events as $evt)
                    @php
                        $isPast = \Carbon\Carbon::parse($evt->start_time)->isPast();
                    @endphp
                    <div class="p-4 rounded-md border {{ $isPast ? 'bg-[#faf9f5] border-[#e5e3dc] opacity-75' : 'bg-white border-[#23493a]/30 shadow-xs' }} flex items-start gap-4">
                        <div class="w-12 h-12 rounded-md {{ $isPast ? 'bg-[#e5e3dc] text-[#646864]' : 'bg-[#23493a] text-white' }} flex flex-col items-center justify-center font-mono shrink-0">
                            <span class="text-base font-bold leading-none">{{ \Carbon\Carbon::parse($evt->start_time)->format('d') }}</span>
                            <span class="text-[9px] uppercase tracking-wider {{ $isPast ? 'text-[#646864]' : 'text-[#a7f3d0]' }} mt-0.5">{{ \Carbon\Carbon::parse($evt->start_time)->format('M') }}</span>
                        </div>

                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <h4 class="text-sm font-semibold text-[#1a1a1a]">{{ $evt->title }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-medium {{ $isPast ? 'bg-stone-100 text-stone-700' : 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' }}">
                                    {{ $evt->event_type ?? 'Court Hearing' }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-[#646864] mt-1">
                                <span class="font-mono text-[#ba1a1a] font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">schedule</span>
                                    <span>{{ \Carbon\Carbon::parse($evt->start_time)->format('h:i A') }}</span>
                                </span>
                                @if($evt->location)
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px] text-[#23493a]">location_on</span>
                                    <span>{{ $evt->location }}</span>
                                </span>
                                @endif
                            </div>

                            @if($evt->notes)
                            <p class="text-[11.5px] text-[#646864] mt-1.5 leading-relaxed">{{ $evt->notes }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2">event_available</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No scheduled hearings currently listed.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">Upcoming cause lists will be synchronized automatically as registry updates are issued.</p>
                </div>
                @endif
            </div>

            <!-- ========================================== -->
            <!-- TAB 7: PERMITTED NOTES -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'notes'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">description</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Permitted Briefing Notes</h3>
                            <p class="text-xs text-[#8a8a8a]">Official case summaries released by your legal counsel</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-[#065f46] bg-[#ecfdf5] px-2.5 py-1 rounded border border-[#a7f3d0]">
                        Client Authorized
                    </span>
                </div>

                <!-- Legal privilege notice banner -->
                <div class="bg-[#faf9f5] border border-[#e5e3dc] rounded-md p-3.5 flex items-start gap-3 text-xs">
                    <span class="material-symbols-outlined text-[#23493a] text-lg shrink-0 mt-0.5">verified_user</span>
                    <div class="flex flex-col">
                        <span class="font-semibold text-[#1a1a1a]">Official Counsel Briefing Notes</span>
                        <p class="text-[#646864] mt-0.5 leading-relaxed">
                            These notes represent summarized observations and updates explicitly authorized by your lead counsel for client disclosure. Internal attorney work product remains protected.
                        </p>
                    </div>
                </div>

                @if($notes->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($notes as $note)
                    <div class="border border-[#e5e3dc] bg-white rounded-md p-4 shadow-xs flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-semibold text-[#1a1a1a]">{{ $note->title }}</h4>
                                @if($note->is_pinned)
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-amber-50 text-amber-800 border border-amber-200">
                                    Pinned
                                </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-mono text-[#8a8a8a]">
                                {{ \Carbon\Carbon::parse($note->created_at)->format('d M Y') }}
                            </span>
                        </div>

                        <div class="text-xs text-[#1a1a1a] leading-relaxed whitespace-pre-line pt-1">
                            {{ $note->body }}
                        </div>

                        <div class="pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[11px] text-[#8a8a8a]">
                            <span>Author: {{ $note->user?->name ?? 'Chambers Counsel' }}</span>
                            <span class="font-mono text-[10.5px]">Client Safe</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2">sticky_note_2</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No client notes published yet.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">When counsel issues client briefing summaries or status notes, they will appear here.</p>
                </div>
                @endif
            </div>

            <!-- ========================================== -->
            <!-- TAB 8: SAFE ACTIVITY -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'activity'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">history</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Procedural Activity Log</h3>
                            <p class="text-xs text-[#8a8a8a]">Client-safe docket actions, filings, and milestone updates</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-[#646864]">
                        {{ $activities->count() }} Entries Logged
                    </span>
                </div>

                @if($activities->count() > 0)
                <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#e5e3dc]">
                    @foreach($activities as $act)
                    @php
                        $actIcon = match($act->activity_type) {
                            'document_uploaded' => 'upload_file',
                            'document_request_created' => 'assignment',
                            'message_sent' => 'chat',
                            'task_created' => 'task_alt',
                            'event_created' => 'event',
                            'status_changed' => 'gavel',
                            default => 'history'
                        };
                    @endphp
                    <div class="relative flex items-start gap-3">
                        <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-[#23493a] border-2 border-white ring-2 ring-[#23493a]/30"></div>
                        <div class="flex flex-col flex-1 bg-[#faf9f5] border border-[#e5e3dc] rounded-md p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px] text-[#23493a]">{{ $actIcon }}</span>
                                    <span>{{ ucfirst(str_replace('_', ' ', $act->activity_type)) }}</span>
                                </span>
                                <span class="text-[10.5px] font-mono text-[#8a8a8a]">
                                    {{ $act->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-xs text-[#646864] mt-1 leading-relaxed">
                                {{ $act->description }}
                            </p>
                            @if($act->user)
                            <span class="text-[10px] text-[#8a8a8a] mt-1">
                                Action by: {{ $act->user->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-12 text-center text-xs text-[#8a8a8a] bg-[#faf9f5] rounded-md border border-[#e5e3dc] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2">timeline</span>
                    <p class="font-medium text-[#1a1a1a] text-sm">No activity records logged yet.</p>
                    <p class="text-[11.5px] text-[#646864] mt-1">Public case actions and filings will appear on this timeline.</p>
                </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
