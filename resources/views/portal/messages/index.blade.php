@extends('portal.layout')

@section('title', 'Counsel Messages')

@section('content')
<div class="flex flex-col gap-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-[#222222]">Privileged Counsel Communications</h1>
            <p class="text-xs text-[#766A5E] mt-1">Direct, confidential messaging channel between your organization and assigned advocates.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded bg-[#F4ECE1]/50 text-[#9F8349] text-xs font-mono font-medium border border-[#F4ECE1]">
                Confidential Communication
            </span>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-sm grid grid-cols-1 md:grid-cols-3 min-h-[550px] overflow-hidden">
        
        <!-- Left: Cases List Sidebar -->
        <div class="border-r border-[#EFECE6] p-4 bg-[#FAF8F5] flex flex-col gap-2">
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#766A5E] font-semibold px-2">Select Case Channel</span>
            
            <div class="flex flex-col gap-1 mt-1">
                @foreach($matters as $m)
                @php
                    $isSelected = ($selectedMatter && $selectedMatter->id === $m->id);
                @endphp
                <a href="{{ route('portal.messages.index', ['matter_id' => $m->id]) }}" class="p-3 rounded-xl border transition-all flex flex-col gap-1 {{ $isSelected ? 'bg-white border-[#9F8349] shadow-sm' : 'bg-transparent border-transparent hover:bg-white/60 text-[#554D45]' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10px] font-bold text-[#9F8349]">{{ $m->case_number }}</span>
                        <span class="text-[10px] text-[#766A5E]">{{ $m->messages->count() }} msgs</span>
                    </div>
                    <span class="text-xs font-semibold text-[#222222] truncate">{{ $m->title }}</span>
                    <span class="text-[10px] text-[#766A5E] truncate">Lead: {{ $m->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</span>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Right: Active Chat Feed -->
        <div class="md:col-span-2 flex flex-col justify-between bg-white">
            
            @if($selectedMatter)
            <!-- Active Channel Header -->
            <div class="p-4 border-b border-[#EFECE6] bg-[#FAF8F5]/40 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] text-[#9F8349] font-bold">{{ $selectedMatter->case_number }}</span>
                    <h2 class="text-sm font-bold text-[#222222]">{{ $selectedMatter->title }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[#766A5E]">Lead: <strong>{{ $selectedMatter->leadAttorney->name ?? 'Adv. Rajesh Sharma' }}</strong></span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="p-6 flex-1 overflow-y-auto flex flex-col gap-4 max-h-[420px]">
                @forelse($selectedMatter->messages as $msg)
                @php
                    $isSenderClient = ($msg->sender_id === auth()->id());
                @endphp
                <div class="flex items-start gap-3 {{ $isSenderClient ? 'flex-row-reverse' : '' }}">
                    <img alt="Sender" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#EAE4DC] shrink-0" src="{{ $msg->sender->avatar_url ?? 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=100&auto=format&fit=crop&q=80' }}"/>
                    
                    <div class="flex flex-col max-w-md {{ $isSenderClient ? 'items-end' : '' }}">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-[#222222]">{{ $msg->sender->name ?? 'Counsel' }}</span>
                            <span class="font-mono text-[10px] text-[#766A5E]">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M, h:i A') }}</span>
                        </div>
                        <div class="p-3.5 rounded-2xl text-xs leading-relaxed shadow-sm {{ $isSenderClient ? 'bg-[#9F8349] text-white rounded-tr-none' : 'bg-[#FAF8F5] text-[#222222] rounded-tl-none border border-[#EFECE6]' }}">
                            {{ $msg->body }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center text-[#766A5E] flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl mb-2 text-[#EAE4DC]">chat_bubble_outline</span>
                    <p class="text-xs">No prior messages for this case. Start the conversation with counsel below.</p>
                </div>
                @endforelse
            </div>

            <!-- Message Input Dispatch Form -->
            <div class="p-4 border-t border-[#EFECE6] bg-[#FAF8F5]">
                <form action="{{ route('portal.messages.store') }}" method="POST" class="flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $selectedMatter->id }}"/>
                    
                    <div class="flex-1 flex flex-col gap-1">
                        <textarea name="body" rows="2" required placeholder="Type your query or reply for counsel..." class="w-full p-3 rounded-xl bg-white border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:border-[#9F8349] resize-none shadow-sm"></textarea>
                    </div>

                    <button type="submit" class="px-5 py-3 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] transition-colors shadow-sm shrink-0 flex items-center gap-1.5 h-[52px]">
                        <span>Send</span>
                        <span class="material-symbols-outlined text-base">send</span>
                    </button>
                </form>
            </div>
            @else
            <div class="py-24 text-center text-[#766A5E] flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl mb-2 text-[#EAE4DC]">chat</span>
                <p class="text-xs">Please select a case channel from the left sidebar to view messages.</p>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
