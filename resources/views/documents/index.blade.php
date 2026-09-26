@extends('layouts.app')

@section('title', 'Document Vault & Evidence Hub — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Document Vault')

@section('content')
<div x-data="{
    openUploadModal: {{ ($errors->any() || session('error')) ? 'true' : 'false' }},
    categoryFilter: 'all',
    selectedFileName: '',
    selectedFileSizeText: '',
    fileSizeError: '',
    openVersionModal: false,
    versionFilterStatus: 'all',
    versionFilterDoc: 'all',
    selectedVersionDocId: '{{ $documents->first()?->id ?? '' }}',
    selectedVersionFileName: '',
    selectedVersionFileSizeText: '',
    versionFileSizeError: ''
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

    <!-- Document Version Control Section -->
    <div class="mt-8 border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden" id="document-version-control">
        <!-- Section Header -->
        <div class="p-4 sm:p-5 border-b border-[#f0eee8] bg-[#faf8f5]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">history_edu</span>
                        <h2 class="text-base sm:text-lg font-semibold text-[#1a1a1a]">Document Version Control</h2>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-medium bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc]">
                            {{ $documentVersions->count() }} {{ Str::plural('revision', $documentVersions->count()) }}
                        </span>
                    </div>
                    <p class="text-xs text-[#646864] mt-1">Audit trail, revision lineage, change descriptions, and lifecycle tracking across legal case files</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Filter by Document -->
                    <select x-model="versionFilterDoc" class="h-8 px-2.5 rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="all">All Documents</option>
                        @foreach($documents as $d)
                            <option value="{{ $d->id }}">{{ $d->document_number ?: ('DOC-' . $d->id) }} &middot; {{ Str::limit($d->filename, 22) }}</option>
                        @endforeach
                    </select>

                    <!-- Filter by Version Status -->
                    <select x-model="versionFilterStatus" class="h-8 px-2.5 rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="all">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Review">Review</option>
                        <option value="Final">Final</option>
                        <option value="Executed">Executed</option>
                    </select>

                    <!-- Upload New Version Button -->
                    <button type="button" @click="selectedVersionDocId = '{{ $documents->first()?->id ?? '' }}'; openVersionModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">upload_file</span>
                        <span>Upload New Version</span>
                    </button>
                </div>
            </div>

            <!-- Typical Sequence Lifecycle Pipeline Banner -->
            <div class="mt-4 pt-3.5 border-t border-[#edebe4] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 font-medium text-[#646864] text-[11.5px]">
                    <span class="material-symbols-outlined text-[#23493a] text-sm">timeline</span>
                    <span>Typical Sequence:</span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[11px] font-mono">
                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft v1
                    </span>
                    <span class="text-[#8a8a8a] text-xs">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft v2
                    </span>
                    <span class="text-[#8a8a8a] text-xs">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-800 border border-purple-200 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Review v3
                    </span>
                    <span class="text-[#8a8a8a] text-xs">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Final
                    </span>
                    <span class="text-[#8a8a8a] text-xs">&rarr;</span>
                    <span class="px-2 py-0.5 rounded bg-teal-50 text-teal-800 border border-teal-300 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span> Executed
                    </span>
                </div>
            </div>
        </div>

        <!-- Version Control Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[13px] whitespace-nowrap">
                <thead>
                    <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider bg-[#faf8f5]">
                        <th class="py-3 px-4 font-medium">Version</th>
                        <th class="py-3 px-4 font-medium">Document ID</th>
                        <th class="py-3 px-4 font-medium">Stored File</th>
                        <th class="py-3 px-4 font-medium">Uploaded By / Date</th>
                        <th class="py-3 px-4 font-medium">Change Description</th>
                        <th class="py-3 px-4 font-medium">Version Status &amp; Relationship</th>
                        <th class="py-3 px-4 font-medium text-center">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($documentVersions as $version)
                    @php
                        $parentDoc = $version->document;
                        $isCurrent = $parentDoc && ((int)$parentDoc->version === (int)$version->version_number);
                        $status = $version->version_status ?: 'Draft';
                        $statusLower = strtolower($status);
                    @endphp
                    <tr x-show="(versionFilterStatus === 'all' || versionFilterStatus.toLowerCase() === '{{ strtolower($status) }}') && (versionFilterDoc === 'all' || versionFilterDoc == '{{ $version->document_id }}')" 
                        class="hover:bg-[#faf9f5] transition-colors group">
                        
                        <!-- Version Number & Sequence Label -->
                        <td class="py-3.5 px-4 font-mono text-xs">
                            <div class="flex items-center gap-1.5">
                                @if($statusLower === 'executed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-teal-100 text-teal-900 border border-teal-300">
                                        <span class="material-symbols-outlined text-[13px]">verified</span>
                                        <span>Executed (v{{ $version->version_number }})</span>
                                    </span>
                                @elseif($statusLower === 'final')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        <span class="material-symbols-outlined text-[13px]">task_alt</span>
                                        <span>Final (v{{ $version->version_number }})</span>
                                    </span>
                                @elseif($statusLower === 'review')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-purple-100 text-purple-900 border border-purple-300">
                                        <span class="material-symbols-outlined text-[13px]">rate_review</span>
                                        <span>Review v{{ $version->version_number }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-amber-100 text-amber-900 border border-amber-300">
                                        <span class="material-symbols-outlined text-[13px]">edit_document</span>
                                        <span>Draft v{{ $version->version_number }}</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Document ID & Matter Reference -->
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-semibold text-[#23493a]">
                                    {{ $parentDoc ? ($parentDoc->document_number ?: ('DOC-' . ($parentDoc->created_at ? $parentDoc->created_at->format('Y') : date('Y')) . '-' . str_pad($parentDoc->id, 4, '0', STR_PAD_LEFT))) : ('DOC-' . $version->document_id) }}
                                </span>
                                @if($parentDoc && $parentDoc->matter)
                                    <a href="{{ route('matters.show', $parentDoc->matter->id) }}" class="text-[11px] text-[#646864] hover:text-[#23493a] hover:underline truncate max-w-[170px]" title="{{ $parentDoc->matter->title }} ({{ $parentDoc->matter->case_number }})">
                                        {{ $parentDoc->matter->case_number ?? $parentDoc->matter->title }}
                                    </a>
                                @endif
                            </div>
                        </td>

                        <!-- Stored File -->
                        <td class="py-3.5 px-4 font-mono text-xs text-[#1a1a1a]">
                            <div class="flex items-center gap-2" title="{{ $version->filename }}">
                                <span class="material-symbols-outlined text-[#8a8a8a] text-base shrink-0">
                                    @if(str_ends_with(strtolower($version->filename), '.pdf'))
                                        picture_as_pdf
                                    @elseif(str_ends_with(strtolower($version->filename), '.doc') || str_ends_with(strtolower($version->filename), '.docx'))
                                        description
                                    @elseif(str_ends_with(strtolower($version->filename), '.png') || str_ends_with(strtolower($version->filename), '.jpg') || str_ends_with(strtolower($version->filename), '.jpeg'))
                                        image
                                    @else
                                        draft
                                    @endif
                                </span>
                                <div class="flex flex-col">
                                    <span class="font-medium text-xs truncate max-w-[180px] text-[#1a1a1a]">{{ $version->filename }}</span>
                                    <span class="text-[10.5px] text-[#8a8a8a] font-normal">{{ $version->formattedSize() }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Uploaded By / Date -->
                        <td class="py-3.5 px-4 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-[#f0eee8] text-[#23493a] flex items-center justify-center font-bold text-[10px] shrink-0 border border-[#e5e3dc]">
                                    {{ $version->uploader ? strtoupper(substr($version->uploader->name, 0, 2)) : 'SC' }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-medium text-[#1a1a1a] text-xs leading-tight">
                                        {{ $version->uploader ? $version->uploader->name : 'Chambers Legal Team' }}
                                    </span>
                                    <span class="text-[10.5px] text-[#8a8a8a] font-mono leading-tight mt-0.5">
                                        {{ $version->created_at ? $version->created_at->format('d M Y, h:i A') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Change Description -->
                        <td class="py-3.5 px-4 text-xs">
                            <div class="max-w-[260px] truncate" title="{{ $version->change_description ?: ($version->change_summary ?: 'No description provided') }}">
                                <span class="text-[#1a1a1a] font-normal">
                                    {{ $version->change_description ?: ($version->change_summary ?: 'Initial repository version') }}
                                </span>
                            </div>
                        </td>

                        <!-- Version Status & Previous/Current Relationship -->
                        <td class="py-3.5 px-4 text-xs">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-1.5">
                                    @if($isCurrent)
                                        <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Current Version</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10.5px] font-medium text-[#646864] bg-[#f5f3ed] px-2 py-0.5 rounded-full border border-[#e5e3dc]">
                                            <span class="material-symbols-outlined text-[12px]">history</span>
                                            <span>Superseded</span>
                                        </span>
                                    @endif

                                    <span class="text-[11px] font-mono text-[#8a8a8a]">
                                        ({{ $status }})
                                    </span>
                                </div>

                                <!-- Relationship Lineage -->
                                <div class="text-[10.5px] text-[#8a8a8a] font-mono flex items-center gap-1">
                                    @if($version->previous_version_id && $version->previousVersion)
                                        <span class="material-symbols-outlined text-xs text-[#23493a]">subdirectory_arrow_right</span>
                                        <span>Supersedes v{{ $version->previousVersion->version_number }} ({{ $version->previousVersion->version_status ?: 'Draft' }})</span>
                                    @elseif($version->version_number > 1)
                                        <span class="material-symbols-outlined text-xs text-[#23493a]">subdirectory_arrow_right</span>
                                        <span>Supersedes v{{ $version->version_number - 1 }}</span>
                                    @else
                                        <span class="material-symbols-outlined text-xs text-[#8a8a8a]">fiber_manual_record</span>
                                        <span>Root Origin (v1)</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Download Version Action -->
                        <td class="py-3.5 px-4 text-center">
                            @if($parentDoc)
                                <a href="{{ route('documents.versions.download', ['document' => $parentDoc->id, 'version' => $version->id]) }}" 
                                   class="p-1.5 text-[#646864] hover:text-[#23493a] hover:bg-[#f5f3ed] rounded transition-colors inline-flex" 
                                   title="Download Stored Version {{ $version->version_number }} File">
                                    <span class="material-symbols-outlined text-lg">download</span>
                                </a>
                            @else
                                <span class="text-[#8a8a8a]">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-xs text-[#8a8a8a]">
                            No document versions recorded yet. Upload a document filing or revision to initiate version control.
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

            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3.5 text-xs max-h-[70vh] overflow-y-auto pr-1" x-data="{
                selectedTags: [],
                tagInput: '',
                toggleTag(tag) {
                    const idx = this.selectedTags.indexOf(tag);
                    if (idx === -1) { this.selectedTags.push(tag); } else { this.selectedTags.splice(idx, 1); }
                },
                get tagsValue() { return this.selectedTags.join(','); }
            }">
                @csrf
                {{-- Hidden field to sync selected tags --}}
                <input type="hidden" name="tags" :value="tagsValue" />

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

                {{-- Row 1: Document Type + Document Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Legal Document Type *</label>
                        <select name="category" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="" disabled selected>Select type…</option>
                            <optgroup label="Court Filings & Pleadings">
                                <option value="Pleading">Pleading</option>
                                <option value="Motion">Motion</option>
                                <option value="Brief">Brief</option>
                                <option value="Order">Order</option>
                                <option value="Judgment">Judgment</option>
                                <option value="Court Filing">Court Filing</option>
                                <option value="Notice">Notice</option>
                            </optgroup>
                            <optgroup label="Contracts & Agreements">
                                <option value="Contract">Contract</option>
                                <option value="Agreement">Agreement</option>
                                <option value="Correspondence">Correspondence</option>
                            </optgroup>
                            <optgroup label="Discovery & Evidence">
                                <option value="Discovery">Discovery</option>
                                <option value="Deposition">Deposition</option>
                                <option value="Evidence">Evidence</option>
                                <option value="Exhibit">Exhibit</option>
                            </optgroup>
                            <optgroup label="Sworn Statements">
                                <option value="Affidavit">Affidavit</option>
                                <option value="Declaration">Declaration</option>
                            </optgroup>
                            <optgroup label="Research & Client Documents">
                                <option value="Legal Research">Legal Research</option>
                                <option value="Memorandum">Memorandum</option>
                                <option value="Client Document">Client Document</option>
                            </optgroup>
                            <optgroup label="Other Records">
                                <option value="Financial Document">Financial Document</option>
                                <option value="Medical Record">Medical Record</option>
                                <option value="Photograph">Photograph</option>
                                <option value="Other">Other</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Document Status</label>
                        <select name="document_status" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="draft">Draft</option>
                            <option value="final" selected>Final</option>
                            <option value="in_review">In Review</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: Visibility + Classification --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Visibility</label>
                        <select name="visibility" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="internal_only" selected>Internal Only</option>
                            <option value="attorney_only">Attorney Only</option>
                            <option value="legal_team">Legal Team</option>
                            <option value="client_visible">Client Visible</option>
                            <option value="specific_users">Specific Users</option>
                            <option value="specific_client">Specific Client</option>
                            <option value="restricted">Restricted</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Classification</label>
                        <select name="classification" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="public">Public</option>
                            <option value="internal">Internal</option>
                            <option value="confidential" selected>Confidential</option>
                            <option value="highly_confidential">Highly Confidential</option>
                            <option value="attorney_client_privileged">Attorney-Client Privileged</option>
                            <option value="attorney_work_product">Attorney Work Product</option>
                        </select>
                        <input type="hidden" name="privilege" value="Confidential"/>
                    </div>
                </div>

                {{-- Document Tags Section --}}
                <div class="flex flex-col gap-1.5">
                    <label class="font-semibold text-[#1a1a1a]">Document Tags</label>
                    <div class="flex flex-wrap gap-1.5">
                        @php
                            $tagPresets = [
                                'confidential', 'privileged', 'work-product', 'discovery', 'evidence', 'court-filing',
                                'client-provided', 'opposing-counsel', 'draft', 'final', 'urgent', 'hearing',
                                'settlement', 'contract', 'deposition', 'review-required', 'follow-up'
                            ];
                        @endphp
                        @foreach($tagPresets as $preset)
                        <button type="button"
                            @click="toggleTag('{{ $preset }}')"
                            :class="selectedTags.includes('{{ $preset }}') ? 'bg-[#23493a] text-white border-[#23493a]' : 'bg-white text-[#334155] border-[#cbd5e1] hover:border-[#23493a] hover:text-[#23493a]'"
                            class="px-2 py-1 rounded text-[10.5px] font-medium border transition-all duration-150 cursor-pointer inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[11px]" x-text="selectedTags.includes('{{ $preset }}') ? 'check_circle' : 'add_circle_outline'"></span>
                            #{{ $preset }}
                        </button>
                        @endforeach
                    </div>
                    {{-- Selected tags summary --}}
                    <template x-if="selectedTags.length > 0">
                        <div class="flex items-center gap-1.5 text-[10.5px] text-[#23493a] font-medium mt-0.5">
                            <span class="material-symbols-outlined text-xs">label</span>
                            <span x-text="selectedTags.length + ' tag' + (selectedTags.length > 1 ? 's' : '') + ' selected'"></span>
                        </div>
                    </template>
                </div>

                {{-- Client visibility checkbox --}}
                <div class="p-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex items-start gap-2.5">
                    <input type="checkbox" name="is_client_visible" id="is_client_visible" value="1" checked class="mt-0.5 rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="is_client_visible" class="text-xs text-[#1a1a1a] font-medium flex flex-col cursor-pointer">
                        <span class="font-semibold text-[#1a1a1a]">Share with Client in Portal</span>
                        <span class="text-[11px] text-[#8a8a8a] font-normal">When checked, the client associated with this case can view and download this filing. Uncheck for internal chambers work product or confidential strategy memos.</span>
                    </label>
                </div>

                {{-- Classification disclaimer --}}
                <div class="p-2 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10.5px] flex items-start gap-1.5">
                    <span class="material-symbols-outlined text-sm mt-0.5 shrink-0">info</span>
                    <span>Classification labels are administrative markers and should <strong>not</strong> be treated as a legal determination merely because a user selected a label.</span>
                </div>

                {{-- File Selection + Capture --}}
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-[#1a1a1a]">Document File (PDF, DOCX, TXT, Images) *</label>
                        <span class="text-[10px] text-[#8a8a8a] font-mono">Max 50 MB</span>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Select Document File button --}}
                        <label class="flex-1 cursor-pointer">
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
                                class="hidden" id="upload-file-input"/>
                            <span class="h-10 px-4 rounded-md bg-[#f5f3ed] text-[#23493a] hover:bg-[#eae8e2] border border-[#e5e3dc] font-semibold text-xs inline-flex items-center gap-2 transition-colors w-full justify-center cursor-pointer">
                                <span class="material-symbols-outlined text-base">folder_open</span>
                                <span>Select Document File</span>
                            </span>
                        </label>
                        {{-- Capture button (camera) --}}
                        <label class="shrink-0 cursor-pointer">
                            <input type="file" accept="image/*" capture="environment"
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
                                        document.getElementById('upload-file-input').files = $event.target.files;
                                    }
                                "
                                class="hidden"/>
                            <span class="h-10 px-3.5 rounded-md bg-[#23493a] text-white hover:bg-[#1a3a2e] font-semibold text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">photo_camera</span>
                                <span>Capture</span>
                            </span>
                        </label>
                    </div>
                    
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

    <!-- Upload New Version Modal -->
    <div x-show="openVersionModal" 
         x-cloak 
         @click.away="openVersionModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">history_edu</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Upload New Document Version</h3>
                </div>
                <button type="button" @click="openVersionModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('documents.versions.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3.5 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Target Document Dossier *</label>
                    <select name="document_id" x-model="selectedVersionDocId" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($documents as $d)
                        <option value="{{ $d->id }}">
                            {{ $d->document_number ?: ('DOC-' . $d->id) }} &middot; {{ $d->filename }} (Current: v{{ $d->version ?? 1 }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">New Version Status *</label>
                    <select name="version_status" required class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="Draft">Draft (e.g. Draft v2, Draft v3)</option>
                        <option value="Review">Review (e.g. Review v3 - Pre-filing)</option>
                        <option value="Final">Final (Final court e-filing version)</option>
                        <option value="Executed">Executed (Executed agreement / attested affidavits)</option>
                    </select>
                    <p class="text-[11px] text-[#8a8a8a] mt-0.5">Typical lifecycle sequence: Draft v1 &rarr; Draft v2 &rarr; Review v3 &rarr; Final &rarr; Executed</p>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Change Description / Revision Notes *</label>
                    <textarea name="change_description" rows="3" required placeholder="Explain changes in this version (e.g. incorporated client feedback, amended jurisdiction clause 14, signed and executed affidavits)..." class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-[#1a1a1a]">Revised Stored File (PDF, DOCX, TXT, Images) *</label>
                        <span class="text-[10px] text-[#8a8a8a] font-mono">Max 50 MB</span>
                    </div>
                    <input name="file" required type="file" 
                        accept=".pdf,.docx,.doc,.txt,.tiff,.png,.jpg,.jpeg"
                        @change="
                            const f = $event.target.files[0];
                            if (f) {
                                selectedVersionFileName = f.name;
                                const mb = (f.size / (1024 * 1024)).toFixed(2);
                                selectedVersionFileSizeText = mb + ' MB';
                                if (f.size > 52428800) {
                                    versionFileSizeError = 'File size (' + mb + ' MB) exceeds the maximum allowed 50 MB limit.';
                                } else {
                                    versionFileSizeError = '';
                                }
                            } else {
                                selectedVersionFileName = '';
                                selectedVersionFileSizeText = '';
                                versionFileSizeError = '';
                            }
                        "
                        class="h-10 text-xs text-[#1a1a1a] file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#f5f3ed] file:text-[#23493a] hover:file:bg-[#eae8e2] cursor-pointer"/>
                    
                    <template x-if="selectedVersionFileName">
                        <div class="flex items-center justify-between text-[11px] p-2 mt-1 rounded bg-[#faf8f5] border border-[#e5e3dc]">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="material-symbols-outlined text-sm text-[#23493a]">attach_file</span>
                                <span class="font-mono truncate" x-text="selectedVersionFileName"></span>
                            </div>
                            <span class="font-mono text-[#8a8a8a] shrink-0 ml-2" x-text="selectedVersionFileSizeText"></span>
                        </div>
                    </template>
                    <template x-if="versionFileSizeError">
                        <div class="text-[11px] text-red-600 flex items-center gap-1 font-medium mt-1">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            <span x-text="versionFileSizeError"></span>
                        </div>
                    </template>
                </div>

                <div class="p-2.5 rounded-md bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0] text-[11px] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">history</span>
                    <span>New version will establish revision lineage and preserve previous versions without overwriting.</span>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openVersionModal = false" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" :disabled="versionFileSizeError !== ''" class="btn-primary h-9 px-4 text-xs disabled:opacity-50 disabled:cursor-not-allowed">Record &amp; Upload Version</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
