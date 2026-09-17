<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Platform Super Admin — Multi-Tenant Legal Cloud</title>
    
    <!-- Juris Prestige Typography: EB Garamond & Manrope -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen">
    
    <!-- Executive Top Header (Neutral Dark #222222 with Warm Cognac #9F8349 Accents) -->
    <header class="h-16 bg-[#222222] text-white px-6 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#9F8349] text-white flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
            </div>
            <div>
                <span class="font-serif text-lg font-bold text-white tracking-wide">LexisCore Multi-Tenant Cloud</span>
                <span class="text-[10px] font-mono text-[#D4C4B5] block -mt-1">Super Administrator Management Console</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#2E2823] p-1 rounded-lg border border-[#3E352E] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md bg-[#9F8349] text-white font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" class="px-3 py-1.5 rounded-md text-[#D4C4B5] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Law Firms</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md text-[#D4C4B5] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <a href="{{ route('dashboard') }}" class="text-xs text-[#FAF8F5] hover:text-[#B88B56] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Switch to Law Firm Workspace</span>
        </a>
    </header>

    <main class="max-w-6xl mx-auto p-6 md:p-8">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-serif font-bold text-[#222222]">Platform Tenant Telemetry</h1>
                <p class="text-sm text-[#766A5E] mt-1">Multi-firm resource isolation, database latency, and AWS S3 / Cloudflare R2 storage consumption</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.settings.environment') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">tune</span>
                    <span>Environment &amp; Cloud Settings</span>
                </a>
                <a href="{{ route('admin.firms.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                    <span class="material-symbols-outlined text-base">domain_add</span>
                    <span>Provision New Law Firm</span>
                </a>
            </div>
        </div>

        <!-- Infrastructure Health Telemetry -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Tenants Active</span>
                <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $firms->count() }} Firm(s)</span>
                <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">100% Provisioned</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Dual Database Mode</span>
                <span class="text-xl font-mono font-bold text-[#9F8349] mt-1 block">Postgres / MySQL</span>
                <span class="text-xs text-[#766A5E] mt-2 block font-mono">Driver: SQLite (Local)</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Encrypted Vault Storage</span>
                <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">35.2 <span class="text-xs font-normal">MB</span></span>
                <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">AWS S3 / Cloudflare R2</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Platform Health</span>
                <span class="text-2xl font-serif font-bold text-[#9F8349] mt-1 block">Optimal</span>
                <span class="text-xs text-[#766A5E] mt-2 block font-mono">Latency: 2.4ms · Queues Active</span>
            </div>
        </div>

        <!-- Law Firm Tenant Directory -->
        <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-[#222222]">Registered Law Firm Tenants</h3>
                    <span class="font-mono text-xs text-[#766A5E]">{{ $firms->count() }} Tenant Accounts</span>
                </div>
                <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#9F8349] font-semibold hover:underline flex items-center gap-1">
                    <span>Manage All Law Firms</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                        <th class="py-3 px-4">Law Firm Name</th>
                        <th class="py-3 px-4">Slug / Domain</th>
                        <th class="py-3 px-4 text-center">Attorneys &amp; Staff</th>
                        <th class="py-3 px-4 text-center">Active Matters</th>
                        <th class="py-3 px-4 text-center">Vault Files</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6] text-sm">
                    @foreach($firms as $firm)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">
                            <a href="{{ route('admin.firms.show', $firm) }}" class="hover:text-[#9F8349] hover:underline">
                                {{ $firm->name }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-[#766A5E]">
                            {{ $firm->slug }}.lexiscore.app
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                            {{ $firm->users_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                            {{ $firm->matters_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                            {{ $firm->documents_count }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.firms.show', $firm) }}" class="px-2.5 py-1 rounded-lg border border-[#EAE4DC] text-xs font-semibold text-[#222222] hover:bg-white shadow-2xs">
                                    Overview
                                </a>
                                <a href="{{ route('dashboard') }}" class="px-2.5 py-1 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-2xs">
                                    Workspace
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
