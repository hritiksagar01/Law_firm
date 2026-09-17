@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    activeTab: '{{ $tab ?? 'invoices' }}',
    logTimeModal: false,
    logExpenseModal: false,
    generateInvoiceModal: false,
    settleModal: false,
    recordTxnModal: false,
    selectedInvoice: null
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span class="material-symbols-outlined text-sm">payments</span>
                <span>Financial Management &amp; Invoicing</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Billing, Accounts &amp; Ledger</h1>
            <p class="text-sm text-[#766A5E] mt-1">Track billable advocacy hours, case expenses, automated client invoices, and transactional ledgers.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="generateInvoiceModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">receipt_long</span>
                <span>Generate Invoice</span>
            </button>
            <button @click="logTimeModal = true" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">timer</span>
                <span>Log Time</span>
            </button>
            <button @click="logExpenseModal = true" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">attach_money</span>
                <span>Log Expense</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Unbilled WIP Hours</span>
                <span class="material-symbols-outlined text-base text-[#9F8349]">timer</span>
            </div>
            <div class="text-2xl font-serif font-bold text-[#222222]">₹{{ number_format($unbilledHoursAmount, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Ready for invoice aggregation</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Unbilled Case Expenses</span>
                <span class="material-symbols-outlined text-base text-amber-700">receipt</span>
            </div>
            <div class="text-2xl font-serif font-bold text-[#222222]">₹{{ number_format($unbilledExpensesAmount, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Court fees, travel &amp; filing disbursements</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Outstanding Invoices</span>
                <span class="material-symbols-outlined text-base text-rose-700">pending_actions</span>
            </div>
            <div class="text-2xl font-serif font-bold text-[#8B263E]">₹{{ number_format($outstandingInvoicesAmount, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Awaiting client payment settlement</div>
        </div>

        <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
            <div class="flex items-center justify-between text-xs text-[#766A5E] mb-2 font-mono uppercase">
                <span>Collections Realized</span>
                <span class="material-symbols-outlined text-base text-emerald-700">account_balance_wallet</span>
            </div>
            <div class="text-2xl font-serif font-bold text-emerald-700">₹{{ number_format($totalCollected, 2) }}</div>
            <div class="text-[11px] text-[#766A5E] mt-1">Total payments received in ledger</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">receipt</span>
            <span>Client Invoices ({{ $invoices->count() }})</span>
        </button>
        <button @click="activeTab = 'time-entries'" :class="activeTab === 'time-entries' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">schedule</span>
            <span>Billable Hours ({{ $timeEntries->count() }})</span>
        </button>
        <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">paid</span>
            <span>Case Expenses ({{ $expenses->count() }})</span>
        </button>
        <button @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">menu_book</span>
            <span>Chambers Ledger &amp; Transactions ({{ $transactions->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: INVOICES -->
    <div x-show="activeTab === 'invoices'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Invoice Number</th>
                        <th class="py-3 px-4 font-semibold">Client / Debtor</th>
                        <th class="py-3 px-4 font-semibold">Case Matter</th>
                        <th class="py-3 px-4 font-semibold">Issue / Due Date</th>
                        <th class="py-3 px-4 font-semibold text-right">Total Amount</th>
                        <th class="py-3 px-4 font-semibold text-right">Amount Settled</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($invoices as $inv)
                    @php
                        $balance = $inv->total_amount - $inv->amount_paid;
                    @endphp
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#9F8349]">{{ $inv->invoice_number }}</td>
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $inv->client->name ?? 'General Retainer' }}</td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            @if($inv->matter)
                            <a href="{{ route('matters.show', $inv->matter->id) }}" class="text-[#9F8349] hover:underline font-medium">
                                {{ $inv->matter->case_number }}: {{ $inv->matter->title }}
                            </a>
                            @else
                            <span class="text-[#766A5E]">Chambers Retainer</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#766A5E]">
                            <div>Issued: {{ \Carbon\Carbon::parse($inv->issue_date)->format('d M Y') }}</div>
                            <div class="text-[#222222]">Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-[#222222]">₹{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-700">₹{{ number_format($inv->amount_paid, 2) }}</td>
                        <td class="py-3.5 px-4">
                            @if($inv->status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid in Full</span>
                            @elseif($inv->status === 'sent')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Dispatched (Unpaid)</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">Overdue</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($inv->status !== 'paid')
                            <button @click="selectedInvoice = { id: {{ $inv->id }}, number: '{{ $inv->invoice_number }}', balance: {{ $balance }} }; settleModal = true" class="px-2.5 py-1 rounded bg-[#FAF8F5] border border-[#EAE4DC] text-[#9F8349] font-semibold hover:bg-[#9F8349] hover:text-white transition-colors">
                                Settle Payment
                            </button>
                            @else
                            <span class="text-[11px] font-mono text-emerald-700 font-bold">Settled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#766A5E]">No client invoices generated. Click "Generate Invoice" above to create one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: TIME ENTRIES -->
    <div x-show="activeTab === 'time-entries'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Advocate</th>
                        <th class="py-3 px-4 font-semibold">Case Matter</th>
                        <th class="py-3 px-4 font-semibold">LEDES Task</th>
                        <th class="py-3 px-4 font-semibold">Hours</th>
                        <th class="py-3 px-4 font-semibold text-right">Hourly Rate</th>
                        <th class="py-3 px-4 font-semibold text-right">Total Fee</th>
                        <th class="py-3 px-4 font-semibold">Billing Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($timeEntries as $te)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#222222]">{{ \Carbon\Carbon::parse($te->entry_date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $te->user->name ?? 'Advocate' }}</td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            @if($te->matter)
                            <a href="{{ route('matters.show', $te->matter->id) }}" class="text-[#9F8349] hover:underline">
                                {{ $te->matter->case_number }}: {{ $te->matter->title }}
                            </a>
                            @else
                            General Chambers Practice
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] font-bold text-[#9F8349]">{{ $te->activity_code }}</span>
                            <span class="text-[#554D45] block text-[11px]">{{ $te->activity_name }}</span>
                            @if($te->narrative)
                            <span class="text-[10px] text-[#766A5E] block italic">{{ $te->narrative }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold">{{ $te->hours }} hrs</td>
                        <td class="py-3.5 px-4 text-right font-mono text-[#766A5E]">₹{{ number_format($te->rate, 2) }}/hr</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-[#222222]">₹{{ number_format($te->total_amount, 2) }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold {{ $te->status === 'billed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ ucfirst($te->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#766A5E]">No billable hours recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: EXPENSES -->
    <div x-show="activeTab === 'expenses'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Disbursement Title</th>
                        <th class="py-3 px-4 font-semibold">Category</th>
                        <th class="py-3 px-4 font-semibold">Case Matter</th>
                        <th class="py-3 px-4 font-semibold">Incurred By</th>
                        <th class="py-3 px-4 font-semibold text-right">Amount</th>
                        <th class="py-3 px-4 font-semibold">Billable</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#222222]">{{ \Carbon\Carbon::parse($exp->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-[#222222]">{{ $exp->title }}</div>
                            @if($exp->description)
                            <div class="text-[10px] text-[#766A5E]">{{ $exp->description }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono bg-[#FAF8F5] text-[#554D45] border border-[#EAE4DC]">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">
                            @if($exp->matter)
                            {{ $exp->matter->case_number }}: {{ $exp->matter->title }}
                            @else
                            General Chambers
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">{{ $exp->user->name ?? 'Counsel' }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-[#222222]">₹{{ number_format($exp->amount, 2) }}</td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] font-bold {{ $exp->is_billable ? 'text-[#9F8349]' : 'text-[#766A5E]' }}">
                                {{ $exp->is_billable ? 'Billable to Client' : 'Firm Overhead' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold {{ $exp->status === 'billed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                {{ ucfirst($exp->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#766A5E]">No case expenses logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: CHAMBERS LEDGER -->
    <div x-show="activeTab === 'ledger'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs text-[#766A5E]">Chronological audit trail of all financial inflows, payments, and client retainer deposits.</span>
            <button @click="recordTxnModal = true" class="px-3 py-1.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36]">
                Record Advance / Retainer
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Transaction Date</th>
                        <th class="py-3 px-4 font-semibold">Reference #</th>
                        <th class="py-3 px-4 font-semibold">Client / Party</th>
                        <th class="py-3 px-4 font-semibold">Matter Attribution</th>
                        <th class="py-3 px-4 font-semibold">Type</th>
                        <th class="py-3 px-4 font-semibold">Payment Channel</th>
                        <th class="py-3 px-4 font-semibold text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#222222]">{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#9F8349] font-bold">{{ $txn->reference_number }}</td>
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $txn->client->name ?? 'General Party' }}</td>
                        <td class="py-3.5 px-4 text-[#554D45]">{{ $txn->matter->case_number ?? 'Chambers General' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase font-mono {{ $txn->type === 'payment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ str_replace('_', ' ', $txn->type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#554D45] uppercase">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">+₹{{ number_format($txn->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-[#766A5E]">No ledger transactions registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: LOG TIME -->
    <div x-show="logTimeModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="logTimeModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Log Billable Advocacy Hours</h3>
                <button @click="logTimeModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.time-entries.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Matter / Case File</label>
                    <select name="matter_id" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Hours Spent</label>
                        <input type="number" step="0.1" min="0.1" name="hours" value="1.5" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Entry Date</label>
                        <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">LEDES Task Code</label>
                    <select name="activity_code" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono">
                        <option value="L120">L120 - Analysis &amp; Case Strategy</option>
                        <option value="L110">L110 - Fact Investigation &amp; Client Conference</option>
                        <option value="L330">L330 - Depositions &amp; Oral Arguments</option>
                        <option value="A104">A104 - Document &amp; Evidence Review</option>
                        <option value="B110">B110 - Written Pleadings &amp; Notice Drafting</option>
                        <option value="L240">L240 - Court Registry Filing &amp; Compliance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Narrative of Services Rendered</label>
                    <textarea name="narrative" rows="3" placeholder="Detailed professional description..." class="w-full p-2.5 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_billable" name="is_billable" checked class="rounded border-[#EAE4DC] text-[#9F8349] focus:ring-[#9F8349]"/>
                    <label for="is_billable" class="text-xs text-[#222222] font-medium">Billable to Client Account</label>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="logTimeModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Log Time</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: LOG EXPENSE -->
    <div x-show="logExpenseModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="logExpenseModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Log Case-Related Expense</h3>
                <button @click="logExpenseModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Matter / Case File</label>
                    <select name="matter_id" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        <option value="">General Chambers Overhead</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Expense Title</label>
                        <input type="text" name="title" required placeholder="e.g. Court Fee Stamp" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Category</label>
                        <select name="category" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                            <option value="Court Fees">Court Fees &amp; Stamps</option>
                            <option value="Travel & Conveyance">Travel &amp; Conveyance</option>
                            <option value="Administrative">Administrative &amp; Printing</option>
                            <option value="Filing & Registry">Filing &amp; Registry</option>
                            <option value="Process Service">Process Service / Summons</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Amount (₹)</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 2500" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Date Incurred</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Attach Receipt / Voucher (Optional)</label>
                    <input type="file" name="receipt" class="w-full text-xs text-[#766A5E] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#FAF8F5] file:text-[#9F8349] hover:file:bg-[#F8F4EE]"/>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="exp_billable" name="is_billable" checked class="rounded border-[#EAE4DC] text-[#9F8349] focus:ring-[#9F8349]"/>
                    <label for="exp_billable" class="text-xs text-[#222222] font-medium">Reimbursable from Client</label>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="logExpenseModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Log Expense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: GENERATE INVOICE -->
    <div x-show="generateInvoiceModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="generateInvoiceModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Automated Invoice Generation</h3>
                <button @click="generateInvoiceModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.invoices.generate') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Select Matter</label>
                    <select name="matter_id" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }} ({{ $m->client->name ?? 'Client' }})</option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-[#766A5E] block mt-1">All unbilled hours and case disbursements will be automatically compiled into an official invoice.</span>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">GST / Tax Rate (%)</label>
                    <input type="number" step="0.1" name="tax_rate" value="18.0" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="generateInvoiceModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Generate &amp; Dispatch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: SETTLE INVOICE -->
    <div x-show="settleModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="settleModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Settle Invoice Payment</h3>
                <button @click="settleModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form :action="'/billing/invoices/' + (selectedInvoice ? selectedInvoice.id : '') + '/settle'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="p-3 bg-[#FAF8F5] rounded-lg border border-[#EAE4DC]">
                    <div class="text-[#766A5E]">Settling Invoice: <strong class="text-[#222222]" x-text="selectedInvoice ? selectedInvoice.number : ''"></strong></div>
                    <div class="text-[#766A5E] mt-0.5">Remaining Balance: <strong class="text-emerald-700 font-mono" x-text="selectedInvoice ? ('₹' + selectedInvoice.balance.toFixed(2)) : ''"></strong></div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Settlement Amount (₹)</label>
                    <input type="number" step="0.01" min="1" name="amount" :value="selectedInvoice ? selectedInvoice.balance : 0" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono">
                        <option value="bank_transfer">Bank Wire / NEFT / RTGS</option>
                        <option value="cheque">Cheque / Demand Draft</option>
                        <option value="upi">UPI / Online Gateway</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="cash">Chambers Cash Receipt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">UTR / Cheque / Auth Reference #</label>
                    <input type="text" name="reference_number" placeholder="e.g. UTR-2026-981240" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="settleModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-700 text-white font-semibold hover:bg-emerald-800">Confirm Settlement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: RECORD TRANSACTION -->
    <div x-show="recordTxnModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="recordTxnModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Record Client Advance / Retainer</h3>
                <button @click="recordTxnModal = false" class="text-[#766A5E] hover:text-[#222222]"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.transactions.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Client Name</label>
                    <select name="client_id" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Matter Attribution (Optional)</label>
                    <select name="matter_id" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        <option value="">General Client Advance</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Deposit Amount (₹)</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="50000" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Transaction Nature</label>
                    <select name="type" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono">
                        <option value="advance_deposit">Client Advance Retainer Deposit</option>
                        <option value="payment">Direct Fee Settlement</option>
                        <option value="expense_reimbursement">Disbursement Reimbursement</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono">
                        <option value="bank_transfer">Bank Wire / NEFT / RTGS</option>
                        <option value="cheque">Cheque / Demand Draft</option>
                        <option value="upi">UPI / Online Gateway</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="UTR or Cheque number" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="recordTxnModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Credit to Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
