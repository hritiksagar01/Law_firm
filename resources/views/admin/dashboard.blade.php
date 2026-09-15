<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Platform Super Admin — Multi-Tenant Legal Cloud</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f6] font-sans text-[#1a1c1a] antialiased min-h-screen">
    
    <header class="h-16 bg-[#111714] text-white px-6 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-[#1a3c2a] text-[#fed977] flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
            </div>
            <div>
                <span class="font-serif text-lg font-bold text-white">LexisCore Multi-Tenant Cloud</span>
                <span class="text-[10px] font-mono text-[#82a78f] block -mt-1">Super Administrator Management Console</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#1a231e] p-1 rounded-lg border border-[#2b3830] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md bg-[#1a3c2a] text-[#fed977] font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md text-[#a3b8aa] hover:text-white hover:bg-[#222e27] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <a href="{{ route('dashboard') }}" class="text-xs text-[#c5ecd2] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Switch to Law Firm Workspace</span>
        </a>
    </header>

    <main class="max-w-6xl mx-auto p-6 md:p-8">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-serif font-bold text-[#1a1c1a]">Platform Tenant Telemetry</h1>
                <p class="text-sm text-[#727973] mt-1">Multi-firm resource isolation, database latency, and Cloudflare R2 / S3 storage consumption</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.settings.environment') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#c1c8c1] text-[#1a1c1a] text-xs font-medium hover:bg-[#faf9f6] shadow-sm">
                    <span class="material-symbols-outlined text-base text-amber-600">tune</span>
                    <span>Environment &amp; Cloud Settings</span>
                </a>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm">
                    <span class="material-symbols-outlined text-base">domain_add</span>
                    <span>Provision New Law Firm</span>
                </button>
            </div>
        </div>

        <!-- Infrastructure Health Telemetry -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-xl bg-white border border-[#e9e8e5] shadow-sm">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#727973]">Tenants Active</span>
                <span class="text-2xl font-serif font-bold text-[#1a1c1a] mt-1 block">{{ $firms->count() }} Firm(s)</span>
                <span class="text-xs text-emerald-700 mt-2 block font-mono">100% Provisioned</span>
            </div>

            <div class="p-5 rounded-xl bg-white border border-[#e9e8e5] shadow-sm">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#727973]">Dual Database Mode</span>
                <span class="text-xl font-mono font-bold text-[#1a3c2a] mt-1 block">Postgres / MySQL</span>
                <span class="text-xs text-[#727973] mt-2 block font-mono">Driver: SQLite (Local)</span>
            </div>

            <div class="p-5 rounded-xl bg-white border border-[#e9e8e5] shadow-sm">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#727973]">Encrypted Vault Storage</span>
                <span class="text-2xl font-mono font-bold text-[#1a1c1a] mt-1 block">35.2 <span class="text-xs font-normal">MB</span></span>
                <span class="text-xs text-emerald-700 mt-2 block font-mono">Cloudflare R2 (Free Tier)</span>
            </div>

            <div class="p-5 rounded-xl bg-white border border-[#e9e8e5] shadow-sm">
                <span class="font-mono text-[10px] uppercase tracking-wider text-[#727973]">Platform Health</span>
                <span class="text-2xl font-serif font-bold text-emerald-800 mt-1 block">Optimal</span>
                <span class="text-xs text-[#727973] mt-2 block font-mono">Latency: 2.4ms · Queues Active</span>
            </div>
        </div>

        <!-- Law Firm Tenant Directory -->
        <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                <h3 class="text-sm font-semibold text-[#1a1c1a]">Registered Law Firm Tenants</h3>
                <span class="font-mono text-xs text-[#727973]">{{ $firms->count() }} Tenant Accounts</span>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f4f3f1] border-b border-[#e9e8e5] text-[11px] font-mono text-[#727973] uppercase tracking-wider">
                        <th class="py-3 px-4">Law Firm Name</th>
                        <th class="py-3 px-4">Slug / Domain</th>
                        <th class="py-3 px-4 text-center">Attorneys &amp; Staff</th>
                        <th class="py-3 px-4 text-center">Active Matters</th>
                        <th class="py-3 px-4 text-center">Vault Files</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeeeb] text-sm">
                    @foreach($firms as $firm)
                    <tr class="hover:bg-[#faf9f6]">
                        <td class="py-3.5 px-4 font-semibold text-[#1a1c1a]">
                            {{ $firm->name }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-[#727973]">
                            {{ $firm->slug }}.lexiscore.app
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs">
                            {{ $firm->users_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs">
                            {{ $firm->matters_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-xs">
                            {{ $firm->documents_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('dashboard') }}" class="px-2.5 py-1 rounded bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616]">
                                Impersonate Chambers
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
