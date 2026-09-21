@extends('layouts.app')

@section('title', 'Edit ' . $opinion->opinion_number . ' — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <div class="mb-6">
        <a href="{{ route('opinions.show', $opinion) }}" class="inline-flex items-center gap-1.5 text-xs text-[#646864] hover:text-[#23493a] transition-colors mb-2 font-medium">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Opinion</span>
        </a>
        <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Edit Legal Opinion</h1>
        <p class="text-xs text-[#646864] mt-0.5">Refine legal analysis, citations, and strategic recommendations</p>
    </div>

    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        <div class="font-semibold mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-rose-600 text-sm">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-rose-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('opinions.update', $opinion) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Metadata Section -->
        <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm">
            <h2 class="text-sm font-semibold text-[#1a1a1a] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-base">bookmark</span>
                <span>Opinion Particulars</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Subject / Question of Law <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $opinion->title) }}" required
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Advisory Type <span class="text-rose-500">*</span></label>
                    <select name="type" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                        <option value="legal_advice" {{ old('type', $opinion->type) === 'legal_advice' ? 'selected' : '' }}>Legal Advice (Formal Counsel)</option>
                        <option value="case_strategy" {{ old('type', $opinion->type) === 'case_strategy' ? 'selected' : '' }}>Case Strategy &amp; Trial Tactics</option>
                        <option value="document_review" {{ old('type', $opinion->type) === 'document_review' ? 'selected' : '' }}>Document &amp; Contract Review</option>
                        <option value="statutory_compliance" {{ old('type', $opinion->type) === 'statutory_compliance' ? 'selected' : '' }}>Regulatory Compliance Advisory</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Associated Case / Matter</label>
                    <select name="matter_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                        <option value="">-- General Chambers Advisory (No Case Linked) --</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}" {{ old('matter_id', $opinion->matter_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->case_number }} — {{ $m->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Client</label>
                    <select name="client_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                        <option value="">-- Select Client --</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $opinion->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Reviewing Partner</label>
                    <select name="reviewer_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                        <option value="">-- Assign Reviewer --</option>
                        @foreach($reviewers as $rev)
                        <option value="{{ $rev->id }}" {{ old('reviewer_id', $opinion->reviewer_id) == $rev->id ? 'selected' : '' }}>{{ $rev->name }} ({{ $rev->title ?? $rev->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Body & Legal Analysis -->
        <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm space-y-4">
            <h2 class="text-sm font-semibold text-[#1a1a1a] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-base">gavel</span>
                <span>Substantive Legal Analysis</span>
            </h2>

            <div>
                <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Executive Summary / Brief Statement of Facts</label>
                <textarea name="summary" rows="3"
                    class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">{{ old('summary', $opinion->summary) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Detailed Legal Opinion &amp; Rationale <span class="text-rose-500">*</span></label>
                <textarea name="body" rows="10" required
                    class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a] leading-relaxed">{{ old('body', $opinion->body) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Actionable Recommendations</label>
                <textarea name="recommendations" rows="3"
                    class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">{{ old('recommendations', $opinion->recommendations) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Judicial Precedents &amp; Statutory Citations</label>
                <textarea name="precedents_cited" rows="3"
                    class="w-full px-3.5 py-2 text-xs font-mono rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">{{ old('precedents_cited', $opinion->precedents_cited) }}</textarea>
            </div>
        </div>

        <!-- Workflow Status -->
        <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <label class="block text-xs font-medium text-[#1a1a1a]">Workflow Status</label>
                <p class="text-[11px] text-[#646864] mt-0.5">Update review state or publish</p>
            </div>
            <select name="status" class="px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a] font-medium">
                <option value="draft" {{ old('status', $opinion->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="under_review" {{ old('status', $opinion->status) === 'under_review' ? 'selected' : '' }}>Under Peer Review</option>
                <option value="approved" {{ old('status', $opinion->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="published" {{ old('status', $opinion->status) === 'published' ? 'selected' : '' }}>Published &amp; Delivered</option>
            </select>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('opinions.show', $opinion) }}" class="px-4 py-2 rounded-lg border border-[#e5e3dc] bg-white text-[#1a1a1a] text-xs font-medium hover:bg-[#faf8f5] transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">save</span>
                <span>Update Opinion</span>
            </button>
        </div>
    </form>

</div>
@endsection
