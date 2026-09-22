@extends('portal.layout')

@section('title', 'Document Requests')
@section('header_title', 'Document Requests')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" 
     x-data="{ 
         uploadModalOpen: false, 
         selectedRequestId: null, 
         selectedRequestTitle: '', 
         selectedMatterTitle: '', 
         isResubmission: false 
     }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Document Requests</h1>
            <p class="text-[13px] text-[#646864] mt-1">Specific documents, contracts, and filings requested by your legal team for active court proceedings.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            @php $pendingCount = $requests->whereIn('status', ['pending', 'rejected'])->count(); @endphp
            <span class="px-3 py-1.5 {{ $pendingCount > 0 ? 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca]' : 'bg-[#ecfdf5] text-[#065f46] border-[#a7f3d0]' }} border rounded-md font-mono font-medium shadow-xs">
                {{ $pendingCount }} Action Required
            </span>
        </div>
    </div>

    <!-- Status Filters -->
    @php
        $activeTab = request('status', 'all');
    @endphp
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] pb-2 text-xs overflow-x-auto">
        <a href="{{ route('portal.requests.index') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $activeTab === 'all' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            All Requests ({{ $requests->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $activeTab === 'pending' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Pending ({{ $requests->where('status', 'pending')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $activeTab === 'rejected' ? 'bg-[#ba1a1a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Re-submission Needed ({{ $requests->where('status', 'rejected')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'submitted']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $activeTab === 'submitted' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Under Review ({{ $requests->where('status', 'submitted')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $activeTab === 'completed' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Accepted &amp; Completed ({{ $requests->where('status', 'completed')->count() }})
        </a>
    </div>

    <!-- Requests Table / List -->
    <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf9f5] border-b border-[#e5e3dc] text-[#8a8a8a] font-mono text-[10.5px] uppercase">
                        <th class="py-3.5 px-4 font-medium">Document Title &amp; Scope</th>
                        <th class="py-3.5 px-4 font-medium">Associated Case</th>
                        <th class="py-3.5 px-4 font-medium">Category</th>
                        <th class="py-3.5 px-4 font-medium">Due Date</th>
                        <th class="py-3.5 px-4 font-medium">Status</th>
                        <th class="py-3.5 px-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @php
                        $filtered = $requests;
                        if ($activeTab !== 'all') {
                            $filtered = $requests->where('status', $activeTab);
                        }
                    @endphp
                    @forelse($filtered as $req)
                    <tr class="hover:bg-[#faf9f5]/70 transition-colors">
                        <td class="py-4 px-4 max-w-sm">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-[#1a1a1a] text-sm">{{ $req->title }}</span>
                                    @if($req->priority === 'urgent')
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium bg-[#fef2f2] text-[#991b1b] border border-[#fecaca] uppercase">Urgent</span>
                                    @elseif($req->priority === 'high')
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium bg-[#fefce8] text-[#854d0e] border border-[#fef08a] uppercase">High</span>
                                    @endif
                                </div>
                                <p class="text-[11.5px] text-[#646864] leading-relaxed">{{ $req->description }}</p>

                                @if($req->status === 'rejected' && ($req->rejection_reason || $req->review_notes))
                                <div class="p-2.5 rounded bg-[#fef2f2] border border-[#fecaca] mt-1.5 text-[#991b1b] text-[11.5px]">
                                    <strong class="block text-[10px] uppercase font-mono text-[#7f1d1d]">Advocate's Remarks / Correction Needed:</strong>
                                    {{ $req->rejection_reason ?: $req->review_notes }}
                                </div>
                                @endif

                                @if($req->client_notes)
                                <p class="text-[10.5px] font-mono text-[#23493a] bg-[#faf9f5] border border-[#e5e3dc] p-1.5 rounded mt-0.5">
                                    Your Submission: {{ $req->client_notes }}
                                </p>
                                @endif
                            </div>
                        </td>

                        <td class="py-4 px-4 text-[#646864]">
                            <div class="flex flex-col">
                                <span class="font-medium text-[#1a1a1a] truncate max-w-[200px]">{{ $req->matter->title ?? 'General Matter' }}</span>
                                <span class="font-mono text-[10.5px] text-[#8a8a8a]">{{ $req->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[#1a1a1a] font-mono text-[10.5px]">
                                {{ $req->category->name ?? 'Court Pleadings' }}
                            </span>
                        </td>

                        <td class="py-4 px-4 font-mono text-[11.5px] text-[#646864]">
                            @if($req->due_date)
                            <span class="{{ \Carbon\Carbon::parse($req->due_date)->isPast() && in_array($req->status, ['pending', 'rejected']) ? 'text-red-600 font-bold' : '' }}">
                                {{ \Carbon\Carbon::parse($req->due_date)->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-[#8a8a8a]">Before Hearing</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($req->status === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#fefce8] text-[#854d0e] border border-[#fef08a]">
                                Pending Action
                            </span>
                            @elseif($req->status === 'rejected')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">
                                Re-upload Needed
                            </span>
                            @elseif($req->status === 'submitted')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#f0f9ff] text-[#0369a1] border border-[#bae6fd]">
                                Under Review
                            </span>
                            @elseif($req->status === 'completed')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                Accepted &amp; Filed
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if(in_array($req->status, ['pending', 'rejected']))
                            <button type="button" 
                                    @click="
                                        selectedRequestId = {{ $req->id }};
                                        selectedRequestTitle = '{{ addslashes($req->title) }}';
                                        selectedMatterTitle = '{{ addslashes($req->matter->title ?? '') }}';
                                        isResubmission = {{ $req->status === 'rejected' ? 'true' : 'false' }};
                                        uploadModalOpen = true;
                                    "
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] transition-colors shadow-xs cursor-pointer">
                                <span class="material-symbols-outlined text-sm">upload_file</span>
                                <span>{{ $req->status === 'rejected' ? 'Re-upload' : 'Upload' }}</span>
                            </button>
                            @else
                            <span class="text-xs text-[#8a8a8a] flex items-center justify-end gap-1 font-medium">
                                <span class="material-symbols-outlined text-sm text-[#065f46]">done_all</span>
                                <span>Received</span>
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-xs text-[#8a8a8a]">
                            No document requests found matching this status filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div x-show="uploadModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="uploadModalOpen = false"
             class="bg-white border border-[#e5e3dc] rounded-lg max-w-lg w-full p-6 shadow-xl">
            
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-md bg-[#23493a]/10 text-[#23493a] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">upload_file</span>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-semibold text-[#1a1a1a]" x-text="isResubmission ? 'Submit Corrected Document' : 'Submit Requested Document'"></h3>
                        <p class="text-[11.5px] text-[#8a8a8a]" x-text="selectedRequestTitle"></p>
                    </div>
                </div>
                <button type="button" @click="uploadModalOpen = false" class="text-[#8e8e8e] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'{{ url('/portal/requests') }}/' + selectedRequestId + '/upload'" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="mt-4 flex flex-col gap-4">
                @csrf

                <!-- Dropzone Box -->
                <div class="border-2 border-dashed border-[#e5e3dc] hover:border-[#23493a] rounded-md p-6 text-center transition-colors bg-[#faf9f5]">
                    <span class="material-symbols-outlined text-3xl text-[#23493a] mb-2">drive_folder_upload</span>
                    <label class="block text-xs font-semibold text-[#1a1a1a] cursor-pointer">
                        <span>Click to browse file</span>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="sr-only" onchange="document.getElementById('filename-display').innerText = this.files[0]?.name || '';"/>
                    </label>
                    <p class="text-[11px] text-[#8a8a8a] mt-1">PDF, DOCX, JPG, PNG up to 50MB</p>
                    <p id="filename-display" class="text-xs font-mono font-bold text-[#23493a] mt-2"></p>
                </div>

                <!-- Client Remarks / Notes -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1a1a]">Client Remarks (Optional)</label>
                    <textarea name="client_notes" rows="2" placeholder="e.g. Certified registered copy attached; original in safe custody..." class="w-full p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] resize-none"></textarea>
                </div>

                <!-- Statutory Notice -->
                <div class="p-2.5 rounded bg-[#faf9f5] border border-[#e5e3dc] flex items-center gap-2 text-[11px] text-[#646864]">
                    <span class="material-symbols-outlined text-base text-[#23493a]">shield</span>
                    <span>Document integrity verified with SHA-256 evidentiary hash upon upload.</span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="uploadModalOpen = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">send</span>
                        <span>Submit to Counsel</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
