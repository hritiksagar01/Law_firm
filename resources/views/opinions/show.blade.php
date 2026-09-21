@extends('layouts.app')

@section('title', $opinion->opinion_number . ' — ' . $opinion->title)

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-rose-600 text-base">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('opinions.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#646864] hover:text-[#23493a] transition-colors font-medium">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>All Legal Opinions</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#e5e3dc] bg-white text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] shadow-xs transition-colors">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Print Memorandum</span>
            </button>

            @if($opinion->status !== 'published')
            <a href="{{ route('opinions.edit', $opinion) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#e5e3dc] bg-white text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] shadow-xs transition-colors">
                <span class="material-symbols-outlined text-base">edit</span>
                <span>Edit</span>
            </a>
            @endif

            <!-- Status Transition Buttons -->
            @if($opinion->status === 'draft')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="under_review">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-medium hover:bg-amber-700 shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-base">rate_review</span>
                    <span>Submit for Review</span>
                </button>
            </form>
            @elseif($opinion->status === 'under_review')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-base">verified</span>
                    <span>Approve Opinion</span>
                </button>
            </form>
            @elseif($opinion->status === 'approved')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="published">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Publish &amp; Deliver</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Formal Legal Document Container -->
    <article class="bg-white rounded-xl border border-[#e5e3dc] p-8 md:p-12 shadow-sm">
        
        <!-- Chambers Letterhead Header -->
        <div class="border-b-2 border-[#23493a] pb-6 mb-8 text-center">
            <h2 class="font-bold text-xl tracking-tight text-[#1a1a1a] uppercase">{{ config('legal.app_name', 'Lawyer Practice Management') }}</h2>
            <p class="text-[11px] font-mono tracking-widest text-[#23493a] uppercase mt-1">Advocates &amp; Legal Consultants • Supreme Court &amp; High Courts</p>
            <div class="h-0.5 w-16 bg-[#23493a] mx-auto mt-3"></div>
        </div>

        <!-- Document Metadata Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-[#faf8f5] border border-[#e5e3dc] mb-8 text-xs">
            <div>
                <span class="text-[#646864] font-mono text-[10px] uppercase block">Opinion Reference</span>
                <span class="font-mono font-bold text-[#23493a] text-sm">{{ $opinion->opinion_number }}</span>
            </div>
            <div>
                <span class="text-[#646864] font-mono text-[10px] uppercase block">Date Rendered</span>
                <span class="font-mono text-[#1a1a1a] font-semibold">{{ $opinion->published_at ? $opinion->published_at->format('d M Y') : $opinion->created_at->format('d M Y') }}</span>
            </div>
            <div>
                <span class="text-[#646864] font-mono text-[10px] uppercase block">Advisory Class</span>
                <span class="capitalize font-semibold text-[#1a1a1a]">{{ str_replace('_', ' ', $opinion->type) }}</span>
            </div>
            <div>
                <span class="text-[#646864] font-mono text-[10px] uppercase block">Workflow Stage</span>
                @if($opinion->status === 'published')
                <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published &amp; Delivered
                </span>
                @elseif($opinion->status === 'under_review')
                <span class="inline-flex items-center gap-1.5 text-amber-700 font-semibold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> In Peer Review
                </span>
                @elseif($opinion->status === 'approved')
                <span class="inline-flex items-center gap-1.5 text-blue-700 font-semibold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Approved
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 text-[#646864] font-medium text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#8a8a8a]"></span> Draft Memorandum
                </span>
                @endif
            </div>
        </div>

        <!-- Reference Case & Client Banner (if linked) -->
        @if($opinion->matter || $opinion->client)
        <div class="mb-8 p-3.5 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] flex flex-wrap items-center justify-between text-xs gap-2">
            @if($opinion->matter)
            <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] uppercase text-[#646864]">In Re Matter:</span>
                <a href="{{ route('matters.show', $opinion->matter) }}" class="font-semibold text-[#23493a] hover:underline">
                    {{ $opinion->matter->case_number }} — {{ $opinion->matter->title }}
                </a>
            </div>
            @endif
            @if($opinion->client)
            <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] uppercase text-[#646864]">Instructing Client:</span>
                <span class="font-semibold text-[#1a1a1a]">{{ $opinion->client->name }}</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Title / Question -->
        <h1 class="font-bold text-xl text-[#1a1a1a] leading-snug mb-6 border-b border-[#e5e3dc] pb-4">
            {{ $opinion->title }}
        </h1>

        <!-- Executive Summary -->
        @if($opinion->summary)
        <div class="p-5 rounded-xl bg-[#faf8f5] border-l-4 border-[#23493a] mb-8">
            <h3 class="font-mono text-[10px] uppercase tracking-wider text-[#23493a] font-bold mb-2">Statement of Issue &amp; Facts</h3>
            <p class="text-xs text-[#1a1a1a] leading-relaxed">{{ $opinion->summary }}</p>
        </div>
        @endif

        <!-- Substantive Legal Analysis Body -->
        <div class="max-w-none text-[#1a1a1a] text-xs leading-relaxed mb-8 space-y-4 whitespace-pre-line">
            {{ $opinion->body }}
        </div>

        <!-- Recommendations Block -->
        @if($opinion->recommendations)
        <div class="p-5 rounded-xl bg-emerald-50/70 border border-emerald-200 mb-8">
            <h3 class="font-semibold text-xs text-emerald-900 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-700 text-base">check_circle</span>
                <span>Chambers Recommendations &amp; Course of Action</span>
            </h3>
            <div class="text-xs text-emerald-950 leading-relaxed whitespace-pre-line">
                {{ $opinion->recommendations }}
            </div>
        </div>
        @endif

        <!-- Precedents Cited Block -->
        @if($opinion->precedents_cited)
        <div class="p-5 rounded-xl bg-[#faf8f5] border border-[#e5e3dc] mb-8">
            <h3 class="font-mono text-[10px] uppercase tracking-wider text-[#646864] font-bold mb-2 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base text-[#23493a]">menu_book</span>
                <span>Judicial Authorities &amp; Statutory Precedents Cited</span>
            </h3>
            <div class="font-mono text-xs text-[#1a1a1a] leading-relaxed whitespace-pre-line">
                {{ $opinion->precedents_cited }}
            </div>
        </div>
        @endif

        <!-- Sign-off Block -->
        <div class="pt-8 border-t border-[#e5e3dc] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-xs text-[#646864]">
            <div>
                <span class="block font-mono text-[10px] uppercase text-[#646864]">Authored by:</span>
                <span class="font-semibold text-[#1a1a1a] text-xs block mt-0.5">{{ $opinion->author->name ?? 'Advocate' }}</span>
                <span class="text-[11px] text-[#646864]">{{ $opinion->author->title ?? 'Chambers Counsel' }}</span>
            </div>
            @if($opinion->reviewer)
            <div>
                <span class="block font-mono text-[10px] uppercase text-[#646864]">Reviewed &amp; Approved by:</span>
                <span class="font-semibold text-[#1a1a1a] text-xs block mt-0.5">{{ $opinion->reviewer->name }}</span>
                <span class="text-[11px] text-[#646864]">{{ $opinion->reviewer->title ?? 'Partner' }}</span>
            </div>
            @endif
        </div>

    </article>

</div>
@endsection
