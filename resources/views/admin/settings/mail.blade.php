@extends('layouts.app')

@section('title', 'Platform Mail & SMTP Gateway — Super Admin Console')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto py-6" x-data="{
    testEmail: '{{ auth()->user()->email ?? 'admin@sharmalegal.in' }}',
    testing: false,
    testSuccess: null,
    testMessage: '',
    async runMailTest() {
        if (!this.testEmail) {
            alert('Please enter a recipient email address.');
            return;
        }
        this.testing = true;
        this.testSuccess = null;
        this.testMessage = '';
        try {
            const res = await fetch('{{ route('admin.settings.mail.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ test_email: this.testEmail })
            });
            const data = await res.json();
            this.testSuccess = data.success;
            this.testMessage = data.message;
        } catch (e) {
            this.testSuccess = false;
            this.testMessage = 'Network error: ' + e.message;
        } finally {
            this.testing = false;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between pb-6 border-b border-[#EFECE6] gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest px-2 py-0.5 rounded bg-[#9F8349]/10 text-[#9F8349] border border-[#9F8349]/20">
                    Platform Communications
                </span>
                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Production Hardened
                </span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#222222] mt-1">Platform Mail &amp; SMTP Gateway</h1>
            <p class="text-xs text-[#766A5E] mt-1">Configure and verify outbound communications, court notice dispatches, and appointment emails.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-xs bg-white text-[#766A5E] border border-[#EAE4DC] hover:text-[#222222] font-medium transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">dashboard</span>
                <span>Tenant Overview</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" class="px-3 py-2 rounded-lg text-xs bg-[#9F8349] text-white hover:bg-[#856C36] font-medium transition-colors flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Law Firm Directory</span>
            </a>
        </div>
    </div>

    <!-- Security & Architecture Banner -->
    <div class="p-4 rounded-xl bg-gradient-to-r from-[#FAF8F5] to-[#F4EFEA] border border-[#E8DAC8] flex items-start gap-3">
        <span class="material-symbols-outlined text-[#9F8349] text-xl mt-0.5">verified_user</span>
        <div class="text-xs text-[#554D45] space-y-1">
            <div class="font-semibold text-[#222222]">Production Infrastructure Hardening Active</div>
            <p class="text-[11px] leading-relaxed text-[#766A5E]">
                Database connection strings, relational poolers, and S3 object vault credentials are locked to immutable server-side environment variables managed strictly via your secure CI/CD pipeline. Web-based configuration mutation has been disabled to ensure zero attack surface.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- SMTP Gateway Configuration Status (2 cols) -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349]">forward_to_inbox</span>
                        <h2 class="text-sm font-semibold text-[#222222]">Active Mail Configuration</h2>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium {{ $mailConfig['mailer'] === 'smtp' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $mailConfig['mailer'] === 'smtp' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>{{ strtoupper($mailConfig['mailer']) }} Driver</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">SMTP Host</div>
                        <div class="font-mono font-semibold text-[#222222] mt-1 truncate">{{ $mailConfig['host'] }}</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">Port &amp; Encryption</div>
                        <div class="font-mono font-semibold text-[#222222] mt-1">{{ $mailConfig['port'] }} · {{ strtoupper($mailConfig['encryption']) }}</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">Authenticated Username</div>
                        <div class="font-mono font-semibold text-[#222222] mt-1 truncate">{{ $mailConfig['username'] ?: 'None (Local Relay)' }}</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">Password Status</div>
                        <div class="font-mono font-semibold text-emerald-700 mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">lock</span>
                            <span>{{ $mailConfig['has_password'] ? 'Configured in Environment' : 'Not Set' }}</span>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">Default Sender Email</div>
                        <div class="font-mono font-semibold text-[#222222] mt-1 truncate">{{ $mailConfig['from_address'] }}</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-[#FAF8F5] border border-[#EFECE6]">
                        <div class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider">Default Sender Name</div>
                        <div class="font-mono font-semibold text-[#222222] mt-1 truncate">{{ $mailConfig['from_name'] }}</div>
                    </div>
                </div>

                <div class="mt-5 p-3 rounded-lg bg-[#FAF8F5] border border-[#EFECE6] text-[11px] text-[#766A5E] flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-[#9F8349]">info</span>
                    <span>To update SMTP credentials, set <code>MAIL_HOST</code>, <code>MAIL_PORT</code>, <code>MAIL_USERNAME</code>, and <code>MAIL_PASSWORD</code> in your deployment environment or GitHub Secrets.</span>
                </div>
            </div>
        </div>

        <!-- Live SMTP Dispatch Verification Tool (1 col) -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-xs">
                <div class="flex items-center gap-2 pb-4 border-b border-[#F4EFEA] mb-4">
                    <span class="material-symbols-outlined text-[#9F8349]">mark_email_read</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Verify SMTP Delivery</h3>
                </div>
                <p class="text-xs text-[#766A5E] mb-4 leading-relaxed">
                    Dispatch an instantaneous live probe and verification email through your configured SMTP gateway to confirm handshake and delivery.
                </p>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-medium text-[#222222] block mb-1">Recipient Email</label>
                        <input type="email" x-model="testEmail" placeholder="your-email@example.com" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] font-mono text-xs focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                    </div>

                    <button type="button" @click="runMailTest()" :disabled="testing" class="w-full py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white font-semibold text-xs transition-colors flex items-center justify-center gap-2 shadow-xs disabled:opacity-50 cursor-pointer">
                        <template x-if="!testing">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">send</span>
                                <span>Send Test Email</span>
                            </div>
                        </template>
                        <template x-if="testing">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-block w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span>Dispatching Probe...</span>
                            </div>
                        </template>
                    </button>
                </div>

                <!-- Result Message -->
                <template x-if="testSuccess === true">
                    <div class="mt-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2">
                        <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                        <div class="text-[11px]" x-text="testMessage"></div>
                    </div>
                </template>
                <template x-if="testSuccess === false">
                    <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-start gap-2">
                        <span class="material-symbols-outlined text-sm text-red-600 mt-0.5">error</span>
                        <div class="text-[11px] leading-relaxed break-all" x-text="testMessage"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
