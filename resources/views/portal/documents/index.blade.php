@extends('portal.layout')

@section('title', 'Case Documents Vault')
@section('header_title', 'Documents Vault')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" x-data="{ uploadDocModalOpen: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Case Documents Vault</h1>
            <p class="text-[13px] text-[#646864] mt-1">Court orders, written pleadings, filings, and evidence documents shared with your organization.</p>
        </div>
        <button type="button" @click="uploadDocModalOpen = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] shadow-xs transition-colors self-start sm:self-auto cursor-pointer">
            <span class="material-symbols-outlined text-base">upload</span>
            <span>Upload Document to Case</span>
        </button>
    </div>

    <!-- Category Filters -->
    @php
        $currentCat = request('category', 'all');
    @endphp
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] pb-2 text-xs overflow-x-auto">
        <a href="{{ route('portal.documents.index') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'all' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            All Filings ({{ $documents->count() }})
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Pleadings']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'Pleadings' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Pleadings
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Court Orders']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'Court Orders' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Court Orders
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Contracts']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'Contracts' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Contracts &amp; Agreements
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Evidence']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'Evidence' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Evidence
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Client Submissions']) }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ $currentCat === 'Client Submissions' ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:text-[#1a1a1a] hover:bg-white' }}">
            Client Submissions
        </a>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf9f5] border-b border-[#e5e3dc] text-[#8a8a8a] font-mono text-[10.5px] uppercase">
                        <th class="py-3.5 px-4 font-medium">Document Title</th>
                        <th class="py-3.5 px-4 font-medium">Matter Docket</th>
                        <th class="py-3.5 px-4 font-medium">Category</th>
                        <th class="py-3.5 px-4 font-medium">File Details</th>
                        <th class="py-3.5 px-4 font-medium">Uploaded Date</th>
                        <th class="py-3.5 px-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @php
                        $filteredDocs = $documents;
                        if ($currentCat !== 'all') {
                            $filteredDocs = $documents->where('category', $currentCat);
                        }
                    @endphp
                    @forelse($filteredDocs as $doc)
                    <tr class="hover:bg-[#faf9f5]/70 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-2xl text-red-600 shrink-0">picture_as_pdf</span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-[#1a1a1a] text-sm">{{ $doc->title }}</span>
                                    <span class="text-[10.5px] font-mono text-[#8a8a8a]">{{ $doc->filename }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-[#646864]">
                            <div class="flex flex-col">
                                <span class="font-medium text-[#1a1a1a] truncate max-w-[200px]">{{ $doc->matter->title ?? 'General' }}</span>
                                <span class="font-mono text-[10.5px] text-[#8a8a8a]">{{ $doc->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded bg-[#faf9f5] border border-[#e5e3dc] text-[#1a1a1a] font-mono text-[10.5px]">
                                {{ $doc->category }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 font-mono text-[11.5px] text-[#646864]">
                            <span>{{ number_format($doc->file_size / 1048576, 2) }} MB</span>
                            <span class="block text-[9.5px] text-[#065f46]">SHA-256 Verified</span>
                        </td>

                        <td class="py-3.5 px-4 font-mono text-[11.5px] text-[#646864]">
                            {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('portal.documents.download', $doc->id) }}" class="inline-flex items-center gap-1 text-xs text-[#23493a] hover:underline font-medium">
                                <span class="material-symbols-outlined text-[14px]">download</span>
                                <span>Download</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-xs text-[#8a8a8a]">
                            No documents found in this vault category.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div x-show="uploadDocModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="uploadDocModalOpen = false"
             class="bg-white border border-[#e5e3dc] rounded-lg max-w-lg w-full p-6 shadow-xl">
            
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-md bg-[#23493a]/10 text-[#23493a] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">upload</span>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Upload Case Document</h3>
                        <p class="text-[11.5px] text-[#8a8a8a]">Add filings or records to your active matter folder</p>
                    </div>
                </div>
                <button type="button" @click="uploadDocModalOpen = false" class="text-[#8e8e8e] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('portal.documents.upload') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                @csrf

                <!-- Select Case -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1a1a]">Associated Matter Docket</label>
                    <select name="matter_id" required class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Title -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1a1a]">Document Title</label>
                    <input type="text" name="title" required placeholder="e.g. Certified Copy of Power of Attorney" class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>

                <!-- Category -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1a1a]">Category</label>
                    <select name="category" required class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="Client Submissions">Client Submissions</option>
                        <option value="Evidence">Evidence</option>
                        <option value="Contracts">Contracts &amp; Agreements</option>
                        <option value="Pleadings">Pleadings</option>
                    </select>
                </div>

                <!-- Dropzone Box -->
                <div class="border-2 border-dashed border-[#e5e3dc] hover:border-[#23493a] rounded-md p-6 text-center transition-colors bg-[#faf9f5]">
                    <span class="material-symbols-outlined text-3xl text-[#23493a] mb-2">drive_folder_upload</span>
                    <label class="block text-xs font-semibold text-[#1a1a1a] cursor-pointer">
                        <span>Click to browse file</span>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="sr-only" onchange="document.getElementById('vault-file-display').innerText = this.files[0]?.name || '';"/>
                    </label>
                    <p class="text-[11px] text-[#8a8a8a] mt-1">PDF, DOCX, JPG, PNG up to 50MB</p>
                    <p id="vault-file-display" class="text-xs font-mono font-bold text-[#23493a] mt-2"></p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="uploadDocModalOpen = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">upload</span>
                        <span>Upload Document</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
