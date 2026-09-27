@extends('layouts.app')

@section('title', 'Initiate New Case Dossier — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Initiate Matter Dossier')

@section('content')
<div class="max-w-5xl mx-auto pb-16 text-[#1a1a1a]" 
     x-data="{
        currentStep: 1,
        totalSteps: 3,
        clientId: '{{ old('client_id', $selectedClientId ?? '') }}',
        title: '{{ old('title', '') }}',
        practiceArea: '{{ old('practice_area', 'Commercial Litigation & Arbitration') }}',
        status: '{{ old('status', 'Active') }}',
        priority: '{{ old('priority', 'medium') }}',
        openedAt: '{{ old('opened_at', date('Y-m-d')) }}',
        closedAt: '{{ old('closed_at', '') }}',
        description: '{{ old('description', '') }}',
        courtName: '{{ old('court_name', '') }}',
        courtType: '{{ old('court_type', 'High Court') }}',
        jurisdiction: '{{ old('jurisdiction', 'Original Jurisdiction') }}',
        county: '{{ old('county', 'New Delhi') }}',
        state: '{{ old('state', 'Delhi (NCT)') }}',
        docketNumber: '{{ old('docket_number', '') }}',
        filingDate: '{{ old('filing_date', '') }}',
        hearingDate: '{{ old('hearing_date', '') }}',
        trialDate: '{{ old('trial_date', '') }}',
        statuteReferences: '{{ old('statute_references', '') }}',
        stage: '{{ old('stage', 'Notice & Pleadings') }}',
        leadAttorneyId: '{{ old('lead_attorney_id', auth()->id() ?? '') }}',
        supervisingAttorneyId: '{{ old('supervising_attorney_id', '') }}',
        assignedParalegalId: '{{ old('assigned_paralegal_id', '') }}',
        billingType: '{{ old('billing_type', 'flat_fee') }}',
        budget: '{{ old('budget', '150000') }}',
        selectedTypes: {{ json_encode(old('matter_types', ['Civil Litigation', 'Commercial Litigation'])) }},
        customType: '{{ old('custom_matter_type', '') }}',
        judges: {{ json_encode(old('judges', [
            ['name' => '', 'type' => 'current', 'courtroom' => 'Courtroom 14', 'notes' => 'Presiding Bench']
        ])) }},
        clientsList: {{ json_encode($clients->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->name, 'type' => $c->type, 'email' => $c->email, 'contact' => $c->contact_person])) }},
        
        get selectedClientObj() {
            return this.clientsList.find(c => String(c.id) === String(this.clientId)) || null;
        },
        toggleType(type) {
            if (this.selectedTypes.includes(type)) {
                this.selectedTypes = this.selectedTypes.filter(t => t !== type);
            } else {
                this.selectedTypes.push(type);
            }
        },
        addJudge() {
            this.judges.push({
                name: '',
                type: 'new',
                courtroom: '',
                notes: ''
            });
        },
        removeJudge(index) {
            if (this.judges.length > 1) {
                this.judges.splice(index, 1);
            }
        },
        validateStep(step) {
            if (step === 1) {
                if (!this.title.trim()) {
                    alert('Please provide the Case Title / Matter Caption (1st field).');
                    return false;
                }
                if (!this.clientId) {
                    alert('Please select a Client for this matter dossier (2nd field).');
                    return false;
                }
                if (!this.practiceArea) {
                    alert('Please select a Primary Practice Area.');
                    return false;
                }
            }
            return true;
        },
        goToStep(step) {
            if (step > this.currentStep) {
                for (let s = this.currentStep; s < step; s++) {
                    if (!this.validateStep(s)) return;
                }
            }
            this.currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
     }">

    <!-- Top Breadcrumb & Heading Ribbon -->
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#646864] hover:text-[#23493a] mb-2 font-medium transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Back to Matters Directory</span>
            </a>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Initiate New Case Dossier</h1>
            <p class="text-[13px] text-[#646864] mt-1">Enter court parameters, client relationship, judicial forum, and procedural stage</p>
        </div>

        <!-- Quick Meta Badges -->
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-[#f5f3ed] border border-[#e5e3dc] text-xs font-mono font-medium text-[#23493a]">
                <span class="material-symbols-outlined text-sm">tag</span>
                <span>Matter ID: Auto-created on submission</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-stone-100 border border-[#e5e3dc] text-[11px] font-mono text-[#646864]">
                <span>Stage: </span>
                <strong class="text-[#1a1a1a]" x-text="currentStep + ' of 3'"></strong>
            </span>
        </div>
    </div>

    <!-- Multi-Stage Stepper Progress Ribbon -->
    <div class="border border-[#e5e3dc] bg-white rounded-lg p-3 sm:p-4 mb-6 shadow-xs">
        <div class="grid grid-cols-3 gap-2">
            <!-- Step 1 Tab -->
            <button type="button" @click="goToStep(1)" 
                    class="flex items-center gap-3 p-2.5 rounded-md text-left transition-all relative overflow-hidden"
                    :class="currentStep === 1 ? 'bg-[#f5f3ed] text-[#23493a] font-semibold ring-1 ring-[#23493a]/30' : (currentStep > 1 ? 'text-[#1a1a1a] hover:bg-[#faf9f5]' : 'text-[#8a8a8a] hover:bg-[#faf9f5]')">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-mono shrink-0 transition-colors"
                     :class="currentStep === 1 ? 'bg-[#23493a] text-white shadow-xs' : (currentStep > 1 ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-stone-600')">
                    <template x-if="currentStep > 1">
                        <span class="material-symbols-outlined text-[16px]">check</span>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>1</span>
                    </template>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] uppercase tracking-wider font-mono opacity-70">Stage 01</span>
                    <span class="block text-xs sm:text-sm truncate">Matter Identification</span>
                </div>
            </button>

            <!-- Step 2 Tab -->
            <button type="button" @click="goToStep(2)" 
                    class="flex items-center gap-3 p-2.5 rounded-md text-left transition-all relative overflow-hidden"
                    :class="currentStep === 2 ? 'bg-[#f5f3ed] text-[#23493a] font-semibold ring-1 ring-[#23493a]/30' : (currentStep > 2 ? 'text-[#1a1a1a] hover:bg-[#faf9f5]' : 'text-[#8a8a8a] hover:bg-[#faf9f5]')">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-mono shrink-0 transition-colors"
                     :class="currentStep === 2 ? 'bg-[#23493a] text-white shadow-xs' : (currentStep > 2 ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-stone-600')">
                    <template x-if="currentStep > 2">
                        <span class="material-symbols-outlined text-[16px]">check</span>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>2</span>
                    </template>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] uppercase tracking-wider font-mono opacity-70">Stage 02</span>
                    <span class="block text-xs sm:text-sm truncate">Legal &amp; Judicial Forum</span>
                </div>
            </button>

            <!-- Step 3 Tab -->
            <button type="button" @click="goToStep(3)" 
                    class="flex items-center gap-3 p-2.5 rounded-md text-left transition-all relative overflow-hidden"
                    :class="currentStep === 3 ? 'bg-[#f5f3ed] text-[#23493a] font-semibold ring-1 ring-[#23493a]/30' : 'text-[#8a8a8a] hover:bg-[#faf9f5]'">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-mono shrink-0 transition-colors"
                     :class="currentStep === 3 ? 'bg-[#23493a] text-white shadow-xs' : 'bg-stone-200 text-stone-600'">
                    <span>3</span>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] uppercase tracking-wider font-mono opacity-70">Stage 03</span>
                    <span class="block text-xs sm:text-sm truncate">Staffing &amp; Confirmation</span>
                </div>
            </button>
        </div>

        <!-- Animated Progress Line -->
        <div class="w-full bg-[#f0eee8] h-1.5 rounded-full mt-3 overflow-hidden">
            <div class="bg-[#23493a] h-full transition-all duration-300 ease-out"
                 :style="'width: ' + ((currentStep / totalSteps) * 100) + '%'"></div>
        </div>
    </div>

    <!-- Server Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <span class="material-symbols-outlined text-sm">error</span>
                <span>Please correct the errors below before opening the case dossier:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 ml-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Dossier Form -->
    <form action="{{ route('matters.store') }}" method="POST" id="matterDossierForm">
        @csrf

        <!-- ========================================================================================= -->
        <!-- STAGE 1: MATTER IDENTIFICATION & CLIENT RELATIONSHIP -->
        <!-- ========================================================================================= -->
        <div x-show="currentStep === 1" x-cloak class="flex flex-col gap-6">
            
            <!-- Section 1.1: Case Title, Client & Practice Discipline -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">folder_managed</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">1.1 Case Title &amp; Client Relationship</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#8a8a8a]">Fields marked * are mandatory</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- 1ST: Case Title (Case Caption / Full Matter Name) -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Case Title / Matter Caption *</label>
                        <input type="text" name="title" x-model="title" required
                               placeholder="e.g. Malhotra Enterprises Pvt. Ltd. v. Apex Commercial Bank &amp; Ors."
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Formal judicial caption as styled on court petitions, pleadings, or legal briefs</span>
                    </div>

                    <!-- 2ND: Client / Retaining Entity -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-[#1a1a1a]">Client / Retaining Entity *</label>
                            <span class="text-[11px] text-[#646864]">Select corporate client or individual principal</span>
                        </div>
                        <select name="client_id" x-model="clientId" required 
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">Choose an onboarded client...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->name }} ({{ ucfirst($c->type) }}{{ $c->contact_person ? ' — Contact: ' . $c->contact_person : '' }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Dynamic Client Context Banner -->
                        <template x-if="selectedClientObj">
                            <div class="mt-2 p-3 rounded-md bg-[#faf8f5] border border-[#f0eee8] flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[#23493a] text-[18px]">verified_user</span>
                                    <div>
                                        <span class="font-semibold text-[#1a1a1a]" x-text="selectedClientObj.name"></span>
                                        <span class="text-[#646864] text-[11px] ml-1" x-text="'(' + selectedClientObj.type + ')'"></span>
                                        <div class="text-[11px] text-[#8a8a8a] mt-0.5" x-text="selectedClientObj.email ? 'Email: ' + selectedClientObj.email : 'No email listed'"></div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Client Verified
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- 3RD: Primary Practice Area Discipline -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Primary Practice Area *</label>
                        <select name="practice_area" x-model="practiceArea" required
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="Commercial Litigation & Arbitration">Commercial Litigation &amp; Arbitration</option>
                            <option value="Civil Litigation">Civil Litigation</option>
                            <option value="Corporate & Insolvency (IBC / NCLT)">Corporate &amp; Insolvency (IBC / NCLT)</option>
                            <option value="Criminal Defense & Bail Matters">Criminal Defense &amp; Bail Matters</option>
                            <option value="Constitutional & Writ Jurisdiction">Constitutional &amp; Writ Jurisdiction</option>
                            <option value="Banking & Financial Services">Banking &amp; Financial Services</option>
                            <option value="Real Estate & Property Law">Real Estate &amp; Property Law</option>
                            <option value="Intellectual Property & Technology">Intellectual Property &amp; Technology</option>
                            <option value="Labor & Employment Law">Labor &amp; Employment Law</option>
                            <option value="Family & Matrimonial Law">Family &amp; Matrimonial Law</option>
                            <option value="Taxation & Customs">Taxation &amp; Customs</option>
                            <option value="Regulatory & Environmental Compliance">Regulatory &amp; Environmental Compliance</option>
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Primary chambers discipline responsible for this engagement</span>
                    </div>

                    <!-- Matter ID Notice (Auto-created on submit) -->
                    <div class="md:col-span-2 p-3 rounded-md bg-[#faf8f5] border border-[#f0eee8] flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[#23493a] text-lg">tag</span>
                            <div>
                                <span class="font-semibold text-[#1a1a1a]">Matter ID / Number: </span>
                                <span class="text-[#646864]">Will be automatically generated upon creating this case dossier</span>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-[#23493a] bg-white px-2.5 py-0.5 rounded border border-[#e5e3dc]">
                            Auto-assigned (e.g. {{ $suggestedCaseNumber }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 1.2: Matter Types Checkboxes -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">checklist</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">1.2 Matter Types &amp; Practice Classifications</h2>
                    </div>
                    <span class="text-xs font-mono font-medium px-2.5 py-0.5 rounded-full bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc]">
                        <span x-text="selectedTypes.length"></span> Types Selected
                    </span>
                </div>
                <p class="text-xs text-[#646864] mb-4">
                    Select all applicable matter types and substantive practice areas corresponding to this case dossier. You may select multiple categories.
                </p>

                <!-- Checkbox Grid grouped by category -->
                <div class="space-y-4">
                    <!-- Group A: Litigation & Dispute Resolution -->
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a] block mb-2 font-medium">Dispute Resolution &amp; Litigation</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                            @php
                                $litigationTypes = [
                                    'Civil Litigation', 'Criminal', 'Family Law', 'Employment',
                                    'Personal Injury', 'Medical Malpractice', 'Commercial Litigation', 'Real Estate Litigation'
                                ];
                            @endphp
                            @foreach($litigationTypes as $type)
                            <label class="relative flex items-center gap-2 p-2.5 rounded-md border text-xs cursor-pointer select-none transition-all"
                                   :class="selectedTypes.includes('{{ $type }}') ? 'bg-[#23493a]/[0.06] border-[#23493a] text-[#23493a] font-medium shadow-xs' : 'bg-white border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5]'">
                                <input type="checkbox" name="matter_types[]" value="{{ $type }}"
                                       :checked="selectedTypes.includes('{{ $type }}')"
                                       @change="toggleType('{{ $type }}')"
                                       class="rounded text-[#23493a] focus:ring-[#23493a] border-[#e5e3dc]"/>
                                <span>{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Group B: Contracts, Corporate & Real Estate Transactions -->
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a] block mb-2 font-medium">Transactional, Corporate &amp; Estates</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                            @php
                                $transactionalTypes = [
                                    'Contracts', 'Corporate', 'M&A', 'Real Estate Transactions', 'Estate Planning'
                                ];
                            @endphp
                            @foreach($transactionalTypes as $type)
                            <label class="relative flex items-center gap-2 p-2.5 rounded-md border text-xs cursor-pointer select-none transition-all"
                                   :class="selectedTypes.includes('{{ $type }}') ? 'bg-[#23493a]/[0.06] border-[#23493a] text-[#23493a] font-medium shadow-xs' : 'bg-white border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5]'">
                                <input type="checkbox" name="matter_types[]" value="{{ $type }}"
                                       :checked="selectedTypes.includes('{{ $type }}')"
                                       @change="toggleType('{{ $type }}')"
                                       class="rounded text-[#23493a] focus:ring-[#23493a] border-[#e5e3dc]"/>
                                <span>{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Group C: Regulatory, Tax, Insolvency & Specialty -->
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a] block mb-2 font-medium">Regulatory, Fiscal &amp; Specialty Forums</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                            @php
                                $specialtyTypes = [
                                    'Immigration', 'Tax', 'Bankruptcy', 'Regulatory', 'Probate', 'Insurance'
                                ];
                            @endphp
                            @foreach($specialtyTypes as $type)
                            <label class="relative flex items-center gap-2 p-2.5 rounded-md border text-xs cursor-pointer select-none transition-all"
                                   :class="selectedTypes.includes('{{ $type }}') ? 'bg-[#23493a]/[0.06] border-[#23493a] text-[#23493a] font-medium shadow-xs' : 'bg-white border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5]'">
                                <input type="checkbox" name="matter_types[]" value="{{ $type }}"
                                       :checked="selectedTypes.includes('{{ $type }}')"
                                       @change="toggleType('{{ $type }}')"
                                       class="rounded text-[#23493a] focus:ring-[#23493a] border-[#e5e3dc]"/>
                                <span>{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Group D: Firm-defined custom type input -->
                    <div class="pt-2">
                        <label class="text-xs font-semibold text-[#1a1a1a] mb-1.5 block">Other Firm-Defined Matter Type</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="custom_matter_type" x-model="customType"
                                   placeholder="e.g. Maritime &amp; Admiralty, Cyber Law &amp; Data Privacy..."
                                   class="flex-1 h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                            <button type="button" @click="if(customType.trim() && !selectedTypes.includes(customType.trim())) { selectedTypes.push(customType.trim()); customType = ''; }"
                                    class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">add</span>
                                <span>Add Custom Type</span>
                            </button>
                        </div>
                        <span class="text-[10.5px] text-[#8a8a8a]">Specify custom specialized litigation or transaction categories defined by your firm</span>
                    </div>
                </div>
            </div>

            <!-- Section 1.3: Lifecycle Status, Priority & Dates -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">flag</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">1.3 Lifecycle Status, Priority &amp; Dates</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#8a8a8a]">Procedural tracking</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Status Selector (Recommended matter statuses) -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Matter Status *</label>
                        <select name="status" x-model="status" required
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] font-medium">
                            @php
                                $recommendedStatuses = [
                                    'Prospective', 'Intake', 'Conflict Check', 'Open', 'Active', 'Pending',
                                    'On Hold', 'Settled', 'Resolved', 'Closed', 'Archived'
                                ];
                            @endphp
                            @foreach($recommendedStatuses as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Current operational posture of this matter</span>
                    </div>

                    <!-- Priority Segmented Selector -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Priority Level *</label>
                        <input type="hidden" name="priority" :value="priority"/>
                        <div class="grid grid-cols-4 gap-1 p-1 rounded-md bg-[#faf8f5] border border-[#e5e3dc] h-10">
                            <button type="button" @click="priority = 'low'"
                                    class="rounded text-[11px] font-medium transition-colors flex items-center justify-center gap-1"
                                    :class="priority === 'low' ? 'bg-white shadow-xs text-stone-700 font-semibold border border-stone-200' : 'text-[#646864] hover:text-[#1a1a1a]'">
                                Low
                            </button>
                            <button type="button" @click="priority = 'medium'"
                                    class="rounded text-[11px] font-medium transition-colors flex items-center justify-center gap-1"
                                    :class="priority === 'medium' ? 'bg-white shadow-xs text-blue-700 font-semibold border border-blue-200' : 'text-[#646864] hover:text-[#1a1a1a]'">
                                Med
                            </button>
                            <button type="button" @click="priority = 'high'"
                                    class="rounded text-[11px] font-medium transition-colors flex items-center justify-center gap-1"
                                    :class="priority === 'high' ? 'bg-white shadow-xs text-amber-700 font-semibold border border-amber-200' : 'text-[#646864] hover:text-[#1a1a1a]'">
                                High
                            </button>
                            <button type="button" @click="priority = 'urgent'"
                                    class="rounded text-[11px] font-medium transition-colors flex items-center justify-center gap-1"
                                    :class="priority === 'urgent' ? 'bg-white shadow-xs text-rose-700 font-semibold border border-rose-200' : 'text-[#646864] hover:text-[#1a1a1a]'">
                                Urgent
                            </button>
                        </div>
                        <span class="text-[10.5px] text-[#8a8a8a]">Triaging and escalation priority</span>
                    </div>

                    <!-- Open Date -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Open / Retainer Date *</label>
                        <input type="date" name="opened_at" x-model="openedAt" required
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Formal retention or commencement date</span>
                    </div>

                    <!-- Target / Close Date (Optional) -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Target Disposition / Close Date</label>
                        <input type="date" name="closed_at" x-model="closedAt"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Leave blank for active ongoing matters</span>
                    </div>

                    <!-- Description & Objectives -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Matter Description &amp; Objectives</label>
                        <textarea name="description" x-model="description" rows="3"
                                  placeholder="Summary of factual dispute, remedies sought, interim injunction targets, or transaction brief..."
                                  class="w-full p-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] leading-relaxed"></textarea>
                    </div>
                </div>
            </div>

            <!-- Stage 1 Footer Navigation -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('matters.index') }}" class="btn-secondary h-10 px-4 text-xs">
                    Cancel
                </a>
                <button type="button" @click="goToStep(2)" class="btn-primary h-10 px-5 text-xs inline-flex items-center gap-1.5">
                    <span>Next: Legal &amp; Judicial Details</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================================= -->
        <!-- STAGE 2: LEGAL DETAILS & JUDICIAL FORUM -->
        <!-- ========================================================================================= -->
        <div x-show="currentStep === 2" x-cloak class="flex flex-col gap-6">

            <!-- Section 2.1: Court Parameters & Judicial Forum -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">gavel</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">2.1 Court Parameters &amp; Judicial Forum</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#8a8a8a]">Forum jurisdiction &amp; docket info</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Court Name -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Court / Tribunal Name</label>
                        <input type="text" name="court_name" x-model="courtName"
                               placeholder="e.g. High Court of Delhi, New Delhi or Supreme Court of India"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Formal judicial seat where the matter is pending or instituted</span>
                    </div>

                    <!-- Court Type -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Court Type</label>
                        <select name="court_type" x-model="courtType"
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="Supreme Court">Supreme Court</option>
                            <option value="High Court" selected>High Court</option>
                            <option value="District & Sessions Court">District &amp; Sessions Court</option>
                            <option value="Commercial Court / Division">Commercial Court / Division</option>
                            <option value="National Company Law Tribunal (NCLT / NCLAT)">National Company Law Tribunal (NCLT / NCLAT)</option>
                            <option value="Debt Recovery Tribunal (DRT / DRAT)">Debt Recovery Tribunal (DRT / DRAT)</option>
                            <option value="Appellate Tribunal">Appellate Tribunal</option>
                            <option value="Family Court">Family Court</option>
                            <option value="Magistrate Court">Magistrate Court</option>
                            <option value="Arbitration Tribunal">Arbitration Tribunal</option>
                            <option value="Consumer Disputes Forum">Consumer Disputes Forum</option>
                            <option value="Labor Court / Industrial Tribunal">Labor Court / Industrial Tribunal</option>
                            <option value="Other Judicial Authority">Other Judicial Authority</option>
                        </select>
                    </div>

                    <!-- Jurisdiction -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Jurisdiction</label>
                        <select name="jurisdiction" x-model="jurisdiction"
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="Original Jurisdiction">Original Jurisdiction</option>
                            <option value="Appellate Jurisdiction">Appellate Jurisdiction</option>
                            <option value="Writ Jurisdiction">Writ Jurisdiction</option>
                            <option value="Commercial Jurisdiction">Commercial Jurisdiction</option>
                            <option value="Territorial Jurisdiction">Territorial Jurisdiction</option>
                            <option value="Insolvency / Corporate">Insolvency / Corporate</option>
                            <option value="Arbitral Jurisdiction">Arbitral Jurisdiction</option>
                        </select>
                    </div>

                    <!-- County / District -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">County / District</label>
                        <input type="text" name="county" x-model="county"
                               placeholder="e.g. New Delhi District or Central District"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                    </div>

                    <!-- State / Province -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">State / Territory</label>
                        <input type="text" name="state" x-model="state"
                               placeholder="e.g. Delhi (NCT), Maharashtra, Karnataka"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                    </div>

                    <!-- Official Case / Docket Number -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Court Case / Docket Number</label>
                        <input type="text" name="docket_number" x-model="docketNumber"
                               placeholder="e.g. WP(C) No. 4920/2026 or CS(COMM) 214/2026"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Official filing / diary / docket number stamped by the court registry</span>
                    </div>
                </div>
            </div>

            <!-- Section 2.2: Presiding, Previous & New Judges (Dynamic Bench Assignment) -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">balance</span>
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">2.2 Judges, Magistrates &amp; Bench History</h2>
                            <p class="text-[11.5px] text-[#646864] mt-0.5">Manage current presiding bench, previous recused/transferred judges, and successor officers</p>
                        </div>
                    </div>
                    <button type="button" @click="addJudge()" 
                            class="btn-secondary h-8 px-3 text-xs inline-flex items-center gap-1.5 text-[#23493a] border-emerald-300 hover:bg-emerald-50">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Add Another Judge / Officer</span>
                    </button>
                </div>

                <!-- Hidden field for primary judge backwards compatibility -->
                <input type="hidden" name="judge_name" :value="judges[0]?.name || ''"/>

                <!-- Dynamic Judges Cards List -->
                <div class="space-y-3">
                    <template x-for="(j, index) in judges" :key="index">
                        <div class="p-4 rounded-md border border-[#e5e3dc] bg-[#faf8f5]/60 hover:bg-[#faf8f5] transition-colors relative">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-[#f0eee8]">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-white border border-[#e5e3dc] text-[#23493a]" x-text="'Judge #' + (index + 1)"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase"
                                          :class="j.type === 'current' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (j.type === 'previous' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200')"
                                          x-text="j.type === 'current' ? 'Current Presiding' : (j.type === 'previous' ? 'Previous Judge' : 'New / Successor')">
                                    </span>
                                </div>
                                <template x-if="judges.length > 1">
                                    <button type="button" @click="removeJudge(index)" 
                                            class="text-xs text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50 transition-colors inline-flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                        <span>Remove</span>
                                    </button>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <!-- Judge Status / Role Selection -->
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-semibold text-[#1a1a1a]">Judge Status / Role *</label>
                                    <select :name="'judges[' + index + '][type]'" x-model="j.type" required
                                            class="w-full h-9 px-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                                        <option value="current">Current Presiding Judge</option>
                                        <option value="previous">Previous Judge (Transferred / Recused)</option>
                                        <option value="new">New Judge (Successor Bench / Assigned)</option>
                                        <option value="magistrate">Magistrate / Associate Judge</option>
                                        <option value="division_bench">Division Bench Colleague</option>
                                        <option value="appellate">Appellate / Reviewing Officer</option>
                                    </select>
                                </div>

                                <!-- Judge Name -->
                                <div class="flex flex-col gap-1 md:col-span-2">
                                    <label class="text-[11px] font-semibold text-[#1a1a1a]">Judge / Judicial Officer Name *</label>
                                    <input type="text" :name="'judges[' + index + '][name]'" x-model="j.name" required
                                           placeholder="e.g. Hon. Justice C. Hari Shankar or Hon. Magistrate R. K. Goel"
                                           class="w-full h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                                </div>

                                <!-- Courtroom / Bench -->
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-semibold text-[#1a1a1a]">Courtroom / Division</label>
                                    <input type="text" :name="'judges[' + index + '][courtroom]'" x-model="j.courtroom"
                                           placeholder="e.g. Courtroom 24 / Bench II"
                                           class="w-full h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                                </div>

                                <!-- Tenure / Transfer Notes -->
                                <div class="flex flex-col gap-1 md:col-span-4">
                                    <label class="text-[11px] font-semibold text-[#1a1a1a]">Tenure / Reassignment / Recusal Notes</label>
                                    <input type="text" :name="'judges[' + index + '][notes]'" x-model="j.notes"
                                           placeholder="e.g. Presided Jan 2024 to Feb 2026 before roster change; matter re-allocated to Court 12"
                                           class="w-full h-8 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#646864] focus:outline-none focus:border-[#23493a]"/>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Section 2.3: Procedural Schedule Dates & Statute References -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">event_upcoming</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">2.3 Appearance Dates &amp; Statutory References</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#8a8a8a]">Procedural deadlines</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Filing Date -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Initial Filing Date</label>
                        <input type="date" name="filing_date" x-model="filingDate"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Date stamped on court petition</span>
                    </div>

                    <!-- Hearing Date -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">First / Next Hearing Date</label>
                        <input type="date" name="hearing_date" x-model="hearingDate"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Next listed appearance before bench</span>
                    </div>

                    <!-- Trial Date -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Trial / Final Arguments Date</label>
                        <input type="date" name="trial_date" x-model="trialDate"
                               class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        <span class="text-[10.5px] text-[#8a8a8a]">Scheduled trial or final decree hearing</span>
                    </div>

                    <!-- Statute / Rule References -->
                    <div class="flex flex-col gap-1.5 md:col-span-3">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Statute / Rule References</label>
                        <textarea name="statute_references" x-model="statuteReferences" rows="2"
                                  placeholder="e.g. Code of Civil Procedure 1908 (Sec 9, 89, Order XXXIX Rules 1 &amp; 2); Arbitration and Conciliation Act 1996 (Sec 9, 11, 34); Companies Act 2013"
                                  class="w-full p-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a] leading-relaxed"></textarea>
                        <span class="text-[10.5px] text-[#8a8a8a]">Applicable legal sections, statutory frameworks, precedents, and rules invoked</span>
                    </div>
                </div>
            </div>

            <!-- Stage 2 Footer Navigation -->
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="goToStep(1)" class="btn-secondary h-10 px-4 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Previous: Matter Identification</span>
                </button>
                <button type="button" @click="goToStep(3)" class="btn-primary h-10 px-5 text-xs inline-flex items-center gap-1.5">
                    <span>Next: Staffing &amp; Review</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================================= -->
        <!-- STAGE 3: PROCEDURAL STAGE, STAFFING & REVIEW -->
        <!-- ========================================================================================= -->
        <div x-show="currentStep === 3" x-cloak class="flex flex-col gap-6">

            <!-- Section 3.1: Procedural Stage & Staffing -->
            <div class="border border-[#e5e3dc] bg-white rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">account_tree</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">3.1 Procedural Stage &amp; Counsel Assignment</h2>
                    </div>
                    <span class="text-[11px] font-mono text-[#8a8a8a]">Legal staffing</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Procedural Stage Selector -->
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Current Procedural Stage *</label>
                        <select name="stage" x-model="stage" required
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="Filing / Pre-Admission">Filing / Pre-Admission</option>
                            <option value="Notice & Pleadings" selected>Notice &amp; Pleadings</option>
                            <option value="Discovery & Evidence">Discovery &amp; Evidence</option>
                            <option value="Arguments & Hearing">Arguments &amp; Hearing</option>
                            <option value="Pre-Trial">Pre-Trial</option>
                            <option value="Trial">Trial</option>
                            <option value="Judgment & Decree">Judgment &amp; Decree</option>
                            <option value="Appeal">Appeal</option>
                            <option value="Execution / Enforcement">Execution / Enforcement</option>
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Current posture in the litigation or transactional milestone lifecycle</span>
                    </div>

                    <!-- Responsible / Supervising Attorney (Partner) -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Responsible / Supervising Attorney</label>
                        <select name="supervising_attorney_id" x-model="supervisingAttorneyId"
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">Select supervising partner...</option>
                            @foreach($supervisingAttorneys as $sa)
                                <option value="{{ $sa->id }}">{{ $sa->name }} ({{ ucfirst($sa->role) }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Partner maintaining overall chamber oversight</span>
                    </div>

                    <!-- Lead Attorney / Counsel -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Lead Advocate / Counsel *</label>
                        <select name="lead_attorney_id" x-model="leadAttorneyId" required
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            @foreach($attorneys as $at)
                                <option value="{{ $at->id }}" {{ $at->id == auth()->id() ? 'selected' : '' }}>
                                    {{ $at->name }} ({{ ucfirst($at->role) }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Day-to-day counsel handling hearings and drafting</span>
                    </div>

                    <!-- Assigned Paralegal / Staff -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Assigned Paralegal / Staff</label>
                        <select name="assigned_paralegal_id" x-model="assignedParalegalId"
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="">Select assigned staff...</option>
                            @foreach($paralegals as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->role) }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10.5px] text-[#8a8a8a]">Staff member handling filings, docketing, and briefs</span>
                    </div>

                    <!-- Billing Type & Budget -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Commercial Billing Type *</label>
                        <select name="billing_type" x-model="billingType" required
                                class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                            <option value="flat_fee">Fixed / Stage-wise Flat Fee</option>
                            <option value="hourly">Hourly Rate Billing</option>
                            <option value="contingency">Contingency / Success Fee</option>
                            <option value="retainer">Monthly General Retainer</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-[#1a1a1a]">Estimated Matter Budget / Retainer Value</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-sm text-[#8a8a8a]">payments</span>
                            <input type="number" step="0.01" name="budget" x-model="budget"
                                   placeholder="e.g. 150000"
                                   class="w-full h-10 pl-9 pr-3 rounded-md bg-white border border-[#e5e3dc] text-xs font-mono text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
                        </div>
                    </div>
                </div>

                <!-- Additional Legal Team Members Selection -->
                <div class="mt-5 pt-4 border-t border-[#f0eee8]">
                    <label class="text-xs font-semibold text-[#1a1a1a] block mb-2">Additional Chambers Team Members</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @foreach($allStaff as $st)
                            <label class="flex items-center gap-2 p-2 rounded border border-[#e5e3dc] hover:bg-[#faf9f5] cursor-pointer text-xs transition-colors">
                                <input type="checkbox" name="team_members[]" value="{{ $st->id }}"
                                       class="rounded text-[#23493a] focus:ring-[#23493a] border-[#e5e3dc]"/>
                                <span class="truncate">{{ $st->name }} ({{ ucfirst($st->role) }})</span>
                            </label>
                        @endforeach
                    </div>
                    <span class="text-[10.5px] text-[#8a8a8a] mt-1 block">Staff checked above will be granted active workspace dossier access</span>
                </div>
            </div>

            <!-- Section 3.2: Executive Dossier Preview & Final Confirmation Card -->
            <div class="border border-[#e5e3dc] bg-[#faf8f5] rounded-lg p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#e5e3dc]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">verified</span>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#1a1a1a]">3.2 Executive Dossier Confirmation</h2>
                    </div>
                    <span class="text-xs font-mono text-[#23493a] font-medium">Ready to Initiate</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <!-- Column 1: Case & Client Profile -->
                    <div class="bg-white p-4 rounded-md border border-[#e5e3dc] flex flex-col gap-2.5">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a]">Matter Summary</span>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px]">Case Caption</span>
                            <span class="font-semibold text-[#1a1a1a] text-sm" x-text="title || 'Not specified'"></span>
                        </div>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px]">Client</span>
                            <span class="text-[#1a1a1a] font-medium" x-text="selectedClientObj ? selectedClientObj.name : 'No client selected'"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Matter Number</span>
                                <span class="font-mono text-[#23493a] font-medium">Auto-generated</span>
                            </div>
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Status</span>
                                <span class="font-semibold text-[#1a1a1a]" x-text="status"></span>
                            </div>
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Priority</span>
                                <span class="uppercase font-mono text-[10px] font-semibold"
                                      :class="priority === 'urgent' ? 'text-rose-700' : 'text-[#23493a]'"
                                      x-text="priority"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px]">Practice Discipline</span>
                            <span class="text-[#1a1a1a]" x-text="practiceArea"></span>
                        </div>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px] mb-1">Matter Types</span>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="t in selectedTypes" :key="t">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc]" x-text="t"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Judicial Forum & Schedule -->
                    <div class="bg-white p-4 rounded-md border border-[#e5e3dc] flex flex-col gap-2.5">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#8a8a8a]">Judicial Forum &amp; Bench</span>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px]">Court / Seat</span>
                            <span class="font-medium text-[#1a1a1a]" x-text="courtName || 'Court not specified'"></span>
                            <span class="text-[#646864] text-[11px] ml-1" x-text="'(' + courtType + ')'"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Docket / Filing #</span>
                                <span class="font-mono text-[#1a1a1a]" x-text="docketNumber || 'Not filed yet'"></span>
                            </div>
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Jurisdiction</span>
                                <span class="text-[#1a1a1a]" x-text="jurisdiction"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[#8a8a8a] block text-[11px] mb-1">Presiding &amp; Bench Judges</span>
                            <div class="space-y-1">
                                <template x-for="j in judges" :key="j.name">
                                    <div class="flex items-center justify-between text-[11px] py-1 border-b border-[#f0eee8]">
                                        <span class="font-medium text-[#1a1a1a]" x-text="j.name || 'Unnamed Judge'"></span>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono uppercase"
                                              :class="j.type === 'current' ? 'bg-emerald-50 text-emerald-700' : (j.type === 'previous' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700')"
                                              x-text="j.type"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-1">
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">Procedural Stage</span>
                                <span class="font-medium text-[#23493a]" x-text="stage"></span>
                            </div>
                            <div>
                                <span class="text-[#8a8a8a] block text-[11px]">First Listing</span>
                                <span class="font-mono text-[#1a1a1a]" x-text="hearingDate || 'Pending'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 3 Footer Navigation & Submit -->
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="goToStep(2)" class="btn-secondary h-10 px-4 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Previous: Legal Details</span>
                </button>
                <div class="flex items-center gap-3">
                    <a href="{{ route('matters.index') }}" class="btn-secondary h-10 px-4 text-xs">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary h-10 px-6 text-xs inline-flex items-center gap-2 shadow-sm font-semibold">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span>Create &amp; Open Case Dossier</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
