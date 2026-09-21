@extends('layouts.app')

@section('title', 'Billing, Accounts & Ledger — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Billing & Accounts')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]" x-data="{ 
    activeTab: '{{ $tab ?? 'invoices' }}',
    logTimeModal: false,
    logExpenseModal: false,
    generateInvoiceModal: false,
    settleModal: false,
    recordTxnModal: false,
    selectedInvoice: null
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Billing, Accounts &amp; Ledger</h1>
            <p class="text-[13px] text-[#646864] mt-1">Track billable advocacy hours, case expenses, automated client invoices, and transactional ledgers.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="generateInvoiceModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">receipt_long</span>
                <span>Generate Invoice</span>
            </button>
            <button @click="logTimeModal = true" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">timer</span>
                <span>Log Time</span>
            </button>
            <button @click="logExpenseModal = true" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-base">attach_money</span>
                <span>Log Expense</span>
            </button>
        </div>
    </div>

    <!-- Top 4 Connected Metric Ribbon (Super Admin Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border border-[#e5e3dc] bg-white rounded-md divide-y sm:divide-y-0 sm:divide-x divide-[#e5e3dc] shadow-xs mb-8">
        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Unbilled WIP Hours</div>
            <div class="text-[28px] font-medium text-[#1a1a1a] tracking-tight mt-1 font-mono">₹{{ number_format($unbilledHoursAmount, 2) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Ready for invoice aggregation</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Unbilled Case Expenses</div>
            <div class="text-[28px] font-medium text-[#d97706] tracking-tight mt-1 font-mono">₹{{ number_format($unbilledExpensesAmount, 2) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Court fees, travel &amp; disbursements</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Outstanding Invoices</div>
            <div class="text-[28px] font-medium text-[#ba1a1a] tracking-tight mt-1 font-mono">₹{{ number_format($outstandingInvoicesAmount, 2) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Awaiting client payment settlement</div>
        </div>

        <div class="p-5">
            <div class="text-[12.5px] text-[#646864]">Collections Realized</div>
            <div class="text-[28px] font-medium text-[#065f46] tracking-tight mt-1 font-mono">₹{{ number_format($totalCollected, 2) }}</div>
            <div class="text-[12px] text-[#8a8a8a] mt-1">Total payments received in ledger</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-[#f0eee8] pb-3 mb-6 overflow-x-auto">
        <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a]'" class="px-3.5 py-1.5 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">receipt</span>
            <span>Client Invoices ({{ $invoices->count() }})</span>
        </button>
        <button @click="activeTab = 'time-entries'" :class="activeTab === 'time-entries' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a]'" class="px-3.5 py-1.5 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">schedule</span>
            <span>Billable Hours ({{ $timeEntries->count() }})</span>
        </button>
        <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a]'" class="px-3.5 py-1.5 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">paid</span>
            <span>Case Expenses ({{ $expenses->count() }})</span>
        </button>
        <button @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a]'" class="px-3.5 py-1.5 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">menu_book</span>
            <span>Chambers Ledger ({{ $transactions->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: INVOICES -->
    <div x-show="activeTab === 'invoices'" x-cloak class="space-y-4">
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[#8a8a8a] font-medium uppercase text-[11.5px] tracking-wider">
                        <th class="py-3 px-4 font-medium">Invoice Number</th>
                        <th class="py-3 px-4 font-medium">Client / Debtor</th>
                        <th class="py-3 px-4 font-medium">Case Matter</th>
                        <th class="py-3 px-4 font-medium">Issue / Due Date</th>
                        <th class="py-3 px-4 font-medium text-right">Total Amount</th>
                        <th class="py-3 px-4 font-medium text-right">Amount Settled</th>
                        <th class="py-3 px-4 font-medium">Status</th>
                        <th class="py-3 px-4 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($invoices as $inv)
                    @php
                        $balance = $inv->total_amount - $inv->amount_paid;
                    @endphp
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-[#23493a]">{{ $inv->invoice_number }}</td>
                        <td class="py-3.5 px-4 font-medium text-[#1a1a1a]">{{ $inv->client->name ?? 'General Retainer' }}</td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            @if($inv->matter)
                            <a href="{{ route('matters.show', $inv->matter->id) }}" class="text-[#23493a] hover:underline font-medium">
                                {{ $inv->matter->case_number }}: {{ $inv->matter->title }}
                            </a>
                            @else
                            <span class="text-[#8a8a8a]">Chambers Retainer</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#8a8a8a]">
                            <div>Issued: {{ \Carbon\Carbon::parse($inv->issue_date)->format('d M Y') }}</div>
                            <div class="text-[#1a1a1a]">Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-semibold text-[#1a1a1a]">₹{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-medium text-[#065f46]">₹{{ number_format($inv->amount_paid, 2) }}</td>
                        <td class="py-3.5 px-4">
                            @if($inv->status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">Paid in Full</span>
                            @elseif($inv->status === 'sent')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]">Dispatched</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-red-50 text-red-800 border border-red-200">Overdue</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($inv->status !== 'paid')
                            <button @click="selectedInvoice = { id: {{ $inv->id }}, number: '{{ $inv->invoice_number }}', balance: {{ $balance }} }; settleModal = true" class="btn-secondary h-7 px-2.5 text-[11px]">
                                Settle Payment
                            </button>
                            @else
                            <span class="text-[11px] font-mono text-[#065f46] font-semibold">Settled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-xs text-[#8a8a8a]">No client invoices generated. Click "Generate Invoice" above to create one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: TIME ENTRIES -->
    <div x-show="activeTab === 'time-entries'" x-cloak class="space-y-4">
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[#8a8a8a] font-medium uppercase text-[11.5px] tracking-wider">
                        <th class="py-3 px-4 font-medium">Date</th>
                        <th class="py-3 px-4 font-medium">Advocate</th>
                        <th class="py-3 px-4 font-medium">Case Matter</th>
                        <th class="py-3 px-4 font-medium">LEDES Task</th>
                        <th class="py-3 px-4 font-medium">Hours</th>
                        <th class="py-3 px-4 font-medium text-right">Hourly Rate</th>
                        <th class="py-3 px-4 font-medium text-right">Total Fee</th>
                        <th class="py-3 px-4 font-medium">Billing Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($timeEntries as $te)
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#1a1a1a]">{{ \Carbon\Carbon::parse($te->entry_date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-medium text-[#1a1a1a]">{{ $te->user->name ?? 'Advocate' }}</td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            @if($te->matter)
                            <a href="{{ route('matters.show', $te->matter->id) }}" class="text-[#23493a] hover:underline">
                                {{ $te->matter->case_number }}: {{ $te->matter->title }}
                            </a>
                            @else
                            General Chambers Practice
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] font-semibold text-[#23493a] bg-[#f5f3ed] px-1.5 py-0.5 rounded border border-[#e5e3dc]">{{ $te->activity_code }}</span>
                            <span class="text-[#1a1a1a] block text-xs mt-0.5">{{ $te->activity_name }}</span>
                            @if($te->narrative)
                            <span class="text-[11px] text-[#8a8a8a] block italic">{{ $te->narrative }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono font-semibold">{{ $te->hours }} hrs</td>
                        <td class="py-3.5 px-4 text-right font-mono text-[#8a8a8a]">₹{{ number_format($te->rate, 2) }}/hr</td>
                        <td class="py-3.5 px-4 text-right font-mono font-semibold text-[#1a1a1a]">₹{{ number_format($te->total_amount, 2) }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10.5px] font-medium {{ $te->status === 'billed' ? 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' : 'bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]' }}">
                                {{ ucfirst($te->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-xs text-[#8a8a8a]">No billable hours recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: EXPENSES -->
    <div x-show="activeTab === 'expenses'" x-cloak class="space-y-4">
        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[#8a8a8a] font-medium uppercase text-[11.5px] tracking-wider">
                        <th class="py-3 px-4 font-medium">Date</th>
                        <th class="py-3 px-4 font-medium">Disbursement Title</th>
                        <th class="py-3 px-4 font-medium">Category</th>
                        <th class="py-3 px-4 font-medium">Case Matter</th>
                        <th class="py-3 px-4 font-medium">Incurred By</th>
                        <th class="py-3 px-4 font-medium text-right">Amount</th>
                        <th class="py-3 px-4 font-medium">Billable</th>
                        <th class="py-3 px-4 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#1a1a1a]">{{ \Carbon\Carbon::parse($exp->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-[#1a1a1a]">{{ $exp->title }}</div>
                            @if($exp->description)
                            <div class="text-[11px] text-[#8a8a8a]">{{ $exp->description }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono bg-[#f5f3ed] text-[#1a1a1a] border border-[#e5e3dc]">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">
                            @if($exp->matter)
                            {{ $exp->matter->case_number }}: {{ $exp->matter->title }}
                            @else
                            General Chambers
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">{{ $exp->user->name ?? 'Counsel' }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-semibold text-[#1a1a1a]">₹{{ number_format($exp->amount, 2) }}</td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10.5px] font-semibold {{ $exp->is_billable ? 'text-[#23493a]' : 'text-[#8a8a8a]' }}">
                                {{ $exp->is_billable ? 'Billable to Client' : 'Firm Overhead' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10.5px] font-medium {{ $exp->status === 'billed' ? 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' : 'bg-[#fbf3db] text-[#634812] border border-[#f0dfaa]' }}">
                                {{ ucfirst($exp->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-xs text-[#8a8a8a]">No case expenses logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: CHAMBERS LEDGER -->
    <div x-show="activeTab === 'ledger'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs text-[#646864]">Chronological audit trail of all financial inflows, payments, and client retainer deposits.</span>
            <button @click="recordTxnModal = true" class="btn-primary h-8 px-3 text-xs">
                Record Advance / Retainer
            </button>
        </div>

        <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#f0eee8] text-[#8a8a8a] font-medium uppercase text-[11.5px] tracking-wider">
                        <th class="py-3 px-4 font-medium">Transaction Date</th>
                        <th class="py-3 px-4 font-medium">Reference #</th>
                        <th class="py-3 px-4 font-medium">Client / Party</th>
                        <th class="py-3 px-4 font-medium">Matter Attribution</th>
                        <th class="py-3 px-4 font-medium">Type</th>
                        <th class="py-3 px-4 font-medium">Payment Channel</th>
                        <th class="py-3 px-4 font-medium text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-[#faf9f5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-[#1a1a1a]">{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#23493a] font-bold">{{ $txn->reference_number }}</td>
                        <td class="py-3.5 px-4 font-medium text-[#1a1a1a]">{{ $txn->client->name ?? 'General Party' }}</td>
                        <td class="py-3.5 px-4 text-[#646864]">{{ $txn->matter->case_number ?? 'Chambers General' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium uppercase font-mono {{ $txn->type === 'payment' ? 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ str_replace('_', ' ', $txn->type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#646864] uppercase">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-semibold text-[#065f46]">+₹{{ number_format($txn->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-xs text-[#8a8a8a]">No ledger transactions registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: LOG TIME -->
    <div x-show="logTimeModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="logTimeModal = false" class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3 mb-4">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Log Billable Advocacy Hours</h3>
                <button @click="logTimeModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.time-entries.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Matter / Case File *</label>
                    <select name="matter_id" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Hours Spent *</label>
                        <input type="number" step="0.1" min="0.1" name="hours" value="1.5" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Entry Date *</label>
                        <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">LEDES Task Code</label>
                    <select name="activity_code" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none font-mono">
                        <option value="L120">L120 - Analysis &amp; Case Strategy</option>
                        <option value="L110">L110 - Fact Investigation &amp; Client Conference</option>
                        <option value="L330">L330 - Depositions &amp; Oral Arguments</option>
                        <option value="A104">A104 - Document &amp; Evidence Review</option>
                        <option value="B110">B110 - Written Pleadings &amp; Notice Drafting</option>
                        <option value="L240">L240 - Court Registry Filing &amp; Compliance</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Narrative of Services Rendered</label>
                    <textarea name="narrative" rows="3" placeholder="Detailed professional description..." class="w-full p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_billable" name="is_billable" checked class="rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="is_billable" class="text-xs text-[#1a1a1a] font-medium cursor-pointer">Billable to Client Account</label>
                </div>
                <div class="pt-3 border-t border-[#f0eee8] flex justify-end gap-2">
                    <button type="button" @click="logTimeModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Log Time</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: LOG EXPENSE -->
    <div x-show="logExpenseModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="logExpenseModal = false" class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3 mb-4">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Log Case-Related Expense</h3>
                <button @click="logExpenseModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Matter / Case File</label>
                    <select name="matter_id" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="">General Chambers Overhead</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Expense Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Court Fee Stamp" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Category</label>
                        <select name="category" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
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
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Amount (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 2500" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Date Incurred *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Attach Receipt / Voucher (Optional)</label>
                    <input type="file" name="receipt" class="h-10 text-xs text-[#1a1a1a] file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#f5f3ed] file:text-[#23493a] hover:file:bg-[#eae8e2] cursor-pointer"/>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="exp_billable" name="is_billable" checked class="rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="exp_billable" class="text-xs text-[#1a1a1a] font-medium cursor-pointer">Reimbursable from Client</label>
                </div>
                <div class="pt-3 border-t border-[#f0eee8] flex justify-end gap-2">
                    <button type="button" @click="logExpenseModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Log Expense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: GENERATE INVOICE -->
    <div x-show="generateInvoiceModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="generateInvoiceModal = false" class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3 mb-4">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Automated Invoice Generation</h3>
                <button @click="generateInvoiceModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.invoices.generate') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Select Matter *</label>
                    <select name="matter_id" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }} ({{ $m->client->name ?? 'Client' }})</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-[#8a8a8a] block mt-1">All unbilled hours and case disbursements will be automatically compiled into an official invoice.</span>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">GST / Tax Rate (%)</label>
                    <input type="number" step="0.1" name="tax_rate" value="18.0" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#f0eee8] flex justify-end gap-2">
                    <button type="button" @click="generateInvoiceModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Generate &amp; Dispatch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: SETTLE INVOICE -->
    <div x-show="settleModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="settleModal = false" class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3 mb-4">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Settle Invoice Payment</h3>
                <button @click="settleModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form :action="'/billing/invoices/' + (selectedInvoice ? selectedInvoice.id : '') + '/settle'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="p-3 bg-[#faf8f5] rounded-md border border-[#e5e3dc]">
                    <div class="text-[#646864]">Settling Invoice: <strong class="text-[#1a1a1a]" x-text="selectedInvoice ? selectedInvoice.number : ''"></strong></div>
                    <div class="text-[#646864] mt-0.5">Remaining Balance: <strong class="text-[#065f46] font-mono" x-text="selectedInvoice ? ('₹' + selectedInvoice.balance.toFixed(2)) : ''"></strong></div>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Settlement Amount (₹) *</label>
                    <input type="number" step="0.01" min="1" name="amount" :value="selectedInvoice ? selectedInvoice.balance : 0" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none font-mono">
                        <option value="bank_transfer">Bank Wire / NEFT / RTGS</option>
                        <option value="cheque">Cheque / Demand Draft</option>
                        <option value="upi">UPI / Online Gateway</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="cash">Chambers Cash Receipt</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">UTR / Cheque / Auth Reference #</label>
                    <input type="text" name="reference_number" placeholder="e.g. UTR-2026-981240" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#f0eee8] flex justify-end gap-2">
                    <button type="button" @click="settleModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Confirm Settlement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: RECORD TRANSACTION -->
    <div x-show="recordTxnModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="recordTxnModal = false" class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3 mb-4">
                <h3 class="text-[15px] font-semibold text-[#1a1a1a]">Record Client Advance / Retainer</h3>
                <button @click="recordTxnModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('billing.transactions.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Client Name *</label>
                    <select name="client_id" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Matter Attribution (Optional)</label>
                    <select name="matter_id" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        <option value="">General Client Advance</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}">{{ $m->case_number }}: {{ $m->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Deposit Amount (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="50000" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1a1a1a] mb-1">Date *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Transaction Nature</label>
                    <select name="type" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none font-mono">
                        <option value="advance_deposit">Client Advance Retainer Deposit</option>
                        <option value="payment">Direct Fee Settlement</option>
                        <option value="expense_reimbursement">Disbursement Reimbursement</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none font-mono">
                        <option value="bank_transfer">Bank Wire / NEFT / RTGS</option>
                        <option value="cheque">Cheque / Demand Draft</option>
                        <option value="upi">UPI / Online Gateway</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#1a1a1a] mb-1">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="UTR or Cheque number" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#f0eee8] flex justify-end gap-2">
                    <button type="button" @click="recordTxnModal = false" class="btn-secondary h-9 px-3.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Credit to Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
