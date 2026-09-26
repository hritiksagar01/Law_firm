<!-- ========================================================================= -->
<!-- CLIENT ONBOARDING / REPRESENTATION INTAKE MODAL                           -->
<!-- Shared across Law Firm Client Directory, Super Admin Dashboard & Directory -->
<!-- ========================================================================= -->
<div x-show="openCreateModal"
     x-cloak
     @click.away="openCreateModal = false"
     class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl border border-[#E7E4DC] shadow-2xl w-full max-w-3xl my-8 p-6 sm:p-8 flex flex-col max-h-[92vh] overflow-y-auto">

        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE8] mb-5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-[#23493A]/10 text-[#23493A] rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">person_add</span>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-[#1A1E1C]">Client Representation Intake</h3>
                    <p class="text-xs text-[#646864] mt-0.5">
                        Single/joint litigants, corporate retainers, demographics &amp; counsel assignment
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden sm:flex items-center gap-1.5">
                    <button type="button" @click="fillDemo('individual')" class="text-[11px] text-[#23493A] hover:underline flex items-center gap-1 bg-[#23493A]/5 px-2.5 py-1 rounded-[4px] font-medium border border-[#23493A]/15 cursor-pointer">
                        <span>Individual Demo</span>
                    </button>
                    <button type="button" @click="fillDemo('joint')" class="text-[11px] text-[#23493A] hover:underline flex items-center gap-1 bg-[#23493A]/5 px-2.5 py-1 rounded-[4px] font-medium border border-[#23493A]/15 cursor-pointer">
                        <span>⚡ Joint Co-Clients</span>
                    </button>
                    <button type="button" @click="fillDemo('assisted')" class="text-[11px] text-[#92400e] hover:underline flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-[4px] font-medium border border-amber-200 cursor-pointer">
                        <span>Assisted Offline</span>
                    </button>
                </div>
                <button type="button" @click="openCreateModal = false" class="p-1.5 text-[#8a8a8a] hover:text-[#1a1a1a] hover:bg-[#faf9f5] rounded-full transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>
        </div>

        <!-- Client Onboarding Form -->
        <form action="{{ route('clients.store') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true" class="flex flex-col gap-4 text-xs">
            @csrf
            <input type="hidden" name="phone" :value="(countryCode + ' ' + phoneRaw).trim()"/>
            <input type="hidden" name="redirect_to" value="{{ $redirectTo ?? (request()->is('admin*') ? 'admin' : 'chambers') }}"/>

            <!-- 0. Assigned Law Firm (When Multiple Firms Exist / Super Admin Mode) -->
            @if(isset($firms) && $firms->count() > 1)
            <div class="p-3.5 rounded-lg bg-[#FAF9F5] border border-[#E7E4DC]">
                <label class="font-semibold text-[#1A1E1C] block mb-1.5 text-xs">
                    Assigned Law Firm Chamber *
                </label>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493A] text-lg">corporate_fare</span>
                    <select name="firm_id" x-model="firmId" required class="w-full h-10 px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] focus:border-[#23493A] focus:outline-none cursor-pointer">
                        @foreach($firms as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @elseif(isset($firms) && $firms->isNotEmpty())
                <input type="hidden" name="firm_id" :value="firmId"/>
            @endif

            <!-- 1. Client Legal Entity Classification * -->
            <div>
                <label class="font-semibold text-[#1A1E1C] block mb-2 text-xs">1. Client Legal Entity Classification *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <label class="border p-3 rounded-lg flex flex-col cursor-pointer transition-all"
                           :class="category === 'individual' ? 'border-[#23493A] bg-[#23493A]/5 font-semibold text-[#23493A] shadow-2xs' : 'border-[#E7E4DC] bg-white text-[#646864] hover:bg-[#FBFBF9]'">
                        <input type="radio" name="category" value="individual" x-model="category" class="sr-only"/>
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <span class="material-symbols-outlined text-lg">person</span>
                            <span>Individual (1 Person)</span>
                        </div>
                        <span class="text-[10.5px] text-[#646864] mt-1 font-normal">Single litigant or petitioner</span>
                    </label>

                    <label class="border p-3 rounded-lg flex flex-col cursor-pointer transition-all"
                           :class="category === 'joint' ? 'border-[#23493A] bg-[#23493A]/5 font-semibold text-[#23493A] shadow-2xs' : 'border-[#E7E4DC] bg-white text-[#646864] hover:bg-[#FBFBF9]'">
                        <input type="radio" name="category" value="joint" x-model="category" @change="if(members.length === 0) addMember()" class="sr-only"/>
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <span class="material-symbols-outlined text-lg">group</span>
                            <span>Joint (2+ Litigants)</span>
                        </div>
                        <span class="text-[10.5px] text-[#646864] mt-1 font-normal">Co-petitioners / Family members</span>
                    </label>

                    <label class="border p-3 rounded-lg flex flex-col cursor-pointer transition-all"
                           :class="category === 'corporate' ? 'border-[#23493A] bg-[#23493A]/5 font-semibold text-[#23493A] shadow-2xs' : 'border-[#E7E4DC] bg-white text-[#646864] hover:bg-[#FBFBF9]'">
                        <input type="radio" name="category" value="corporate" x-model="category" class="sr-only"/>
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <span class="material-symbols-outlined text-lg">apartment</span>
                            <span>Company / LLP</span>
                        </div>
                        <span class="text-[10.5px] text-[#646864] mt-1 font-normal">Pvt Ltd, Public Ltd, LLP, OPC</span>
                    </label>

                    <label class="border p-3 rounded-lg flex flex-col cursor-pointer transition-all"
                           :class="category === 'institution' ? 'border-[#23493A] bg-[#23493A]/5 font-semibold text-[#23493A] shadow-2xs' : 'border-[#E7E4DC] bg-white text-[#646864] hover:bg-[#FBFBF9]'">
                        <input type="radio" name="category" value="institution" x-model="category" class="sr-only"/>
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <span class="material-symbols-outlined text-lg">account_balance</span>
                            <span>Trust / Society</span>
                        </div>
                        <span class="text-[10.5px] text-[#646864] mt-1 font-normal">NGO, Society, Partnership, Firm</span>
                    </label>
                </div>
            </div>

            <!-- 2. Client Digital Literacy & Onboarding Mode * -->
            <div class="p-3.5 rounded-lg bg-[#F6F4EE] border border-[#E7E4DC]">
                <label class="font-semibold text-[#1A1E1C] block mb-1.5 text-xs">2. Client Digital Literacy &amp; Onboarding Mode *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <label class="p-3 rounded-md border flex items-start gap-2.5 cursor-pointer transition-all"
                           :class="onboardingMode === 'portal_online' ? 'border-[#23493A] bg-white font-semibold text-[#23493A] shadow-2xs' : 'border-[#E7E4DC] bg-white/70 text-[#646864] hover:bg-white'">
                        <input type="radio" name="onboarding_mode" value="portal_online" x-model="onboardingMode" class="mt-0.5 text-[#23493A] focus:ring-[#23493A] cursor-pointer"/>
                        <div>
                            <span class="font-semibold text-[#1A1E1C] block">Online Client Portal</span>
                            <span class="text-[11px] text-[#646864] block mt-0.5">Client has email &amp; smartphone. Receives invitation token link to set password.</span>
                        </div>
                    </label>

                    <label class="p-3 rounded-md border flex items-start gap-2.5 cursor-pointer transition-all"
                           :class="onboardingMode === 'assisted_offline' ? 'border-[#92400e] bg-amber-50/70 font-semibold text-[#92400e] shadow-2xs' : 'border-[#E7E4DC] bg-white/70 text-[#646864] hover:bg-white'">
                        <input type="radio" name="onboarding_mode" value="assisted_offline" x-model="onboardingMode" class="mt-0.5 text-[#92400e] focus:ring-[#92400e] cursor-pointer"/>
                        <div>
                            <span class="font-semibold text-[#92400e] block">Assisted Offline (No Email / Illiterate / POA)</span>
                            <span class="text-[11px] text-[#646864] block mt-0.5">Direct chamber visits, phone/WhatsApp notifications, physical intake slip.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 3. Primary Client Information -->
            <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#23493A]">person</span>
                    <span x-text="category === 'joint' ? '03 · Primary Litigant Particulars' : '03 · Basic Particulars'"></span>
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C] min-h-[20px] flex items-center" x-text="category === 'corporate' || category === 'institution' ? 'Official Entity / Company Name *' : (category === 'joint' ? 'Title & Primary Litigant Name *' : 'Title & Full Name *')"></label>
                        <div class="flex items-center gap-1.5">
                            <select name="salutation" x-model="salutation" class="h-[40px] px-2 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] shrink-0 cursor-pointer">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Miss">Miss</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Adv.">Adv.</option>
                                <option value="Shri">Shri</option>
                                <option value="Smt.">Smt.</option>
                            </select>
                            <input name="name" x-model="name" type="text" required placeholder="e.g. Vikram Malhotra" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>

                    <!-- Parentage for Individual & Joint OR Contact Person for Corporate -->
                    <div class="flex flex-col gap-1.5" x-show="category === 'individual' || category === 'joint'">
                        <label class="text-[13px] font-medium text-[#1A1E1C] min-h-[20px] flex items-center">Title &amp; Father's / Mother's Name</label>
                        <div class="flex items-center gap-1.5">
                            <select name="father_salutation" x-model="fatherSalutation" class="h-[40px] px-2 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] shrink-0 cursor-pointer">
                                <option value="Mr.">Mr.</option>
                                <option value="Late">Late</option>
                                <option value="Shri">Shri</option>
                                <option value="Late Shri">Late Shri</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Smt.">Smt.</option>
                                <option value="Late Smt.">Late Smt.</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Adv.">Adv.</option>
                                <option value="Prof.">Prof.</option>
                                <option value="S/o">S/o</option>
                                <option value="D/o">D/o</option>
                                <option value="W/o">W/o</option>
                            </select>
                            <input name="father_husband_name" x-model="fatherHusbandName" type="text" placeholder="e.g. Jagdish Malhotra or Sunita Sharma" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5" x-show="category === 'corporate' || category === 'institution'">
                        <label class="text-[13px] font-medium text-[#1A1E1C] min-h-[20px] flex items-center">Authorized Contact Person Name</label>
                        <input name="contact_person" x-model="contactPerson" type="text" placeholder="e.g. Vikram Malhotra (Managing Director)" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">
                            Email Address <span x-show="onboardingMode === 'portal_online'" class="text-red-500">*</span>
                            <span x-show="onboardingMode === 'assisted_offline'" class="text-[11px] text-[#8a8a8a] font-normal">(Optional for Offline)</span>
                        </label>
                        <input name="email" x-model="email" type="email" :required="onboardingMode === 'portal_online'" placeholder="name@domain.com" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                    </div>

                    <!-- Country Code & Phone Input -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">
                            Mobile Contact Number <span x-show="onboardingMode === 'assisted_offline'" class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <select x-model="countryCode" class="h-[40px] px-2 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] font-mono outline-none focus:border-[#23493A]">
                                <option value="+91">🇮🇳 +91 (IN)</option>
                                <option value="+1">🇺🇸 +1 (US/CA)</option>
                                <option value="+44">🇬🇧 +44 (UK)</option>
                                <option value="+971">🇦🇪 +971 (UAE)</option>
                                <option value="+61">🇦🇺 +61 (AU)</option>
                                <option value="+65">🇸🇬 +65 (SG)</option>
                                <option value="+49">🇩🇪 +49 (DE)</option>
                                <option value="+33">🇫🇷 +33 (FR)</option>
                                <option value="+81">🇯🇵 +81 (JP)</option>
                                <option value="+966">🇸🇦 +966 (SA)</option>
                                <option value="+974">🇶🇦 +974 (QA)</option>
                                <option value="+965">🇰🇼 +965 (KW)</option>
                                <option value="+968">🇴🇲 +968 (OM)</option>
                                <option value="+973">🇧🇭 +973 (BH)</option>
                                <option value="+880">🇧🇩 +880 (BD)</option>
                                <option value="+977">🇳🇵 +977 (NP)</option>
                                <option value="+94">🇱🇰 +94 (LK)</option>
                            </select>
                            <input x-model="phoneRaw" type="text" :required="onboardingMode === 'assisted_offline'" placeholder="9876543210" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs font-mono text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>
                </div>

                <!-- Demographics: Age, Gender & Occupation (NO AADHAAR, NO PAN) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">Age (Years)</label>
                        <input name="age" x-model="age" type="number" min="0" max="130" placeholder="e.g. 35" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs font-mono text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">Gender</label>
                        <select name="gender" x-model="gender" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all cursor-pointer">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">Occupation / Vocation</label>
                        <input name="occupation" x-model="occupation" type="text" placeholder="e.g. Business / Salaried / Agriculture" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                    </div>
                </div>
            </div>

            <!-- 4. Dynamic Step: Joint / Co-Litigants Repeater Section -->
            <div x-show="category === 'joint' || members.length > 0" x-cloak class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#23493A] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">group</span>
                        <span>Co-Litigants &amp; Joint Members</span>
                    </span>
                    <button type="button" @click="addMember()" class="text-xs font-semibold text-[#23493A] hover:underline inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-[4px] border border-[#E7E4DC] shadow-2xs cursor-pointer">
                        <span class="material-symbols-outlined text-sm">group_add</span>
                        <span>+ Add Co-Client</span>
                    </button>
                </div>

                <p class="text-[11.5px] text-[#646864]">Add co-petitioners, co-owners, or joint party members representing this case dossier:</p>

                <div class="flex flex-col gap-3">
                    <template x-for="(member, index) in members" :key="index">
                        <div class="bg-white p-3 rounded-md border border-[#E7E4DC] flex flex-col gap-2.5 relative">
                            <div class="flex items-center justify-between pb-1.5 border-b border-[#F0EEE8]">
                                <span class="text-xs font-semibold text-[#1A1E1C] flex items-center gap-1">
                                    <span class="w-4 h-4 rounded-full bg-[#23493A]/10 text-[#23493A] flex items-center justify-center text-[10px] font-bold" x-text="index + 1"></span>
                                    <span>Joint Co-Client #<span x-text="index + 1"></span></span>
                                </span>
                                <button type="button" @click="removeMember(index)" class="text-xs text-red-600 hover:text-red-800 flex items-center gap-0.5 cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                    <span>Remove</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11.5px] font-medium text-[#1A1E1C]">Co-Client Full Name</label>
                                    <input :name="'members[' + index + '][name]'" x-model="member.name" type="text" required placeholder="e.g. Rajesh Sharma" class="w-full h-[36px] px-2.5 rounded-[4px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A]"/>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11.5px] font-medium text-[#1A1E1C]">Legal Relationship / Status</label>
                                    <select :name="'members[' + index + '][relationship]'" x-model="member.relationship" class="w-full h-[36px] px-2.5 rounded-[4px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A]">
                                        <option value="Co-petitioner">Co-petitioner</option>
                                        <option value="Co-respondent">Co-respondent</option>
                                        <option value="Co-owner">Co-owner / Joint Owner</option>
                                        <option value="Legal Heir">Legal Heir / Next of Kin</option>
                                        <option value="Partner">Partner / Director</option>
                                        <option value="Spouse">Spouse / Family Member</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11.5px] font-medium text-[#1A1E1C]">Mobile Contact</label>
                                    <input :name="'members[' + index + '][phone]'" x-model="member.phone" type="text" placeholder="+91 98765 00000" class="w-full h-[36px] px-2.5 rounded-[4px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A]"/>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11.5px] font-medium text-[#1A1E1C]">Email Address</label>
                                    <input :name="'members[' + index + '][email]'" x-model="member.email" type="email" placeholder="coclient@domain.com" class="w-full h-[36px] px-2.5 rounded-[4px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A]"/>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="members.length === 0" class="p-3 rounded bg-white border border-dashed border-[#cfcbc0] text-center text-xs text-[#646864]">
                        No co-clients added yet. Click <button type="button" @click="addMember()" class="text-[#23493A] font-semibold underline">"+ Add Co-Client"</button> to add joint litigants.
                    </div>
                </div>
            </div>

            <!-- 5. Counsel Assignment & Initial Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1A1E1C]">Lead Consulting Counsel</label>
                    <select name="primary_attorney_id" x-model="primaryAttorneyId" class="h-10 px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] focus:border-[#23493A] focus:outline-none cursor-pointer">
                        <option value="">-- Assign Later / General Chamber Lead --</option>
                        @if(isset($attorneys) && $attorneys->isNotEmpty())
                            @foreach($attorneys as $attorney)
                                <option value="{{ $attorney->id }}" {{ Auth::id() === $attorney->id ? 'selected' : '' }}>
                                    {{ $attorney->name }} ({{ ucfirst($attorney->role) }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1A1E1C]">Initial Lifecycle Status</label>
                    <select name="status" x-model="initialStatus" class="h-10 px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] focus:border-[#23493A] focus:outline-none cursor-pointer">
                        <option value="lead">Lead (Pending Initial Consultation)</option>
                        <option value="intake">Intake (Document &amp; Case Review)</option>
                        <option value="conflict_check">Conflict Check (Bar Clearance)</option>
                        <option value="prospective">Prospective Client</option>
                        <option value="active">Active (Retained Client)</option>
                    </select>
                </div>
            </div>

            <!-- 6. Confidential Case Summary / Representation Notes -->
            <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">gavel</span>
                        <span>Confidential Representation Notes</span>
                    </span>
                    <span class="text-[10px] text-[#23493A] font-mono bg-[#23493A]/10 px-2 py-0.5 rounded font-medium">Chambers Review</span>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[13px] font-medium text-[#1A1E1C]">Brief Facts / Dispute Summary (Confidential Counsel Review)</label>
                    <textarea name="internal_intake_notes" x-model="internalIntakeNotes" rows="2" placeholder="Briefly describe the legal matter, dispute, or representation requirements for advocate review..."
                              class="w-full p-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all resize-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-[#F0EEE8] flex items-center justify-end gap-2.5">
                <button type="button" @click="openCreateModal = false" :disabled="isSubmitting" class="btn-secondary h-10 px-4 text-xs cursor-pointer">Cancel</button>
                <button type="submit" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''" class="btn-primary h-10 px-5 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-[16px]" x-show="!isSubmitting">how_to_reg</span>
                    <span class="material-symbols-outlined text-[16px] animate-spin" x-show="isSubmitting" style="display: none;">progress_activity</span>
                    <span x-text="isSubmitting ? 'Registering Client...' : 'Complete Client Intake &amp; Onboard'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
