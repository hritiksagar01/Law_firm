@extends('layouts.app')

@section('title', 'Case Notes & Internal Memos — Sharma Legal Chambers')
@section('header_title', 'Case Notes')

@section('content')
<div x-data="{
    openNoteModal: false,
    selectedMatterId: '{{ request('matter_id') ?? '' }}',
    noteTitle: '',
    noteBody: '',
    noteType: 'internal',
    isPinned: false
}" class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                Chamber Notes &amp; Legal Memos
            </h1>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Internal practice observations, cross-examination strategies, witness debriefs, and privileged research memos.
            </p>
        </div>

        <button type="button" @click="openNoteModal = true" class="btn-primary h-9 px-4 text-xs inline-flex items-center gap-2 cursor-pointer shadow-xs">
            <span class="material-symbols-outlined text-[17px]">edit_note</span>
            <span>Author New Note</span>
        </button>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="border-b border-[#e5e3dc] flex items-center justify-between gap-4 overflow-x-auto mb-6 text-xs font-medium pb-2">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('notes.index') }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ !request('pinned') && !request('type') ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                All Notes ({{ $notes->total() }})
            </a>
            <a href="{{ route('notes.index', ['pinned' => 1]) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('pinned') ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">push_pin</span>
                    <span>Pinned Memos</span>
                </span>
            </a>
            <a href="{{ route('notes.index', ['type' => 'internal']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('type') === 'internal' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Internal Only
            </a>
            <a href="{{ route('notes.index', ['type' => 'client_visible']) }}"
               class="px-3 py-1.5 rounded-md font-medium transition-colors whitespace-nowrap {{ request('type') === 'client_visible' ? 'bg-[#23493a] text-white' : 'text-[#646864] hover:bg-[#faf8f5] hover:text-[#1a1a1a]' }}">
                Client Visible
            </a>
        </div>

        <form action="{{ route('notes.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative w-56">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[17px] text-[#8a8a8a]">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search note text..."
                       class="w-full h-8 pl-8 pr-3 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a] placeholder-[#8a8a8a] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
            </div>
            @if(request('q') || request('type') || request('pinned'))
                <a href="{{ route('notes.index') }}" class="text-xs text-[#8a8a8a] hover:text-[#1a1a1a]">Clear</a>
            @endif
        </form>
    </div>

    <!-- Notes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
        @forelse($notes as $note)
        <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs hover:border-[#23493a]/50 transition-all flex flex-col justify-between {{ $note->is_pinned ? 'ring-1 ring-[#23493a]/30' : '' }}">
            <div>
                <!-- Top Badge Row -->
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if($note->is_pinned)
                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10.5px] font-semibold bg-[#23493a]/10 text-[#23493a]">
                                <span class="material-symbols-outlined text-[13px]">push_pin</span>
                                <span>Pinned</span>
                            </span>
                        @endif
                        <span class="px-2 py-0.5 rounded text-[10.5px] font-mono {{ $note->type === 'client_visible' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-[#faf8f5] text-[#646864] border border-[#e5e3dc]' }}">
                            {{ $note->type === 'client_visible' ? 'Client Visible' : 'Internal Memo' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('notes.toggle-pin', $note) }}">
                            @csrf
                            <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-[#23493a] cursor-pointer" title="{{ $note->is_pinned ? 'Unpin' : 'Pin to top' }}">
                                <span class="material-symbols-outlined text-[16px]">{{ $note->is_pinned ? 'keep_off' : 'push_pin' }}</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Delete this note?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-red-600 cursor-pointer" title="Delete note">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>

                <h3 class="text-[15px] font-semibold text-[#1a1a1a] mb-1.5 leading-snug">
                    {{ $note->title }}
                </h3>

                @if($note->matter)
                    <div class="mb-2">
                        <a href="{{ route('matters.show', $note->matter_id) }}" class="font-mono text-[11px] text-[#23493a] hover:underline font-medium">
                            {{ $note->matter->case_number }} &middot; {{ $note->matter->title }}
                        </a>
                    </div>
                @endif

                <p class="text-xs text-[#646864] leading-relaxed whitespace-pre-wrap line-clamp-4">
                    {{ $note->body }}
                </p>
            </div>

            <div class="pt-3 border-t border-[#f0eee8] mt-4 flex items-center justify-between text-[11px] text-[#8a8a8a]">
                <span>By <strong class="text-[#1a1a1a]">{{ $note->user->name ?? 'Advocate' }}</strong></span>
                <span class="font-mono">{{ $note->created_at->format('d M Y') }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full border border-[#e5e3dc] bg-white rounded-md p-12 text-center text-xs text-[#8a8a8a]">
            <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2 block">sticky_note_2</span>
            No chamber notes recorded. Click "Author New Note" to write an internal case memo.
        </div>
        @endforelse
    </div>

    @if($notes->hasPages())
    <div class="p-4 border-t border-[#f0eee8] bg-[#faf8f5] rounded-md">
        {{ $notes->links() }}
    </div>
    @endif

    <!-- MODAL: AUTHOR NOTE -->
    <div x-show="openNoteModal" x-cloak @click.away="openNoteModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-[#e5e3dc] rounded-md shadow-2xl w-full max-w-lg p-6 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#f0eee8]">
                <h3 class="text-[16px] font-semibold text-[#1a1a1a]">Author Chamber Case Note</h3>
                <button type="button" @click="openNoteModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('notes.store') }}" method="POST" class="flex flex-col gap-3 text-xs">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Associated Matter / Case (Optional)</label>
                    <select name="matter_id" x-model="selectedMatterId" class="h-9 px-2 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                        <option value="">General Chamber Practice Note (No specific matter)</option>
                        @foreach($matters as $mt)
                            <option value="{{ $mt->id }}">Case {{ $mt->case_number }}: {{ $mt->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Title / Subject *</label>
                    <input name="title" required type="text" placeholder="e.g. Deposition Findings, Statutory Limitation Note..."
                           class="h-9 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"/>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-[#1a1a1a]">Note Body *</label>
                    <textarea name="body" required rows="5" placeholder="Substantive memo, factual chronology, strategy notes..."
                              class="p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a]"></textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-[#f0eee8]">
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="type" value="internal" checked class="text-[#23493a] focus:ring-[#23493a]"/>
                            <span>Internal Only</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="type" value="client_visible" class="text-[#23493a] focus:ring-[#23493a]"/>
                            <span>Client Visible</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="is_pinned" value="1" class="rounded text-[#23493a] focus:ring-[#23493a]"/>
                            <span>Pin Note</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="openNoteModal = false" class="btn-secondary h-8 px-3 text-xs">Cancel</button>
                        <button type="submit" class="btn-primary h-8 px-4 text-xs">Save Memo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
