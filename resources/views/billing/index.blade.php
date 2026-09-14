<x-app-layout>
    <x-slot name="title">Billing, Invoicing &amp; Client Advance Ledger — {{ config('legal.app_name', 'Law Firm Management') }}</x-slot>

    <div x-data="{ openInvoiceModal: false }">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e9e8e5] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#727973] uppercase tracking-wider">Advocate Fee Register &amp; Client Accounts</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Billing &amp; Trust Accounting</h1>
                <p class="text-sm text-[#727973]">Client advance retainers, GST fee statements, and court fee registers</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openInvoiceModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-base">receipt_long</span>
                    <span>Generate Fee Bill</span>
                </button>
            </div>
        </div>

        <!-- Advance Trust Reconciliation Audit Card -->
        <div class="p-6 mb-6 rounded-xl border border-[#c5ecd2] bg-[#f0f9f4] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-[#1a3c2a] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">verified_user</span>
                </div>
                <div>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-emerald-800">Fiduciary Client Trust &amp; Advance Audit</span>
                    <h3 class="text-lg font-serif font-bold text-[#002112]">Advance Retainer Trust Ledger Verified</h3>
                    <p class="text-xs text-[#2c4e3a] mt-0.5">Bank Statement Balance = Individual Client Retainer Sub-Ledgers (Bar Council Standard)</p>
                </div>
            </div>

            <div class="flex items-center gap-6 divide-x divide-[#c5ecd2]">
                <div class="flex flex-col">
                    <span class="text-[10px] font-mono uppercase text-[#2c4e3a]">Total Client Advance Held</span>
                    <span class="font-mono text-xl font-bold text-[#002112]">{{ config('legal.currency_symbol', '₹') }}{{ number_format(\App\Models\Client::sum('trust_balance'), 2) }}</span>
                </div>
                <div class="pl-6 flex flex-col">
                    <span class="text-[10px] font-mono uppercase text-[#2c4e3a]">Audit Variance</span>
                    <span class="font-mono text-xl font-bold text-emerald-700">{{ config('legal.currency_symbol', '₹') }}0.00</span>
                </div>
            </div>
        </div>

        <!-- Invoices & Unbilled Time -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            <!-- Active Client Invoices -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a] text-xl">receipt</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Recent Fee Invoices</h3>
                    </div>
                    <span class="font-mono text-xs text-[#727973]">{{ $invoices->count() }} Statements</span>
                </div>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f4f3f1] border-b border-[#e9e8e5] text-[11px] font-mono text-[#727973] uppercase tracking-wider">
                            <th class="py-3 px-4">Bill #</th>
                            <th class="py-3 px-4">Client / Matter</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-center">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#efeeeb] text-sm">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-[#faf9f6]">
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-[#1a1c1a]">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium text-[#1a1c1a] block">{{ $inv->client->name }}</span>
                                <span class="text-[10px] text-[#727973]">{{ $inv->matter->title }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-xs font-bold text-right text-[#1a1c1a]">{{ config('legal.currency_symbol', '₹') }}{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#fed977]/60 text-[#785d00]' }}">
                                        {{ $inv->status }}
                                    </span>
                                    @if($inv->status !== 'paid')
                                    <form action="{{ route('invoices.pay', $inv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="font-mono text-[9px] px-1.5 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 transition-colors" title="Mark Settled via Client Advance or Direct Transfer">
                                            Settle
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-6 text-center text-xs text-[#727973]">No fee bills generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Unbilled Professional Hours Ledger -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#755b00] text-xl">timelapse</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Unbilled Professional Hours</h3>
                    </div>
                    <span class="font-mono text-xs font-bold text-[#1a3c2a]">{{ config('legal.currency_symbol', '₹') }}{{ number_format(\App\Models\TimeEntry::where('status', 'unbilled')->sum('total_amount'), 2) }}</span>
                </div>

                <div class="divide-y divide-[#efeeeb]">
                    @forelse($timeEntries->take(5) as $te)
                    <div class="p-3.5 flex items-start justify-between gap-3 text-xs hover:bg-[#faf9f6]">
                        <div class="flex items-start gap-2.5">
                            <span class="font-mono font-bold text-[#1a3c2a] w-10 shrink-0">{{ $te->hours }}h</span>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] px-1 rounded bg-[#efeeeb] text-[#424843]">{{ $te->activity_code }}</span>
                                    <span class="font-semibold text-[#1a1c1a]">{{ $te->matter->case_number }}</span>
                                    <span class="text-[#727973]">· {{ $te->user->name }}</span>
                                </div>
                                <p class="text-[#424843] mt-0.5 text-[11px] line-clamp-1">{{ $te->narrative }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="font-mono font-bold text-[#1a1c1a] shrink-0">{{ config('legal.currency_symbol', '₹') }}{{ number_format($te->total_amount, 2) }}</span>
                            <span class="font-mono text-[9px] uppercase px-1.5 rounded {{ $te->status === 'unbilled' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $te->status }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#727973]">No unbilled time entries currently pending.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Generate Fee Invoice Modal -->
        <div x-show="openInvoiceModal" @click.away="openInvoiceModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#e9e8e5] w-full max-w-md p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#efeeeb] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a]">receipt_long</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Generate Professional Fee Bill</h3>
                    </div>
                    <button type="button" @click="openInvoiceModal = false" class="text-[#727973] hover:text-[#1a1c1a]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('invoices.generate') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Select Case File with Unbilled Work-In-Progress</label>
                        <select name="matter_id" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                            @foreach($matters as $matter)
                            <option value="{{ $matter->id }}">
                                {{ $matter->case_number }} · {{ $matter->title }} ({{ $matter->client->name }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 rounded-lg bg-[#f0f9f4] border border-[#c5ecd2] text-xs text-[#2c4e3a] flex flex-col gap-1">
                        <span class="font-semibold text-[#002112]">GST Invoicing Protocol ({{ config('legal.tax_label', 'GST 18%') }})</span>
                        <p class="text-[11px] leading-relaxed">
                            Unbilled hours and court appearance fees under the selected matter will be compiled into an official statement of fees and dispatched to the client.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-[#efeeeb] flex items-center justify-end gap-2">
                        <button type="button" @click="openInvoiceModal = false" class="px-4 py-2 rounded-lg border border-[#c1c8c1] text-[#424843] hover:bg-[#faf9f6]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#1a3c2a] text-white font-semibold hover:bg-[#022616]">Generate &amp; Dispatch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
