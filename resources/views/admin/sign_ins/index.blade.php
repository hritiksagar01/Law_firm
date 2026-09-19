@extends('layouts.admin')

@section('title', 'Sign-in history — Quire Legal Multi-Tenant Cloud')

@section('content')
<div class="space-y-6">

    <!-- Page Header (Exact Quire styling) -->
    <div>
        <h1 class="text-3xl font-serif text-[#1b1c18] tracking-tight">Sign-in history</h1>
        <p class="text-xs text-[#5e625e] mt-1 font-sans">
            Every attempt, successful or not · UTC
        </p>
    </div>

    <!-- Failed Sign-ins Alert Card -->
    <div class="bg-[#faf9f6] border border-[#e5e3dc] rounded-sm p-4">
        <h2 class="text-sm font-semibold text-[#1b1c18]">Failed sign-ins by IP, last 24 hours</h2>
        <p class="text-xs text-[#717974] mt-0.5 mb-3">
            Five failures lock that account for 15 minutes; thirty from one address lock the address.
        </p>

        @if($failedAttempts24h->isEmpty())
            <p class="text-xs text-[#5e625e]">No failed attempts in the last 24 hours.</p>
        @else
            <div class="space-y-1.5 text-xs">
                @foreach($failedAttempts24h as $fa)
                    <div class="flex items-center gap-3 text-[#ba1a1a]">
                        <span class="font-mono text-[11.5px]">{{ $fa->ip_address }}</span>
                        <span>—</span>
                        <span>{{ $fa->email }}</span>
                        <span class="text-[#717974]">({{ $fa->failure_reason ?? 'Failed' }})</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Sign-in Attempts Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <h2 class="text-sm font-semibold text-[#1b1c18]">Sign-in attempts</h2>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <form method="GET" action="{{ route('admin.sign-ins.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[280px]">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1.5">Email (a client's full address)</label>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div class="w-44">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1.5">Result</label>
                    <select name="result" class="w-full h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                        <option value="any">Any</option>
                        <option value="signed_in" {{ strtolower(request('result')) === 'signed_in' ? 'selected' : '' }}>Signed in</option>
                        <option value="failed" {{ strtolower(request('result')) === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="h-8 px-3.5 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors shadow-xs">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Attempts Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-2.5 px-4 font-normal">Time</th>
                        <th class="py-2.5 px-4 font-normal">Email</th>
                        <th class="py-2.5 px-4 font-normal">Result</th>
                        <th class="py-2.5 px-4 font-normal">IP address</th>
                        <th class="py-2.5 px-4 font-normal">Device</th>
                        <th class="py-2.5 px-4 font-normal">Firm</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($signIns as $attempt)
                    <tr class="hover:bg-[#fbf9f5] transition-colors">
                        <td class="py-3 px-4 text-[#5e625e] font-sans whitespace-nowrap">
                            {{ $attempt->created_at->format('M j, Y, g:i A') }} UTC
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-mono text-xs text-[#1b1c18] block">
                                {{ $attempt->email }}
                            </span>
                            @if($attempt->is_client)
                                <span class="text-[11px] text-[#23493a] font-medium block mt-0.5">
                                    Client portal user
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            @if($attempt->result === 'signed_in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#d7f0e5] text-[#0d5236] border border-[#bbf0d8]">
                                    Signed in
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                                    Failed
                                </span>
                                @if($attempt->failure_reason)
                                    <span class="text-[11px] text-[#717974] block mt-0.5">
                                        {{ $attempt->failure_reason }}
                                    </span>
                                @endif
                            @endif
                        </td>

                        <td class="py-3 px-4 font-mono text-xs text-[#1b1c18]">
                            {{ $attempt->ip_address ?? '—' }}
                        </td>

                        <td class="py-3 px-4 text-[#414844]">
                            {{ $attempt->device }}
                        </td>

                        <td class="py-3 px-4">
                            @if($attempt->firm)
                                <a href="{{ route('admin.firms.show', $attempt->firm) }}" class="text-[#1b1c18] hover:underline font-normal">
                                    {{ $attempt->firm->name }}
                                </a>
                            @else
                                <span class="text-[#8e8e8e]">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-[#5e625e]">
                            No sign-in attempts recorded matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($signIns->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white flex items-center justify-between text-xs text-[#5e625e]">
            <span>Page {{ $signIns->currentPage() }} · {{ $signIns->total() }} rows</span>
            <div class="flex gap-1">
                @if($signIns->onFirstPage())
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $signIns->previousPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Previous</a>
                @endif

                @if($signIns->hasMorePages())
                    <a href="{{ $signIns->nextPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Next</a>
                @else
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
