@extends('layouts.app')

@section('title', 'Client Directory — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Client Directory')

@section('content')
<div x-data="{
    openCreateModal: false,
    openAssignModal: false,
    assignClient: { id: null, name: '', attorney_id: '', notes: '' },
    isSubmitting: false,
    activeTab: '{{ request('category', 'all') }}',
    category: 'individual',
    onboardingMode: 'portal_online',
    salutation: 'Mr.',
    name: '',
    contactPerson: '',
    fatherSalutation: 'Late Shri',
    fatherHusbandName: '',
    email: '',
    countryCode: '+91',
    phoneRaw: '',
    age: '',
    gender: 'male',
    occupation: '',
    primaryAttorneyId: '{{ Auth::id() }}',
    initialStatus: 'lead',
    internalIntakeNotes: '',
    members: [],
    addMember() {
        this.members.push({ name: '', relationship: 'Co-petitioner', phone: '', email: '' });
    },
    removeMember(index) {
        this.members.splice(index, 1);
    },
    fillDemo(type) {
        if (type === 'joint') {
            this.category = 'joint';
            this.name = 'Vikram & Rajesh (Joint Litigants)';
            this.contactPerson = 'Vikram Malhotra';
            this.fatherSalutation = 'Late Shri';
            this.fatherHusbandName = 'Rameshwaram';
            this.email = 'joint.litigants@gmail.com';
            this.countryCode = '+91';
            this.phoneRaw = '9811099887';
            this.age = 41;
            this.gender = 'male';
            this.occupation = 'Property Owner & Business';
            this.members = [
                { name: 'Rajesh Sharma', relationship: 'Co-petitioner / Co-owner', phone: '+91 98765 11223', email: 'rajesh.sharma@gmail.com' }
            ];
        } else if (type === 'assisted') {
            this.category = 'individual';
            this.onboardingMode = 'assisted_offline';
            this.name = 'Chandra Sekhar';
            this.contactPerson = 'Chandra Sekhar';
            this.fatherSalutation = 'Shri';
            this.fatherHusbandName = 'K. Sekhar';
            this.email = '';
            this.countryCode = '+91';
            this.phoneRaw = '9810077665';
            this.age = 56;
            this.gender = 'male';
            this.occupation = 'Agriculture / Self-Employed';
            this.members = [];
        } else {
            this.category = 'individual';
            this.onboardingMode = 'portal_online';
            this.name = 'Vikram Malhotra';
            this.contactPerson = 'Vikram Malhotra';
            this.fatherSalutation = 'Late Shri';
            this.fatherHusbandName = 'Jagdish Malhotra';
            this.email = 'vikram.client@gmail.com';
            this.countryCode = '+91';
            this.phoneRaw = '9876543210';
            this.age = 38;
            this.gender = 'male';
            this.occupation = 'Business & Commercial';
            this.members = [];
        }
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
    <div class="border border-[#e5e3dc] bg-white rounded-md p-4 mb-6 shadow-xs flex flex-col gap-3">
        <!-- Classification Filter Tabs & Search Form -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs">
                <a href="{{ route('clients.index') }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ !request('category') && !request('mode') && !request('status') ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    All ({{ $totalClientsCount }})
                </a>
                <a href="{{ route('clients.index', ['category' => 'individual']) }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'individual' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    Individuals
                </a>
                <a href="{{ route('clients.index', ['category' => 'joint']) }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'joint' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    Joint / Litigants
                </a>
                <a href="{{ route('clients.index', ['category' => 'corporate']) }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'corporate' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    Corporate &amp; LLPs
                </a>
                <a href="{{ route('clients.index', ['category' => 'institution']) }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('category') === 'institution' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    Institutions
                </a>
                <a href="{{ route('clients.index', ['mode' => 'assisted_offline']) }}"
                   class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('mode') === 'assisted_offline' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Offline ({{ $assistedOfflineCount }})</span>
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
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}"/>
                @endif
                <div class="relative w-full md:w-64">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#8a8a8a]">search</span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, PAN, CIN, P.S..."
                           class="w-full h-9 pl-9 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] placeholder-[#8a8a8a] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                @if(request('q'))
                    <a href="{{ route('clients.index', array_filter(['category' => request('category'), 'mode' => request('mode'), 'status' => request('status')])) }}" class="text-xs text-[#8a8a8a] hover:text-[#1a1a1a] px-1">Clear</a>
                @endif
            </form>
        </div>

        <!-- Lifecycle Status Filter Ribbon (Lead, Intake, Conflict Check, Prospective, Active, Inactive, Former, Archived) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pt-2.5 border-t border-[#f0eee8] text-xs">
            <span class="text-[11px] font-semibold text-[#8a8a8a] uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">tune</span>
                <span>Lifecycle:</span>
            </span>
            @php
                $statusTabs = [
                    'all' => 'All Statuses',
                    'lead' => 'Lead',
                    'intake' => 'Intake',
                    'conflict_check' => 'Conflict Check',
                    'prospective' => 'Prospective',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'former' => 'Former Client',
                    'archived' => 'Archived',
                ];
            @endphp
            @foreach($statusTabs as $sKey => $sLabel)
                <a href="{{ route('clients.index', array_filter(['status' => $sKey === 'all' ? null : $sKey, 'category' => request('category'), 'mode' => request('mode'), 'q' => request('q')])) }}"
                   class="px-2.5 py-1 rounded text-xs font-medium transition-colors whitespace-nowrap {{ (request('status') === $sKey || (!request('status') && $sKey === 'all')) ? 'bg-[#23493a] text-white shadow-xs' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a] border border-[#e5e3dc] bg-white' }}">
                    <span>{{ $sLabel }}</span>
                    @if(isset($statusCounts[$sKey]))
                        <span class="text-[10.5px] opacity-80 font-mono ml-0.5">({{ $statusCounts[$sKey] }})</span>
                    @endif
                </a>
            @endforeach
        </div>
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
                        @php
                            $statusColors = [
                                'lead' => 'bg-amber-100 text-amber-900 border-amber-300',
                                'intake' => 'bg-blue-100 text-blue-900 border-blue-300',
                                'conflict_check' => 'bg-purple-100 text-purple-900 border-purple-300',
                                'prospective' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                                'active' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                'inactive' => 'bg-stone-100 text-stone-700 border-stone-200',
                                'former' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'archived' => 'bg-gray-100 text-gray-600 border-gray-200',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase {{ $statusColors[$client->status] ?? 'bg-stone-100 text-stone-700 border-stone-200' }} border font-medium">
                            {{ str_replace('_', ' ', $client->status) }}
                        </span>
                        @if($client->conflict_check_status)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase {{ $client->conflict_check_status === 'clear' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                Conflict: {{ str_replace('_', ' ', $client->conflict_check_status) }}
                            </span>
                        @endif
                        @if($client->isJoint())
                            <span class="text-[10.5px] text-[#1e40af] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded font-medium">
                                {{ $client->members->count() + 1 }} Litigants
                            </span>
                        @endif
                    </div>

                    <!-- Digital Access Indicator & Lead Status -->
                    @if($client->status === 'lead' || ! $client->primary_attorney_id)
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-800 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full" title="Self-registered lead awaiting advocate assignment">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                            <span>Lead / Intake Pending</span>
                        </span>
                    @elseif($client->isOfflineOnly())
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

                <!-- Demographics Particulars: Age, Gender & Occupation -->
                @if($client->age || $client->gender || $client->occupation)
                <div class="flex flex-wrap items-center gap-1.5 my-2.5">
                    @if($client->age)
                        <span class="inline-flex items-center gap-1 font-mono text-[10.5px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-2 py-0.5 rounded">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">Age:</span> {{ $client->age }} yrs
                        </span>
                    @endif
                    @if($client->gender)
                        <span class="inline-flex items-center gap-1 text-[10.5px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-2 py-0.5 rounded capitalize">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">Gender:</span> {{ $client->gender }}
                        </span>
                    @endif
                    @if($client->occupation)
                        <span class="inline-flex items-center gap-1 text-[10.5px] bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] px-2 py-0.5 rounded truncate max-w-[180px]" title="{{ $client->occupation }}">
                            <span class="text-[9px] text-[#8a8a8a] font-sans uppercase">Occ:</span> {{ $client->occupation }}
                        </span>
                    @endif
                </div>
                @endif

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
                        <span>Counsel: 
                            @if($client->primaryAttorney)
                                <strong class="text-[#1a1a1a]">{{ $client->primaryAttorney->name }}</strong>
                            @else
                                <strong class="text-amber-800 italic font-medium">Unassigned Lead</strong>
                            @endif
                        </span>
                    </div>

                    @if($client->preferredAttorney || $client->assignedParalegal)
                    <div class="flex items-center gap-2 text-[11px] text-[#646864] pt-0.5">
                        @if($client->preferredAttorney)
                            <span>Pref: <strong>{{ $client->preferredAttorney->name }}</strong></span>
                        @endif
                        @if($client->assignedParalegal)
                            <span>Paralegal: <strong>{{ $client->assignedParalegal->name }}</strong></span>
                        @endif
                    </div>
                    @endif

                    @if($client->client_type || $client->referral_source)
                    <div class="flex items-center gap-1.5 text-[10.5px] text-[#8a8a8a] font-mono pt-0.5">
                        @if($client->client_type)
                            <span class="capitalize">Type: {{ str_replace('_', ' ', $client->client_type) }}</span>
                        @endif
                        @if($client->referral_source)
                            <span>&middot; Source: {{ $client->referral_source }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Footer: Cases & Clean Action Layout -->
            <div class="mt-4 pt-3 border-t border-[#f0eee8] flex flex-col gap-2">
                <div class="flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-[#8a8a8a] block uppercase font-mono">Dossiers</span>
                        <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->matters->count() }} active case{{ $client->matters->count() === 1 ? '' : 's' }}</span>
                    </div>

                    <a href="{{ route('clients.show', $client->id) }}" class="btn-primary h-7 px-3 text-xs inline-flex items-center gap-1 font-medium shadow-2xs">
                        <span>View Dossier</span>
                        <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                    </a>
                </div>

                <!-- Secondary Actions Toolbar -->
                <div class="flex items-center justify-end gap-1.5 pt-1.5 border-t border-[#f8f7f4] flex-wrap">
                    <!-- Assign Advocate Button for Leads -->
                    @if($client->status === 'lead' || ! $client->primary_attorney_id)
                        <button type="button" @click="assignClient = { id: {{ $client->id }}, name: '{{ addslashes($client->name) }}', attorney_id: '{{ $client->primary_attorney_id ?? Auth::id() }}', notes: '{{ addslashes($client->internal_intake_notes ?? '') }}' }; openAssignModal = true"
                                class="h-6.5 px-2 text-[11px] rounded inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-medium cursor-pointer transition-colors" title="Assign Lead Advocate">
                            <span class="material-symbols-outlined text-[13px]">how_to_reg</span>
                            <span>Assign Advocate</span>
                        </button>
                    @endif

                    <!-- Physical Chamber Slip Link -->
                    <a href="{{ route('clients.intake-slip', $client->id) }}" target="_blank"
                       class="h-6.5 px-2 text-[11px] rounded inline-flex items-center gap-1 bg-white hover:bg-[#faf9f5] text-[#646864] hover:text-[#1a1a1a] border border-[#e5e3dc] transition-colors" title="Print Chamber Slip">
                        <span class="material-symbols-outlined text-[13px]">receipt_long</span>
                        <span>Slip</span>
                    </a>

                    <!-- Invite to Portal Button if Email Available -->
                    @if($client->email && ! $client->isOfflineOnly() && ! $client->user_id)
                        <form action="{{ route('clients.invite', $client->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="h-6.5 px-2 text-[11px] rounded inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-medium cursor-pointer transition-colors" title="Invite to Portal">
                                <span class="material-symbols-outlined text-[13px]">send</span>
                                <span>Invite</span>
                            </button>
                        </form>
                    @endif

                    <!-- Delete Client Button -->
                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete client {{ addslashes($client->name) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="h-6.5 w-6.5 rounded inline-flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors cursor-pointer" title="Delete Client">
                            <span class="material-symbols-outlined text-[14px]">delete</span>
                        </button>
                    </form>
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
    <!-- CLIENT ONBOARDING / REPRESENTATION INTAKE MODAL                           -->
    <!-- ========================================================================= -->
    @include('clients.partials.onboarding-modal', [
        'firms' => collect([$firm ?? auth()->user()->firm ?? null]),
        'attorneys' => $attorneys,
        'redirectTo' => 'chambers',
    ])

    <!-- ========================================================================= -->
    <!-- ASSIGN LEAD ADVOCATE & COMPLETE INTAKE MODAL                              -->
    <!-- ========================================================================= -->
    <div x-show="openAssignModal"
         x-cloak
         @click.away="openAssignModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-2xl text-[#23493a]">how_to_reg</span>
                    <div>
                        <h3 class="text-base font-semibold text-[#1a1a1a]">Assign Advocate &amp; Complete Intake</h3>
                        <p class="text-xs text-[#646864]">Assign lead counsel to <strong x-text="assignClient.name"></strong> and mark active</p>
                    </div>
                </div>
                <button type="button" @click="openAssignModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'/clients/' + assignClient.id + '/assign-attorney'" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Primary Lead Advocate *</label>
                    <select name="primary_attorney_id" x-model="assignClient.attorney_id" required class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($attorneys as $attorney)
                            <option value="{{ $attorney->id }}">{{ $attorney->name }} ({{ ucfirst($attorney->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Representation Status</label>
                    <select name="status" class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="active" selected>Active (Intake Completed &amp; Active Docket)</option>
                        <option value="lead">Keep as Lead / Pending Intake</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Intake Review &amp; Assignment Notes</label>
                    <textarea name="internal_intake_notes" x-model="assignClient.notes" rows="3" placeholder="Chamber notes on legal strategy, representation scope, or initial client consultation..."
                              class="px-3 py-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openAssignModal = false" class="btn-secondary h-8 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-8 px-4 text-xs inline-flex items-center gap-1.5 bg-[#23493a] hover:bg-[#1a382b]">
                        <span class="material-symbols-outlined text-[15px]">check_circle</span>
                        <span>Assign Advocate &amp; Activate</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
