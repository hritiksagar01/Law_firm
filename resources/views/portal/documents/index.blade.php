@extends('portal.layout')

@section('title', 'Case Documents')

@section('content')
<div class="flex flex-col gap-6" x-data="{ uploadDocModalOpen: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Case Documents Vault</h1>
            <p class="text-xs text-[#727973] mt-1">Court orders, written pleadings, filings, and evidence documents shared with your organization.</p>
        </div>
        <button type="button" @click="uploadDocModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] shadow-sm transition-colors self-start sm:self-auto">
            <span class="material-symbols-outlined text-base">upload</span>
            <span>Upload Document to Case</span>
        </button>
    </div>

    <!-- Category Filters -->
    @php
        $currentCat = request('category', 'all');
    @endphp
    <div class="flex items-center gap-2 border-b border-[#e9e8e5] pb-2 text-xs overflow-x-auto">
        <a href="{{ route('portal.documents.index') }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'all' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            All Filings ({{ $documents->count() }})
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Pleadings']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'Pleadings' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Pleadings
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Court Orders']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'Court Orders' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Court Orders
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Contracts']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'Contracts' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Contracts &amp; Agreements
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Evidence']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'Evidence' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Evidence
        </a>
        <a href="{{ route('portal.documents.index', ['category' => 'Client Submissions']) }}" class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $currentCat === 'Client Submissions' ? 'bg-[#1a3c2a] text-white' : 'text-[#727973] hover:text-[#1a1c1a] hover:bg-white' }}">
            Client Submissions
        </a>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#faf9f6] border-b border-[#e9e8e5] text-[#727973] font-mono text-[10px] uppercase">
                        <th class="py-3.5 px-4 font-semibold">Document Title</th>
                        <th class="py-3.5 px-4 font-semibold">Matter Docket</th>
                        <th class="py-3.5 px-4 font-semibold">Category</th>
                        <th class="py-3.5 px-4 font-semibold">File Details</th>
                        <th class="py-3.5 px-4 font-semibold">Uploaded Date</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f4f3f1]">
                    @php
                        $filteredDocs = $documents;
                        if ($currentCat !== 'all') {
                            $filteredDocs = $documents->where('category', $currentCat);
                        }
                    @endphp
                    @forelse($filteredDocs as $doc)
                    <tr class="hover:bg-[#faf9f6]/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-2xl text-red-600 shrink-0">picture_as_pdf</span>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[#1a1c1a] text-sm">{{ $doc->title }}</span>
                                    <span class="text-[10px] font-mono text-[#727973]">{{ $doc->filename }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-[#424843]">
                            <div class="flex flex-col">
                                <span class="font-semibold text-[#1a1c1a] truncate max-w-[200px]">{{ $doc->matter->title ?? 'General' }}</span>
                                <span class="font-mono text-[10px] text-[#727973]">{{ $doc->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded bg-[#f4f3f1] text-[#424843] font-mono text-[10px]">
                                {{ $doc->category }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#727973]">
                            <span>{{ number_format($doc->file_size / 1048576, 2) }} MB</span>
                            <span class="block text-[9px] text-[#82a78f]">SHA-256 Auth</span>
                        </td>

                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#727973]">
                            {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('documents.download', $doc->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#f4f3f1] hover:bg-[#e9e8e5] text-[#1a1c1a] text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-sm text-[#1a3c2a]">download</span>
                                <span>Download</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#727973]">
                            <span class="material-symbols-outlined text-3xl mb-2 text-[#c1c8c1]">folder_open</span>
                            <p class="text-xs">No documents found matching this category.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Client Direct Upload Modal -->
    <div x-show="uploadDocModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="uploadDocModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#e9e8e5] flex flex-col gap-5 relative">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-[#1a3c2a] font-semibold">Direct File Upload</span>
                    <h3 class="text-base font-bold text-[#1a1c1a] mt-0.5">Upload Document to Case File</h3>
                </div>
                <button type="button" @click="uploadDocModalOpen = false" class="text-[#727973] hover:text-[#1a1c1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('portal.documents.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Select Associated Case</label>
                    <select name="matter_id" required class="w-full p-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a]">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Document Title / Description</label>
                    <input type="text" name="title" required placeholder="e.g. Scanned Agreement dated 12.04.2023" class="w-full p-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a]"/>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Category</label>
                    <select name="category" class="w-full p-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a]">
                        <option value="Client Submissions">Client Submissions</option>
                        <option value="Contracts">Contracts &amp; Agreements</option>
                        <option value="Evidence">Evidence &amp; Records</option>
                        <option value="Pleadings">Pleadings &amp; Affidavits</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Choose File (PDF, DOCX, Images)</label>
                    <input type="file" name="file" required class="w-full text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1a3c2a] file:text-white hover:file:bg-[#022616] file:cursor-pointer p-2 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1]"/>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f4f3f1]">
                    <button type="button" @click="uploadDocModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-[#727973] hover:bg-[#f4f3f1]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] shadow-sm">
                        Upload to Case File
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
