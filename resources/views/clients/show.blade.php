@extends('layouts.app')

@section('title', $client->name . ' — Client Dossier')
@section('header_title', 'Client Dossier')

@section('content')
<div x-data="{
    activeTab: 'overview',
    openRequestModal: false,
    openReviewModal: false,
    openAssistedModal: false,
    openEditModal: false,
    selectedRequest: null,
    reviewStatus: 'completed',
    rejectionReason: '',
    selectedMatterId: '{{ $client->matters->first()->id ?? 'auto_create' }}',
    presetCategory: 'KYC & Identification',
    presetTitle: '',
    presetDescription: '',
    selectPreset(cat, title, desc) {
        this.presetCategory = cat;
        this.presetTitle = title;
        this.presetDescription = desc;
    }
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Dossier Breadcrumb & Header -->
    <div class="mb-5">
        <div class="flex items-center gap-2 text-xs text-[#8a8a8a] mb-2 font-mono">
            <a href="{{ route('clients.index') }}" class="hover:text-[#23493a] transition-colors">Client Registry</a>
            <span>/</span>
            <span class="text-[#1a1a1a] font-semibold">{{ $client->name }}</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                        {{ $client->name }}
                    </h1>
                    <span class="font-mono text-[10.5px] uppercase tracking-wider px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] font-semibold">
                        {{ ucfirst($client->category ?? $client->type) }}
                    </span>
                    @if($client->isOfflineOnly())
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#92400e] bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>Assisted Offline</span>
                        </span>
                    @elseif($client->portal_status === 'active' || $client->user_id)
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                            <span>Portal Active</span>
                        </span>
                    @elseif($client->portal_status === 'invited')
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#1e40af] bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full">
                            <span class="material-symbols-outlined text-[13px]">schedule</span>
                            <span>Invited (Pending Activation)</span>
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-[#646864]">
                    @if($client->father_husband_name)
                        <span>{{ $client->father_husband_name }}</span>
                        <span>•</span>
                    @endif
                    @if($client->police_station)
                        <span class="text-[#854d0e] font-medium">P.S. {{ $client->police_station }}</span>
                        <span>•</span>
                    @endif
                    <span>Lead Counsel: <strong class="text-[#1a1a1a]">{{ $client->primaryAttorney->name ?? 'Chambers Counsel' }}</strong></span>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Print Chamber Docket Slip -->
                <a href="{{ route('clients.intake-slip', $client->id) }}" target="_blank"
                   class="btn-secondary h-8 px-3 text-xs inline-flex items-center gap-1.5" title="Print Chamber File Docket Sheet for Physical Case File">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    <span>Chamber Slip</span>
                </a>

                <!-- Request Document Button -->
                <button type="button" @click="openRequestModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1.5" title="Request evidentiary document or KYC submission from client">
                    <span class="material-symbols-outlined text-[16px]">add_task</span>
                    <span>Request Document</span>
                </button>

                <!-- Portal Invite Button if Email Exists -->
                @if($client->email && ! $client->isOfflineOnly())
                    <form action="{{ route('clients.invite', $client->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-secondary h-8 px-3 text-xs inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">send</span>
                            <span>{{ $client->portal_status === 'invited' ? 'Resend Invite' : 'Send Invite' }}</span>
                        </button>
                    </form>
                @endif

                <!-- Edit Particulars Modal Toggle -->
                <button @click="openEditModal = true" class="btn-secondary h-8 px-2.5 text-xs inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">edit</span>
                    <span>Edit</span>
                </button>

                <!-- Delete Client Button -->
                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete client {{ addslashes($client->name) }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary h-8 px-2.5 text-xs inline-flex items-center gap-1 text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200" title="Delete Client Record">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                        <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dossier Navigation Tabs (Super Admin Standard) -->
    <div class="border-b border-[#e5e3dc] flex items-center gap-1 overflow-x-auto mb-6 text-xs font-medium">
        <button @click="activeTab = 'overview'"
                class="px-4 py-2.5 border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                :class="activeTab === 'overview' ? 'border-[#23493a] text-[#23493a] font-semibold' : 'border-transparent text-[#646864] hover:text-[#1a1a1a]'">
            <span class="material-symbols-outlined text-[16px]">info</span>
            <span>Overview &amp; Statutory KYC</span>
        </button>

        <button @click="activeTab = 'matters'"
                class="px-4 py-2.5 border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                :class="activeTab === 'matters' ? 'border-[#23493a] text-[#23493a] font-semibold' : 'border-transparent text-[#646864] hover:text-[#1a1a1a]'">
            <span class="material-symbols-outlined text-[16px]">folder_open</span>
            <span>Associated Matters ({{ $client->matters->count() }})</span>
        </button>

        <button @click="activeTab = 'requests'"
                class="px-4 py-2.5 border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                :class="activeTab === 'requests' ? 'border-[#23493a] text-[#23493a] font-semibold' : 'border-transparent text-[#646864] hover:text-[#1a1a1a]'">
            <span class="material-symbols-outlined text-[16px]">fact_check</span>
            <span>Document Requests ({{ $client->documentRequests->count() }})</span>
            @php $pendingRequests = $client->documentRequests->where('status', 'submitted')->count(); @endphp
            @if($pendingRequests > 0)
                <span class="px-1.5 py-0.2 rounded-full bg-amber-100 text-amber-800 text-[10px] font-mono">{{ $pendingRequests }} to review</span>
            @endif
        </button>

        <button @click="activeTab = 'portal'"
                class="px-4 py-2.5 border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                :class="activeTab === 'portal' ? 'border-[#23493a] text-[#23493a] font-semibold' : 'border-transparent text-[#646864] hover:text-[#1a1a1a]'">
            <span class="material-symbols-outlined text-[16px]">lock_open</span>
            <span>Portal &amp; Communications</span>
        </button>

        <button @click="activeTab = 'billing'"
                class="px-4 py-2.5 border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                :class="activeTab === 'billing' ? 'border-[#23493a] text-[#23493a] font-semibold' : 'border-transparent text-[#646864] hover:text-[#1a1a1a]'">
            <span class="material-symbols-outlined text-[16px]">receipt</span>
            <span>Retainer &amp; Ledger</span>
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: OVERVIEW & STATUTORY KYC                                       -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left 8 Cols: Court Memo & Statutory Particulars -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Court Memo Particulars Card -->
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Court Memo Identification</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Statutory party details for memo of parties, affidavits &amp; court filings</p>
                        </div>
                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] font-semibold">
                            UIDAI / ITD Compliant
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs">
                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Full Legal Name</span>
                            <span class="font-semibold text-[#1a1a1a] text-[13px]">{{ $client->name }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Parentage / Spouse (Court Memo)</span>
                            <span class="text-[#1a1a1a] font-medium">{{ $client->father_husband_name ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Permanent Account Number (PAN)</span>
                            <span class="font-mono font-semibold text-[#1a1a1a] text-[13px]">
                                {{ $client->pan ?? 'Not Recorded' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Aadhaar (Masked as per UIDAI)</span>
                            <span class="font-mono font-semibold text-[#1a1a1a] text-[13px]">
                                {{ $client->masked_aadhaar ?? 'Not Recorded' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Age &amp; Gender</span>
                            <span class="text-[#1a1a1a]">
                                {{ $client->age ? $client->age . ' Years' : '—' }} / {{ ucfirst($client->gender ?? 'Not Specified') }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Occupation / Profession</span>
                            <span class="text-[#1a1a1a]">{{ $client->occupation ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Corporate / Institutional Information if Applicable -->
                @if($client->isCorporate() || $client->isInstitutional() || $client->cin || $client->gstin)
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                    <div class="pb-3 mb-4 border-b border-[#f0eee8]">
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Corporate &amp; Regulatory Credentials</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Ministry of Corporate Affairs (MCA) and GST credentials</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs">
                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">CIN / LLPIN</span>
                            <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->cin ?: ($client->llpin ?: '—') }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">GSTIN</span>
                            <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->gstin ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">Authorized Signatory</span>
                            <span class="text-[#1a1a1a] font-medium">{{ $client->contact_person ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#8a8a8a] block uppercase font-mono">ROC Jurisdiction / Reg. No.</span>
                            <span class="text-[#1a1a1a]">{{ $client->roc_jurisdiction ?: ($client->registration_number ?: '—') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Power of Attorney (POA) & Representation Details -->
                @if($client->representation_mode !== 'self' || $client->representative_name)
                <div class="border border-amber-200 bg-amber-50/40 rounded-md p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-amber-700 text-lg">assignment_ind</span>
                        <h2 class="text-[14px] font-semibold text-amber-900">Representation &amp; Power of Attorney Dossier</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-xs">
                        <div>
                            <span class="text-[11px] text-[#78350f] block uppercase font-mono">Representation Capacity</span>
                            <span class="font-semibold text-[#78350f]">{{ ucwords(str_replace('_', ' ', $client->representation_mode)) }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#78350f] block uppercase font-mono">Authorized Representative</span>
                            <span class="font-semibold text-amber-950">{{ $client->representative_name ?? '—' }} ({{ $client->representative_relation ?? 'Representative' }})</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#78350f] block uppercase font-mono">Representative Phone</span>
                            <span class="font-mono text-amber-950">{{ $client->representative_phone ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-[#78350f] block uppercase font-mono">POA Registration Number</span>
                            <span class="font-mono text-amber-950">{{ $client->poa_registration_number ?? 'Not Recorded' }}</span>
                        </div>

                        @if($client->poa_sub_registrar_office)
                        <div class="sm:col-span-2">
                            <span class="text-[11px] text-[#78350f] block uppercase font-mono">Sub-Registrar Office</span>
                            <span class="text-amber-950">{{ $client->poa_sub_registrar_office }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Co-Litigants & Joint Litigants Table (if Joint) -->
                @if($client->members->isNotEmpty())
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#f0eee8]">
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Joint Litigants &amp; Co-Petitioners ({{ $client->members->count() }})</h2>
                            <p class="text-[12px] text-[#8a8a8a] mt-0.5">Additional parties represented under this unified retainer</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-[#f0eee8] text-[11px] text-[#8a8a8a] uppercase font-mono">
                                    <th class="pb-2 font-medium">Party Name</th>
                                    <th class="pb-2 font-medium">Relationship</th>
                                    <th class="pb-2 font-medium">PAN</th>
                                    <th class="pb-2 font-medium">Aadhaar (Last 4)</th>
                                    <th class="pb-2 font-medium">Contact</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0eee8]">
                                @foreach($client->members as $member)
                                <tr>
                                    <td class="py-2.5 font-semibold text-[#1a1a1a]">{{ $member->name }}</td>
                                    <td class="py-2.5 text-[#646864]">{{ $member->relationship }}</td>
                                    <td class="py-2.5 font-mono text-[#1a1a1a]">{{ $member->pan ?: '—' }}</td>
                                    <td class="py-2.5 font-mono text-[#1a1a1a]">{{ $member->masked_aadhaar ?: '—' }}</td>
                                    <td class="py-2.5 font-mono text-[#646864]">{{ $member->phone ?: '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right 4 Cols: Service Address, Jurisdiction & Internal Notes -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Address for Service Card -->
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#f0eee8]">
                        <h3 class="text-[13.5px] font-semibold text-[#1a1a1a]">Address for Service</h3>
                        <span class="material-symbols-outlined text-[18px] text-[#23493a]">home_pin</span>
                    </div>

                    <p class="text-xs text-[#1a1a1a] leading-relaxed mb-3">
                        {{ $client->full_address ?: 'No formal street address recorded.' }}
                    </p>

                    @if($client->police_station)
                    <div class="p-2.5 rounded bg-[#fdfaf2] border border-[#fef08a] flex items-start gap-2 text-xs">
                        <span class="material-symbols-outlined text-amber-700 text-[18px]">local_police</span>
                        <div>
                            <span class="text-[10.5px] text-amber-800 uppercase font-mono block">Thana Jurisdiction</span>
                            <span class="font-semibold text-amber-950">P.S. {{ $client->police_station }}</span>
                            <span class="text-[10px] text-amber-800 block mt-0.5">Critical for court summons &amp; jurisdictional verification</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Digital Mode & Access Card -->
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-2">Onboarding &amp; Access Status</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[#8a8a8a]">Mode:</span>
                            <span class="font-medium text-[#1a1a1a]">{{ $client->isOfflineOnly() ? 'Assisted Offline' : 'Digital Portal' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#8a8a8a]">Portal Status:</span>
                            <span class="font-medium text-[#065f46]">{{ ucfirst($client->portal_status) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#8a8a8a]">Registered Phone:</span>
                            <span class="font-mono text-[#1a1a1a]">{{ $client->phone ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#8a8a8a]">Client Since:</span>
                            <span class="font-mono text-[#1a1a1a]">{{ $client->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Internal Chamber Notes -->
                @if($client->internal_intake_notes)
                <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#23493a]">lock</span>
                        <span>Counsel Intake Notes</span>
                    </h3>
                    <p class="text-xs text-[#646864] whitespace-pre-wrap leading-relaxed">
                        {{ $client->internal_intake_notes }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: ASSOCIATED MATTERS                                             -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'matters'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Ongoing Legal Dockets &amp; Cases</h2>
            <a href="{{ route('matters.create') }}" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Open New Matter</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($client->matters as $matter)
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs hover:border-[#23493a]/50 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-mono text-[11px] text-[#23493a] bg-[#f5f3ed] border border-[#e5e3dc] px-2 py-0.5 rounded font-semibold">
                            {{ $matter->case_number }}
                        </span>
                        <span class="text-[11px] text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full font-medium">
                            {{ ucfirst($matter->stage) }}
                        </span>
                    </div>

                    <h3 class="text-[15px] font-semibold text-[#1a1a1a] mb-1">
                        <a href="{{ route('matters.show', $matter->id) }}" class="hover:text-[#23493a] transition-colors">
                            {{ $matter->title }}
                        </a>
                    </h3>

                    <p class="text-xs text-[#646864] mb-3">
                        Court: <strong class="text-[#1a1a1a]">{{ $matter->court_name ?? 'District Court' }}</strong>
                        @if($matter->judge_name) • Presiding: {{ $matter->judge_name }} @endif
                    </p>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-between text-xs text-[#8a8a8a]">
                    <span>Counsel: <strong class="text-[#1a1a1a]">{{ $matter->leadAttorney->name ?? 'Advocate' }}</strong></span>
                    <a href="{{ route('matters.show', $matter->id) }}" class="text-[#23493a] hover:underline font-medium inline-flex items-center gap-0.5">
                        <span>Open Case</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full border border-[#e5e3dc] bg-white rounded-md p-8 text-center text-xs text-[#8a8a8a]">
                No court matters currently linked to this client. Click "Open New Matter" to assign a case dossier.
            </div>
            @endforelse
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: DOCUMENT REQUESTS & SUBMISSIONS                                -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'requests'" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Client Evidentiary Document Requests (F-07)</h2>
                <p class="text-[12px] text-[#8a8a8a]">Clients can only upload files against explicit active requests. Protected under Section 126 Evidence Act.</p>
            </div>
            <button type="button" @click="openRequestModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1.5 self-start" title="Initiate an evidentiary document request">
                <span class="material-symbols-outlined text-[16px]">add_task</span>
                <span>Initiate Document Request</span>
            </button>
        </div>

        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#f0eee8] text-[11px] text-[#8a8a8a] uppercase font-mono">
                        <th class="py-3 px-4 font-medium">Document Title</th>
                        <th class="py-3 px-4 font-medium">Case Docket</th>
                        <th class="py-3 px-4 font-medium">Category</th>
                        <th class="py-3 px-4 font-medium">Due Date</th>
                        <th class="py-3 px-4 font-medium">Status</th>
                        <th class="py-3 px-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($client->documentRequests as $req)
                    <tr class="hover:bg-[#faf9f5]">
                        <td class="py-3.5 px-4 font-medium text-[#1a1a1a]">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-[#23493a]">description</span>
                                <div>
                                    <span class="font-semibold block">{{ $req->title }}</span>
                                    @if($req->is_assisted_submission)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-[#92400e] bg-amber-50 px-1.5 py-0.2 rounded font-sans">
                                            <span>Chamber Physical Intake (Clerk Scanned)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[#646864]">
                            {{ $req->matter->case_number ?? 'General' }}
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">{{ $req->category }}</td>
                        <td class="py-3.5 px-4 font-mono text-[#1a1a1a]">
                            {{ $req->due_date ? $req->due_date->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($req->status === 'completed')
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full">
                                    <span class="material-symbols-outlined text-[12px]">verified</span>
                                    <span>Accepted &amp; Filed</span>
                                </span>
                            @elseif($req->status === 'submitted' || $req->status === 'under_review')
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#1e40af] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    <span>Ready for Review</span>
                                </span>
                            @elseif($req->status === 'rejected')
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                                    <span class="material-symbols-outlined text-[12px]">error</span>
                                    <span>Re-submission Required</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                                    <span>Pending Client Upload</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($req->document)
                                    <a href="{{ route('documents.download', $req->document->id) }}" class="btn-secondary h-7 px-2 text-[11px] inline-flex items-center gap-1" title="Download & Inspect Submission">
                                        <span class="material-symbols-outlined text-[14px]">download</span>
                                        <span>Download</span>
                                    </a>
                                @endif

                                @if($req->status === 'submitted' || $req->status === 'under_review')
                                    <button type="button" @click="selectedRequest = {{ $req->id }}; openReviewModal = true;"
                                            class="btn-primary h-7 px-2.5 text-[11px] inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">fact_check</span>
                                        <span>Review</span>
                                    </button>
                                @endif

                                @if($req->status === 'pending')
                                    <!-- Assisted Physical Intake Action -->
                                    <button type="button" @click="selectedRequest = {{ $req->id }}; openAssistedModal = true;"
                                            class="btn-secondary h-7 px-2 text-[11px] inline-flex items-center gap-1 text-[#92400e] border-amber-200 hover:bg-amber-50" title="Scan & upload paper document received in chambers">
                                        <span class="material-symbols-outlined text-[14px]">scanner</span>
                                        <span>Assisted Intake</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-4 text-center">
                            <div class="flex flex-col items-center justify-center gap-2.5 max-w-md mx-auto">
                                <div class="w-10 h-10 rounded-full bg-[#23493a]/10 text-[#23493a] flex items-center justify-center mb-1">
                                    <span class="material-symbols-outlined text-[22px]">assignment_add</span>
                                </div>
                                <h4 class="text-sm font-semibold text-[#1a1a1a]">No document requests initiated yet</h4>
                                <p class="text-xs text-[#8a8a8a] leading-relaxed">
                                    You haven't requested any documents from {{ $client->name }} yet. You can request identification proofs (Aadhaar / PAN), signed Vakalatnama, or case evidentiary files.
                                </p>
                                <button type="button" @click="openRequestModal = true" class="btn-primary h-8 px-4 text-xs inline-flex items-center gap-1.5 mt-2 shadow-xs">
                                    <span class="material-symbols-outlined text-[16px]">add_task</span>
                                    <span>Initiate Document Request</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: PORTAL & COMMUNICATIONS                                       -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'portal'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Portal Status & Invitation Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <h3 class="text-[14px] font-semibold text-[#1a1a1a] mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">verified_user</span>
                    <span>Client Portal Authentication</span>
                </h3>
                <p class="text-xs text-[#646864] mb-4 leading-relaxed">
                    Client Portal enables the litigant to track court hearing schedules, read counsel advisories, and upload required documents.
                </p>

                <div class="p-3.5 rounded-md bg-[#faf8f5] border border-[#e5e3dc] space-y-2.5 text-xs mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[#8a8a8a]">Portal Login Email:</span>
                        <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->email ?? 'No email registered' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#8a8a8a]">Account Status:</span>
                        <span class="font-semibold {{ $client->portal_status === 'active' ? 'text-[#065f46]' : 'text-[#92400e]' }}">
                            {{ ucfirst($client->portal_status) }}
                        </span>
                    </div>

                    @if($client->invitation_token && $client->invitation_expires_at)
                    <div class="flex items-center justify-between">
                        <span class="text-[#8a8a8a]">Invitation Link Expiry:</span>
                        <span class="font-mono text-[#1a1a1a]">{{ $client->invitation_expires_at->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>

                @if($client->email)
                    <form action="{{ route('clients.invite', $client->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-[17px]">send</span>
                            <span>{{ $client->hasActiveInvitation() ? 'Resend Invitation Link' : 'Dispatch Activation Invitation' }}</span>
                        </button>
                    </form>
                @else
                    <div class="p-3 rounded bg-amber-50 border border-amber-200 text-xs text-[#92400e]">
                        Client has no registered email address. Update client particulars to provide an email before dispatching portal credentials.
                    </div>
                @endif
            </div>

            <!-- Click-to-Send WhatsApp Notice Card (Indian Legal Reality) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#15803d]">chat</span>
                    <h3 class="text-[14px] font-semibold text-[#1a1a1a]">WhatsApp Client Notices (Instant Chambers Dispatch)</h3>
                </div>
                <p class="text-xs text-[#646864] mb-4">
                    Send quick, formal court and document notices directly to the litigant's mobile number via WhatsApp.
                </p>

                @if($client->phone)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone);
                    if (!str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 10) {
                        $cleanPhone = '91' . $cleanPhone;
                    }
                    $firmName = $client->firm->name ?? 'Sharma Legal Chambers';
                    $docNotice = urlencode("Namaskar {$client->name}, this is from {$firmName}. Your counsel has requested essential case documents for your ongoing court matter. Please submit them to our chamber or upload to your client portal.");
                    $courtNotice = urlencode("Dear {$client->name}, update regarding your case from {$firmName}. Your upcoming hearing has been scheduled. Please contact counsel for preparation.");
                @endphp
                <div class="space-y-2.5">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $docNotice }}" target="_blank"
                       class="btn-secondary w-full h-9 px-3 text-xs flex items-center justify-between text-[#15803d] border-[#15803d]/30 hover:bg-[#15803d]/10">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">contact_support</span>
                            <span>Document Request Reminder (WhatsApp)</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                    </a>

                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $courtNotice }}" target="_blank"
                       class="btn-secondary w-full h-9 px-3 text-xs flex items-center justify-between text-[#1e40af] border-blue-200 hover:bg-blue-50">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">event</span>
                            <span>Court Date / Hearing Advisory (WhatsApp)</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                    </a>
                </div>
                @else
                <div class="p-3 rounded bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#8a8a8a]">
                    No phone number recorded. Update client profile to enable WhatsApp notices.
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 5: RETAINER & BILLING LEDGER                                      -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'billing'" class="space-y-4">
        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Client Retainer Trust Ledger</h3>
                    <p class="text-xs text-[#8a8a8a]">Advance trust deposits and billing statements</p>
                </div>
                <div class="text-right">
                    <span class="text-[11px] text-[#8a8a8a] uppercase font-mono block">Advance Balance</span>
                    <span class="font-mono text-xl font-bold text-[#23493a]">₹{{ number_format($client->trust_balance, 2) }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#f0eee8] text-[11px] text-[#8a8a8a] uppercase font-mono">
                            <th class="pb-2 font-medium">Invoice #</th>
                            <th class="pb-2 font-medium">Matter</th>
                            <th class="pb-2 font-medium">Amount</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0eee8]">
                        @forelse($client->invoices as $inv)
                        <tr>
                            <td class="py-2.5 font-mono font-semibold text-[#1a1a1a]">{{ $inv->invoice_number }}</td>
                            <td class="py-2.5 text-[#646864]">{{ $inv->matter->case_number ?? 'Chambers Retainer' }}</td>
                            <td class="py-2.5 font-mono font-semibold text-[#1a1a1a]">₹{{ number_format($inv->total, 2) }}</td>
                            <td class="py-2.5">
                                <span class="font-mono text-[10.5px] uppercase px-2 py-0.5 rounded {{ $inv->status === 'paid' ? 'bg-[#ecfdf5] text-[#065f46]' : 'bg-amber-50 text-[#92400e]' }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-2.5 font-mono text-[#8a8a8a]">{{ $inv->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-[#8a8a8a]">No invoice records on file for this client.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: INITIATE NEW DOCUMENT REQUEST WITH INDIAN LEGAL PRESETS        -->
    <!-- ===================================================================== -->
    <div x-show="openRequestModal" x-cloak @click.away="openRequestModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-xl p-6 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">add_task</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Initiate Document Request</h3>
                </div>
                <button type="button" @click="openRequestModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Indian Document Preset Quick-Select Chips -->
            <div class="mb-4">
                <label class="font-semibold text-[#1a1a1a] text-xs block mb-1.5">Indian Legal Document Presets</label>
                <div class="flex flex-wrap gap-1.5 text-[11px]">
                    <button type="button" @click="selectPreset('Property & Civil', 'Registered Sale Deed (Original Scan)', 'Please upload complete scanned copy including stamp paper & sub-registrar endorsement seals.')"
                            class="px-2 py-1 rounded bg-[#faf8f5] border border-[#e5e3dc] hover:border-[#23493a] text-[#1a1a1a]">
                        Sale Deed
                    </button>
                    <button type="button" @click="selectPreset('Property & Civil', '7/12 Extract (Satbara / Khatiyan)', 'Latest certified copy of Record of Rights (7/12 extract) issued by Revenue Authority.')"
                            class="px-2 py-1 rounded bg-[#faf8f5] border border-[#e5e3dc] hover:border-[#23493a] text-[#1a1a1a]">
                        7/12 Satbara
                    </button>
                    <button type="button" @click="selectPreset('Criminal & Bail', 'Certified Copy of FIR with Complaint', 'Police Station FIR copy along with initial written complaint.')"
                            class="px-2 py-1 rounded bg-[#faf8f5] border border-[#e5e3dc] hover:border-[#23493a] text-[#1a1a1a]">
                        FIR Copy
                    </button>
                    <button type="button" @click="selectPreset('Corporate', 'Board Resolution for Representation', 'Certified true copy of Board Resolution authorizing legal representation.')"
                            class="px-2 py-1 rounded bg-[#faf8f5] border border-[#e5e3dc] hover:border-[#23493a] text-[#1a1a1a]">
                        Board Resolution
                    </button>
                    <button type="button" @click="selectPreset('KYC & Identification', 'Vakalatnama (Signed Original Scan)', 'Duly executed and signed Vakalatnama on green legal paper.')"
                            class="px-2 py-1 rounded bg-[#faf8f5] border border-[#e5e3dc] hover:border-[#23493a] text-[#1a1a1a]">
                        Vakalatnama
                    </button>
                </div>
            </div>

            <form action="{{ route('document-requests.store') }}" method="POST" class="flex flex-col gap-3 text-xs overflow-y-auto pr-1">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}"/>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Target Matter / Case Docket *</label>
                    <select name="matter_id" required class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                        @forelse($client->matters as $matter)
                            <option value="{{ $matter->id }}">{{ $matter->case_number }} — {{ $matter->title }}</option>
                        @empty
                            <option value="auto_create" selected>Client Intake &amp; Onboarding Dossier (Auto-organized)</option>
                        @endforelse
                        @if($client->matters->isNotEmpty())
                            <option value="auto_create">+ New Client Intake &amp; Onboarding Dossier</option>
                        @endif
                    </select>
                    @if($client->matters->isEmpty())
                        <p class="text-[11px] text-[#8a8a8a] mt-0.5">This client has no active court case yet. Documents will be automatically organized under a <em>Client Onboarding &amp; Intake Dossier</em> docket.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Document Title *</label>
                        <input name="title" required type="text" x-model="presetTitle" placeholder="e.g. Registered Sale Deed"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Category</label>
                        <input name="category" type="text" x-model="presetCategory" placeholder="e.g. Property Documents"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Priority</label>
                        <select name="priority" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="normal" selected>Normal Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent (Court Filing Imminent)</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Submission Due Date</label>
                        <input name="due_date" type="date" value="{{ now()->addDays(7)->format('Y-m-d') }}"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Instructions &amp; Guidance for Client</label>
                    <textarea name="description" rows="2" x-model="presetDescription" placeholder="Specify any specific instructions, e.g. color scan, certified copy, or all schedules..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openRequestModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Dispatch Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: REVIEW CLIENT SUBMISSION (ACCEPT OR REJECT WITH REASON)        -->
    <!-- ===================================================================== -->
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
                    <label class="font-semibold text-[#1a1a1a]">Review Determination</label>
                    <select name="status" x-model="reviewStatus" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                        <option value="completed">✓ Accept &amp; File in Matter Vault</option>
                        <option value="rejected">✕ Reject &amp; Request Re-submission</option>
                        <option value="under_review">⋯ Mark as Under Review</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1" x-show="reviewStatus === 'rejected'">
                    <label class="font-semibold text-red-700">Reason for Rejection *</label>
                    <textarea name="rejection_reason" rows="3" placeholder="e.g. Page 4 signature is cut off; please upload high-resolution scan of all pages..."
                              class="p-2.5 rounded-md bg-red-50/50 border border-red-200 text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="flex flex-col gap-1" x-show="reviewStatus === 'completed'">
                    <label class="font-semibold text-[#1a1a1a]">Counsel Review Notes (Optional)</label>
                    <textarea name="review_notes" rows="2" placeholder="e.g. Verified original stamp paper dated 12-04-2018..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openReviewModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: ASSISTED PHYSICAL INTAKE (CLERK SCANNING FOR OFFLINE CLIENT)   -->
    <!-- ===================================================================== -->
    <div x-show="openAssistedModal" x-cloak @click.away="openAssistedModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-md p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#92400e]">scanner</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Assisted Physical Document Intake</h3>
                </div>
                <button type="button" @click="openAssistedModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <p class="text-xs text-[#646864] mb-3 leading-relaxed">
                Scan and attach paper documents physically brought to chambers by the client. The system computes a cryptographic SHA-256 hash for evidentiary record.
            </p>

            <form :action="'/document-requests/' + selectedRequest + '/assisted-upload'" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Select Scanned PDF / Document *</label>
                    <input name="file" required type="file" accept=".pdf,.png,.jpg,.jpeg,.docx"
                           class="file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-[#23493a] file:text-white border border-[#e5e3dc] rounded-md p-1.5 text-xs text-[#1a1a1a]"/>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Intake Remarks / Physical Filing Docket Note</label>
                    <textarea name="clerk_notes" rows="2" placeholder="e.g. Received in chamber from Suresh (Son & POA). Original inspected and returned..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openAssistedModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Upload &amp; Verify Hash</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: EDIT CLIENT PARTICULARS                                        -->
    <!-- ===================================================================== -->
    <div x-show="openEditModal" x-cloak @click.away="openEditModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-2xl my-8 p-6 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Update Client Particulars &amp; KYC</h3>
                <button type="button" @click="openEditModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('clients.update', $client->id) }}" method="POST" class="flex flex-col gap-3 text-xs overflow-y-auto pr-1">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Legal Name *</label>
                        <input name="name" value="{{ $client->name }}" required type="text"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Parentage (S/o, D/o, W/o)</label>
                        <input name="father_husband_name" value="{{ $client->father_husband_name }}" type="text"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Classification</label>
                        <select name="category" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="individual" {{ ($client->category ?? $client->type) === 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="joint" {{ $client->category === 'joint' ? 'selected' : '' }}>Joint / Multiple</option>
                            <option value="corporate" {{ in_array($client->category, ['corporate', 'company', 'llp']) ? 'selected' : '' }}>Corporate / LLP</option>
                            <option value="institution" {{ in_array($client->category, ['institution', 'trust', 'society']) ? 'selected' : '' }}>Institution / Trust</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Mode</label>
                        <select name="onboarding_mode" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="portal_online" {{ $client->onboarding_mode === 'portal_online' ? 'selected' : '' }}>Online Portal</option>
                            <option value="assisted_offline" {{ $client->onboarding_mode === 'assisted_offline' ? 'selected' : '' }}>Assisted Offline</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Status</label>
                        <select name="status" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="conflict" {{ $client->status === 'conflict' ? 'selected' : '' }}>Conflict</option>
                            <option value="archived" {{ $client->status === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Email Address</label>
                        <input name="email" value="{{ $client->email }}" type="email"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1" x-data="{
                        countryCode: '+91',
                        phoneRaw: '',
                        init() {
                            const full = '{{ $client->phone }}'.trim();
                            const codes = ['+971', '+880', '+977', '+966', '+965', '+968', '+973', '+94', '+91', '+44', '+61', '+65', '+49', '+33', '+81', '+1'];
                            let found = false;
                            for (const c of codes) {
                                if (full.startsWith(c)) {
                                    this.countryCode = c;
                                    this.phoneRaw = full.substring(c.length).trim();
                                    found = true;
                                    break;
                                }
                            }
                            if (!found) {
                                this.phoneRaw = full;
                            }
                        },
                        get fullPhone() { return (this.countryCode + ' ' + this.phoneRaw).trim(); }
                    }">
                        <label class="font-semibold text-[#1a1a1a]">Mobile / WhatsApp Phone</label>
                        <input type="hidden" name="phone" :value="fullPhone"/>
                        <div class="flex items-center gap-1.5">
                            <select x-model="countryCode" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                                <option value="+91">🇮🇳 +91</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+971">🇦🇪 +971</option>
                                <option value="+61">🇦🇺 +61</option>
                                <option value="+65">🇸🇬 +65</option>
                                <option value="+49">🇩🇪 +49</option>
                                <option value="+33">🇫🇷 +33</option>
                                <option value="+81">🇯🇵 +81</option>
                                <option value="+966">🇸🇦 +966</option>
                                <option value="+974">🇶🇦 +974</option>
                                <option value="+965">🇰🇼 +965</option>
                                <option value="+968">🇴🇲 +968</option>
                                <option value="+973">🇧🇭 +973</option>
                                <option value="+880">🇧🇩 +880</option>
                                <option value="+977">🇳🇵 +977</option>
                                <option value="+94">🇱🇰 +94</option>
                            </select>
                            <input x-model="phoneRaw" type="text" placeholder="98100 12345"
                                   class="w-full h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                    </div>
                </div>

                <!-- Client Demographics: Age, Gender, Occupation -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Age (Years)</label>
                        <input name="age" value="{{ $client->age }}" type="number" min="0" max="120" placeholder="e.g. 35"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Gender</label>
                        <select name="gender" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                            <option value="">Select Gender</option>
                            <option value="male" {{ $client->gender === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ $client->gender === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ $client->gender === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Occupation / Profession</label>
                        <input name="occupation" value="{{ $client->occupation }}" type="text" placeholder="e.g. Business / Salaried"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">PAN</label>
                        <input name="pan" value="{{ $client->pan }}" maxlength="10" type="text" style="text-transform: uppercase;"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Aadhaar Last 4 Digits</label>
                        <input name="aadhaar_last_four" value="{{ $client->aadhaar_last_four }}" maxlength="4" type="text"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Police Station (Thana)</label>
                        <input name="police_station" value="{{ $client->police_station }}" type="text"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Address Line 1</label>
                    <input name="address_line_1" value="{{ $client->address_line_1 ?: $client->address }}" type="text"
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">District</label>
                        <input name="district" value="{{ $client->district }}" type="text"
                               class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">State</label>
                        <input name="state" value="{{ $client->state }}" type="text"
                               class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Pincode</label>
                        <input name="pincode" value="{{ $client->pincode }}" maxlength="6" type="text"
                               class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a]"/>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openEditModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs">Save Updates</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
