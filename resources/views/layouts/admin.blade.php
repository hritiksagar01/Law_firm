<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Platform Governance Console') — Quire Legal Multi-Tenant Cloud</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <!-- Google Fonts: Newsreader & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-canvas-ivory font-body-md text-body-md text-text-primary antialiased min-h-screen"
      x-data="{ 
          sidebarOpen: false, 
          userMenuOpen: false, 
          changePasswordModal: false, 
          changeAvatarModal: false 
      }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden transition-opacity">
    </div>

    <!-- Left Sidebar Shell (Quire Persistent Dark Sidebar #121513) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed left-0 top-0 h-screen w-64 bg-sidebar-bg text-[#e0e3de] z-50 flex flex-col justify-between border-r border-sidebar-border select-none transition-transform duration-300 ease-in-out">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Brand & Platform Header -->
            <div class="px-5 py-5 border-b border-sidebar-border flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Quire Legal" class="h-9 w-auto object-contain rounded shadow-xs bg-white/95 p-1"/>
                    <div class="flex flex-col min-w-0">
                        <span class="font-serif text-[17px] font-medium text-white tracking-tight leading-tight truncate">Platform Console</span>
                        <span class="text-[11px] font-mono text-sidebar-text-muted uppercase tracking-wider mt-0.5">Multi-Tenant Cloud</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-sidebar-text-muted hover:text-white rounded">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Navigation Sections -->
            <nav class="px-3 py-4 flex flex-col gap-1">
                <span class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-sidebar-text-muted font-medium">Governance &amp; Tenants</span>

                <!-- Telemetry Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.dashboard') ? 'text-[#C7EBD9]' : '' }}">dashboard</span>
                    <span>Tenant Telemetry</span>
                </a>

                <!-- Firms Management -->
                <a href="{{ route('admin.firms.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.firms.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.firms.*') ? 'text-[#C7EBD9]' : '' }}">corporate_fare</span>
                    <span>Law Firm Practices</span>
                </a>

                <!-- Subscription Plans -->
                <a href="{{ route('admin.plans.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.plans.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.plans.*') ? 'text-[#C7EBD9]' : '' }}">subscriptions</span>
                    <span>Subscription Plans</span>
                </a>

                <!-- Subscription Renewals & Governance -->
                <a href="{{ route('admin.subscriptions.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.subscriptions.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.subscriptions.*') ? 'text-[#C7EBD9]' : '' }}">autorenew</span>
                    <span>Renewals &amp; Status</span>
                </a>

                <span class="px-2.5 pt-4 pb-1 font-mono text-[10px] uppercase tracking-wider text-sidebar-text-muted font-medium">Infrastructure &amp; Access</span>

                <!-- Mail Gateway -->
                <a href="{{ route('admin.settings.mail') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.settings.*') ? 'text-[#C7EBD9]' : '' }}">mail</span>
                    <span>Mail Gateway</span>
                </a>

                <!-- Super Admin Profile -->
                <a href="{{ route('admin.profile.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md font-title-sm text-[13px] transition-colors {{ request()->routeIs('admin.profile.*') ? 'bg-pine-primary text-white shadow-sm font-semibold' : 'text-[#a6aca7] hover:text-white hover:bg-sidebar-surface' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.profile.*') ? 'text-[#C7EBD9]' : '' }}">manage_accounts</span>
                    <span>Admin Profile</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom Footer -->
        <div class="p-3 border-t border-sidebar-border bg-sidebar-bg flex flex-col gap-2">
            @if(Auth::check() && Auth::user()->firm_id)
            <a href="{{ route('dashboard') }}" 
               class="flex items-center justify-center gap-2 px-3 py-2 rounded-md text-xs font-medium text-[#a6aca7] hover:text-white hover:bg-sidebar-surface border border-transparent transition-all">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Switch to Firm Chambers</span>
            </a>
            @endif

            <div class="flex items-center justify-between px-2 pt-1 text-[11px] text-sidebar-text-muted font-mono">
                <span>Infrastructure Isolation</span>
                <span class="inline-flex items-center gap-1.5 text-[#8fb7a4]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Active Live</span>
                </span>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper with Left Margin on Desktop -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        
        <!-- Top Executive Bar (Sticky) -->
        <header class="h-16 bg-surface-card border-b border-border-hairline px-4 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" 
                        class="lg:hidden p-2 rounded-md text-text-secondary hover:bg-surface-subtle transition-all">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2.5">
                    <span class="font-serif text-lg font-medium text-text-primary">@yield('header_title', 'Multi-Tenant Governance Console')</span>
                    <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-pine-primary font-mono bg-pine-primary/10 px-2 py-0.5 rounded border border-pine-primary/20">
                        <span>Cluster Host</span>
                        <span>·</span>
                        <span>Root Isolation</span>
                    </span>
                </div>
            </div>

            <!-- Top Right Profile Dropdown & Controls -->
            <div class="flex items-center gap-3">
                @php
                    $currentUser = Auth::user();
                    $avatar = $currentUser?->resolved_avatar;
                    $displayName = $currentUser?->full_display_name ?? 'Super Administrator';
                    $userEmail = $currentUser?->email ?? 'admin@sharmalegal.in';
                    $initials = strtoupper(substr($currentUser?->first_name ?? $currentUser?->name ?? 'S', 0, 1)) . strtoupper(substr($currentUser?->surname ?? 'A', 0, 1));
                @endphp

                <!-- User Profile Menu -->
                <div class="relative" @click.outside="userMenuOpen = false">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-2.5 p-1 rounded-md hover:bg-surface-subtle transition-all focus:outline-none focus:ring-2 focus:ring-pine-primary/30">
                        @if($avatar)
                            <img src="{{ $avatar }}" 
                                 alt="{{ $displayName }}" 
                                 class="w-8 h-8 rounded-full object-cover border border-border-hairline shadow-xs"/>
                        @else
                            <div class="w-8 h-8 rounded-full bg-pine-primary text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ $initials }}
                            </div>
                        @endif
                        <div class="hidden md:flex flex-col text-left pr-1">
                            <span class="text-xs font-semibold text-text-primary leading-tight">
                                {{ $displayName }}
                            </span>
                            <span class="text-[10px] font-mono text-text-secondary leading-tight">
                                Chief Platform Officer
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-sm text-text-muted">expand_more</span>
                    </button>

                    <!-- Account Management Dropdown -->
                    <div x-show="userMenuOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-surface-card rounded-md shadow-quire border border-border-hairline py-1.5 z-50 divide-y divide-border-hairline">
                        
                        <div class="px-4 py-2.5">
                            <p class="text-xs font-semibold text-text-primary truncate">{{ $displayName }}</p>
                            <p class="text-[11px] font-mono text-text-secondary truncate">{{ $userEmail }}</p>
                        </div>

                        <div class="py-1">
                            <button type="button" 
                                    @click="userMenuOpen = false; changePasswordModal = true" 
                                    class="w-full text-left px-4 py-2 text-xs text-text-secondary hover:bg-surface-subtle hover:text-text-primary flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base text-pine-primary">lock_reset</span>
                                <span>Change Security Key</span>
                            </button>

                            <button type="button" 
                                    @click="userMenuOpen = false; changeAvatarModal = true" 
                                    class="w-full text-left px-4 py-2 text-xs text-text-secondary hover:bg-surface-subtle hover:text-text-primary flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base text-pine-primary">account_circle</span>
                                <span>Update Avatar</span>
                            </button>

                            <a href="{{ route('admin.profile.index') }}" 
                               class="block px-4 py-2 text-xs text-text-secondary hover:bg-surface-subtle hover:text-text-primary flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base text-pine-primary">settings</span>
                                <span>Administrator Preferences</span>
                            </a>
                        </div>

                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-4 py-2 text-xs text-error hover:bg-error-container/40 flex items-center gap-2.5 transition-all">
                                    <span class="material-symbols-outlined text-base">logout</span>
                                    <span>Sign Out Console</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Main Content -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-md bg-surface-card border-l-[3px] border-pine-primary border border-border-hairline shadow-sm flex items-center gap-3">
                    <span class="material-symbols-outlined text-pine-primary text-xl">check_circle</span>
                    <p class="text-xs text-text-primary font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-md bg-surface-card border-l-[3px] border-error border border-border-hairline shadow-sm flex items-center gap-3">
                    <span class="material-symbols-outlined text-error text-xl">error</span>
                    <p class="text-xs text-error font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Archival Footer -->
        <footer class="mt-auto border-t border-border-hairline bg-surface-card py-4 px-6 text-center text-xs text-text-muted font-caption flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <span>Quire Legal Cloud</span>
                <span>·</span>
                <span>Multi-Tenant Infrastructure Architecture</span>
                <span>·</span>
                <span>ISO 27001 &amp; SOC2 Type II Certified</span>
            </div>
            <div class="font-mono text-[10px]">
                <span>Cluster Node: AP-SOUTH-1</span>
                <span>·</span>
                <span>Latency: 0.14ms</span>
            </div>
        </footer>
    </div>

    <!-- Modals (Change Password & Avatar) -->
    <!-- Modal: Change Password -->
    <div x-show="changePasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="changePasswordModal = false"
             class="bg-surface-card border border-border-hairline rounded-lg max-w-md w-full p-6 shadow-quire">
            <div class="flex items-center justify-between pb-4 border-b border-border-hairline">
                <h3 class="font-serif text-lg font-medium text-text-primary">Update Security Credentials</h3>
                <button @click="changePasswordModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('admin.profile.change-password') }}" method="POST" class="mt-4 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-primary">Current Password</label>
                    <input type="password" name="current_password" required class="h-10 px-3 rounded-md border border-border-hairline bg-white text-xs text-text-primary focus:border-pine-primary focus:outline-none"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-primary">New Password</label>
                    <input type="password" name="password" required class="h-10 px-3 rounded-md border border-border-hairline bg-white text-xs text-text-primary focus:border-pine-primary focus:outline-none"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-primary">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="h-10 px-3 rounded-md border border-border-hairline bg-white text-xs text-text-primary focus:border-pine-primary focus:outline-none"/>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="changePasswordModal = false" class="btn-secondary h-9 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 text-xs">Save Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Change Avatar -->
    <div x-show="changeAvatarModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="changeAvatarModal = false"
             class="bg-surface-card border border-border-hairline rounded-lg max-w-md w-full p-6 shadow-quire">
            <div class="flex items-center justify-between pb-4 border-b border-border-hairline">
                <h3 class="font-serif text-lg font-medium text-text-primary">Update Avatar Portrait</h3>
                <button @click="changeAvatarModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('admin.profile.change-avatar') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-primary">Select Image File</label>
                    <input type="file" name="avatar" accept="image/*" required class="h-10 text-xs text-text-primary file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-surface-subtle file:text-pine-primary hover:file:bg-surface-container-high"/>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="changeAvatarModal = false" class="btn-secondary h-9 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 text-xs">Upload Avatar</button>
                </div>
            </form>
        </div>
    </div>

    @livewireScripts
</body>
</html>
