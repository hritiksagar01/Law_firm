@extends('layouts.admin')

@section('title', 'Platform Mail & SMTP Gateway')
@section('header_title', 'Mail & Communications Gateway')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto py-6" x-data="{
    mailMailer: '{{ old('mail_mailer', $mailConfig['mailer']) }}',
    mailHost: '{{ old('mail_host', $mailConfig['host']) }}',
    mailPort: '{{ old('mail_port', $mailConfig['port']) }}',
    mailUsername: '{{ old('mail_username', $mailConfig['username']) }}',
    mailPassword: '',
    mailEncryption: '{{ old('mail_encryption', $mailConfig['encryption']) }}',
    mailFromAddress: '{{ old('mail_from_address', $mailConfig['from_address']) }}',
    mailFromName: '{{ old('mail_from_name', $mailConfig['from_name']) }}',
    showPassword: false,
    clearPassword: false,

    // Test tool state
    testEmail: '{{ auth()->user()->email ?? 'admin@sharmalegal.in' }}',
    testing: false,
    testSuccess: null,
    testMessage: '',

    applyPreset(preset) {
        if (preset === 'brevo') {
            this.mailMailer = 'smtp';
            this.mailHost = 'smtp-relay.brevo.com';
            this.mailPort = '587';
            this.mailEncryption = 'tls';
        } else if (preset === 'gmail') {
            this.mailMailer = 'smtp';
            this.mailHost = 'smtp.gmail.com';
            this.mailPort = '587';
            this.mailEncryption = 'tls';
        } else if (preset === 'office365') {
            this.mailMailer = 'smtp';
            this.mailHost = 'smtp.office365.com';
            this.mailPort = '587';
            this.mailEncryption = 'tls';
        } else if (preset === 'mailgun') {
            this.mailMailer = 'smtp';
            this.mailHost = 'smtp.mailgun.org';
            this.mailPort = '587';
            this.mailEncryption = 'tls';
        } else if (preset === 'log') {
            this.mailMailer = 'log';
        }
    },

    clearAllFields() {
        if (confirm('Clear all mail configuration fields so you can enter fresh credentials?')) {
            this.mailHost = '';
            this.mailPort = '587';
            this.mailUsername = '';
            this.mailPassword = '';
            this.mailEncryption = 'tls';
            this.clearPassword = true;
        }
    },

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
                body: JSON.stringify({
                    test_email: this.testEmail,
                    mailer: this.mailMailer,
                    host: this.mailHost,
                    port: this.mailPort,
                    username: this.mailUsername,
                    password: this.mailPassword,
                    encryption: this.mailEncryption,
                    from_address: this.mailFromAddress,
                    from_name: this.mailFromName
                })
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
            <p class="text-xs text-[#766A5E] mt-1">Configure, update, and verify outbound communications, court notice dispatches, and appointment emails.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-xs bg-white text-[#766A5E] border border-[#EAE4DC] hover:text-[#222222] font-medium transition-colors flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-sm">dashboard</span>
                <span>Tenant Overview</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" class="px-3 py-2 rounded-lg text-xs bg-[#9F8349] text-white hover:bg-[#856C36] font-medium transition-colors flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Law Firm Directory</span>
            </a>
        </div>
    </div>

    <!-- Alert / Feedback Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-xs">
        <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1 shadow-xs">
        <div class="font-semibold flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-red-600">error</span>
            <span>Please resolve the following errors:</span>
        </div>
        <ul class="list-disc list-inside text-[11px] space-y-0.5 text-red-700 pl-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SMTP Gateway Configuration Form (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('admin.settings.mail.update') }}" method="POST" class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-xs space-y-5">
                @csrf
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#F4EFEA] gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349]">forward_to_inbox</span>
                        <h2 class="text-sm font-semibold text-[#222222]">Active Mail Gateway Configuration</h2>
                    </div>

                    <!-- Quick Preset Selectors -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-mono text-[#766A5E] uppercase tracking-wider mr-1">Presets:</span>
                        <button type="button" @click="applyPreset('brevo')" class="px-2 py-1 rounded bg-[#FAF8F5] hover:bg-[#FAF0E6] text-[10px] font-medium text-[#554D45] border border-[#EAE4DC] transition-colors">Brevo</button>
                        <button type="button" @click="applyPreset('gmail')" class="px-2 py-1 rounded bg-[#FAF8F5] hover:bg-[#FAF0E6] text-[10px] font-medium text-[#554D45] border border-[#EAE4DC] transition-colors">Gmail</button>
                        <button type="button" @click="applyPreset('office365')" class="px-2 py-1 rounded bg-[#FAF8F5] hover:bg-[#FAF0E6] text-[10px] font-medium text-[#554D45] border border-[#EAE4DC] transition-colors">Office 365</button>
                        <button type="button" @click="applyPreset('log')" class="px-2 py-1 rounded bg-[#FAF8F5] hover:bg-[#FAF0E6] text-[10px] font-medium text-[#554D45] border border-[#EAE4DC] transition-colors">Local Log</button>
                    </div>
                </div>

                <!-- Driver & Protocol -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-[#222222] mb-1">Mail Driver</label>
                        <select name="mail_mailer" x-model="mailMailer" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]">
                            <option value="smtp">SMTP (Production Standard - Recommended)</option>
                            <option value="log">LOG (Development / Local Test File)</option>
                            <option value="sendmail">Sendmail (System Binary)</option>
                            <option value="array">Array (Testing In-Memory)</option>
                        </select>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">Use 'smtp' for real delivery via Brevo, Sendgrid, AWS SES, or Gmail.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-[#222222] mb-1">Encryption Protocol</label>
                        <select name="mail_encryption" x-model="mailEncryption" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]">
                            <option value="tls">TLS (Standard for Port 587)</option>
                            <option value="ssl">SSL (Standard for Port 465)</option>
                            <option value="none">None (Plaintext / Unencrypted)</option>
                        </select>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">TLS is recommended for Port 587.</span>
                    </div>
                </div>

                <!-- Host & Port -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-[#222222] mb-1">SMTP Host Server</label>
                        <input type="text" name="mail_host" x-model="mailHost" placeholder="smtp-relay.brevo.com" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">Fully qualified hostname of your relay provider.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-[#222222] mb-1">Port</label>
                        <input type="number" name="mail_port" x-model="mailPort" placeholder="587" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">587 (TLS) or 465 (SSL)</span>
                    </div>
                </div>

                <!-- Username & Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-[#222222] mb-1">SMTP Username / API Key</label>
                        <input type="text" name="mail_username" x-model="mailUsername" placeholder="e.g. 7f4a21001@smtp-brevo.com or user@gmail.com" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">Login username or relay API key.</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-[#222222]">SMTP Password</label>
                            <button type="button" @click="showPassword = !showPassword" class="text-[10px] text-[#9F8349] hover:underline flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                                <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" name="mail_password" x-model="mailPassword" placeholder="{{ $mailConfig['has_password'] ? '•••••••• (Leave blank to retain current)' : 'Enter SMTP password' }}" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[10px] text-[#766A5E]">
                                Status: <strong class="{{ $mailConfig['has_password'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $mailConfig['has_password'] ? 'Password Configured' : 'Not Set' }}</strong>
                            </span>
                            @if($mailConfig['has_password'])
                            <label class="inline-flex items-center gap-1 text-[10px] text-red-600 cursor-pointer">
                                <input type="checkbox" name="clear_password" value="1" x-model="clearPassword" class="rounded border-[#EAE4DC] text-red-600"/>
                                <span>Clear Password</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- From Address & From Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-[#222222] mb-1">Default From Email Address</label>
                        <input type="email" name="mail_from_address" x-model="mailFromAddress" placeholder="contact@vennamraj.com" required class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">Sender address on outgoing notifications.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-[#222222] mb-1">Default From Sender Name</label>
                        <input type="text" name="mail_from_name" x-model="mailFromName" placeholder="Vennamraj Associates" required class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs font-mono focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">Display name shown in clients' inboxes.</span>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="pt-4 border-t border-[#F4EFEA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <button type="button" @click="clearAllFields()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#EAE4DC] text-xs text-[#766A5E] hover:text-red-700 hover:border-red-300 hover:bg-red-50 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-sm">restart_alt</span>
                        <span>Clear All Fields</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Save Mail Configuration</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Live SMTP Dispatch Verification Tool (1 col) -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-[#EFECE6] p-6 shadow-xs">
                <div class="flex items-center gap-2 pb-4 border-b border-[#F4EFEA] mb-4">
                    <span class="material-symbols-outlined text-[#9F8349]">mark_email_read</span>
                    <h3 class="text-sm font-semibold text-[#222222]">Verify SMTP Delivery</h3>
                </div>
                <p class="text-xs text-[#766A5E] mb-4 leading-relaxed">
                    Dispatch an instantaneous live test email using your configured credentials to confirm server handshake, TLS cipher, and inbox delivery.
                </p>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-medium text-[#222222] block mb-1">Recipient Test Email</label>
                        <input type="email" x-model="testEmail" placeholder="your-email@example.com" class="w-full px-3 py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] font-mono text-xs focus:bg-white outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]"/>
                        <span class="text-[10px] text-[#766A5E] mt-0.5 block">We will send a real test email to this mailbox.</span>
                    </div>

                    <button type="button" @click="runMailTest()" :disabled="testing" class="w-full py-2.5 rounded-lg bg-[#222222] hover:bg-[#3A322B] text-white font-semibold text-xs transition-colors flex items-center justify-center gap-2 shadow-xs disabled:opacity-50 cursor-pointer">
                        <template x-if="!testing">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#B88B56]">send</span>
                                <span>Send Test Email Now</span>
                            </div>
                        </template>
                        <template x-if="testing">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-block w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span>Dispatching Live Probe...</span>
                            </div>
                        </template>
                    </button>
                </div>

                <!-- Result Message Feedback -->
                <template x-if="testSuccess === true">
                    <div class="mt-4 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2.5 animate-fadeIn">
                        <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                        <div class="text-[11px] leading-relaxed" x-text="testMessage"></div>
                    </div>
                </template>
                <template x-if="testSuccess === false">
                    <div class="mt-4 p-3.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-start gap-2.5 animate-fadeIn">
                        <span class="material-symbols-outlined text-sm text-red-600 mt-0.5">error</span>
                        <div class="text-[11px] leading-relaxed break-all" x-text="testMessage"></div>
                    </div>
                </template>

                <!-- Helpful Guide -->
                <div class="mt-5 pt-4 border-t border-[#F4EFEA] space-y-2 text-[11px] text-[#766A5E]">
                    <div class="font-semibold text-[#222222] flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs text-[#9F8349]">lightbulb</span>
                        <span>Common Providers Configuration</span>
                    </div>
                    <ul class="space-y-1 pl-4 list-disc text-[10px]">
                        <li><strong>Brevo / Sendinblue:</strong> Host <code>smtp-relay.brevo.com</code>, Port <code>587</code>, TLS, Username is your registered Brevo account, Password is your SMTP Key.</li>
                        <li><strong>Google Gmail:</strong> Host <code>smtp.gmail.com</code>, Port <code>587</code>, TLS. Requires a Google <em>App Password</em> (not standard account password).</li>
                        <li><strong>Office 365:</strong> Host <code>smtp.office365.com</code>, Port <code>587</code>, TLS.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
