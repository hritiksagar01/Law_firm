@extends('layouts.admin')

@section('title', 'Matters — Platform Console')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div>
        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Matters</h1>
        <p class="text-[13px] text-[#646864] mt-1">
            Volume and activity across firms, for support and capacity planning
        </p>
    </div>

    <!-- Privacy & Isolation Notice Banner -->
    <div class="bg-[#f5f3ed] border border-[#e5e3dc] rounded-md p-4 text-[13px] text-[#414844] leading-relaxed">
        Platform staff see record types, counts, statuses and matter numbers. Client identities, matter titles, document and message content, and individual amounts stay with each firm.
    </div>

    <!-- Matters Directory Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        
        <!-- Filter Toolbar -->
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <form method="GET" action="{{ route('admin.matters.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1.5">Matter number</label>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="e.g. 2026-0118"
                           class="w-full h-9 px-3 rounded-md border border-[#c1c8c3] bg-white text-[13px] text-[#1b1c18] placeholder-[#9ca3af] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]">
                </div>

                <div class="w-64">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1.5">Firm</label>
                    <select name="firm_id" class="w-full h-9 px-3 rounded-md border border-[#c1c8c3] bg-white text-[13px] text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]">
                        <option value="all">All firms</option>
                        @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ request('firm_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-44">
                    <label class="block text-[11.5px] font-medium text-[#5e625e] mb-1.5">Status</label>
                    <select name="status" class="w-full h-9 px-3 rounded-md border border-[#c1c8c3] bg-white text-[13px] text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]">
                        <option value="any">Any</option>
                        <option value="intake" {{ strtolower(request('status')) === 'intake' ? 'selected' : '' }}>Intake</option>
                        <option value="open" {{ strtolower(request('status')) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ strtolower(request('status')) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="closed" {{ strtolower(request('status')) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="h-9 px-4 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-[13px] font-medium text-[#1b1c18] transition-colors shadow-xs">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Matters Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11.5px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-3 px-4 font-normal">Matter</th>
                        <th class="py-3 px-4 font-normal">Firm</th>
                        <th class="py-3 px-4 font-normal">Practice area</th>
                        <th class="py-3 px-4 font-normal">Status</th>
                        <th class="py-3 px-4 font-normal">Opened</th>
                        <th class="py-3 px-4 font-normal text-right">Docs</th>
                        <th class="py-3 px-4 font-normal text-right">Messages</th>
                        <th class="py-3 px-4 font-normal text-right">Last activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($matters as $matter)
                        @php
                            $st = strtolower($matter->status ?? 'open');
                        @endphp
                        <tr class="hover:bg-[#f4f2ed] transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[13px] font-medium text-[#1b1c18]">
                                    {{ $matter->case_number }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                @if($matter->firm)
                                    <a href="{{ route('admin.firms.show', $matter->firm) }}" class="text-[13px] text-[#1b1c18] hover:underline font-normal">
                                        {{ $matter->firm->name }}
                                    </a>
                                @else
                                    <span class="text-[13px] text-[#8e8e8e]">—</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-[13px] text-[#414844]">
                                {{ $matter->practice_area ?? 'General' }}
                            </td>

                            <td class="py-3.5 px-4">
                                @if($st === 'intake')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#e0e8f0] text-[#1e3a5f] border border-[#cbd5e1]">
                                        Intake
                                    </span>
                                @elseif($st === 'open' || $st === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#d7f0e5] text-[#0d5236] border border-[#bbf0d8]">
                                        Open
                                    </span>
                                @elseif($st === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#fef3c7] text-[#92400e] border border-[#fde68a]">
                                        Pending
                                    </span>
                                @elseif($st === 'closed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#ebe8e1] text-[#5b5f5b] border border-[#dedad0]">
                                        Closed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#f0eee8] text-[#5e625e]">
                                        {{ ucfirst($st) }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-[13px] text-[#1b1c18]">
                                <div>
                                    {{ $matter->opened_at ? $matter->opened_at->format('M j, Y') : $matter->created_at->format('M j, Y') }}
                                </div>
                                @if($st === 'closed' && $matter->closed_at)
                                    <div class="text-[11px] text-[#717974] mt-0.5">
                                        Closed {{ $matter->closed_at->format('M j, Y') }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono text-[13px] text-[#1b1c18]">
                                {{ $matter->documents_count ?? 0 }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono text-[13px] text-[#1b1c18]">
                                {{ $matter->messages_count ?? 0 }}
                            </td>

                            <td class="py-3.5 px-4 text-right text-[13px] text-[#5b5f5b]">
                                {{ $matter->updated_at ? $matter->updated_at->format('M j, Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-[#5e625e]">
                                No matters matching the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
