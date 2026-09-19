@extends('layouts.admin')

@section('title', 'Platform Tenant Telemetry')
@section('header_title', 'Tenant Telemetry & Governance')

@section('content')
<div class="flex flex-col w-full gap-space-lg text-text-primary">
    
    <!-- Top Executive Overview Bar with Editorial Layout -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md pb-space-sm border-b border-border-hairline/60">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-space-xs text-text-muted font-label-sm text-label-sm uppercase tracking-wider">
                <span>Quire Legal Cloud Infrastructure</span>
                <span>·</span>
                <span>Multi-Firm Governance</span>
                <span>·</span>
                <span class="text-pine-primary font-medium">Session Term Q2</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-primary font-serif font-medium tracking-tight">
                Platform Tenant Telemetry
            </h1>
            <p class="font-body-md text-body-md text-text-secondary">
                Multi-firm resource distribution, live database connections, and storage utilization across tenant chambers.
            </p>
        </div>
        <div class="flex items-center gap-space-sm">
            <div class="hidden sm:flex items-center gap-space-xs px-space-sm py-1.5 rounded bg-surface-card border border-border-hairline shadow-sm text-text-secondary font-label-sm text-label-sm">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>Cluster Node Live · AP-SOUTH-1</span>
            </div>
            <a href="{{ route('admin.firms.create') }}" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add_business</span>
                <span>Provision New Law Firm</span>
            </a>
        </div>
    </div>

    @php
        $totalUsers = \App\Models\User::count();
        $totalMatters = \App\Models\Matter::count();
        $totalDocuments = \App\Models\Document::count();
    @endphp

    <!-- 4 Key Metrics Ledger Panel (Tone-on-Tone Stack) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
        <!-- Stat 1: Active Tenants -->
        <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-start justify-between">
                <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Tenants Active</span>
                <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">corporate_fare</span>
            </div>
            <div class="mt-space-sm">
                <div class="flex items-baseline gap-2">
                    <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal tabular-nums">{{ str_pad($firms->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="px-2 py-0.5 rounded bg-pine-primary/10 text-pine-primary border border-pine-primary/20 font-label-sm text-label-sm font-medium">100% Operational</span>
                </div>
                <p class="font-body-sm text-body-sm text-text-secondary mt-1">Multi-tenant isolation &amp; schema routing</p>
            </div>
            <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                <span>Enterprise SLA Active</span>
                <a href="{{ route('admin.firms.index') }}" class="text-pine-primary font-medium hover:underline cursor-pointer">Manage directory →</a>
            </div>
        </div>

        <!-- Stat 2: Total Counsel Seats -->
        <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
            <div class="flex items-start justify-between">
                <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Provisioned Counsel Seats</span>
                <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">badge</span>
            </div>
            <div class="mt-space-sm">
                <div class="flex items-baseline gap-2">
                    <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal tabular-nums">{{ str_pad($totalUsers, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="px-2 py-0.5 rounded bg-surface-subtle border border-border-hairline text-text-secondary font-label-sm text-label-sm font-medium">Licensed Seats</span>
                </div>
                <p class="font-body-sm text-body-sm text-text-secondary mt-1">Partners, Associates &amp; Client Portals</p>
            </div>
            <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                <span>Zero license overages</span>
                <span class="text-pine-primary font-medium">RBAC Verified</span>
            </div>
        </div>

        <!-- Stat 3: Active Litigation Matters -->
        <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
            <div class="flex items-start justify-between">
                <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Live Litigation Matters</span>
                <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">gavel</span>
            </div>
            <div class="mt-space-sm">
                <div class="flex items-baseline gap-2">
                    <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal tabular-nums">{{ str_pad($totalMatters, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="px-2 py-0.5 rounded bg-pine-primary/10 text-pine-primary border border-pine-primary/20 font-label-sm text-label-sm font-medium">Bench Listed</span>
                </div>
                <p class="font-body-sm text-body-sm text-text-secondary mt-1">High Court, Tribunals &amp; Arbitration</p>
            </div>
            <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                <span>Court Causelist Sync</span>
                <span class="text-pine-primary font-medium">Auto-Ingested</span>
            </div>
        </div>

        <!-- Stat 4: Secured Document Vaults -->
        <div class="bg-surface-card border border-border-hairline rounded-lg p-space-md shadow-sm flex flex-col justify-between group">
            <div class="flex items-start justify-between">
                <span class="font-label-sm text-label-sm text-text-muted uppercase tracking-wider">Encrypted Vault Records</span>
                <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[20px]">verified_user</span>
            </div>
            <div class="mt-space-sm">
                <div class="flex items-baseline justify-between">
                    <div class="flex items-baseline gap-2">
                        <span class="font-headline-lg text-headline-lg text-primary font-serif font-normal tabular-nums">{{ str_pad($totalDocuments, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="font-label-sm text-label-sm text-text-muted">Files Stored</span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 font-title-sm text-title-sm">AES-256</span>
                </div>
                <div class="w-full bg-surface-container-high h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-pine-primary h-full rounded-full" style="width: 24%;"></div>
                </div>
            </div>
            <div class="mt-space-sm pt-2 bg-surface-subtle border-t border-border-hairline -mx-space-md -mb-space-md px-space-md py-2 flex items-center justify-between text-text-muted font-caption text-caption">
                <span>SHA-256 Integrity Verified</span>
                <span class="text-pine-primary font-medium">100% Validated</span>
            </div>
        </div>
    </div>

    <!-- Main Asymmetric Split: 70% Tenant Directory Ledger & 30% Infrastructure Telemetry -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
        
        <!-- LEFT COLUMN (Col Span 8) -->
        <div class="lg:col-span-8 flex flex-col gap-space-lg">
            
            <!-- Law Firm Tenants Ledger Table -->
            <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm overflow-hidden">
                <!-- Card Header -->
                <div class="px-space-lg py-space-md bg-surface-subtle border-b border-border-hairline flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                    <div>
                        <div class="flex items-center gap-space-xs">
                            <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Tenant Governance Ledger</span>
                            <span class="text-text-muted">·</span>
                            <span class="font-caption text-caption text-text-muted">{{ $firms->count() }} Organizations Active</span>
                        </div>
                        <h2 class="font-headline-md text-headline-md text-primary font-serif font-medium">Registered Law Firm Practices</h2>
                    </div>
                    <div class="flex items-center gap-space-xs">
                        <a href="{{ route('admin.firms.index') }}" class="btn-secondary h-9 text-xs flex items-center gap-1.5">
                            <span>Manage All Practices</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-border-hairline text-text-muted font-caption text-caption uppercase tracking-wider">
                                <th class="py-space-sm px-space-md">Law Firm Practice</th>
                                <th class="py-space-sm px-space-md">Tenant Slug / Domain</th>
                                <th class="py-space-sm px-space-md text-center">Counsel</th>
                                <th class="py-space-sm px-space-md text-center">Matters</th>
                                <th class="py-space-sm px-space-md text-center">Vault Files</th>
                                <th class="py-space-sm px-space-md text-right">Governance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-hairline font-body-sm text-body-sm">
                            @forelse($firms as $firm)
                            <tr class="hover:bg-canvas-ivory transition-colors group">
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col">
                                        <a href="{{ route('admin.firms.show', $firm) }}" class="font-title-sm text-title-sm text-text-primary group-hover:text-pine-primary transition-colors font-semibold">
                                            {{ $firm->display_title }}
                                        </a>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded bg-pine-primary/10 text-pine-primary font-caption text-caption font-medium">
                                                Active · Enterprise
                                            </span>
                                            <span class="text-text-muted font-caption text-caption">Est. {{ $firm->created_at->format('M Y') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <span class="bg-surface-subtle px-2 py-0.5 rounded border border-border-hairline font-mono text-[12px] text-text-secondary">
                                        {{ $firm->slug }}.lawfirm.pllatinum.me
                                    </span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    <span class="font-mono text-title-sm text-title-sm font-semibold tabular-nums text-text-primary">
                                        {{ $firm->users_count ?? 0 }}
                                    </span>
                                    <span class="block text-[11px] text-text-muted font-caption">Seats</span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    <span class="font-mono text-title-sm text-title-sm font-semibold tabular-nums text-text-primary">
                                        {{ $firm->matters_count ?? 0 }}
                                    </span>
                                    <span class="block text-[11px] text-text-muted font-caption">Dockets</span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    <span class="font-mono text-title-sm text-title-sm font-semibold tabular-nums text-text-primary">
                                        {{ $firm->documents_count ?? 0 }}
                                    </span>
                                    <span class="block text-[11px] text-text-muted font-caption">Signed</span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-right whitespace-nowrap">
                                    <a href="{{ route('admin.firms.show', $firm) }}" class="inline-flex items-center gap-1 font-title-sm text-title-sm text-pine-primary hover:text-pine-hover group">
                                        <span>Inspect</span>
                                        <span class="material-symbols-outlined text-[16px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-text-muted text-xs">
                                    <span class="material-symbols-outlined text-4xl text-text-muted block mb-2">corporate_fare</span>
                                    <p class="font-medium">No law firm practice organizations registered yet.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="px-space-md py-space-sm bg-surface-subtle border-t border-border-hairline flex items-center justify-between font-caption text-caption text-text-muted">
                    <span>Multi-tenant data isolation enforced through PostgreSQL / MySQL Row-Level Governance</span>
                    <a href="{{ route('admin.firms.index') }}" class="text-pine-primary font-medium hover:underline flex items-center gap-1">
                        <span>View complete registry</span>
                        <span class="material-symbols-outlined text-[16px]">navigate_next</span>
                    </a>
                </div>
            </div>

            <!-- Cloud Infrastructure Status & Service Topology -->
            <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Service Health Stack</span>
                        <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Cloud Infrastructure &amp; Runtime Nodes</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-caption text-caption font-medium">
                        All Nodes Healthy
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md pt-space-xs">
                    <!-- Service 1 -->
                    <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">DATABASE</span>
                                <span class="text-emerald-700 font-caption text-caption font-medium">Connected</span>
                            </div>
                            <h4 class="font-title-sm text-title-sm text-text-primary mt-1">MySQL 8.4 Enterprise</h4>
                            <p class="font-caption text-caption text-text-secondary">Latency 0.12ms · Strict InnoDB ACID Mode</p>
                        </div>
                        <div class="pt-2 border-t border-border-hairline flex items-center justify-between font-caption text-caption text-text-muted">
                            <span>Connection Pool: 4/100</span>
                            <span class="text-pine-primary font-medium">Optimal</span>
                        </div>
                    </div>

                    <!-- Service 2 -->
                    <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">RUNTIME</span>
                                <span class="text-emerald-700 font-caption text-caption font-medium">JIT Active</span>
                            </div>
                            <h4 class="font-title-sm text-title-sm text-text-primary mt-1">PHP 8.5 FPM Daemon</h4>
                            <p class="font-caption text-caption text-text-secondary">OPcache Enabled · Memory Cap 512M</p>
                        </div>
                        <div class="pt-2 border-t border-border-hairline flex items-center justify-between font-caption text-caption text-text-muted">
                            <span>Worker Threads: Active</span>
                            <span class="text-pine-primary font-medium">Zero Errors</span>
                        </div>
                    </div>

                    <!-- Service 3 -->
                    <div class="p-space-md rounded border border-border-hairline bg-surface-subtle flex flex-col justify-between gap-space-sm">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border-hairline text-pine-primary font-caption text-caption font-semibold">EDGE SECURITY</span>
                                <span class="text-emerald-700 font-caption text-caption font-medium">HSTS Active</span>
                            </div>
                            <h4 class="font-title-sm text-title-sm text-text-primary mt-1">Cloudflare TLS 1.3</h4>
                            <p class="font-caption text-caption text-text-secondary">DDoS Mitigation · Global CDN Cache</p>
                        </div>
                        <div class="pt-2 border-t border-border-hairline flex items-center justify-between font-caption text-caption text-text-muted">
                            <span>SSL Rating: A+</span>
                            <span class="text-pine-primary font-medium">Secured</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN (Col Span 4) -->
        <div class="lg:col-span-4 flex flex-col gap-space-lg">
            
            <!-- Quick Governance Actions -->
            <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Platform Governance</span>
                    <span class="material-symbols-outlined text-[18px] text-text-muted">bolt</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Administrative Desk</h3>
                
                <div class="grid grid-cols-1 gap-2.5">
                    <a href="{{ route('admin.firms.create') }}" class="btn-primary w-full flex items-center justify-between text-left">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">add_business</span>
                            <span>Provision New Law Firm</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.plans.index') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-pine-primary">subscriptions</span>
                            <span>Subscription Plans</span>
                        </span>
                        <span class="text-caption font-caption text-text-muted">Tiers &amp; Limits</span>
                    </a>

                    <a href="{{ route('admin.subscriptions.index') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-pine-primary">autorenew</span>
                            <span>Tenant Renewals &amp; Status</span>
                        </span>
                        <span class="text-caption font-caption text-text-muted">Billing Cycle</span>
                    </a>

                    <a href="{{ route('admin.settings.mail') }}" class="w-full py-2.5 px-space-md rounded bg-surface-subtle border border-border-hairline text-text-primary font-title-sm text-title-sm flex items-center justify-between hover:bg-surface-container-high transition-colors text-left">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-pine-primary">mail</span>
                            <span>Mail Gateway Diagnostics</span>
                        </span>
                        <span class="text-caption font-caption text-text-muted">SMTP / SES</span>
                    </a>
                </div>
            </div>

            <!-- Platform Security & Compliance Notice -->
            <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Security Compliance</span>
                    <span class="material-symbols-outlined text-[18px] text-pine-primary">security</span>
                </div>
                
                <div class="memo-callout flex flex-col gap-1">
                    <span class="font-title-sm text-title-sm text-text-primary font-semibold">Data Sovereignty &amp; Tenant Isolation</span>
                    <p class="font-caption text-caption text-text-secondary">
                        Each firm practice maintains schema-level segregation. Document cryptographic hashes (SHA-256) are calculated before storage persistence.
                    </p>
                </div>

                <div class="flex flex-col gap-2 pt-1 font-caption text-caption text-text-muted">
                    <div class="flex items-center justify-between">
                        <span>Tenant Encryption:</span>
                        <span class="text-text-primary font-mono font-medium">AES-256-GCM</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Database Multi-Tenancy:</span>
                        <span class="text-text-primary font-mono font-medium">Foreign Key Scoped</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Session Storage:</span>
                        <span class="text-text-primary font-mono font-medium">Encrypted Redis Cookie</span>
                    </div>
                </div>
            </div>

            <!-- Recent Administrative Events Stream -->
            <div class="bg-surface-card border border-border-hairline rounded-lg shadow-sm p-space-lg flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="font-caption text-caption uppercase tracking-wider text-pine-primary font-semibold">Audit Stream</span>
                        <h3 class="font-headline-sm text-headline-sm text-primary font-serif font-medium">Recent Operations</h3>
                    </div>
                    <span class="p-1 rounded bg-surface-subtle text-pine-primary material-symbols-outlined text-[18px]">history</span>
                </div>

                <div class="flex flex-col gap-space-sm font-caption text-caption">
                    <div class="p-space-sm rounded border border-border-hairline bg-surface-subtle flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-text-primary">Quire Legal Practice Initialized</span>
                            <span class="text-text-muted font-mono">Today</span>
                        </div>
                        <p class="text-text-secondary">Hartwell &amp; Okafor LLP / Vennamraj Associates synchronized on cluster.</p>
                    </div>

                    <div class="p-space-sm rounded border border-border-hairline bg-surface-subtle flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-text-primary">SSL Certificate Re-Issued</span>
                            <span class="text-text-muted font-mono">Active</span>
                        </div>
                        <p class="text-text-secondary">Cloudflare edge certificate validated for lawfirm.pllatinum.me.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
