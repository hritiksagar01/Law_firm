@extends('layouts.app')

@section('title', 'Legal Opinions & Strategy Memoranda — ' . config('legal.app_name', 'Sharma Legal Chambers'))
@section('header_title', 'Legal Opinions')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Legal Opinions &amp; Memoranda</h1>
            <p class="text-[13px] text-[#646864] mt-1">Formal legal advice, litigation strategy assessments, and statutory review annotations</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('opinions.create') }}" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">draw</span>
                <span>Draft Legal Opinion</span>
            </a>
        </div>
    </div>

    <!-- Top 4 Connected Metric Stat Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Total Opinions</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $stats['total'] }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Formal opinions issued</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Published / Delivered</div>
            <div class="text-[28px] font-medium text-[#065f46] tracking-tight mt-1">{{ $stats['published'] }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Final and rendered to client</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Under Partner Review</div>
            <div class="text-[28px] font-medium text-[#d97706] tracking-tight mt-1">{{ $stats['under_review'] }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Pending supervisory review</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Drafts</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $stats['draft'] }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">In drafting phase</div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-md border border-[#e5e3dc] shadow-xs mb-6">
        <form method="GET" action="{{ route('opinions.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#8a8a8a] text-[18px]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by opinion #, subject title, legal keywords..."
                    class="w-full pl-9 pr-4 h-9 text-xs rounded-md border border-[#e5e3dc] focus:outline-none focus:border-[#23493a] bg-white text-[#1a1a1a]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="type" onchange="this.form.submit()" class="text-xs h-9 px-3 rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Advisory Types</option>
                    <option value="legal_advice" {{ request('type') == 'legal_advice' ? 'selected' : '' }}>Legal Advice</option>
                    <option value="case_strategy" {{ request('type') == 'case_strategy' ? 'selected' : '' }}>Case Strategy</option>
                    <option value="document_review" {{ request('type') == 'document_review' ? 'selected' : '' }}>Document Review</option>
                    <option value="statutory_compliance" {{ request('type') == 'statutory_compliance' ? 'selected' : '' }}>Compliance</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="text-xs h-9 px-3 rounded-md border border-[#e5e3dc] bg-white text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
                @if(request('q') || request('type') || request('status'))
                <a href="{{ route('opinions.index') }}" class="text-xs text-[#23493a] hover:underline px-2">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Opinions Table -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse text-[13px]">
            <thead>
                <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider">
                    <th class="py-3 px-4 font-medium">Opinion # &amp; Subject</th>
                    <th class="py-3 px-4 font-medium">Category</th>
                    <th class="py-3 px-4 font-medium">Matter / Client</th>
                    <th class="py-3 px-4 font-medium">Author &amp; Reviewer</th>
                    <th class="py-3 px-4 font-medium text-center">Status</th>
                    <th class="py-3 px-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0eee8]">
                @forelse($opinions as $op)
                <tr class="hover:bg-[#faf9f5] transition-colors">
                    <td class="py-3.5 px-4">
                        <a href="{{ route('opinions.show', $op) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline block">
                            {{ $op->title }}
                        </a>
                        <span class="font-mono text-[10px] text-[#23493a] font-semibold">{{ $op->opinion_number }}</span>
                        @if($op->summary)
                        <p class="text-[11.5px] text-[#646864] mt-0.5 line-clamp-1">{{ $op->summary }}</p>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded text-[10.5px] font-mono capitalize bg-[#f5f3ed] border border-[#e5e3dc] text-[#1a1a1a]">
                            {{ str_replace('_', ' ', $op->type) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs">
                        @if($op->matter)
                        <a href="{{ route('matters.show', $op->matter) }}" class="text-[#1a1a1a] font-medium hover:underline block truncate max-w-xs">
                            {{ $op->matter->title }}
                        </a>
                        <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $op->matter->case_number }}</span>
                        @elseif($op->client)
                        <span class="text-[#1a1a1a] font-medium">{{ $op->client->name }}</span>
                        @else
                        <span class="text-[#8a8a8a] italic">General Chambers Advisory</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-xs">
                        <div class="text-[#1a1a1a] font-medium">{{ $op->author->name ?? 'Advocate' }}</div>
                        @if($op->reviewer)
                        <div class="text-[10.5px] text-[#8a8a8a]">Rev: {{ $op->reviewer->name }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        @if($op->status === 'published')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                            Published
                        </span>
                        @elseif($op->status === 'under_review')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#d97706]"></span>
                            In Review
                        </span>
                        @elseif($op->status === 'approved')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Approved
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-gray-50 text-gray-700 border border-gray-200">
                            Draft
                        </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('opinions.show', $op) }}" class="p-1 rounded text-[#646864] hover:text-[#23493a] hover:bg-[#f5f3ed] transition-colors" title="Read Opinion">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            <a href="{{ route('opinions.edit', $op) }}" class="p-1 rounded text-[#646864] hover:text-[#23493a] hover:bg-[#f5f3ed] transition-colors" title="Edit Draft">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-[#8a8a8a]">
                        <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">menu_book</span>
                        <p class="font-medium">No legal opinions recorded yet.</p>
                        <a href="{{ route('opinions.create') }}" class="inline-block mt-3 text-xs text-[#23493a] font-medium hover:underline">
                            Draft the first legal opinion &rarr;
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($opinions->hasPages())
        <div class="p-4 border-t border-[#f0eee8]">
            {{ $opinions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
