@extends('portal.layout')

@section('title', 'Invoices & Retainer Ledger')
@section('header_title', 'Invoices & Billing')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" x-data="{ payModalOpen: false, selectedInvoice: null }">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Invoices &amp; Retainer Account</h1>
            <p class="text-[13px] text-[#646864] mt-1">Itemized professional legal fee bills, GST tax invoices, and advance retainer ledger statements.</p>
        </div>
    </div>

    <!-- Financial Ledger Overview Connected Ribbon -->
    @php
        $totalInvoiced = $invoices->sum('total_amount');
        $totalPaid = $invoices->where('status', 'paid')->sum('total_amount');
        $totalOutstanding = $invoices->where('status', '!=', 'paid')->sum('total_amount');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs">
        <!-- Retainer Balance -->
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Available advance retainer</div>
            <div class="text-[28px] font-medium text-[#23493a] tracking-tight mt-1 font-mono">
                {{ config('legal.currency.symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}
            </div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Held securely in client advance trust account</div>
        </div>

        <!-- Total Invoiced -->
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Total fee bills issued</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 font-mono">
                {{ config('legal.currency.symbol', '₹') }}{{ number_format($totalInvoiced, 2) }}
            </div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">{{ $invoices->count() }} formal bills generated to date</div>
        </div>

        <!-- Outstanding Due -->
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Outstanding due balance</div>
            <div class="text-[28px] font-medium {{ $totalOutstanding > 0 ? 'text-[#ba1a1a]' : 'text-[#065f46]' }} tracking-tight mt-1 font-mono">
                {{ config('legal.currency.symbol', '₹') }}{{ number_format($totalOutstanding, 2) }}
            </div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">
                {{ $invoices->where('status', '!=', 'paid')->count() }} unpaid bill(s) &middot; 18% GST included
            </div>
        </div>
    </div>

    <!-- Invoices Ledger Table -->
    <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs overflow-hidden flex flex-col">
        <div class="p-4 border-b border-[#f0eee8] bg-[#faf9f5] flex items-center justify-between">
            <h2 class="text-sm font-semibold text-[#1a1a1a]">Itemized Fee Invoices</h2>
            <span class="font-mono text-xs text-[#646864]">GST Compliant Tax Invoices</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[#8a8a8a] font-mono text-[10.5px] uppercase bg-white">
                        <th class="py-3.5 px-4 font-medium">Invoice No.</th>
                        <th class="py-3.5 px-4 font-medium">Case Matter</th>
                        <th class="py-3.5 px-4 font-medium">Billing Period</th>
                        <th class="py-3.5 px-4 font-medium">Subtotal</th>
                        <th class="py-3.5 px-4 font-medium">18% GST</th>
                        <th class="py-3.5 px-4 font-medium">Total Amount</th>
                        <th class="py-3.5 px-4 font-medium">Status</th>
                        <th class="py-3.5 px-4 font-medium text-right">Settlement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-[#faf9f5]/70 transition-colors">
                        <td class="py-4 px-4 font-mono font-semibold text-[#23493a]">
                            {{ $inv->invoice_number }}
                        </td>

                        <td class="py-4 px-4 text-[#646864]">
                            <div class="flex flex-col">
                                <span class="font-medium text-[#1a1a1a] truncate max-w-[200px]">{{ $inv->matter->title ?? 'Legal Services' }}</span>
                                <span class="font-mono text-[10.5px] text-[#8a8a8a]">{{ $inv->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4 font-mono text-[11.5px] text-[#646864]">
                            <span>{{ \Carbon\Carbon::parse($inv->issue_date)->format('d M Y') }}</span>
                            <span class="block text-[10.5px] text-[#8a8a8a]">Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M') }}</span>
                        </td>

                        <td class="py-4 px-4 font-mono text-xs text-[#1a1a1a]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->subtotal, 2) }}
                        </td>

                        <td class="py-4 px-4 font-mono text-xs text-[#646864]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->tax_amount, 2) }}
                        </td>

                        <td class="py-4 px-4 font-mono text-xs font-semibold text-[#1a1a1a]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->total_amount, 2) }}
                        </td>

                        <td class="py-4 px-4">
                            @if($inv->status === 'paid')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                Settled
                            </span>
                            @elseif($inv->status === 'overdue')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#fef2f2] text-[#991b1b] border border-[#fecaca]">
                                Overdue
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#fefce8] text-[#854d0e] border border-[#fef08a]">
                                Outstanding
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($inv->status !== 'paid')
                            <button type="button" 
                                    @click="
                                        selectedInvoice = {
                                            id: {{ $inv->id }},
                                            number: '{{ $inv->invoice_number }}',
                                            amount: '{{ number_format($inv->total_amount, 2) }}',
                                            total: {{ $inv->total_amount }}
                                        };
                                        payModalOpen = true;
                                    "
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] transition-colors shadow-xs cursor-pointer">
                                <span>Settle Bill</span>
                            </button>
                            @else
                            <span class="text-xs text-[#065f46] flex items-center justify-end gap-1 font-medium">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>Receipt Issued</span>
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-[#8a8a8a]">
                            No fee bills issued for this client account.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Settlement Confirmation Modal -->
    <div x-show="payModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="payModalOpen = false"
             class="bg-white border border-[#e5e3dc] rounded-lg max-w-md w-full p-6 shadow-xl">
            
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0eee8]">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Settle Legal Fee Bill</h3>
                <button type="button" @click="payModalOpen = false" class="text-[#8e8e8e] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form :action="'{{ url('/portal/invoices') }}/' + selectedInvoice?.id + '/pay'" method="POST" class="mt-4 flex flex-col gap-4">
                @csrf
                <div class="p-3.5 rounded-md bg-[#faf9f5] border border-[#e5e3dc] flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#646864]">Invoice Number:</span>
                        <span class="font-mono font-bold text-[#1a1a1a]" x-text="selectedInvoice?.number"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#646864]">Total Amount Due:</span>
                        <span class="font-mono font-bold text-[#1a1a1a] text-sm">
                            {{ config('legal.currency.symbol', '₹') }}<span x-text="selectedInvoice?.amount"></span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-[#e5e3dc]">
                        <span class="text-[#646864]">Available Advance Retainer:</span>
                        <span class="font-mono font-bold text-[#23493a]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-[#1a1a1a]">Select Settlement Source</label>
                    <label class="flex items-center gap-2.5 p-3 rounded-md border border-[#e5e3dc] hover:border-[#23493a] bg-white cursor-pointer transition-colors">
                        <input type="radio" name="payment_method" value="retainer" checked class="text-[#23493a] focus:ring-0"/>
                        <div class="flex flex-col text-xs">
                            <span class="font-medium text-[#1a1a1a]">Apply from Advance Retainer Account</span>
                            <span class="text-[11px] text-[#8a8a8a]">Instant settlement; debits your client trust balance</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-md border border-[#e5e3dc] hover:border-[#23493a] bg-white cursor-pointer transition-colors">
                        <input type="radio" name="payment_method" value="bank_transfer" class="text-[#23493a] focus:ring-0"/>
                        <div class="flex flex-col text-xs">
                            <span class="font-medium text-[#1a1a1a]">Direct Bank NEFT / RTGS / Cheque</span>
                            <span class="text-[11px] text-[#8a8a8a]">Inform chambers accounts desk of settlement</span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#f0eee8]">
                    <button type="button" @click="payModalOpen = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span>Confirm Settlement</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
