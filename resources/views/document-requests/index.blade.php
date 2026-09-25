@extends('layouts.app')

@section('title', 'Document Requests & Client Submissions — Sharma Legal Chambers')
@section('header_title', 'Document Requests')

@section('content')
<div x-data="{
    openRequestModal: false,
    openReviewModal: false,
    openAssistedModal: false,
    selectedRequest: null,
    reviewStatus: 'completed',
    rejectionReason: '',
    selectedMatterId: '',
    selectedClientId: '',
    presetCategory: 'KYC & Identification',
    presetTitle: '',
    presetDescription: ''
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                Document Requests &amp; Submissions
            </h1>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Track evidentiary submissions, KYC verifications, and paper filing pickups requested from litigants.
            </p>
        </div>

        <button type="button" @click="openRequestModal = true" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-2 cursor-pointer shadow-xs">
            <span class="material-symbols-outlined text-[17px]">add_task</span>
            <span>Request Document</span>
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="border-b border-[#e5e3dc] flex items-center justify-between gap-4 overflow-x-auto mb-6 text-xs font-medium pb-2">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('document-requests.index') }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                All ({{ $statusCounts['all'] ?? 0 }})
            </a>
            <a href="{{ route('document-requests.index', ['status' => 'pending']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'pending' ? 'bg-[#23493a] text-white' : 'text-[#854d0e] bg-amber-50 hover:bg-amber-100 border border-amber-200' }}">
                Pending Submission ({{ $statusCounts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('document-requests.index', ['status' => 'submitted']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'submitted' ? 'bg-[#23493a] text-white' : 'text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200' }}">
                Submitted / To Review ({{ $statusCounts['submitted'] ?? 0 }})
            </a>
            <a href="{{ route('document-requests.index', ['status' => 'under_review']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'under_review' ? 'bg-[#23493a] text-white' : 'text-purple-700 hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Under Review ({{ $statusCounts['under_review'] ?? 0 }})
            </a>
            <a href="{{ route('document-requests.index', ['status' => 'completed']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'completed' ? 'bg-[#23493a] text-white' : 'text-[#065f46] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Accepted &amp; Vaulted ({{ $statusCounts['completed'] ?? 0 }})
            </a>
            <a href="{{ route('document-requests.index', ['status' => 'rejected']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('status') === 'rejected' ? 'bg-[#23493a] text-white' : 'text-red-700 hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Rejected ({{ $statusCounts['rejected'] ?? 0 }})
            </a>
        </div>

        <form action="{{ route('document-requests.index') }}" method="GET" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}"/>
            @endif
            <div class="relative w-56">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[17px] text-[#8a8a8a]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search request title..."
                       class="w-full h-8 pl-8 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] placeholder-[#8a8a8a] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
            </div>
            @if(request('q'))
                <a href="{{ route('document-requests.index', array_filter(['status' => request('status')])) }}" class="text-xs text-[#8a8a8a] hover:text-[#1a1a1a]">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[11px] text-[#646864] uppercase font-mono tracking-wider">
                        <th class="py-3 px-4 font-semibold">Document Requested</th>
                        <th class="py-3 px-4 font-semibold">Client Dossier</th>
                        <th class="py-3 px-4 font-semibold">Matter / Case</th>
                        <th class="py-3 px-4 font-semibold">Priority &amp; Due Date</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($requests as $req)
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <!-- Title & Category -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col min-w-0 max-w-xs">
                                <span class="font-semibold text-[#1a1a1a] text-[13px] leading-snug">{{ $req->title }}</span>
                                <span class="text-[11px] text-[#8a8a8a] font-mono mt-0.5">{{ $req->category ?? 'General Document' }}</span>
                                @if($req->description)
                                    <p class="text-[11px] text-[#646864] mt-1 line-clamp-1 italic">{{ $req->description }}</p>
                                @endif
                            </div>
                        </td>

                        <!-- Client -->
                        <td class="py-3.5 px-4">
                            @if($req->client)
                                <a href="{{ route('clients.show', $req->client_id) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a]">
                                    {{ $req->client->name }}
                                </a>
                                <span class="text-[10.5px] text-[#8a8a8a] block">{{ ucfirst($req->client->category) }}</span>
                            @else
                                <span class="text-[#8a8a8a] italic">None</span>
                            @endif
                        </td>

                        <!-- Matter -->
                        <td class="py-3.5 px-4">
                            @if($req->matter)
                                <a href="{{ route('matters.show', $req->matter_id) }}" class="font-mono text-[11px] text-[#23493a] hover:underline font-semibold block">
                                    {{ $req->matter->case_number }}
                                </a>
                                <span class="text-[11px] text-[#646864] truncate max-w-[180px] block">{{ $req->matter->title }}</span>
                            @else
                                <span class="text-[#8a8a8a] italic">General Intake</span>
                            @endif
                        </td>

                        <!-- Priority & Due Date -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col text-[11px]">
                                @if(in_array($req->priority, ['urgent', 'high']))
                                    <span class="font-semibold text-red-700 uppercase tracking-wider text-[10px]">{{ ucfirst($req->priority) }}</span>
                                @else
                                    <span class="text-[#8a8a8a] uppercase tracking-wider text-[10px]">{{ ucfirst($req->priority ?? 'normal') }}</span>
                                @endif
                                <span class="font-mono {{ $req->due_date && $req->due_date->lt(now()) && $req->status === 'pending' ? 'text-red-700 font-semibold' : 'text-[#646864]' }}">
                                    Due: {{ $req->due_date ? $req->due_date->format('d M Y') : 'Open' }}
                                </span>
                            </div>
                        </td>

                        <!-- Status badge -->
                        <td class="py-3.5 px-4">
                            @php
                                $statusBadges = [
                                    'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'submitted' => 'bg-blue-50 text-blue-700 border-blue-200 font-semibold',
                                    'under_review' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $bcls = $statusBadges[$req->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border {{ $bcls }}">
                                <span>{{ ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(in_array($req->status, ['submitted', 'under_review']))
                                    <button type="button" @click="selectedRequest = {{ $req->id }}; openReviewModal = true"
                                            class="btn-primary h-7 px-2 text-[11px] inline-flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-[13px]">rate_review</span>
                                        <span>Review</span>
                                    </button>
                                @endif

                                @if($req->status === 'pending')
                                    <button type="button" @click="selectedRequest = {{ $req->id }}; openAssistedModal = true"
                                            class="btn-secondary h-7 px-2 text-[11px] inline-flex items-center gap-1 cursor-pointer" title="Scan & Upload for offline client">
                                        <span class="material-symbols-outlined text-[13px]">scanner</span>
                                        <span>Scan Paper</span>
                                    </button>
                                @endif

                                @if($req->document_id)
                                    <a href="{{ route('documents.download', $req->document_id) }}" class="btn-secondary h-7 px-2 text-[11px] inline-flex items-center gap-1" title="Download Document">
                                        <span class="material-symbols-outlined text-[13px]">download</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#8a8a8a] text-xs">
                            <span class="material-symbols-outlined text-[36px] text-[#c1c8c3] block mb-2">fact_check</span>
                            No document requests found. Click "Request Document" to dispatch a request to a client.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="p-4 border-t border-[#f0eee8] bg-[#faf8f5]">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: REQUEST DOCUMENT -->
    <div x-show="openRequestModal" x-cloak @click.away="openRequestModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <h3 class="text-[16px] font-semibold text-[#1a1a1a]">Request Evidentiary Document / KYC</h3>
                <button type="button" @click="openRequestModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('document-requests.store') }}" method="POST" class="flex flex-col gap-3 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Target Client *</label>
                        <select name="client_id" required x-model="selectedClientId" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="">Select Client...</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Matter / Case *</label>
                        <select name="matter_id" required x-model="selectedMatterId" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="auto_create">Auto-create Intake Dossier</option>
                            @foreach($matters as $mt)
                                <option value="{{ $mt->id }}">Case {{ $mt->case_number }}: {{ $mt->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Document Title *</label>
                    <input name="title" required type="text" placeholder="e.g. Registered Sale Deed dated 2018, Income Tax Returns..."
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Priority</label>
                        <select name="priority" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent (Hearing Imminent)</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Submission Due Date</label>
                        <input name="due_date" type="date" value="{{ now()->addDays(7)->format('Y-m-d') }}"
                               class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Instructions for Client</label>
                    <textarea name="description" rows="2" placeholder="Specify requirements, e.g. all pages scanned in color..."
                              class="p-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openRequestModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Send Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: REVIEW SUBMISSION -->
    <div x-show="openReviewModal" x-cloak @click.away="openReviewModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-md p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Review Client Document Filing</h3>
                <button type="button" @click="openReviewModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'/document-requests/' + selectedRequest + '/review'" method="POST" class="flex flex-col gap-3 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Determination</label>
                    <select name="status" x-model="reviewStatus" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                        <option value="completed">✓ Accept &amp; File in Matter Vault</option>
                        <option value="rejected">✕ Reject &amp; Request Re-submission</option>
                        <option value="under_review">⋯ Mark as Under Review</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1" x-show="reviewStatus === 'rejected'">
                    <label class="font-semibold text-red-700">Reason for Rejection *</label>
                    <textarea name="rejection_reason" rows="3" placeholder="Explain why the document was rejected..."
                              class="p-2.5 rounded-md bg-red-50/50 border border-red-200 text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="flex flex-col gap-1" x-show="reviewStatus === 'completed'">
                    <label class="font-semibold text-[#1a1a1a]">Review Notes (Optional)</label>
                    <textarea name="review_notes" rows="2" placeholder="e.g. Verified original stamp paper..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openReviewModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ASSISTED UPLOAD -->
    <div x-show="openAssistedModal" x-cloak @click.away="openAssistedModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-md p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#92400e]">scanner</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Assisted Physical Intake</h3>
                </div>
                <button type="button" @click="openAssistedModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'/document-requests/' + selectedRequest + '/assisted-upload'" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Select Scanned PDF *</label>
                    <input name="file" required type="file" accept=".pdf,.png,.jpg,.jpeg,.docx"
                           class="file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-[#23493a] file:text-white border border-[#e5e3dc] rounded-md p-1.5 text-xs text-[#1a1a1a]"/>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Clerk Notes</label>
                    <textarea name="clerk_notes" rows="2" placeholder="e.g. Client submitted original in chambers..."
                              class="p-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openAssistedModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Scan &amp; Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
