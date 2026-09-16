@extends('portal.layout')

@section('title', $matter->title)

@section('content')
<div class="flex flex-col gap-8">
    
    <!-- Top Bar Navigation Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#845D33] font-medium transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Back to All Cases</span>
        </a>
        <span class="font-mono text-xs font-bold text-[#845D33] bg-[#F4ECE1]/40 px-2.5 py-1 rounded">
            Docket Ref: {{ $matter->case_number }}
        </span>
    </div>

    <!-- Case Header Banner -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] p-6 sm:p-8 shadow-sm flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F8F4EE] text-[#6D4B27] border border-[#E8DAC8]">
                        {{ $matter->stage }}
                    </span>
                    <span class="text-xs text-[#766A5E] font-mono">{{ $matter->practice_area }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#222222]">{{ $matter->title }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-xs text-[#766A5E] mt-1">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">account_balance</span>
                        <span>{{ $matter->court_name ?? 'High Court of Delhi' }}</span>
                    </span>
                    @if($matter->judge_name)
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">person</span>
                        <span>Bench: {{ $matter->judge_name }}</span>
                    </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">calendar_today</span>
                        <span>Filing Date: {{ \Carbon\Carbon::parse($matter->opened_at)->format('d M Y') }}</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('portal.messages.index', ['matter_id' => $matter->id]) }}" class="px-4 py-2 bg-[#845D33] hover:bg-[#6D4B27] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">chat</span>
                    <span>Message Counsel</span>
                </a>
            </div>
        </div>

        <!-- 5-Stage Visual Case Progress Tracker (F-08 Specification) -->
        <div class="pt-6 border-t border-[#FAF8F5] flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="font-mono text-xs uppercase tracking-wider text-[#766A5E] font-semibold">Procedural Stage Tracker</span>
                <span class="text-xs font-bold text-[#845D33] bg-[#F4ECE1]/50 px-2 py-0.5 rounded">Active Stage: {{ $matter->stage }}</span>
            </div>

            @php
                $stages = [
                    ['name' => 'Notice & Pleadings', 'desc' => 'Vakalatnama & Written Statements'],
                    ['name' => 'Interim Relief', 'desc' => 'Injunction & Interim Orders'],
                    ['name' => 'Evidence & Arguments', 'desc' => 'Affidavits & Cross-Examination'],
                    ['name' => 'Final Hearing', 'desc' => 'Oral Submissions & Case Authorities'],
                    ['name' => 'Judgment & Decree', 'desc' => 'Final Pronouncement & Compliance'],
                ];

                // Determine active index
                $stageLower = strtolower($matter->stage);
                $activeIndex = 0;
                if (str_contains($stageLower, 'notice') || str_contains($stageLower, 'pleading')) {
                    $activeIndex = 0;
                } elseif (str_contains($stageLower, 'interim') || str_contains($stageLower, 'discovery')) {
                    $activeIndex = 1;
                } elseif (str_contains($stageLower, 'evidence') || str_contains($stageLower, 'argument')) {
                    $activeIndex = 2;
                } elseif (str_contains($stageLower, 'final') || str_contains($stageLower, 'hearing')) {
                    $activeIndex = 3;
                } elseif (str_contains($stageLower, 'judgment') || str_contains($stageLower, 'decree') || str_contains($stageLower, 'settled')) {
                    $activeIndex = 4;
                }
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 pt-2">
                @foreach($stages as $idx => $s)
                <div class="flex flex-col gap-1.5 p-3 rounded-xl border {{ $idx === $activeIndex ? 'bg-[#845D33] text-white border-[#845D33] shadow-md' : ($idx < $activeIndex ? 'bg-[#F8F4EE] text-[#442E15] border-[#E8DAC8]' : 'bg-[#FAF8F5] text-[#766A5E] border-[#EFECE6]') }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10px] font-bold {{ $idx === $activeIndex ? 'text-[#B88B56]' : ($idx < $activeIndex ? 'text-[#845D33]' : 'text-[#766A5E]') }}">
                            STAGE 0{{ $idx + 1 }}
                        </span>
                        @if($idx < $activeIndex)
                        <span class="material-symbols-outlined text-sm text-[#845D33]">check_circle</span>
                        @elseif($idx === $activeIndex)
                        <span class="material-symbols-outlined text-sm text-[#B88B56] animate-pulse">radio_button_checked</span>
                        @else
                        <span class="material-symbols-outlined text-sm text-[#EAE4DC]">schedule</span>
                        @endif
                    </div>
                    <span class="text-xs font-bold leading-tight">{{ $s['name'] }}</span>
                    <span class="text-[10px] {{ $idx === $activeIndex ? 'text-white/80' : 'text-[#766A5E]' }} leading-tight">{{ $s['desc'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 2-Column Dossier Content: Documents & Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Shared Documents & Requests -->
        <div class="lg:col-span-2 flex flex-col gap-8">
            
            <!-- Pending Document Requests for this Case -->
            @php
                $matterRequests = $matter->documentRequests;
            @endphp
            @if($matterRequests->count() > 0)
            <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">assignment</span>
                        <h2 class="text-base font-bold text-[#222222]">Document Requests for this Case</h2>
                    </div>
                    <a href="{{ route('portal.requests.index') }}" class="text-xs text-[#845D33] hover:underline font-semibold">View All Requests &rarr;</a>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach($matterRequests as $req)
                    <div class="p-4 rounded-lg border {{ $req->status === 'pending' ? 'bg-amber-50/50 border-amber-200' : 'bg-[#FAF8F5] border-[#EFECE6]' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-[#222222]">{{ $req->title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($req->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-[#F4ECE1] text-[#6D4B27]') }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-[#766A5E] mt-0.5">{{ $req->description }}</p>
                        </div>

                        @if($req->status === 'pending')
                        <a href="{{ route('portal.requests.index') }}" class="px-3 py-1.5 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-semibold shrink-0 transition-colors">
                            Upload Now
                        </a>
                        @else
                        <span class="text-xs text-[#845D33] font-medium flex items-center gap-1">
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
            <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33]">folder_open</span>
                        <h2 class="text-base font-bold text-[#222222]">Court Pleadings &amp; Filed Documents</h2>
                    </div>
                    <a href="{{ route('portal.documents.index') }}" class="text-xs text-[#845D33] hover:underline font-semibold">Open Documents Vault &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#EFECE6] text-[#766A5E] font-mono text-[10px] uppercase">
                                <th class="pb-3 font-semibold">Document Title</th>
                                <th class="pb-3 font-semibold">Category</th>
                                <th class="pb-3 font-semibold">Filing Date</th>
                                <th class="pb-3 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#FAF8F5]">
                            @forelse($matter->documents as $doc)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="material-symbols-outlined text-lg text-red-600">picture_as_pdf</span>
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-[#222222]">{{ $doc->title }}</span>
                                            <span class="text-[10px] font-mono text-[#766A5E]">{{ $doc->filename }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-[#766A5E]">
                                    <span class="px-2 py-0.5 rounded bg-[#FAF8F5] text-[#554D45] font-mono text-[10px]">
                                        {{ $doc->category }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-[#766A5E] font-mono text-[11px]">
                                    {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('documents.download', $doc->id) }}" class="inline-flex items-center gap-1 text-xs text-[#845D33] hover:underline font-medium">
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        <span>Download</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-[#766A5E]">
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
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm flex flex-col gap-4">
                <span class="text-xs font-mono uppercase tracking-wider text-[#766A5E] font-semibold">Lead Counsel Assigned</span>
                <div class="flex items-center gap-3">
                    <img alt="Counsel" class="w-12 h-12 rounded-xl object-cover ring-2 ring-[#F4ECE1]" src="{{ $matter->leadAttorney->avatar_url ?? 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=150&auto=format&fit=crop&q=80' }}"/>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-[#222222]">{{ $matter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                        <span class="text-xs text-[#845D33]">{{ $matter->leadAttorney->title ?? 'Senior Advocate' }}</span>
                        <span class="text-[10px] font-mono text-[#766A5E] mt-0.5">{{ $matter->leadAttorney->email }}</span>
                    </div>
                </div>

                <a href="{{ route('portal.messages.index', ['matter_id' => $matter->id]) }}" class="w-full py-2 bg-[#845D33] text-white hover:bg-[#6D4B27] rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-base">chat</span>
                    <span>Direct Message Counsel</span>
                </a>
            </div>

            <!-- Matter Hearings -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm flex flex-col gap-4">
                <span class="text-xs font-mono uppercase tracking-wider text-[#766A5E] font-semibold">Scheduled Court Dates</span>
                <div class="flex flex-col gap-3">
                    @forelse($matter->events as $event)
                    <div class="p-3 rounded-lg bg-[#FAF8F5] border border-[#f0eee9] flex items-start gap-3">
                        <div class="w-8 h-8 rounded bg-[#845D33] text-[#B88B56] flex flex-col items-center justify-center font-mono text-[10px] font-bold shrink-0">
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                            <span class="text-[8px] uppercase text-white">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#222222] truncate">{{ $event->title }}</span>
                            <span class="text-[11px] text-[#766A5E] mt-0.5 truncate">{{ $event->location }}</span>
                            <span class="text-[10px] font-mono text-[#6D4B27] mt-0.5">
                                {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#766A5E] py-2">No hearings listed for this case currently.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
