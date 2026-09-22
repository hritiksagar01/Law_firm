@extends('portal.layout')

@section('title', $matter->title)
@section('header_title', 'Case Dossier: ' . $matter->case_number)

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6">
    
    <!-- Top Bar Navigation Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#23493a] hover:underline font-medium transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Back to All Cases</span>
        </a>
        <span class="font-mono text-xs font-semibold text-[#23493a] bg-[#faf9f5] border border-[#e5e3dc] px-2.5 py-1 rounded-md">
            Docket Ref: {{ $matter->case_number }}
        </span>
    </div>

    <!-- Case Header Banner Card -->
    <div class="bg-white rounded-md border border-[#e5e3dc] p-6 shadow-xs flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                        {{ $matter->stage }}
                    </span>
                    <span class="text-xs text-[#646864] font-mono">{{ $matter->practice_area }}</span>
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
                        <span>Filing Date: {{ \Carbon\Carbon::parse($matter->opened_at ?? $matter->created_at)->format('d M Y') }}</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('portal.messages.index', ['matter_id' => $matter->id]) }}" class="px-4 py-2 bg-[#23493a] hover:bg-[#1a382b] text-white text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">chat</span>
                    <span>Message Counsel</span>
                </a>
            </div>
        </div>

        <!-- 7-Stage Visual Case Progress Tracker (F-08 Specification) -->
        <div class="pt-5 border-t border-[#f0eee8] flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs uppercase tracking-wider text-[#646864] font-semibold">Procedural Stage Tracker</span>
                <span class="text-xs font-semibold text-[#23493a] bg-[#faf9f5] border border-[#e5e3dc] px-2.5 py-0.5 rounded">
                    Active Stage: {{ $matter->stage }}
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

    <!-- 2-Column Dossier Content: Documents & Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Shared Documents & Requests -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Pending Document Requests for this Case -->
            @php
                $matterRequests = $matter->documentRequests;
            @endphp
            @if($matterRequests->count() > 0)
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-700 text-lg">assignment</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Document Requests for this Case</h2>
                    </div>
                    <a href="{{ route('portal.requests.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">All Requests &rarr;</a>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach($matterRequests as $req)
                    <div class="p-3.5 rounded-md border {{ $req->status === 'pending' ? 'bg-[#fbf3db]/50 border-[#f0dfaa]' : 'bg-[#faf9f5] border-[#e5e3dc]' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-[#1a1a1a]">{{ $req->title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($req->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-[#ecfdf5] text-[#065f46]') }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </div>
                            <p class="text-[11.5px] text-[#646864] mt-0.5">{{ $req->description }}</p>
                        </div>

                        @if($req->status === 'pending')
                        <a href="{{ route('portal.requests.index') }}" class="px-3 py-1.5 bg-[#23493a] hover:bg-[#1a382b] text-white rounded-md text-xs font-medium shrink-0 transition-colors shadow-xs">
                            Upload Now
                        </a>
                        @else
                        <span class="text-xs text-[#065f46] font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">done</span>
                            <span>Submitted</span>
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Shared Case Documents Table -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a]">folder_open</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Court Pleadings &amp; Filed Documents</h2>
                    </div>
                    <a href="{{ route('portal.documents.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">Open Vault &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#f0eee8] text-[#8a8a8a] font-mono text-[10.5px] uppercase">
                                <th class="pb-2.5 font-medium">Document Title</th>
                                <th class="pb-2.5 font-medium">Category</th>
                                <th class="pb-2.5 font-medium">Filing Date</th>
                                <th class="pb-2.5 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($matter->documents as $doc)
                            <tr class="hover:bg-[#faf9f5]">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="material-symbols-outlined text-lg text-red-600">picture_as_pdf</span>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-[#1a1a1a]">{{ $doc->title }}</span>
                                            <span class="text-[10px] font-mono text-[#8a8a8a]">{{ $doc->filename }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-[#646864]">
                                    <span class="px-2 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[#1a1a1a] font-mono text-[10px]">
                                        {{ $doc->category }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-[#646864] font-mono text-[11px]">
                                    {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('portal.documents.download', $doc->id) }}" class="inline-flex items-center gap-1 text-xs text-[#23493a] hover:underline font-medium">
                                        <span class="material-symbols-outlined text-[14px]">download</span>
                                        <span>Download</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-[#8a8a8a]">
                                    No shared case documents uploaded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Assigned Advocate Card & Upcoming Hearing -->
        <div class="flex flex-col gap-6">
            
            <!-- Lead Attorney Card -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 shadow-xs flex flex-col gap-3.5">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a] font-medium">Lead Counsel Assigned</span>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#23493a] text-white flex items-center justify-center font-medium text-sm shrink-0 shadow-xs">
                        {{ strtoupper(substr($matter->leadAttorney->name ?? 'Advocate', 0, 2)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-semibold text-[#1a1a1a] truncate">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                        <span class="text-xs text-[#23493a]">{{ $matter->leadAttorney->title ?? 'Senior Advocate' }}</span>
                        <span class="text-[10.5px] font-mono text-[#8a8a8a] truncate mt-0.5">{{ $matter->leadAttorney->email ?? 'chambers@sharmalegal.in' }}</span>
                    </div>
                </div>

                <a href="{{ route('portal.messages.index', ['matter_id' => $matter->id]) }}" class="w-full py-2 bg-[#23493a] text-white hover:bg-[#1a382b] rounded-md text-xs font-medium flex items-center justify-center gap-1.5 transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">chat</span>
                    <span>Direct Message Counsel</span>
                </a>
            </div>

            <!-- Scheduled Court Dates for this Matter -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 shadow-xs flex flex-col gap-3.5">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a] font-medium">Scheduled Court Dates</span>
                <div class="flex flex-col gap-2.5">
                    @forelse($matter->events as $event)
                    <div class="p-3 rounded-md bg-[#faf9f5] border border-[#e5e3dc] flex items-start gap-3">
                        <div class="w-9 h-9 rounded bg-[#23493a] text-white flex flex-col items-center justify-center font-mono text-[10px] font-bold shrink-0">
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                            <span class="text-[8px] uppercase text-[#a7f3d0]">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $event->title }}</span>
                            <span class="text-[11px] text-[#646864] mt-0.5 truncate">{{ $event->location }}</span>
                            <span class="text-[10px] font-mono text-[#ba1a1a] mt-0.5">
                                {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#8a8a8a] py-2 text-center">No hearings listed for this case currently.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
