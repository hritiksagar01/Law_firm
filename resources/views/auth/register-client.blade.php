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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f2ed] text-[#1A1E1C] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative"
    x-data="{
        category: 'individual',
        firmId: '{{ $firms->first()?->id ?? 1 }}',
        name: 'Vikram Malhotra',
        contactPerson: 'Vikram Malhotra',
        email: 'vikram.client@gmail.com',
        phone: '+91 98765 43210',
        fillDemo() {
            this.category = 'corporate';
            this.name = 'Malhotra Enterprises Pvt Ltd';
            this.contactPerson = 'Vikram Malhotra (Director)';
            this.email = 'vikram.enterprise@gmail.com';
            this.phone = '+91 98765 12345';
            $refs.passwordInput.value = 'Client123456';
            $refs.passwordConfirmInput.value = 'Client123456';
        }
    }">

    <div class="w-full max-w-xl flex flex-col items-center my-6">
        
        <!-- Brand Emblem -->
        <div class="flex flex-col items-center text-center mb-5">
            <div class="p-3.5 bg-white rounded-2xl border border-[#E7E4DC] shadow-[0_4px_24px_-2px_rgba(26,30,28,0.08)] flex items-center justify-center mb-3">
                <img src="{{ \App\Models\PlatformSetting::logoUrl() }}" alt="{{ \App\Models\PlatformSetting::platformName() }}" class="h-16 sm:h-20 w-auto object-contain"/>
            </div>
            <h1 class="font-headline text-2xl font-semibold text-[#1A1E1C] tracking-tight">Client Portal Registration</h1>
            <p class="text-xs text-[#646864] mt-1">
                Direct Client Access for Case Tracking, Document Repository &amp; Legal Opinions
            </p>
        </div>

        <!-- Flash Notifications -->
        @if($errors->any())
        <div class="w-full mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-red-600 shrink-0">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Card Container -->
        <div class="w-full bg-white rounded-xl border border-[#E7E4DC] shadow-[0_4px_24px_-2px_rgba(26,30,28,0.06),0_0_0_1px_#E7E4DC] p-6 sm:p-7 flex flex-col relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE8] mb-5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493A] text-xl">how_to_reg</span>
                    <h2 class="font-headline text-lg font-medium text-[#1A1E1C]">Onboard as Client</h2>
                </div>
                <button type="button" @click="fillDemo()" class="text-xs text-[#23493A] hover:underline flex items-center gap-1 bg-[#23493A]/5 px-2.5 py-1 rounded-[6px] font-medium border border-[#23493A]/15 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">bolt</span>
                    <span>1-Click Demo Fill</span>
                </button>
            </div>

            <!-- Client Registration Form -->
            <form action="{{ route('register.client') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <!-- Step 1: Client Category & Law Firm Selection -->
                <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">business_center</span>
                        <span>01 · Account Category &amp; Law Firm Association</span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Client Entity Type</label>
                            <select name="category" x-model="category" required class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all">
                                <option value="individual">Individual Client</option>
                                <option value="corporate">Corporate Entity / Company</option>
                                <option value="institution">Institution / Trust / Society</option>
                                <option value="joint">Joint / Co-Clients</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Select Law Firm / Practice</label>
                            <select name="firm_id" x-model="firmId" required class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all">
                                @foreach($firms as $firm)
                                    <option value="{{ $firm->id }}">{{ $firm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Basic Client Information -->
                <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">person</span>
                        <span>02 · Basic Information</span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]" x-text="category === 'corporate' || category === 'institution' ? 'Official Entity / Company Name' : 'Full Name'"></label>
                            <input name="name" x-model="name" type="text" required placeholder="e.g. Vikram Malhotra" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Contact Person Name</label>
                            <input name="contact_person" x-model="contactPerson" type="text" placeholder="e.g. Vikram Malhotra (Director)" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Email Address</label>
                            <input name="email" x-model="email" type="email" required placeholder="name@domain.com" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Mobile Contact Number</label>
                            <input name="phone" x-model="phone" type="text" required placeholder="+91 98765 43210" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Security Credentials -->
                <div class="bg-[#F6F4EE] p-4 rounded-lg border border-[#E7E4DC] flex flex-col gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#23493A]">lock</span>
                        <span>03 · Account Security Password</span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Password</label>
                            <input name="password" x-ref="passwordInput" value="Client123456" type="password" required placeholder="Minimum 8 characters" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[13px] font-medium text-[#1A1E1C]">Confirm Password</label>
                            <input name="password_confirmation" x-ref="passwordConfirmInput" value="Client123456" type="password" required placeholder="Re-enter password" class="w-full h-[40px] px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-xs text-[#1A1E1C] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all"/>
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
                <span class="text-[#646864]">Already registered as a client?</span>
                <a href="{{ route('login') }}" class="font-semibold text-[#23493A] hover:underline flex items-center gap-1">
                    <span>Sign In to Portal</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
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
