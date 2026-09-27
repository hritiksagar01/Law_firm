@extends('layouts.app')

@section('title', 'Case Messages & Communication Threads — Sharma Legal Chambers')
@section('header_title', 'Messages')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" x-data="{ newThreadModal: false, selectedType: 'client_communication' }">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                    Case Messages &amp; Communication Threads
                </h1>
                @if($unreadTotal > 0)
                    <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-mono font-semibold border border-red-200">
                        {{ $unreadTotal }} unread
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Privileged counsel-client matter threads &amp; intra-chamber confidential discussions with technical database isolation.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="px-2.5 py-1 rounded-full bg-[#ecfdf5] text-[#065f46] text-xs font-mono font-medium border border-[#a7f3d0] inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">verified_user</span>
                <span>Evidence Act Sec 126 &amp; 129 Protected</span>
            </span>

            <button @click="newThreadModal = true" class="btn-primary h-8 px-3.5 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <span class="material-symbols-outlined text-base">add_comment</span>
                <span>Start New Thread</span>
            </button>
        </div>
    </div>

    <!-- 3-Pane Console Container -->
    <div class="grid grid-cols-1 lg:grid-cols-12 border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden min-h-[640px]">
        
        <!-- Left 3 Cols: Matter Channels -->
        <div class="lg:col-span-3 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-[#faf9f5] flex flex-col">
            <div class="p-3.5 border-b border-[#e5e3dc] bg-white flex items-center justify-between">
                <span class="text-xs font-semibold text-[#1a1a1a] uppercase font-mono tracking-wider">Matter Channels</span>
                <span class="text-xs text-[#8a8a8a] font-mono">{{ $mattersWithMessages->count() }} active</span>
            </div>

            <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[620px]">
                @forelse($mattersWithMessages as $m)
                @php
                    $isMatSelected = ($selectedMatter && $selectedMatter->id === $m->id);
                @endphp
                <a href="{{ route('messages.index', ['matter_id' => $m->id, 'type' => $typeFilter]) }}"
                   class="p-3.5 block transition-colors {{ $isMatSelected ? 'bg-white border-l-4 border-[#23493a] shadow-xs' : 'hover:bg-white text-[#646864]' }}">
                    <div class="flex items-start justify-between gap-1">
                        <span class="font-mono text-[11px] font-semibold text-[#23493a]">
                            {{ $m->case_number }}
                        </span>
                        @if($m->unread_count > 0)
                            <span class="px-1.5 py-0.2 rounded-full bg-red-500 text-white text-[10px] font-mono font-bold animate-pulse">
                                {{ $m->unread_count }} new
                            </span>
                        @endif
                    </div>
                    <div class="text-[13px] font-semibold text-[#1a1a1a] line-clamp-1 mt-0.5">
                        {{ $m->title }}
                    </div>
                    <div class="text-[11px] text-[#646864] mt-1 flex items-center justify-between">
                        <span class="truncate">{{ $m->client->name ?? 'Client Unassigned' }}</span>
                        <span class="text-[#8a8a8a] font-mono text-[10.5px]">
                            {{ $m->messages()->latest()->first()?->created_at?->format('M j') }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a] flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl mb-1 text-[#e5e3dc]">folder_off</span>
                    <span>No active cases with communications yet.</span>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Middle 4 Cols: Matter Threads List & Filter Pills -->
        <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-white flex flex-col">
            @if($selectedMatter)
            <div class="p-3 border-b border-[#e5e3dc] bg-[#faf9f5] flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[11px] uppercase tracking-wider text-[#8a8a8a] font-semibold">
                        Threads ({{ $threads->count() }})
                    </span>
                    <button @click="newThreadModal = true" class="text-xs text-[#23493a] font-medium hover:underline flex items-center gap-0.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[14px]">add</span>
                        <span>New Thread</span>
                    </button>
                </div>

                <!-- Thread Type Filter Pills -->
                <div class="flex items-center gap-1 overflow-x-auto text-[10.5px] pb-0.5">
                    <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'all']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'all' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        All
                    </a>
                    <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'client_communication']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'client_communication' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        Client
                    </a>
                    <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'document_request']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'document_request' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        Doc Request
                    </a>
                    <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'general_matter_communication']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'general_matter_communication' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        General
                    </a>
                    <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'internal']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'internal' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        🔒 Internal
                    </a>
                </div>
            </div>

            <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[560px] flex-1">
                @forelse($threads as $th)
                @php
                    $isThSelected = ($selectedThread && $selectedThread->id === $th->id);
                    $thTypeBadge = match($th->thread_type) {
                        'internal_team' => 'bg-amber-50 text-amber-900 border-amber-200',
                        'client_communication' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'document_request' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                        default => 'bg-blue-50 text-blue-800 border-blue-200'
                    };
                @endphp
                <a href="{{ route('messages.index', ['matter_id' => $selectedMatter->id, 'thread_id' => $th->id, 'type' => $typeFilter]) }}" 
                   class="p-3.5 block transition-colors {{ $isThSelected ? 'bg-[#faf9f5] border-l-4 border-[#23493a]' : 'hover:bg-[#faf9f5]/60' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $th->thread_number ?? 'THR-'.$th->id }}</span>
                        <div class="flex items-center gap-1">
                            @if($th->is_internal)
                                <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium border bg-amber-50 text-amber-900 border-amber-200 inline-flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[11px]">lock</span>
                                    <span>Internal Chambers</span>
                                </span>
                            @else
                                <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium border {{ $thTypeBadge }}">
                                    {{ $th->type_label }}
                                </span>
                            @endif
                            @if(($th->unread_count ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            @endif
                        </div>
                    </div>
                    <h4 class="text-xs font-semibold text-[#1a1a1a] truncate mt-1 leading-snug">{{ $th->subject }}</h4>
                    <div class="flex items-center justify-between text-[11px] text-[#8a8a8a] mt-1">
                        <span class="truncate">By {{ $th->creator->name ?? 'Staff' }}</span>
                        <span class="font-mono text-[10px]">{{ $th->last_message_at ? $th->last_message_at->format('M j, g:i A') : '' }}</span>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a] flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl mb-1 text-[#e5e3dc]">forum</span>
                    <p>No communication threads under this filter.</p>
                    <button @click="newThreadModal = true" class="mt-2 text-xs text-[#23493a] font-medium hover:underline">
                        Start a new thread &rarr;
                    </button>
                </div>
                @endforelse
            </div>
            @else
            <div class="p-8 text-center text-xs text-[#8a8a8a] flex flex-col items-center justify-center h-full">
                <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2">folder_open</span>
                <p class="font-medium text-[#1a1a1a]">Select a matter on the left to view communication threads.</p>
            </div>
            @endif
        </div>

        <!-- Right 5 Cols: Active Thread Stream & Transmission Console -->
        <div class="lg:col-span-5 flex flex-col justify-between bg-white">
            @if($selectedThread)
                <!-- Active Thread Banner -->
                <div class="p-4 border-b border-[#e5e3dc] bg-[#faf9f5] flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-[#23493a] bg-white border border-[#e5e3dc] px-2 py-0.5 rounded">
                                {{ $selectedThread->thread_number ?? 'THR-'.$selectedThread->id }}
                            </span>
                            @if($selectedThread->is_internal)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-amber-50 text-amber-900 border border-amber-200 inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">lock</span>
                                    <span>Intra-Chambers Confidential</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                                    {{ $selectedThread->type_label }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10.5px] font-mono text-[#8a8a8a]">Status: {{ ucfirst($selectedThread->status) }}</span>
                            <a href="{{ route('matters.show', $selectedMatter->id) }}" class="btn-secondary h-6 px-2 text-[10.5px] inline-flex items-center gap-1">
                                <span>Case Dossier</span>
                                <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                            </a>
                        </div>
                    </div>

                    <h2 class="text-sm font-semibold text-[#1a1a1a] leading-tight">{{ $selectedThread->subject }}</h2>

                    <!-- Technical Database Separation Security Notice -->
                    @if($selectedThread->is_internal)
                    <div class="p-2 rounded bg-amber-50/80 border border-amber-200 text-amber-950 text-[11px] flex items-center gap-1.5 font-mono">
                        <span class="material-symbols-outlined text-[15px] text-amber-700 shrink-0">security</span>
                        <span><strong>Database Isolated:</strong> Excluded from client portal queries. Strictly visible to chambers advocates.</span>
                    </div>
                    @else
                    <div class="p-2 rounded bg-emerald-50/80 border border-emerald-200 text-emerald-950 text-[11px] flex items-center gap-1.5 font-mono">
                        <span class="material-symbols-outlined text-[15px] text-emerald-700 shrink-0">shield_person</span>
                        <span><strong>Client Visible:</strong> Shared with {{ $selectedMatter->client->name ?? 'Client' }} via secure client portal.</span>
                    </div>
                    @endif
                </div>

                <!-- Messages Stream -->
                <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1 max-h-[440px]">
                    @forelse($messages as $msg)
                        @php
                            $isMe = ($msg->sender_id === auth()->id());
                            $senderName = $msg->sender?->name ?? 'Participant';
                            $senderRole = $msg->sender?->role ?? 'advocate';
                            $statusColor = match($msg->status) {
                                'read' => 'text-[#065f46]',
                                'delivered' => 'text-blue-700',
                                default => 'text-[#8a8a8a]'
                            };
                        @endphp
                        <div class="flex items-start gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs {{ $isMe ? 'bg-[#23493a] text-white' : ($senderRole === 'client' ? 'bg-sky-700 text-white' : 'bg-[#161718] text-white') }}">
                                {{ strtoupper(substr($senderName, 0, 2)) }}
                            </div>

                            <div class="flex flex-col max-w-[85%] {{ $isMe ? 'items-end' : '' }}">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="font-mono text-[9.5px] bg-[#f5f3ed] border border-[#e5e3dc] px-1 py-0.2 rounded text-[#8a8a8a]">
                                        {{ $msg->formatted_id }}
                                    </span>
                                    <span class="text-xs font-semibold text-[#1a1a1a]">
                                        {{ $isMe ? 'You' : $senderName }}
                                    </span>
                                    <span class="text-[10px] font-mono px-1 py-0.2 rounded {{ $senderRole === 'client' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-[#f5f3ed] text-[#23493a]' }}">
                                        {{ ucfirst($senderRole) }}
                                    </span>
                                    @if($msg->recipient)
                                        <span class="text-[10.5px] text-[#8a8a8a]">&rarr; {{ $msg->recipient->name }}</span>
                                    @endif
                                    <span class="font-mono text-[10px] text-[#8a8a8a]">
                                        {{ ($msg->sent_at ?? $msg->created_at)->format('d M, h:i A') }}
                                    </span>
                                </div>

                                <div class="p-3 rounded-md text-xs leading-relaxed shadow-xs {{ $isMe ? 'bg-[#23493a] text-white rounded-tr-none' : 'bg-[#faf8f5] text-[#1a1a1a] rounded-tl-none border border-[#e5e3dc]' }}">
                                    @if($msg->subject && $msg->subject !== $selectedThread->subject)
                                        <div class="font-semibold mb-1 pb-1 border-b {{ $isMe ? 'border-[#3a6352]' : 'border-[#e5e3dc]' }}">
                                            {{ $msg->subject }}
                                        </div>
                                    @endif

                                    <p class="whitespace-pre-line">{{ $msg->body }}</p>

                                    <!-- Attachments -->
                                    @if($msg->attachments->count() > 0 || $msg->attachment_path)
                                    <div class="mt-2.5 pt-2 border-t {{ $isMe ? 'border-[#3a6352]' : 'border-[#e5e3dc]' }} flex flex-col gap-1.5">
                                        <span class="text-[10px] uppercase font-mono tracking-wider {{ $isMe ? 'text-[#a7f3d0]' : 'text-[#8a8a8a]' }}">
                                            Attachments ({{ $msg->attachments->count() ?: 1 }})
                                        </span>
                                        @foreach($msg->attachments as $att)
                                        <a href="{{ route('messages.attachments.download', $att->id) }}" 
                                           class="inline-flex items-center justify-between gap-2 p-1.5 rounded text-[11px] transition-colors {{ $isMe ? 'bg-[#1a382b] text-white hover:bg-[#152e23]' : 'bg-white border border-[#e5e3dc] text-[#23493a] hover:bg-[#faf9f5]' }}">
                                            <span class="flex items-center gap-1.5 truncate">
                                                <span class="material-symbols-outlined text-[14px]">attach_file</span>
                                                <span class="truncate">{{ $att->file_name }}</span>
                                            </span>
                                            <span class="font-mono text-[9.5px] opacity-75 shrink-0">{{ $att->formattedSize() }}</span>
                                        </a>
                                        @endforeach

                                        @if($msg->attachments->isEmpty() && $msg->attachment_path)
                                        <div class="text-[11px] flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[14px]">attach_file</span>
                                            <span>{{ $msg->attachment_name ?? 'Attachment file' }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Read / Delivery Status Indicator -->
                                <div class="flex items-center gap-2 mt-1 text-[10px] font-mono {{ $statusColor }}">
                                    @if($msg->is_read)
                                        <span class="inline-flex items-center gap-0.5 text-[#065f46]">
                                            <span class="material-symbols-outlined text-[13px]">done_all</span>
                                            <span>Read {{ $msg->read_at ? $msg->read_at->format('d M, h:i A') : '' }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-[13px]">check</span>
                                            <span>{{ ucfirst($msg->status ?? 'Sent') }}</span>
                                        </span>
                                    @endif
                                    @if($msg->is_privileged)
                                        <span class="text-[#8a8a8a] uppercase font-mono tracking-wider">&middot; ⚖️ Privileged</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-xs text-[#8a8a8a] flex flex-col items-center">
                            <span class="material-symbols-outlined text-3xl mb-1 text-[#e5e3dc]">chat_bubble_outline</span>
                            <p>No dispatches in this thread yet. Author a privileged communication below.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Reply Composer -->
                <div class="p-3.5 sm:p-4 border-t border-[#e5e3dc] bg-[#faf9f5]">
                    <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2.5">
                        @csrf
                        <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>
                        <input type="hidden" name="thread_id" value="{{ $selectedThread->id }}"/>

                        @if($selectedThread->is_internal)
                        <div class="flex items-center gap-2">
                            <label class="text-[10.5px] font-mono uppercase text-[#646864] font-semibold shrink-0">Direct Recipient:</label>
                            <select name="recipient_id" class="p-1.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] flex-1">
                                <option value="">All Matter Advocates / Chambers Team</option>
                                @foreach($matterUsers as $mu)
                                    @if($mu->id !== auth()->id())
                                        <option value="{{ $mu->id }}">{{ $mu->name }} ({{ ucfirst($mu->role ?? 'Advocate') }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="recipient_id" value="{{ $selectedMatter->client?->user_id }}"/>
                        @endif

                        <textarea name="body" required rows="2" placeholder="Compose privileged dispatch on this thread..."
                                  class="w-full p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] resize-none shadow-xs"></textarea>

                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-1 text-[11px] text-[#646864] hover:text-[#1a1a1a] cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px] text-[#23493a]">attach_file</span>
                                    <span>Attach Files</span>
                                    <input type="file" name="attachments[]" multiple class="hidden" onchange="document.getElementById('staff-attach-counter').innerText = this.files.length + ' file(s) selected'"/>
                                </label>
                                <span id="staff-attach-counter" class="text-[10.5px] font-mono text-[#8a8a8a]"></span>

                                <label class="inline-flex items-center gap-1 text-[11px] text-[#646864] cursor-pointer">
                                    <input type="checkbox" name="is_privileged" value="1" checked class="rounded text-[#23493a] focus:ring-[#23493a]"/>
                                    <span>Privileged</span>
                                </label>
                            </div>

                            <button type="submit" class="btn-primary h-8 px-4 text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Send Dispatch</span>
                                <span class="material-symbols-outlined text-sm">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            @elseif($selectedMatter)
                <div class="flex flex-col items-center justify-center h-full p-8 text-center text-xs text-[#8a8a8a]">
                    <span class="material-symbols-outlined text-4xl text-[#e5e3dc] mb-2">forum</span>
                    <p class="font-medium text-[#1a1a1a]">Select a thread from the list or start a new thread for this case.</p>
                    <button @click="newThreadModal = true" class="mt-3 btn-primary h-8 px-3 text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add_comment</span>
                        <span>Start New Thread</span>
                    </button>
                </div>
            @else
                <div class="p-12 text-center text-xs text-[#8a8a8a] flex flex-col items-center justify-center h-full">
                    <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2">forum</span>
                    <p class="font-medium text-[#1a1a1a]">Select a case channel on the left to read or author dispatches.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- Start New Thread Modal -->
    <div x-show="newThreadModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="newThreadModal = false" class="bg-white border border-[#e5e3dc] rounded-lg max-w-lg w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">add_comment</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Start Matter Message Thread</h3>
                </div>
                <button @click="newThreadModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('messages.threads.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                @csrf

                <!-- Matter Picker -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Matter Docket *</label>
                    <select name="matter_id" required class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                        @foreach($allMatters as $am)
                            <option value="{{ $am->id }}" {{ ($selectedMatter && $selectedMatter->id === $am->id) ? 'selected' : '' }}>
                                {{ $am->case_number }} &middot; {{ $am->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Thread Type Selector with Technical Database Separation -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Thread Type *</label>
                    <select name="thread_type" x-model="selectedType" required class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                        <option value="client_communication">Client Communication (Client Visible)</option>
                        <option value="document_request">Document Request (Client Visible)</option>
                        <option value="general_matter_communication">General Matter Communication (Client Visible)</option>
                        <option value="internal_team">🔒 Internal Chambers Communication (Strictly Staff Only)</option>
                    </select>
                </div>

                <!-- Dynamic Technical Separation Notice -->
                <div x-show="selectedType === 'internal_team'" class="p-2.5 rounded bg-amber-50 border border-amber-200 text-amber-950 text-xs flex items-start gap-2">
                    <span class="material-symbols-outlined text-amber-700 text-base shrink-0 mt-0.5">lock</span>
                    <span class="leading-relaxed">
                        <strong>Technical Database Separation:</strong> Internal communications are strictly restricted through database-level query scopes and authorization checks. Clients cannot view, query, or download files from this thread.
                    </span>
                </div>

                <div x-show="selectedType !== 'internal_team'" class="p-2.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs flex items-start gap-2">
                    <span class="material-symbols-outlined text-emerald-700 text-base shrink-0 mt-0.5">shield_person</span>
                    <span class="leading-relaxed">
                        <strong>Client Accessible:</strong> This thread will be delivered to the client portal under Section 126 &amp; 129 privileged legal dispatch protections.
                    </span>
                </div>

                <!-- Recipient Selector (Optional for staff) -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Specific Recipient (Optional)</label>
                    <select name="recipient_id" class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                        <option value="">Default (Client for client threads / Team for internal)</option>
                        @if($selectedMatter && $selectedMatter->client?->user)
                            <option value="{{ $selectedMatter->client->user_id }}">Client: {{ $selectedMatter->client->name }}</option>
                        @endif
                        @foreach($matterUsers as $mu)
                            @if($mu->id !== auth()->id())
                                <option value="{{ $mu->id }}">Advocate: {{ $mu->name }} ({{ ucfirst($mu->role ?? 'Staff') }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Subject -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Subject *</label>
                    <input type="text" name="subject" required placeholder="e.g. Discovery Production Review or Client Briefing" class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                </div>

                <!-- Initial Message Body -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Initial Message *</label>
                    <textarea name="body" required rows="4" placeholder="Detail the initial communication, request, or confidential intra-chamber memorandum..." class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none resize-none"></textarea>
                </div>

                <!-- Attachments -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Attachments (Optional)</label>
                    <input type="file" name="attachments[]" multiple class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#faf9f5] file:text-[#23493a] file:font-semibold hover:file:bg-[#e5e3dc]"/>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="newThreadModal = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs hover:bg-[#faf9f5]">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary px-4 py-2 text-xs font-medium rounded-md shadow-xs">
                        Open Thread
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
