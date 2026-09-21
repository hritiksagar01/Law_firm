@extends('layouts.app')

@section('title', 'Advocates & Personnel Directory — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-rose-600 text-base">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <span>Chambers Administration</span>
                <span>•</span>
                <span>Personnel Directory</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Advocates &amp; Personnel Directory</h1>
            <p class="text-xs text-[#646864] mt-0.5">Manage legal counsels, research associates, paralegals, and practice groups</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('user-groups.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-[#1a1a1a] text-xs font-medium hover:bg-[#faf8f5] shadow-xs transition-colors">
                <span class="material-symbols-outlined text-base text-[#23493a]">workspaces</span>
                <span>Practice Groups</span>
            </a>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Add Team Member</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Ribbon (Super Admin Connected Style) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 bg-white rounded-xl border border-[#e5e3dc] shadow-sm divide-y lg:divide-y-0 lg:divide-x divide-[#e5e3dc] mb-6 overflow-hidden">
        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Total Personnel</span>
            <span class="text-2xl font-bold text-[#1a1a1a] mt-1 block tracking-tight">{{ $users->total() }}</span>
            <span class="text-[11px] text-[#646864] mt-1 block">Chambers Staff &amp; Counsel</span>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Advocates &amp; Counsel</span>
            <span class="text-2xl font-bold text-[#23493a] mt-1 block tracking-tight">
                {{ \App\Models\User::where('firm_id', auth()->user()->firm_id)->whereIn('role', ['admin', 'partner', 'lawyer', 'associate'])->count() }}
            </span>
            <span class="text-[11px] text-[#23493a] mt-1 block font-medium">Active Court Bar</span>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Paralegals &amp; Clerks</span>
            <span class="text-2xl font-bold text-[#1a1a1a] mt-1 block tracking-tight">
                {{ \App\Models\User::where('firm_id', auth()->user()->firm_id)->where('role', 'paralegal')->count() }}
            </span>
            <span class="text-[11px] text-[#646864] mt-1 block">Research &amp; Filings</span>
        </div>

        <div class="p-5">
            <span class="text-[11px] font-medium text-[#646864] block">Practice Groups</span>
            <span class="text-2xl font-bold text-[#1a1a1a] mt-1 block tracking-tight">{{ $groups->count() }}</span>
            <span class="text-[11px] text-emerald-700 mt-1 block font-medium">Specialized Practice Teams</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-3.5 rounded-xl border border-[#e5e3dc] shadow-sm mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by advocate name, email, or designation title..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="role" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a] font-medium">
                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->slug }}" {{ request('role') == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @if(request('q') || request('role'))
                <a href="{{ route('users.index') }}" class="text-xs text-[#646864] hover:text-[#1a1a1a] px-2 font-medium">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Personnel Table -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[11px] font-semibold text-[#646864] uppercase tracking-wider">
                    <th class="py-3 px-4">Counsel / Staff</th>
                    <th class="py-3 px-4">Role &amp; Privilege</th>
                    <th class="py-3 px-4">Practice Groups</th>
                    <th class="py-3 px-4 text-center">Lead Cases</th>
                    <th class="py-3 px-4 text-center">Tasks</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e3dc] text-xs">
                @forelse($users as $u)
                <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#23493a]/10 border border-[#23493a]/20 flex items-center justify-center font-bold text-[#23493a] shrink-0 text-xs">
                                @if($u->avatar_url)
                                <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-full h-full rounded-full object-cover"/>
                                @else
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="font-semibold text-[#1a1a1a] flex items-center gap-2">
                                    <span>{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-[#23493a]/10 text-[#23493a] font-semibold">You</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-[#646864]">{{ $u->title ?? 'Legal Professional' }}</div>
                                <div class="text-[10px] font-mono text-[#8a8a8a]">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium 
                            @if(in_array($u->role, ['admin', 'partner'])) bg-[#23493a]/10 text-[#23493a] border border-[#23493a]/20
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
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a]">
                                {{ $g->name }}
                            </span>
                            @empty
                            <span class="text-[11px] text-[#8a8a8a]">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold text-[#1a1a1a]">
                        {{ $u->lead_matters_count }}
                    </td>
                    <td class="py-3.5 px-4 text-center font-mono text-xs text-[#1a1a1a]">
                        {{ $u->tasks_count }}
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        @if($u->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Suspended
                        </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('users.edit', $u) }}" class="p-1.5 rounded-lg text-[#646864] hover:text-[#23493a] hover:bg-[#faf8f5] transition-colors" title="Edit Personnel">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle-status', $u) }}" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-[#646864] hover:text-amber-600 hover:bg-amber-50 transition-colors" title="{{ $u->status === 'active' ? 'Suspend Access' : 'Restore Access' }}">
                                    <span class="material-symbols-outlined text-base">{{ $u->status === 'active' ? 'pause_circle' : 'play_circle' }}</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-[#646864]">
                        <span class="material-symbols-outlined text-4xl text-[#8a8a8a] block mb-2">person_search</span>
                        <p class="font-medium text-xs">No personnel found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="p-4 border-t border-[#e5e3dc] bg-[#faf8f5]">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
