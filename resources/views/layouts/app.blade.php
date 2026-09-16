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
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen" x-data="{ ...lawyerTimer(), openTimeModal: false }" @keydown.window.cmd.t.prevent="openTimeModal = true" @keydown.window.ctrl.t.prevent="openTimeModal = true">
<style>[x-cloak] { display: none !important; }</style>
    
    <!-- Sidebar Navigation Shell (Juris Prestige White & Warm Cognac Brown #9F8349) -->
    <aside class="fixed left-0 top-0 h-screen w-[260px] bg-white text-[#222222] z-50 flex flex-col justify-between border-r border-[#EFECE6] shadow-[1px_0_12px_rgba(159,131,73,0.04)]">
        <div class="flex flex-col">
            <!-- Brand & Chambers Header -->
            <div class="p-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5 mb-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-9 w-auto object-contain rounded-md shadow-xs"/>
                        <span class="font-serif text-sm text-[#222222] font-bold tracking-tight truncate leading-tight">{{ config('legal.app_name', 'Vennamraj Associates') }}</span>
                    </a>
                </div>
                <button class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-[#FAF8F5] hover:bg-[#F5F0E8] transition-colors text-left border border-[#EFECE6]">
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-[#222222] truncate font-semibold">{{ auth()->user()->firm->name ?? 'Vennamraj Associates' }}</span>
                        <span class="font-mono text-[10px] text-[#766A5E] truncate">{{ auth()->user()->firm->address ? Str::limit(auth()->user()->firm->address, 30) : 'Delhi High Court Chambers' }}</span>
                    </div>
                    <span class="material-symbols-outlined text-[#766A5E] text-base">unfold_more</span>
                </button>
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

                <a href="{{ route('billing.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('billing.*') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        <span>Billing &amp; Trust</span>
                    </div>
                </a>

                <a href="{{ route('briefing') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('briefing') ? 'bg-[#9F8349] text-white font-medium shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg">newspaper</span>
                        <span>Daily Broadsheet</span>
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

            <!-- Active Billable Stopwatch & Profile Tools -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Live Stopwatch Pill Widget -->
                <div class="flex items-center gap-2 bg-[#FAF8F5] border border-[#EAE4DC] px-3 h-9 rounded-lg font-mono text-xs text-[#222222]">
                    <button @click="toggle()" class="hover:opacity-80 transition-opacity" :title="running ? 'Pause Timer' : 'Start Timer'">
                        <span class="material-symbols-outlined text-[#9F8349] text-base" :class="running ? 'animate-pulse text-[#B88B56]' : ''" x-text="running ? 'pause_circle' : 'play_circle'">play_circle</span>
                    </button>
                    <span class="font-semibold text-[#9F8349]" x-text="formattedTime">02:14:00</span>
                    <span class="text-[#8C7F72]">—</span>
                    <span class="text-[#554D45] max-w-[140px] truncate" x-text="matter">Malhotra v. Apex Bank</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-[#F8F4EE] text-[#9F8349] border border-[#EAE4DC] font-sans text-[10px] font-semibold">Billable</span>
                </div>

                <!-- Quick Log Billable Time Button (⌘T) -->
                <button @click="openTimeModal = true" class="inline-flex items-center gap-1 px-3 h-9 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-sm">
                    <span class="material-symbols-outlined text-base">timer</span>
                    <span>Log Time</span>
                    <span class="font-mono text-[9px] bg-black/20 px-1 py-0.5 rounded text-white/90 ml-0.5">⌘T</span>
                </button>

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

    <!-- Quick Time Entry Modal -->
    <div x-show="openTimeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl p-6 flex flex-col gap-4 border border-[#EFECE6]" @click.away="openTimeModal = false">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#F8F4EE] border border-[#EAE4DC] flex items-center justify-center text-[#9F8349]">
                        <span class="material-symbols-outlined text-lg">timer</span>
                    </div>
                    <div>
                        <h3 class="text-base font-serif font-bold text-[#222222]">Log Billable Legal Hours</h3>
                        <p class="text-xs text-[#766A5E]">Capture attorney time with LEDES/UTBMS activity codes</p>
                    </div>
                </div>
                <button type="button" class="p-1 text-[#766A5E] hover:text-[#222222] rounded hover:bg-[#FAF8F5]" @click="openTimeModal = false">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('time-entries.store') }}" method="POST" class="flex flex-col gap-3">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-semibold text-[#554D45] uppercase tracking-wider">Matter Dossier</label>
                    <select name="matter_id" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                        @foreach(\App\Models\Matter::all() as $m)
                            <option value="{{ $m->id }}">{{ $m->case_number }} — {{ $m->title }} ({{ $m->practice_area }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-[#554D45] uppercase tracking-wider">Duration (Hours)</label>
                        <input name="hours" type="number" step="0.25" min="0.25" value="1.75" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm font-mono text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[11px] font-semibold text-[#554D45] uppercase tracking-wider">Activity / Professional Service</label>
                        <select name="activity_code" class="w-full h-10 px-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white">
                            <option value="L120">L120 — Court Appearance &amp; Arguments</option>
                            <option value="L110">L110 — Plaint, Petition &amp; Reply Drafting</option>
                            <option value="L330">L330 — Client Conference &amp; Case Strategy</option>
                            <option value="A104">A104 — Evidence, Certified Copies &amp; Registry</option>
                            <option value="B110">B110 — Written Submissions &amp; Case Law</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-semibold text-[#554D45] uppercase tracking-wider">Narrative Description</label>
                    <textarea name="narrative" rows="3" class="w-full p-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-sm text-[#222222] focus:outline-none focus:border-[#9F8349] focus:bg-white resize-none" placeholder="Drafted reply brief regarding motion in limine; reviewed forensic financial transcripts..."></textarea>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-[#EFECE6]">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_billable" value="1" checked class="w-4 h-4 rounded text-[#9F8349] accent-[#9F8349]"/>
                        <span class="text-xs font-medium text-[#222222]">Billable to Client</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5]" @click="openTimeModal = false">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm">Record Entry</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @livewireScripts
</body>
</html>
