@extends('layouts.admin')

@section('title', $firm->name . ' — Firm Overview')
@section('header_title', $firm->name . ' Overview')

@section('content')
<div class="space-y-6">

    <!-- Firm Banner Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFECE6] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-center text-[#9F8349] shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-3xl">domain</span>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-serif font-bold text-[#222222]">{{ $firm->display_title }}</h1>
                    @if($firm->status === 'active')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Active
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Inactive
                    </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-[#766A5E]">
                    <span class="font-mono bg-[#FAF8F5] px-2 py-0.5 rounded-md border border-[#EAE4DC]">{{ $firm->slug }}.lexiscore.app</span>
                    @if($firm->city || $firm->state)
                    <span>•</span>
                    <span>{{ $firm->city }}{{ $firm->city && $firm->state ? ', ' : '' }}{{ $firm->state }}</span>
                    @endif
                    @if($firm->email)
                    <span>•</span>
                    <span class="font-mono">{{ $firm->email }}</span>
                    @endif
                    @if($firm->phone)
                    <span>•</span>
                    <span>{{ $firm->phone }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.firms.edit', $firm) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base">edit</span>
                <span>Edit Firm</span>
            </a>
            <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border text-xs font-semibold transition-all {{ $firm->status === 'active' ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    <span class="material-symbols-outlined text-base">{{ $firm->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                    <span>{{ $firm->status === 'active' ? 'Suspend Practice' : 'Activate Practice' }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Personnel</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $firm->users_count }}</span>
            <span class="text-xs text-[#766A5E] mt-1 block">Advocates &amp; Staff</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Active Matters</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $firm->matters_count }}</span>
            <span class="text-xs text-[#766A5E] mt-1 block">Live Case Dossiers</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Clients</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $firm->clients_count }}</span>
            <span class="text-xs text-[#766A5E] mt-1 block">Corporate &amp; Individual</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Documents</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $firm->documents_count }}</span>
            <span class="text-xs text-[#766A5E] mt-1 block">Vault Files Stored</span>
        </div>
    </div>

    <!-- Two Column Layout: Personnel & Cases vs Practice Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Col: Personnel & Matters -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Personnel Roster Table -->
            <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-base">group</span>
                        <span>Firm Advocates &amp; Staff Roster</span>
                    </h3>
                    <span class="font-mono text-xs text-[#766A5E]">{{ $firm->users_count }} Total Accounts</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">
                            <th class="py-2.5 px-4 font-semibold">User</th>
                            <th class="py-2.5 px-4 font-semibold">Role</th>
                            <th class="py-2.5 px-4 font-semibold">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6] text-xs">
                        @forelse($users as $user)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-[#222222]">{{ $user->name }}</div>
                                <div class="font-mono text-[11px] text-[#766A5E]">{{ $user->email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium capitalize {{ $user->role === 'partner' ? 'bg-[#9F8349]/10 text-[#9F8349] font-bold' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[#766A5E]">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-400">No personnel registered in this firm yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Recent Matters Table -->
            <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#EFECE6] flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-base">gavel</span>
                        <span>Recent Matters / Cases</span>
                    </h3>
                    <span class="font-mono text-xs text-[#766A5E]">{{ $firm->matters_count }} Total Cases</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">
                            <th class="py-2.5 px-4 font-semibold">Case # / Title</th>
                            <th class="py-2.5 px-4 font-semibold">Client</th>
                            <th class="py-2.5 px-4 font-semibold">Stage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6] text-xs">
                        @forelse($matters as $matter)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="py-3 px-4">
                                <span class="font-mono text-[10px] text-[#9F8349] block">{{ $matter->case_number }}</span>
                                <span class="font-semibold text-[#222222]">{{ $matter->title }}</span>
                            </td>
                            <td class="py-3 px-4 text-[#766A5E]">
                                {{ $matter->client->name ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium capitalize bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ str_replace('_', ' ', $matter->stage) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-400">No active matters in this firm yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right Col: Firm Details & Practice Areas -->
        <div class="space-y-6">
            
            <!-- Practice Disciplines -->
            <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Practice Disciplines</h3>
                <div class="flex flex-wrap gap-1.5">
                    @if(is_array($firm->practice_areas) && count($firm->practice_areas) > 0)
                        @foreach($firm->practice_areas as $area)
                        <span class="px-2.5 py-1 rounded-xl text-xs bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222]">
                            {{ $area }}
                        </span>
                        @endforeach
                    @else
                        <span class="text-xs text-gray-400">General Practice (All Areas)</span>
                    @endif
                </div>
            </div>

            <!-- Organization Particulars -->
            <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Organization Particulars</h3>
                <dl class="space-y-2.5 text-xs">
                    <div>
                        <dt class="text-[#766A5E]">Default Currency</dt>
                        <dd class="font-mono font-semibold text-[#222222]">{{ $firm->currency ?? 'INR' }}</dd>
                    </div>
                    @if($firm->address)
                    <div>
                        <dt class="text-[#766A5E]">Chambers Address</dt>
                        <dd class="text-[#222222] mt-0.5">{{ $firm->address }}</dd>
                    </div>
                    @endif
                    @if($firm->website)
                    <div>
                        <dt class="text-[#766A5E]">Website</dt>
                        <dd class="text-[#9F8349] font-mono mt-0.5 truncate">
                            <a href="{{ $firm->website }}" target="_blank" class="hover:underline">{{ $firm->website }}</a>
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-[#766A5E]">Provisioned On</dt>
                        <dd class="text-[#222222] font-mono mt-0.5">{{ $firm->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

        </div>

    </div>

</div>
@endsection
