@extends('layouts.app')

@section('title', 'Legal Opinions & Strategy Memoranda — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8">

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

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span>Chambers Jurisprudence</span>
                <span>•</span>
                <span>Case Strategy &amp; Advisory</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Legal Opinions &amp; Memoranda</h1>
            <p class="text-sm text-[#766A5E] mt-1">Formal legal advice, litigation strategy assessments, and statutory review annotations</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('opinions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">draw</span>
                <span>Draft Legal Opinion</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Opinions</span>
            <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $stats['total'] }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Formal Opinions Issued</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Published / Delivered</span>
            <span class="text-2xl font-serif font-bold text-emerald-700 mt-1 block">{{ $stats['published'] }}</span>
            <span class="text-xs text-emerald-600 mt-2 block font-mono">Final &amp; Rendered</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Under Partner Review</span>
            <span class="text-2xl font-serif font-bold text-amber-700 mt-1 block">{{ $stats['under_review'] }}</span>
            <span class="text-xs text-amber-600 mt-2 block font-mono">Pending Peer Review</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Drafts</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $stats['draft'] }}</span>
            <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">In Preparation</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-[#EFECE6] shadow-xs mb-6">
        <form method="GET" action="{{ route('opinions.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by opinion #, subject title, legal keywords..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-[#EAE4DC] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] bg-[#FAF8F5]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="type" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Advisory Types</option>
                    <option value="legal_advice" {{ request('type') == 'legal_advice' ? 'selected' : '' }}>Legal Advice</option>
                    <option value="case_strategy" {{ request('type') == 'case_strategy' ? 'selected' : '' }}>Case Strategy</option>
                    <option value="document_review" {{ request('type') == 'document_review' ? 'selected' : '' }}>Document Review</option>
                    <option value="statutory_compliance" {{ request('type') == 'statutory_compliance' ? 'selected' : '' }}>Compliance</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
                @if(request('q') || request('type') || request('status'))
                <a href="{{ route('opinions.index') }}" class="text-xs text-[#766A5E] hover:text-[#222222] px-2">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Opinions Table -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                    <th class="py-3.5 px-4">Opinion # &amp; Subject</th>
                    <th class="py-3.5 px-4">Category</th>
                    <th class="py-3.5 px-4">Matter / Client</th>
                    <th class="py-3.5 px-4">Author &amp; Reviewer</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFECE6] text-sm">
                @forelse($opinions as $op)
                <tr class="hover:bg-[#FAF8F5] transition-colors">
                    <td class="py-3.5 px-4">
                        <a href="{{ route('opinions.show', $op) }}" class="font-semibold text-[#222222] hover:text-[#9F8349] hover:underline block">
                            {{ $op->title }}
                        </a>
                        <span class="font-mono text-[10px] text-[#9F8349]">{{ $op->opinion_number }}</span>
                        @if($op->summary)
                        <p class="text-[11px] text-[#766A5E] mt-0.5 line-clamp-1">{{ $op->summary }}</p>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono capitalize bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222]">
                            {{ str_replace('_', ' ', $op->type) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs">
                        @if($op->matter)
                        <a href="{{ route('matters.show', $op->matter) }}" class="text-[#222222] font-medium hover:underline block truncate max-w-xs">
                            {{ $op->matter->title }}
                        </a>
                        <span class="font-mono text-[10px] text-[#766A5E]">{{ $op->matter->case_number }}</span>
                        @elseif($op->client)
                        <span class="text-[#222222] font-medium">{{ $op->client->name }}</span>
                        @else
                        <span class="text-gray-400 italic">General Chambers Advisory</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-xs">
                        <div class="text-[#222222] font-medium">{{ $op->author->name ?? 'Advocate' }}</div>
                        @if($op->reviewer)
                        <div class="text-[10px] text-[#766A5E]">Rev: {{ $op->reviewer->name }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        @if($op->status === 'published')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Published
                        </span>
                        @elseif($op->status === 'under_review')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            In Review
                        </span>
                        @elseif($op->status === 'approved')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Approved
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-700 border border-gray-200">
                            Draft
                        </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('opinions.show', $op) }}" class="p-1 rounded text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="Read Opinion">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            <a href="{{ route('opinions.edit', $op) }}" class="p-1 rounded text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="Edit Draft">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-[#766A5E]">
                        <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">menu_book</span>
                        <p class="font-medium">No legal opinions recorded yet.</p>
                        <a href="{{ route('opinions.create') }}" class="inline-block mt-3 text-xs text-[#9F8349] font-semibold hover:underline">
                            Draft the first legal opinion &rarr;
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($opinions->hasPages())
        <div class="p-4 border-t border-[#EFECE6]">
            {{ $opinions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
