@extends('layouts.app')

@section('title', 'Client Directory — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Client Directory')

@section('content')
<div x-data="{
    openCreateModal: false,
    isSubmitting: false,
    activeTab: '{{ request('category', 'all') }}',
    entityType: 'individual',
    onboardingMode: 'portal_online',
    members: [],
    addMember() {
        this.members.push({ name: '', relationship: 'Co-petitioner', phone: '', email: '', pan: '', aadhaar_last_four: '' });
    },
    removeMember(index) {
        this.members.splice(index, 1);
    }
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Client Directory</h1>
            <p class="text-[13px] text-[#646864] mt-1">Indian legal representation registry, joint litigants, corporate retainers, and assisted intake</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="openCreateModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>New Client Onboarding</span>
            </button>
        </div>
    </div>

    <!-- Connected Metric Ribbon (Super Admin Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-6">
        <a href="{{ route('clients.index') }}" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Total Client Roster</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1 font-mono">{{ $totalClientsCount }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-1">Active &amp; retainer entities</div>
        </a>
        <a href="{{ route('clients.index', ['mode' => 'portal']) }}" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Portal Active (Digital)</div>
            <div class="text-[26px] font-semibold text-[#065f46] tracking-tight mt-1 font-mono">{{ $portalActiveCount }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-1">Online self-service clients</div>
        </a>
        <a href="{{ route('clients.index', ['mode' => 'assisted_offline']) }}" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Assisted Offline Intake</div>
            <div class="text-[26px] font-semibold text-[#92400e] tracking-tight mt-1 font-mono">{{ $assistedOfflineCount }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-1">No email / POA / chamber files</div>
        </a>
        <a href="{{ route('matters.index') }}" class="p-4 sm:p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Ongoing Court Dossiers</div>
            <div class="text-[26px] font-semibold text-[#1a1a1a] tracking-tight mt-1 font-mono">{{ $activeMattersCount }}</div>
            <div class="text-[11.5px] text-[#8a8a8a] mt-1">Active matters represented</div>
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="border border-[#e5e3dc] bg-white rounded-md p-4 mb-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Classification Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs">
            <a href="{{ route('clients.index') }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ !request('category') && !request('mode') ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                All ({{ $totalClientsCount }})
            </a>
            <a href="{{ route('clients.index', ['category' => 'individual']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'individual' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Individuals (Single)
            </a>
            <a href="{{ route('clients.index', ['category' => 'joint']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'joint' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Joint / Multiple Litigants
            </a>
            <a href="{{ route('clients.index', ['category' => 'corporate']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'corporate' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Corporate &amp; LLPs
            </a>
            <a href="{{ route('clients.index', ['category' => 'institution']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'institution' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Institutions &amp; Trusts
            </a>
            <a href="{{ route('clients.index', ['mode' => 'assisted_offline']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('mode') === 'assisted_offline' ? 'bg-[#23493a] text-white' : 'text-[#92400e] bg-amber-50 hover:bg-amber-100' }}">
                <span class="inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Offline / Low Literacy ({{ $assistedOfflineCount }})</span>
                </span>
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('clients.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}"/>
            @endif
            @if(request('mode'))
                <input type="hidden" name="mode" value="{{ request('mode') }}"/>
            @endif
            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#8a8a8a]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, PAN, CIN, P.S..."
                       class="w-full h-9 pl-9 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] placeholder-[#8a8a8a] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
            </div>
            @if(request('q'))
                <a href="{{ route('clients.index', array_filter(['category' => request('category'), 'mode' => request('mode')])) }}" class="text-xs text-[#8a8a8a] hover:text-[#1a1a1a] px-1">Clear</a>
            @endif
        </form>
    </div>

    <!-- Client Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
        @forelse($clients as $client)
        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs hover:border-[#23493a]/50 transition-all flex flex-col justify-between">
            <div>
                <!-- Card Header: Category & Digital Mode Badges -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="font-mono text-[10.5px] uppercase tracking-wider px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] font-semibold">
                            {{ ucfirst($client->category ?? $client->type) }}
                        </span>
                        @if($client->isJoint())
                            <span class="text-[10.5px] text-[#1e40af] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded font-medium">
                                {{ $client->members->count() + 1 }} Litigants
                            </span>
                        @endif
                    </div>

                    <!-- Digital Access Indicator -->
                    @if($client->isOfflineOnly())
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#92400e] bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full" title="Assisted Offline Intake (No portal login / Assisted by Clerk)">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>Assisted Offline</span>
                        </span>
                    @elseif($client->portal_status === 'active' || $client->user_id)
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full" title="Client has an active portal login">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                            <span>Portal Active</span>
                        </span>
                    @elseif($client->portal_status === 'invited')
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#1e40af] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full" title="Invitation sent, waiting for client password setup">
                            <span class="material-symbols-outlined text-[12px]">schedule</span>
                            <span>Invited</span>
                        </span>
                    @else
                        <span class="text-[10.5px] text-[#8a8a8a] bg-[#faf8f5] border border-[#e5e3dc] px-2 py-0.5 rounded">
                            Uninvited
                        </span>
                    @endif
                </div>

                <!-- Client Title & Parentage -->
                <h3 class="text-[15.5px] font-semibold text-[#1a1a1a] mb-0.5 leading-snug">
                    <a href="{{ route('clients.show', $client->id) }}" class="hover:text-[#23493a] transition-colors">
                        {{ $client->name }}
                    </a>
                </h3>

                @if($client->father_husband_name)
                    <p class="text-[11.5px] text-[#646864] mb-1.5 font-sans">{{ $client->father_husband_name }}</p>
                @elseif($client->contact_person)
                    <p class="text-[11.5px] text-[#646864] mb-1.5">Attn: {{ $client->contact_person }}</p>
                @endif

                <!-- Statutory Indian KYC Chips -->
                <div class="flex flex-wrap items-center gap-1.5 my-2.5">
                    @if($client->pan)
                        <span class="inline-flex items-center gap-1 font-mono text-[10.5px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-1.5 py-0.5 rounded">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">PAN:</span> {{ $client->pan }}
                        </span>
                    @endif
                    @if($client->aadhaar_last_four)
                        <span class="inline-flex items-center gap-1 font-mono text-[10.5px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-1.5 py-0.5 rounded">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">Aadhaar:</span> {{ $client->masked_aadhaar }}
                        </span>
                    @endif
                    @if($client->cin)
                        <span class="inline-flex items-center gap-1 font-mono text-[10px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-1.5 py-0.5 rounded truncate max-w-[170px]" title="CIN: {{ $client->cin }}">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">CIN:</span> {{ $client->cin }}
                        </span>
                    @endif
                    @if($client->police_station)
                        <span class="inline-flex items-center gap-1 text-[10.5px] bg-[#fdfaf2] text-[#854d0e] border border-[#fef08a] px-1.5 py-0.5 rounded" title="Police Station Jurisdiction">
                            <span class="material-symbols-outlined text-[12px]">local_police</span>
                            <span>P.S. {{ $client->police_station }}</span>
                        </span>
                    @endif
                </div>

                <!-- Contact Particulars -->
                <div class="space-y-1 text-xs text-[#646864] pt-2 border-t border-[#f0eee8]">
                    @if($client->email)
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">mail</span>
                        <span class="truncate font-mono">{{ $client->email }}</span>
                    </div>
                    @else
                    <div class="flex items-center gap-1.5 text-[#92400e] text-[11px] font-medium">
                        <span class="material-symbols-outlined text-[14px]">mail_off</span>
                        <span>No Email Registered (Offline Litigant)</span>
                    </div>
                    @endif

                    @if($client->phone)
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-[#8a8a8a]">call</span>
                        <span class="font-mono">{{ $client->phone }}</span>
                    </div>
                    @endif

                    <div class="flex items-center gap-1.5 text-[11.5px]">
                        <span class="material-symbols-outlined text-[14px] text-[#23493a]">gavel</span>
                        <span>Counsel: <strong class="text-[#1a1a1a]">{{ $client->primaryAttorney->name ?? 'Chambers Counsel' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Card Footer: Cases & Quick Actions -->
            <div class="mt-4 pt-3 border-t border-[#f0eee8] flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] text-[#8a8a8a] block uppercase font-mono">Dossiers</span>
                    <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->matters->count() }} active cases</span>
                </div>

                <div class="flex items-center gap-1.5">
                    <!-- Physical Chamber Slip Link -->
                    <a href="{{ route('clients.intake-slip', $client->id) }}" target="_blank"
                       class="btn-secondary h-7 px-2 text-[11px] inline-flex items-center gap-1 text-[#646864] hover:text-[#1a1a1a]" title="Print Chamber File Docket Slip (Basta Slip)">
                        <span class="material-symbols-outlined text-[14px]">receipt_long</span>
                        <span>Slip</span>
                    </a>

                    <!-- Invite to Portal Button if Email Available -->
                    @if($client->email && ! $client->isOfflineOnly() && ! $client->user_id)
                        <form action="{{ route('clients.invite', $client->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary h-7 px-2.5 text-[11px] inline-flex items-center gap-1" title="Dispatch Portal Activation Link">
                                <span class="material-symbols-outlined text-[14px]">send</span>
                                <span>Invite</span>
                            </button>
                        </form>
                    @endif

                    <!-- View Client Dossier -->
                    <a href="{{ route('clients.show', $client->id) }}" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1 font-medium text-[#23493a] border-[#23493a]/30 hover:bg-[#23493a]/10">
                        <span>Dossier</span>
                        <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full border border-[#e5e3dc] bg-white rounded-md p-10 text-center text-xs text-[#8a8a8a] shadow-xs">
            <span class="material-symbols-outlined text-4xl text-[#8a8a8a] mb-2 block">person_search</span>
            No client records matching the selected classification or search criteria.
            <div class="mt-3">
                <button @click="openCreateModal = true" class="btn-primary h-8 px-3 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>Onboard First Client</span>
                </button>
            </div>
        </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- DYNAMIC CLIENT ONBOARDING MODAL (INDIAN LEGAL STANDARDS)                  -->
    <!-- ========================================================================= -->
    <div x-show="openCreateModal"
         x-cloak
         @click.away="openCreateModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-3xl my-8 p-6 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-2xl text-[#23493a]">person_add</span>
                    <div>
                        <h3 class="text-[16px] font-semibold text-[#1a1a1a]">Indian Legal Client Representation Intake</h3>
                        <p class="text-[12px] text-[#646864]">Single/joint litigants, corporate retainers, Indian KYC, and assisted offline onboarding</p>
                    </div>
                </div>
                <button type="button" @click="openCreateModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('clients.store') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true" class="overflow-y-auto pr-1 flex flex-col gap-4 text-xs">
                @csrf

                <!-- Step 1: Entity Classification Radio Chips -->
                <div>
                    <label class="font-semibold text-[#1a1a1a] block mb-1.5">1. Client Legal Entity Classification *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="border p-2.5 rounded-md flex flex-col cursor-pointer transition-all"
                               :class="entityType === 'individual' ? 'border-[#23493a] bg-[#23493a]/5 font-semibold text-[#23493a]' : 'border-[#e5e3dc] bg-white text-[#646864] hover:bg-[#faf8f5]'">
                            <input type="radio" name="category" value="individual" x-model="entityType" class="sr-only"/>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="material-symbols-outlined text-[17px]">person</span>
                                <span>Individual (1 Person)</span>
                            </div>
                            <span class="text-[10px] text-[#8a8a8a] mt-1 font-normal">Single litigant or petitioner</span>
                        </label>

                        <label class="border p-2.5 rounded-md flex flex-col cursor-pointer transition-all"
                               :class="entityType === 'joint' ? 'border-[#23493a] bg-[#23493a]/5 font-semibold text-[#23493a]' : 'border-[#e5e3dc] bg-white text-[#646864] hover:bg-[#faf8f5]'">
                            <input type="radio" name="category" value="joint" x-model="entityType" class="sr-only"/>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="material-symbols-outlined text-[17px]">group</span>
                                <span>Joint (2+ Litigants)</span>
                            </div>
                            <span class="text-[10px] text-[#8a8a8a] mt-1 font-normal">Co-petitioners / Family members</span>
                        </label>

                        <label class="border p-2.5 rounded-md flex flex-col cursor-pointer transition-all"
                               :class="entityType === 'corporate' ? 'border-[#23493a] bg-[#23493a]/5 font-semibold text-[#23493a]' : 'border-[#e5e3dc] bg-white text-[#646864] hover:bg-[#faf8f5]'">
                            <input type="radio" name="category" value="corporate" x-model="entityType" class="sr-only"/>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="material-symbols-outlined text-[17px]">apartment</span>
                                <span>Company / LLP</span>
                            </div>
                            <span class="text-[10px] text-[#8a8a8a] mt-1 font-normal">Pvt Ltd, Public Ltd, LLP, OPC</span>
                        </label>

                        <label class="border p-2.5 rounded-md flex flex-col cursor-pointer transition-all"
                               :class="entityType === 'institution' ? 'border-[#23493a] bg-[#23493a]/5 font-semibold text-[#23493a]' : 'border-[#e5e3dc] bg-white text-[#646864] hover:bg-[#faf8f5]'">
                            <input type="radio" name="category" value="institution" x-model="entityType" class="sr-only"/>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="material-symbols-outlined text-[17px]">account_balance</span>
                                <span>Trust / Society</span>
                            </div>
                            <span class="text-[10px] text-[#8a8a8a] mt-1 font-normal">NGO, Society, Partnership, Firm</span>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Digital Literacy & Onboarding Mode -->
                <div class="p-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc]">
                    <label class="font-semibold text-[#1a1a1a] block mb-1">2. Client Digital Literacy &amp; Onboarding Mode *</label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-xs mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="onboarding_mode" value="portal_online" x-model="onboardingMode"
                                   class="text-[#23493a] focus:ring-[#23493a]"/>
                            <div>
                                <span class="font-medium text-[#1a1a1a]">Online Client Portal</span>
                                <span class="text-[11px] text-[#646864] block">Client has email &amp; smartphone. Receives invitation token link to set password.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="onboarding_mode" value="assisted_offline" x-model="onboardingMode"
                                   class="text-[#23493a] focus:ring-[#23493a]"/>
                            <div>
                                <span class="font-medium text-[#92400e]">Assisted Offline (No Email / Illiterate / POA)</span>
                                <span class="text-[11px] text-[#646864] block">Direct chamber visits, phone/WhatsApp notifications, physical intake slip.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Step 3: Entity Primary Name & Contact -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]" x-text="entityType === 'corporate' || entityType === 'institution' ? 'Entity / Organization Legal Name *' : 'Primary Litigant Full Name *'"></label>
                        <input name="name" required type="text" placeholder="e.g. Ramesh Chandra Sharma or Sharma Infotech Pvt Ltd"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>

                    <!-- Individual Parentage: Father/Husband Name (Court Memo Standard) -->
                    <div class="flex flex-col gap-1" x-show="entityType === 'individual' || entityType === 'joint'">
                        <label class="font-semibold text-[#1a1a1a]">Father's / Husband's / Mother's Name (Court Standard)</label>
                        <input name="father_husband_name" type="text" placeholder="e.g. S/o Late Shri Jagdish Sharma"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>

                    <!-- Corporate Authorized Signatory Contact -->
                    <div class="flex flex-col gap-1" x-show="entityType === 'corporate' || entityType === 'institution'">
                        <label class="font-semibold text-[#1a1a1a]">Authorized Signatory / Contact Person *</label>
                        <input name="contact_person" type="text" placeholder="e.g. Vikram Malhotra, Managing Director"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <!-- Contact Particulars: Email & Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">
                            Official Email <span x-show="onboardingMode === 'portal_online'" class="text-red-500">*</span>
                            <span x-show="onboardingMode === 'assisted_offline'" class="text-[11px] text-[#8a8a8a] font-normal">(Optional for Offline)</span>
                        </label>
                        <input name="email" :required="onboardingMode === 'portal_online'" type="email" placeholder="client@domain.com"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">
                            Mobile / WhatsApp Phone Number <span x-show="onboardingMode === 'assisted_offline'" class="text-red-500">*</span>
                        </label>
                        <input name="phone" :required="onboardingMode === 'assisted_offline'" type="text" placeholder="+91 98100 12345"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <!-- Step 4: Indian Statutory KYC -->
                <div class="pt-2 border-t border-[#f0eee8]">
                    <h4 class="font-semibold text-[#1a1a1a] mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#23493a]">badge</span>
                        <span>Indian Statutory KYC &amp; Identification</span>
                    </h4>

                    <!-- Individual KYC: PAN & Masked Aadhaar -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3" x-show="entityType === 'individual' || entityType === 'joint'">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">PAN (10 Characters)</label>
                            <input name="pan" type="text" maxlength="10" placeholder="ABCDE1234F" style="text-transform: uppercase;"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Aadhaar Last 4 Digits (UIDAI Norms)</label>
                            <input name="aadhaar_last_four" type="text" maxlength="4" placeholder="4321"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Age / Gender</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <input name="age" type="number" min="1" max="120" placeholder="Age"
                                       class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                                <select name="gender" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                                    <option value="">Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Corporate KYC: CIN, LLPIN, GSTIN -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3" x-show="entityType === 'corporate' || entityType === 'institution'">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">CIN / LLPIN</label>
                            <input name="cin" type="text" placeholder="U72200DL2020PTC123456" style="text-transform: uppercase;"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Company PAN</label>
                            <input name="pan" type="text" maxlength="10" placeholder="AAACM1234F" style="text-transform: uppercase;"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">GSTIN (15 Characters)</label>
                            <input name="gstin" type="text" maxlength="15" placeholder="07AAACM1234F1Z5" style="text-transform: uppercase;"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Address for Service & Court Police Station Jurisdiction -->
                <div class="pt-2 border-t border-[#f0eee8]">
                    <h4 class="font-semibold text-[#1a1a1a] mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#23493a]">pin_drop</span>
                        <span>Service Address &amp; Court Jurisdiction</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2.5">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Address Line 1 (Premises / Mohalla / Street)</label>
                            <input name="address_line_1" type="text" placeholder="e.g. Flat No. 402, Royal Residency, Sector 15"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Address Line 2 (Village / Post Office / Landmark)</label>
                            <input name="address_line_2" type="text" placeholder="e.g. Near Old Tehsil Office"
                                   class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">District / City</label>
                            <input name="district" type="text" placeholder="e.g. South Delhi"
                                   class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">State</label>
                            <input name="state" type="text" value="Delhi"
                                   class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#1a1a1a]">Pincode (6 Digits)</label>
                            <input name="pincode" type="text" maxlength="6" placeholder="110001"
                                   class="h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#854d0e] flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">local_police</span>
                                <span>Police Station (Thana)</span>
                            </label>
                            <input name="police_station" type="text" placeholder="e.g. Hauz Khas"
                                   class="h-9 px-2.5 rounded-md bg-white border border-amber-300 bg-amber-50/40 text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                        </div>
                    </div>
                </div>

                <!-- Conditional Section: Power of Attorney (POA) / Representative for Offline Clients -->
                <div x-show="onboardingMode === 'assisted_offline'" class="p-3.5 rounded-md bg-amber-50/50 border border-amber-200">
                    <h4 class="font-semibold text-[#92400e] mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">assignment_ind</span>
                        <span>Power of Attorney (POA) / Legal Representative Particulars</span>
                    </h4>
                    <p class="text-[11px] text-[#78350f] mb-3">If the client is illiterate, minor, or represented through a POA holder, next friend, or guardian:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-2.5">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">Representation Capacity</label>
                            <select name="representation_mode" class="h-9 px-2 rounded-md bg-white border border-amber-200 text-xs text-[#1a1a1a]">
                                <option value="self">Self (Litigant Directly in Chamber)</option>
                                <option value="poa_holder">Power of Attorney (POA) Holder</option>
                                <option value="next_friend_guardian">Next Friend / Guardian (CPC Order 32)</option>
                                <option value="relative">Son / Daughter / Authorized Relative</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">Representative Full Name</label>
                            <input name="representative_name" type="text" placeholder="e.g. Suresh Kumar (Son &amp; POA)"
                                   class="h-9 px-3 rounded-md bg-white border border-amber-200 text-xs text-[#1a1a1a]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">Representative Phone</label>
                            <input name="representative_phone" type="text" placeholder="+91 98111 22334"
                                   class="h-9 px-3 rounded-md bg-white border border-amber-200 text-xs text-[#1a1a1a]"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">POA Reg. Number (if registered)</label>
                            <input name="poa_registration_number" type="text" placeholder="e.g. IV-1204/2023"
                                   class="h-9 px-3 rounded-md bg-white border border-amber-200 font-mono text-xs text-[#1a1a1a]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">POA Registration Date</label>
                            <input name="poa_date" type="date"
                                   class="h-9 px-3 rounded-md bg-white border border-amber-200 text-xs text-[#1a1a1a]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-[#78350f]">Sub-Registrar Office</label>
                            <input name="poa_sub_registrar_office" type="text" placeholder="e.g. SR-V Mehrauli, Delhi"
                                   class="h-9 px-3 rounded-md bg-white border border-amber-200 text-xs text-[#1a1a1a]"/>
                        </div>
                    </div>
                </div>

                <!-- Conditional Section: Joint Co-Litigants Repeater -->
                <div x-show="entityType === 'joint'" class="p-3.5 rounded-md bg-blue-50/50 border border-blue-200">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h4 class="font-semibold text-[#1e40af] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">groups</span>
                                <span>Co-Litigants &amp; Joint Litigant Members</span>
                            </h4>
                            <p class="text-[11px] text-[#1e3a8a]">Add all co-plaintiffs, co-petitioners, or joint property owners represented under this file.</p>
                        </div>
                        <button type="button" @click="addMember()" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1 text-[#1e40af] border-blue-300 hover:bg-blue-100">
                            <span class="material-symbols-outlined text-[14px]">add</span>
                            <span>Add Co-Litigant</span>
                        </button>
                    </div>

                    <div class="space-y-2 mt-2">
                        <template x-for="(member, index) in members" :key="index">
                            <div class="bg-white border border-blue-200 rounded-md p-3 grid grid-cols-1 sm:grid-cols-6 gap-2 items-center">
                                <div class="sm:col-span-2">
                                    <input type="text" :name="'members[' + index + '][name]'" x-model="member.name" required placeholder="Co-Litigant Name"
                                           class="h-8 px-2.5 w-full rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                                </div>
                                <div>
                                    <input type="text" :name="'members[' + index + '][relationship]'" x-model="member.relationship" placeholder="Relation (e.g. Brother, Co-owner)"
                                           class="h-8 px-2.5 w-full rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                                </div>
                                <div>
                                    <input type="text" :name="'members[' + index + '][phone]'" x-model="member.phone" placeholder="Phone"
                                           class="h-8 px-2.5 w-full rounded bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                                </div>
                                <div>
                                    <input type="text" :name="'members[' + index + '][pan]'" x-model="member.pan" maxlength="10" placeholder="PAN" style="text-transform: uppercase;"
                                           class="h-8 px-2.5 w-full rounded bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a]"/>
                                </div>
                                <div class="flex items-center justify-end">
                                    <button type="button" @click="removeMember(index)" class="text-red-500 hover:text-red-700 p-1" title="Remove">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="members.length === 0" class="text-center py-3 text-[11.5px] text-[#1e3a8a] bg-white/60 border border-dashed border-blue-200 rounded">
                            No co-litigants added yet. Click <strong>"Add Co-Litigant"</strong> to attach additional petitioners or joint claimants.
                        </div>
                    </div>
                </div>

                <!-- Step 6: Counsel Assignment & Advance Trust Balance -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 border-t border-[#f0eee8]">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Lead Consulting Counsel</label>
                        <select name="primary_attorney_id" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            @foreach($attorneys as $attorney)
                                <option value="{{ $attorney->id }}" {{ Auth::id() === $attorney->id ? 'selected' : '' }}>
                                    {{ $attorney->name }} ({{ ucfirst($attorney->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Opening Retainer / Advance Trust (₹)</label>
                        <input name="trust_balance" type="number" step="0.01" min="0" value="0.00"
                               class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <!-- Internal Chamber Intake Notes -->
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Confidential Chamber Intake Notes (Advocate Only)</label>
                    <textarea name="internal_intake_notes" rows="2" placeholder="Brief facts of dispute, initial counsel impressions, or physical file docket notes..."
                              class="px-3 py-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"></textarea>
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2.5">
                    <button type="button" @click="openCreateModal = false" :disabled="isSubmitting" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''" class="btn-primary h-9 px-5 text-xs inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]" x-show="!isSubmitting">how_to_reg</span>
                        <span class="material-symbols-outlined text-[16px] animate-spin" x-show="isSubmitting" style="display: none;">progress_activity</span>
                        <span x-text="isSubmitting ? 'Registering Client...' : 'Complete Client Intake'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
