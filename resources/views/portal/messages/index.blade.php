@extends('portal.layout')

@section('title', 'Client Communication Threads')
@section('header_title', 'Privileged Communications')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6" x-data="{ newThreadModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Privileged Communications</h1>
            <p class="text-[13px] text-[#646864] mt-1">Matter-based confidential counsel threads, document requests &amp; case dispatches.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="px-2.5 py-1 rounded-full bg-[#ecfdf5] text-[#065f46] text-xs font-mono font-medium border border-[#a7f3d0]">
                Section 126 &amp; 129 Evidence Act Protected
            </span>
            @if($selectedMatter)
            <button @click="newThreadModal = true" class="px-3.5 py-1.5 bg-[#23493a] hover:bg-[#1a382b] text-white text-xs font-medium rounded-md shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-base">add_comment</span>
                <span>New Thread</span>
            </button>
            @endif
        </div>
    </div>

    <!-- 3-Pane Console Container -->
    <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs grid grid-cols-1 lg:grid-cols-12 min-h-[620px] overflow-hidden">
        
        <!-- Left 3 Cols: Matters List -->
        <div class="lg:col-span-3 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-[#faf9f5] flex flex-col">
            <div class="p-3.5 border-b border-[#e5e3dc] bg-white flex items-center justify-between">
                <span class="font-mono text-[10.5px] uppercase tracking-wider text-[#8a8a8a] font-semibold">My Cases</span>
                <span class="text-[10.5px] font-mono text-[#646864]">{{ $matters->count() }} Dockets</span>
            </div>

            <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[600px]">
                @forelse($matters as $m)
                @php
                    $isMatSelected = ($selectedMatter && $selectedMatter->id === $m->id);
                @endphp
                <a href="{{ route('portal.messages.index', ['matter_id' => $m->id]) }}" 
                   class="p-3.5 block transition-colors {{ $isMatSelected ? 'bg-white border-l-4 border-[#23493a] shadow-xs' : 'hover:bg-white text-[#646864]' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-mono text-[11px] font-semibold text-[#23493a]">{{ $m->case_number }}</span>
                        @if($m->unread_count > 0)
                            <span class="px-1.5 py-0.2 rounded-full bg-[#ba1a1a] text-white text-[10px] font-mono font-bold">
                                {{ $m->unread_count }}
                            </span>
                        @endif
                    </div>
                    <div class="text-xs font-semibold text-[#1a1a1a] truncate mt-1">{{ $m->title }}</div>
                    <div class="text-[11px] text-[#8a8a8a] mt-0.5 truncate">Counsel: {{ $m->leadAttorney->name ?? 'Chambers Counsel' }}</div>
                </a>
                @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a]">
                    No active matters available.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Middle 3 Cols: Matter Threads List & Thread Type Filter -->
        <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-white flex flex-col">
            
            @if($selectedMatter)
            <div class="p-3 border-b border-[#e5e3dc] bg-[#faf9f5] flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10.5px] uppercase tracking-wider text-[#8a8a8a] font-semibold">Threads ({{ $threads->count() }})</span>
                    <button @click="newThreadModal = true" class="text-xs text-[#23493a] font-medium hover:underline flex items-center gap-0.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[14px]">add</span>
                        <span>Start Thread</span>
                    </button>
                </div>

                <!-- Thread Type Filter Pills -->
                <div class="flex items-center gap-1 overflow-x-auto text-[10.5px] pb-1">
                    <a href="{{ route('portal.messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'all']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'all' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        All
                    </a>
                    <a href="{{ route('portal.messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'client_communication']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'client_communication' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        Client
                    </a>
                    <a href="{{ route('portal.messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'document_request']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'document_request' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        Doc Request
                    </a>
                    <a href="{{ route('portal.messages.index', ['matter_id' => $selectedMatter->id, 'type' => 'general_matter_communication']) }}" 
                       class="px-2 py-0.5 rounded-full font-mono transition-colors whitespace-nowrap {{ $typeFilter === 'general_matter_communication' ? 'bg-[#23493a] text-white font-medium' : 'bg-white border border-[#e5e3dc] text-[#646864] hover:text-[#1a1a1a]' }}">
                        General
                    </a>
                </div>
            </div>

            <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[550px] flex-1">
                @forelse($threads as $th)
                @php
                    $isThSelected = ($selectedThread && $selectedThread->id === $th->id);
                    $thTypeBadge = match($th->thread_type) {
                        'client_communication' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'document_request' => 'bg-amber-50 text-amber-800 border-amber-200',
                        default => 'bg-blue-50 text-blue-800 border-blue-200'
                    };
                @endphp
                <a href="{{ route('portal.messages.index', ['matter_id' => $selectedMatter->id, 'thread_id' => $th->id, 'type' => $typeFilter]) }}" 
                   class="p-3.5 block transition-colors {{ $isThSelected ? 'bg-[#faf9f5] border-l-4 border-[#23493a]' : 'hover:bg-[#faf9f5]/60' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-mono text-[10px] text-[#8a8a8a]">{{ $th->thread_number ?? 'THR-'.$th->id }}</span>
                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-medium border {{ $thTypeBadge }}">
                            {{ $th->type_label }}
                        </span>
                    </div>
                    <h4 class="text-xs font-semibold text-[#1a1a1a] truncate mt-1 leading-snug">{{ $th->subject }}</h4>
                    <div class="flex items-center justify-between text-[11px] text-[#8a8a8a] mt-1">
                        <span class="truncate">By {{ $th->creator->name ?? 'Counsel' }}</span>
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
            <div class="p-8 text-center text-xs text-[#8a8a8a]">Select a matter on the left.</div>
            @endif

        </div>

        <!-- Right 5 Cols: Active Thread Messages Stream & Transmission Console -->
        <div class="lg:col-span-5 flex flex-col justify-between bg-white">
            
            @if($selectedThread)
            <!-- Active Thread Banner -->
            <div class="p-4 border-b border-[#e5e3dc] bg-[#faf9f5] flex flex-col gap-1.5">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-semibold text-[#23493a] bg-white border border-[#e5e3dc] px-2 py-0.5 rounded">
                            {{ $selectedThread->thread_number ?? 'THR-'.$selectedThread->id }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                            {{ $selectedThread->type_label }}
                        </span>
                    </div>
                    <span class="text-[10.5px] font-mono text-[#8a8a8a]">Status: {{ ucfirst($selectedThread->status) }}</span>
                </div>
                <h2 class="text-sm font-semibold text-[#1a1a1a] leading-tight">{{ $selectedThread->subject }}</h2>
                <span class="text-[11px] text-[#646864] font-mono">{{ $selectedMatter->case_number }} &middot; {{ $selectedMatter->title }}</span>
            </div>

            <!-- Messages Stream -->
            <div class="p-4 sm:p-5 flex-1 overflow-y-auto flex flex-col gap-4 max-h-[440px]">
                @forelse($messages as $msg)
                @php
                    $isSenderClient = ($msg->sender_id === auth()->id());
                    $statusColor = match($msg->status) {
                        'read' => 'text-[#065f46]',
                        'delivered' => 'text-blue-700',
                        default => 'text-[#8a8a8a]'
                    };
                @endphp
                <div class="flex items-start gap-3 {{ $isSenderClient ? 'flex-row-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white' : 'bg-[#161718] text-white' }}">
                        {{ strtoupper(substr($msg->sender->name ?? 'User', 0, 2)) }}
                    </div>
                    
                    <div class="flex flex-col max-w-[85%] {{ $isSenderClient ? 'items-end' : '' }}">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="font-mono text-[9.5px] text-[#8a8a8a]">{{ $msg->formatted_id }}</span>
                            <span class="text-xs font-semibold text-[#1a1a1a]">{{ $msg->sender->name ?? 'Counsel' }}</span>
                            @if($msg->recipient)
                                <span class="text-[10px] text-[#8a8a8a]">&rarr; {{ $msg->recipient->name }}</span>
                            @endif
                            <span class="font-mono text-[10px] text-[#8a8a8a]">
                                {{ ($msg->sent_at ?? $msg->created_at)->format('d M, h:i A') }}
                            </span>
                        </div>

                        <div class="p-3 rounded-md text-xs leading-relaxed shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white rounded-tr-none' : 'bg-[#faf9f5] text-[#1a1a1a] rounded-tl-none border border-[#e5e3dc]' }}">
                            <p class="whitespace-pre-line">{{ $msg->body }}</p>

                            <!-- Attachments -->
                            @if($msg->attachments->count() > 0 || $msg->attachment_path)
                            <div class="mt-2.5 pt-2 border-t {{ $isSenderClient ? 'border-[#3a6352]' : 'border-[#e5e3dc]' }} flex flex-col gap-1.5">
                                <span class="text-[10px] uppercase font-mono tracking-wider {{ $isSenderClient ? 'text-[#a7f3d0]' : 'text-[#8a8a8a]' }}">
                                    Attachments ({{ $msg->attachments->count() ?: 1 }})
                                </span>
                                @foreach($msg->attachments as $att)
                                <a href="{{ route('portal.messages.attachments.download', $att->id) }}" 
                                   class="inline-flex items-center justify-between gap-2 p-1.5 rounded text-[11px] transition-colors {{ $isSenderClient ? 'bg-[#1a382b] text-white hover:bg-[#152e23]' : 'bg-white border border-[#e5e3dc] text-[#23493a] hover:bg-[#faf9f5]' }}">
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
                        <div class="flex items-center gap-1 mt-1 text-[10px] font-mono {{ $statusColor }}">
                            @if($msg->is_read)
                                <span class="material-symbols-outlined text-[13px]">done_all</span>
                                <span>Read {{ $msg->read_at ? $msg->read_at->format('d M, h:i A') : '' }}</span>
                            @else
                                <span class="material-symbols-outlined text-[13px]">check</span>
                                <span>{{ ucfirst($msg->status ?? 'Sent') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center text-[#8a8a8a] flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl mb-1 text-[#e5e3dc]">chat_bubble_outline</span>
                    <p class="text-xs">No messages in this thread yet. Send a reply below.</p>
                </div>
                @endforelse
            </div>

            <!-- Message Reply Box with Attachments -->
            <div class="p-3.5 sm:p-4 border-t border-[#e5e3dc] bg-[#faf9f5]">
                <form action="{{ route('portal.messages.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2.5">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>
                    <input type="hidden" name="thread_id" value="{{ $selectedThread->id }}"/>

                    <textarea name="body" rows="2" required placeholder="Type reply to chambers counsel on this thread..." class="w-full p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] resize-none shadow-xs"></textarea>

                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <label class="inline-flex items-center gap-1 text-[11px] text-[#646864] hover:text-[#1a1a1a] cursor-pointer">
                            <span class="material-symbols-outlined text-[16px] text-[#23493a]">attach_file</span>
                            <span>Add Attachments</span>
                            <input type="file" name="attachments[]" multiple class="hidden" onchange="document.getElementById('attach-counter').innerText = this.files.length + ' file(s) selected'"/>
                        </label>
                        <span id="attach-counter" class="text-[10.5px] font-mono text-[#8a8a8a]"></span>

                        <button type="submit" class="px-4 py-2 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <span>Send Reply</span>
                            <span class="material-symbols-outlined text-sm">send</span>
                        </button>
                    </div>
                </form>
            </div>
            @elseif($selectedMatter)
            <div class="flex flex-col items-center justify-center h-full p-8 text-center text-xs text-[#8a8a8a]">
                <span class="material-symbols-outlined text-4xl text-[#e5e3dc] mb-2">forum</span>
                <p class="font-medium text-[#1a1a1a]">Select a thread from the list or start a new conversation.</p>
                <button @click="newThreadModal = true" class="mt-3 px-3 py-1.5 bg-[#23493a] text-white text-xs font-medium rounded-md shadow-xs hover:bg-[#1a382b] transition-colors">
                    Start New Thread
                </button>
            </div>
            @else
            <div class="flex items-center justify-center h-full p-8 text-xs text-[#8a8a8a]">
                Select a case channel on the left.
            </div>
            @endif

        </div>

    </div>

    <!-- Start New Thread Modal -->
    @if($selectedMatter)
    <div x-show="newThreadModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="newThreadModal = false" class="bg-white border border-[#e5e3dc] rounded-lg max-w-lg w-full p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a]">add_comment</span>
                    <h3 class="text-sm font-semibold text-[#1a1a1a]">Start New Communication Thread</h3>
                </div>
                <button @click="newThreadModal = false" class="text-[#8a8a8a] hover:text-[#1a1a1a] cursor-pointer">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form action="{{ route('portal.messages.threads.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Matter Docket</label>
                    <input type="text" readonly value="{{ $selectedMatter->case_number }} &middot; {{ $selectedMatter->title }}" class="p-2.5 rounded-md border border-[#e5e3dc] bg-[#faf9f5] text-xs font-medium text-[#1a1a1a]"/>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Thread Type *</label>
                    <select name="thread_type" required class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none">
                        <option value="client_communication">Client Communication</option>
                        <option value="document_request">Document Request</option>
                        <option value="general_matter_communication">General Matter Communication</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Subject *</label>
                    <input type="text" name="subject" required placeholder="e.g. Urgent Inquiry regarding Notice Returnable" class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none"/>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Initial Message *</label>
                    <textarea name="body" required rows="4" placeholder="Detail your communication or query for chambers counsel..." class="p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] outline-none resize-none"></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-mono uppercase tracking-wider text-[#646864] font-semibold">Attachments (Optional)</label>
                    <input type="file" name="attachments[]" multiple class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#faf9f5] file:text-[#23493a] file:font-semibold hover:file:bg-[#e5e3dc]"/>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#f0eee8]">
                    <button type="button" @click="newThreadModal = false" class="bg-white border border-[#e5e3dc] text-[#1a1a1a] px-3.5 py-2 text-xs font-medium rounded-md shadow-xs hover:bg-[#faf9f5]">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs">
                        Open Thread
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
