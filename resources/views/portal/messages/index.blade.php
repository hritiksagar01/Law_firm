@extends('portal.layout')

@section('title', 'Counsel Communications')
@section('header_title', 'Privileged Messages')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Privileged Counsel Communications</h1>
            <p class="text-[13px] text-[#646864] mt-1">Direct, confidential messaging channel between your organization and assigned advocates.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full bg-[#ecfdf5] text-[#065f46] text-xs font-mono font-medium border border-[#a7f3d0]">
                Section 126/129 Protected
            </span>
        </div>
    </div>

    <!-- Messages 2-Pane Console Container -->
    <div class="bg-white rounded-md border border-[#e5e3dc] shadow-xs grid grid-cols-1 md:grid-cols-3 min-h-[550px] overflow-hidden">
        
        <!-- Left: Cases List Sidebar -->
        <div class="border-r border-[#e5e3dc] p-3.5 bg-[#faf9f5] flex flex-col gap-2">
            <span class="font-mono text-[10.5px] uppercase tracking-wider text-[#8a8a8a] font-medium px-1">Case Channels</span>
            
            <div class="flex flex-col gap-1.5 mt-0.5">
                @forelse($matters as $m)
                @php
                    $isSelected = ($selectedMatter && $selectedMatter->id === $m->id);
                @endphp
                <a href="{{ route('portal.messages.index', ['matter_id' => $m->id]) }}" class="p-3 rounded-md border transition-all flex flex-col gap-1 {{ $isSelected ? 'bg-white border-[#23493a] shadow-xs' : 'bg-transparent border-transparent hover:bg-white/80 text-[#646864]' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10.5px] font-semibold text-[#23493a]">{{ $m->case_number }}</span>
                        <span class="text-[10.5px] font-mono text-[#8a8a8a]">{{ $m->messages->count() }} msgs</span>
                    </div>
                    <span class="text-xs font-semibold text-[#1a1a1a] truncate">{{ $m->title }}</span>
                    <span class="text-[11px] text-[#8a8a8a] truncate">Lead: {{ $m->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                </a>
                @empty
                <p class="text-xs text-[#8a8a8a] p-3 text-center">No cases found.</p>
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Feed -->
        <div class="md:col-span-2 flex flex-col justify-between bg-white">
            
            @if($selectedMatter)
            <!-- Active Channel Header -->
            <div class="p-4 border-b border-[#f0eee8] bg-[#faf9f5]/60 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[11px] text-[#23493a] font-semibold">CNR: {{ $selectedMatter->case_number }}</span>
                    <h2 class="text-sm font-semibold text-[#1a1a1a]">{{ $selectedMatter->title }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[#646864]">Lead: <strong class="text-[#1a1a1a] font-medium">{{ $selectedMatter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</strong></span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="p-6 flex-1 overflow-y-auto flex flex-col gap-4 max-h-[420px]">
                @forelse($selectedMatter->messages as $msg)
                @php
                    $isSenderClient = ($msg->sender_id === auth()->id());
                @endphp
                <div class="flex items-start gap-3 {{ $isSenderClient ? 'flex-row-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs shrink-0 shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white' : 'bg-[#161718] text-white' }}">
                        {{ strtoupper(substr($msg->sender->name ?? 'User', 0, 2)) }}
                    </div>
                    
                    <div class="flex flex-col max-w-md {{ $isSenderClient ? 'items-end' : '' }}">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-medium text-[#1a1a1a]">{{ $msg->sender->name ?? 'Counsel' }}</span>
                            <span class="font-mono text-[10px] text-[#8a8a8a]">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M, h:i A') }}</span>
                        </div>
                        <div class="p-3 rounded-md text-xs leading-relaxed shadow-xs {{ $isSenderClient ? 'bg-[#23493a] text-white rounded-tr-none' : 'bg-[#faf9f5] text-[#1a1a1a] rounded-tl-none border border-[#e5e3dc]' }}">
                            {{ $msg->body }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center text-[#8a8a8a] flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl mb-2 text-[#e5e3dc]">chat_bubble_outline</span>
                    <p class="text-xs">No prior messages for this case channel. Dispatch a message to counsel below.</p>
                </div>
                @endforelse
            </div>

            <!-- Message Input Form -->
            <div class="p-4 border-t border-[#f0eee8] bg-[#faf9f5]">
                <form action="{{ route('portal.messages.store') }}" method="POST" class="flex items-end gap-2.5">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>
                    
                    <div class="flex-1 flex flex-col gap-1">
                        <textarea name="body" rows="2" required placeholder="Type your confidential query or reply for counsel..." class="w-full p-2.5 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] outline-none focus:border-[#23493a] resize-none shadow-xs"></textarea>
                    </div>

                    <button type="submit" class="px-4 py-2.5 rounded-md bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382b] transition-colors shadow-xs shrink-0 flex items-center gap-1.5 h-[48px] cursor-pointer">
                        <span>Send</span>
                        <span class="material-symbols-outlined text-base">send</span>
                    </button>
                </form>
            </div>
            @else
            <div class="flex items-center justify-center h-full p-8 text-xs text-[#8a8a8a]">
                Select a case channel from the left to view privileged communications.
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
