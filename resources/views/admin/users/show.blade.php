@extends('layouts.admin')

@section('title', $user->name . ' — Users — Platform Console')

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

    <!-- Breadcrumb & Header -->
    <div>
        <nav class="text-xs text-[#5e625e] mb-1 font-sans flex items-center gap-1.5">
            <a href="{{ route('admin.users.index') }}" class="hover:underline text-[#5e625e]">Users</a>
            <span class="text-[#a3a5a8]">&nbsp;/&nbsp;</span>
            <span class="text-[#1b1c18] font-medium">{{ $user->name }}</span>
        </nav>

        <h1 class="text-3xl font-serif text-[#1b1c18] tracking-tight">{{ $user->name }}</h1>

        @php
            $roleLabel = match($user->role) {
                'partner' => 'Firm administrator',
                'associate' => 'Attorney',
                'paralegal', 'staff' => 'Paralegal / staff',
                'client' => 'Client',
                'superadmin' => 'Platform admin',
                default => ucfirst($user->role),
            };
        @endphp

        <!-- Meta Line -->
        <div class="flex flex-wrap items-center gap-2.5 mt-2 text-xs font-sans">
            <span class="text-[#5e625e] font-mono text-[11px]">{{ $user->email }}</span>

            @if($user->status !== 'suspended')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                Active
            </span>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                Suspended
            </span>
            @endif

            <span class="text-[#1b1c18]">{{ $roleLabel }}</span>
        </div>
    </div>

    <!-- Two Column Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Account, Active sessions, Sign-in attempts, Recent actions -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Account Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18] mb-4">Account</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <!-- Firm -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Firm</span>
                        @if($user->firm)
                        <a href="{{ route('admin.firms.show', $user->firm) }}" class="text-[#23493a] hover:underline font-medium block mt-0.5">
                            {{ $user->firm->name }}
                        </a>
                        @else
                        <span class="text-[#1b1c18] block mt-0.5">&mdash;</span>
                        @endif
                    </div>

                    <!-- Title -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Title</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-medium">
                            {{ $user->title ?? 'Office Manager' }}
                        </span>
                    </div>

                    <!-- Phone -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Phone</span>
                        <span class="text-[#1b1c18] block mt-0.5 font-mono">
                            {{ $user->phone ?? '&mdash;' }}
                        </span>
                    </div>

                    <!-- Two-step sign-in -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Two-step sign-in</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->two_factor_confirmed_at ? 'On' : 'Off' }}
                        </span>
                    </div>

                    <!-- Notifications -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Notifications</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            Email
                        </span>
                    </div>

                    <!-- Last sign-in -->
                    <div>
                        <span class="text-[#5e625e] block text-[11px]">Last sign-in</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->updated_at ? $user->updated_at->format('M j, Y, g:i A') . ' UTC' : 'Never' }}
                        </span>
                    </div>

                    <!-- Created -->
                    <div class="md:col-span-2">
                        <span class="text-[#5e625e] block text-[11px]">Created</span>
                        <span class="text-[#1b1c18] block mt-0.5">
                            {{ $user->created_at->format('M j, Y, g:i A') }} UTC
                        </span>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18]">Active sessions</h2>
                <p class="text-xs text-[#5e625e] mt-0.5 mb-4">0 signed-in devices</p>

                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-10 px-4 text-center text-xs text-[#5e625e]">
                    Not signed in anywhere
                </div>
            </div>

            <!-- Sign-in Attempts Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18]">Sign-in attempts</h2>
                <p class="text-xs text-[#5e625e] mt-0.5 mb-4">Last 15</p>

                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-10 px-4 text-center text-xs text-[#5e625e]">
                    No sign-in attempts recorded
                </div>
            </div>

            <!-- Recent Actions Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18] mb-4">Recent actions by this account</h2>

                <div class="bg-[#faf8f5] border border-[#f0eee8] rounded-sm py-10 px-4 text-center text-xs text-[#5e625e]">
                    No recorded actions
                </div>
            </div>

        </div>

        <!-- Right Column: Role & Access -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Role Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
                <h2 class="text-sm font-semibold text-[#1b1c18] mb-3">Role</h2>

                <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="space-y-3">
                    @csrf
                    <div class="relative">
                        <select name="role"
                                class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="firm_admin" {{ ($user->role === 'partner') ? 'selected' : '' }}>Firm administrator</option>
                            <option value="attorney" {{ ($user->role === 'associate') ? 'selected' : '' }}>Attorney</option>
                            <option value="paralegal" {{ in_array($user->role, ['paralegal', 'staff']) ? 'selected' : '' }}>Paralegal / staff</option>
                            <option value="client" {{ ($user->role === 'client') ? 'selected' : '' }}>Client</option>
                            <option value="platform_admin" {{ ($user->role === 'superadmin') ? 'selected' : '' }}>Platform admin</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="px-3 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none">
                            Change role
                        </button>
                    </div>
                </form>
            </div>

            <!-- Access Card -->
            <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none space-y-4">
                <h2 class="text-sm font-semibold text-[#1b1c18]">Access</h2>

                <!-- Send Password Reset -->
                <div>
                    <p class="text-xs text-[#5e625e] mb-2.5">
                        Emails a one-hour reset link to the account holder. The link is never shown here.
                    </p>
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none">
                            Send password reset
                        </button>
                    </form>
                </div>

                <!-- Active Session Termination -->
                <div class="pt-3 border-t border-[#f0eee8]">
                    <p class="text-xs text-[#5e625e] mb-1">
                        Two-step sign-in is not set up on this account.
                    </p>
                    <p class="text-xs text-[#5e625e] mb-2.5">
                        Not signed in on any device, so there is nothing to end.
                    </p>
                    <form method="POST" action="{{ route('admin.users.sign-out-everywhere', $user) }}">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 bg-white border border-[#dcdad4] text-xs font-medium text-[#1b1c18] hover:bg-[#f5f3ed] rounded-sm transition-colors shadow-none">
                            Sign out everywhere
                        </button>
                    </form>
                </div>

                <!-- Disable / Reactivate Account -->
                <div class="pt-3 border-t border-[#f0eee8]">
                    <p class="text-xs text-[#5e625e] mb-2.5">
                        Blocks sign-in, ends sessions and cancels unused invitation links. Nothing is deleted.
                    </p>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#fff5f5] text-xs font-medium rounded-sm transition-colors shadow-none">
                            {{ $user->status === 'active' ? 'Disable account' : 'Reactivate account' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
