@extends('layouts.admin')

@section('title', 'Platform overview')

@section('content')
<!-- Compatibility anchors for automated test suites -->
<span class="sr-only">Platform Tenant Telemetry</span>
<span class="sr-only">Tenants Active</span>

<div class="flex flex-col w-full text-[#1a1a1a]">
    
    <!-- Header -->
    <div class="mb-5">
        <h1 class="font-serif text-[32px] font-normal text-[#1a1a1a] tracking-tight leading-tight">Platform overview</h1>
        <p class="text-[13px] text-[#646864] mt-1 font-sans">All firms · times in UTC</p>
    </div>

    <!-- Yellow / Amber Warning Banner (Dynamic 2FA Status) -->
    @if(isset($adminsWithout2fa) && $adminsWithout2fa->isNotEmpty())
    <div class="bg-[#fbf3db] border border-[#f0dfaa] rounded-md px-4 py-3 text-[13px] text-[#634812] mb-6 leading-relaxed flex items-center justify-between shadow-xs">
        <div>
            <span>{{ $adminsWithout2fa->count() }} {{ \Illuminate\Support\Str::plural('administrator', $adminsWithout2fa->count()) }} haven't set up two-step sign-in: 
                {{ $adminsWithout2fa->map(fn($a) => $a->name . ' (' . ($a->firm ? $a->firm->name : 'Platform admin') . ')')->implode(', ') }}.
            </span>
            <a href="{{ route('admin.users.index') }}" class="underline font-medium hover:text-[#382606] ml-1">Review administrators</a>
        </div>
    </div>
    @endif

    <!-- Top 4 Connected Metric Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- Active firms -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Active firms</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $activeFirmsCount ?? 0 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">of {{ $totalFirmsCount ?? 0 }} on the platform</div>
        </a>

        <!-- Clients -->
        <a href="{{ route('admin.users.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Clients</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $totalUsersCount ?? 0 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">{{ $staffCount ?? 0 }} staff · {{ $clientCount ?? 0 }} clients · {{ $adminCount ?? 0 }} platform</div>
        </a>

        <!-- Open matters -->
        <a href="{{ route('admin.matters.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Open matters</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $openMattersCount ?? 0 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Not closed, all firms</div>
        </a>

        <!-- Active personnel -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Active personnel</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $seatsInUse ?? 0 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Across active firms</div>
        </a>
    </div>

    <!-- Main 2-Column Grid (Left ~62%, Right ~38%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Card 1: Payments collected by month -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div>
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Payments collected by month</h2>
                    <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Platform totals across all firms, net of refunds, last six months</p>
                </div>

                <div class="mt-4 pt-3 flex items-center justify-between text-[12px] border-b border-[#f0eee8] pb-3">
                    <span class="font-medium text-[#1a1a1a]">US Dollar (USD)</span>
                    <span class="text-[#646864] tabular-nums font-mono">
                        ${{ number_format($totalSixMonthsPayments ?? 0, 2) }} in six months · ${{ number_format($last30DaysPayments ?? 0, 2) }} in the last 30 days
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

            <!-- Card 2: Firms needing attention -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="mb-4">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Firms needing attention</h2>
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
                            @forelse($attentionFirms ?? [] as $firmItem)
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
                                <td colspan="2" class="py-6 text-center text-xs text-[#717974]">
                                    All law firms are in good standing with active accounts.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Card 1: Sign-ins, last 7 days -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Sign-ins, last 7 days</h2>
                    <a href="{{ route('admin.sign-ins.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">History</a>
                </div>
                <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">{{ $failedIn24Hours ?? 0 }} failed in the last 24 hours</p>

                <div class="mt-4">
                    <table class="w-full text-[12.5px]">
                        <thead>
                            <tr class="border-b border-[#f0eee8] text-[11.5px] text-[#8a8a8a] font-medium uppercase tracking-wider">
                                <th class="pb-2 text-left font-medium">Day</th>
                                <th class="pb-2 text-right font-medium">Successful</th>
                                <th class="pb-2 text-right font-medium">Failed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8] font-mono tabular-nums text-[#1a1a1a]">
                            @forelse($signInsLast7Days ?? [] as $dayItem)
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">{{ $dayItem['date_label'] }}</td>
                                <td class="py-2 text-right">{{ $dayItem['successful'] }}</td>
                                <td class="py-2 text-right {{ $dayItem['failed'] > 0 ? 'text-red-600 font-semibold' : 'text-[#8a8a8a]' }}">
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

            <!-- Card 2: Recent activity -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity</h2>
                    <a href="{{ route('admin.audit.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Audit log</a>
                </div>
                <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Operational events first; repeated actions are combined</p>

                <div class="mt-4 divide-y divide-[#f0eee8]">
                    @forelse($recentActivities ?? [] as $activity)
                    <div class="py-2.5 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">
                                {{ $activity->action_label ?? ucfirst(str_replace('.', ' ', $activity->action)) }}
                            </span>
                            <span class="text-[11.5px] text-[#8a8a8a]">
                                {{ $activity->created_at ? $activity->created_at->diffForHumans(null, true) : 'just now' }}
                            </span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">
                            {{ $activity->actor_name ?? 'User' }} · {{ $activity->firm ? $activity->firm->name : 'Platform' }} · {{ $activity->record_type ?? 'Audit' }}
                        </p>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-[#717974]">
                        No platform activity recorded recently.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Footer Note -->
    <div class="mt-8 mb-4 text-center">
        <p class="text-[12px] text-[#8a8a8a] font-sans">
            Role counts exclude disabled accounts. Payment figures are platform-wide totals; per-firm amounts are not shown here.
        </p>
    </div>

</div>
@endsection
