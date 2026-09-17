@extends('layouts.app')

@section('title', 'Edit ' . $opinion->opinion_number . ' — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <div class="mb-8">
        <a href="{{ route('opinions.show', $opinion) }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Opinion</span>
        </a>
        <h1 class="text-3xl font-serif font-bold text-[#222222]">Edit Legal Opinion</h1>
        <p class="text-sm text-[#766A5E] mt-1">Refine legal analysis, citations, and strategic recommendations</p>
    </div>

    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
        <div class="font-semibold mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-rose-600">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
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
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">bookmark</span>
                <span>Opinion Particulars</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Subject / Question of Law *</label>
                    <input type="text" name="title" value="{{ old('title', $opinion->title) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Advisory Type *</label>
                    <select name="type" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="legal_advice" {{ old('type', $opinion->type) === 'legal_advice' ? 'selected' : '' }}>Legal Advice (Formal Counsel)</option>
                        <option value="case_strategy" {{ old('type', $opinion->type) === 'case_strategy' ? 'selected' : '' }}>Case Strategy &amp; Trial Tactics</option>
                        <option value="document_review" {{ old('type', $opinion->type) === 'document_review' ? 'selected' : '' }}>Document &amp; Contract Review</option>
                        <option value="statutory_compliance" {{ old('type', $opinion->type) === 'statutory_compliance' ? 'selected' : '' }}>Regulatory Compliance Advisory</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Associated Case / Matter</label>
                    <select name="matter_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="">-- General Chambers Advisory (No Case Linked) --</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}" {{ old('matter_id', $opinion->matter_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->case_number }} — {{ $m->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Client</label>
                    <select name="client_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="">-- Select Client --</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $opinion->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Reviewing Partner</label>
                    <select name="reviewer_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="">-- Assign Reviewer --</option>
                        @foreach($reviewers as $rev)
                        <option value="{{ $rev->id }}" {{ old('reviewer_id', $opinion->reviewer_id) == $rev->id ? 'selected' : '' }}>{{ $rev->name }} ({{ $rev->title ?? $rev->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Body & Legal Analysis -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs space-y-4">
            <h2 class="text-base font-serif font-bold text-[#222222] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">gavel</span>
                <span>Substantive Legal Analysis</span>
            </h2>

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Executive Summary / Brief Statement of Facts</label>
                <textarea name="summary" rows="3"
                    class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">{{ old('summary', $opinion->summary) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Detailed Legal Opinion &amp; Rationale *</label>
                <textarea name="body" rows="10" required
                    class="w-full px-3.5 py-2 text-xs font-sans rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] leading-relaxed">{{ old('body', $opinion->body) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Actionable Recommendations</label>
                <textarea name="recommendations" rows="3"
                    class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">{{ old('recommendations', $opinion->recommendations) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Judicial Precedents &amp; Statutory Citations</label>
                <textarea name="precedents_cited" rows="3"
                    class="w-full px-3.5 py-2 text-xs font-mono rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">{{ old('precedents_cited', $opinion->precedents_cited) }}</textarea>
            </div>
        </div>

        <!-- Workflow Status -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs flex items-center justify-between">
            <div>
                <label class="block text-xs font-semibold text-[#222222]">Workflow Status</label>
                <p class="text-[11px] text-[#766A5E]">Update review state or publish</p>
            </div>
            <select name="status" class="px-4 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] font-medium">
                <option value="draft" {{ old('status', $opinion->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="under_review" {{ old('status', $opinion->status) === 'under_review' ? 'selected' : '' }}>Under Peer Review</option>
                <option value="approved" {{ old('status', $opinion->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="published" {{ old('status', $opinion->status) === 'published' ? 'selected' : '' }}>Published &amp; Delivered</option>
            </select>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('opinions.show', $opinion) }}" class="px-5 py-2.5 rounded-lg border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">save</span>
                <span>Update Opinion</span>
            </button>
        </div>
    </form>

</div>
@endsection
