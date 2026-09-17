@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Bank Activity &amp; Reconciliation</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Bank Activity &amp; Reconciliation Report</h1>
            <p class="text-sm text-[#766A5E] mt-1">Audit log of all funds movements, client settlements, retainer deposits, and operating disbursements.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Audit Statement</span>
            </button>
            <a href="{{ route('billing.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">payments</span>
                <span>Billing &amp; Ledger</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#554D45] hover:bg-white hover:text-[#9F8349] border border-transparent hover:border-[#EFECE6] transition-all">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#9F8349] text-white shadow-xs">Bank Activity &amp; Reconciliation</a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Total Funds Inflow</span>
                <span class="material-symbols-outlined text-base text-emerald-700">trending_up</span>
            </div>
            <div class="text-3xl font-serif font-bold text-emerald-700">₹{{ number_format($totalInflow, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Client settlements &amp; advance deposits</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Total Disbursements &amp; Expenses</span>
                <span class="material-symbols-outlined text-base text-rose-700">trending_down</span>
            </div>
            <div class="text-3xl font-serif font-bold text-rose-700">₹{{ number_format($totalOutflow, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Court fees, registry filings, &amp; case overhead</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Net Chambers Operating Balance</span>
                <span class="material-symbols-outlined text-base text-[#9F8349]">account_balance</span>
            </div>
            <div class="text-3xl font-serif font-bold text-[#222222]">₹{{ number_format($netOperatingBalance, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Cumulative operational cash balance</div>
        </div>
    </div>

    <!-- Linked Firm Bank Accounts -->
    <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
        <div class="flex items-center justify-between mb-3 border-b border-[#FAF8F5] pb-2">
            <h3 class="font-serif font-semibold text-base text-[#222222]">Linked Bank Accounts for Chambers Operations</h3>
            <span class="font-mono text-xs text-[#766A5E]">{{ $bankAccounts->count() }} Registered Accounts</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($bankAccounts as $bank)
            <div class="p-4 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-between">
                <div>
                    <span class="font-bold text-xs text-[#222222] block">{{ $bank->bank_name }}</span>
                    <span class="text-[11px] text-[#766A5E] block font-mono">A/C: {{ $bank->account_number }} ({{ $bank->ifsc_code ?? 'IFSC' }})</span>
                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-white text-[#9F8349] border border-[#EAE4DC] mt-1 inline-block">
                        {{ $bank->account_type }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-[#766A5E] font-mono block">Current Balance</span>
                    <span class="font-serif font-bold text-base text-[#222222]">₹{{ number_format($bank->current_balance, 2) }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-4 text-center text-xs text-[#766A5E]">
                No primary bank accounts registered. Default operations account active.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Financial Transactions Audit Trail -->
    <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
            <h3 class="font-serif font-semibold text-base text-[#222222]">Bank &amp; Ledger Reconciliation Log</h3>
            <span class="font-mono text-xs text-[#766A5E]">{{ $transactions->count() }} Audited Entries</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Reference / UTR</th>
                        <th class="py-3 px-4 font-semibold">Client / Party</th>
                        <th class="py-3 px-4 font-semibold">Matter Context</th>
                        <th class="py-3 px-4 font-semibold">Classification</th>
                        <th class="py-3 px-4 font-semibold">Channel</th>
                        <th class="py-3 px-4 text-right font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono">{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#9F8349]">{{ $txn->reference_number }}</td>
                        <td class="py-3.5 px-4 font-medium text-[#222222]">{{ $txn->client->name ?? 'Direct Counterparty' }}</td>
                        <td class="py-3.5 px-4 text-[#554D45]">{{ $txn->matter->case_number ?? 'General Chambers' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase font-mono {{ $txn->type === 'payment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ str_replace('_', ' ', $txn->type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] uppercase text-[#766A5E]">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">₹{{ number_format($txn->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-[#766A5E]">No bank activities logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
