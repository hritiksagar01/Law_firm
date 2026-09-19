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

    <!-- Left Sidebar Shell (Quire Platform Administration #161718) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed left-0 top-0 h-screen w-64 bg-[#161718] text-[#d1d5db] z-50 flex flex-col justify-between border-r border-[#26282a] select-none transition-transform duration-300 ease-in-out">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Brand & Platform Header -->
            <div class="px-6 py-5 border-b border-white/[.08] flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="block">
                    <span class="font-serif text-[22px] tracking-tight text-white font-normal block leading-tight">Quire</span>
                    <span class="text-[12px] text-[#8e8e8e] block mt-0.5 font-sans">Platform administration</span>
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

                <!-- Firms & plans -->
                <a href="{{ route('admin.firms.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.firms.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.firms.*') ? 'text-white' : 'text-[#9ca3af]' }}">balance</span>
                    <span>Firms &amp; plans</span>
                </a>

                <!-- Users -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-[#9ca3af]' }}">group</span>
                    <span>Users</span>
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
                <a href="{{ route('admin.plans.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors text-[#9ca3af] hover:text-white hover:bg-white/[.05]">
                    <span class="material-symbols-outlined text-[19px] text-[#9ca3af]">tune</span>
                    <span>Roles &amp; categories</span>
                </a>

                <!-- System settings -->
                <a href="{{ route('admin.profile.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('admin.profile.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('admin.profile.*') ? 'text-white' : 'text-[#9ca3af]' }}">settings</span>
                    <span>System settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom User Profile & Sign Out -->
        <div class="p-4 border-t border-white/[.08] bg-[#161718] flex flex-col gap-2">
            @php
                $currentUser = Auth::user();
                $displayName = $currentUser?->name ?? 'Rowan Blake';
                $userEmail = $currentUser?->email ?? 'admin@quire.example';
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
        
        <!-- Mobile Top Navigation Header -->
        <div class="lg:hidden h-14 bg-[#161718] border-b border-white/[.08] px-4 flex items-center justify-between sticky top-0 z-30">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-[19px] text-white font-normal">Quire</a>
            <button @click="sidebarOpen = true" class="p-2 text-white">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
        </div>

        <!-- Top Demo Environment Notice Banner -->
        <div class="w-full text-center py-2.5 text-[12px] text-[#8a8e89] font-sans border-b border-[#f0eee8]/60">
            Demo environment · fictional firms, people and documents
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
