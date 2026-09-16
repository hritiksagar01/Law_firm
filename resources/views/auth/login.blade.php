<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sign In — {{ config('legal.app_name', 'Vennamraj Associates') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>

    <!-- Juris Prestige Typography: EB Garamond (Headline) & Manrope (Body/Label) -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#222222] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative" x-data="{ tab: 'firm', email: 'rajesh@sharmalegal.in', showPassword: false }">

    <!-- Atmospheric Warm Glow Accents (Pure Warm Ivory / Light Brown) -->
    <div class="absolute top-1/4 -left-12 w-96 h-96 bg-[#F5EADB]/40 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute bottom-10 -right-8 w-80 h-80 bg-[#FAF1E6]/50 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="w-full max-w-md flex flex-col items-center my-8">
        
        <!-- Brand Emblem & Official Logo -->
        <div class="flex flex-col items-center text-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-16 w-auto object-contain rounded-xl shadow-md mb-3 border border-[#E8DAC8]/60 bg-white p-1.5"/>
            <h1 class="text-2xl font-serif font-bold text-[#222222] tracking-tight">{{ config('legal.app_name', 'Vennamraj Associates') }}</h1>
            <p class="text-[11px] font-mono uppercase tracking-widest text-[#766A5E] font-semibold mt-1">
                Advocates and Legal Consultants · Court Dockets · Client Portal
            </p>
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
        <div class="w-full mb-4 p-3 rounded-lg bg-[#F8F4EE] border border-[#E8DAC8] text-xs text-[#6D4B27] flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-[#845D33]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('info'))
        <div class="w-full mb-4 p-3 rounded-lg bg-[#F8F4EE] border border-[#845D33]/30 text-xs text-[#6D4B27] flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-[#845D33]">info</span>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        @php
            $dbErrorText = $errors->first() ?? session('error') ?? '';
            $isDbOffline = !empty($dbErrorText) && (
                str_contains(strtolower($dbErrorText), 'database') || 
                str_contains(strtolower($dbErrorText), 'unreachable') || 
                str_contains(strtolower($dbErrorText), 'offline') ||
                str_contains(strtolower($dbErrorText), 'emergency')
            );
        @endphp

        @if($isDbOffline)
        <!-- High-Priority Emergency Console Dispatch Card -->
        <div class="w-full mb-5 p-4 rounded-xl bg-[#FFFBF5] border-2 border-[#845D33] shadow-lg flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#845D33] text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                    <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs font-bold text-[#442E15] uppercase tracking-wide">Database Unreachable — Emergency Mode Active</h3>
                    <p class="text-[11px] text-[#766A5E] mt-1 leading-relaxed">
                        {{ $dbErrorText }}
                    </p>
                </div>
            </div>
            
            <form action="{{ route('demo-login') }}" method="POST" class="w-full mt-1">
                @csrf
                <input type="hidden" name="email" value="admin@sharmalegal.in"/>
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#845D33] text-white text-xs font-bold flex items-center justify-center gap-2 hover:bg-[#6D4B27] shadow-md transition-all active:scale-[0.99]">
                    <span class="material-symbols-outlined text-base">bolt</span>
                    <span>1-Click Enter Emergency Super Admin Console</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>
            
            <div class="text-[10px] text-[#8C7F72] text-center bg-[#F8F4EE] py-1.5 px-2 rounded border border-[#EAE4DC]">
                Manual Credentials: <span class="font-mono font-bold text-[#442E15]">admin@sharmalegal.in</span> &bull; <span class="font-mono font-bold text-[#442E15]">password123</span>
            </div>
        </div>
        @elseif(session('error'))
        <div class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            <span>{{ session('error') }}</span>
        </div>
        @elseif($errors->any())
        <div class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Clean Authentication Card with Dual Tabs -->
        <div class="w-full bg-white rounded-2xl shadow-xl border border-[#EFECE6] p-7 flex flex-col relative">
            
            <!-- Persona Switcher Tabs -->
            <div class="grid grid-cols-2 p-1 bg-[#FAF8F5] rounded-xl mb-5 border border-[#EAE4DC]">
                <button type="button" 
                    @click="tab = 'firm'; email = 'rajesh@sharmalegal.in'"
                    :class="tab === 'firm' ? 'bg-white text-[#845D33] shadow-xs font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-base" :class="tab === 'firm' ? 'text-[#845D33]' : 'text-[#766A5E]'">balance</span>
                    <span>Advocate / Firm</span>
                </button>
                <button type="button" 
                    @click="tab = 'client'; email = 'vikram@malhotragroup.in'"
                    :class="tab === 'client' ? 'bg-white text-[#845D33] shadow-xs font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-base" :class="tab === 'client' ? 'text-[#845D33]' : 'text-[#766A5E]'">domain</span>
                    <span>Client Portal</span>
                </button>
            </div>

            <!-- Dynamic Tab Header -->
            <div class="mb-5">
                <template x-if="tab === 'firm'">
                    <div>
                        <h2 class="text-base font-serif font-bold text-[#222222]">Advocate &amp; Chambers Sign In</h2>
                        <p class="text-xs text-[#766A5E] mt-0.5">Access litigation dockets, time entries, and matter billing.</p>
                    </div>
                </template>
                <template x-if="tab === 'client'">
                    <div>
                        <h2 class="text-base font-serif font-bold text-[#222222]">Client Portal Sign In</h2>
                        <p class="text-xs text-[#766A5E] mt-0.5">Track case milestones, fulfill document requests, and review billing.</p>
                    </div>
                </template>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#222222]">Email Address</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#766A5E] text-lg pointer-events-none">mail</span>
                        <input name="email" x-model="email" type="email" required placeholder="name@domain.in" class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#845D33] transition-all"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-[#222222]">Password</label>
                        <a href="{{ route('password.request') }}" class="text-[11px] text-[#845D33] hover:underline font-medium">Forgot password?</a>
                    </div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#766A5E] text-lg pointer-events-none">lock</span>
                        <input name="password" value="password123" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••••••" class="w-full pl-10 pr-10 py-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#845D33] transition-all"/>
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-[#766A5E] hover:text-[#222222] transition-colors">
                            <span class="material-symbols-outlined text-base" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-[#845D33] accent-[#845D33] cursor-pointer"/>
                        <span class="text-xs text-[#554D45]">Remember session (30 days)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#845D33] text-white text-xs font-semibold flex items-center justify-center gap-2 hover:bg-[#6D4B27] shadow-sm transition-all active:scale-[0.99] mt-1">
                    <span x-text="tab === 'firm' ? 'Sign In to Chambers' : 'Sign In to Portal'">Sign In</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <!-- 1-Click Fast Login Showcase -->
            <div class="mt-6 pt-5 border-t border-[#EFECE6]">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#766A5E] font-semibold">1-Click Fast Test Profiles</span>
                    <span class="text-[10px] font-mono text-[#845D33] bg-[#F8F4EE] px-1.5 py-0.5 rounded border border-[#EAE4DC]">Instant Fill</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- Rajesh Sharma (Senior Advocate) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="rajesh@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] hover:border-[#845D33] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Rajesh" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#845D33]/30" src="https://images.unsplash.com/photo-1556157382-97eda2d62296?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#222222] truncate group-hover:text-[#845D33]">Adv. Rajesh</span>
                                <span class="text-[9px] text-[#766A5E] truncate">Senior Advocate</span>
                            </div>
                        </button>
                    </form>

                    <!-- Priya Nair (Associate) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="priya@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] hover:border-[#845D33] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Priya" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#845D33]/30" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#222222] truncate group-hover:text-[#845D33]">Adv. Priya</span>
                                <span class="text-[9px] text-[#766A5E] truncate">Associate Counsel</span>
                            </div>
                        </button>
                    </form>

                    <!-- Amit Verma (Court Munshi / Clerk) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="amit@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] hover:border-[#845D33] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Amit" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#845D33]/30" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#222222] truncate group-hover:text-[#845D33]">Amit Verma</span>
                                <span class="text-[9px] text-[#766A5E] truncate">Law Clerk / Munshi</span>
                            </div>
                        </button>
                    </form>

                    <!-- Vikram Malhotra (Corporate Client) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="vikram@malhotragroup.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] hover:border-[#845D33] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Client" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#845D33]/30" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#222222] truncate group-hover:text-[#845D33]">V. Malhotra</span>
                                <span class="text-[9px] text-[#845D33] font-semibold truncate">Corporate Client</span>
                            </div>
                        </button>
                    </form>

                    <!-- Platform Super Admin -->
                    <form action="{{ route('demo-login') }}" method="POST" class="col-span-2">
                        @csrf
                        <input type="hidden" name="email" value="admin@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2.5 rounded-lg bg-[#222222] text-white border border-[#3D352F] hover:bg-[#333333] transition-all text-left group flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#845D33] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[11px] font-semibold text-white truncate">Platform Super Admin</span>
                                    <span class="text-[9px] text-[#C4B6A6] truncate">Multi-Tenant Cloud, S3 &amp; .env Config</span>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono text-white px-2 py-0.5 rounded bg-[#845D33] border border-[#A67848]">SuperAdmin</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- New Chambers Sign Up Banner -->
            <div class="mt-4 p-3 bg-[#F8F4EE] rounded-xl border border-[#E8DAC8] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#845D33] text-lg">domain_add</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#442E15]">New Law Firm or Chambers?</span>
                        <span class="text-[10px] text-[#766A5E]">Register your legal practice and court docket</span>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="py-1.5 px-3 rounded-lg bg-[#845D33] text-white text-[11px] font-semibold hover:bg-[#6D4B27] transition-colors shrink-0">
                    Sign Up
                </a>
            </div>

        </div>

    </div>

</body>
</html>
