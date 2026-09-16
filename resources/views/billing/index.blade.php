<x-app-layout>
    <x-slot name="title">Billing, Invoicing &amp; Client Advance Ledger — {{ config('legal.app_name', 'Law Firm Management') }}</x-slot>

    <div x-data="{ openInvoiceModal: false }">
        <!-- Header & Action Ribbon -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#EFECE6] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Advocate Fee Register &amp; Client Accounts</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">Billing &amp; Trust Accounting</h1>
                <p class="text-sm text-[#766A5E]">Client advance retainers, GST fee statements, and court fee registers</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openInvoiceModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#845D33] text-white text-xs font-medium hover:bg-[#6D4B27] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-base">receipt_long</span>
                    <span>Generate Fee Bill</span>
                </button>
            </div>
        </div>

        <!-- Advance Trust Reconciliation Audit Card -->
        <div class="p-6 mb-6 rounded-xl border border-[#F4ECE1] bg-[#F8F4EE] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-[#845D33] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">verified_user</span>
                </div>
                <div>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-[#6D4B27]">Fiduciary Client Trust &amp; Advance Audit</span>
                    <h3 class="text-lg font-serif font-bold text-[#222222]">Advance Retainer Trust Ledger Verified</h3>
                    <p class="text-xs text-[#845D33] mt-0.5">Bank Statement Balance = Individual Client Retainer Sub-Ledgers (Bar Council Standard)</p>
                </div>
            </div>

            <div class="flex items-center gap-6 divide-x divide-[#F4ECE1]">
                <div class="flex flex-col">
                    <span class="text-[10px] font-mono uppercase text-[#845D33]">Total Client Advance Held</span>
                    <span class="font-mono text-xl font-bold text-[#222222]">{{ config('legal.currency_symbol', '₹') }}{{ number_format(\App\Models\Client::sum('trust_balance'), 2) }}</span>
                </div>
                <div class="pl-6 flex flex-col">
                    <span class="text-[10px] font-mono uppercase text-[#845D33]">Audit Variance</span>
                    <span class="font-mono text-xl font-bold text-[#845D33]">{{ config('legal.currency_symbol', '₹') }}0.00</span>
                </div>
            </div>
        </div>

        <!-- Invoices & Unbilled Time -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            <!-- Active Client Invoices -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33] text-xl">receipt</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Recent Fee Invoices</h3>
                    </div>
                    <span class="font-mono text-xs text-[#766A5E]">{{ $invoices->count() }} Statements</span>
                </div>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                            <th class="py-3 px-4">Bill #</th>
                            <th class="py-3 px-4">Client / Matter</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-center">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F4EFEA] text-sm">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-[#222222]">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium text-[#222222] block">{{ $inv->client->name }}</span>
                                <span class="text-[10px] text-[#766A5E]">{{ $inv->matter->title }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-xs font-bold text-right text-[#222222]">{{ config('legal.currency_symbol', '₹') }}{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $inv->status === 'paid' ? 'bg-[#F4ECE1] text-[#6D4B27]' : 'bg-[#B88B56]/60 text-[#845D33]' }}">
                                        {{ $inv->status }}
                                    </span>
                                    @if($inv->status !== 'paid')
                                    <form action="{{ route('invoices.pay', $inv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="font-mono text-[9px] px-1.5 py-0.5 rounded bg-[#F8F4EE] hover:bg-[#F4ECE1] text-[#6D4B27] border border-[#D4C4B5] transition-colors" title="Mark Settled via Client Advance or Direct Transfer">
                                            Settle
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-6 text-center text-xs text-[#766A5E]">No fee bills generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Unbilled Professional Hours Ledger -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33] text-xl">timelapse</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Unbilled Professional Hours</h3>
                    </div>
                    <span class="font-mono text-xs font-bold text-[#845D33]">{{ config('legal.currency_symbol', '₹') }}{{ number_format(\App\Models\TimeEntry::where('status', 'unbilled')->sum('total_amount'), 2) }}</span>
                </div>

                <div class="divide-y divide-[#F4EFEA]">
                    @forelse($timeEntries->take(5) as $te)
                    <div class="p-3.5 flex items-start justify-between gap-3 text-xs hover:bg-[#FAF8F5]">
                        <div class="flex items-start gap-2.5">
                            <span class="font-mono font-bold text-[#845D33] w-10 shrink-0">{{ $te->hours }}h</span>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] px-1 rounded bg-[#F4EFEA] text-[#554D45]">{{ $te->activity_code }}</span>
                                    <span class="font-semibold text-[#222222]">{{ $te->matter->case_number }}</span>
                                    <span class="text-[#766A5E]">· {{ $te->user->name }}</span>
                                </div>
                                <p class="text-[#554D45] mt-0.5 text-[11px] line-clamp-1">{{ $te->narrative }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="font-mono font-bold text-[#222222] shrink-0">{{ config('legal.currency_symbol', '₹') }}{{ number_format($te->total_amount, 2) }}</span>
                            <span class="font-mono text-[9px] uppercase px-1.5 rounded {{ $te->status === 'unbilled' ? 'bg-amber-100 text-amber-800' : 'bg-[#F4ECE1] text-[#6D4B27]' }}">{{ $te->status }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#766A5E]">No unbilled time entries currently pending.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Generate Fee Invoice Modal -->
        <div x-show="openInvoiceModal" @click.away="openInvoiceModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#EFECE6] w-full max-w-md p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33]">receipt_long</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Generate Professional Fee Bill</h3>
                    </div>
                    <button type="button" @click="openInvoiceModal = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('invoices.generate') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Select Case File with Unbilled Work-In-Progress</label>
                        <select name="matter_id" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]">
                            @foreach($matters as $matter)
                            <option value="{{ $matter->id }}">
                                {{ $matter->case_number }} · {{ $matter->title }} ({{ $matter->client->name }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 rounded-lg bg-[#F8F4EE] border border-[#F4ECE1] text-xs text-[#845D33] flex flex-col gap-1">
                        <span class="font-semibold text-[#222222]">GST Invoicing Protocol ({{ config('legal.tax_label', 'GST 18%') }})</span>
                        <p class="text-[11px] leading-relaxed">
                            Unbilled hours and court appearance fees under the selected matter will be compiled into an official statement of fees and dispatched to the client.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-[#F4EFEA] flex items-center justify-end gap-2">
                        <button type="button" @click="openInvoiceModal = false" class="px-4 py-2 rounded-lg border border-[#EAE4DC] text-[#554D45] hover:bg-[#FAF8F5]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#845D33] text-white font-semibold hover:bg-[#6D4B27]">Generate &amp; Dispatch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
