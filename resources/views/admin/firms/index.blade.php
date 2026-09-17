<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Law Firm Directory — Super Admin Platform Console</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen">
    
    <!-- Executive Top Header -->
    <header class="h-16 bg-[#222222] text-white px-6 flex items-center justify-between shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Platform Logo" class="h-9 w-auto object-contain rounded-md bg-white p-0.5"/>
            </a>
            <div>
                <span class="font-serif text-base font-bold text-white tracking-wide">Multi-Tenant Platform Console</span>
                <span class="text-[10px] font-mono text-[#8C7F72] block -mt-1">Super Administrator Access</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#2E2823] p-1 rounded-lg border border-[#3E352E] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" class="px-3 py-1.5 rounded-md bg-[#9F8349] text-white font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Law Firms</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <a href="{{ route('dashboard') }}" class="text-xs text-[#FAF8F5] hover:text-[#B88B56] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Firm Workspace</span>
        </a>
    </header>

    <main class="max-w-6xl mx-auto p-6 md:p-8">
        
        <!-- Alerts -->
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-rose-600">error</span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-serif font-bold text-[#222222]">Law Firm Tenants</h1>
                <p class="text-sm text-[#766A5E] mt-1">Manage subscribed practice firms, chambers, and organization accounts</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.firms.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                    <span class="material-symbols-outlined text-base">domain_add</span>
                    <span>Provision New Law Firm</span>
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Firms</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $stats['total'] }}</span>
                <span class="text-xs text-[#766A5E] mt-2 block font-mono">Registered Chambers</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Active Practices</span>
                <span class="text-2xl font-serif font-bold text-emerald-700 mt-1 block">{{ $stats['active'] }}</span>
                <span class="text-xs text-emerald-600 mt-2 block font-mono">Operational &amp; Live</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Inactive / Suspended</span>
                <span class="text-2xl font-serif font-bold text-amber-700 mt-1 block">{{ $stats['inactive'] }}</span>
                <span class="text-xs text-amber-600 mt-2 block font-mono">Access Restricted</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Staff &amp; Advocates</span>
                <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $stats['total_users'] }}</span>
                <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">Across All Firms</span>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white p-4 rounded-2xl border border-[#EFECE6] shadow-xs mb-6">
            <form method="GET" action="{{ route('admin.firms.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by firm name, slug, email, or city..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-[#EAE4DC] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] bg-[#FAF8F5]" />
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="status" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                    @if(request('q') || request('status'))
                    <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#766A5E] hover:text-[#222222] px-2">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Law Firm Tenant Table -->
        <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                        <th class="py-3.5 px-4">Law Firm</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4 text-center">Attorneys &amp; Staff</th>
                        <th class="py-3.5 px-4 text-center">Cases</th>
                        <th class="py-3.5 px-4 text-center">Clients</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6] text-sm">
                    @forelse($firms as $firm)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-[#222222]">{{ $firm->name }}</div>
                            <div class="text-[11px] font-mono text-[#766A5E]">{{ $firm->slug }}.lexiscore.app</div>
                            @if($firm->email)
                            <div class="text-[11px] text-[#8C7F72] mt-0.5">{{ $firm->email }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-xs text-[#766A5E]">
                            @if($firm->city || $firm->state)
                                {{ $firm->city }}{{ $firm->city && $firm->state ? ', ' : '' }}{{ $firm->state }}
                            @else
                                <span class="text-gray-400">National</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold text-[#222222]">
                            {{ $firm->users_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                            {{ $firm->matters_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                            {{ $firm->clients_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($firm->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.firms.show', $firm) }}" class="p-1 rounded-md text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="View Firm Overview">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </a>
                                <a href="{{ route('admin.firms.edit', $firm) }}" class="p-1 rounded-md text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="Edit Firm">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 rounded-md text-[#766A5E] hover:text-amber-600 hover:bg-amber-50" title="{{ $firm->status === 'active' ? 'Deactivate Firm' : 'Activate Firm' }}">
                                        <span class="material-symbols-outlined text-lg">{{ $firm->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#766A5E]">
                            <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">corporate_fare</span>
                            <p class="font-medium">No law firms found matching your criteria.</p>
                            <a href="{{ route('admin.firms.create') }}" class="inline-block mt-3 text-xs text-[#9F8349] font-semibold hover:underline">
                                Provision the first law firm &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($firms->hasPages())
            <div class="p-4 border-t border-[#EFECE6]">
                {{ $firms->links() }}
            </div>
            @endif
        </div>

    </main>
</body>
</html>
