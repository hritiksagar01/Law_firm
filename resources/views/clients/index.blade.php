<x-app-layout>
    <x-slot name="title">Clients &amp; Corporate Entities — {{ config('legal.app_name', 'Law Firm Management') }}</x-slot>

    <div x-data="{ 
        openCreateModal: false, 
        openDepositModal: false, 
        depositClientId: '', 
        depositClientName: '',
        openDeposit(id, name) {
            this.depositClientId = id;
            this.depositClientName = name;
            this.openDepositModal = true;
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#EFECE6] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Enterprise &amp; Individual Relationships</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">Client Directory</h1>
                <p class="text-sm text-[#766A5E]">Retainers, client advance ledger balances, and active matters</p>
            </div>
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#845D33] text-white text-xs font-medium hover:bg-[#6D4B27] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>New Client Onboarding</span>
            </button>
        </div>

        <!-- Client Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            @foreach($clients as $client)
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm hover:border-[#8C7F72] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <span class="font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-[#F4EFEA] text-[#554D45]">
                            {{ $client->type }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#845D33]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F8F4EE]0"></span> Active
                        </span>
                    </div>
                    <h3 class="text-base font-semibold text-[#222222] mb-1">{{ $client->name }}</h3>
                    <p class="text-xs text-[#766A5E]">{{ $client->contact_person ?? 'Direct Representation' }}</p>
                    <div class="flex items-center gap-1.5 text-xs text-[#554D45] mt-2">
                        <span class="material-symbols-outlined text-sm text-[#766A5E]">mail</span>
                        <span class="truncate">{{ $client->email }}</span>
                    </div>
                    @if($client->phone)
                    <div class="flex items-center gap-1.5 text-xs text-[#766A5E] mt-1">
                        <span class="material-symbols-outlined text-sm text-[#766A5E]">call</span>
                        <span>{{ $client->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-[#F4EFEA] flex flex-col gap-2">
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] text-[#766A5E] block uppercase font-mono">Advance Retainer</span>
                            <span class="font-mono font-bold text-[#845D33]">{{ config('legal.currency_symbol', '₹') }}{{ number_format($client->trust_balance, 2) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-[#766A5E] block uppercase font-mono">Matters</span>
                            <span class="font-mono font-semibold text-[#222222]">{{ $client->matters->count() }} active</span>
                        </div>
                    </div>

                    <button type="button" @click="openDeposit({{ $client->id }}, '{{ addslashes($client->name) }}')" class="w-full py-1.5 text-center text-[11px] font-medium text-[#845D33] bg-[#F8F4EE] hover:bg-[#F4ECE1]/50 rounded transition-colors flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-xs">add_card</span>
                        <span>Credit Advance Deposit</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- New Client Onboarding Modal -->
        <div x-show="openCreateModal" @click.away="openCreateModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#EFECE6] w-full max-w-lg p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33]">person_add</span>
                        <h3 class="text-sm font-semibold text-[#222222]">New Client Representation Intake</h3>
                    </div>
                    <button type="button" @click="openCreateModal = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('clients.store') }}" method="POST" class="flex flex-col gap-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Client / Entity Name</label>
                            <input name="name" required type="text" placeholder="e.g. Malhotra Enterprises Pvt Ltd" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Client Classification</label>
                            <select name="type" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]">
                                <option value="corporate" selected>Corporate Entity (Pvt Ltd / Ltd)</option>
                                <option value="individual">Individual Private Client</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Primary Contact Person</label>
                            <input name="contact_person" type="text" placeholder="e.g. Vikram Malhotra, Managing Director" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Official Email</label>
                            <input name="email" required type="email" placeholder="client@company.in" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Telephone / Mobile</label>
                            <input name="phone" type="text" placeholder="+91 98100 12345" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">GSTIN / PAN</label>
                            <input name="tax_id" type="text" placeholder="07AAACM1234F1Z5" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Initial Advance Retainer Deposit ({{ config('legal.currency_symbol', '₹') }})</label>
                        <input name="trust_balance" type="number" step="1000" value="50000" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                    </div>

                    <div class="pt-3 border-t border-[#F4EFEA] flex items-center justify-end gap-2">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-lg border border-[#EAE4DC] text-[#554D45] hover:bg-[#FAF8F5]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#845D33] text-white font-semibold hover:bg-[#6D4B27]">Onboard Client</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trust Deposit Modal -->
        <div x-show="openDepositModal" @click.away="openDepositModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#EFECE6] w-full max-w-sm p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#6D4B27]">account_balance</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Credit Advance Account</h3>
                    </div>
                    <button type="button" @click="openDepositModal = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="'/clients/' + depositClientId + '/trust-deposit'" method="POST" class="flex flex-col gap-3 text-xs">
                    @csrf
                    <div>
                        <span class="text-[#766A5E] block text-[11px]">Client Sub-Ledger</span>
                        <span class="font-semibold text-[#222222] text-sm" x-text="depositClientName"></span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Deposit Amount ({{ config('legal.currency_symbol', '₹') }} INR)</label>
                        <input name="amount" type="number" step="1000" value="25000" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33] font-mono text-sm font-bold"/>
                    </div>

                    <div class="p-2.5 rounded bg-[#F8F4EE] text-[#845D33] text-[11px]">
                        Deposit will be credited to client's advance retainer ledger for legal fees and court expenses.
                    </div>

                    <div class="pt-3 border-t border-[#F4EFEA] flex items-center justify-end gap-2">
                        <button type="button" @click="openDepositModal = false" class="px-4 py-2 rounded-lg border border-[#EAE4DC] text-[#554D45] hover:bg-[#FAF8F5]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#845D33] text-white font-semibold hover:bg-[#6D4B27]">Record Deposit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
