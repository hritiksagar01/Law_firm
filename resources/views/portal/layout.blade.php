<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Client Portal') — {{ config('legal.app_name', 'Vennamraj Associates') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>

    <!-- Juris Prestige Typography: EB Garamond & Manrope -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#222222] font-sans antialiased min-h-screen" x-data="{ mobileMenuOpen: false }">
<style>[x-cloak] { display: none !important; }</style>

    @php
        $portalUser = auth()->user();
        $portalClient = \App\Models\Client::where('user_id', $portalUser->id)->first() 
            ?? \App\Models\Client::where('email', $portalUser->email)->first() 
            ?? \App\Models\Client::first();
        $pendingRequestsCount = \App\Models\DocumentRequest::where('client_id', $portalClient->id ?? 0)->where('status', 'pending')->count();
        $mattersCount = \App\Models\Matter::where('client_id', $portalClient->id ?? 0)->count();
    @endphp

    <!-- Left Sidebar Navigation Shell (Juris Prestige Crisp White & Brown Accent) -->
    <aside class="fixed left-0 top-0 h-screen w-[260px] bg-white text-[#222222] z-50 flex flex-col justify-between border-r border-[#EFECE6] shadow-[1px_0_12px_rgba(132,93,51,0.04)] hidden md:flex">
        <div class="flex flex-col">
            <!-- Brand & Client Portal Header -->
            <div class="p-4 border-b border-[#EFECE6]">
                <div class="flex items-center gap-2.5 mb-3">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2 min-w-0">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-9 w-auto object-contain rounded-md shadow-xs"/>
                        <div class="flex flex-col min-w-0">
                            <span class="font-serif text-sm text-[#222222] font-bold tracking-tight truncate leading-tight">{{ config('legal.app_name', 'Vennamraj Associates') }}</span>
                            <span class="font-mono text-[9px] uppercase tracking-widest text-[#845D33] font-semibold mt-0.5">Client Portal</span>
                        </div>
                    </a>
                </div>

                @if($portalClient)
                <div class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-[#222222] truncate font-semibold">{{ $portalClient->name }}</span>
                        <span class="font-mono text-[10px] text-[#766A5E] truncate">Retained Client</span>
                    </div>
                    <span class="material-symbols-outlined text-[#845D33] text-base">verified</span>
                </div>
                @endif
            </div>

            <!-- Client Portal Navigation Links (On The Left) -->
            <div class="px-2 py-3 flex flex-col gap-0.5">
                <span class="px-2 py-1 font-mono text-[10px] uppercase tracking-wider text-[#845D33] font-semibold">Client Services</span>
                
                <!-- Overview -->
                <a href="{{ route('portal.dashboard') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.dashboard') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.dashboard') ? 'text-white' : 'text-[#845D33]' }}">dashboard</span>
                        <span>Overview</span>
                    </div>
                </a>

                <!-- My Cases -->
                <a href="{{ route('portal.matters.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.matters.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.matters.*') ? 'text-white' : 'text-[#845D33]' }}">gavel</span>
                        <span>My Cases</span>
                    </div>
                    <span class="font-mono text-[11px] px-1.5 py-0.5 rounded-full {{ request()->routeIs('portal.matters.*') ? 'bg-white/20 text-white' : 'bg-[#F8F4EE] text-[#845D33]' }}">{{ $mattersCount }}</span>
                </a>

                <!-- Document Requests -->
                <a href="{{ route('portal.requests.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.requests.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.requests.*') ? 'text-white' : 'text-[#845D33]' }}">drive_folder_upload</span>
                        <span>Document Requests</span>
                    </div>
                    @if($pendingRequestsCount > 0)
                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-[#B88B56] text-white">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>

                <!-- Documents Vault -->
                <a href="{{ route('portal.documents.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.documents.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.documents.*') ? 'text-white' : 'text-[#845D33]' }}">description</span>
                        <span>Documents Vault</span>
                    </div>
                </a>

                <!-- Counsel Messages -->
                <a href="{{ route('portal.messages.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.messages.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.messages.*') ? 'text-white' : 'text-[#845D33]' }}">chat</span>
                        <span>Counsel Messages</span>
                    </div>
                </a>

                <!-- Invoices & Retainer -->
                <a href="{{ route('portal.invoices.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.invoices.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.invoices.*') ? 'text-white' : 'text-[#845D33]' }}">account_balance_wallet</span>
                        <span>Invoices &amp; Retainer</span>
                    </div>
                </a>

                <!-- Court Hearings -->
                <a href="{{ route('portal.calendar.index') }}" class="flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-all {{ request()->routeIs('portal.calendar.*') ? 'bg-[#845D33] text-white font-semibold shadow-sm' : 'text-[#554D45] hover:bg-[#F8F4EE] hover:text-[#845D33]' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('portal.calendar.*') ? 'text-white' : 'text-[#845D33]' }}">calendar_month</span>
                        <span>Court Hearings</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-[#EFECE6] bg-[#FAF8F5]">
            <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-[#EFECE6] shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <img alt="{{ auth()->user()->name ?? 'Client' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#845D33]/30" src="{{ auth()->user()->avatar_url ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80' }}"/>
                        <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-[#845D33] ring-2 ring-white"></span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-[#222222] font-semibold truncate">{{ auth()->user()->name ?? 'Client' }}</span>
                        <span class="text-[10px] text-[#766A5E] truncate">{{ $portalClient->name ?? 'Corporate Client' }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign Out of Portal" class="p-1.5 text-[#766A5E] hover:text-[#845D33] hover:bg-[#F8F4EE] transition-colors rounded">
                        <span class="material-symbols-outlined text-lg">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile Drawer for Left Navigation -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 md:hidden flex">
        <div @click="mobileMenuOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>
        <aside class="relative w-[270px] max-w-[80%] bg-white text-[#222222] h-full flex flex-col justify-between p-4 shadow-2xl z-10 border-r border-[#EFECE6]">
            <!-- Mobile Sidebar Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-[#845D33] flex items-center justify-center text-white">
                            <span class="material-symbols-outlined text-xl">balance</span>
                        </div>
                        <span class="font-serif text-base text-[#222222] font-bold">Client Portal</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-1">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.dashboard') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('portal.matters.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.matters.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                        <span>My Cases ({{ $mattersCount }})</span>
                    </a>
                    <a href="{{ route('portal.requests.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.requests.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">drive_folder_upload</span>
                        <span>Document Requests ({{ $pendingRequestsCount }})</span>
                    </a>
                    <a href="{{ route('portal.documents.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.documents.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">description</span>
                        <span>Documents Vault</span>
                    </a>
                    <a href="{{ route('portal.messages.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.messages.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">chat</span>
                        <span>Counsel Messages</span>
                    </a>
                    <a href="{{ route('portal.invoices.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.invoices.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        <span>Invoices &amp; Retainer</span>
                    </a>
                    <a href="{{ route('portal.calendar.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.calendar.*') ? 'bg-[#845D33] text-white font-bold' : 'text-[#554D45]' }}">
                        <span class="material-symbols-outlined text-lg">calendar_month</span>
                        <span>Court Hearings</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="pt-4 border-t border-[#EFECE6]">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-semibold">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    <span>Sign Out</span>
                </button>
            </form>
        </aside>
    </div>

    <!-- Main Content Area (Offset for Left Sidebar) -->
    <div class="md:pl-[260px] flex flex-col min-h-screen">
        
        <!-- Global Top Header Bar -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#EFECE6] h-16 px-4 sm:px-8 flex items-center justify-between shadow-[0_1px_4px_rgba(132,93,51,0.02)]">
            <!-- Left Header: Mobile Toggle & Chambers Context -->
            <div class="flex items-center gap-3">
                <button @click="mobileMenuOpen = true" class="md:hidden p-1.5 rounded-lg text-[#222222] hover:bg-[#FAF8F5]">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <span class="text-xs font-semibold text-[#222222]">{{ $portalClient->firm->name ?? 'Vennamraj Associates' }}</span>
                    <span class="hidden sm:inline text-xs text-[#8C7F72]">/</span>
                    <span class="text-[11px] font-mono text-[#766A5E]">Confidential Client Portal</span>
                </div>
            </div>

            <!-- Right Header: Trust Balance & Fast Logout -->
            <div class="flex items-center gap-3">
                @if($portalClient)
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-[#FAF8F5] border border-[#EAE4DC] rounded-lg shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#845D33]"></span>
                    <span class="text-xs text-[#766A5E] font-mono">Retainer Balance:</span>
                    <span class="text-xs font-serif font-bold text-[#845D33]">{{ config('legal.currency.symbol', '₹') }}{{ number_format($portalClient->trust_balance, 2) }}</span>
                </div>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 px-3 h-8.5 rounded-lg bg-white hover:bg-red-50 text-[#766A5E] hover:text-red-700 text-xs font-medium border border-[#EAE4DC] hover:border-red-200 transition-colors shadow-2xs" title="Sign Out">
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
            <div class="mb-6 p-4 rounded-xl bg-[#F8F4EE] border border-[#E8DAC8] text-[#6D4B27] text-xs flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-lg text-[#845D33]">check_circle</span>
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
        <footer class="bg-white border-t border-[#EFECE6] py-4 px-6 text-xs text-[#766A5E]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <span>&copy; {{ date('Y') }} {{ config('legal.app_name', 'Vennamraj Associates') }}. Client Portal.</span>
                <span class="text-[11px] font-mono text-[#845D33] font-semibold">Legal Communications Strictly Privileged</span>
            </div>
        </footer>

    </div>

</body>
</html>
