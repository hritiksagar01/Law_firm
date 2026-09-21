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

        <table class="w-full text-left border-collapse text-[13px]">
            <thead>
                <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider bg-[#faf8f5]">
                    <th class="py-3 px-4 font-medium">Document Title</th>
                    <th class="py-3 px-4 font-medium">Matter Dossier</th>
                    <th class="py-3 px-4 font-medium">Category</th>
                    <th class="py-3 px-4 font-medium">Privilege Assertion</th>
                    <th class="py-3 px-4 font-medium">Portal Visibility</th>
                    <th class="py-3 px-4 font-medium">Size</th>
                    <th class="py-3 px-4 font-medium">SHA-256 Checksum</th>
                    <th class="py-3 px-4 font-medium text-center">Download</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0eee8]">
                @forelse($documents as $doc)
                <tr x-show="categoryFilter === 'all' || categoryFilter === '{{ $doc->category }}'" class="hover:bg-[#faf9f5] transition-colors group">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-700 text-xl">picture_as_pdf</span>
                            <div class="flex flex-col">
                                <span class="font-medium text-xs text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">{{ $doc->title }}</span>
                                <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $doc->filename }} (v{{ $doc->version }})</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-[#1a1a1a]">
                        {{ $doc->matter->case_number ?? 'General' }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]">
                            {{ $doc->category }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]">
                            {{ $doc->privilege }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        @if($doc->is_client_visible)
                            <span class="inline-flex items-center gap-1 font-mono text-[10px] px-2 py-0.5 rounded-full bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                <span class="material-symbols-outlined text-xs">visibility</span>
                                <span>Client Visible</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-mono text-[10px] px-2 py-0.5 rounded-full bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]">
                                <span class="material-symbols-outlined text-xs">lock</span>
                                <span>Chambers Only</span>
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 font-mono text-xs text-[#646864]">
                        {{ $doc->formattedSize() }}
                    </td>
                    <td class="py-3.5 px-4 font-mono text-[10.5px] text-[#8a8a8a] max-w-[120px] truncate" title="{{ $doc->sha256 }}">
                        {{ substr($doc->sha256, 0, 16) }}...
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#646864] hover:text-[#23493a] hover:bg-[#f5f3ed] rounded transition-colors inline-flex" title="Download Verified File">
                            <span class="material-symbols-outlined text-lg">download</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-xs text-[#8a8a8a]">
                        No documents stored in the vault yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
                        <label class="font-semibold text-[#1a1a1a]">Filing Category</label>
                        <select name="category" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="Pleading" selected>Pleading / Complaint</option>
                            <option value="Motion">Motion / Memorandum</option>
                            <option value="Brief">Appellate Brief</option>
                            <option value="Exhibit">Exhibit / Evidentiary Artifact</option>
                            <option value="Deposition">Deposition Transcript</option>
                            <option value="Contract">Executed Contract</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Privilege Assertion</label>
                        <select name="privilege" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="Attorney-Client" selected>Attorney-Client Privileged</option>
                            <option value="Work Product">Attorney Work Product</option>
                            <option value="Confidential">Confidential / Protective Order</option>
                            <option value="Public Filing">Public Court Filing</option>
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
