<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>403 — Unauthorized Access · SJM Law Firm Management</title>
    <!-- Industry Standard Readable Typography: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f2ed] font-sans text-[#222222] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl border border-[#EFECE6] p-8 shadow-sm text-center">
        <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-700 flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-3xl">lock</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-red-700 bg-red-50 px-3 py-1 rounded-full border border-red-200">Error 403 · Privileged Access Restriction</span>
        <h1 class="font-serif text-3xl font-bold text-[#222222] mt-4 mb-2">Restricted Access</h1>
        <p class="text-sm text-[#766A5E] leading-relaxed mb-6">
            You do not have the necessary security clearance or tenancy permissions to access this case dossier, client file, or administrative module.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            @auth
                @if(auth()->user()->isClient())
                    <a href="{{ route('portal.dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#23493A] text-white text-xs font-semibold hover:bg-[#1B3B2F] shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        <span>Return to Client Portal</span>
                    </a>
                @elseif(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#23493A] text-white text-xs font-semibold hover:bg-[#1B3B2F] shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        <span>Return to Admin Console</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#23493A] text-white text-xs font-semibold hover:bg-[#1B3B2F] shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        <span>Return to Chambers</span>
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-lg bg-white border border-[#EAE4DC] text-red-600 text-xs font-semibold hover:bg-red-50 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        <span>Sign Out / Switch Account</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#23493A] text-white text-xs font-semibold hover:bg-[#1B3B2F] shadow-sm transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">login</span>
                    <span>Sign In</span>
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
