@extends('layouts.app')

@section('title', 'Case Dispatches & Messages — Sharma Legal Chambers')
@section('header_title', 'Messages')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                    Case Messages &amp; Dispatches
                </h1>
                @if($unreadTotal > 0)
                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-mono font-semibold">
                        {{ $unreadTotal }} unread
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Secure, end-to-end privileged counsel-client and intra-chamber case dispatches.
            </p>
        </div>
    </div>

    <!-- 2-Column Split View: Matter List on Left, Thread on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden min-h-[600px]">
        
        <!-- Left 4 Cols: Matter Channels -->
        <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-[#e5e3dc] bg-[#faf8f5] flex flex-col">
            <div class="p-3 border-b border-[#e5e3dc] flex items-center justify-between bg-white">
                <span class="text-xs font-semibold text-[#1a1a1a] uppercase font-mono tracking-wider">Case Threads</span>
                <span class="text-xs text-[#8a8a8a] font-mono">{{ $mattersWithMessages->count() }} active</span>
            </div>

            <div class="divide-y divide-[#f0eee8] overflow-y-auto max-h-[600px]">
                @forelse($mattersWithMessages as $m)
                <a href="{{ route('messages.index', ['matter_id' => $m->id]) }}"
                   class="p-3.5 block transition-colors {{ $selectedMatter && $selectedMatter->id === $m->id ? 'bg-white border-l-4 border-[#23493a] shadow-xs' : 'hover:bg-white' }}">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-mono text-[11px] text-[#23493a] font-semibold">
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
                        <span>{{ $m->client->name ?? 'Client' }}</span>
                        <span class="text-[#8a8a8a] font-mono">{{ $m->messages()->latest()->first()?->created_at?->format('M j') }}</span>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-xs text-[#8a8a8a]">
                    No messages yet on active cases.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right 8 Cols: Active Thread -->
        <div class="lg:col-span-8 flex flex-col justify-between bg-white">
            @if($selectedMatter)
                <!-- Thread Header -->
                <div class="p-4 border-b border-[#e5e3dc] flex items-center justify-between bg-[#faf8f5]">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-[#23493a] font-semibold">{{ $selectedMatter->case_number }}</span>
                            <span class="text-xs text-[#8a8a8a]">&middot;</span>
                            <h2 class="text-sm font-semibold text-[#1a1a1a]">{{ $selectedMatter->title }}</h2>
                        </div>
                        <p class="text-xs text-[#646864] mt-0.5">
                            Client: <a href="{{ $selectedMatter->client ? route('clients.show', $selectedMatter->client->id) : '#' }}" class="hover:underline font-medium text-[#1a1a1a]">{{ $selectedMatter->client->name ?? 'Unassigned' }}</a>
                            @if($selectedMatter->leadAttorney)
                                &middot; Lead Counsel: <span class="font-medium text-[#1a1a1a]">{{ $selectedMatter->leadAttorney->name }}</span>
                            @endif
                        </p>
                    </div>

                    <a href="{{ route('matters.show', $selectedMatter->id) }}" class="btn-secondary h-7 px-2.5 text-[11px] inline-flex items-center gap-1">
                        <span>Open Case</span>
                        <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                    </a>
                </div>

                <!-- Messages Feed -->
                <div class="p-5 overflow-y-auto space-y-4 flex-1 max-h-[460px]">
                    @forelse($messages as $msg)
                        @php
                            $isMe = $msg->sender_id === auth()->id();
                            $senderName = $msg->sender?->name ?? 'Participant';
                            $senderRole = $msg->sender?->role ?? 'advocate';
                        @endphp
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[11px] font-semibold {{ $isMe ? 'text-[#23493a]' : 'text-[#1a1a1a]' }}">
                                    {{ $isMe ? 'You' : $senderName }}
                                </span>
                                <span class="text-[10px] font-mono px-1 py-0.2 rounded {{ $senderRole === 'client' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-[#f5f3ed] text-[#23493a]' }}">
                                    {{ ucfirst($senderRole) }}
                                </span>
                                <span class="text-[10px] font-mono text-[#8a8a8a]">{{ $msg->created_at->format('M j, g:i A') }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg max-w-lg text-xs leading-relaxed {{ $isMe ? 'bg-[#23493a] text-white rounded-tr-none' : 'bg-[#faf8f5] text-[#1a1a1a] border border-[#e5e3dc] rounded-tl-none' }}">
                                <p class="whitespace-pre-wrap">{{ $msg->body }}</p>
                            </div>
                            @if($msg->is_privileged)
                                <span class="text-[9.5px] text-[#8a8a8a] uppercase font-mono tracking-wider mt-0.5">⚖️ Privileged &amp; Confidential</span>
                            @endif
                        </div>
                    @empty
                        <div class="py-12 text-center text-xs text-[#8a8a8a]">
                            No previous dispatches in this matter thread. Author a privileged communication below.
                        </div>
                    @endforelse
                </div>

                <!-- Reply Composer -->
                <div class="p-4 border-t border-[#e5e3dc] bg-[#faf8f5]">
                    <form action="{{ route('messages.store') }}" method="POST" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>
                        <textarea name="body" required rows="3" placeholder="Compose privileged legal dispatch to client or co-counsel..."
                                  class="w-full p-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"></textarea>
                        
                        <div class="flex items-center justify-between pt-1">
                            <label class="inline-flex items-center gap-1.5 text-xs text-[#646864] cursor-pointer">
                                <input type="checkbox" name="is_privileged" value="1" checked class="rounded text-[#23493a] focus:ring-[#23493a]"/>
                                <span>Attorney-Client Privileged Communication</span>
                            </label>

                            <button type="submit" class="btn-primary h-8 px-4 text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Send Dispatch</span>
                                <span class="material-symbols-outlined text-[14px]">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-12 text-center text-xs text-[#8a8a8a] flex flex-col items-center justify-center h-full">
                    <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2">forum</span>
                    <p class="font-medium text-[#1a1a1a]">Select a case thread on the left to read or author dispatches.</p>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
