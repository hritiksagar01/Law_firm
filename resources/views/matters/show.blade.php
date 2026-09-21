@extends('layouts.app')

@section('title', $matter->case_number . ' — ' . $matter->title)
@section('header_title', 'Matter ' . $matter->case_number)

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">
    <!-- Dossier Breadcrumb & Top Bar -->
    <div class="flex flex-col gap-4 pb-6 border-b border-[#e5e3dc] mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#646864] hover:text-[#23493a] transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Matters Directory</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs px-2.5 py-0.5 rounded-full bg-[#ecfdf5] text-[#065f46] font-medium border border-[#a7f3d0]">
                    {{ $matter->stage }}
                </span>
                <span class="font-mono text-xs px-2.5 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]">
                    {{ $matter->case_number }}
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">{{ $matter->title }}</h1>
                <p class="text-[13px] text-[#646864] mt-1">
                    {{ $matter->court_name }} <span class="mx-1">&middot;</span> Presiding: {{ $matter->judge_name }} <span class="mx-1">&middot;</span> Opened {{ $matter->opened_at?->format('M d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">draw</span>
                    <span>Draft Opinion</span>
                </a>
                <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="btn-secondary h-9 px-3 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">event</span>
                    <span>Book Hearing</span>
                </a>
                <a href="{{ route('documents.index') }}" class="btn-primary h-9 px-3.5 text-xs inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">upload_file</span>
                    <span>Upload Filing</span>
                </a>
            </div>
        </div>

        <!-- Dossier Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-1 pt-2 -mb-6 border-b border-[#f0eee8]">
            <a href="#overview-section" class="px-3.5 py-2 text-xs font-semibold border-b-2 border-[#23493a] text-[#23493a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">dashboard</span>
                <span>Case Overview</span>
            </a>
            <a href="#opinions-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">draw</span>
                <span>Opinions ({{ $matter->opinions->count() }})</span>
            </a>
            <a href="#hearings-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">calendar_month</span>
                <span>Hearings &amp; Docket ({{ $matter->appointments->count() + $matter->events->count() }})</span>
            </a>
            <a href="#filings-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">description</span>
                <span>Filings &amp; Evidence ({{ $matter->documents->count() }})</span>
            </a>
            <a href="#dispatches-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">forum</span>
                <span>Privileged Messages ({{ $matter->messages->count() }})</span>
            </a>
            <a href="#notes-section" class="px-3.5 py-2 text-xs font-medium text-[#646864] hover:text-[#1a1a1a] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-base">sticky_note_2</span>
                <span>Case Notes ({{ $matter->caseNotes->count() }})</span>
            </a>
        </div>
    </div>

    <!-- Dossier Content Columns -->
    <div id="overview-section" class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4 items-start">
        <!-- Left 2-Columns: Milestones, Filings & Time -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Procedural Stage Tracker -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Litigation Procedural Stages</h3>
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">01</span> Intake
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">02</span> Pleadings
                    </div>
                    <div class="p-2 rounded bg-[#23493a] text-white font-semibold shadow-xs">
                        <span class="font-mono text-[10px] block text-[#a7f3d0]">03 ACTIVE</span> Discovery
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">04</span> Pre-Trial
                    </div>
                    <div class="p-2 rounded bg-[#faf8f5] text-[#646864] border border-[#f0eee8]">
                        <span class="font-mono text-[10px] block text-[#8a8a8a]">05</span> Trial
                    </div>
                </div>
            </div>

            <!-- Legal Opinions & Strategy Memoranda -->
            <div id="opinions-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">draw</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Legal Opinions &amp; Case Strategy</h3>
                    </div>
                    <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Draft Opinion</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->opinions as $op)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div>
                            <a href="{{ route('opinions.show', $op) }}" class="text-xs font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                {{ $op->title }}
                            </a>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#646864]">
                                <span class="font-mono text-[#23493a]">{{ $op->opinion_number }}</span>
                                <span>&middot;</span>
                                <span class="capitalize">{{ str_replace('_', ' ', $op->type) }}</span>
                                <span>&middot;</span>
                                <span>{{ $op->author->name ?? 'Advocate' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                                @if($op->status === 'published') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($op->status === 'under_review') bg-amber-50 text-amber-700 border border-amber-200
                                @elseif($op->status === 'approved') bg-blue-50 text-blue-700 border border-blue-200
                                @else bg-gray-50 text-gray-700 border border-gray-200 @endif">
                                {{ $op->status }}
                            </span>
                            <a href="{{ route('opinions.show', $op) }}" class="p-1 rounded text-[#8a8a8a] hover:text-[#23493a]">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#8a8a8a]">No formal legal opinions drafted for this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Hearings & Court Docket Appearances -->
            <div id="hearings-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">calendar_month</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Scheduled Hearings &amp; Appearances</h3>
                    </div>
                    <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Book Appearance</span>
                    </a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->appointments as $apt)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-md bg-[#faf8f5] border border-[#e5e3dc] flex flex-col items-center justify-center shrink-0">
                                <span class="font-mono text-[9px] uppercase font-bold text-[#23493a]">{{ $apt->scheduled_at->format('M') }}</span>
                                <span class="font-medium text-sm text-[#1a1a1a] leading-none">{{ $apt->scheduled_at->format('d') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('appointments.show', $apt) }}" class="text-xs font-semibold text-[#1a1a1a] hover:text-[#23493a] hover:underline">
                                    {{ $apt->title }}
                                </a>
                                <div class="text-[11px] text-[#646864] mt-0.5">
                                    <span>{{ $apt->scheduled_at->format('h:i A') }}</span>
                                    @if($apt->location) <span>&middot; {{ $apt->location }}</span> @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                            @if($apt->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @elseif($apt->status === 'adjourned') bg-amber-50 text-amber-700 border border-amber-200
                            @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                            {{ $apt->status }}
                        </span>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#8a8a8a]">No court appearances currently scheduled.</div>
                    @endforelse
                </div>
            </div>

            <!-- Documents & Evidence in Matter -->
            <div id="filings-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">folder</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Matter Evidence &amp; Filings</h3>
                    </div>
                    <a href="{{ route('documents.index') }}" class="text-xs text-[#23493a] hover:underline font-medium">Vault View &rarr;</a>
                </div>

                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->documents as $doc)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-red-50 text-red-700 flex items-center justify-center font-mono text-[10px] font-bold shrink-0 border border-red-200">
                                PDF
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1a1a] hover:underline cursor-pointer">{{ $doc->title }}</span>
                                <span class="text-[11px] text-[#8a8a8a] font-mono mt-0.5">
                                    {{ $doc->filename }} <span class="mx-1">&middot;</span> {{ $doc->formattedSize() }} <span class="mx-1">&middot;</span> v{{ $doc->version }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#f5f3ed] text-[#23493a] font-semibold border border-[#e5e3dc]">{{ $doc->privilege }}</span>
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#8a8a8a] hover:text-[#23493a] rounded inline-flex" title="Download Filing">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No documents filed in this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Privileged Attorney-Client Communications -->
            <div id="dispatches-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#f0eee8] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">lock</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1a1a]">Privileged Matter Dispatch</h3>
                            <span class="text-[10px] font-mono text-[#23493a] bg-[#ecfdf5] border border-[#a7f3d0] px-1.5 py-0.5 rounded font-medium uppercase">ABA Model Rule 1.6 Protected</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-[#8a8a8a]">{{ $matter->messages->count() }} Dispatches</span>
                </div>

                <!-- Messages Stream -->
                <div class="p-4 flex flex-col gap-4 max-h-96 overflow-y-auto bg-[#faf8f5]">
                    @forelse($matter->messages as $msg)
                    <div class="flex items-start gap-3 {{ $msg->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        @if($msg->sender?->avatar_url)
                            <img alt="{{ $msg->sender->name }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#e5e3dc] shrink-0" src="{{ $msg->sender->avatar_url }}"/>
                        @else
                            <div class="w-7 h-7 rounded-full bg-[#5b4382] text-white flex items-center justify-center font-semibold text-[10px] shrink-0 shadow-xs">
                                {{ strtoupper(substr($msg->sender?->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex flex-col max-w-lg {{ $msg->sender_id === auth()->id() ? 'items-end' : '' }}">
                            <div class="flex items-center gap-2 mb-1 text-[11px]">
                                <span class="font-semibold text-[#1a1a1a]">{{ $msg->sender->name ?? 'Participant' }}</span>
                                <span class="text-[#8a8a8a] font-mono">{{ $msg->created_at->format('M d, g:i A') }}</span>
                            </div>
                            <div class="p-3 rounded-md text-xs leading-relaxed {{ $msg->sender_id === auth()->id() ? 'bg-[#23493a] text-white rounded-tr-none shadow-xs' : 'bg-white border border-[#e5e3dc] text-[#1a1a1a] rounded-tl-none shadow-xs' }}">
                                {{ $msg->body }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No encrypted dispatches sent under this matter yet.</div>
                    @endforelse
                </div>

                <!-- Message Composer -->
                <form action="{{ route('messages.store') }}" method="POST" class="p-3 bg-white border-t border-[#f0eee8] flex gap-2">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                    <input name="body" required placeholder="Type privileged counsel communication or discovery inquiry..." class="flex-1 px-3 py-2 text-xs rounded-md border border-[#e5e3dc] outline-none focus:border-[#23493a]"/>
                    <button type="submit" class="btn-primary h-9 px-4 text-xs flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-sm">send</span>
                        <span>Send</span>
                    </button>
                </form>
            </div>

            <!-- Case Notes & Strategy Memoranda (F-12) -->
            <div id="notes-section" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden" x-data="{ addNote: false }">
                <div class="p-4 border-b border-[#f0eee8] bg-[#faf8f5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-xl">sticky_note_2</span>
                        <h3 class="text-sm font-semibold text-[#1a1a1a]">Case Notes &amp; Strategy Memoranda</h3>
                    </div>
                    <button @click="addNote = !addNote" class="text-xs text-[#23493a] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span x-text="addNote ? 'Cancel' : 'Add Note'">Add Note</span>
                    </button>
                </div>

                <!-- Add Note Inline Form -->
                <div x-show="addNote" x-cloak class="p-4 border-b border-[#e5e3dc] bg-[#faf8f5]/50">
                    <form method="POST" action="{{ route('notes.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                        <div>
                            <input type="text" name="title" required placeholder="Note Title (e.g. Witness Interview Summary, Discovery Strategy)"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"/>
                        </div>
                        <div>
                            <textarea name="body" rows="3" required placeholder="Substantive case notes, findings, observations, or action points..."
                                class="w-full p-3 text-xs rounded-lg border border-[#e5e3dc] bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]"></textarea>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1.5 text-[#646864]">
                                    <input type="radio" name="type" value="internal" checked class="text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Internal Only</span>
                                </label>
                                <label class="flex items-center gap-1.5 text-[#646864]">
                                    <input type="radio" name="type" value="client_visible" class="text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Client Visible</span>
                                </label>
                                <label class="flex items-center gap-1.5 text-[#646864]">
                                    <input type="checkbox" name="is_pinned" value="1" class="rounded text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Pin to Top</span>
                                </label>
                            </div>
                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-colors">
                                Save Note
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notes List -->
                <div class="divide-y divide-[#f0eee8]">
                    @forelse($matter->caseNotes->sortByDesc('is_pinned')->sortByDesc('created_at') as $note)
                    <div class="p-4 hover:bg-[#faf9f5] transition-colors {{ $note->is_pinned ? 'bg-[#23493a]/[0.02]' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($note->is_pinned)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#23493a]/10 text-[#23493a]">
                                        <span class="material-symbols-outlined text-xs">push_pin</span>
                                        <span>Pinned</span>
                                    </span>
                                    @endif
                                    <h4 class="text-xs font-semibold text-[#1a1a1a]">{{ $note->title }}</h4>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono {{ $note->type === 'client_visible' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-stone-100 text-stone-600' }}">
                                        {{ $note->type === 'client_visible' ? 'Client Visible' : 'Internal Memo' }}
                                    </span>
                                </div>
                                <p class="text-xs text-[#646864] mt-1.5 leading-relaxed whitespace-pre-line">{{ $note->body }}</p>
                                <div class="flex items-center gap-3 mt-2 text-[11px] text-[#8a8a8a]">
                                    <span>By {{ $note->user?->name ?? 'Advocate' }}</span>
                                    <span>&middot;</span>
                                    <span class="font-mono">{{ $note->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <form method="POST" action="{{ route('notes.toggle-pin', $note) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-[#23493a] transition-colors" title="{{ $note->is_pinned ? 'Unpin' : 'Pin to top' }}">
                                        <span class="material-symbols-outlined text-base">{{ $note->is_pinned ? 'keep_off' : 'push_pin' }}</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Delete this note?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded text-[#8a8a8a] hover:text-rose-600 transition-colors" title="Delete note">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#8a8a8a]">No notes recorded for this matter yet. Click "Add Note" to author internal memos.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right 1-Column: Judicial Information & Team -->
        <div class="flex flex-col gap-6">

            <!-- Forum & Judge Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Judicial Assignment</h3>
                <div class="flex flex-col gap-3 text-xs">
                    <div>
                        <span class="text-[#8a8a8a] block text-[11px]">Court Forum</span>
                        <span class="font-medium text-[#1a1a1a]">{{ $matter->court_name ?? 'Forum Not Assigned' }}</span>
                    </div>
                    <div>
                        <span class="text-[#8a8a8a] block text-[11px]">Presiding Judge</span>
                        <span class="font-medium text-[#1a1a1a]">{{ $matter->judge_name ?? 'Magistrate Pending' }}</span>
                    </div>
                </div>
            </div>

            <!-- Client & Trust Card -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Client Information</h3>
                <div class="flex flex-col gap-2.5 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-[#23493a]">corporate_fare</span>
                        <span class="font-semibold text-sm text-[#1a1a1a]">{{ $matter->client->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="text-[#646864]">
                        <span class="text-[#8a8a8a] block text-[11px]">Primary Contact</span>
                        <span class="text-[#1a1a1a]">{{ $matter->client->contact_person ?? 'N/A' }}</span>
                    </div>
                    <div class="text-[#646864]">
                        <span class="text-[#8a8a8a] block text-[11px]">Email &amp; Direct Phone</span>
                        <span class="text-[#1a1a1a]">{{ $matter->client->email ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Assigned Legal Team -->
            <div class="border border-[#e5e3dc] bg-white rounded-md p-5 shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#8a8a8a] mb-3">Litigation Team</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        @if($matter->leadAttorney?->avatar_url)
                            <img alt="{{ $matter->leadAttorney?->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#e5e3dc]" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                        @else
                            <div class="w-8 h-8 rounded-full bg-[#5b4382] text-white flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs">
                                {{ strtoupper(substr($matter->leadAttorney?->name ?? 'L', 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#1a1a1a]">{{ $matter->leadAttorney?->name ?? 'Lead Attorney' }}</span>
                            <span class="text-[11px] text-[#8a8a8a]">Lead Trial Counsel</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
