<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sign In — {{ config('legal.app_name', 'Law Firm Management') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f6] text-[#1a1c1a] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative" x-data="{ tab: 'firm', email: 'rajesh@sharmalegal.in', showPassword: false }">

    <!-- Atmospheric Warm Blur Accents -->
    <div class="absolute top-1/4 -left-12 w-96 h-96 bg-[#c5ecd2]/30 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute bottom-10 -right-8 w-80 h-80 bg-[#fed977]/20 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="w-full max-w-md flex flex-col items-center my-8">
        
        <!-- Brand Emblem -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#1a3c2a] flex items-center justify-center text-white shadow-md mb-3">
                <span class="material-symbols-outlined text-2xl text-[#fed977]">balance</span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a] tracking-tight">{{ config('legal.app_name', 'Law Firm Management') }}</h1>
            <p class="text-[11px] font-mono uppercase tracking-widest text-[#727973] font-medium mt-1">
                Court Dockets · Case Files · Client Accounts
            </p>
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
        <div class="w-full mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="w-full mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Clean Authentication Card with Dual Tabs -->
        <div class="w-full bg-white rounded-xl shadow-xl border border-[#e9e8e5] p-7 flex flex-col relative">
            
            <!-- Persona Switcher Tabs -->
            <div class="grid grid-cols-2 p-1 bg-[#f4f3f1] rounded-xl mb-5 border border-[#e9e8e5]">
                <button type="button" 
                    @click="tab = 'firm'; email = 'rajesh@sharmalegal.in'"
                    :class="tab === 'firm' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-base" :class="tab === 'firm' ? 'text-[#1a3c2a]' : 'text-[#727973]'">balance</span>
                    <span>Advocate / Firm</span>
                </button>
                <button type="button" 
                    @click="tab = 'client'; email = 'vikram@malhotragroup.in'"
                    :class="tab === 'client' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                    class="py-2.5 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-base" :class="tab === 'client' ? 'text-[#1a3c2a]' : 'text-[#727973]'">domain</span>
                    <span>Client Portal</span>
                </button>
            </div>

            <!-- Dynamic Tab Header -->
            <div class="mb-5">
                <template x-if="tab === 'firm'">
                    <div>
                        <h2 class="text-base font-semibold text-[#1a1c1a]">Advocate &amp; Chambers Sign In</h2>
                        <p class="text-xs text-[#727973] mt-0.5">Access litigation dockets, time entries, and matter billing.</p>
                    </div>
                </template>
                <template x-if="tab === 'client'">
                    <div>
                        <h2 class="text-base font-semibold text-[#1a1c1a]">Client Portal Sign In</h2>
                        <p class="text-xs text-[#727973] mt-0.5">Track case milestones, fulfill document requests, and review billing.</p>
                    </div>
                </template>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1a1c1a]">Email Address</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#727973] text-lg pointer-events-none">mail</span>
                        <input name="email" x-model="email" type="email" required placeholder="name@domain.in" class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a] transition-all"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-[#1a1c1a]">Password</label>
                        <a href="{{ route('password.request') }}" class="text-[11px] text-[#1a3c2a] hover:underline font-medium">Forgot password?</a>
                    </div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#727973] text-lg pointer-events-none">lock</span>
                        <input name="password" value="password123" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••••••" class="w-full pl-10 pr-10 py-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a] transition-all"/>
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-[#727973] hover:text-[#1a1c1a] transition-colors">
                            <span class="material-symbols-outlined text-base" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-[#1a3c2a] accent-[#1a3c2a] cursor-pointer"/>
                        <span class="text-xs text-[#424843]">Remember session (30 days)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold flex items-center justify-center gap-2 hover:bg-[#022616] shadow-sm transition-all active:scale-[0.99] mt-1">
                    <span x-text="tab === 'firm' ? 'Sign In to Chambers' : 'Sign In to Portal'">Sign In</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>

            <!-- 1-Click Fast Login Showcase -->
            <div class="mt-6 pt-5 border-t border-[#efeeeb]">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#727973] font-semibold">1-Click Fast Test Profiles</span>
                    <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Instant Fill</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- Rajesh Sharma (Senior Advocate) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="rajesh@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#faf9f6] border border-[#e9e8e5] hover:border-[#1a3c2a] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Rajesh" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="https://images.unsplash.com/photo-1556157382-97eda2d62296?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#1a1c1a] truncate group-hover:text-[#1a3c2a]">Adv. Rajesh</span>
                                <span class="text-[9px] text-[#727973] truncate">Senior Advocate</span>
                            </div>
                        </button>
                    </form>

                    <!-- Priya Nair (Associate) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="priya@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#faf9f6] border border-[#e9e8e5] hover:border-[#1a3c2a] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Priya" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#1a1c1a] truncate group-hover:text-[#1a3c2a]">Adv. Priya</span>
                                <span class="text-[9px] text-[#727973] truncate">Associate Counsel</span>
                            </div>
                        </button>
                    </form>

                    <!-- Amit Verma (Court Munshi / Clerk) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="amit@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#faf9f6] border border-[#e9e8e5] hover:border-[#1a3c2a] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Amit" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#1a1c1a] truncate group-hover:text-[#1a3c2a]">Amit Verma</span>
                                <span class="text-[9px] text-[#727973] truncate">Law Clerk / Munshi</span>
                            </div>
                        </button>
                    </form>

                    <!-- Vikram Malhotra (Corporate Client) -->
                    <form action="{{ route('demo-login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="vikram@malhotragroup.in"/>
                        <button type="submit" class="w-full p-2 rounded-lg bg-[#faf9f6] border border-[#e9e8e5] hover:border-[#1a3c2a] hover:bg-white transition-all text-left group flex items-center gap-2">
                            <img alt="Client" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80"/>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold text-[#1a1c1a] truncate group-hover:text-[#1a3c2a]">V. Malhotra</span>
                                <span class="text-[9px] text-[#755b00] truncate">Corporate Client</span>
                            </div>
                        </button>
                    </form>

                    <!-- Platform Super Admin -->
                    <form action="{{ route('demo-login') }}" method="POST" class="col-span-2">
                        @csrf
                        <input type="hidden" name="email" value="admin@sharmalegal.in"/>
                        <button type="submit" class="w-full p-2.5 rounded-lg bg-[#111714] text-white border border-[#2b3830] hover:bg-[#1a3c2a] transition-all text-left group flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#1a3c2a] text-[#fed977] flex items-center justify-center font-bold text-xs shrink-0">
                                    <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[11px] font-semibold text-white truncate">Platform Super Admin</span>
                                    <span class="text-[9px] text-[#82a78f] truncate">Multi-Tenant Cloud, S3 &amp; .env Config</span>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono text-[#fed977] px-2 py-0.5 rounded bg-[#1a3c2a] border border-[#2e4c3a]">SuperAdmin</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- New Chambers Sign Up Banner -->
            <div class="mt-4 p-3 bg-[#f0f9f4] rounded-lg border border-[#c5ecd2] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a3c2a] text-lg">domain_add</span>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-[#002112]">New Law Firm or Chambers?</span>
                        <span class="text-[10px] text-[#2c4e3a]">Register your legal practice and court docket</span>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="py-1.5 px-3 rounded bg-[#1a3c2a] text-white text-[11px] font-semibold hover:bg-[#022616] transition-colors shrink-0">
                    Sign Up
                </a>
            </div>

        </div>

    </div>

</body>
</html>
