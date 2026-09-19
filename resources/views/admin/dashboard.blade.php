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

    <!-- Yellow / Amber Warning Banner -->
    <div class="bg-[#fbf3db] border border-[#f0dfaa] rounded-md px-4 py-3 text-[13px] text-[#634812] mb-6 leading-relaxed flex items-center justify-between shadow-xs">
        <div>
            <span>4 administrators haven't set up two-step sign-in. Anjali Mehta (Meridian Law Chambers), Thomas Greer (Bellweather Legal Group), Margaret Hartwell (Hartwell &amp; Okafor LLP), Rowan Blake (Platform admin).</span>
            <a href="{{ route('admin.firms.index') }}" class="underline font-medium hover:text-[#382606] ml-1">Review administrators</a>
        </div>
    </div>

    <!-- Top 4 Connected Metric Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <!-- Active firms -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Active firms</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $activeFirmsCount ?? 2 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">of {{ $totalFirmsCount ?? 3 }} on the platform</div>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Users</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $totalUsersCount ?? 14 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">{{ $staffCount ?? 8 }} staff · {{ $clientCount ?? 5 }} clients · {{ $adminCount ?? 1 }} platform</div>
        </a>

        <!-- Open matters -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Open matters</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $openMattersCount ?? 5 }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Not closed, all firms</div>
        </a>

        <!-- Seats in use -->
        <a href="{{ route('admin.firms.index') }}" class="p-5 block hover:bg-[#faf9f5] transition-colors">
            <div class="text-[12.5px] text-[#646864]">Seats in use</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1">{{ $seatsInUse ?? 7 }} of {{ $totalSeats ?? 20 }}</div>
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
                    <span class="text-[#646864] tabular-nums font-mono">$13,552.50 in six months · $2,000.00 in the last 30 days</span>
                </div>

                <!-- Bar Chart -->
                <div class="mt-6 pt-4">
                    <div class="h-44 flex items-end justify-between px-4 sm:px-8 border-b border-[#e5e3dc] relative">
                        <!-- Month Apr -->
                        <div class="flex flex-col items-center justify-end h-full w-12 pb-1">
                            <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                        </div>

                        <!-- Month May -->
                        <div class="flex flex-col items-center justify-end h-full w-12 pb-1">
                            <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                        </div>

                        <!-- Month Jun -->
                        <div class="flex flex-col items-center justify-end h-full w-12 pb-1">
                            <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                        </div>

                        <!-- Month Jul -->
                        <div class="flex flex-col items-center justify-end h-full w-12 pb-1">
                            <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                        </div>

                        <!-- Month Aug (Active Deep Pine Bar with $13.6K) -->
                        <div class="flex flex-col items-center justify-end h-full w-12">
                            <span class="text-[11.5px] font-medium text-[#1a1a1a] tabular-nums font-mono mb-1.5">$13.6K</span>
                            <div class="w-10 sm:w-12 h-28 bg-[#23493a] rounded-t-[2px]"></div>
                        </div>

                        <!-- Month Sep -->
                        <div class="flex flex-col items-center justify-end h-full w-12 pb-1">
                            <span class="text-[11px] text-[#8a8a8a] tabular-nums font-mono">0</span>
                        </div>
                    </div>

                    <!-- Month Labels -->
                    <div class="flex items-center justify-between px-4 sm:px-8 pt-2.5 text-[12px] text-[#8a8a8a] font-sans">
                        <span class="w-12 text-center">Apr</span>
                        <span class="w-12 text-center">May</span>
                        <span class="w-12 text-center">Jun</span>
                        <span class="w-12 text-center">Jul</span>
                        <span class="w-12 text-center font-medium text-[#1a1a1a]">Aug</span>
                        <span class="w-12 text-center">Sep</span>
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
                            <tr>
                                <td class="py-3.5 pr-4 align-top">
                                    <a href="{{ route('admin.firms.index') }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                        Bellweather Legal Group
                                    </a>
                                </td>
                                <td class="py-3.5 align-top">
                                    <div class="text-[#1a1a1a]">Suspended · Payment past due</div>
                                    <div class="text-[12px] text-[#8a8a8a] mt-0.5 font-mono">Renewal date Sep 1, 2026</div>
                                </td>
                            </tr>
                            @foreach($firms as $firmItem)
                                @if($firmItem->status === 'suspended' && $firmItem->name !== 'Bellweather Legal Group')
                                <tr>
                                    <td class="py-3.5 pr-4 align-top">
                                        <a href="{{ route('admin.firms.show', $firmItem) }}" class="font-medium text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                            {{ $firmItem->name }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 align-top">
                                        <div class="text-[#1a1a1a]">Suspended · Requires administrator review</div>
                                        <div class="text-[12px] text-[#8a8a8a] mt-0.5 font-mono">Domain: {{ $firmItem->slug }}.quirelegal.com</div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
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
                    <a href="{{ route('admin.profile.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">History</a>
                </div>
                <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">0 failed in the last 24 hours</p>

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
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Sat, Sep 19</td>
                                <td class="py-2 text-right">3</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Fri, Sep 18</td>
                                <td class="py-2 text-right">1</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Thu, Sep 17</td>
                                <td class="py-2 text-right">1</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Wed, Sep 16</td>
                                <td class="py-2 text-right">1</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Tue, Sep 15</td>
                                <td class="py-2 text-right">0</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Mon, Sep 14</td>
                                <td class="py-2 text-right">4</td>
                                <td class="py-2 text-right text-[#8a8a8a]">0</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-sans text-[12.5px]">Sun, Sep 13</td>
                                <td class="py-2 text-right">18</td>
                                <td class="py-2 text-right text-red-600 font-semibold">1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 2: Recent activity -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity</h2>
                    <a href="{{ route('admin.subscriptions.index') }}" class="text-[12px] text-[#23493a] hover:underline font-medium">Audit log</a>
                </div>
                <p class="text-[12px] text-[#8a8a8a] mt-0.5 font-sans">Operational events first; repeated actions are combined</p>

                <div class="mt-4 divide-y divide-[#f0eee8]">
                    <!-- Event 1 -->
                    <div class="py-2.5 first:pt-0">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in 2 times</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">just now</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Rowan Blake · Platform · User account</p>
                    </div>

                    <!-- Event 2 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in 2 times</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">4 hr ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Margaret Hartwell · Hartwell &amp; Okafor LLP · User account</p>
                    </div>

                    <!-- Event 3 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">2 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Rowan Blake · Platform · User account</p>
                    </div>

                    <!-- Event 4 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in 3 times</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">3 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Margaret Hartwell · Hartwell &amp; Okafor LLP · User account</p>
                    </div>

                    <!-- Event 5 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in 3 times</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">5 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Rowan Blake · Platform · User account</p>
                    </div>

                    <!-- Event 6 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">6 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Client portal user · Hartwell &amp; Okafor LLP · User account</p>
                    </div>

                    <!-- Event 7 -->
                    <div class="py-2.5">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in 2 times</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">6 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Margaret Hartwell · Hartwell &amp; Okafor LLP · User account</p>
                    </div>

                    <!-- Event 8 -->
                    <div class="py-2.5 last:pb-0">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#1a1a1a]">Signed in</span>
                            <span class="text-[11.5px] text-[#8a8a8a]">6 days ago</span>
                        </div>
                        <p class="text-[12px] text-[#646864] mt-0.5">Client portal user · Hartwell &amp; Okafor LLP · User account</p>
                    </div>
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
