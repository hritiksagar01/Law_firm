@extends('layouts.app')

@section('title', 'Notifications Center — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8 space-y-6">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <span>In-App Dispatch</span>
                <span>•</span>
                <span>Chambers Notifications</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight flex items-center gap-2.5">
                <span>Notifications Center</span>
                @if($unreadCount > 0)
                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#23493a] text-white">
                    {{ $unreadCount }} new
                </span>
                @endif
            </h1>
            <p class="text-xs text-[#646864] mt-0.5">Real-time alerts on case assignments, court listings, client uploads, and task deadlines.</p>
        </div>

        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-base text-[#23493a]">done_all</span>
                <span>Mark All as Read</span>
            </button>
        </form>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm divide-y divide-[#e5e3dc] overflow-hidden">
        @forelse($notifications as $notification)
        @php
            $isUnread = $notification->unread();
            $data = $notification->data;
            $title = $data['title'] ?? 'Chambers Notification';
            $message = $data['message'] ?? ($data['body'] ?? 'No additional details provided.');
            $actionUrl = $data['url'] ?? ($data['action_url'] ?? null);
            $icon = $data['icon'] ?? 'notifications';
        @endphp
        <div class="p-4 sm:p-5 flex items-start justify-between gap-4 transition-colors {{ $isUnread ? 'bg-[#23493a]/[0.03]' : 'hover:bg-[#faf8f5]/80' }}">
            <div class="flex items-start gap-3.5 min-w-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5 {{ $isUnread ? 'bg-[#23493a]/10 text-[#23493a]' : 'bg-[#faf8f5] text-[#8a8a8a] border border-[#e5e3dc]' }}">
                    <span class="material-symbols-outlined text-base">{{ $icon }}</span>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-[#1a1a1a]">{{ $title }}</span>
                        @if($isUnread)
                        <span class="w-2 h-2 rounded-full bg-[#23493a] inline-block"></span>
                        @endif
                    </div>
                    <p class="text-xs text-[#646864] mt-0.5 leading-relaxed">{{ $message }}</p>

                    <div class="flex items-center gap-3 mt-2 text-[11px] text-[#8a8a8a]">
                        <span class="font-mono">{{ $notification->created_at->diffForHumans() }}</span>
                        @if($actionUrl)
                        <span>•</span>
                        <a href="{{ $actionUrl }}" class="text-[#23493a] font-medium hover:underline flex items-center gap-0.5">
                            <span>View Context</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                @if($isUnread)
                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-[#646864] hover:text-[#23493a] hover:bg-[#faf8f5] transition-colors" title="Mark as read">
                        <span class="material-symbols-outlined text-base">check</span>
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-[#646864] hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete notification">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-16 text-center text-[#646864]">
            <span class="material-symbols-outlined text-4xl text-[#8a8a8a] block mb-2">notifications_off</span>
            <p class="font-semibold text-xs text-[#1a1a1a]">You're all caught up</p>
            <p class="text-[11px] text-[#646864] mt-0.5">No new notifications in your inbox.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="p-4 bg-white rounded-xl border border-[#e5e3dc] shadow-sm">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
