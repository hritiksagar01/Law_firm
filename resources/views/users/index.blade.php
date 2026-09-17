@extends('layouts.app')

@section('title', 'Advocates & Personnel Directory — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-rose-600">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span>Chambers Administration</span>
                <span>•</span>
                <span>Role-Based Access Control</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Advocates &amp; Personnel Directory</h1>
            <p class="text-sm text-[#766A5E] mt-1">Manage legal counsels, research associates, paralegals, and practice groups</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('user-groups.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">workspaces</span>
                <span>Practice Groups</span>
            </a>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Add Team Member</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Total Personnel</span>
            <span class="text-2xl font-serif font-bold text-[#222222] mt-1 block">{{ $users->total() }}</span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Chambers Staff &amp; Counsel</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Advocates &amp; Counsel</span>
            <span class="text-2xl font-serif font-bold text-[#9F8349] mt-1 block">
                {{ \App\Models\User::where('firm_id', auth()->user()->firm_id)->whereIn('role', ['admin', 'partner', 'lawyer', 'associate'])->count() }}
            </span>
            <span class="text-xs text-[#9F8349] mt-2 block font-mono font-semibold">Active Court Bar</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Paralegals &amp; Clerks</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">
                {{ \App\Models\User::where('firm_id', auth()->user()->firm_id)->where('role', 'paralegal')->count() }}
            </span>
            <span class="text-xs text-[#766A5E] mt-2 block font-mono">Research &amp; Filings</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#EFECE6] shadow-xs">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E]">Practice Groups</span>
            <span class="text-2xl font-mono font-bold text-[#222222] mt-1 block">{{ $groups->count() }}</span>
            <span class="text-xs text-emerald-600 mt-2 block font-mono">Specialized Practice Teams</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-[#EFECE6] shadow-xs mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by advocate name, email, or designation title..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-[#EAE4DC] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] bg-[#FAF8F5]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="role" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->slug }}" {{ request('role') == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @if(request('q') || request('role'))
                <a href="{{ route('users.index') }}" class="text-xs text-[#766A5E] hover:text-[#222222] px-2">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Personnel Table -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                    <th class="py-3.5 px-4">Counsel / Staff</th>
                    <th class="py-3.5 px-4">Role &amp; Privilege</th>
                    <th class="py-3.5 px-4">Practice Groups</th>
                    <th class="py-3.5 px-4 text-center">Lead Cases</th>
                    <th class="py-3.5 px-4 text-center">Tasks</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFECE6] text-sm">
                @forelse($users as $u)
                <tr class="hover:bg-[#FAF8F5] transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-center font-bold text-[#9F8349] shrink-0 text-xs">
                                @if($u->avatar_url)
                                <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-full h-full rounded-full object-cover"/>
                                @else
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="font-semibold text-[#222222] flex items-center gap-2">
                                    <span>{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-[#9F8349]/10 text-[#9F8349]">You</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-[#766A5E]">{{ $u->title ?? 'Legal Professional' }}</div>
                                <div class="text-[10px] font-mono text-[#8C7F72]">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium 
                            @if(in_array($u->role, ['admin', 'partner'])) bg-[#9F8349]/10 text-[#9F8349] border border-[#9F8349]/30
                            @elseif(in_array($u->role, ['lawyer', 'associate'])) bg-blue-50 text-blue-700 border border-blue-200
                            @elseif($u->role === 'paralegal') bg-purple-50 text-purple-700 border border-purple-200
                            @elseif($u->role === 'client') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @else bg-gray-50 text-gray-700 border border-gray-200 @endif">
                            {{ $u->roleRelation->name ?? ucfirst(str_replace('_', ' ', $u->role)) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($u->groups as $g)
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222]">
                                {{ $g->name }}
                            </span>
                            @empty
                            <span class="text-[11px] text-gray-400">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold text-[#222222]">
                        {{ $u->lead_matters_count }}
                    </td>
                    <td class="py-3.5 px-4 text-center font-mono text-xs text-[#222222]">
                        {{ $u->tasks_count }}
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        @if($u->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Suspended
                        </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('users.edit', $u) }}" class="p-1 rounded-md text-[#766A5E] hover:text-[#9F8349] hover:bg-[#FAF8F5]" title="Edit Personnel">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle-status', $u) }}" class="inline">
                                @csrf
                                <button type="submit" class="p-1 rounded-md text-[#766A5E] hover:text-amber-600 hover:bg-amber-50" title="{{ $u->status === 'active' ? 'Suspend Access' : 'Restore Access' }}">
                                    <span class="material-symbols-outlined text-lg">{{ $u->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-[#766A5E]">
                        <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">person_search</span>
                        <p class="font-medium">No personnel found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="p-4 border-t border-[#EFECE6]">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
