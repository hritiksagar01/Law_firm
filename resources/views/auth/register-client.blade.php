<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Client Portal Registration — {{ \App\Models\PlatformSetting::platformName() }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Models\PlatformSetting::logoUrl() }}"/>

    <!-- Google Fonts: Inter, Newsreader & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black/65 backdrop-blur-xs text-[#1A1E1C] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 relative selection:bg-[#23493A]/10 selection:text-[#23493A]"
    x-data="{
        category: 'individual',
        onboardingMode: 'portal_online',
        firmId: '{{ $firms->first()?->id ?? 1 }}',
        name: 'Vikram Malhotra',
        contactPerson: 'Vikram Malhotra',
        email: 'vikram.client@gmail.com',
        countryCode: '+91',
        phoneRaw: '9876543210',
        age: 38,
        gender: 'male',
        occupation: 'Business & Commercial',
        members: [],
        addMember() {
            this.members.push({ name: '', relationship: 'Co-petitioner', phone: '', email: '' });
        },
        removeMember(index) {
            this.members.splice(index, 1);
        },
        passcode: ['1', '2', '3', '4', '5', '6'],
        showPasscode: false,
        fullPassword: '123456',
        fillDemo(type) {
            if (type === 'joint') {
                this.category = 'joint';
                this.name = 'Vikram & Rajesh (Joint Litigants)';
                this.contactPerson = 'Vikram Malhotra (Primary Litigant)';
                this.email = 'joint.litigants@gmail.com';
                this.countryCode = '+91';
                this.phoneRaw = '9811099887';
                this.age = 41;
                this.gender = 'male';
                this.occupation = 'Property Owner & Business';
                this.members = [
                    { name: 'Rajesh Sharma', relationship: 'Co-petitioner / Co-owner', phone: '+91 98765 11223', email: 'rajesh.sharma@gmail.com' },
                    { name: 'Sanjay Malhotra', relationship: 'Co-litigant / Relative', phone: '+91 98100 44556', email: 'sanjay.m@gmail.com' }
                ];
            } else if (type === 'assisted') {
                this.category = 'individual';
                this.onboardingMode = 'assisted_offline';
                this.name = 'Chandra Sekhar (Assisted Intake)';
                this.contactPerson = 'Chandra Sekhar';
                this.email = 'chandra.assisted@gmail.com';
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
                this.email = 'vikram.client@gmail.com';
                this.countryCode = '+91';
                this.phoneRaw = '9876543210';
                this.age = 38;
                this.gender = 'male';
                this.occupation = 'Business & Commercial';
                this.members = [];
            }
            this.passcode = ['1', '2', '3', '4', '5', '6'];
            this.fullPassword = '123456';
        },
        handleInput(e, index) {
            const val = e.target.value;
            if (val.length > 1) {
                const pasted = val.split('').slice(0, 6);
                pasted.forEach((ch, i) => {
                    if (i < 6) this.passcode[i] = ch;
                });
                this.fullPassword = this.passcode.join('');
                const nextIdx = Math.min(5, pasted.length);
                this.$refs['box' + nextIdx]?.focus();
                return;
            }
            this.passcode[index] = val;
            this.fullPassword = this.passcode.join('');
            if (val && index < 5) {
                this.$refs['box' + (index + 1)]?.focus();
            }
        },
        handleKeyDown(e, index) {
            if (e.key === 'Backspace' && !this.passcode[index] && index > 0) {
                this.$refs['box' + (index - 1)]?.focus();
            }
        },
        handlePaste(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (!text) return;
            const chars = text.split('').slice(0, 6);
            chars.forEach((ch, i) => {
                if (i < 6) this.passcode[i] = ch;
            });
            this.fullPassword = text;
            const focusIdx = Math.min(5, chars.length);
            this.$refs['box' + focusIdx]?.focus();
        }
    }">

    <!-- Modal Overlay Container -->
    <div class="w-full max-w-3xl flex flex-col items-center my-4">

        <!-- Flash Notifications -->
        @if($errors->any())
        <div class="w-full mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-red-600 shrink-0">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Card Container (Modal Overlay Window) -->
        <div class="w-full bg-white rounded-2xl border border-[#E7E4DC] shadow-2xl p-6 sm:p-8 flex flex-col relative max-h-[92vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE8] mb-5">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-[#23493A]/10 text-[#23493A] rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">person_add</span>
                    </div>
                    <div>
                        <h1 class="font-headline text-xl font-semibold text-[#1A1E1C]">Client Representation Intake</h1>
                        <p class="text-xs text-[#646864] mt-0.5">
                            Single/joint litigants, corporate retainers, identity KYC, and assisted offline onboarding
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1.5">
                        <button type="button" @click="fillDemo('individual')" class="text-[11px] text-[#23493A] hover:underline flex items-center gap-1 bg-[#23493A]/5 px-2.5 py-1 rounded-[4px] font-medium border border-[#23493A]/15 cursor-pointer">
                            <span>Individual Demo</span>
                        </button>
                        <button type="button" @click="fillDemo('joint')" class="text-[11px] text-[#23493A] hover:underline flex items-center gap-1 bg-[#23493A]/5 px-2.5 py-1 rounded-[4px] font-medium border border-[#23493A]/15 cursor-pointer">
                            <span>⚡ Joint Co-Clients</span>
                        </button>
                    </div>
                    <a href="{{ route('login') }}" class="p-1.5 text-[#8A8E89] hover:text-[#1A1E1C] hover:bg-[#F4F2ED] rounded-full transition-all cursor-pointer" title="Return to Sign In">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </a>
                </div>
            </div>

            <!-- Client Registration Form -->
            <form action="{{ route('register.client') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="firm_id" :value="firmId"/>
                <input type="hidden" name="phone" :value="countryCode + ' ' + phoneRaw"/>

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

                <!-- Step 2: Primary Client Information (Includes Country Code, DOB & Age) -->
                <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">person</span>
                        <span x-text="category === 'joint' ? '02 · Primary Litigant Particulars' : '02 · Basic Particulars'"></span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]" x-text="category === 'corporate' || category === 'institution' ? 'Official Entity / Company Name' : (category === 'joint' ? 'Primary Litigant / Client Name' : 'Full Name')"></label>
                            <input name="name" x-model="name" type="text" required placeholder="e.g. Vikram Malhotra" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Contact Person Name</label>
                            <input name="contact_person" x-model="contactPerson" type="text" placeholder="e.g. Vikram Malhotra (Primary)" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Email Address</label>
                            <input name="email" x-model="email" type="email" required placeholder="name@domain.com" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>

                        <!-- Country Code & Phone Input -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Mobile Contact Number</label>
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
                                <input x-model="phoneRaw" type="text" required placeholder="9876543210" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs font-mono text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                            </div>
                        </div>
                    </div>

                    <!-- Demographics: Age, Gender & Occupation -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Age (Years)</label>
                            <input name="age" x-model="age" type="number" min="0" max="130" placeholder="e.g. 35" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs font-mono text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Gender</label>
                            <select name="gender" x-model="gender" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Occupation / Vocation</label>
                            <input name="occupation" x-model="occupation" type="text" placeholder="e.g. Business / Salaried" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Step: Joint / Co-Litigants Repeater Section -->
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

                <!-- Step 3: Security 6-Digit Passcode -->
                <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">lock</span>
                        <span>03 · Set 6-Digit Passcode</span>
                    </span>

                    <div class="flex flex-col gap-2">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">6-Digit Passcode</label>
                        
                        <!-- 6 Staggered Digit Boxes -->
                        <div class="flex items-center justify-between gap-1.5 sm:gap-2" @paste="handlePaste($event)">
                            <template x-for="(digit, index) in passcode" :key="index">
                                <input :type="showPasscode ? 'text' : 'password'"
                                       maxlength="1"
                                       inputmode="numeric"
                                       :x-ref="'box' + index"
                                       :value="passcode[index]"
                                       @input="handleInput($event, index)"
                                       @keydown="handleKeyDown($event, index)"
                                       class="w-11 sm:w-12 h-12 text-center text-lg font-mono font-semibold rounded-[6px] bg-white border border-[#E7E4DC] text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all shadow-xs"
                                       :class="passcode[index] ? 'border-[#23493A] bg-[#23493A]/5 text-[#23493A]' : ''" />
                            </template>
                        </div>

                        <!-- Hidden sync inputs for backend form submission -->
                        <input type="hidden" name="password" :value="fullPassword" id="password" />
                        <input type="hidden" name="password_confirmation" :value="fullPassword" id="password_confirmation" />

                        <div class="flex items-center justify-between mt-1">
                            <button type="button" @click="showPasscode = !showPasscode"
                                class="text-[11.5px] text-[#646864] hover:text-[#1A1E1C] flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-[15px]" x-text="showPasscode ? 'visibility_off' : 'visibility'">visibility</span>
                                <span x-text="showPasscode ? 'Hide passcode' : 'Show passcode'">Show passcode</span>
                            </button>
                            <span class="text-[11px] text-[#8A8E89]">Secure 6-digit credentials</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-[#23493A]/5 rounded-[6px] border border-[#23493A]/15 flex items-start gap-2 text-xs text-[#23493A]">
                    <span class="material-symbols-outlined text-base text-[#23493A] shrink-0 mt-0.5">verified_user</span>
                    <p class="leading-relaxed">
                        Your client account provides 256-bit encrypted access to track case proceedings, review legal opinion drafts, and upload requested documents.
                    </p>
                </div>

                <button type="submit" class="w-full h-11 rounded-[6px] bg-[#23493A] hover:bg-[#1B3B2F] text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.99] mt-1 cursor-pointer">
                    <span>Create Client Account &amp; Access Portal</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-[#F0EEE8] flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="font-semibold text-[#23493A] hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Back to Sign In</span>
                </a>
                <span class="text-[#646864]">Already registered as a client?</span>
            </div>

        </div>

    </div>

    <!-- Public Footer -->
    <footer class="w-full max-w-[500px] text-center my-4 text-[12px] space-y-1.5">
        <div class="flex items-center justify-center gap-3 text-[11.5px] text-[#646864]">
            <a href="{{ route('public.about') }}" target="_blank" class="hover:text-[#23493A] transition-colors">About</a>
            <span>&middot;</span>
            <a href="{{ route('public.contact') }}" target="_blank" class="hover:text-[#23493A] transition-colors">Contact Registry</a>
            <span>&middot;</span>
            <a href="{{ route('public.privacy') }}" target="_blank" class="hover:text-[#23493A] transition-colors">Privilege &amp; Privacy</a>
            <span>&middot;</span>
            <a href="{{ route('public.terms') }}" target="_blank" class="hover:text-[#23493A] transition-colors">Terms</a>
        </div>
        <p class="text-[11px] text-[#8A8E89]">
            &copy; {{ date('Y') }} {{ \App\Models\PlatformSetting::platformName() }}. All rights reserved.
        </p>
        <p class="text-[11px] text-[#8A8E89]">
            Designed and Developed by 
            <a href="https://skybridgeit.com/" target="_blank" rel="noopener noreferrer" 
               class="text-[#8A8E89] hover:text-[#1A1E1C] underline">Skybridge IT Consulting</a>
        </p>
    </footer>

</body>
</html>
