@extends('layouts.app')

@section('title', 'Practice Groups & Teams — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-7xl mx-auto p-6 md:p-8" x-data="{ createModal: false, editModal: false, activeGroup: null }">

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
                <a href="{{ route('users.index') }}" class="hover:underline">Personnel Directory</a>
                <span>•</span>
                <span>Chambers Practice Groups</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Practice Groups &amp; Teams</h1>
            <p class="text-sm text-[#766A5E] mt-1">Organize advocates into specialized practice teams (e.g. Litigation, Corporate Advisory, IP)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5] shadow-xs transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">people</span>
                <span>All Personnel</span>
            </a>
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Create Practice Group</span>
            </button>
        </div>
    </div>

    <!-- Practice Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $g)
        <div class="bg-white rounded-2xl border border-[#EFECE6] p-6 shadow-xs flex flex-col justify-between hover:border-[#9F8349]/40 transition-all">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $g->color ?? '#9F8349' }}"></span>
                        <h3 class="font-serif font-bold text-lg text-[#222222]">{{ $g->name }}</h3>
                    </div>
                    <form method="POST" action="{{ route('user-groups.destroy', $g) }}" onsubmit="return confirm('Delete this practice group? Members will remain intact.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 rounded text-gray-400 hover:text-rose-600 hover:bg-rose-50" title="Delete Group">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </form>
                </div>

                <p class="text-xs text-[#766A5E] mb-4 line-clamp-2">{{ $g->description ?? 'Specialized practice group in chambers.' }}</p>

                <!-- Assigned Advocates -->
                <div class="mb-4">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#766A5E] block mb-2">Team Members ({{ $g->users_count }})</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($g->users as $member)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#9F8349]"></span>
                            <span>{{ $member->name }}</span>
                        </span>
                        @empty
                        <span class="text-xs text-gray-400 italic">No advocates assigned yet</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-[#EFECE6] flex items-center justify-between text-xs">
                <span class="font-mono text-[#766A5E]">{{ $g->created_at->format('M Y') }}</span>
                <span class="text-[11px] text-[#9F8349] font-medium">{{ $g->users_count }} Advocates</span>
            </div>
        </div>
        @empty
        <div class="md:col-span-3 py-16 text-center bg-white rounded-2xl border border-[#EFECE6]">
            <span class="material-symbols-outlined text-5xl text-gray-300 block mb-2">workspaces</span>
            <h3 class="font-serif font-bold text-lg text-[#222222]">No Practice Groups Created Yet</h3>
            <p class="text-xs text-[#766A5E] mt-1 max-w-md mx-auto">Group your advocates into dedicated practice departments like Criminal Defense, Corporate Law, Real Estate, and Civil Litigation.</p>
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36]">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Create First Practice Group</span>
            </button>
        </div>
        @endforelse
    </div>

    <!-- Create Practice Group Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-[#EFECE6]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-serif font-bold text-lg text-[#222222]">Create Practice Group</h3>
                <button @click="createModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('user-groups.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Group Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Litigation Practice Group"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Primary focus areas and objectives..."
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Badge Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="#9F8349" class="w-8 h-8 rounded border border-[#EAE4DC] cursor-pointer" />
                        <span class="text-xs text-[#766A5E]">Pick group accent color</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Assign Members</label>
                    <div class="max-h-40 overflow-y-auto space-y-1.5 p-2 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5]">
                        @foreach($allUsers as $u)
                        <label class="flex items-center gap-2 text-xs text-[#222222] cursor-pointer hover:bg-white p-1 rounded">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="rounded text-[#9F8349] focus:ring-[#9F8349]" />
                            <span>{{ $u->name }} ({{ $u->title ?? $u->role }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-semibold text-[#766A5E] hover:text-[#222222]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-[#9F8349] text-white rounded-lg hover:bg-[#856C36]">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
