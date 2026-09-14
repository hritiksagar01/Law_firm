@extends('portal.layout')

@section('title', 'Document Requests')

@section('content')
<div class="flex flex-col gap-6" x-data="{ uploadModalOpen: false, selectedRequestId: null, selectedRequestTitle: '', selectedMatterTitle: '' }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Document Requests</h1>
            <p class="text-xs text-[#727973] mt-1">Specific documents, contracts, and filings requested by your legal team for active proceedings.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg font-mono font-medium">
                {{ $requests->where('status', 'pending')->count() }} Pending Upload
            </span>
        </div>
    </div>

    <!-- Status Filters -->
    @php
        $activeTab = request('status', 'all');
    @endphp
    <div class="flex items-center gap-2 border-b border-[#e9e8e5] pb-2 text-xs overflow-x-auto">
        <a href="{{ route('portal.requests.index') }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'all' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            All Requests ({{ $requests->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'pending' ? 'bg-amber-700 text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Pending ({{ $requests->where('status', 'pending')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'submitted']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'submitted' ? 'bg-blue-700 text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Submitted ({{ $requests->where('status', 'submitted')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $activeTab === 'completed' ? 'bg-emerald-700 text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Completed ({{ $requests->where('status', 'completed')->count() }})
        </a>
    </div>

    <!-- Requests Table / List -->
    <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf9f6] border-b border-[#e9e8e5] text-[#727973] font-mono text-[10px] uppercase">
                        <th class="py-3.5 px-4 font-semibold">Document Title &amp; Scope</th>
                        <th class="py-3.5 px-4 font-semibold">Associated Case</th>
                        <th class="py-3.5 px-4 font-semibold">Category</th>
                        <th class="py-3.5 px-4 font-semibold">Due Date</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f4f3f1]">
                    @php
                        $filtered = $requests;
                        if ($activeTab !== 'all') {
                            $filtered = $requests->where('status', $activeTab);
                        }
                    @endphp
                    @forelse($filtered as $req)
                    <tr class="hover:bg-[#faf9f6]/60 transition-colors">
                        <td class="py-4 px-4 max-w-sm">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#1a1c1a] text-sm">{{ $req->title }}</span>
                                    @if($req->priority === 'urgent')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-red-100 text-red-800 uppercase">Urgent</span>
                                    @elseif($req->priority === 'high')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-amber-100 text-amber-800 uppercase">High</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-[#727973] leading-relaxed">{{ $req->description }}</p>
                                @if($req->client_notes)
                                <p class="text-[10px] font-mono text-[#1a3c2a] bg-[#c5ecd2]/30 p-1 rounded mt-0.5">
                                    Your Submission: {{ $req->client_notes }}
                                </p>
                                @endif
                            </div>
                        </td>

                        <td class="py-4 px-4 text-[#424843]">
                            <div class="flex flex-col">
                                <span class="font-semibold text-[#1a1c1a] truncate max-w-[200px]">{{ $req->matter->title ?? 'General Matter' }}</span>
                                <span class="font-mono text-[10px] text-[#727973]">{{ $req->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-[#727973]">
                            <span class="px-2 py-0.5 rounded bg-[#f4f3f1] font-mono text-[10px] text-[#424843]">
                                {{ $req->category }}
                            </span>
                        </td>

                        <td class="py-4 px-4 font-mono text-[11px]">
                            @if($req->due_date)
                            <span class="{{ \Carbon\Carbon::parse($req->due_date)->isPast() && $req->status === 'pending' ? 'text-red-600 font-bold' : 'text-[#727973]' }}">
                                {{ \Carbon\Carbon::parse($req->due_date)->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-[#c1c8c1]">—</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($req->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Pending Upload</span>
                            </span>
                            @elseif($req->status === 'submitted')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                <span>Submitted</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="material-symbols-outlined text-xs text-emerald-600">check</span>
                                <span>Verified &amp; Completed</span>
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($req->status === 'pending')
                            <button type="button" @click="selectedRequestId = {{ $req->id }}; selectedRequestTitle = '{{ addslashes($req->title) }}'; selectedMatterTitle = '{{ addslashes($req->matter->title ?? '') }}'; uploadModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-sm">upload_file</span>
                                <span>Upload &amp; Fulfill</span>
                            </button>
                            @else
                            <span class="text-xs text-[#727973] font-mono">
                                {{ $req->submitted_at ? \Carbon\Carbon::parse($req->submitted_at)->format('d M, h:i A') : 'Completed' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#727973]">
                            <span class="material-symbols-outlined text-3xl mb-2 text-[#c1c8c1]">done_all</span>
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
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#e9e8e5] flex flex-col gap-5 relative">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-[#1a3c2a] font-semibold">Fulfill Document Request</span>
                    <h3 class="text-base font-bold text-[#1a1c1a] mt-0.5" x-text="selectedRequestTitle">Upload Document</h3>
                    <p class="text-xs text-[#727973]" x-text="selectedMatterTitle"></p>
                </div>
                <button type="button" @click="uploadModalOpen = false" class="text-[#727973] hover:text-[#1a1c1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'/portal/requests/' + selectedRequestId + '/upload'" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Select Document File (PDF, DOCX, Images)</label>
                    <input type="file" name="file" required class="w-full text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1a3c2a] file:text-white hover:file:bg-[#022616] file:cursor-pointer p-2 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1]"/>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Client Submission Notes (Optional)</label>
                    <textarea name="client_notes" rows="3" placeholder="Add any details, date of execution, or remarks for counsel..." class="w-full p-3 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a]"></textarea>
                </div>

                <div class="p-3 bg-[#f0f9f4] rounded-lg border border-[#c5ecd2] flex items-center gap-2 text-[11px] text-[#2c4e3a]">
                    <span class="material-symbols-outlined text-base text-[#1a3c2a] shrink-0">lock</span>
                    <span>Document will be authenticated and securely transmitted to your counsel's case file.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f4f3f1]">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-[#727973] hover:bg-[#f4f3f1]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] shadow-sm">
                        Submit Document to Legal Team
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
