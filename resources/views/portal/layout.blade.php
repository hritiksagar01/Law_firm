<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Client Portal') — {{ config('legal.app_name', 'Sharma Legal Chambers') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>

    <!-- Google Fonts: Newsreader & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas-ivory font-body-md text-body-md text-on-surface flex min-h-screen antialiased">
<style>[x-cloak] { display: none !important; }</style>

    @php
        $portalUser = auth()->user();
        $portalClient = \App\Models\Client::where('user_id', $portalUser->id)->first() 
            ?? \App\Models\Client::where('email', $portalUser->email)->first() 
            ?? \App\Models\Client::first();
        $pendingRequestsCount = \App\Models\DocumentRequest::where('client_id', $portalClient->id ?? 0)->where('status', 'pending')->count();
        $mattersCount = \App\Models\Matter::where('client_id', $portalClient->id ?? 0)->count();
        
        $uName = $portalUser->name ?? 'Client';
        $words = explode(' ', trim($uName));
        $initials = count($words) >= 2 ? strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1)) : strtoupper(substr($uName, 0, 2));
    @endphp

    <!-- Persistent Dark Left Sidebar (#121513) -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-sidebar-bg text-[#cfd3ce] flex flex-col z-50 border-r border-[#202521] select-none">
        <!-- Sidebar Brand Header -->
        <div class="h-20 px-6 flex flex-col justify-center border-b border-[#202521]">
            <div class="flex items-center gap-2">
                <span class="font-headline-md text-[18px] font-serif tracking-tight text-white truncate">
                    {{ $portalClient->firm->name ?? config('legal.app_name', 'Sharma Legal Chambers') }}
                </span>
            </div>
            <span class="font-caption text-[11px] text-[#7d857f] tracking-wide mt-0.5">
                Client Portal · Ref #{{ $portalClient->id ?? 'CL-01' }}
            </span>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 py-4 px-3 flex flex-col gap-1 overflow-y-auto">
            <!-- Overview -->
            <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.dashboard') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.dashboard') ? 'text-[#8fb7a4]' : '' }}">dashboard</span>
                <span>Overview</span>
            </a>

            <!-- Matters & Dockets -->
            <a href="{{ route('portal.matters.index') }}" class="flex items-center justify-between px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.matters.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.matters.*') ? 'text-[#8fb7a4]' : '' }}">folder_open</span>
                    <span>Matters &amp; Dockets</span>
                </div>
                <span class="font-caption text-[11px] px-1.5 py-0.2 rounded-full {{ request()->routeIs('portal.matters.*') ? 'bg-white/20 text-white' : 'bg-[#1d221e] text-[#9da39e]' }}">{{ $mattersCount }}</span>
            </a>

            <!-- Document Requests -->
            <a href="{{ route('portal.requests.index') }}" class="flex items-center justify-between px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.requests.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.requests.*') ? 'text-[#8fb7a4]' : '' }}">drive_folder_upload</span>
                    <span>Document Requests</span>
                </div>
                @if($pendingRequestsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full bg-[#ba1a1a] text-[10px] text-white font-semibold">{{ $pendingRequestsCount }}</span>
                @endif
            </a>

            <!-- Documents Vault -->
            <a href="{{ route('portal.documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.documents.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.documents.*') ? 'text-[#8fb7a4]' : '' }}">description</span>
                <span>Documents</span>
            </a>

            <!-- Messages -->
            <a href="{{ route('portal.messages.index') }}" class="flex items-center justify-between px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.messages.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.messages.*') ? 'text-[#8fb7a4]' : '' }}">forum</span>
                    <span>Messages</span>
                </div>
                <span class="px-1.5 py-0.2 rounded-full bg-pine-primary text-[10px] text-[#c2ecd7] font-semibold">3</span>
            </a>

            <!-- Litigation Calendar -->
            <a href="{{ route('portal.calendar.index') }}" class="flex items-center gap-3 px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.calendar.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.calendar.*') ? 'text-[#8fb7a4]' : '' }}">calendar_month</span>
                <span>Litigation Calendar</span>
            </a>

            <!-- Billing & Invoices -->
            <a href="{{ route('portal.invoices.index') }}" class="flex items-center gap-3 px-3 py-2 rounded font-title-sm text-[13px] transition-colors {{ request()->routeIs('portal.invoices.*') ? 'bg-pine-primary text-white shadow-sm font-medium' : 'text-[#9da39e] hover:text-white hover:bg-[#1d221e]' }}">
                <span class="material-symbols-outlined text-[19px] {{ request()->routeIs('portal.invoices.*') ? 'text-[#8fb7a4]' : '' }}">receipt_long</span>
                <span>Invoices &amp; Billing</span>
            </a>
        </nav>

        <!-- Sidebar Footer: Client Profile -->
        <div class="p-4 border-t border-[#202521] bg-[#101210] flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-pine-primary text-[#c2ecd7] font-title-sm text-[12px] flex items-center justify-center shrink-0 font-semibold">
                    {{ $initials }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="font-title-sm text-[13px] text-white truncate">{{ $portalUser->name }}</span>
                    <span class="font-caption text-[11px] text-[#7d857f] truncate">{{ $portalClient->name ?? 'Retained Client' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button class="text-[#7d857f] hover:text-white transition-colors ml-2 cursor-pointer" title="Sign out" type="submit">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Wrapper with Left Sidebar Offset -->
    <div class="pl-64 flex-1 flex flex-col min-h-screen">
        <!-- Top Bar -->
        <header class="h-16 bg-surface border-b border-border-hairline sticky top-0 z-40 px-6 lg:px-8 flex items-center justify-between gap-4">
            <!-- Clean Search Bar -->
            <div class="flex items-center gap-2 bg-surface-card border border-border-hairline rounded px-3 py-1.5 w-full max-w-md text-text-muted focus-within:border-pine-primary focus-within:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-[18px] text-text-muted">search</span>
                <input class="bg-transparent border-0 p-0 text-[13px] text-text-primary placeholder:text-text-muted focus:ring-0 w-full outline-none" placeholder="Search matters, documents, invoices..." type="text"/>
                <kbd class="px-1.5 py-0.5 rounded bg-surface-subtle border border-border-hairline font-caption text-[11px] text-text-secondary">⌘K</kbd>
            </div>

            <!-- Right Header Utilities -->
            <div class="flex items-center gap-4 shrink-0">
                <!-- Subtle demo environment pill -->
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-subtle border border-border-hairline text-text-secondary font-caption text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-pine-primary animate-pulse"></span>
                    <span>Secure Chambers Environment</span>
                </div>

                <!-- Notification Bell with Counter -->
                <button class="relative w-8 h-8 flex items-center justify-center rounded border border-border-hairline bg-surface-card text-on-surface-variant hover:text-on-surface hover:bg-surface-subtle transition-colors" title="Chambers Notifications" type="button">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-error text-white font-caption text-[9px] font-bold rounded-full flex items-center justify-center">2</span>
                </button>

                <!-- Client Dossier Context Pill -->
                <div class="hidden md:flex items-center gap-2 pl-3 border-l border-border-hairline text-left">
                    <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Client Context:</span>
                    <span class="font-label-sm text-label-sm text-text-primary font-medium truncate max-w-[140px]">{{ $portalClient->name ?? 'Retained Client' }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Canvas -->
        <main class="flex-1 w-full bg-canvas-ivory">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-8 py-6">
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

                @yield('content')
            </div>
        </main>

        <!-- Sticky Compact Footer -->
        <footer class="w-full bg-surface-card border-t border-border-hairline py-3 px-6 lg:px-8 mt-auto">
            <div class="max-w-[1400px] mx-auto flex flex-col md:flex-row items-center justify-between gap-2 font-caption text-caption text-text-secondary">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-[16px] text-pine-primary">shield_locked</span>
                    <span>Archival grade encryption</span>
                    <span class="inline-block w-1 h-1 rounded-full bg-outline-variant"></span>
                    <span>Two-step sign-in active</span>
                    <span class="inline-block w-1 h-1 rounded-full bg-outline-variant"></span>
                    <span>Audit trail enabled</span>
                </div>
                <div class="flex items-center gap-space-lg text-text-muted">
                    <span class="font-mono text-[11px]">DOCKET-SYNC v4.12.0</span>
                    <span class="text-[11px]">Chambers Seal #SL-8821</span>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
