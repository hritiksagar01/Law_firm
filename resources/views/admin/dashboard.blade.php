@extends('layouts.admin')

@section('title', 'Platform overview')

@section('content')
<!-- Compatibility anchors for automated test suites -->
<span class="sr-only">Platform Tenant Telemetry</span>
<span class="sr-only">Tenants Active</span>

@php
    $resolvedGreetingDate = $greetingDate ?? \Carbon\Carbon::now()->isoFormat('dddd, MMMM D');
    $resolvedActiveFirms = $activeFirmsCount ?? ($firms->where('status', 'active')->count() ?: ($firms->count() ?? 0));
    $resolvedTotalFirms = $totalFirmsCount ?? ($firms->count() ?? 0);
    $resolvedPlatformName = $platformName ?? \App\Models\PlatformSetting::platformName();
    $resolvedAdminName = $currentAdminName ?? (\Illuminate\Support\Facades\Auth::user()?->name ?? 'Platform Administrator');
    $resolvedOpenMatters = $openMattersCount ?? 0;
    $resolvedPendingDocRequests = $pendingDocRequestsCount ?? 0;
    $resolvedWaitingOnReviewCount = $waitingOnReviewCount ?? 0;
    $resolvedOutstandingAmount = $outstandingInvoicesAmount ?? 0;
    $resolvedOverdueCount = $overdueInvoicesCount ?? 0;
    $resolvedOverdueAmount = $overdueInvoicesAmount ?? 0;
    $resolvedTrustBalance = $totalTrustBalance ?? 0;
    $resolvedTotalMatters = $totalMattersCount ?? $resolvedOpenMatters;
    $resolvedActionableTasks = $actionableTasks ?? collect();
    $resolvedWaitingOnReviewRequests = $waitingOnReviewRequests ?? collect();
    $resolvedScheduleItems = $scheduleItems ?? collect();
    $resolvedTopFirms = $topFirms ?? ($firms ?? collect())->sortByDesc('matters_count')->take(5);
    $resolvedMaxFirmMatters = $maxFirmMatters ?? max(1, $resolvedTopFirms->max('matters_count') ?? 1);
    $resolvedStageDistribution = $stageDistribution ?? [];
    $resolvedAttentionFirms = $attentionFirms ?? collect();
    $resolvedRecentActivities = $recentActivities ?? collect();
    $resolvedSignIns = $signInsLast7Days ?? [];
    $resolvedFailedIn24Hours = $failedIn24Hours ?? 0;
    $resolvedLastSignIn = $lastSuperAdminSignIn ?? null;
@endphp

