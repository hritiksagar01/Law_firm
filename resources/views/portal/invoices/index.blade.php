@extends('portal.layout')

@section('title', 'Invoices & Retainer Ledger')

@section('content')
<div class="flex flex-col gap-8" x-data="{ payModalOpen: false, selectedInvoice: null }">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Invoices &amp; Retainer Account</h1>
            <p class="text-xs text-[#727973] mt-1">Itemized professional legal fee bills, GST tax invoices, and advance retainer ledger statements.</p>
        </div>
    </div>

    <!-- Financial Ledger Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Retainer Balance -->
        <div class="bg-gradient-to-br from-[#1a3c2a] to-[#022616] text-white rounded-2xl p-6 shadow-md flex flex-col justify-between">
            <span class="text-[11px] font-mono uppercase tracking-wider text-white/70 font-semibold">Available Advance Retainer</span>
            <div class="my-2">
                <span class="text-3xl font-serif font-bold text-[#fed977]">{{ config('legal.currency.symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}</span>
                <span class="block text-[11px] text-white/70 mt-1">Held securely in client advance account</span>
            </div>
            <span class="text-[10px] font-mono text-emerald-300">Can be applied to settle pending fee bills</span>
        </div>

        <!-- Total Invoiced -->
        @php
            $totalInvoiced = $invoices->sum('total_amount');
            $totalPaid = $invoices->where('status', 'paid')->sum('total_amount');
            $totalOutstanding = $invoices->where('status', '!=', 'paid')->sum('total_amount');
        @endphp
        <div class="bg-white rounded-2xl p-6 border border-[#e9e8e5] shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#727973] font-semibold">Total Fee Bills Issued</span>
            <div class="my-2">
                <span class="text-3xl font-serif font-bold text-[#1a1c1a]">{{ config('legal.currency.symbol', '₹') }}{{ number_format($totalInvoiced, 2) }}</span>
                <span class="block text-[11px] text-[#727973] mt-1">{{ $invoices->count() }} formal bills generated to date</span>
            </div>
            <span class="text-[10px] font-mono text-emerald-800">{{ config('legal.currency.symbol', '₹') }}{{ number_format($totalPaid, 2) }} Settled</span>
        </div>

        <!-- Outstanding Due -->
        <div class="bg-white rounded-2xl p-6 border border-[#e9e8e5] shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-mono uppercase tracking-wider text-[#727973] font-semibold">Outstanding Due Balance</span>
            <div class="my-2">
                <span class="text-3xl font-serif font-bold {{ $totalOutstanding > 0 ? 'text-amber-600' : 'text-emerald-700' }}">
                    {{ config('legal.currency.symbol', '₹') }}{{ number_format($totalOutstanding, 2) }}
                </span>
                <span class="block text-[11px] text-[#727973] mt-1">
                    {{ $invoices->where('status', '!=', 'paid')->count() }} unpaid bill(s)
                </span>
            </div>
            <span class="text-[10px] font-mono text-[#727973]">18% GST included</span>
        </div>
    </div>

    <!-- Invoices Ledger Table -->
    <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 border-b border-[#e9e8e5] bg-[#faf9f6] flex items-center justify-between">
            <h2 class="text-sm font-bold text-[#1a1c1a]">Itemized Fee Invoices</h2>
            <span class="font-mono text-xs text-[#727973]">GST Compliant Tax Invoices</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#e9e8e5] text-[#727973] font-mono text-[10px] uppercase bg-white">
                        <th class="py-3.5 px-4 font-semibold">Invoice No.</th>
                        <th class="py-3.5 px-4 font-semibold">Case Matter</th>
                        <th class="py-3.5 px-4 font-semibold">Billing Period</th>
                        <th class="py-3.5 px-4 font-semibold">Subtotal</th>
                        <th class="py-3.5 px-4 font-semibold">18% GST</th>
                        <th class="py-3.5 px-4 font-semibold">Total Amount</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Settlement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f4f3f1]">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-[#faf9f6]/60 transition-colors">
                        <td class="py-4 px-4 font-mono font-bold text-[#1a3c2a]">
                            {{ $inv->invoice_number }}
                        </td>

                        <td class="py-4 px-4 text-[#424843]">
                            <div class="flex flex-col">
                                <span class="font-semibold text-[#1a1c1a] truncate max-w-[200px]">{{ $inv->matter->title ?? 'Legal Services' }}</span>
                                <span class="font-mono text-[10px] text-[#727973]">{{ $inv->matter->case_number ?? '' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-4 font-mono text-[11px] text-[#727973]">
                            <span>{{ \Carbon\Carbon::parse($inv->issue_date)->format('d M Y') }}</span>
                            <span class="block text-[10px] text-[#82a78f]">Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M') }}</span>
                        </td>

                        <td class="py-4 px-4 font-mono text-xs text-[#1a1c1a]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->subtotal, 2) }}
                        </td>

                        <td class="py-4 px-4 font-mono text-xs text-[#727973]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->tax_amount, 2) }}
                        </td>

                        <td class="py-4 px-4 font-mono font-bold text-sm text-[#1a1c1a]">
                            {{ config('legal.currency.symbol', '₹') }}{{ number_format($inv->total_amount, 2) }}
                        </td>

                        <td class="py-4 px-4">
                            @if($inv->status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="material-symbols-outlined text-xs text-emerald-600">check_circle</span>
                                <span>Paid in Full</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Pending Payment</span>
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($inv->status !== 'paid')
                            <button type="button" @click="selectedInvoice = {{ json_encode($inv) }}; payModalOpen = true" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-sm">payments</span>
                                <span>Pay Invoice</span>
                            </button>
                            @else
                            <span class="font-mono text-[11px] text-emerald-700 flex items-center justify-end gap-1">
                                <span class="material-symbols-outlined text-sm">verified</span>
                                <span>Settled</span>
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-[#727973]">
                            <p class="text-xs">No invoices generated for this client account yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Settle / Pay Invoice Modal (Simulated Payment / Retainer Deduction) -->
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="payModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[#e9e8e5] flex flex-col gap-5 relative">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-[#1a3c2a] font-semibold">Invoice Settlement</span>
                    <h3 class="text-base font-bold text-[#1a1c1a] mt-0.5" x-text="selectedInvoice ? 'Pay ' + selectedInvoice.invoice_number : 'Pay Invoice'"></h3>
                </div>
                <button type="button" @click="payModalOpen = false" class="text-[#727973] hover:text-[#1a1c1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <div class="p-4 rounded-xl bg-[#faf9f6] border border-[#f0eee9] flex flex-col gap-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-[#727973]">Subtotal:</span>
                    <span class="font-mono font-semibold" x-text="selectedInvoice ? '{{ config('legal.currency.symbol', '₹') }}' + parseFloat(selectedInvoice.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2}) : ''"></span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-[#727973]">18% GST Tax:</span>
                    <span class="font-mono font-semibold" x-text="selectedInvoice ? '{{ config('legal.currency.symbol', '₹') }}' + parseFloat(selectedInvoice.tax_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) : ''"></span>
                </div>
                <div class="h-px bg-[#e9e8e5] my-1"></div>
                <div class="flex items-center justify-between text-sm">
                    <span class="font-bold text-[#1a1c1a]">Total Due:</span>
                    <span class="font-mono font-bold text-base text-[#1a3c2a]" x-text="selectedInvoice ? '{{ config('legal.currency.symbol', '₹') }}' + parseFloat(selectedInvoice.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) : ''"></span>
                </div>
            </div>

            <!-- Payment Options -->
            <form :action="'/portal/invoices/' + (selectedInvoice ? selectedInvoice.id : 0) + '/pay'" method="POST" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-2.5">
                    <span class="text-xs font-semibold text-[#1a1c1a]">Choose Settlement Method:</span>

                    <!-- Option 1: Retainer Account Deduction -->
                    <label class="p-3.5 rounded-xl border border-[#c5ecd2] bg-[#f0f9f4] flex items-start gap-3 cursor-pointer hover:border-[#1a3c2a] transition-all">
                        <input type="radio" name="payment_method" value="retainer" checked class="mt-0.5 text-[#1a3c2a] accent-[#1a3c2a]"/>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-[#002112]">Settle from Advance Retainer</span>
                            <span class="text-[11px] text-[#2c4e3a] mt-0.5">
                                Current Retainer: {{ config('legal.currency.symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}
                            </span>
                        </div>
                    </label>

                    <!-- Option 2: UPI / Net Banking -->
                    <label class="p-3.5 rounded-xl border border-[#e9e8e5] bg-white flex items-start gap-3 cursor-pointer hover:border-[#1a3c2a] transition-all">
                        <input type="radio" name="payment_method" value="gateway" class="mt-0.5 text-[#1a3c2a] accent-[#1a3c2a]"/>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-[#1a1c1a]">Instant UPI / Corporate NetBanking</span>
                            <span class="text-[11px] text-[#727973] mt-0.5">Simulate instant bank wire or GST invoice clearance</span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f4f3f1]">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-[#727973] hover:bg-[#f4f3f1]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">lock</span>
                        <span>Authorize &amp; Pay</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
