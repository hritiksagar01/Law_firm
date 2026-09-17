@extends('layouts.app')

@section('title', $opinion->opinion_number . ' — ' . $opinion->title)

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-rose-600">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('opinions.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349]">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>All Legal Opinions</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#EAE4DC] bg-white text-xs font-semibold text-[#222222] hover:bg-[#FAF8F5] shadow-xs">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Print Memorandum</span>
            </button>

            @if($opinion->status !== 'published')
            <a href="{{ route('opinions.edit', $opinion) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#EAE4DC] bg-white text-xs font-semibold text-[#222222] hover:bg-[#FAF8F5] shadow-xs">
                <span class="material-symbols-outlined text-base">edit</span>
                <span>Edit</span>
            </a>
            @endif

            <!-- Status Transition Buttons -->
            @if($opinion->status === 'draft')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="under_review">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 shadow-xs">
                    <span class="material-symbols-outlined text-base">rate_review</span>
                    <span>Submit for Review</span>
                </button>
            </form>
            @elseif($opinion->status === 'under_review')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 shadow-xs">
                    <span class="material-symbols-outlined text-base">verified</span>
                    <span>Approve Opinion</span>
                </button>
            </form>
            @elseif($opinion->status === 'approved')
            <form method="POST" action="{{ route('opinions.change-status', $opinion) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="published">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-xs">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Publish &amp; Deliver</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Formal Legal Document Container -->
    <article class="bg-white rounded-2xl border border-[#EFECE6] p-8 md:p-12 shadow-sm">
        
        <!-- Chambers Letterhead Header -->
        <div class="border-b-2 border-[#9F8349] pb-6 mb-8 text-center">
            <h2 class="font-serif font-bold text-2xl tracking-wide text-[#222222] uppercase">{{ config('legal.app_name', 'Vennamraj Associates') }}</h2>
            <p class="text-xs font-mono tracking-widest text-[#9F8349] uppercase mt-1">Advocates &amp; Legal Consultants • Supreme Court &amp; High Courts</p>
            <div class="h-0.5 w-16 bg-[#9F8349] mx-auto mt-3"></div>
        </div>

        <!-- Document Metadata Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC] mb-8 text-xs">
            <div>
                <span class="text-[#766A5E] font-mono text-[10px] uppercase block">Opinion Reference</span>
                <span class="font-mono font-bold text-[#9F8349] text-sm">{{ $opinion->opinion_number }}</span>
            </div>
            <div>
                <span class="text-[#766A5E] font-mono text-[10px] uppercase block">Date Rendered</span>
                <span class="font-mono text-[#222222] font-semibold">{{ $opinion->published_at ? $opinion->published_at->format('d F Y') : $opinion->created_at->format('d F Y') }}</span>
            </div>
            <div>
                <span class="text-[#766A5E] font-mono text-[10px] uppercase block">Advisory Class</span>
                <span class="capitalize font-semibold text-[#222222]">{{ str_replace('_', ' ', $opinion->type) }}</span>
            </div>
            <div>
                <span class="text-[#766A5E] font-mono text-[10px] uppercase block">Workflow Stage</span>
                @if($opinion->status === 'published')
                <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold font-mono text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Final &amp; Delivered
                </span>
                @elseif($opinion->status === 'under_review')
                <span class="inline-flex items-center gap-1 text-amber-700 font-semibold font-mono text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> In Peer Review
                </span>
                @elseif($opinion->status === 'approved')
                <span class="inline-flex items-center gap-1 text-blue-700 font-semibold font-mono text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Approved
                </span>
                @else
                <span class="text-gray-500 font-mono text-[11px]">Draft Memorandum</span>
                @endif
            </div>
        </div>

        <!-- Reference Case & Client Banner (if linked) -->
        @if($opinion->matter || $opinion->client)
        <div class="mb-8 p-3.5 rounded-lg border border-[#EAE4DC] bg-white flex flex-wrap items-center justify-between text-xs gap-2">
            @if($opinion->matter)
            <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] uppercase text-[#766A5E]">In Re Matter:</span>
                <a href="{{ route('matters.show', $opinion->matter) }}" class="font-semibold text-[#9F8349] hover:underline">
                    {{ $opinion->matter->case_number }} — {{ $opinion->matter->title }}
                </a>
            </div>
            @endif
            @if($opinion->client)
            <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] uppercase text-[#766A5E]">Instructing Client:</span>
                <span class="font-semibold text-[#222222]">{{ $opinion->client->name }}</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Title / Question -->
        <h1 class="font-serif font-bold text-2xl text-[#222222] leading-snug mb-6 border-b border-[#EFECE6] pb-4">
            {{ $opinion->title }}
        </h1>

        <!-- Executive Summary -->
        @if($opinion->summary)
        <div class="p-5 rounded-xl bg-[#FAF8F5] border-l-4 border-[#9F8349] mb-8">
            <h3 class="font-mono text-[11px] uppercase tracking-wider text-[#9F8349] font-bold mb-2">Statement of Issue &amp; Facts</h3>
            <p class="text-sm text-[#222222] leading-relaxed italic">{{ $opinion->summary }}</p>
        </div>
        @endif

        <!-- Substantive Legal Analysis Body -->
        <div class="prose max-w-none text-[#222222] text-sm leading-relaxed mb-8 space-y-4 font-sans whitespace-pre-line">
            {{ $opinion->body }}
        </div>

        <!-- Recommendations Block -->
        @if($opinion->recommendations)
        <div class="p-5 rounded-xl bg-emerald-50/50 border border-emerald-200 mb-8">
            <h3 class="font-serif font-bold text-sm text-emerald-900 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
                <span>Chambers Recommendations &amp; Course of Action</span>
            </h3>
            <div class="text-xs text-emerald-950 leading-relaxed whitespace-pre-line">
                {{ $opinion->recommendations }}
            </div>
        </div>
        @endif

        <!-- Precedents Cited Block -->
        @if($opinion->precedents_cited)
        <div class="p-5 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC] mb-8">
            <h3 class="font-mono text-[11px] uppercase tracking-wider text-[#766A5E] font-bold mb-2 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base text-[#9F8349]">menu_book</span>
                <span>Judicial Authorities &amp; Statutory Precedents Cited</span>
            </h3>
            <div class="font-mono text-xs text-[#222222] leading-relaxed whitespace-pre-line">
                {{ $opinion->precedents_cited }}
            </div>
        </div>
        @endif

        <!-- Sign-off Block -->
        <div class="pt-8 border-t border-[#EFECE6] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-xs text-[#766A5E]">
            <div>
                <span class="block font-mono text-[10px] uppercase text-[#766A5E]">Authored by:</span>
                <span class="font-semibold text-[#222222] text-sm block mt-0.5">{{ $opinion->author->name ?? 'Advocate' }}</span>
                <span class="text-[11px] text-[#766A5E]">{{ $opinion->author->title ?? 'Chambers Counsel' }}</span>
            </div>
            @if($opinion->reviewer)
            <div>
                <span class="block font-mono text-[10px] uppercase text-[#766A5E]">Reviewed &amp; Approved by:</span>
                <span class="font-semibold text-[#222222] text-sm block mt-0.5">{{ $opinion->reviewer->name }}</span>
                <span class="text-[11px] text-[#766A5E]">{{ $opinion->reviewer->title ?? 'Partner' }}</span>
            </div>
            @endif
        </div>

    </article>

</div>
@endsection
