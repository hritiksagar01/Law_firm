@extends('layouts.admin')

@section('title', 'Email & SMS — Platform Console')

@section('content')
<div class="space-y-6">

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-3.5 bg-[#f0fdf4] border border-[#bbf7d0] text-[#166534] rounded-sm text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] rounded-sm text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-[#dc2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div>
        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Email &amp; SMS</h1>
        <p class="text-[13px] text-[#646864] mt-1">
            Outbound notification delivery for every firm
        </p>
    </div>

    <!-- 3-Card Configuration Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Channels -->
        <div class="bg-white border border-[#e5e3dc] rounded-sm p-4 flex flex-col justify-between shadow-xs">
            <div>
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Channels</h2>
                <p class="text-xs text-[#717974] mt-0.5 mb-4">A channel switched off here is off for every firm.</p>

                <form method="POST" action="{{ route('admin.settings.mail.channels') }}" id="channels-form" class="space-y-3">
                    @csrf
                    <label class="flex items-center gap-2.5 text-xs text-[#1b1c18] cursor-pointer">
                        <input type="checkbox" 
                               name="send_email" 
                               value="1" 
                               {{ ($channelsEnabled['email'] ?? true) ? 'checked' : '' }}
                               class="rounded border-[#c1c8c3] text-[#23493a] focus:ring-[#23493a] w-4 h-4">
                        <span>Send email notifications</span>
                    </label>

                    <label class="flex items-center gap-2.5 text-xs text-[#1b1c18] cursor-pointer">
                        <input type="checkbox" 
                               name="send_sms" 
                               value="1" 
                               {{ ($channelsEnabled['sms'] ?? true) ? 'checked' : '' }}
                               class="rounded border-[#c1c8c3] text-[#23493a] focus:ring-[#23493a] w-4 h-4">
                        <span>Send SMS notifications</span>
                    </label>
                </form>

                <p class="text-[11.5px] text-[#717974] mt-4 leading-relaxed">
                    Sign-in emails — invitations and password resets — still go out when email is off, so people can always get into their accounts. In-app notifications are unaffected.
                </p>
            </div>

            <div class="pt-4">
                <button type="submit" 
                        form="channels-form" 
                        class="h-7 px-3.5 rounded bg-[#23493a] text-white text-xs font-medium hover:bg-[#1b382d] transition-colors shadow-xs">
                    Save
                </button>
            </div>
        </div>

        <!-- Card 2: Providers -->
        <div class="bg-white border border-[#e5e3dc] rounded-sm p-4 flex flex-col justify-between shadow-xs">
            <div>
                <h2 class="text-[14px] font-semibold text-[#1a1a1a] mb-3">Providers</h2>

                <div class="space-y-3 text-xs">
                    <!-- Email Provider -->
                    <div class="border-b border-[#f0eee8] pb-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-[#1b1c18]">Email</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ ($mailConfig['mailer'] ?? '') === 'smtp' ? 'bg-[#d7f0e5] text-[#0d5236]' : 'bg-[#f0eee8] text-[#5e625e]' }}">
                                {{ ($mailConfig['mailer'] ?? '') === 'smtp' ? 'SMTP Active' : 'Logged only' }}
                            </span>
                        </div>
                        <span class="font-mono text-[11px] text-[#717974] block mt-0.5">
                            {{ ($mailConfig['mailer'] ?? '') === 'smtp' ? ($mailConfig['host'] ?? '127.0.0.1') : 'MAIL_MAILER=log' }}
                        </span>
                    </div>

                    <!-- From Address -->
                    <div class="border-b border-[#f0eee8] pb-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-[#1b1c18]">From address</span>
                            <span class="font-mono text-[11px] text-[#414844] truncate max-w-[150px]">{{ $mailConfig['from_name'] ?? config('mail.from.name', 'Platform') }} &lt;{{ $mailConfig['from_address'] ?? config('mail.from.address', 'notices@sharmalegal.in') }}&gt;</span>
                        </div>
                        <span class="font-mono text-[11px] text-[#717974] block mt-0.5">MAIL_FROM_ADDRESS</span>
                    </div>

                    <!-- SMS Provider -->
                    <div class="pb-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-[#1b1c18]">SMS</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f0eee8] text-[#5e625e]">Logged only</span>
                        </div>
                        <span class="font-mono text-[11px] text-[#717974] block mt-0.5">TWILIO_*</span>
                    </div>
                </div>
            </div>

            <p class="text-[11.5px] text-[#717974] mt-4 leading-relaxed">
                Configure your SMTP server below to dispatch live outbound messages to all platform tenants.
            </p>
        </div>

        <!-- Card 3: Send a test -->
        <div class="bg-white border border-[#e5e3dc] rounded-sm p-4 shadow-xs">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a] mb-3">Send a test</h2>

            <!-- Test Email Form -->
            <form method="POST" action="{{ route('admin.settings.mail.test-email') }}" class="mb-4">
                @csrf
                <label class="block text-xs text-[#5e625e] mb-1">Email address</label>
                <div class="flex items-center gap-2">
                    <input type="email" 
                           name="test_email" 
                           placeholder="e.g. ops@yourfirm.com" 
                           required
                           class="flex-1 h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] placeholder-[#9ca3af] focus:outline-none focus:border-[#23493a]">
                    <button type="submit" 
                            class="h-8 px-3 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors shrink-0">
                        Send
                    </button>
                </div>
            </form>

            <!-- Test SMS Form -->
            <form method="POST" action="{{ route('admin.settings.mail.test-sms') }}">
                @csrf
                <label class="block text-xs text-[#5e625e] mb-1">Mobile number</label>
                <div class="flex items-center gap-2">
                    <input type="text" 
                           name="test_phone" 
                           placeholder="e.g. +12125550100" 
                           required
                           class="flex-1 h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] placeholder-[#9ca3af] focus:outline-none focus:border-[#23493a]">
                    <button type="submit" 
                            class="h-8 px-3 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors shrink-0">
                        Send
                    </button>
                </div>
                <span class="text-[11px] text-[#717974] block mt-1">International format.</span>
            </form>
        </div>

    </div>

    <!-- Prominent Platform Mail & SMTP Gateway Configuration Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm p-5 shadow-xs">
        <div class="flex items-center justify-between border-b border-[#f0eee8] pb-3.5 mb-4">
            <div>
                <h2 class="text-[14px] font-semibold text-[#1a1a1a] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#23493a]">mail</span>
                    <span>Platform Mail &amp; SMTP Gateway</span>
                </h2>
                <p class="text-xs text-[#717974] mt-0.5">
                    Configure outbound mail delivery credentials, server host, and default sender identity
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-medium {{ ($mailConfig['mailer'] ?? '') === 'smtp' ? 'bg-[#d7f0e5] text-[#0d5236] border border-[#bbf0d8]' : 'bg-[#f0eee8] text-[#5e625e]' }}">
                    Active driver: {{ strtoupper($mailConfig['mailer'] ?? 'LOG') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.mail.update') }}" id="smtp-settings-form" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">Mailer Driver</label>
                    <select name="mail_mailer" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                        <option value="smtp" {{ ($mailConfig['mailer'] ?? '') === 'smtp' ? 'selected' : '' }}>SMTP Server</option>
                        <option value="log" {{ ($mailConfig['mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (Testing)</option>
                        <option value="sendmail" {{ ($mailConfig['mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="array" {{ ($mailConfig['mailer'] ?? '') === 'array' ? 'selected' : '' }}>Array</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $mailConfig['host'] ?? '127.0.0.1' }}" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">Port</label>
                    <input type="number" name="mail_port" value="{{ $mailConfig['port'] ?? 587 }}" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                        <option value="tls" {{ ($mailConfig['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($mailConfig['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ ($mailConfig['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">Username</label>
                    <input type="text" name="mail_username" value="{{ $mailConfig['username'] ?? '' }}" placeholder="e.g. apikey or user@domain.com" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">Password / API Key</label>
                    <input type="password" name="mail_password" placeholder="{{ ($mailConfig['has_password'] ?? false) ? '•••••••• (Stored)' : 'Enter SMTP password' }}" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $mailConfig['from_address'] ?? config('mail.from.address', 'contact@vennamraj.com') }}" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>

                <div>
                    <label class="block font-medium text-[#5e625e] mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $mailConfig['from_name'] ?? config('legal.app_name', 'Vennamraj Associates') }}" class="w-full h-8 px-2.5 rounded border border-[#c1c8c3] text-xs bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-[#f0eee8]">
                <button type="submit" class="h-8 px-4 rounded bg-[#23493a] text-white text-xs font-medium hover:bg-[#1b382d] transition-colors shadow-xs">
                    Save Mail Configuration
                </button>
                <button type="button" onclick="document.querySelectorAll('#smtp-settings-form input[type=text], #smtp-settings-form input[type=password]').forEach(el => el.value = '')" class="text-xs text-[#717974] hover:text-[#ba1a1a]">
                    Clear All Fields
                </button>
            </div>
        </form>
    </div>

    <!-- Delivery Log Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        
        <!-- Delivery Log Header & Notice -->
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Delivery log</h2>
            <p class="text-xs text-[#5e625e] mt-1 leading-relaxed">
                Platform staff see record types, counts, statuses and matter numbers. Client identities, matter titles, document and message content, and individual amounts stay with each firm. Recipients are masked and message wording is withheld; test messages are shown in full.
            </p>
        </div>

        <!-- Filter Bar -->
        <div class="p-3 border-b border-[#e5e3dc] bg-[#faf9f6]">
            <form method="GET" action="{{ route('admin.settings.mail') }}" class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-[#5e625e]">Channel</label>
                    <select name="channel" class="h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                        <option value="both" {{ request('channel') === 'both' ? 'selected' : '' }}>Both</option>
                        <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs text-[#5e625e]">Status</label>
                    <select name="status" class="h-8 px-2.5 rounded-md border border-[#c1c8c3] bg-white text-xs text-[#1b1c18] focus:outline-none focus:border-[#23493a]">
                        <option value="any" {{ request('status') === 'any' ? 'selected' : '' }}>Any</option>
                        <option value="logged_only" {{ request('status') === 'logged_only' ? 'selected' : '' }}>Logged only</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <button type="submit" class="h-8 px-3 rounded-md border border-[#c1c8c3] bg-white hover:bg-[#f5f3ed] text-xs font-medium text-[#1b1c18] transition-colors shadow-xs">
                    Apply
                </button>
            </form>
        </div>

        <!-- Delivery Log Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-2.5 px-4 font-normal">Time (UTC)</th>
                        <th class="py-2.5 px-4 font-normal">Firm</th>
                        <th class="py-2.5 px-4 font-normal">Channel</th>
                        <th class="py-2.5 px-4 font-normal">Recipient</th>
                        <th class="py-2.5 px-4 font-normal">Notification</th>
                        <th class="py-2.5 px-4 font-normal">Provider</th>
                        <th class="py-2.5 px-4 font-normal text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($deliveryLogs as $log)
                    <tr class="hover:bg-[#f4f2ed] transition-colors">
                        <td class="py-3 px-4 text-[#5e625e] font-sans">
                            {{ $log->created_at->format('M j, Y, g:i A') }} UTC
                        </td>
                        <td class="py-3 px-4">
                            @if($log->firm)
                                <a href="{{ route('admin.firms.show', $log->firm) }}" class="text-[#1b1c18] hover:underline font-medium">
                                    {{ $log->firm->name }}
                                </a>
                            @else
                                <span class="text-[#8e8e8e]">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18]">
                            {{ strtoupper($log->channel) === 'SMS' ? 'SMS' : 'Email' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[#1b1c18]">
                            {{ $log->recipient }}
                        </td>
                        <td class="py-3 px-4 text-[#414844]">
                            {{ $log->notification }}
                        </td>
                        <td class="py-3 px-4 text-[#5e625e]">
                            {{ $log->provider ?? 'None' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if($log->status === 'logged_only')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#f0eee8] text-[#5e625e]">
                                    Logged only
                                </span>
                            @elseif($log->status === 'sent')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#d7f0e5] text-[#0d5236] border border-[#bbf0d8]">
                                    Sent
                                </span>
                            @elseif($log->status === 'failed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                                    Failed
                                </span>
                            @else
                                <span class="text-[#5e625e] text-[11px]">{{ $log->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-xs text-[#5e625e]">
                            No notifications recorded in delivery log.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveryLogs->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white flex items-center justify-between text-xs text-[#5e625e]">
            <span>Page {{ $deliveryLogs->currentPage() }} · {{ $deliveryLogs->total() }} records</span>
            <div class="flex gap-1">
                @if($deliveryLogs->onFirstPage())
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $deliveryLogs->previousPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Previous</a>
                @endif

                @if($deliveryLogs->hasMorePages())
                    <a href="{{ $deliveryLogs->nextPageUrl() }}" class="px-2.5 py-1 rounded border border-[#c1c8c3] text-[#1b1c18] hover:bg-[#f5f3ed]">Next</a>
                @else
                    <span class="px-2.5 py-1 rounded border border-[#e5e3dc] text-[#a0a0a0] cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
