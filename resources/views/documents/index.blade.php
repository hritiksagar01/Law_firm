@extends('layouts.app')

@section('title', 'Document Vault & Evidence Hub — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Document Vault')

@section('content')
<div x-data="{
    openUploadModal: {{ ($errors->any() || session('error')) ? 'true' : 'false' }},
    categoryFilter: 'all',
    selectedFileName: '',
    selectedFileSizeText: '',
    fileSizeError: ''
}}" class="flex flex-col w-full text-[#1a1a1a]">
    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Document Vault &amp; Evidence Hub</h1>
            <p class="text-[13px] text-[#646864] mt-1">SHA-256 authenticated filings, exhibits, depositions, and executed agreements</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openUploadModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">upload</span>
                <span>Upload New Filing</span>
            </button>
        </div>
    </div>

    <!-- Drag & Drop Upload Zone -->
    <div @click="openUploadModal = true" class="p-6 mb-6 rounded-md border-2 border-dashed border-[#e5e3dc] bg-white hover:border-[#23493a] transition-colors flex flex-col items-center justify-center text-center cursor-pointer group shadow-xs">
        <div class="w-10 h-10 rounded-full bg-[#f5f3ed] group-hover:bg-[#ecfdf5] flex items-center justify-center text-[#23493a] mb-2.5 transition-colors">
            <span class="material-symbols-outlined text-2xl">cloud_upload</span>
        </div>
        <h3 class="text-sm font-semibold text-[#1a1a1a]">Drag and drop legal filings or exhibit archives here</h3>
        <p class="text-xs text-[#8a8a8a] mt-0.5">Supports PDF, DOCX, TIFF, Bates-stamped bundles up to 50 MB (SHA-256 Hash Authenticated)</p>
        <span class="mt-3 inline-flex items-center px-3 py-1 rounded text-xs font-medium bg-[#f5f3ed] text-[#1a1a1a] group-hover:bg-[#23493a] group-hover:text-white transition-colors">Select Document File</span>
    </div>

    <!-- Vault Files Table -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#f0eee8] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#faf8f5]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-xl">folder_special</span>
                <h3 class="text-sm font-semibold text-[#1a1a1a]">Verified Document Repository</h3>
            </div>
            <div class="flex items-center gap-1 bg-white p-1 rounded-md border border-[#e5e3dc] text-xs">
                <button @click="categoryFilter = 'all'" :class="categoryFilter === 'all' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">All</button>
                <button @click="categoryFilter = 'Pleading'" :class="categoryFilter === 'Pleading' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Pleadings</button>
                <button @click="categoryFilter = 'Motion'" :class="categoryFilter === 'Motion' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Motions</button>
                <button @click="categoryFilter = 'Exhibit'" :class="categoryFilter === 'Exhibit' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Exhibits</button>
                <button @click="categoryFilter = 'Deposition'" :class="categoryFilter === 'Deposition' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:text-[#1a1a1a]'" class="px-2.5 py-1 rounded transition-colors">Depositions</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[13px] whitespace-nowrap">
                <thead>
                    <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider bg-[#faf8f5]">
                        <th class="py-3 px-4 font-medium">Doc ID</th>
                        <th class="py-3 px-4 font-medium">File Name</th>
                        <th class="py-3 px-4 font-medium">Type</th>
                        <th class="py-3 px-4 font-medium">Matter</th>
                        <th class="py-3 px-4 font-medium">Client</th>
                        <th class="py-3 px-4 font-medium">Tag</th>
                        <th class="py-3 px-4 font-medium">Confidentiality</th>
                        <th class="py-3 px-4 font-medium">Visibility</th>
                        <th class="py-3 px-4 font-medium">Doc Status</th>
                        <th class="py-3 px-4 font-medium text-center">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($documents as $doc)
                    <tr x-show="categoryFilter === 'all' || categoryFilter === '{{ $doc->category }}' || categoryFilter === '{{ $doc->document_type }}'" class="hover:bg-[#faf9f5] transition-colors group">
                        <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#23493a]">
                            {{ $doc->document_number ?: ('DOC-' . ($doc->created_at ? $doc->created_at->format('Y') : date('Y')) . '-' . str_pad($doc->id, 4, '0', STR_PAD_LEFT)) }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-[#1a1a1a]">
                            <div class="flex items-center gap-1.5" title="{{ $doc->filename }}">
                                <span class="material-symbols-outlined text-[#8a8a8a] text-base shrink-0">
                                    @if(str_ends_with(strtolower($doc->filename), '.pdf'))
                                        picture_as_pdf
                                    @elseif(str_ends_with(strtolower($doc->filename), '.doc') || str_ends_with(strtolower($doc->filename), '.docx'))
                                        description
                                    @elseif(str_ends_with(strtolower($doc->filename), '.png') || str_ends_with(strtolower($doc->filename), '.jpg') || str_ends_with(strtolower($doc->filename), '.jpeg'))
                                        image
                                    @else
                                        draft
                                    @endif
                                </span>
                                <span class="truncate max-w-[150px] font-medium">{{ $doc->filename }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]">
                                {{ $doc->document_type ?: ($doc->category ?: 'Pleading') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs">
                            @if($doc->matter)
                                <a href="{{ route('matters.show', $doc->matter->id) }}" class="font-medium text-[#23493a] hover:underline block truncate max-w-[160px]" title="{{ $doc->matter->title }} ({{ $doc->matter->case_number }})">
                                    {{ $doc->matter->case_number ?? $doc->matter->title }}
                                </a>
                            @else
                                <span class="text-[#8a8a8a]">General</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-xs">
                            @php
                                $docClient = $doc->client ?? ($doc->matter->client ?? null);
                            @endphp
                            @if($docClient)
                                <a href="{{ route('clients.show', $docClient->id) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline block truncate max-w-[150px]" title="{{ $docClient->name }}">
                                    {{ $docClient->name }}
                                </a>
                            @else
                                <span class="text-[#8a8a8a]">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $rawTags = $doc->tags;
                                $docTags = is_array($rawTags) ? $rawTags : ($rawTags ? (json_decode($rawTags, true) ?: []) : []);
                            @endphp
                            @if(!empty($docTags) && count($docTags) > 0)
                                <div class="flex flex-wrap gap-1 max-w-[150px]">
                                    @foreach(array_slice($docTags, 0, 2) as $t)
                                        <span class="px-1.5 py-0.5 rounded text-[10.5px] font-medium bg-[#eef2f6] text-[#334155] border border-[#cbd5e1]">
                                            #{{ $t }}
                                        </span>
                                    @endforeach
                                    @if(count($docTags) > 2)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] text-[#64748b] bg-gray-100" title="{{ implode(', ', array_slice($docTags, 2)) }}">
                                            +{{ count($docTags) - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[10.5px] text-[#8a8a8a] bg-[#faf8f5] border border-[#f0eee8]">
                                    #docket
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $classification = $doc->classification ?? (strtolower($doc->privilege ?? '') === 'public filing' ? 'public' : 'confidential');
                            @endphp
                            @if(str_contains(strtolower($classification), 'public'))
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">
                                    <span>Public</span>
                                </span>
                            @elseif(str_contains(strtolower($classification), 'privileged') || str_contains(strtolower($classification), 'attorney'))
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 font-medium">
                                    <span>Privileged</span>
                                </span>
                            @elseif(str_contains(strtolower($classification), 'highly'))
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-medium">
                                    <span>Highly Conf.</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-medium">
                                    <span>Confidential</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($doc->is_client_visible || $doc->visibility === 'client_visible')
                                <span class="inline-flex items-center gap-1 font-mono text-[10px] px-2 py-0.5 rounded-full bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0] font-medium">
                                    <span class="material-symbols-outlined text-xs">visibility</span>
                                    <span>Client Visible</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-mono text-[10px] px-2 py-0.5 rounded-full bg-[#fbf3db] text-[#634812] border border-[#f0dfaa] font-medium">
                                    <span class="material-symbols-outlined text-xs">lock</span>
                                    <span>Chambers Only</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $status = strtolower($doc->document_status ?? 'final');
                            @endphp
                            @if($status === 'final' || $status === 'approved')
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Final</span>
                                </span>
                            @elseif($status === 'archived')
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded bg-gray-100 text-gray-700 border border-gray-200 font-medium">
                                    <span>Archived</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-mono text-[10.5px] px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Draft</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#646864] hover:text-[#23493a] hover:bg-[#f5f3ed] rounded transition-colors inline-flex" title="Download Verified File">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-xs text-[#8a8a8a]">
                            No documents stored in the vault yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Document Upload Modal -->
    <div x-show="openUploadModal" 
         x-cloak 
         @click.away="openUploadModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">upload_file</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">File Legal Filing into Vault</h3>
                </div>
                <button type="button" @click="openUploadModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            @if($errors->any())
            <div class="mb-3 p-3 rounded-md bg-red-50 border border-red-200 text-xs text-red-800">
                <div class="flex items-center gap-1.5 font-semibold mb-1">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span>Upload Issue:</span>
                </div>
                <ul class="list-disc list-inside text-[11px] space-y-0.5">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @elseif(session('error'))
            <div class="mb-3 p-3 rounded-md bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3.5 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Matter Dossier *</label>
                    <select name="matter_id" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($matters as $matter)
                        <option value="{{ $matter->id }}">{{ $matter->case_number }} &middot; {{ $matter->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Filing / Exhibit Title *</label>
                    <input name="title" required type="text" placeholder="e.g. Plaintiff's Response to Defendant's Motion to Dismiss" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Filing Category / Type</label>
                        <select name="category" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="Pleading" selected>Pleading / Complaint</option>
                            <option value="Motion">Motion / Memorandum</option>
                            <option value="Brief">Appellate Brief</option>
                            <option value="Exhibit">Exhibit / Evidentiary Artifact</option>
                            <option value="Deposition">Deposition Transcript</option>
                            <option value="Contract">Executed Contract</option>
                            <option value="Court Order">Court Order</option>
                            <option value="Notice">Legal Notice / Summons</option>
                            <option value="Affidavit">Affidavit</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Confidentiality Level</label>
                        <select name="classification" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="confidential" selected>Confidential</option>
                            <option value="public">Public Court Filing</option>
                            <option value="attorney_client_privileged">Attorney-Client Privileged</option>
                            <option value="highly_confidential">Highly Confidential</option>
                            <option value="internal">Internal Chambers</option>
                        </select>
                        <input type="hidden" name="privilege" value="Confidential"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Tags (Comma-Separated)</label>
                        <input name="tags" type="text" placeholder="e.g. pleading, urgent, injunction" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Document Status</label>
                        <select name="document_status" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="final" selected>Final</option>
                            <option value="draft">Draft</option>
                            <option value="in_review">In Review</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex items-start gap-2.5">
                    <input type="checkbox" name="is_client_visible" id="is_client_visible" value="1" checked class="mt-0.5 rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="is_client_visible" class="text-xs text-[#1a1a1a] font-medium flex flex-col cursor-pointer">
                        <span class="font-semibold text-[#1a1a1a]">Share with Client in Portal</span>
                        <span class="text-[11px] text-[#8a8a8a] font-normal">When checked, the client associated with this case can view and download this filing. Uncheck for internal chambers work product or confidential strategy memos.</span>
                    </label>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-[#1a1a1a]">Document File (PDF, DOCX, TXT, Images) *</label>
                        <span class="text-[10px] text-[#8a8a8a] font-mono">Max 50 MB</span>
                    </div>
                    <input name="file" required type="file" 
                        accept=".pdf,.docx,.doc,.txt,.tiff,.png,.jpg,.jpeg"
                        @change="
                            const f = $event.target.files[0];
                            if (f) {
                                selectedFileName = f.name;
                                const mb = (f.size / (1024 * 1024)).toFixed(2);
                                selectedFileSizeText = mb + ' MB';
                                if (f.size > 52428800) {
                                    fileSizeError = 'File size (' + mb + ' MB) exceeds the maximum allowed 50 MB limit.';
                                } else {
                                    fileSizeError = '';
                                }
                            } else {
                                selectedFileName = '';
                                selectedFileSizeText = '';
                                fileSizeError = '';
                            }
                        "
                        class="h-10 text-xs text-[#1a1a1a] file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#f5f3ed] file:text-[#23493a] hover:file:bg-[#eae8e2] cursor-pointer"/>
                    
                    <template x-if="selectedFileName">
                        <div class="flex items-center justify-between text-[11px] p-2 mt-1 rounded bg-[#faf8f5] border border-[#e5e3dc]">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="material-symbols-outlined text-sm text-[#23493a]">attach_file</span>
                                <span class="font-mono truncate" x-text="selectedFileName"></span>
                            </div>
                            <span class="font-mono text-[#8a8a8a] shrink-0 ml-2" x-text="selectedFileSizeText"></span>
                        </div>
                    </template>
                    <template x-if="fileSizeError">
                        <div class="text-[11px] text-red-600 flex items-center gap-1 font-medium mt-1">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            <span x-text="fileSizeError"></span>
                        </div>
                    </template>
                </div>

                <div class="p-2.5 rounded-md bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0] text-[11px] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">enhanced_encryption</span>
                    <span>File will be hashed with SHA-256 for chain of custody court admissibility.</span>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openUploadModal = false" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" :disabled="fileSizeError !== ''" class="btn-primary h-9 px-4 text-xs disabled:opacity-50 disabled:cursor-not-allowed">Authenticate &amp; Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
