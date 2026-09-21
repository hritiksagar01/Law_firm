@extends('portal.layout')

@section('title', 'Document Requests')

@section('content')
<div class="flex flex-col gap-6" x-data="{ uploadModalOpen: false, selectedRequestId: null, selectedRequestTitle: '', selectedMatterTitle: '', isResubmission: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#222222]">Document Requests</h1>
            <p class="text-xs text-[#766A5E] mt-1">Specific documents, contracts, and filings requested by your legal team for active proceedings.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            @php $pendingCount = $requests->whereIn('status', ['pending', 'rejected'])->count(); @endphp
            <span class="px-3 py-1 {{ $pendingCount > 0 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' }} border rounded-lg font-mono font-medium">
                {{ $pendingCount }} Action Required
            </span>
        </div>
    </div>

    <!-- Status Filters -->
    @php
        $activeTab = request('status', 'all');
    @endphp
    <div class="flex items-center gap-2 border-b border-[#EFECE6] pb-2 text-xs overflow-x-auto">
        <a href="{{ route('portal.requests.index') }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'all' ? 'bg-[#9F8349] text-white' : 'text-[#766A5E] hover:text-[#222222] hover:bg-white' }}">
            All Requests ({{ $requests->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'pending' ? 'bg-amber-700 text-white' : 'text-[#766A5E] hover:text-[#222222] hover:bg-white' }}">
            Pending ({{ $requests->where('status', 'pending')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'rejected' ? 'bg-red-700 text-white' : 'text-[#766A5E] hover:text-[#222222] hover:bg-white' }}">
            Re-submission Needed ({{ $requests->where('status', 'rejected')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'submitted']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'submitted' ? 'bg-blue-700 text-white' : 'text-[#766A5E] hover:text-[#222222] hover:bg-white' }}">
            Submitted ({{ $requests->where('status', 'submitted')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'completed' ? 'bg-[#856C36] text-white' : 'text-[#766A5E] hover:text-[#222222] hover:bg-white' }}">
            Completed &amp; Accepted ({{ $requests->where('status', 'completed')->count() }})
        </a>
    </div>

    <!-- Requests Table / List -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono text-[10px] uppercase">
                        <th class="py-3.5 px-4 font-semibold">Document Title &amp; Scope</th>
                        <th class="py-3.5 px-4 font-semibold">Associated Case</th>
                        <th class="py-3.5 px-4 font-semibold">Category</th>
                        <th class="py-3.5 px-4 font-semibold">Due Date</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#FAF8F5]">
                    @php
                        $filtered = $requests;
                        if ($activeTab !== 'all') {
                            $filtered = $requests->where('status', $activeTab);
                        }
                    @endphp
                    @forelse($filtered as $req)
                    <tr class="hover:bg-[#FAF8F5]/60 transition-colors">
                        <td class="py-4 px-4 max-w-sm">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#222222] text-sm">{{ $req->title }}</span>
                                    @if($req->priority === 'urgent')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-red-100 text-red-800 uppercase">Urgent</span>
                                    @elseif($req->priority === 'high')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-amber-100 text-amber-800 uppercase">High</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-[#766A5E] leading-relaxed">{{ $req->description }}</p>

                                @if($req->status === 'rejected' && ($req->rejection_reason || $req->review_notes))
                                <div class="p-2 rounded bg-red-50 border border-red-200 mt-1.5 text-red-800 text-[11px]">
                                    <strong class="block text-[10px] uppercase font-mono text-red-900">Advocate's Remarks / Correction Needed:</strong>
                                    {{ $req->rejection_reason ?: $req->review_notes }}
                                </div>
                                @endif

                                @if($req->client_notes)
                                <p class="text-[10px] font-mono text-[#9F8349] bg-[#F4ECE1]/30 p-1 rounded mt-0.5">
                                    Your Submission: {{ $req->client_notes }}
                                </p>
                                @endif
                            </div>
                        </td>

                        <td class="py-4 px-4 text-[#554D45]">
                            <div class="flex flex-col">
                                <span class="font-semibold text-[#222222] truncate max-w-[200px]">{{ $req->matter->title ?? 'General Matter' }}</span>
                                <span class="font-mono text-[10px] text-[#766A5E]">{{ $req->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-[#766A5E]">
                            <span class="px-2 py-0.5 rounded bg-[#FAF8F5] font-mono text-[10px] text-[#554D45]">
                                {{ $req->category }}
                            </span>
                        </td>

                        <td class="py-4 px-4 font-mono text-[11px]">
                            @if($req->due_date)
                            <span class="{{ \Carbon\Carbon::parse($req->due_date)->isPast() && $req->status === 'pending' ? 'text-red-600 font-bold' : 'text-[#766A5E]' }}">
                                {{ \Carbon\Carbon::parse($req->due_date)->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-[#EAE4DC]">—</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($req->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Pending Upload</span>
                            </span>
                            @elseif($req->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-800 border border-red-200">
                                <span class="material-symbols-outlined text-[13px]">error</span>
                                <span>Re-submission Required</span>
                            </span>
                            @elseif($req->status === 'submitted')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                <span>Submitted for Counsel Review</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F8F4EE] text-[#856C36] border border-[#E8DAC8]">
                                <span class="material-symbols-outlined text-xs text-[#9F8349]">check</span>
                                <span>Verified &amp; Filed</span>
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($req->status === 'pending' || $req->status === 'rejected')
                            <button type="button" @click="selectedRequestId = {{ $req->id }}; selectedRequestTitle = '{{ addslashes($req->title) }}'; selectedMatterTitle = '{{ addslashes($req->matter->title ?? '') }}'; isResubmission = {{ $req->status === 'rejected' ? 'true' : 'false' }}; uploadModalOpen = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $req->status === 'rejected' ? 'bg-red-700 hover:bg-red-800' : 'bg-[#9F8349] hover:bg-[#856C36]' }} text-white text-xs font-semibold transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-sm">{{ $req->status === 'rejected' ? 'replay' : 'upload_file' }}</span>
                                <span>{{ $req->status === 'rejected' ? 'Re-upload Document' : 'Upload & Fulfill' }}</span>
                            </button>
                            @else
                            <span class="text-xs text-[#766A5E] font-mono">
                                {{ $req->submitted_at ? \Carbon\Carbon::parse($req->submitted_at)->format('d M, h:i A') : 'Completed' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#766A5E]">
                            <span class="material-symbols-outlined text-3xl mb-2 text-[#EAE4DC]">done_all</span>
                            <p class="text-xs">No document requests found in this view.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload & Fulfill Modal -->
    <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="uploadModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#EFECE6] flex flex-col gap-5 relative">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold" x-text="isResubmission ? 'Re-submission Upload' : 'Fulfill Document Request'"></span>
                    <h3 class="text-base font-bold text-[#222222] mt-0.5" x-text="selectedRequestTitle"></h3>
                    <p class="text-xs text-[#766A5E]" x-text="selectedMatterTitle"></p>
                </div>
                <button type="button" @click="uploadModalOpen = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'/portal/requests/' + selectedRequestId + '/upload'" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#222222]">Select Document File (PDF, DOCX, JPG, PNG up to 50MB) *</label>
                    <input type="file" name="file" required class="w-full text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#9F8349] file:text-white hover:file:bg-[#856C36] file:cursor-pointer p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC]"/>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#222222]">Client Submission Remarks (Optional)</label>
                    <textarea name="client_notes" rows="3" placeholder="Add any details, date of execution, certified copy number, or remarks for counsel..." class="w-full p-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#9F8349]"></textarea>
                </div>

                <div class="p-3 bg-[#F8F4EE] rounded-lg border border-[#F4ECE1] flex items-center gap-2 text-[11px] text-[#9F8349]">
                    <span class="material-symbols-outlined text-base text-[#9F8349] shrink-0">lock</span>
                    <span>Protected under Section 126 Evidence Act. System computes SHA-256 hash for evidentiary integrity.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#FAF8F5]">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-[#766A5E] hover:bg-[#FAF8F5]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm">
                        Submit Document to Legal Counsel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
