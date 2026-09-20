<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $title ?? (config('legal.app_name', 'Sharma Legal Chambers') . ' — Legal Practice Platform') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <!-- Google Fonts: Newsreader & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-canvas-ivory font-body-md text-body-md text-text-primary antialiased flex min-h-screen">
<style>[x-cloak] { display: none !important; }</style>
    
    <!-- LEFT PERSISTENT SIDEBAR (#121513 dark sidebar) -->
    <aside class="w-64 bg-sidebar-bg text-[#e0e3de] flex flex-col justify-between shrink-0 border-r border-sidebar-border fixed top-0 bottom-0 left-0 z-40 select-none">
        <div class="flex flex-col">
            <!-- Chambers Brand Header -->
            <div class="px-4 py-4 border-b border-sidebar-border flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ asset('logo.png') }}" alt="Firm Logo" class="w-8 h-8 rounded shrink-0 object-contain"/>
                    <div class="flex flex-col min-w-0">
                        <span class="font-headline-sm text-[15px] text-white font-serif tracking-tight truncate" title="{{ auth()->user()->firm->name ?? config('legal.app_name', 'Sharma Legal Chambers') }}">
                            {{ auth()->user()->firm->name ?? config('legal.app_name', 'Sharma Legal Chambers') }}
                        </span>
                        <span class="font-caption text-[10px] text-sidebar-text-muted uppercase tracking-wider mt-0.5">
                            Chambers workspace
                        </span>
                    </div>
                </div>
                <button class="text-sidebar-text-muted hover:text-white transition-colors shrink-0" title="Workspace active" type="button">
                    <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-col gap-1 px-3 py-4">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('dashboard') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('dashboard') ? 'text-[#C7EBD9]' : '' }}">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <!-- Matters -->
                <a href="{{ route('matters.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('matters.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('matters.*') ? 'text-[#C7EBD9]' : '' }}">folder_open</span>
                        <span>Matters</span>
                    </div>
                    @php $mCount = \App\Models\Matter::where('firm_id', auth()->user()->firm_id ?? 1)->count(); @endphp
                    <span class="font-caption text-[11px] px-1.5 py-0.2 rounded-full {{ request()->routeIs('matters.*') ? 'bg-white/20 text-white' : 'bg-sidebar-surface text-sidebar-text-muted' }}">{{ $mCount }}</span>
                </a>

                <!-- Clients -->
                <a href="{{ route('clients.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('clients.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('clients.*') ? 'text-[#C7EBD9]' : '' }}">corporate_fare</span>
                        <span>Clients</span>
                    </div>
                    @php $cCount = \App\Models\Client::where('firm_id', auth()->user()->firm_id ?? 1)->count(); @endphp
                    <span class="font-caption text-[11px] px-1.5 py-0.2 rounded-full {{ request()->routeIs('clients.*') ? 'bg-white/20 text-white' : 'bg-sidebar-surface text-sidebar-text-muted' }}">{{ $cCount }}</span>
                </a>

                <!-- Documents -->
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('documents.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('documents.*') ? 'text-[#C7EBD9]' : '' }}">description</span>
                    <span>Documents</span>
                </a>

                <!-- Legal Opinions -->
                <a href="{{ route('opinions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('opinions.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('opinions.*') ? 'text-[#C7EBD9]' : '' }}">draw</span>
                    <span>Legal Opinions</span>
                </a>

                <!-- Tasks -->
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('tasks.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('tasks.*') ? 'text-[#C7EBD9]' : '' }}">checklist</span>
                    <span>Tasks</span>
                </a>

                <!-- Calendar & Docket -->
                <a href="{{ route('calendar.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('calendar.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('calendar.*') ? 'text-[#C7EBD9]' : '' }}">calendar_month</span>
                    <span>Litigation Calendar</span>
                </a>

                <!-- Billing & Retainers -->
                <a href="{{ route('billing.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('billing.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('billing.*') ? 'text-[#C7EBD9]' : '' }}">receipt_long</span>
                    <span>Billing</span>
                </a>

                <!-- Advocates & Staff -->
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('users.*') || request()->routeIs('user-groups.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('users.*') ? 'text-[#C7EBD9]' : '' }}">group</span>
                    <span>Advocates &amp; Staff</span>
                </a>

                <!-- Firm Settings -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-title-sm text-[13px] transition-colors {{ request()->routeIs('settings.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('settings.*') ? 'text-[#C7EBD9]' : '' }}">settings</span>
                    <span>Firm settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer (User & Sign Out) -->
        <div class="p-3 border-t border-sidebar-border flex flex-col gap-2">
            <div class="flex items-center gap-3 p-2 rounded-lg bg-sidebar-surface">
                @php
                    $uName = auth()->user()->name ?? 'Counsel';
                    $words = explode(' ', trim($uName));
                    $initials = count($words) >= 2 ? strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1)) : strtoupper(substr($uName, 0, 2));
                @endphp
                <div class="w-8 h-8 rounded-full bg-pine-primary text-[#C7EBD9] flex items-center justify-center font-title-sm text-[12px] font-semibold shrink-0">
                    {{ $initials }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="font-title-sm text-[12px] text-white truncate">{{ $uName }}</span>
                    <span class="font-caption text-[10px] text-sidebar-text-muted truncate">
                        {{ auth()->user()->title ?? ucfirst(auth()->user()->role ?? 'Partner') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center justify-between px-2 text-[11px] text-sidebar-text-muted">
                <span>Chambers Seal #SL-8821</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button class="hover:text-white transition-colors flex items-center gap-1 cursor-pointer" type="submit" title="Sign out of Chambers">
                        <span>Sign out</span>
                        <span class="material-symbols-outlined text-[14px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER WITH TOP BAR & CONTENT AREA -->
    <div class="flex-1 flex flex-col pl-64 min-w-0 bg-canvas-ivory min-h-screen">
        
        <!-- TOP BAR -->
        <header class="h-16 bg-surface-card/95 backdrop-blur-md border-b border-border-hairline px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-30">
            <!-- Search Input -->
            <div class="relative w-full max-w-md">
                <form action="{{ route('search') }}" method="GET" class="relative">
                    <span class="material-symbols-outlined text-[18px] text-text-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">search</span>
                    <input name="q" value="{{ request('q') }}" class="w-full bg-surface-subtle border border-border-hairline rounded pl-9 pr-12 py-1.5 text-body-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:border-pine-primary focus:ring-1 focus:ring-pine-primary transition-all" placeholder="Search matters, clients, documents..." type="text"/>
                    <kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline font-caption text-[11px] text-text-secondary">⌘K</kbd>
                </form>
            </div>

            <!-- Right Header Tools -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Subtle Live Environment Indicator -->
                <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-text-secondary bg-surface-subtle border border-border-hairline px-2.5 py-1 rounded">
                    <span class="w-1.5 h-1.5 rounded-full bg-pine-primary animate-pulse"></span>
                    <span>Chambers live environment · Q2 Term</span>
                </span>

                <!-- Notification Bell -->
                <button class="relative w-9 h-9 flex items-center justify-center rounded border border-border-hairline bg-surface-card text-text-secondary hover:text-text-primary hover:bg-surface-subtle transition-colors" title="Notifications" type="button">
                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                    @php
                        $unreadAlerts = ($tasksOverdueCount ?? 0) + ($uploadsToReviewCount ?? 0);
                        if ($unreadAlerts === 0) { $unreadAlerts = 2; }
                    @endphp
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">{{ min($unreadAlerts, 9) }}</span>
                </button>

                <!-- Profile Dropdown & Super Admin Console -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-1.5 p-1 rounded hover:bg-surface-subtle transition-all cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-pine-primary text-white flex items-center justify-center font-title-sm text-[12px] font-semibold">
                            {{ $initials }}
                        </div>
                        <span class="material-symbols-outlined text-sm text-text-muted">expand_more</span>
                    </button>

                    <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-transition class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-border-hairline py-2 z-50 text-xs">
                        <div class="px-4 py-2.5 border-b border-border-hairline">
                            <span class="font-semibold text-sm text-text-primary block">{{ auth()->user()->name }}</span>
                            <span class="text-[11px] text-text-muted block">{{ auth()->user()->email }}</span>
                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-surface-subtle text-pine-primary font-bold border border-border-hairline">
                                {{ auth()->user()->title ?? ucfirst(auth()->user()->role) }}
                            </span>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 hover:bg-surface-subtle text-pine-primary font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-pine-primary">admin_panel_settings</span>
                            <span>Platform Super Admin</span>
                        </a>
                        <div class="h-px bg-border-hairline my-1"></div>
                        @endif

                        <a href="{{ route('profile.show') }}" class="px-4 py-2 hover:bg-surface-subtle text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-pine-primary">person</span>
                            <span>My Profile &amp; Security</span>
                        </a>

                        <a href="{{ route('settings.index') }}" class="px-4 py-2 hover:bg-surface-subtle text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">settings</span>
                            <span>Firm Settings</span>
                        </a>

                        <div class="h-px bg-border-hairline my-1"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-700 hover:bg-red-50 flex items-center gap-2 font-medium cursor-pointer">
                                <span class="material-symbols-outlined text-sm">logout</span>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN PAGE CONTENT -->
        <main class="flex-1 p-6 lg:p-8 max-w-[1400px] w-full mx-auto">
            <!-- Flash Alerts -->
            @if(session('success'))
            <div class="mb-6 p-3.5 rounded-lg bg-[#eef5f1] border border-[#c4ded0] text-xs text-[#1e4636] flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-lg text-pine-primary">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <span class="text-[10px] font-mono uppercase text-pine-primary font-bold">Confirmed</span>
            </div>
            @endif

            @if(session('error') || (isset($errors) && $errors->any()))
            <div class="mb-6 p-3.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-lg text-error">error</span>
                    <span class="font-medium">{{ session('error') ?? ($errors->first() ?? '') }}</span>
                </div>
                <span class="text-[10px] font-mono uppercase text-error font-bold">Alert</span>
            </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="w-full bg-surface-card border-t border-border-hairline py-3 px-8 mt-auto">
            <div class="max-w-[1400px] mx-auto flex flex-col md:flex-row items-center justify-between gap-2 font-caption text-caption text-text-secondary">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-[16px] text-pine-primary">shield_locked</span>
                    <span>Archival grade chambers encryption</span>
                    <span class="inline-block w-1 h-1 rounded-full bg-outline-variant"></span>
                    <span>Two-step sign-in active</span>
                    <span class="inline-block w-1 h-1 rounded-full bg-outline-variant"></span>
                    <span>Audit trail enabled</span>
                </div>
                <div class="flex items-center gap-space-lg text-text-muted">
                    <span class="font-mono">DOCKET-SYNC v4.12.0</span>
                    <span>Chambers Seal #SL-8821</span>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
