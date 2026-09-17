<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $firm->name }} — Firm Overview — Super Admin Console</title>
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

        <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#FAF8F5] hover:text-[#B88B56] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Firms</span>
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

        <!-- Firm Banner Header -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-center text-[#9F8349] shrink-0">
                    <span class="material-symbols-outlined text-3xl">domain</span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-serif font-bold text-[#222222]">{{ $firm->name }}</h1>
                        @if($firm->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Inactive
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-[#766A5E]">
                        <span class="font-mono">{{ $firm->slug }}.lexiscore.app</span>
                        @if($firm->city || $firm->state)
                        <span>•</span>
                        <span>{{ $firm->city }}{{ $firm->city && $firm->state ? ', ' : '' }}{{ $firm->state }}</span>
                        @endif
                        @if($firm->email)
                        <span>•</span>
                        <span>{{ $firm->email }}</span>
                        @endif
                        @if($firm->phone)
                        <span>•</span>
                        <span>{{ $firm->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-start md:self-center">
                <a href="{{ route('admin.firms.edit', $firm) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">edit</span>
                    <span>Edit Firm</span>
                </a>
                <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg {{ $firm->status === 'active' ? 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }} text-xs font-semibold transition-all">
                        <span class="material-symbols-outlined text-base">{{ $firm->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                        <span>{{ $firm->status === 'active' ? 'Deactivate' : 'Activate' }}</span>
                    </button>
                </form>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                    <span class="material-symbols-outlined text-base">login</span>
                    <span>Enter Workspace</span>
                </a>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Attorneys &amp; Staff</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $firm->users_count }}</span>
                <span class="text-xs text-[#766A5E] mt-2 block font-mono">Assigned Advocates</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Active Cases</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $firm->matters_count }}</span>
                <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">Litigation &amp; Advisory</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Clients Represented</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $firm->clients_count }}</span>
                <span class="text-xs text-[#766A5E] mt-2 block font-mono">Corporate &amp; Individual</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Vault Documents</span>
                <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $firm->documents_count }}</span>
                <span class="text-xs text-emerald-600 mt-2 block font-mono">Encrypted &amp; Stored</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Cols: Staff & Recent Matters -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Advocates & Staff Table -->
                <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#222222] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#9F8349] text-base">group</span>
                            <span>Advocates &amp; Personnel</span>
                        </h3>
                        <span class="font-mono text-xs text-[#766A5E]">{{ $firm->users_count }} Members</span>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">
                                <th class="py-2.5 px-4">Name</th>
                                <th class="py-2.5 px-4">Role</th>
                                <th class="py-2.5 px-4">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EFECE6] text-xs">
                            @forelse($users as $user)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="py-3 px-4 font-semibold text-[#222222]">
                                    {{ $user->name }}
                                    @if($user->title)
                                    <span class="block text-[10px] font-normal text-[#766A5E]">{{ $user->title }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono capitalize bg-[#FAF8F5] text-[#222222] border border-[#EAE4DC]">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#766A5E] font-mono">{{ $user->email }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-400">No personnel registered in this firm yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Recent Matters Table -->
                <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#222222] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#9F8349] text-base">gavel</span>
                            <span>Recent Matters / Cases</span>
                        </h3>
                        <span class="font-mono text-xs text-[#766A5E]">{{ $firm->matters_count }} Total Cases</span>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">
                                <th class="py-2.5 px-4">Case # / Title</th>
                                <th class="py-2.5 px-4">Client</th>
                                <th class="py-2.5 px-4">Stage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EFECE6] text-xs">
                            @forelse($matters as $matter)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[10px] text-[#9F8349] block">{{ $matter->case_number }}</span>
                                    <span class="font-semibold text-[#222222]">{{ $matter->title }}</span>
                                </td>
                                <td class="py-3 px-4 text-[#766A5E]">
                                    {{ $matter->client->name ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium capitalize bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ str_replace('_', ' ', $matter->stage) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-400">No active matters in this firm yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Right Col: Firm Details & Practice Areas -->
            <div class="space-y-6">
                
                <!-- Practice Disciplines -->
                <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Practice Disciplines</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @if(is_array($firm->practice_areas) && count($firm->practice_areas) > 0)
                            @foreach($firm->practice_areas as $area)
                            <span class="px-2.5 py-1 rounded-lg text-xs bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222]">
                                {{ $area }}
                            </span>
                            @endforeach
                        @else
                            <span class="text-xs text-gray-400">General Practice (All Areas)</span>
                        @endif
                    </div>
                </div>

                <!-- Organization Particulars -->
                <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Organization Particulars</h3>
                    <dl class="space-y-2.5 text-xs">
                        <div>
                            <dt class="text-[#766A5E]">Default Currency</dt>
                            <dd class="font-mono font-semibold text-[#222222]">{{ $firm->currency ?? 'INR' }}</dd>
                        </div>
                        @if($firm->address)
                        <div>
                            <dt class="text-[#766A5E]">Chambers Address</dt>
                            <dd class="text-[#222222] mt-0.5">{{ $firm->address }}</dd>
                        </div>
                        @endif
                        @if($firm->website)
                        <div>
                            <dt class="text-[#766A5E]">Website</dt>
                            <dd class="text-[#9F8349] font-mono mt-0.5 truncate">
                                <a href="{{ $firm->website }}" target="_blank" class="hover:underline">{{ $firm->website }}</a>
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-[#766A5E]">Provisioned On</dt>
                            <dd class="text-[#222222] font-mono mt-0.5">{{ $firm->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>

            </div>

        </div>

    </main>
</body>
</html>
