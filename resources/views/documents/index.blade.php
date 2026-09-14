<x-app-layout>
    <x-slot name="title">Document &amp; Evidence Vault — Quire Legal</x-slot>

    <div x-data="{ openUploadModal: false, categoryFilter: 'all' }">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e9e8e5] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#727973] uppercase tracking-wider">Encrypted Litigation Repository</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Document Vault &amp; Evidence Hub</h1>
                <p class="text-sm text-[#727973]">SHA-256 authenticated filings, exhibits, depositions, and executed agreements</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openUploadModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-base">upload</span>
                    <span>Upload New Filing</span>
                </button>
            </div>
        </div>

        <!-- Drag & Drop Upload Zone (Stitch spec) -->
        <div @click="openUploadModal = true" class="p-8 mb-6 rounded-xl border-2 border-dashed border-[#c1c8c1] bg-white hover:border-[#1a3c2a] transition-colors flex flex-col items-center justify-center text-center cursor-pointer group">
            <div class="w-12 h-12 rounded-full bg-[#f4f3f1] group-hover:bg-[#c5ecd2]/50 flex items-center justify-center text-[#1a3c2a] mb-3 transition-colors">
                <span class="material-symbols-outlined text-2xl">cloud_upload</span>
            </div>
            <h3 class="text-sm font-semibold text-[#1a1c1a]">Drag and drop legal filings or exhibit archives here</h3>
            <p class="text-xs text-[#727973] mt-1">Supports PDF, DOCX, TIFF, Bates-stamped bundles up to 50 MB (SHA-256 Hash Authenticated)</p>
            <span class="mt-3 inline-flex items-center px-3 py-1 rounded bg-[#efeeeb] text-[#424843] text-xs font-medium group-hover:bg-[#1a3c2a] group-hover:text-white transition-colors">Select Document File</span>
        </div>

        <!-- Vault Files Table -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a] text-xl">folder_special</span>
                    <h3 class="text-sm font-semibold text-[#1a1c1a]">Verified Document Repository</h3>
                </div>
                <div class="flex items-center gap-1 bg-[#f4f3f1] p-1 rounded-lg border border-[#e9e8e5] text-xs">
                    <button @click="categoryFilter = 'all'" :class="categoryFilter === 'all' ? 'bg-white font-semibold text-[#1a3c2a] shadow-xs' : 'text-[#727973]'" class="px-2.5 py-1 rounded">All</button>
                    <button @click="categoryFilter = 'Pleading'" :class="categoryFilter === 'Pleading' ? 'bg-white font-semibold text-[#1a3c2a] shadow-xs' : 'text-[#727973]'" class="px-2.5 py-1 rounded">Pleadings</button>
                    <button @click="categoryFilter = 'Motion'" :class="categoryFilter === 'Motion' ? 'bg-white font-semibold text-[#1a3c2a] shadow-xs' : 'text-[#727973]'" class="px-2.5 py-1 rounded">Motions</button>
                    <button @click="categoryFilter = 'Exhibit'" :class="categoryFilter === 'Exhibit' ? 'bg-white font-semibold text-[#1a3c2a] shadow-xs' : 'text-[#727973]'" class="px-2.5 py-1 rounded">Exhibits</button>
                    <button @click="categoryFilter = 'Deposition'" :class="categoryFilter === 'Deposition' ? 'bg-white font-semibold text-[#1a3c2a] shadow-xs' : 'text-[#727973]'" class="px-2.5 py-1 rounded">Depositions</button>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f4f3f1] border-b border-[#e9e8e5] text-[11px] font-mono text-[#727973] uppercase tracking-wider">
                        <th class="py-3 px-4">Document Title</th>
                        <th class="py-3 px-4">Matter Dossier</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Privilege Assertion</th>
                        <th class="py-3 px-4">Size</th>
                        <th class="py-3 px-4">SHA-256 Checksum</th>
                        <th class="py-3 px-4 text-center">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeeeb] text-sm">
                    @foreach($documents as $doc)
                    <tr x-show="categoryFilter === 'all' || categoryFilter === '{{ $doc->category }}'" class="hover:bg-[#faf9f6] transition-colors group">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-red-700 text-xl">picture_as_pdf</span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-xs text-[#1a1c1a] group-hover:text-[#1a3c2a] transition-colors">{{ $doc->title }}</span>
                                    <span class="font-mono text-[10px] text-[#727973]">{{ $doc->filename }} (v{{ $doc->version }})</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-medium text-[#424843]">
                            {{ $doc->matter->case_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#efeeeb] text-[#424843]">
                                {{ $doc->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#fed977]/60 text-[#785d00] font-semibold">
                                {{ $doc->privilege }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-[#727973]">
                            {{ $doc->formattedSize() }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[10px] text-[#727973] max-w-[120px] truncate" title="{{ $doc->sha256 }}">
                            {{ substr($doc->sha256, 0, 16) }}...
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#727973] hover:text-[#1a3c2a] hover:bg-[#efeeeb] rounded inline-flex" title="Download Verified File">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Document Upload Modal -->
        <div x-show="openUploadModal" @click.away="openUploadModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#e9e8e5] w-full max-w-lg p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#efeeeb] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a]">upload_file</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">File Legal Filing into Vault</h3>
                    </div>
                    <button type="button" @click="openUploadModal = false" class="text-[#727973] hover:text-[#1a1c1a]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Matter Dossier</label>
                        <select name="matter_id" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                            @foreach($matters as $matter)
                            <option value="{{ $matter->id }}">{{ $matter->case_number }} · {{ $matter->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Filing / Exhibit Title</label>
                        <input name="title" required type="text" placeholder="e.g. Plaintiff's Response to Defendant's Motion to Dismiss" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Filing Category</label>
                            <select name="category" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                                <option value="Pleading" selected>Pleading / Complaint</option>
                                <option value="Motion">Motion / Memorandum</option>
                                <option value="Brief">Appellate Brief</option>
                                <option value="Exhibit">Exhibit / Evidentiary Artifact</option>
                                <option value="Deposition">Deposition Transcript</option>
                                <option value="Contract">Executed Contract</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Privilege Assertion</label>
                            <select name="privilege" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                                <option value="Attorney-Client" selected>Attorney-Client Privileged</option>
                                <option value="Work Product">Attorney Work Product</option>
                                <option value="Confidential">Confidential / Protective Order</option>
                                <option value="Public Filing">Public Court Filing</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Document File (PDF, DOCX, TXT)</label>
                        <input name="file" required type="file" class="w-full p-2 rounded-lg bg-[#faf9f6] border border-[#c1c8c1] file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-[#1a3c2a] file:text-white hover:file:bg-[#022616] cursor-pointer"/>
                    </div>

                    <div class="p-2.5 rounded bg-[#f0f9f4] text-[#2c4e3a] text-[11px] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">enhanced_encryption</span>
                        <span>File will be hashed with SHA-256 for chain of custody court admissibility.</span>
                    </div>

                    <div class="pt-3 border-t border-[#efeeeb] flex items-center justify-end gap-2">
                        <button type="button" @click="openUploadModal = false" class="px-4 py-2 rounded-lg border border-[#c1c8c1] text-[#424843] hover:bg-[#faf9f6]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#1a3c2a] text-white font-semibold hover:bg-[#022616]">Authenticate &amp; Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
