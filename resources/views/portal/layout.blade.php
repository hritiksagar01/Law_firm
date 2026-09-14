<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Client Portal') — {{ config('legal.app_name', 'Law Firm Management') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f6] text-[#1a1c1a] font-sans antialiased min-h-screen" x-data="{ mobileMenuOpen: false }">
<style>[x-cloak] { display: none !important; }</style>

    @php
        $portalUser = auth()->user();
        $portalClient = \App\Models\Client::where('user_id', $portalUser->id)->first() 
            ?? \App\Models\Client::where('email', $portalUser->email)->first() 
            ?? \App\Models\Client::first();
        $pendingRequestsCount = \App\Models\DocumentRequest::where('client_id', $portalClient->id ?? 0)->where('status', 'pending')->count();
        $mattersCount = \App\Models\Matter::where('client_id', $portalClient->id ?? 0)->count();
    @endphp

    <!-- Left Sidebar Navigation Shell (Evergreen Depth #022616 / #111714) -->
    <aside class="fixed left-0 top-0 h-screen w-[260px] bg-[#022616] text-[#dee4df] z-50 flex flex-col justify-between border-r border-[#1a3c2a] shadow-[0_1px_8px_rgba(0,0,0,0.06)] hidden md:flex">
        <div class="flex flex-col">
            <!-- Brand & Client Portal Header -->
            <div class="p-4 border-b border-[#1a3c2a]">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded bg-[#1a3c2a] border border-[#82a78f]/30 flex items-center justify-center text-[#fed977]">
                        <span class="material-symbols-outlined text-xl">balance</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-serif text-lg text-white font-bold tracking-tight truncate leading-none">{{ config('legal.app_name', 'Law Firm Management') }}</span>
                        <span class="font-mono text-[9px] uppercase tracking-widest text-[#82a78f] font-semibold mt-1">Client Portal</span>
                    </div>
                </div>

                @if($portalClient)
                <div class="w-full flex items-center justify-between px-2.5 py-1.5 rounded bg-[#1c221f] border border-[#313733]">
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-white truncate font-medium">{{ $portalClient->name }}</span>
                        <span class="font-mono text-[10px] text-[#82a78f] truncate">Retained Client</span>
                    </div>
                    <span class="material-symbols-outlined text-[#82a78f] text-base">verified</span>
                </div>
                @endif
            </div>

            <!-- Client Portal Navigation Links (On The Left) -->
            <div class="px-2 py-3 flex flex-col gap-0.5">
                <span class="px-2 py-1 font-mono text-[10px] uppercase tracking-wider text-[#82a78f]">Client Services</span>
                
                <!-- Overview -->
                <a href="{{ route('portal.dashboard') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.dashboard') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.dashboard') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">dashboard</span>
                        <span>Overview</span>
                    </div>
                </a>

                <!-- My Cases -->
                <a href="{{ route('portal.matters.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.matters.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.matters.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">gavel</span>
                        <span>My Cases</span>
                    </div>
                    <span class="font-mono text-[11px] px-1.5 py-0.5 rounded-full bg-[#1a3c2a] text-[#c5ecd2]">{{ $mattersCount }}</span>
                </a>

                <!-- Document Requests -->
                <a href="{{ route('portal.requests.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.requests.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.requests.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">drive_folder_upload</span>
                        <span>Document Requests</span>
                    </div>
                    @if($pendingRequestsCount > 0)
                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-[#fed977] text-[#785d00]">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>

                <!-- Documents Vault -->
                <a href="{{ route('portal.documents.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.documents.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.documents.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">description</span>
                        <span>Documents Vault</span>
                    </div>
                </a>

                <!-- Counsel Messages -->
                <a href="{{ route('portal.messages.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.messages.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.messages.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">chat</span>
                        <span>Counsel Messages</span>
                    </div>
                </a>

                <!-- Invoices & Retainer -->
                <a href="{{ route('portal.invoices.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.invoices.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.invoices.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">account_balance_wallet</span>
                        <span>Invoices &amp; Retainer</span>
                    </div>
                </a>

                <!-- Court Hearings -->
                <a href="{{ route('portal.calendar.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded text-sm transition-colors {{ request()->routeIs('portal.calendar.*') ? 'bg-[#1a3c2a] text-white font-semibold shadow-sm' : 'text-[#dee4df] hover:bg-[#1a3c2a]/60 hover:text-white' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.calendar.*') ? 'text-[#fed977]' : 'text-[#82a78f]' }}">calendar_month</span>
                        <span>Court Hearings</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-[#1a3c2a] bg-[#011a0f]">
            <div class="flex items-center justify-between p-2 rounded bg-[#1a3c2a]/40">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <img alt="{{ auth()->user()->name ?? 'Client' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#82a78f]/40" src="{{ auth()->user()->avatar_url ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80' }}"/>
                        <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-[#10b981] ring-2 ring-[#022616]"></span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-white font-medium truncate">{{ auth()->user()->name ?? 'Client' }}</span>
                        <span class="text-[10px] text-[#82a78f] truncate">{{ $portalClient->name ?? 'Corporate Client' }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign Out of Portal" class="p-1.5 text-[#82a78f] hover:text-white hover:bg-[#1a3c2a] transition-colors rounded">
                        <span class="material-symbols-outlined text-lg">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile Drawer for Left Navigation -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 md:hidden flex">
        <div @click="mobileMenuOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-xs"></div>
        <aside class="relative w-[270px] max-w-[80%] bg-[#022616] text-[#dee4df] h-full flex flex-col justify-between p-4 shadow-2xl z-10">
            <!-- Mobile Sidebar Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#1a3c2a]">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded bg-[#1a3c2a] flex items-center justify-center text-[#fed977]">
                            <span class="material-symbols-outlined text-xl">balance</span>
                        </div>
                        <span class="font-serif text-base text-white font-bold">Client Portal</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="text-white/70 hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-1">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.dashboard') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('portal.matters.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.matters.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                        <span>My Cases ({{ $mattersCount }})</span>
                    </a>
                    <a href="{{ route('portal.requests.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.requests.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">drive_folder_upload</span>
                        <span>Document Requests ({{ $pendingRequestsCount }})</span>
                    </a>
                    <a href="{{ route('portal.documents.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.documents.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">description</span>
                        <span>Documents Vault</span>
                    </a>
                    <a href="{{ route('portal.messages.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.messages.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">chat</span>
                        <span>Counsel Messages</span>
                    </a>
                    <a href="{{ route('portal.invoices.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.invoices.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        <span>Invoices &amp; Retainer</span>
                    </a>
                    <a href="{{ route('portal.calendar.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded text-sm {{ request()->routeIs('portal.calendar.*') ? 'bg-[#1a3c2a] text-white font-bold' : 'text-[#dee4df]' }}">
                        <span class="material-symbols-outlined text-lg">calendar_month</span>
                        <span>Court Hearings</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="pt-4 border-t border-[#1a3c2a]">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 bg-red-900/30 text-red-200 border border-red-800/40 rounded-lg text-xs">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    <span>Sign Out</span>
                </button>
            </form>
        </aside>
    </div>

    <!-- Main Content Area (Offset for Left Sidebar) -->
    <div class="md:pl-[260px] flex flex-col min-h-screen">
        
        <!-- Global Top Header Bar -->
        <header class="sticky top-0 z-40 bg-[#faf9f6]/95 backdrop-blur-md border-b border-[#e9e8e5] h-16 px-4 sm:px-8 flex items-center justify-between shadow-[0_1px_4px_rgba(0,0,0,0.02)]">
            <!-- Left Header: Mobile Toggle & Chambers Context -->
            <div class="flex items-center gap-3">
                <button @click="mobileMenuOpen = true" class="md:hidden p-1.5 rounded-lg text-[#1a1c1a] hover:bg-[#f4f3f1]">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <span class="text-xs font-semibold text-[#1a1c1a]">{{ $portalClient->firm->name ?? 'Sharma & Associates, Advocates' }}</span>
                    <span class="hidden sm:inline text-xs text-[#727973]">/</span>
                    <span class="text-[11px] font-mono text-[#727973]">Confidential Client Portal</span>
                </div>
            </div>

            <!-- Right Header: Trust Balance & Fast Logout -->
            <div class="flex items-center gap-3">
                @if($portalClient)
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-[#e9e8e5] rounded-lg shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-[#727973] font-mono">Retainer Balance:</span>
                    <span class="text-xs font-serif font-bold text-[#1a3c2a]">{{ config('legal.currency.symbol', '₹') }}{{ number_format($portalClient->trust_balance, 2) }}</span>
                </div>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 px-3 h-8.5 rounded-lg bg-white hover:bg-red-50 text-[#727973] hover:text-red-700 text-xs font-medium border border-[#e9e8e5] hover:border-red-200 transition-colors shadow-2xs" title="Sign Out">
                        <span class="material-symbols-outlined text-base">logout</span>
                        <span class="hidden sm:inline">Sign Out</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Page View Content -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Alerts -->
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-lg text-emerald-700">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 text-xs flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-lg text-red-700">error</span>
                <span class="font-medium">{{ $errors->first() }}</span>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-[#e9e8e5] py-4 px-6 text-xs text-[#727973]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <span>&copy; {{ date('Y') }} {{ config('legal.app_name', 'Law Firm Management') }}. Client Portal.</span>
                <span class="text-[11px] font-mono text-[#1a3c2a]">Legal Communications Strictly Privileged</span>
            </div>
        </footer>

    </div>

</body>
</html>
