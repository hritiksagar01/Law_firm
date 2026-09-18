<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — {{ config('legal.app_name', 'Vennamraj Associates') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}" />

    <!-- Industry Standard Readable Typography: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[#FAF8F5] text-[#222222] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative"
    x-data="{ tab: 'firm', showPassword: false }">

    <!-- Atmospheric Warm Glow Accents -->
    <div class="absolute top-1/4 -left-12 w-96 h-96 bg-[#F5EADB]/40 rounded-full blur-3xl pointer-events-none -z-10">
    </div>
    <div class="absolute bottom-10 -right-8 w-80 h-80 bg-[#FAF1E6]/50 rounded-full blur-3xl pointer-events-none -z-10">
    </div>

    <div class="w-full max-w-md flex flex-col items-center my-8">

        <!-- Brand Emblem & Official Logo -->
        <div class="flex flex-col items-center text-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}"
                class="h-20 w-auto object-contain rounded-xl shadow-md border border-[#E8DAC8]/60 bg-white p-2" />
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
            <div
                class="w-full mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div
                class="w-full mb-4 p-3 rounded-lg bg-[#F8F4EE] border border-[#9F8349]/30 text-xs text-[#856C36] flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-base text-[#9F8349]">info</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-base text-red-600">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div
                class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-base text-red-600">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Clean Authentication Card with Dual Tabs -->
        <div class="w-full bg-white rounded-2xl shadow-xl border border-[#EFECE6] p-7 flex flex-col relative">

            <!-- Persona Guidance Tabs -->
            <div class="grid grid-cols-2 p-1 bg-[#FAF8F5] rounded-xl mb-5 border border-[#EAE4DC]">
                <button type="button" @click="tab = 'firm'"
                    :class="tab === 'firm' ? 'bg-white text-[#9F8349] shadow-xs font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base"
                        :class="tab === 'firm' ? 'text-[#9F8349]' : 'text-[#766A5E]'">balance</span>
                    <span>Advocate &amp; Staff</span>
                </button>
                <button type="button" @click="tab = 'client'"
                    :class="tab === 'client' ? 'bg-white text-[#9F8349] shadow-xs font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base"
                        :class="tab === 'client' ? 'text-[#9F8349]' : 'text-[#766A5E]'">domain</span>
                    <span>Client Portal</span>
                </button>
            </div>

            <!-- Dynamic Tab Header -->
            <div class="mb-5">
                <template x-if="tab === 'firm'">
                    <div>
                        <h2 class="text-base font-serif font-bold text-[#222222]">Advocate &amp; Chambers Sign In</h2>
                        <p class="text-xs text-[#766A5E] mt-0.5">Enter your practice credentials to access litigation
                            dockets, case files, and opinions.</p>
                    </div>
                </template>
                <template x-if="tab === 'client'">
                    <div>
                        <h2 class="text-base font-serif font-bold text-[#222222]">Client Portal Sign In</h2>
                        <p class="text-xs text-[#766A5E] mt-0.5">Enter your invited client credentials to track case
                            progress, review filings, and message counsel.</p>
                    </div>
                </template>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#222222]">Email Address</label>
                    <div class="relative flex items-center">
                        <span
                            class="material-symbols-outlined absolute left-3 text-[#766A5E] text-lg pointer-events-none">mail</span>
                        <input name="email" value="{{ old('email') }}" type="email" required autofocus
                            placeholder="name@domain.com"
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#9F8349] transition-all" />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-[#222222]">Password</label>
                        <a href="{{ route('password.request') }}"
                            class="text-[11px] text-[#9F8349] hover:underline font-medium">Forgot password?</a>
                    </div>
                    <div class="relative flex items-center">
                        <span
                            class="material-symbols-outlined absolute left-3 text-[#766A5E] text-lg pointer-events-none">lock</span>
                        <input name="password" :type="showPassword ? 'text' : 'password'" required
                            placeholder="Enter your password"
                            class="w-full pl-10 pr-10 py-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#9F8349] transition-all" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 text-[#766A5E] hover:text-[#222222] transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-base"
                                x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1"
                            class="w-4 h-4 rounded text-[#9F8349] accent-[#9F8349] cursor-pointer" />
                        <span class="text-xs text-[#554D45]">Remember my session</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 rounded-lg bg-[#9F8349] text-white text-xs font-semibold flex items-center justify-center gap-2 hover:bg-[#856C36] shadow-sm transition-all active:scale-[0.99] mt-1 cursor-pointer">
                    <span x-text="tab === 'firm' ? 'Sign In to Chambers' : 'Sign In to Client Portal'">Sign In</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <!-- New Chambers Sign Up Banner -->
            <div class="mt-6 pt-5 border-t border-[#EFECE6] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349] text-lg">domain_add</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#442E15]">New Law Practice?</span>
                        <span class="text-[10px] text-[#766A5E]">Register your firm &amp; practice chambers</span>
                    </div>
                </div>
                <a href="{{ route('register') }}"
                    class="py-1.5 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-[#9F8349] text-[11px] font-semibold hover:bg-white hover:border-[#9F8349] transition-colors shrink-0">
                    Register Practice
                </a>
            </div>

        </div>

    </div>

</body>

</html>