<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — {{ config('legal.app_name', 'Vennamraj Associates') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}" />

    <!-- Google Fonts: Newsreader (Editorial Serif) & Inter (Clean UI Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[#F8F6F0] text-[#1A1E1C] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative selection:bg-[#23493A]/10 selection:text-[#23493A]"
    x-data="{
        tab: 'firm',
        email: '{{ old('email', 'rajesh@sharmalegal.in') }}',
        password: 'password123',
        showPassword: false,
        setCredential(role, mail, pass) {
            this.tab = role;
            this.email = mail;
            this.password = pass;
        }
    }">

    <div class="w-full max-w-[440px] flex flex-col items-center my-6">

        <!-- Brand Emblem -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="p-3 bg-white rounded-xl border border-[#E7E4DC] shadow-[0_4px_20px_-2px_rgba(26,30,28,0.06)]">
                <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}"
                    class="h-12 w-auto object-contain" />
            </div>
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="w-full mb-4 p-3 rounded-[6px] bg-[#23493A]/10 border border-[#23493A]/20 text-xs text-[#23493A] flex items-center gap-2">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="w-full mb-4 p-3 rounded-[6px] bg-[#F6F4EE] border border-[#E7E4DC] text-xs text-[#1A1E1C] flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-[#23493A]">info</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="w-full mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-red-600">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="w-full mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-red-600">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Card Container with Archival Tonal Stack -->
        <div class="w-full bg-white rounded-lg border border-[#E7E4DC] shadow-[0_4px_20px_-2px_rgba(26,30,28,0.06),0_0_0_1px_#E7E4DC] p-6 sm:p-7 flex flex-col relative">

            <!-- Persona Selector Tabs -->
            <div class="grid grid-cols-3 p-1 bg-[#F6F4EE] rounded-[6px] mb-5 border border-[#E7E4DC]">
                <button type="button" @click="setCredential('firm', 'rajesh@sharmalegal.in', 'password123')"
                    :class="tab === 'firm' ? 'bg-white text-[#23493A] shadow-xs font-semibold border border-[#E7E4DC]' : 'text-[#646864] hover:text-[#1A1E1C] font-medium border border-transparent'"
                    class="py-2 px-1.5 rounded-[4px] text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]"
                        :class="tab === 'firm' ? 'text-[#23493A]' : 'text-[#646864]'">balance</span>
                    <span>Advocate</span>
                </button>
                <button type="button" @click="setCredential('client', 'hritiksagar.tech@gmail.com', 'password111')"
                    :class="tab === 'client' ? 'bg-white text-[#23493A] shadow-xs font-semibold border border-[#E7E4DC]' : 'text-[#646864] hover:text-[#1A1E1C] font-medium border border-transparent'"
                    class="py-2 px-1.5 rounded-[4px] text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]"
                        :class="tab === 'client' ? 'text-[#23493A]' : 'text-[#646864]'">domain</span>
                    <span>Client</span>
                </button>
                <button type="button" @click="setCredential('admin', 'admin@sharmalegal.in', 'password123')"
                    :class="tab === 'admin' ? 'bg-white text-[#23493A] shadow-xs font-semibold border border-[#E7E4DC]' : 'text-[#646864] hover:text-[#1A1E1C] font-medium border border-transparent'"
                    class="py-2 px-1.5 rounded-[4px] text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]"
                        :class="tab === 'admin' ? 'text-[#23493A]' : 'text-[#646864]'">admin_panel_settings</span>
                    <span>Super Admin</span>
                </button>
            </div>

            <!-- Dynamic Tab Header with Newsreader Editorial Typography -->
            <div class="mb-5">
                <template x-if="tab === 'firm'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">Chambers &amp; Partner Sign In</h2>
                        <p class="text-xs text-[#646864] mt-0.5 font-sans">Practice credentials for active litigation dockets, filings, and trust ledger.</p>
                    </div>
                </template>
                <template x-if="tab === 'client'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">Client Litigation Portal</h2>
                        <p class="text-xs text-[#646864] mt-0.5 font-sans">Direct access for enterprise retainers, case filings, and legal opinions.</p>
                    </div>
                </template>
                <template x-if="tab === 'admin'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">Platform Telemetry &amp; Admin</h2>
                        <p class="text-xs text-[#646864] mt-0.5 font-sans">Super-administrator console for multi-tenant firm governance.</p>
                    </div>
                </template>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-[13px] font-medium text-[#1A1E1C]">Email Address</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#8A8E89] text-[18px] pointer-events-none">mail</span>
                        <input name="email" x-model="email" type="email" required autofocus
                            placeholder="name@domain.com"
                            class="w-full h-[42px] pl-10 pr-3 rounded-[6px] bg-white border border-[#E7E4DC] text-sm text-[#1A1E1C] placeholder-[#8A8E89] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all" />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">Password</label>
                        <a href="{{ route('password.request') }}"
                            class="text-xs text-[#23493A] hover:underline font-medium">Forgot password?</a>
                    </div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#8A8E89] text-[18px] pointer-events-none">lock</span>
                        <input name="password" x-model="password" :type="showPassword ? 'text' : 'password'" required
                            placeholder="Enter password"
                            class="w-full h-[42px] pl-10 pr-10 rounded-[6px] bg-white border border-[#E7E4DC] text-sm text-[#1A1E1C] placeholder-[#8A8E89] outline-none focus:border-[#23493A] focus:ring-1 focus:ring-[#23493A] transition-all" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 text-[#8A8E89] hover:text-[#1A1E1C] transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]"
                                x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked
                            class="w-4 h-4 rounded-[4px] border-[#E7E4DC] text-[#23493A] focus:ring-[#23493A] cursor-pointer" />
                        <span class="text-xs text-[#646864]">Remember session</span>
                    </label>
                    <span class="text-[11px] text-[#8A8E89] tabular-nums">256-Bit Encrypted</span>
                </div>

                <button type="submit"
                    class="w-full h-11 rounded-[6px] bg-[#23493A] hover:bg-[#1B3B2F] text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.99] mt-1 cursor-pointer">
                    <span x-text="tab === 'firm' ? 'Sign In to Chambers' : (tab === 'client' ? 'Access Client Portal' : 'Access Super Admin')">Sign In</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Hint Bar -->
            <div class="mt-4 pt-3 border-t border-[#E7E4DC] flex flex-col gap-2">
                <span class="text-[11px] font-medium text-[#8A8E89] uppercase tracking-wider">Quick Fill Demo Accounts</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="setCredential('firm', 'rajesh@sharmalegal.in', 'password123')"
                        class="px-2 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer">
                        Adv. Rajesh
                    </button>
                    <button type="button" @click="setCredential('client', 'hritiksagar.tech@gmail.com', 'password111')"
                        class="px-2 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer">
                        Client Hritik
                    </button>
                    <button type="button" @click="setCredential('admin', 'admin@sharmalegal.in', 'password123')"
                        class="px-2 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer font-medium text-[#23493A]">
                        Super Admin
                    </button>
                </div>
            </div>

            <!-- New Chambers Sign Up Banner -->
            <div class="mt-5 pt-4 border-t border-[#E7E4DC] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493A] text-lg">domain_add</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1A1E1C]">New Law Practice?</span>
                        <span class="text-[11px] text-[#646864]">Establish practice chambers</span>
                    </div>
                </div>
                <a href="{{ route('register') }}"
                    class="py-1.5 px-3 rounded-[6px] bg-white border border-[#E7E4DC] text-[#23493A] text-xs font-medium hover:bg-[#F6F4EE] hover:border-[#23493A] transition-colors shrink-0">
                    Register Practice
                </a>
            </div>

        </div>

    </div>

</body>

</html>