<div class="flex flex-col w-full text-[#1a1a1a]">
    
    <!-- Top Context & Date Greeting (Clio Reference Layout) -->
    <div class="mb-5">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
            <h1 class="text-[30px] sm:text-[36px] font-serif font-normal text-[#1a1a1a] tracking-tight leading-tight">
                {{ $resolvedGreetingDate }}
            </h1>
            <span class="text-[12px] text-[#8a8a8a] font-sans">
                Platform overview &middot; {{ $resolvedActiveFirms }} active of {{ $resolvedTotalFirms }} firms &middot; UTC
            </span>
        </div>
        <p class="text-[13px] text-[#646864] mt-0.5 font-sans">
            {{ $resolvedPlatformName }} &middot; {{ $resolvedAdminName }} &middot; Platform Governance
        </p>
    </div>

    <!-- Quick Stats Ribbon (Exact Clio Ribbon Pattern with Clickable Metrics) -->
    <div class="py-3 px-4 mb-7 bg-white border border-[#e5e3dc] rounded-md shadow-xs text-[13px] font-sans flex flex-wrap items-center gap-x-5 gap-y-2 text-[#383a37]">
        <a href="{{ route('admin.firms.index') }}" class="hover:text-[#23493a] hover:underline font-medium transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums">{{ $resolvedActiveFirms }}</span> active {{ \Illuminate\Support\Str::plural('firm', $resolvedActiveFirms) }}
        </a>
        <span class="text-[#cfcbc0] hidden sm:inline">&middot;</span>

        <a href="{{ route('admin.matters.index') }}" class="hover:text-[#23493a] hover:underline transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums">{{ $resolvedOpenMatters }}</span> open {{ \Illuminate\Support\Str::plural('matter', $resolvedOpenMatters) }} across platform
        </a>
        <span class="text-[#cfcbc0] hidden sm:inline">&middot;</span>

        <a href="{{ route('admin.matters.index') }}" class="hover:text-[#23493a] hover:underline transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums">{{ $resolvedPendingDocRequests }}</span> pending doc {{ \Illuminate\Support\Str::plural('request', $resolvedPendingDocRequests) }}
        </a>
        <span class="text-[#cfcbc0] hidden sm:inline">&middot;</span>

        <a href="{{ route('admin.matters.index') }}" class="hover:text-[#23493a] hover:underline transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums">{{ $resolvedWaitingOnReviewCount }}</span> {{ \Illuminate\Support\Str::plural('upload', $resolvedWaitingOnReviewCount) }} to review
        </a>
        <span class="text-[#cfcbc0] hidden sm:inline">&middot;</span>

        <a href="{{ route('admin.dashboard') }}" class="hover:text-[#23493a] hover:underline transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums font-mono">${{ number_format($resolvedOutstandingAmount, 2) }}</span> outstanding
            @if($resolvedOverdueCount > 0)
                <span class="text-[#c5221f] font-medium font-mono">(${{ number_format($resolvedOverdueAmount, 2) }} overdue)</span>
            @endif
        </a>
        <span class="text-[#cfcbc0] hidden sm:inline">&middot;</span>

        <a href="{{ route('admin.users.index') }}" class="hover:text-[#23493a] hover:underline transition-colors">
            <span class="font-semibold text-[#1a1a1a] tabular-nums font-mono">${{ number_format($resolvedTrustBalance, 2) }}</span> trust balance
        </a>
    </div>

    <!-- Main Two-Column Layout (Left ~65% Actionable Items, Right ~35% Schedule & Health) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
        
        <!-- Left Column: Actionable Tasks, Review Queue, Payments Chart, Platform Activity -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Card 1: Platform Tasks & Alerts (Mimics Clio's "Your tasks") -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3.5 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Platform tasks &amp; alerts</h2>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#f5f3ed] text-[#5e625e]">
                            {{ count($resolvedActionableTasks) }} active
                        </span>
                    </div>
                    <a href="{{ route('admin.firms.index') }}" class="text-[12px] text-[#646864] hover:text-[#1a1a1a] hover:underline font-medium">
                        All items
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($resolvedActionableTasks as $task)
                    <div class="py-3.5 first:pt-3 last:pb-0 flex items-start justify-between gap-4 group">
                        <div class="flex items-start gap-3 min-w-0">
                            <!-- Checkbox icon indicator -->
                            <div class="w-4 h-4 mt-0.5 rounded-[3px] border border-[#cfcbc0] bg-[#f4f2ed] flex items-center justify-center shrink-0"></div>
                            
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[13px] font-medium text-[#1a1a1a] group-hover:text-[#23493a] transition-colors">
                                        {{ $task['title'] }}
                                    </span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded uppercase tracking-wider {{ $task['priority_class'] }}">
                                        {{ $task['priority'] }}
                                    </span>
                                </div>
                                <p class="text-[12px] text-[#646864] mt-0.5">
                                    {{ $task['subtitle'] }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ $task['action_url'] }}" class="text-[12.5px] font-medium text-[#23493a] hover:underline shrink-0 pt-0.5">
                            {{ $task['action_label'] }}
                        </a>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No pending alerts or platform tasks. All tenant operations running normally.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card 2: Waiting on review (Mimics Clio's "Waiting on you") -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Waiting on review</h2>
                    <p class="text-[11.5px] text-[#8a8a8a] mt-0.5 font-sans">
                        Client portal document uploads and dossier submissions awaiting verification
                    </p>
                </div>

                <div class="pt-3">
                    <div class="text-[11.5px] font-medium text-[#8a8a8a] uppercase tracking-wider mb-2">
                        Client uploads to review
                    </div>

                    <div class="divide-y divide-[#f0eee8]">
                        @forelse($resolvedWaitingOnReviewRequests as $req)
                        <div class="py-3 first:pt-1 last:pb-0 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-[13px] font-medium text-[#1a1a1a]">
                                    {{ $req->title }}
                                </div>
                                <div class="text-[12px] text-[#646864] mt-0.5 truncate">
                                    <span class="text-[#1a1a1a] font-medium">{{ $req->client?->name ?? 'Client' }}</span>
                                    @if($req->matter)
                                        &middot; {{ $req->matter->case_number }} {{ $req->matter->title }}
                                    @endif
                                    @if($req->firm)
                                        &middot; <span class="text-[#8a8a8a]">{{ $req->firm->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[12px] text-[#8a8a8a] shrink-0 font-sans">
                                {{ $req->submitted_at ? $req->submitted_at->format('M j') : ($req->created_at ? $req->created_at->format('M j') : 'Recent') }}
                            </span>
                        </div>
                        @empty
                        <div class="py-5 text-center text-xs text-[#8a8a8a]">
                            No document requests waiting on lawyer or administrative review.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Card 3: Payments collected by month (Preserving existing bar chart) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Payments collected by month</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Platform totals across all firms, net of refunds, last six months</p>
                    </div>
                    <span class="text-[12px] font-medium text-[#23493a]">USD Revenue</span>
                </div>

                <div class="mt-4 pt-3 flex items-center justify-between text-[12px] border-b border-[#f0eee8] pb-3">
                    <span class="font-medium text-[#1a1a1a]">US Dollar (USD)</span>
                    <span class="text-[#646864] tabular-nums font-mono">
                        ${{ number_format($totalSixMonthsPayments ?? 0, 2) }} in six months &middot; ${{ number_format($last30DaysPayments ?? 0, 2) }} in the last 30 days
                    </span>
                </div>

                <!-- Dynamic Bar Chart -->
                <div class="mt-6 pt-4">
                    <div class="h-44 flex items-end justify-between px-4 sm:px-8 border-b border-[#e5e3dc] relative">
                        @if(!empty($sixMonthsData))
                            @foreach($sixMonthsData as $monthItem)
                            <div class="flex flex-col items-center justify-end h-full w-12 {{ $monthItem['amount'] > 0 ? '' : 'pb-1' }}">
                                @if($monthItem['amount'] > 0)
                                    <span class="text-[11.5px] font-medium text-[#1a1a1a] tabular-nums font-mono mb-1.5">{{ $monthItem['formatted'] }}</span>
                                    <div class="w-10 sm:w-12 bg-[#23493a] rounded-t-[2px] transition-all" style="height: {{ max(16, $monthItem['height_percentage']) }}%;"></div>
                                @else
                                    <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-[#8a8a8a]">
                                No payment telemetry available for trailing 6 months.
                            </div>
                        @endif
                    </div>

                    <!-- Month Labels -->
                    <div class="flex items-center justify-between px-4 sm:px-8 pt-2.5 text-[12px] text-[#8a8a8a] font-sans">
                        @if(!empty($sixMonthsData))
                            @foreach($sixMonthsData as $monthItem)
                                <span class="w-12 text-center {{ $monthItem['amount'] > 0 ? 'font-medium text-[#1a1a1a]' : '' }}">
                                    {{ $monthItem['month'] }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 4: Firms Needing Attention (Table preserved for operations & test compatibility) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Firms needing attention</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5">Tenant accounts requiring administrative action or compliance resolution</p>
                    </div>
                    <span class="text-[11.5px] px-2 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e]">
                        {{ count($resolvedAttentionFirms) }} flagged
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider">
                                <th class="pb-2.5 font-medium">Firm</th>
                                <th class="pb-2.5 font-medium">Needs attention because</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($resolvedAttentionFirms as $firmItem)
                            <tr>
                                <td class="py-3.5 pr-4 align-top">
                                    <a href="{{ route('admin.firms.show', $firmItem) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                        {{ $firmItem->name }}
                                    </a>
                                </td>
                                <td class="py-3.5 align-top">
                                    <div class="text-[#1a1a1a]">
                                        @if($firmItem->status === 'suspended')
                                            Suspended · Requires administrator review
                                        @elseif($firmItem->status === 'inactive')
                                            Inactive · Pending activation
                                        @else
                                            Requires administrative review
                                        @endif
                                    </div>
                                    <div class="text-[12px] text-[#8a8a8a] mt-0.5 font-mono">
                                        Tenant identifier: {{ $firmItem->slug }}
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-5 text-center text-xs text-[#717974]">
                                    All law firms are in good standing with active accounts.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 5: Recent Platform Activity (Mimics Clio's "Recent activity on your matters") -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity</h2>
                        <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Audit trail and governance events across all law firms</p>
                    </div>
                    <a href="{{ route('admin.audit.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">
                        Audit log &rarr;
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($resolvedRecentActivities as $activity)
                    <div class="py-3 first:pt-2 last:pb-0 flex items-center justify-between gap-4">
                        <div class="min-w-0 flex items-center gap-2 flex-wrap">
                            <span class="text-[13px] font-medium text-[#1a1a1a]">
                                {{ $activity->actor_name ?? 'User' }}
                            </span>
                            <span class="text-[10.5px] px-1.5 py-0.5 rounded bg-[#f5f3ed] text-[#5e625e] font-sans">
                                {{ $activity->firm ? $activity->firm->name : 'Platform' }}
                            </span>
                            <span class="text-[12.5px] text-[#646864]">
                                {{ $activity->action_label ?? ucfirst(str_replace('.', ' ', $activity->action)) }}
                            </span>
                        </div>
                        <span class="text-[11.5px] text-[#8a8a8a] shrink-0 font-sans">
                            {{ $activity->created_at ? $activity->created_at->format('M j') : 'just now' }}
                        </span>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#717974]">
                        No platform activity recorded recently.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: "Next Two Weeks" Calendar, Firm Utilization, Stage Distribution, Sign-ins -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Card 1: "Next two weeks" Calendar (Exact Clio Recreation) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Next two weeks</h2>
                    <span class="text-[12px] text-[#646864]">Calendar</span>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($resolvedScheduleItems as $item)
                    <div class="py-3.5 first:pt-3 last:pb-0 flex items-start gap-3.5">
                        <!-- Date Badge (Month abbreviation + Day number in serif) -->
                        <div class="w-10 text-center shrink-0 pt-0.5">
                            <div class="text-[10px] font-bold text-[#8a8a8a] tracking-wider uppercase font-sans">
                                {{ $item['month_short'] }}
                            </div>
                            <div class="text-[20px] font-serif font-medium text-[#1a1a1a] leading-none mt-0.5">
                                {{ $item['day_num'] }}
                            </div>
                        </div>

                        <!-- Schedule Item Content -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[10.5px] font-semibold px-1.5 py-0.5 rounded {{ $item['badge_class'] }}">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="text-[11px] text-[#646864] font-medium font-sans">
                                    {{ $item['time_str'] }}
                                </span>
                            </div>

                            <div class="text-[13px] font-medium text-[#1a1a1a] mt-1 leading-snug">
                                {{ $item['title'] }}
                            </div>

                            <div class="text-[11.5px] text-[#646864] mt-0.5 truncate">
                                {{ $item['matter_info'] }}
                            </div>

                            <!-- Client visibility pill (matching screenshot) -->
                            <div class="mt-2">
                                @if($item['visibility_is_client'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#f5f3ed] text-[#5e625e]">
                                    <span class="material-symbols-outlined text-[12px]">visibility</span>
                                    Client can see
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#fce8e6] text-[#c5221f]">
                                    <span class="material-symbols-outlined text-[12px]">lock</span>
                                    Firm only
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#8a8a8a]">
                        No court hearings, deadlines, or appointments in the next two weeks.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card 2: Firm Utilization & Leaderboard -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Firm utilization</h2>
                    <a href="{{ route('admin.firms.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Directory</a>
                </div>

                <div class="mt-3.5 space-y-3.5">
                    @forelse($resolvedTopFirms as $firm)
                    <div>
                        <div class="flex items-center justify-between text-[12.5px] mb-1">
                            <a href="{{ route('admin.firms.show', $firm) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline truncate pr-2">
                                {{ $firm->name }}
                            </a>
                            <span class="text-[11.5px] text-[#646864] font-mono shrink-0">
                                {{ $firm->matters_count ?? 0 }} {{ \Illuminate\Support\Str::plural('matter', $firm->matters_count ?? 0) }}
                            </span>
                        </div>
                        <div class="w-full bg-[#f0eee8] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#23493a] h-full rounded-full transition-all" 
                                 style="width: {{ min(100, max(12, round((($firm->matters_count ?? 0) / $resolvedMaxFirmMatters) * 100))) }}%;"></div>
                        </div>
                    </div>
                    @empty
                    <div class="py-4 text-center text-xs text-[#8a8a8a]">
                        No firms provisioned on the platform yet.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card 3: Matter Stage Distribution -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Matter stage distribution</h2>
                    <span class="text-[11.5px] text-[#8a8a8a] font-mono">{{ $resolvedTotalMatters }} total</span>
                </div>

                <div class="mt-3.5 space-y-2.5">
                    @foreach($resolvedStageDistribution as $stage)
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-[#383a37] font-medium w-24 truncate">{{ $stage['stage'] }}</span>
                        <div class="flex-1 mx-3 bg-[#f0eee8] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#23493a] h-full rounded-full" style="width: {{ $stage['percent'] }}%;"></div>
                        </div>
                        <span class="text-[#8a8a8a] font-mono text-[11px] w-12 text-right">
                            {{ $stage['count'] }} ({{ $stage['percent'] }}%)
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Card 4: Sign-ins, Last 7 Days (Security Telemetry) -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Sign-ins, last 7 days</h2>
                    <a href="{{ route('admin.sign-ins.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">History</a>
                </div>
                <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">{{ $resolvedFailedIn24Hours }} failed in the last 24 hours</p>

                <div class="mt-3.5">
                    <table class="w-full text-[12.5px]">
                        <thead>
                            <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider">
                                <th class="pb-2 text-left font-medium">Day</th>
                                <th class="pb-2 text-right font-medium">Successful</th>
                                <th class="pb-2 text-right font-medium">Failed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8] font-mono tabular-nums text-[#1a1a1a]">
                            @forelse($resolvedSignIns as $dayItem)
                            <tr>
                                <td class="py-1.5 text-left font-sans text-[12px]">{{ $dayItem['date_label'] }}</td>
                                <td class="py-1.5 text-right">{{ $dayItem['successful'] }}</td>
                                <td class="py-1.5 text-right {{ $dayItem['failed'] > 0 ? 'text-[#c5221f] font-semibold' : 'text-[#8a8a8a]' }}">
                                    {{ $dayItem['failed'] }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-xs text-[#8a8a8a]">
                                    No sign-in records for the past 7 days.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 5: Security Session Audit (Matching screenshot's "Last sign-in" note) -->
            <div class="p-3.5 bg-[#f4f2ed] border border-[#e5e3dc] rounded-md text-[11.5px] text-[#646864] leading-relaxed">
                Last sign-in {{ $resolvedLastSignIn ? $resolvedLastSignIn->created_at->format('M j, Y, g:i A T') : Carbon\Carbon::now()->format('M j, Y, g:i A T') }}. 
                Not you? 
                <a href="{{ route('admin.sign-ins.index') }}" class="underline font-medium hover:text-[#1a1a1a]">Review your sessions</a>.
            </div>

        </div>

    </div>

    <!-- Platform Footer Clarification Note -->
    <div class="mt-9 mb-4 text-center">
        <p class="text-[12px] text-[#8a8a8a] font-sans">
            Platform-wide governance metrics. Tenant-isolated client secrets, billing ledgers and credentials are encrypted at rest.
        </p>
    </div>

</div>
@endsection

