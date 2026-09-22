<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Client Portal') — {{ \App\Models\PlatformSetting::platformName() }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Models\PlatformSetting::logoUrl() }}"/>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        /* Single Unified Font Family (Inter) across Client Portal */
        body, h1, h2, h3, h4, h5, h6, p, span, a, input, select, textarea, button, table, td, th {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .font-mono, code, pre, [data-mono] {
            font-family: 'JetBrains Mono', monospace !important;
        }
    </style>
</head>
<body class="bg-[#fbf9f5] font-sans text-[#1b1c18] antialiased min-h-screen"
      x-data="{ 
          sidebarOpen: false, 
          changePasswordModal: false, 
          changeAvatarModal: false 
      }">

    @php
        $portalUser = Auth::user();
        $portalClient = \App\Models\Client::where('user_id', $portalUser?->id)->first() 
            ?? \App\Models\Client::where('email', $portalUser?->email)->first() 
            ?? \App\Models\Client::first();
        $pendingRequestsCount = \App\Models\DocumentRequest::where('client_id', $portalClient?->id ?? 0)->whereIn('status', ['pending', 'rejected'])->count();
        $mattersCount = \App\Models\Matter::where('client_id', $portalClient?->id ?? 0)->count();
        
        $uName = $portalUser?->name ?? 'Client';
        $words = explode(' ', trim($uName));
        $initials = count($words) >= 2 ? strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1)) : strtoupper(substr($uName, 0, 2));
    @endphp

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
                <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ \App\Models\PlatformSetting::logoUrl() }}" alt="{{ \App\Models\PlatformSetting::platformName() }}" class="h-10 w-auto object-contain rounded"/>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[13.5px] font-semibold text-white tracking-wide block leading-tight">Client Portal</span>
                        <span class="text-[11px] text-[#8e8e8e] block mt-0.5 font-sans truncate max-w-[130px]">{{ $portalClient->name ?? 'Litigation Dossier' }}</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-[#8e8e8e] hover:text-white rounded cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="px-3 py-4 flex flex-col gap-1">
                <!-- Overview -->
                <a href="{{ route('portal.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.dashboard') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.dashboard') ? 'text-white' : 'text-[#9ca3af]' }}">bar_chart</span>
                    <span>Overview</span>
                </a>

                <!-- Matters & Dockets -->
                <a href="{{ route('portal.matters.index') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.matters.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.matters.*') ? 'text-white' : 'text-[#9ca3af]' }}">folder</span>
                        <span>Matters &amp; Dockets</span>
                    </div>
                    <span class="text-[11px] px-1.5 py-0.5 rounded-full {{ request()->routeIs('portal.matters.*') ? 'bg-white/20 text-white' : 'bg-white/[.08] text-[#9ca3af]' }}">
                        {{ $mattersCount }}
                    </span>
                </a>

                <!-- Document Requests -->
                <a href="{{ route('portal.requests.index') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.requests.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.requests.*') ? 'text-white' : 'text-[#9ca3af]' }}">drive_folder_upload</span>
                        <span>Document Requests</span>
                    </div>
                    @if($pendingRequestsCount > 0)
                    <span class="text-[10.5px] font-semibold px-1.5 py-0.2 rounded-full bg-[#ba1a1a] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                    @endif
                </a>

                <!-- Documents Vault -->
                <a href="{{ route('portal.documents.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.documents.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.documents.*') ? 'text-white' : 'text-[#9ca3af]' }}">description</span>
                    <span>Documents Vault</span>
                </a>

                <!-- Counsel Messages -->
                <a href="{{ route('portal.messages.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.messages.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.messages.*') ? 'text-white' : 'text-[#9ca3af]' }}">forum</span>
                    <span>Messages</span>
                </a>

                <!-- Litigation Calendar -->
                <a href="{{ route('portal.calendar.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.calendar.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.calendar.*') ? 'text-white' : 'text-[#9ca3af]' }}">calendar_month</span>
                    <span>Litigation Calendar</span>
                </a>

                <!-- Invoices & Billing -->
                <a href="{{ route('portal.invoices.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.invoices.*') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.invoices.*') ? 'text-white' : 'text-[#9ca3af]' }}">receipt_long</span>
                    <span>Invoices &amp; Billing</span>
                </a>

                <!-- Account Settings -->
                <a href="{{ route('portal.settings') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13.5px] transition-colors {{ request()->routeIs('portal.settings') ? 'bg-white/[.10] text-white font-medium shadow-xs' : 'text-[#9ca3af] hover:text-white hover:bg-white/[.05]' }}">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.settings') ? 'text-white' : 'text-[#9ca3af]' }}">settings</span>
                    <span>Account Settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom Client Profile & Sign Out -->
        <div class="p-4 border-t border-white/[.08] bg-[#161718] flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#23493a] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                    {{ $initials }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[13px] font-medium text-white truncate leading-tight">{{ $portalUser?->name ?? 'Retained Client' }}</span>
                    <span class="text-[11.5px] text-[#8e8e8e] truncate mt-0.5">{{ $portalClient?->name ?? $portalUser?->email }}</span>
                </div>
            </div>
            <div class="pt-1 flex items-center justify-between">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-[12px] text-[#8e8e8e] hover:text-white transition-colors cursor-pointer">
                        Sign out
                    </button>
                </form>
                <span class="text-[10.5px] font-mono text-[#8e8e8e]">Ref: {{ $portalClient?->formatted_id ?? 'CL-'.$portalClient?->id }}</span>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper with Left Margin on Desktop -->
    <div class="lg:pl-64 flex flex-col min-h-screen bg-[#fbf9f5]">
        
        <!-- Desktop & Mobile Top Header Bar with Notifications, Settings & Profile -->
        <header class="h-14 bg-white border-b border-[#e5e3dc] px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <!-- Left: Mobile toggle + Breadcrumb / Context -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-1.5 text-[#5e625e] hover:text-[#1b1c18] rounded-md transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2 text-xs text-[#5e625e]">
                    <span class="font-medium text-[#1b1c18] tracking-tight text-[13px]">Client Portal</span>
                    <span class="text-[#c1c8c3] hidden sm:inline">/</span>
                    <span class="hidden sm:inline text-[12px] font-sans">@yield('header_title', 'Litigation Dossier')</span>
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
                            title="Case Notifications">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        @if($pendingRequestsCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#23493a]"></span>
                        @endif
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
                            <span class="font-semibold text-[#1b1c18] text-[12.5px]">Litigation Alerts</span>
                            <span class="text-[10.5px] px-1.5 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e]">Live</span>
                        </div>
                        <div class="divide-y divide-[#f5f3ed] max-h-64 overflow-y-auto">
                            @if($pendingRequestsCount > 0)
                            <div class="px-4 py-2.5 hover:bg-[#faf8f5] transition-colors">
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-[#ba1a1a]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#ba1a1a]"></span> Document Action Required
                                </div>
                                <p class="text-[11.5px] text-[#414844] mt-0.5">{{ $pendingRequestsCount }} filing request(s) awaiting your upload.</p>
                                <a href="{{ route('portal.requests.index') }}" class="text-[10.5px] text-[#23493a] font-medium hover:underline mt-1 block">Review requests &rarr;</a>
                            </div>
                            @endif
                            <div class="px-4 py-2.5 hover:bg-[#faf8f5] transition-colors">
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-[#23493a]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></span> Statutory Evidence Privilege
                                </div>
                                <p class="text-[11.5px] text-[#414844] mt-0.5">Communications protected under Sec 126 &amp; 129 Evidence Act.</p>
                                <span class="text-[10px] text-[#8a8e89] mt-1 block">Encrypted repository</span>
                            </div>
                            <div class="px-4 py-2.5 hover:bg-[#faf8f5] transition-colors">
                                <div class="flex items-center gap-1.5 text-[11px] font-medium text-[#0369a1]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0284c7]"></span> Cause List Synchronized
                                </div>
                                <p class="text-[11.5px] text-[#414844] mt-0.5">High Court electronic cause lists up to date.</p>
                                <span class="text-[10px] text-[#8a8e89] mt-1 block">Daily court registry feed</span>
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-[#f0eee8] bg-[#faf8f5] text-center">
                            <a href="{{ route('portal.matters.index') }}" class="text-[11.5px] font-medium text-[#23493a] hover:underline">
                                View all active case dockets &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Settings Gear Icon -->
                <a href="{{ route('portal.settings') }}" 
                   class="p-2 text-[#5e625e] hover:text-[#1b1c18] hover:bg-[#f5f3ed] rounded-md transition-colors" 
                   title="Account &amp; KYC Settings">
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
                            {{ $portalUser?->name ?? 'Client' }}
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
                            <p class="font-medium text-[#1b1c18] text-[12.5px] truncate">{{ $portalUser?->name }}</p>
                            <p class="text-[11px] text-[#5e625e] font-mono truncate mt-0.5">{{ $portalUser?->email }}</p>
                        </div>
                        <a href="{{ route('portal.settings') }}" class="flex items-center gap-2 px-3.5 py-2 text-[#414844] hover:bg-[#faf8f5] hover:text-[#1b1c18]">
                            <span class="material-symbols-outlined text-base">badge</span>
                            <span>Legal Profile &amp; KYC</span>
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

        <!-- Top Statutory Privilege Environment Notice Banner -->
        <div class="w-full text-center py-2 text-[12px] text-[#8a8e89] font-sans border-b border-[#f0eee8]/60 bg-[#faf8f5]">
            Privileged Client Environment &middot; Protected under Section 126 &amp; 129 Indian Evidence Act
        </div>

        <!-- Dynamic Main Content -->
        <main class="flex-1 w-full max-w-[1240px] mx-auto px-6 sm:px-10 py-6 lg:py-8">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-6 p-3.5 rounded-md bg-[#23493a]/10 border border-[#23493a]/20 text-xs text-[#23493a] flex items-center gap-2.5 shadow-xs">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-3.5 rounded-md bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2.5 shadow-xs">
                    <span class="material-symbols-outlined text-base text-red-600">error</span>
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Portal Footer with Dynamic Legal Links -->
        <footer class="w-full max-w-[1240px] mx-auto px-6 sm:px-10 py-6 border-t border-[#f0eee8] text-[12px] text-[#6b7280] flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                {{ \App\Models\PlatformSetting::get('footer_copyright', '© ' . date('Y') . ' ' . \App\Models\PlatformSetting::platformName() . '. All rights reserved.') }}
            </div>
            <div class="flex items-center gap-4 text-[12px]">
                <a href="{{ route('public.about') }}" target="_blank" class="hover:text-[#093225] transition-colors">About Chambers</a>
                <span>·</span>
                <a href="{{ route('public.contact') }}" target="_blank" class="hover:text-[#093225] transition-colors">Registry Contact</a>
                <span>·</span>
                <a href="{{ route('public.privacy') }}" target="_blank" class="hover:text-[#093225] transition-colors">Advocate Privilege</a>
                <span>·</span>
                <a href="{{ route('public.terms') }}" target="_blank" class="hover:text-[#093225] transition-colors">Terms</a>
            </div>
        </footer>
    </div>

    <!-- Modals (Change Password & Avatar) -->
    <!-- Modal: Change Password -->
    <div x-show="changePasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="changePasswordModal = false"
             class="bg-white border border-[#e5e3dc] rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1b1c18]">Update Security Credentials</h3>
                <button @click="changePasswordModal = false" class="text-[#8e8e8e] hover:text-[#1b1c18] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('profile.change-password') }}" method="POST" class="mt-4 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1b1c18]">Current Password</label>
                    <input type="password" name="current_password" required class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1b1c18] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1b1c18]">New Password</label>
                    <input type="password" name="password" required class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1b1c18] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1b1c18]">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1b1c18] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="changePasswordModal = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">Save Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Change Avatar -->
    <div x-show="changeAvatarModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="changeAvatarModal = false"
             class="bg-white border border-[#e5e3dc] rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1b1c18]">Update Avatar Portrait</h3>
                <button @click="changeAvatarModal = false" class="text-[#8e8e8e] hover:text-[#1b1c18] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('profile.change-avatar') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-[#1b1c18]">Select Image File</label>
                    <input type="file" name="avatar" accept="image/*" required class="h-10 text-xs text-[#1b1c18] file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#faf8f5] file:text-[#23493a] hover:file:bg-[#f5f3ed]"/>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="changeAvatarModal = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">Upload Avatar</button>
                </div>
            </form>
        </div>
    </div>

    @livewireScripts
</body>
</html>
