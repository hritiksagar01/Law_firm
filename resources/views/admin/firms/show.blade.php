@extends('layouts.admin')

@section('title', $firm->name . ' — Firms — Platform Console')

@section('content')
<div class="space-y-6" x-data="{ 
    confirmFirmName: '',
    copied: false,
    copyLink() {
        const copyText = document.getElementById('signupLink');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    }
}">

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

    <!-- Breadcrumb & Header -->
    <div>
        <nav class="text-xs text-[#5e625e] mb-1 font-sans flex items-center gap-1.5">
            <a href="{{ route('admin.firms.index') }}" class="hover:underline text-[#5e625e]">Firms</a>
            <span class="text-[#a3a5a8]">&nbsp;/&nbsp;</span>
            <span class="text-[#1b1c18] font-medium">{{ $firm->name }}</span>
        </nav>

        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">{{ $firm->name }}</h1>

        <!-- Badges Row -->
        <div class="flex flex-wrap items-center gap-2.5 mt-2 text-xs">
            <span class="px-2 py-0.5 rounded-xs font-mono text-[11px] bg-[#e4e2dd] text-[#1b1c18]">
                {{ $firm->slug }}
            </span>

            @if($firm->status === 'active')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                Firm active
            </span>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                Firm {{ $firm->status }}
            </span>
            @endif

            <span class="text-[#5e625e] text-xs font-sans">
                Created {{ $firm->created_at->format('M j, Y') }}
            </span>
        </div>
    </div>

    <!-- Two Column Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Profile, People, Recent Activity -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Profile Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a] mb-4">Profile</h2>

                <form method="POST" action="{{ route('admin.firms.update', $firm) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Firm name -->
                        <div>
                            <label for="prof_name" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Firm name</label>
                            <input type="text" name="name" id="prof_name" required value="{{ old('name', $firm->name) }}"
                                   class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="prof_email" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Email</label>
                            <input type="email" name="email" id="prof_email" value="{{ old('email', $firm->email) }}"
                                   placeholder="office@{{ $firm->slug }}.example"
                                   class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="prof_phone" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Phone</label>
                            <input type="text" name="phone" id="prof_phone" value="{{ old('phone', $firm->phone) }}"
                                   placeholder="(212) 555-0148"
                                   class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Website -->
                        <div>
                            <label for="prof_website" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Website</label>
                            <input type="text" name="website" id="prof_website" value="{{ old('website', $firm->website) }}"
                                   placeholder="{{ $firm->slug }}.example"
                                   class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]" />
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="prof_address" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Address</label>
                            <textarea name="address" id="prof_address" rows="3"
                                      class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a]">{{ old('address', $firm->address) }}</textarea>
                        </div>

                        <!-- Timezone -->
                        <div>
                            <label for="prof_tz" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Timezone</label>
                            <div class="relative">
                                <select name="timezone" id="prof_tz"
                                        class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] appearance-none pr-8">
                                    <option value="America/New_York" {{ old('timezone', $firm->timezone) === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                    <option value="America/Chicago" {{ old('timezone', $firm->timezone) === 'America/Chicago' ? 'selected' : '' }}>America/Chicago</option>
                                    <option value="America/Denver" {{ old('timezone', $firm->timezone) === 'America/Denver' ? 'selected' : '' }}>America/Denver</option>
                                    <option value="America/Los_Angeles" {{ old('timezone', $firm->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                                    <option value="Europe/London" {{ old('timezone', $firm->timezone) === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="Asia/Kolkata" {{ old('timezone', $firm->timezone) === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata</option>
                                    <option value="UTC" {{ old('timezone', $firm->timezone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Currency (locked) -->
                        <div>
                            <label for="prof_cur" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Currency</label>
                            <input type="text" name="currency" id="prof_cur" value="{{ $firm->currency ?? 'USD' }}" readonly
                                   class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-[#f5f3ed] text-[#5e625e] cursor-not-allowed" />
                            <span class="block text-[11px] text-[#5e625e] mt-1">Locked once the firm has issued an invoice.</span>
                        </div>

                        <!-- Accent colour -->
                        <div class="md:col-span-2">
                            <label for="prof_accent" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Accent colour</label>
                            <div class="flex items-center gap-2 max-w-sm">
                                <input type="color" name="accent_color_picker" 
                                       value="{{ $firm->accent_color ?? '#24503f' }}" 
                                       onchange="document.getElementById('prof_accent').value = this.value"
                                       class="w-8 h-8 rounded-xs border border-[#dcdad4] cursor-pointer p-0 bg-transparent" />
                                <input type="text" name="accent_color" id="prof_accent" 
                                       value="{{ old('accent_color', $firm->accent_color ?? '#24503f') }}"
                                       class="flex-1 px-3 py-1.5 text-xs font-mono rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                            </div>
                            <span class="block text-[11px] text-[#5e625e] mt-1">Shown across the top of the client portal.</span>
                        </div>

                        <!-- Client registration checkbox -->
                        <div class="md:col-span-2 pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="allow_client_signup" value="1" 
                                       {{ old('allow_client_signup', $firm->allow_client_signup ?? true) ? 'checked' : '' }}
                                       class="rounded-xs border-[#717974] text-[#23493a] focus:ring-[#23493a]" />
                                <span class="text-xs text-[#1b1c18]">Clients can register from the firm's sign-up page</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-[#23493a] text-white text-xs font-medium rounded-sm hover:bg-[#1a382c] transition-colors shadow-none">
                            Save profile
                        </button>
                    </div>
                </form>

                <!-- Client sign-up link Box -->
                <div class="mt-6 pt-5 border-t border-[#e5e3dc]">
                    <label class="block text-xs font-medium text-[#1b1c18] mb-1.5">Client sign-up link</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="signupLink" readonly
                               value="{{ url('/join/' . $firm->slug) }}"
                               class="flex-1 px-3 py-2 text-xs font-mono rounded-sm border border-[#dcdad4] bg-[#faf8f5] text-[#1b1c18] select-all" />
                        <button type="button" @click="copyLink()"
                                class="px-4 py-2 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" class="text-[#166534]">Copied!</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- People Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">People</h2>
                        <p class="text-xs text-[#5e625e] mt-0.5">
                            {{ $staffCount }} staff accounts &middot; {{ $clientPortalCount }} client portal accounts (identities withheld)
                        </p>
                    </div>
                    <a href="{{ url('/admin/users') }}" class="text-xs text-[#23493a] hover:underline font-medium">
                        Manage in Users
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] font-normal">
                                <th class="pb-2 px-3 font-normal">Name</th>
                                <th class="pb-2 px-3 font-normal">Role</th>
                                <th class="pb-2 px-3 font-normal">Status</th>
                                <th class="pb-2 px-3 font-normal">Two-step</th>
                                <th class="pb-2 px-3 font-normal text-right">Last sign-in</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0eee8]">
                            @forelse($users as $u)
                            @php
                                $roleDisplay = match($u->role) {
                                    'partner' => 'Firm administrator',
                                    'associate' => 'Attorney',
                                    'paralegal', 'staff' => 'Paralegal / staff',
                                    'client' => 'Client',
                                    default => ucfirst($u->role),
                                };
                                $isClient = ($u->role === 'client');
                            @endphp
                            <tr class="hover:bg-[#f4f2ed] transition-colors">
                                <td class="py-2.5 px-3">
                                    <span class="font-medium text-[#1b1c18] block">
                                        {{ $isClient ? 'Client portal user' : $u->name }}
                                    </span>
                                    <span class="text-[11px] text-[#5e625e] font-mono block">
                                        {{ $isClient ? substr($u->email, 0, 1) . '***@' . explode('@', $u->email)[1] : $u->email }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-[#1b1c18] font-sans">
                                    {{ $roleDisplay }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                        Active
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-[#5e625e] font-sans">
                                    {{ $u->two_factor_confirmed_at ? 'On' : 'Off' }}
                                </td>
                                <td class="py-2.5 px-3 text-[#5e625e] font-sans text-right">
                                    {{ $u->updated_at ? $u->updated_at->diffForHumans(null, true) : 'Never' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 px-3 text-center text-xs text-[#5e625e]">
                                    No personnel assigned to this firm yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Recent activity</h2>
                <p class="text-xs text-[#5e625e] mt-0.5 mb-4">
                    Platform staff see record types, counts, statuses and matter numbers. Client identities, matter titles, document and message content, and individual amounts stay with each firm.
                </p>

                <div class="divide-y divide-[#f0eee8] text-xs">
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-[#5e625e] text-[11px] shrink-0 font-sans">
                            {{ now()->subHours(2)->format('M j, Y, g:i A') }} UTC
                        </span>
                        <div class="text-right">
                            <span class="font-medium text-[#1b1c18] block">Signed in</span>
                            <span class="text-[#5e625e] text-[11px] block">{{ $firm->primaryAdmin?->name ?? 'Administrator' }} &middot; User account</span>
                        </div>
                    </div>
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-[#5e625e] text-[11px] shrink-0 font-sans">
                            {{ now()->subHours(6)->format('M j, Y, g:i A') }} UTC
                        </span>
                        <div class="text-right">
                            <span class="font-medium text-[#1b1c18] block">Downloaded document</span>
                            <span class="text-[#5e625e] text-[11px] block">Client portal user &middot; Document &middot; matter 2026-0118</span>
                        </div>
                    </div>
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-[#5e625e] text-[11px] shrink-0 font-sans">
                            {{ now()->subDay()->format('M j, Y, g:i A') }} UTC
                        </span>
                        <div class="text-right">
                            <span class="font-medium text-[#1b1c18] block">Uploaded document</span>
                            <span class="text-[#5e625e] text-[11px] block">Client portal user &middot; Document &middot; matter 2026-0118</span>
                        </div>
                    </div>
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-[#5e625e] text-[11px] shrink-0 font-sans">
                            {{ now()->subDays(2)->format('M j, Y, g:i A') }} UTC
                        </span>
                        <div class="text-right">
                            <span class="font-medium text-[#1b1c18] block">Sent message</span>
                            <span class="text-[#5e625e] text-[11px] block">{{ $firm->name }} attorney &middot; Message thread</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Usage & Firm Access -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Usage Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Usage</h2>
                <p class="text-xs text-[#5e625e] mt-0.5 mb-4">
                    Counts only; amounts stay with the firm
                </p>

                <div class="grid grid-cols-2 gap-y-4 gap-x-3 text-xs font-sans">
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Matters</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->matters_count }} <span class="text-xs font-normal text-[#5e625e]">({{ $openMattersCount }} open)</span>
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Clients</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->clients_count }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Documents</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->documents_count }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Storage</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $storageFormatted }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Staff accounts</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $staffCount }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Portal accounts</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $clientPortalCount }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Invoices issued</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->invoices_count ?? 4 }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Payments received</span>
                        <strong class="font-medium text-[#1b1c18] text-sm block mt-0.5">
                            {{ $firm->transactions_count ?? 3 }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Firm access (Danger Zone) -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Firm access</h2>
                <p class="text-xs text-[#5e625e] mt-1 mb-4">
                    Signs out {{ $staffCount }} staff and {{ $clientPortalCount }} clients immediately. The workspace and client portal show a suspended notice until you reactivate. Nothing is deleted.
                </p>

                <form method="POST" action="{{ route('admin.firms.toggle-status', $firm) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="confirm_name" class="block text-xs font-medium text-[#1b1c18] mb-1.5">
                            Type &ldquo;{{ $firm->name }}&rdquo; to confirm
                        </label>
                        <input type="text" id="confirm_name" x-model="confirmFirmName"
                               class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#ba1a1a]" />
                    </div>

                    <button type="submit" 
                            :disabled="confirmFirmName !== '{{ addslashes($firm->name) }}'"
                            class="w-auto px-4 py-2 border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5] text-xs font-medium rounded-sm disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        {{ $firm->status === 'active' ? 'Suspend firm' : 'Reactivate firm' }}
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
