@extends('layouts.app')

@section('title', 'Practice Groups & Teams — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8" x-data="{ createModal: false, editModal: false, activeGroup: null }">

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
                <a href="{{ route('users.index') }}" class="hover:underline">Personnel Directory</a>
                <span>•</span>
                <span>Chambers Practice Groups</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Practice Groups &amp; Teams</h1>
            <p class="text-xs text-[#646864] mt-0.5">Organize advocates into specialized practice teams (e.g. Litigation, Corporate Advisory, IP)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-[#1a1a1a] text-xs font-medium hover:bg-[#faf8f5] shadow-xs transition-colors">
                <span class="material-symbols-outlined text-base text-[#23493a]">people</span>
                <span>All Personnel</span>
            </a>
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Create Practice Group</span>
            </button>
        </div>
    </div>

    <!-- Practice Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($groups as $g)
        <div class="bg-white rounded-xl border border-[#e5e3dc] p-5 shadow-sm flex flex-col justify-between hover:border-[#23493a]/50 transition-all">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $g->color ?? '#23493a' }}"></span>
                        <h3 class="font-semibold text-base text-[#1a1a1a] tracking-tight">{{ $g->name }}</h3>
                    </div>
                    <form method="POST" action="{{ route('user-groups.destroy', $g) }}" onsubmit="return confirm('Delete this practice group? Members will remain intact.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete Group">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </form>
                </div>

                <p class="text-xs text-[#646864] mb-4 line-clamp-2 leading-relaxed">{{ $g->description ?? 'Specialized practice group in chambers.' }}</p>

                <!-- Assigned Advocates -->
                <div class="mb-4">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-[#646864] block mb-2">Team Members ({{ $g->users_count }})</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($g->users as $member)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></span>
                            <span>{{ $member->name }}</span>
                        </span>
                        @empty
                        <span class="text-xs text-[#8a8a8a] italic">No advocates assigned yet</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pt-3.5 border-t border-[#e5e3dc] flex items-center justify-between text-xs">
                <span class="font-mono text-[#646864] text-[11px]">{{ $g->created_at->format('M Y') }}</span>
                <span class="text-xs text-[#23493a] font-medium">{{ $g->users_count }} Advocates</span>
            </div>
        </div>
        @empty
        <div class="md:col-span-3 py-16 text-center bg-white rounded-xl border border-[#e5e3dc] shadow-sm">
            <span class="material-symbols-outlined text-5xl text-[#8a8a8a] block mb-2">workspaces</span>
            <h3 class="font-bold text-base text-[#1a1a1a]">No Practice Groups Created Yet</h3>
            <p class="text-xs text-[#646864] mt-1 max-w-md mx-auto">Group your advocates into dedicated practice departments like Criminal Defense, Corporate Law, Real Estate, and Civil Litigation.</p>
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Create First Practice Group</span>
            </button>
        </div>
        @endforelse
    </div>

    <!-- Create Practice Group Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModal = false" class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border border-[#e5e3dc]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-base text-[#1a1a1a]">Create Practice Group</h3>
                <button @click="createModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('user-groups.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Group Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Litigation Practice Group"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Primary focus areas and objectives..."
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Badge Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="#23493a" class="w-8 h-8 rounded border border-[#e5e3dc] cursor-pointer" />
                        <span class="text-xs text-[#646864]">Pick group accent color</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Assign Members</label>
                    <div class="max-h-40 overflow-y-auto space-y-1.5 p-2 rounded-lg border border-[#e5e3dc] bg-[#faf8f5]">
                        @foreach($allUsers as $u)
                        <label class="flex items-center gap-2 text-xs text-[#1a1a1a] cursor-pointer hover:bg-white p-1 rounded transition-colors">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="rounded text-[#23493a] focus:ring-[#23493a]" />
                            <span>{{ $u->name }} ({{ $u->title ?? $u->role }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#23493a] text-white rounded-lg hover:bg-[#1a382c] shadow-sm transition-all">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
