@extends('layouts.app')

@section('title', 'Bank Activity & Reconciliation — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <a href="{{ route('reports.index') }}" class="hover:underline">Reports</a>
                <span>/</span>
                <span>Bank Activity &amp; Reconciliation</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Bank Activity &amp; Reconciliation Report</h1>
            <p class="text-xs text-[#646864] mt-0.5">Audit log of all funds movements, client settlements, retainer deposits, and operating disbursements.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print Audit Statement</span>
            </button>
            <a href="{{ route('billing.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">payments</span>
                <span>Billing &amp; Ledger</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <a href="{{ route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Overview</a>
        <a href="{{ route('reports.cases') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Case Progress &amp; Stages</a>
        <a href="{{ route('reports.hearings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Daily Cause List &amp; Hearings</a>
        <a href="{{ route('reports.clients') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Client Portfolio</a>
        <a href="{{ route('reports.workload') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#646864] hover:bg-white hover:text-[#1a1a1a] border border-transparent hover:border-[#e5e3dc] transition-all">Advocate Workload</a>
        <a href="{{ route('reports.bank-activity') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium bg-[#23493a] text-white shadow-xs">Bank Activity &amp; Reconciliation</a>
    </div>

    <!-- Connected KPI Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-3 bg-white rounded-xl border border-[#e5e3dc] shadow-sm divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] overflow-hidden">
        <div class="p-5">
            <div class="flex items-center justify-between text-xs text-[#646864] mb-2 font-mono uppercase">
                <span>Total Funds Inflow</span>
                <span class="material-symbols-outlined text-base text-emerald-700">trending_up</span>
            </div>
            <div class="text-2xl font-bold text-emerald-700 tracking-tight">₹{{ number_format($totalInflow, 2) }}</div>
            <div class="text-[11px] text-[#646864] mt-1">Client settlements &amp; advance deposits</div>
        </div>

        <div class="p-5">
            <div class="flex items-center justify-between text-xs text-[#646864] mb-2 font-mono uppercase">
                <span>Total Disbursements &amp; Expenses</span>
                <span class="material-symbols-outlined text-base text-rose-700">trending_down</span>
            </div>
            <div class="text-2xl font-bold text-rose-700 tracking-tight">₹{{ number_format($totalOutflow, 2) }}</div>
            <div class="text-[11px] text-[#646864] mt-1">Court fees, registry filings, &amp; case overhead</div>
        </div>

        <div class="p-5">
            <div class="flex items-center justify-between text-xs text-[#646864] mb-2 font-mono uppercase">
                <span>Net Chambers Operating Balance</span>
                <span class="material-symbols-outlined text-base text-[#23493a]">account_balance</span>
            </div>
            <div class="text-2xl font-bold text-[#1a1a1a] tracking-tight">₹{{ number_format($netOperatingBalance, 2) }}</div>
            <div class="text-[11px] text-[#646864] mt-1">Cumulative operational cash balance</div>
        </div>
    </div>

    <!-- Linked Firm Bank Accounts -->
    <div class="p-5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm">
        <div class="flex items-center justify-between mb-3 border-b border-[#e5e3dc] pb-2">
            <h3 class="font-bold text-sm text-[#1a1a1a]">Linked Bank Accounts for Chambers Operations</h3>
            <span class="font-mono text-xs text-[#646864]">{{ $bankAccounts->count() }} Registered Accounts</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($bankAccounts as $bank)
            <div class="p-4 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] flex items-center justify-between">
                <div>
                    <span class="font-semibold text-xs text-[#1a1a1a] block">{{ $bank->bank_name }}</span>
                    <span class="text-[11px] text-[#646864] block font-mono">A/C: {{ $bank->account_number }} ({{ $bank->ifsc_code ?? 'IFSC' }})</span>
                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-white text-[#23493a] border border-[#e5e3dc] mt-1 inline-block font-semibold">
                        {{ $bank->account_type }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-[#646864] font-mono block">Current Balance</span>
                    <span class="font-bold text-sm text-[#1a1a1a]">₹{{ number_format($bank->current_balance, 2) }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-4 text-center text-xs text-[#646864]">
                No primary bank accounts registered. Default operations account active.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Financial Transactions Audit Trail -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
        <div class="p-3.5 border-b border-[#e5e3dc] bg-[#faf8f5] flex items-center justify-between">
            <h3 class="font-bold text-sm text-[#1a1a1a]">Bank &amp; Ledger Reconciliation Log</h3>
            <span class="font-mono text-xs text-[#23493a] font-semibold">{{ $transactions->count() }} Audited Entries</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Reference / UTR</th>
                        <th class="py-3 px-4">Client / Party</th>
                        <th class="py-3 px-4">Matter Context</th>
                        <th class="py-3 px-4">Classification</th>
                        <th class="py-3 px-4">Channel</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono text-[#646864]">{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#23493a]">{{ $txn->reference_number }}</td>
                        <td class="py-3.5 px-4 font-medium text-[#1a1a1a]">{{ $txn->client->name ?? 'Direct Counterparty' }}</td>
                        <td class="py-3.5 px-4 text-[#646864]">{{ $txn->matter->case_number ?? 'General Chambers' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium uppercase font-mono {{ $txn->type === 'payment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ str_replace('_', ' ', $txn->type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] uppercase text-[#646864]">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">₹{{ number_format($txn->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-[#646864]">No bank activities logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
