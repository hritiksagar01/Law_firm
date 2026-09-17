@extends('layouts.admin')

@section('title', 'Platform Tenant Overview')
@section('header_title', 'Platform Tenant Overview')

@section('content')
<div class="space-y-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Platform Tenant Telemetry</h1>
            <p class="text-sm text-[#766A5E] mt-1">Multi-firm resource distribution, live database connections, and storage utilization</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.firms.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">domain_add</span>
                <span>Provision New Law Firm</span>
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-[#EFECE6] text-xs font-semibold text-[#554D45] hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">autorenew</span>
                <span>Renewals</span>
            </a>
        </div>
    </div>

    <!-- Infrastructure Health Telemetry -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Tenants Active</span>
            <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $firms->count() }} Firm(s)</span>
            <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">100% Operational</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Dual Database Mode</span>
            <span class="text-xl font-mono font-bold text-[#9F8349] mt-1 block">{{ ucfirst(config('database.default', 'sqlite')) }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Connection Active &amp; Pooled</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Cloud Storage Driver</span>
            <span class="text-xl font-mono font-bold text-[#222222] mt-1 block">{{ ucfirst(config('filesystems.default', 'local')) }}</span>
            <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">AWS S3 &bull; Cloudflare R2 Ready</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Platform Health</span>
            <span class="text-2xl font-serif font-bold text-emerald-700 mt-1 block">Optimal</span>
            <span class="text-xs text-emerald-600 mt-2 block font-mono">All Systems Green</span>
        </div>
    </div>

    <!-- Law Firm Tenant Directory Summary -->
    <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#EFECE6] flex items-center justify-between">
            <div>
                <h3 class="font-serif font-bold text-lg text-[#222222]">Registered Law Firm Tenants</h3>
                <p class="text-xs text-[#766A5E] mt-0.5">{{ $firms->count() }} Organizations Active on Cloud Infrastructure</p>
            </div>
            <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#9F8349] font-semibold hover:underline flex items-center gap-1">
                <span>Manage All Law Firms</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold">Law Firm Name</th>
                        <th class="py-3 px-4 font-semibold">Slug / Domain</th>
                        <th class="py-3 px-4 text-center font-semibold">Attorneys &amp; Staff</th>
                        <th class="py-3 px-4 text-center font-semibold">Active Matters</th>
                        <th class="py-3 px-4 text-center font-semibold">Vault Files</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6] text-xs">
                    @forelse($firms as $firm)
                    <tr class="hover:bg-[#FAF8F5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">
                            <a href="{{ route('admin.firms.show', $firm) }}" class="hover:text-[#9F8349] hover:underline font-bold text-sm">
                                {{ $firm->display_title }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#766A5E]">
                            <span class="bg-[#FAF8F5] px-2 py-0.5 rounded-md border border-[#EAE4DC]">
                                {{ $firm->slug }}.lexiscore.app
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-[#222222]">
                            {{ $firm->users_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-[#222222]">
                            {{ $firm->matters_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-[#766A5E]">
                            {{ $firm->documents_count }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.firms.show', $firm) }}" class="px-3 py-1.5 rounded-lg border border-[#EAE4DC] text-xs font-semibold text-[#222222] hover:bg-[#FAF8F5] shadow-xs transition-colors">
                                    Overview
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#766A5E]">
                            <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">corporate_fare</span>
                            <p class="font-medium">No law firms registered yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
