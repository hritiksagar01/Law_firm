<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register Law Practice — {{ config('legal.app_name', 'Law Firm Management') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#222222] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative" x-data="{
    firmName: 'Verma & Associates, Advocates & Solicitors',
    practiceArea: 'Commercial Litigation & Arbitration',
    barNumber: 'D/1892/2010 (Bar Council of Delhi)',
    partnerName: 'Adv. Anand Verma',
    email: 'anand@vermaassociates.in',
    fillDemo() {
        this.firmName = 'Kapur & Partners, Advocates';
        this.practiceArea = 'Corporate & Insolvency (IBC / NCLT)';
        this.barNumber = 'D/2419/2012 (Bar Council of Delhi)';
        this.partnerName = 'Adv. Vikram Kapur';
        this.email = 'vikram@kapurpartners.in';
        $refs.passwordInput.value = 'Chambers@2026';
        $refs.passwordConfirmInput.value = 'Chambers@2026';
    }
}">

    <!-- Atmospheric Warm Blur Accents -->
    <div class="absolute top-1/6 -left-16 w-96 h-96 bg-[#F4ECE1]/25 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute bottom-10 -right-12 w-96 h-96 bg-[#B88B56]/20 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="w-full max-w-xl flex flex-col items-center my-8">
        
        <!-- Brand Emblem -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#845D33] flex items-center justify-center text-white shadow-md mb-3">
                <span class="material-symbols-outlined text-2xl text-[#B88B56]">balance</span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#222222] tracking-tight">Establish Your Law Practice</h1>
            <p class="text-[11px] font-mono uppercase tracking-widest text-[#766A5E] font-medium mt-1">
                Multi-Advocate Chambers · Court Dockets · Client Trust Ledger
            </p>
        </div>

        <!-- Flash & Validation Notifications -->
        @if($errors->any())
        <div class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base shrink-0">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Registration Dossier Card -->
        <div class="w-full bg-white rounded-xl shadow-xl border border-[#EFECE6] p-6 sm:p-8 flex flex-col relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-6">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#845D33] text-white flex items-center justify-center font-mono text-[11px] font-bold">§</span>
                    <h2 class="text-sm font-semibold text-[#222222]">Law Practice Onboarding Protocol</h2>
                </div>
                <button type="button" @click="fillDemo()" class="text-[11px] font-mono text-[#845D33] hover:underline flex items-center gap-1 bg-[#F4ECE1]/40 px-2 py-1 rounded">
                    <span class="material-symbols-outlined text-xs">bolt</span>
                    <span>1-Click Fast Fill</span>
                </button>
            </div>

            <!-- Multi-tenant Registration Form -->
            <form action="{{ route('register') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <!-- Section 1: Firm Identity -->
                <div class="bg-[#FAF8F5] p-4 rounded-lg border border-[#EFECE6] flex flex-col gap-3">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#766A5E] font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">domain</span>
                        <span>01 · Law Firm / Advocates Chambers Identity</span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Official Law Firm / Chambers Name</label>
                            <input name="firm_name" x-model="firmName" type="text" required placeholder="e.g. Sharma &amp; Associates, Advocates" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33] transition-all"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Primary Practice Discipline</label>
                            <select name="practice_area" x-model="practiceArea" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33] transition-all">
                                @foreach(config('legal.practice_areas', ['Commercial Litigation & Arbitration', 'Corporate & Insolvency (IBC / NCLT)', 'Criminal Defense & Bail Matters']) as $area)
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Bar Council Enrollment No. / License</label>
                            <input name="bar_number" x-model="barNumber" type="text" placeholder="e.g. D/1420/2012" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] font-mono outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Primary Judicial Forum</label>
                            <select class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33]">
                                @foreach(config('legal.courts', ['Supreme Court of India', 'High Court of Delhi']) as $court)
                                    <option value="{{ $court }}">{{ $court }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Managing Advocate Account -->
                <div class="bg-[#FAF8F5] p-4 rounded-lg border border-[#EFECE6] flex flex-col gap-3">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#766A5E] font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#845D33]">badge</span>
                        <span>02 · Managing Advocate / Lead Counsel Account</span>
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Managing Advocate Full Name</label>
                            <input name="name" x-model="partnerName" type="text" required placeholder="Adv. Rajesh Sharma" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Chambers Email Address</label>
                            <input name="email" x-model="email" type="email" required placeholder="counsel@sharmalegal.in" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33]"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Master Password</label>
                            <input name="password" x-ref="passwordInput" value="Chambers@2026" type="password" required placeholder="Minimum 8 characters" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-[#222222]">Confirm Password</label>
                            <input name="password_confirmation" x-ref="passwordConfirmInput" value="Chambers@2026" type="password" required placeholder="Re-enter password" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#845D33]"/>
                        </div>
                    </div>
                </div>

                <!-- Terms & Bar Council Reassurance -->
                <div class="p-3 bg-[#F8F4EE] rounded-lg border border-[#F4ECE1] flex items-start gap-2 text-xs text-[#845D33]">
                    <span class="material-symbols-outlined text-base text-[#845D33] shrink-0 mt-0.5">verified</span>
                    <p class="leading-relaxed">
                        Advocate-client privilege, client advance retainers, and case records are maintained in compliance with the Advocates Act 1961 and Bar Council standards.
                    </p>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-lg bg-[#845D33] text-white text-xs font-semibold flex items-center justify-center gap-2 hover:bg-[#6D4B27] shadow-md transition-all active:scale-[0.99] mt-2">
                    <span>Establish Practice &amp; Enter Dashboard</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-[#F4EFEA] flex items-center justify-between text-xs">
                <span class="text-[#766A5E]">Already have a chambers workspace?</span>
                <a href="{{ route('login') }}" class="font-semibold text-[#845D33] hover:underline flex items-center gap-1">
                    <span>Sign In to Chambers</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

        </div>

        <!-- Compliance & Trust Badges -->
        <div class="w-full mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="bg-white/80 rounded-lg p-2 border border-[#EFECE6] flex flex-col items-center">
                <span class="material-symbols-outlined text-sm text-[#845D33]">balance</span>
                <span class="text-[10px] font-mono text-[#766A5E] mt-0.5">Bar Council Ready</span>
            </div>
            <div class="bg-white/80 rounded-lg p-2 border border-[#EFECE6] flex flex-col items-center">
                <span class="material-symbols-outlined text-sm text-[#845D33]">shield</span>
                <span class="text-[10px] font-mono text-[#766A5E] mt-0.5">Advocate Privilege</span>
            </div>
            <div class="bg-white/80 rounded-lg p-2 border border-[#EFECE6] flex flex-col items-center">
                <span class="material-symbols-outlined text-sm text-[#845D33]">currency_rupee</span>
                <span class="text-[10px] font-mono text-[#766A5E] mt-0.5">₹ INR Accounting</span>
            </div>
        </div>

    </div>

</body>
</html>
