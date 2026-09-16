<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $title ?? (config('legal.app_name', 'Vennamraj Associates') . ' — Legal Practice Platform') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <!-- Juris Prestige Typography: EB Garamond (Headline) & Manrope (Body/Label) -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen">
<style>[x-cloak] { display: none !important; }</style>
    
    <!-- Sidebar Navigation Shell (Juris Prestige White & Warm Cognac Brown #9F8349) -->
    <aside class="fixed left-0 top-0 h-screen w-[260px] bg-white text-[#222222] z-50 flex flex-col justify-between border-r border-[#EFECE6] shadow-[1px_0_12px_rgba(159,131,73,0.04)]">
        <div class="flex flex-col">
            <!-- Brand & Chambers Header -->
            <div class="p-4 border-b border-[#EFECE6]">
                <div class="flex items-center justify-center">
                    <a href="{{ route('dashboard') }}" class="block">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-12 w-auto object-contain rounded-lg shadow-xs"/>
                    </a>
                </div>
            </div>

            <!-- Practice Navigation Links -->
            <div class="px-2 py-3 flex flex-col gap-0.5">
                <span class="px-2 py-1 font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold">Chambers Practice</span>
                
                <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        <span>Dashboard</span>
                    </div>
                </a>

                <a href="{{ route('matters.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('matters.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">cases</span>
                        <span>Matters</span>
                    </div>
                    <span class="font-mono text-[11px] px-1.5 py-0.5 rounded-full {{ request()->routeIs('matters.*') ? 'bg-white/20 text-white' : 'bg-[#F8F4EE] text-[#9F8349]' }}">4</span>
                </a>

                <a href="{{ route('clients.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('clients.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">group</span>
                        <span>Clients</span>
                    </div>
                    <span class="font-mono text-[11px] px-1.5 py-0.5 rounded-full {{ request()->routeIs('clients.*') ? 'bg-white/20 text-white' : 'bg-[#F8F4EE] text-[#9F8349]' }}">4</span>
                </a>

                <a href="{{ route('documents.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('documents.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">description</span>
                        <span>Documents</span>
                    </div>
                </a>

                <a href="{{ route('calendar.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('calendar.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        <span>Calendar &amp; Docket</span>
                    </div>
                </a>

                <a href="{{ route('tasks.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('tasks.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">task_alt</span>
                        <span>Tasks &amp; Notes</span>
                    </div>
                </a>

                <a href="{{ route('settings.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('settings.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">settings</span>
                        <span>Firm Settings</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-[#EFECE6] bg-[#FAF8F5]">
            <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-[#EFECE6] shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <img alt="{{ auth()->user()->name ?? 'Counsel' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#9F8349]/30" src="{{ auth()->user()->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80' }}"/>
                        <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-[#9F8349] ring-2 ring-white"></span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-[#222222] font-semibold truncate">{{ auth()->user()->name ?? 'Counsel' }}</span>
                        <span class="text-[10px] text-[#766A5E] truncate">{{ auth()->user()->title ?? ucfirst(auth()->user()->role ?? 'Attorney') }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign Out of Chambers" class="p-1.5 text-[#766A5E] hover:text-[#9F8349] hover:bg-[#F8F4EE] transition-colors rounded">
                        <span class="material-symbols-outlined text-lg">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="pl-[260px]">
        <!-- Global Top Header Bar -->
        <header class="fixed top-0 left-[260px] right-0 h-16 bg-white/95 backdrop-blur-md border-b border-[#EFECE6] z-40 px-6 flex items-center justify-between shadow-[0_1px_4px_rgba(159,131,73,0.03)]">
            <!-- Search & Quick Action -->
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <form action="{{ route('search') }}" method="GET" class="relative w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#766A5E] text-lg">search</span>
                    <input name="q" value="{{ request('q') }}" class="w-full h-9 pl-9 pr-14 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] placeholder:text-[#8C7F72] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]" placeholder="Search cases, cause lists, orders, clients... (⌘K)" type="text"/>
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 font-mono text-[10px] bg-white text-[#554D45] px-1.5 py-0.5 rounded border border-[#EAE4DC]">⌘K</span>
                </form>
                <a href="{{ route('matters.create') }}" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shrink-0 shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>New Case File</span>
                </a>
            </div>

            <!-- Profile Tools & Logout -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Fast Logout Link (Always Visible) -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 h-9 rounded-lg bg-red-50 text-red-800 text-xs font-medium hover:bg-red-100 transition-colors border border-red-200" title="Sign out to test Login & Sign Up forms">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        <span>Sign Out</span>
                    </button>
                </form>

                <div class="h-5 w-px bg-[#EAE4DC]"></div>

                <!-- Profile Dropdown & Persona Switcher -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-1.5 p-1 rounded-lg hover:bg-[#FAF8F5] transition-all">
                        <img alt="{{ auth()->user()->name ?? 'Counsel' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#9F8349]/30" src="{{ auth()->user()->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80' }}" title="{{ auth()->user()->name ?? 'Counsel' }}"/>
                        <span class="material-symbols-outlined text-sm text-[#766A5E]">expand_more</span>
                    </button>

                    <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-transition class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-[#EFECE6] py-2 z-50 text-xs">
                        <div class="px-4 py-2.5 border-b border-[#EFECE6]">
                            <span class="font-semibold text-sm text-[#222222] block">{{ auth()->user()->name }}</span>
                            <span class="text-[11px] text-[#766A5E] block">{{ auth()->user()->email }}</span>
                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-[#F8F4EE] text-[#9F8349] font-bold border border-[#EAE4DC]">
                                {{ auth()->user()->title ?? ucfirst(auth()->user()->role) }}
                            </span>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 hover:bg-[#F8F4EE] text-[#9F8349] font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-[#9F8349]">admin_panel_settings</span>
                            <span>Super Admin Platform Console</span>
                        </a>
                        <a href="{{ route('admin.settings.environment') }}" class="px-4 py-2 hover:bg-[#F8F4EE] text-[#9F8349] flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-[#B88B56]">tune</span>
                            <span>Cloud &amp; Env Variables</span>
                        </a>
                        <div class="h-px bg-[#EFECE6] my-1"></div>
                        @endif

                        <a href="{{ route('settings.index') }}" class="px-4 py-2 hover:bg-[#FAF8F5] text-[#554D45] flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">settings</span>
                            <span>Chambers Settings</span>
                        </a>

                        <div class="h-px bg-[#EFECE6] my-1"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-700 hover:bg-red-50 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined text-sm">logout</span>
                                <span>Sign Out to Login Form</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main View Canvas -->
        <main class="relative pt-20 bg-[#FAF8F5] min-h-screen p-6">
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-[#F8F4EE] border border-[#E8DAC8] text-xs text-[#856C36] flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-lg text-[#9F8349]">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <span class="text-[10px] font-mono uppercase text-[#9F8349] font-bold">Confirmed</span>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-lg text-red-700">error</span>
                    <span class="font-medium">{{ session('error') ?? $errors->first() }}</span>
                </div>
                <span class="text-[10px] font-mono uppercase text-red-700 font-bold">Alert</span>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
