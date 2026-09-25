@extends('layouts.app')

@section('title', 'Chambers Activity Audit Trail — Sharma Legal Chambers')
@section('header_title', 'Activity')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a]">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">
                Case Activity &amp; Audit Trail
            </h1>
            <p class="text-xs text-[#646864] mt-1 font-sans">
                Immutable, real-time chronological log of evidentiary filings, task resolutions, client interactions, and matter assignments.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-[#e5e3dc] rounded-md shadow-xs mb-6">
        <form action="{{ route('activity.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="font-semibold text-[#1a1a1a] block mb-1">Filter by Matter</label>
                <select name="matter_id" onchange="this.form.submit()" class="w-full h-8 px-2 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                    <option value="">All Matters</option>
                    @foreach($matters as $mt)
                        <option value="{{ $mt->id }}" {{ request('matter_id') == $mt->id ? 'selected' : '' }}>
                            Case {{ $mt->case_number }}: {{ $mt->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold text-[#1a1a1a] block mb-1">Filter by Action Type</label>
                <select name="activity_type" onchange="this.form.submit()" class="w-full h-8 px-2 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                    <option value="all">All Activity Types</option>
                    <option value="document_uploaded" {{ request('activity_type') === 'document_uploaded' ? 'selected' : '' }}>Document Uploaded</option>
                    <option value="task_completed" {{ request('activity_type') === 'task_completed' ? 'selected' : '' }}>Task Completed</option>
                    <option value="message_sent" {{ request('activity_type') === 'message_sent' ? 'selected' : '' }}>Message Sent</option>
                    <option value="note_added" {{ request('activity_type') === 'note_added' ? 'selected' : '' }}>Note Added</option>
                    <option value="assignment_changed" {{ request('activity_type') === 'assignment_changed' ? 'selected' : '' }}>Team Assignment Changed</option>
                    <option value="conflict_check_completed" {{ request('activity_type') === 'conflict_check_completed' ? 'selected' : '' }}>Conflict Check Completed</option>
                    <option value="status_changed" {{ request('activity_type') === 'status_changed' ? 'selected' : '' }}>Status Changed</option>
                </select>
            </div>

            <div>
                <label class="font-semibold text-[#1a1a1a] block mb-1">Filter by Advocate / Staff</label>
                <select name="user_id" onchange="this.form.submit()" class="w-full h-8 px-2 rounded-md bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#1a1a1a]">
                    <option value="">All Advocates &amp; Staff</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Activity Feed Timeline -->
    <div class="border border-[#e5e3dc] bg-white rounded-md shadow-xs overflow-hidden">
        <div class="divide-y divide-[#f0eee8]">
            @forelse($activities as $act)
            <div class="p-4 flex items-start gap-4 hover:bg-[#faf9f5] transition-colors">
                <!-- Icon -->
                <div class="w-8 h-8 rounded-full bg-[#f5f3ed] text-[#23493a] border border-[#e5e3dc] flex items-center justify-center shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-[17px]">{{ $act->icon }}</span>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <strong class="text-[#1a1a1a]">{{ $act->user?->name ?? 'Chambers System' }}</strong>
                        <span class="text-[#8a8a8a]">&middot;</span>
                        <span class="text-[#646864]">{{ $act->description }}</span>
                        @if($act->matter)
                            <a href="{{ route('matters.show', $act->matter_id) }}" class="inline-flex items-center gap-1 font-mono text-[11px] text-[#23493a] bg-[#faf8f5] border border-[#e5e3dc] px-1.5 py-0.2 rounded hover:underline">
                                <span>{{ $act->matter->case_number }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-[#8a8a8a] mt-1 font-mono">
                        <span>{{ $act->created_at->format('d M Y, h:i A') }}</span>
                        <span>({{ $act->created_at->diffForHumans() }})</span>
                        @if($act->client)
                            <span>&middot; Client: {{ $act->client->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-xs text-[#8a8a8a]">
                <span class="material-symbols-outlined text-4xl text-[#c1c8c3] mb-2 block">history</span>
                No case activity logged matching these criteria.
            </div>
            @endforelse
        </div>

        @if($activities->hasPages())
        <div class="p-4 border-t border-[#f0eee8] bg-[#faf8f5]">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
