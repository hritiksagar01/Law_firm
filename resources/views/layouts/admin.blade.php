<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Platform Governance Console') — Multi-Tenant Legal Cloud</title>
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
<body class="bg-[#fbf9f5] font-sans text-[#1b1c18] antialiased min-h-screen"
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
         class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden transition-opacity">
    </div>

    <!-- Left Sidebar Shell (Platform Administration #161718) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed left-0 top-0 h-screen w-64 bg-[#161718] text-[#d1d5db] z-50 flex flex-col justify-between border-r border-[#26282a] select-none transition-transform duration-300 ease-in-out">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Brand & Platform Header -->
            <div class="px-6 py-5 border-b border-white/[.08] flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-10 w-auto object-contain rounded"/>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[13.5px] font-semibold text-white tracking-wide block leading-tight">Super Admin</span>
                        <span class="text-[11px] text-[#8e8e8e] block mt-0.5 font-sans">Platform Console</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-[#8e8e8e] hover:text-white rounded">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="px-3 py-4 flex flex-col gap-1">
                <!-- Overview -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-[#9ca3af]' }}">bar_chart</span>
                    <span>Overview</span>
                </a>

                <!-- Firms -->
                <a href="{{ route('admin.firms.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.firms.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.firms.*') ? 'text-white' : 'text-[#9ca3af]' }}">balance</span>
                    <span>Firms</span>
                </a>

                <!-- Clients -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ (request()->routeIs('admin.users.*') || request()->routeIs('admin.clients.*')) ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ (request()->routeIs('admin.users.*') || request()->routeIs('admin.clients.*')) ? 'text-white' : 'text-[#9ca3af]' }}">group</span>
                    <span>Clients</span>
                </a>

                <!-- Matters -->
                <a href="{{ route('admin.matters.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.matters.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.matters.*') ? 'text-white' : 'text-[#9ca3af]' }}">folder</span>
                    <span>Matters</span>
                </a>

                <!-- Email & SMS -->
                <a href="{{ route('admin.settings.mail') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-[#9ca3af]' }}">mail</span>
                    <span>Email &amp; SMS</span>
                </a>

                <!-- Audit log -->
                <a href="{{ route('admin.audit.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.audit.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.audit.*') ? 'text-white' : 'text-[#9ca3af]' }}">receipt_long</span>
                    <span>Audit log</span>
                </a>

                <!-- Sign-in history -->
                <a href="{{ route('admin.sign-ins.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.sign-ins.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.sign-ins.*') ? 'text-white' : 'text-[#9ca3af]' }}">key</span>
                    <span>Sign-in history</span>
                </a>

                <!-- Roles & categories -->
                <a href="{{ route('admin.roles.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.roles.*') ? 'text-white' : 'text-[#9ca3af]' }}">tune</span>
                    <span>Roles &amp; categories</span>
                </a>

                <!-- System settings -->
                <a href="{{ route('admin.system.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.system.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.system.*') ? 'text-white' : 'text-[#9ca3af]' }}">settings</span>
                    <span>System settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom User Profile & Sign Out -->
        <div class="p-4 border-t border-white/[.08] bg-[#161718] flex flex-col gap-2">
            @php
                $currentUser = Auth::user();
                $displayName = $currentUser?->name ?? 'Rowan Blake';
                $userEmail = $currentUser?->email ?? 'admin@sharmalegal.in';
                $initials = 'RB';
                if ($displayName) {
                    $parts = explode(' ', trim($displayName));
                    $initials = strtoupper(substr($parts[0] ?? 'R', 0, 1) . substr($parts[count($parts) - 1] ?? 'B', 0, 1));
                }
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#5b4382] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                    {{ $initials }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[13px] font-medium text-white truncate leading-tight">{{ $displayName }}</span>
                    <span class="text-[11.5px] text-[#8e8e8e] truncate mt-0.5">{{ $userEmail }}</span>
                </div>
            </div>
            <div class="pt-1 flex items-center justify-between">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-[12px] text-[#8e8e8e] hover:text-white transition-colors cursor-pointer">
                        Sign out
                    </button>
                </form>
                @if(Auth::check() && Auth::user()->firm_id)
                <a href="{{ route('dashboard') }}" class="text-[11px] text-[#8e8e8e] hover:text-white transition-colors">
                    Chambers →
                </a>
                @endif
            </div>
        </div>
    </aside>

    <!-- Main Wrapper with Left Margin on Desktop -->
    <div class="lg:pl-64 flex flex-col min-h-screen bg-[#fbf9f5]">
        
        <!-- Desktop & Mobile Top Header Bar with Notifications, Settings & Profile -->
        <header class="h-14 bg-white border-b border-[#e5e3dc] px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <!-- Left: Mobile toggle + Breadcrumb / Context -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-1.5 text-[#5e625e] hover:text-[#1b1c18] rounded-md transition-colors">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2 text-xs text-[#5e625e]">
                    <span class="font-medium text-[#1b1c18] tracking-tight text-[13px]">Super Admin Platform</span>
                    <span class="text-[#c1c8c3] hidden sm:inline">/</span>
                    <span class="hidden sm:inline text-[12px] font-sans">@yield('header_title', 'Platform Console')</span>
                </div>
            </div>

            <!-- Right: System Status, Notifications, Settings, Profile Dropdown -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Status Pill -->
                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10b981] animate-pulse"></span>
                    Operational
                </span>

                <!-- Notification Bell Dropdown -->
                <div class="relative" x-data="{ notificationsOpen: false }" @click.outside="notificationsOpen = false">
                    <button @click="notificationsOpen = !notificationsOpen" 
                            type="button" 
                            class="relative p-2 text-[#5e625e] hover:text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors cursor-pointer" 
                            title="Platform Notifications">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#23493a]"></span>
                    </button>

                    <!-- Notifications Menu -->
                    <div x-show="notificationsOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white border border-[#e5e3dc] rounded-md shadow-lg py-2 z-50 text-xs">
                        <div class="px-4 py-2 border-b border-[#f0eee8] flex items-center justify-between">
                            <span class="font-semibold text-[#1b1c18] text-[12.5px]">Platform Notifications</span>
                            <span class="text-[10.5px] px-1.5 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e]">Live</span>
                        </div>
                        <div class="divide-y divide-[#f5f3ed] max-h-64 overflow-y-auto">
                            <div class="px-4 py-2.5 hover:bg-[#faf8f5] transition-colors">
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-[#23493a]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></span> Security &amp; MFA
                                </div>
                                <p class="text-[11.5px] text-[#414844] mt-0.5">Two-step sign-in enforcement active across firms.</p>
                                <span class="text-[10px] text-[#8a8e89] mt-1 block">Platform default active</span>
                            </div>
                            <div class="px-4 py-2.5 hover:bg-[#faf8f5] transition-colors">
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-[#0369a1]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0284c7]"></span> Audit Storage
                                </div>
                                <p class="text-[11.5px] text-[#414844] mt-0.5">Append-only audit trail logging active and tamper-evident.</p>
                                <span class="text-[10px] text-[#8a8e89] mt-1 block">Continuous logging</span>
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-[#f0eee8] bg-[#faf8f5] text-center">
                            <a href="{{ route('admin.settings.mail') }}" class="text-[11.5px] font-medium text-[#23493a] hover:underline">
                                View delivery gateway logs &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Settings Gear Icon -->
                <a href="{{ route('admin.system.index') }}" 
                   class="p-2 text-[#5e625e] hover:text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors" 
                   title="System Settings">
                    <span class="material-symbols-outlined text-[20px]">settings</span>
                </a>

                <div class="h-5 w-px bg-[#e5e3dc] mx-1"></div>

                <!-- User Profile Dropdown -->
                <div class="relative" x-data="{ profileDropdownOpen: false }" @click.outside="profileDropdownOpen = false">
                    <button @click="profileDropdownOpen = !profileDropdownOpen" 
                            type="button" 
                            class="flex items-center gap-2 p-1 pl-1.5 pr-2 rounded-md hover:bg-[#f5f3ed] transition-colors cursor-pointer">
                        <div class="w-7 h-7 rounded-full bg-[#23493a] text-white flex items-center justify-center font-semibold text-[11px] shrink-0 shadow-xs">
                            {{ $initials }}
                        </div>
                        <span class="hidden sm:inline text-xs font-medium text-[#1b1c18] max-w-[120px] truncate">
                            {{ $displayName }}
                        </span>
                        <span class="material-symbols-outlined text-[16px] text-[#5e625e]">expand_more</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileDropdownOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white border border-[#e5e3dc] rounded-md shadow-lg py-1.5 z-50 text-xs">
                        <div class="px-3.5 py-2 border-b border-[#f0eee8]">
                            <p class="font-medium text-[#1b1c18] text-[12.5px] truncate">{{ $displayName }}</p>
                            <p class="text-[11px] text-[#5e625e] font-mono truncate mt-0.5">{{ $userEmail }}</p>
                        </div>
                        <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2 px-3.5 py-2 text-[#414844] hover:bg-[#faf8f5] hover:text-[#1b1c18]">
                            <span class="material-symbols-outlined text-base">person</span>
                            <span>Super Admin Profile</span>
                        </a>
                        <button type="button" @click="profileDropdownOpen = false; changePasswordModal = true" class="w-full flex items-center gap-2 px-3.5 py-2 text-[#414844] hover:bg-[#faf8f5] hover:text-[#1b1c18] text-left cursor-pointer">
                            <span class="material-symbols-outlined text-base">lock</span>
                            <span>Change Password</span>
                        </button>
                        <button type="button" @click="profileDropdownOpen = false; changeAvatarModal = true" class="w-full flex items-center gap-2 px-3.5 py-2 text-[#414844] hover:bg-[#faf8f5] hover:text-[#1b1c18] text-left cursor-pointer">
                            <span class="material-symbols-outlined text-base">photo_camera</span>
                            <span>Change Avatar</span>
                        </button>
                        <div class="border-t border-[#f0eee8] my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-2 text-[#ba1a1a] hover:bg-red-50 text-left cursor-pointer">
                                <span class="material-symbols-outlined text-base">logout</span>
                                <span>Sign out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Top Demo Environment Notice Banner -->
        <div class="w-full text-center py-2 text-[12px] text-[#8a8e89] font-sans border-b border-[#f0eee8]/60 bg-[#faf8f5]">
            Demo environment &middot; fictional firms, people and documents
        </div>

        <!-- Dynamic Main Content -->
        <main class="flex-1 w-full max-w-[1240px] mx-auto px-6 sm:px-10 py-6 lg:py-8">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-6 p-3.5 rounded-md bg-[#23493a]/10 border border-[#23493a]/20 text-xs text-[#23493a] flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-3.5 rounded-md bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base text-red-600">error</span>
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Modals (Change Password & Avatar) -->
    <!-- Modal: Change Password -->
    <div x-show="changePasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="changePasswordModal = false"
             class="bg-surface-card border border-border-hairline rounded-lg max-w-md w-full p-6 shadow-xl">
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
             class="bg-surface-card border border-border-hairline rounded-lg max-w-md w-full p-6 shadow-xl">
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
