<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Super Admin Platform Console') — Multi-Tenant Legal Cloud</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <!-- Industry Standard Readable Typography: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen"
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
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity">
    </div>

    <!-- Left Sidebar Shell (Juris Prestige Standard, PDF-Aligned) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed left-0 top-0 h-screen w-64 bg-white text-[#222222] z-50 flex flex-col justify-between border-r border-[#EFECE6] shadow-[1px_0_12px_rgba(159,131,73,0.04)] transition-transform duration-300 ease-in-out">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Brand & Platform Header -->
            <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                <a href="{{ route('admin.firms.index') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 w-auto object-contain rounded-lg shadow-xs"/>
                    <div>
                        <span class="font-serif text-base font-bold text-[#222222] tracking-wide block leading-tight">Super Admin</span>
                        <span class="text-[10px] font-mono text-[#9F8349] font-medium block">Platform Console</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-[#766A5E] hover:text-[#222222] rounded-lg">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Navigation Sections -->
            <div class="px-3 py-4 flex flex-col gap-1">
                <span class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold">Governance</span>

                <!-- Firms Management -->
                <a href="{{ route('admin.firms.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.firms.*') ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <span class="material-symbols-outlined text-xl">corporate_fare</span>
                    <span>Law Firms</span>
                </a>

                <!-- Subscription Plans -->
                <a href="{{ route('admin.plans.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.plans.*') ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <span class="material-symbols-outlined text-xl">subscriptions</span>
                    <span>Subscription Plans</span>
                </a>

                <!-- Subscription Renewals & Governance -->
                <a href="{{ route('admin.subscriptions.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.subscriptions.*') ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <span class="material-symbols-outlined text-xl">autorenew</span>
                    <span>Renewals & Status</span>
                </a>

                <span class="px-2.5 pt-4 pb-1 font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold">System & Security</span>

                <!-- Mail Gateway -->
                <a href="{{ route('admin.settings.mail') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <span class="material-symbols-outlined text-xl">mail</span>
                    <span>Mail Gateway</span>
                </a>


                <!-- Super Admin Profile -->
                <a href="{{ route('admin.profile.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.profile.*') ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349]' }}">
                    <span class="material-symbols-outlined text-xl">manage_accounts</span>
                    <span>User Profile</span>
                </a>
            </div>
        </div>

        <!-- Sidebar Bottom Footer -->
        <div class="p-3 border-t border-[#EFECE6] bg-[#FAF8F5] flex flex-col gap-2">
            @if(Auth::check() && Auth::user()->firm_id)
            <a href="{{ route('dashboard') }}" 
               class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-[#766A5E] hover:text-[#9F8349] hover:bg-white border border-transparent hover:border-[#EAE4DC] transition-all">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Switch to Firm Workspace</span>
            </a>
            @endif

            <div class="flex items-center justify-between px-2 pt-1 text-[11px] text-[#A09587] font-mono">
                <span>Superadmin Mode</span>
                <span class="inline-flex items-center gap-1 text-emerald-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live</span>
                </span>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper with Left Margin on Desktop -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        
        <!-- Top Executive Bar (Sticky) -->
        <header class="h-16 bg-white border-b border-[#EFECE6] px-4 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" 
                        class="lg:hidden p-2 rounded-lg text-[#554D45] hover:bg-[#F8F4EE] transition-all">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="font-serif text-lg font-bold text-[#222222]">@yield('header_title', 'Multi-Tenant Platform Console')</span>
                    <span class="hidden sm:inline-block text-xs text-[#9F8349] font-mono bg-[#F8F4EE] px-2 py-0.5 rounded-md border border-[#EFECE6]">
                        Super Admin
                    </span>
                </div>
            </div>

            <!-- Top Right Profile Dropdown & Controls (PDF Page 5 Specification) -->
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
                            class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-[#F8F4EE] transition-all focus:outline-none focus:ring-2 focus:ring-[#9F8349]/20">
                        @if($avatar)
                            <img src="{{ $avatar }}" 
                                 alt="{{ $displayName }}" 
                                 class="w-9 h-9 rounded-full object-cover border border-[#EAE4DC] shadow-xs"/>
                        @else
                            <div class="w-9 h-9 rounded-full bg-[#9F8349] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ $initials }}
                            </div>
                        @endif
                        <div class="hidden md:flex flex-col text-left pr-1">
                            <span class="text-xs font-semibold text-[#222222] leading-tight">
                                {{ $displayName }}
                            </span>
                            <span class="text-[10px] font-mono text-[#9F8349] leading-tight">
                                Super Administrator
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-sm text-[#766A5E]">expand_more</span>
                    </button>

                    <!-- Account Management Dropdown (Matching PDF Page 5 Screenshot) -->
                    <div x-show="userMenuOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-[#EFECE6] py-1.5 z-50 divide-y divide-[#EFECE6]">
                        
                        <div class="px-4 py-2.5">
                            <p class="text-xs font-semibold text-[#222222] truncate">{{ $displayName }}</p>
                            <p class="text-[11px] font-mono text-[#766A5E] truncate">{{ $userEmail }}</p>
                        </div>

                        <div class="py-1">
                            <!-- Change Password Action -->
                            <button type="button" 
                                    @click="userMenuOpen = false; changePasswordModal = true" 
                                    class="w-full text-left px-4 py-2 text-xs text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349] flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base">lock_reset</span>
                                <span>Change Password</span>
                            </button>

                            <!-- Change Profile Picture Action -->
                            <button type="button" 
                                    @click="userMenuOpen = false; changeAvatarModal = true" 
                                    class="w-full text-left px-4 py-2 text-xs text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349] flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base">account_circle</span>
                                <span>Change Profile Picture</span>
                            </button>

                            <!-- My Setting (Profile Management) -->
                            <a href="{{ route('admin.profile.index') }}" 
                               class="block px-4 py-2 text-xs text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#9F8349] flex items-center gap-2.5 transition-all">
                                <span class="material-symbols-outlined text-base">settings</span>
                                <span>My Setting</span>
                            </a>
                        </div>

                        <div class="py-1">
                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2.5 transition-all">
                                    <span class="material-symbols-outlined text-base">logout</span>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace Area -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <span class="material-symbols-outlined text-rose-600 text-lg">warning</span>
                    <span>Please review the form errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-6 space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="py-4 px-8 border-t border-[#EFECE6] text-center text-xs text-[#A09587] font-mono">
            Platform Super Administrator Console &copy; {{ date('Y') }} Multi-Tenant Legal Cloud. All rights reserved.
        </footer>
    </div>

    <!-- Modal: Change Password (Inline from Header, PDF Page 5) -->
    <div x-show="changePasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="changePasswordModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EFECE6] animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#9F8349]">lock_reset</span>
                    <h3 class="font-serif text-lg font-bold text-[#222222]">Change Your Password</h3>
                </div>
                <button @click="changePasswordModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.profile.change-password') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Current Password *</label>
                    <input type="password" name="current_password" required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                           placeholder="Enter current password"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">New Password (Min 8 chars) *</label>
                    <input type="password" name="new_password" required minlength="8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                           placeholder="Enter new password"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required minlength="8"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                           placeholder="Confirm new password"/>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-[#EFECE6]">
                    <button type="button" @click="changePasswordModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#766A5E] hover:bg-[#FAF8F5] border border-[#EAE4DC]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-sm">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Change Profile Picture (Inline from Header, PDF Page 5) -->
    <div x-show="changeAvatarModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div @click.outside="changeAvatarModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EFECE6] animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#9F8349]">account_circle</span>
                    <h3 class="font-serif text-lg font-bold text-[#222222]">Update Profile Picture</h3>
                </div>
                <button @click="changeAvatarModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.profile.change-avatar') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#554D45] mb-2">Select Image File (JPG, PNG, WEBP — Max 2MB)</label>
                    <input type="file" name="avatar" accept="image/*" required 
                           class="w-full text-xs text-[#554D45] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#F8F4EE] file:text-[#9F8349] hover:file:bg-[#9F8349] hover:file:text-white transition-all"/>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-[#EFECE6]">
                    <button type="button" @click="changeAvatarModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#766A5E] hover:bg-[#FAF8F5] border border-[#EAE4DC]">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-sm">
                        Upload Avatar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @livewireScripts
</body>
</html>
