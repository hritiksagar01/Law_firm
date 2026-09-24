<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — {{ \App\Models\PlatformSetting::platformName() }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Models\PlatformSetting::logoUrl() }}" />

    <!-- Google Fonts: Newsreader, Inter & Material Symbols with display=swap -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[#f4f2ed] text-[#1A1E1C] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative selection:bg-[#23493A]/10 selection:text-[#23493A]"
    x-data="{
        tab: 'client',
        email: '{{ old('email', 'hritik.srivastava28@gmail.com') }}',
        passcode: ['1', '2', '3', '4', '5', '6'],
        showPasscode: false,
        fullPassword: '12345678',
        setCredential(role, mail, pass) {
            this.tab = role;
            this.email = mail;
            this.fullPassword = pass;
            const chars = (pass || '').split('').slice(0, 6);
            this.passcode = [
                chars[0] || '',
                chars[1] || '',
                chars[2] || '',
                chars[3] || '',
                chars[4] || '',
                chars[5] || ''
            ];
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

    <div class="w-full max-w-[480px] flex flex-col items-center my-6">

        <!-- Brand Emblem (Increased Logo Size) -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="p-4 bg-white rounded-2xl border border-[#E7E4DC] shadow-[0_4px_24px_-2px_rgba(26,30,28,0.08)] flex items-center justify-center">
                <img src="{{ \App\Models\PlatformSetting::logoUrl() }}" alt="{{ \App\Models\PlatformSetting::platformName() }}"
                    class="h-20 sm:h-24 w-auto max-w-[280px] object-contain" />
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
        <div class="w-full bg-white rounded-xl border border-[#E7E4DC] shadow-[0_4px_24px_-2px_rgba(26,30,28,0.06),0_0_0_1px_#E7E4DC] p-6 sm:p-7 flex flex-col relative">

            <!-- Persona Selector: Horizontal Layout with Small Left Radio Buttons -->
            <div class="mb-5">
                <!-- Primary Roles: Client Portal & LawFirm side by side like the old horizontal bar -->
                <div class="grid grid-cols-2 p-1 bg-[#F6F4EE] rounded-lg border border-[#E7E4DC] gap-1 mb-2.5">
                    <!-- Client Portal -->
                    <button type="button" @click="setCredential('client', 'hritik.srivastava28@gmail.com', '12345678')"
                        class="py-2 px-2 sm:px-3 rounded-[6px] text-xs flex items-center justify-center gap-2 transition-all cursor-pointer select-none"
                        :class="tab === 'client'
                            ? 'bg-white text-[#23493A] shadow-xs font-semibold border border-[#E7E4DC]'
                            : 'text-[#646864] hover:text-[#1A1E1C] font-medium border border-transparent'">
                        <!-- Small radio button on left -->
                        <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center transition-all shrink-0"
                            :class="tab === 'client' ? 'border-[#23493A] bg-[#23493A]' : 'border-[#cfcbc0] bg-white'">
                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="tab === 'client'"></span>
                        </span>
                        <span class="material-symbols-outlined text-[15px] shrink-0"
                            :class="tab === 'client' ? 'text-[#23493A]' : 'text-[#8A8E89]'">domain</span>
                        <span class="truncate">Client Portal</span>
                    </button>

                    <!-- LawFirm & Advocate Login -->
                    <button type="button" @click="setCredential('firm', 'hritiksagar.tech@gmail.com', '12345678')"
                        class="py-2 px-2 sm:px-3 rounded-[6px] text-xs flex items-center justify-center gap-2 transition-all cursor-pointer select-none"
                        :class="tab === 'firm'
                            ? 'bg-white text-[#23493A] shadow-xs font-semibold border border-[#E7E4DC]'
                            : 'text-[#646864] hover:text-[#1A1E1C] font-medium border border-transparent'">
                        <!-- Small radio button on left -->
                        <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center transition-all shrink-0"
                            :class="tab === 'firm' ? 'border-[#23493A] bg-[#23493A]' : 'border-[#cfcbc0] bg-white'">
                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="tab === 'firm'"></span>
                        </span>
                        <span class="material-symbols-outlined text-[15px] shrink-0"
                            :class="tab === 'firm' ? 'text-[#23493A]' : 'text-[#8A8E89]'">balance</span>
                        <span class="truncate">LawFirm & Advocate</span>
                    </button>
                </div>

                <!-- Super Admin: Subtle, minimal option at bottom with small radio button -->
                <div class="flex items-center justify-center">
                    <button type="button" @click="setCredential('admin', 'admin@sharmalegal.in', 'password123')"
                        class="text-[11.5px] text-[#8A8E89] hover:text-[#23493A] transition-colors cursor-pointer inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full hover:bg-[#F6F4EE]"
                        :class="tab === 'admin' ? 'text-[#23493A] font-semibold bg-[#F6F4EE]' : ''">
                        <span class="w-3 h-3 rounded-full border flex items-center justify-center transition-all shrink-0"
                            :class="tab === 'admin' ? 'border-[#23493A] bg-[#23493A]' : 'border-[#cfcbc0] bg-white'">
                            <span class="w-1 h-1 rounded-full bg-white" x-show="tab === 'admin'"></span>
                        </span>
                        <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                        <span>Super Admin Access</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Tab Header -->
            <div class="mb-5 pb-3 border-b border-[#F0EEE8]">
                <template x-if="tab === 'client'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">Client Portal</h2>
                        <p class="text-xs text-[#646864] mt-0.5 font-sans">Direct access for enterprise retainers, case filings, and legal opinions.</p>
                    </div>
                </template>
                <template x-if="tab === 'firm'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">LawFirm and Advocate Login</h2>
                        <p class="text-xs text-[#646864] mt-0.5 font-sans">Practice credentials for active litigation dockets, filings, and trust ledger.</p>
                    </div>
                </template>
                <template x-if="tab === 'admin'">
                    <div>
                        <h2 class="font-headline text-[22px] font-medium text-[#1A1E1C] tracking-tight">Super Admin</h2>
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

                <!-- 6-Digit Staggered Passcode Boxes -->
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[13px] font-medium text-[#1A1E1C]">6-Digit Passcode</label>
                        <a href="{{ route('password.request') }}"
                            class="text-xs text-[#23493A] hover:underline font-medium">Forgot passcode?</a>
                    </div>

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

                    <!-- Hidden sync input for backend submission and automated test suites -->
                    <input type="hidden" name="password" :value="fullPassword" id="password" />

                    <div class="flex items-center justify-between mt-1">
                        <button type="button" @click="showPasscode = !showPasscode"
                            class="text-[11.5px] text-[#646864] hover:text-[#1A1E1C] flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[15px]" x-text="showPasscode ? 'visibility_off' : 'visibility'">visibility</span>
                            <span x-text="showPasscode ? 'Hide passcode' : 'Show passcode'">Show passcode</span>
                        </button>
                        <span class="text-[11px] text-[#8A8E89]">Secure 6-digit credentials</span>
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
                    <span x-text="tab === 'client' ? 'Sign In to Client Portal' : (tab === 'firm' ? 'Sign In to Law Practice' : 'Access Super Admin')">Sign In</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Hint Bar (Preserved per instruction: keep demo account for now for login) -->
            <div class="mt-4 pt-3 border-t border-[#E7E4DC] flex flex-col gap-2">
                <span class="text-[11px] font-medium text-[#8A8E89] uppercase tracking-wider">Quick Fill Demo Accounts</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="setCredential('client', 'hritik.srivastava28@gmail.com', '12345678')"
                        class="px-2.5 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer"
                        :class="tab === 'client' ? 'border-[#23493A] font-semibold text-[#23493A]' : ''">
                        Client Vikram
                    </button>
                    <button type="button" @click="setCredential('firm', 'hritiksagar.tech@gmail.com', '12345678')"
                        class="px-2.5 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer"
                        :class="tab === 'firm' ? 'border-[#23493A] font-semibold text-[#23493A]' : ''">
                        Adv. Rajesh
                    </button>
                    <button type="button" @click="setCredential('admin', 'admin@sharmalegal.in', 'password123')"
                        class="px-2.5 py-1 rounded-[4px] bg-[#F6F4EE] hover:bg-[#eae8e2] border border-[#E7E4DC] text-[11px] text-[#1A1E1C] transition-colors cursor-pointer font-medium"
                        :class="tab === 'admin' ? 'border-[#23493A] font-semibold text-[#23493A]' : ''">
                        Super Admin
                    </button>
                </div>
            </div>

            <!-- Advocate / Law Practice Prompt: Visible when Advocate is selected -->
            <div x-show="tab === 'firm'" x-cloak class="mt-4 pt-4 border-t border-[#E7E4DC] flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493A] text-lg">domain_add</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#1A1E1C]">New law firm or advocate?</span>
                        <span class="text-[11px] text-[#646864]">Establish chambers or practice</span>
                    </div>
                </div>
                <a href="{{ route('register') }}"
                    class="py-1.5 px-3 rounded-[6px] bg-[#23493A] text-white text-xs font-semibold hover:bg-[#1B3B2F] transition-colors shrink-0 shadow-xs">
                    Register Now
                </a>
            </div>

        </div>

    </div>

    <!-- Public Footer with Required Skybridge IT Consulting Credit -->
    <footer class="w-full max-w-[500px] text-center my-4 text-[12px] text-[#646864] space-y-2">
        <p class="font-medium text-[#1A1E1C]">
            Designed and Developed by 
            <a href="https://skybridgeit.com/" target="_blank" rel="noopener noreferrer" 
               class="text-[#23493A] font-semibold hover:underline">Skybridge IT Consulting</a>
        </p>
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
    </footer>

</body>

</html>