@extends('layouts.app')

@section('title', 'Client Directory — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Client Directory')

@section('content')
<div x-data="{ openCreateModal: false }" class="flex flex-col w-full text-[#1a1a1a]">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Client Directory</h1>
            <p class="text-[13px] text-[#646864] mt-1">Enterprise retainers, advance balances, and ongoing representation portfolios</p>
        </div>
        <button @click="openCreateModal = true" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">person_add</span>
            <span>New Client Onboarding</span>
        </button>
    </div>

    <!-- Client Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @forelse($clients as $client)
        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs hover:border-[#23493a]/40 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-3">
                    <span class="font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] font-semibold">
                        {{ $client->type }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span> Active
                    </span>
                </div>
                <h3 class="text-[15px] font-semibold text-[#1a1a1a] mb-1">{{ $client->name }}</h3>
                <p class="text-xs text-[#8a8a8a]">{{ $client->contact_person ?? 'Direct Representation' }}</p>
                <div class="flex items-center gap-1.5 text-xs text-[#646864] mt-2.5">
                    <span class="material-symbols-outlined text-[15px] text-[#8a8a8a]">mail</span>
                    <span class="truncate font-mono">{{ $client->email }}</span>
                </div>
                @if($client->phone)
                <div class="flex items-center gap-1.5 text-xs text-[#8a8a8a] mt-1">
                    <span class="material-symbols-outlined text-[15px]">call</span>
                    <span class="font-mono">{{ $client->phone }}</span>
                </div>
                @endif

                <div class="mt-2.5 text-[11.5px] text-[#646864] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">gavel</span>
                    <span>Counsel: <strong class="text-[#1a1a1a]">{{ $client->primaryAttorney->name ?? 'Managing Chambers' }}</strong></span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#f0eee8] flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] text-[#8a8a8a] block uppercase font-mono">Dossiers</span>
                    <span class="font-mono font-semibold text-[#1a1a1a]">{{ $client->matters->count() }} active</span>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($client->user_id)
                        <span class="inline-flex items-center gap-1 text-[10.5px] text-[#065f46] bg-[#ecfdf5] border border-[#a7f3d0] px-2 py-0.5 rounded-full font-medium">
                            <span class="material-symbols-outlined text-[13px]">verified_user</span>
                            <span>Portal</span>
                        </span>
                    @else
                        <form action="{{ route('clients.invite', $client->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary h-7 px-2 text-[10.5px] inline-flex items-center gap-1" title="Send Client Portal Invitation">
                                <span class="material-symbols-outlined text-[13px]">send</span>
                                <span>Invite</span>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('matters.index') }}" class="btn-secondary h-7 px-2 text-[10.5px] inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">folder_open</span>
                        <span>Cases</span>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full border border-[#e5e3dc] bg-white rounded-md p-8 text-center text-xs text-[#8a8a8a] shadow-xs">
            No client records found. Onboard your first client using the button above.
        </div>
        @endforelse
    </div>

    <!-- New Client Onboarding Modal -->
    <div x-show="openCreateModal" 
         x-cloak 
         @click.away="openCreateModal = false" 
         class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-[#f0eee8] mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">person_add</span>
                    <h3 class="text-[15px] font-semibold text-[#1a1a1a]">New Client Representation Intake</h3>
                </div>
                <button type="button" @click="openCreateModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('clients.store') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Client / Entity Name *</label>
                        <input name="name" required type="text" placeholder="e.g. Malhotra Enterprises Pvt Ltd" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Client Classification</label>
                        <select name="type" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                            <option value="corporate" selected>Corporate Entity (Pvt Ltd / Ltd)</option>
                            <option value="individual">Individual Private Client</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Primary Contact Person</label>
                        <input name="contact_person" type="text" placeholder="e.g. Vikram Malhotra, Managing Director" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Official Email *</label>
                        <input name="email" required type="email" placeholder="client@company.in" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">Telephone / Mobile</label>
                        <input name="phone" type="text" placeholder="+91 98100 12345" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-[#1a1a1a]">GSTIN / PAN</label>
                        <input name="tax_id" type="text" placeholder="07AAACM1234F1Z5" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Lead Consulting Counsel</label>
                    <select name="primary_attorney_id" class="h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                        @foreach($attorneys as $attorney)
                            <option value="{{ $attorney->id }}" {{ Auth::id() === $attorney->id ? 'selected' : '' }}>
                                {{ $attorney->name }} ({{ ucfirst($attorney->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex items-start gap-2.5">
                    <input type="checkbox" name="invite_portal" id="invite_portal" value="1" checked class="mt-0.5 rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="invite_portal" class="text-xs text-[#1a1a1a] font-medium flex flex-col cursor-pointer">
                        <span class="font-semibold text-[#1a1a1a]">Provision Client Portal User Account</span>
                        <span class="text-[11px] text-[#8a8a8a] font-normal">Creates a client login associated with this legal counsel. Default temporary password: Client@1234</span>
                    </label>
                </div>

                <input type="hidden" name="trust_balance" value="0"/>

                <div class="pt-3 border-t border-[#f0eee8] flex items-center justify-end gap-2">
                    <button type="button" @click="openCreateModal = false" class="btn-secondary h-9 px-4 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs">Onboard Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
