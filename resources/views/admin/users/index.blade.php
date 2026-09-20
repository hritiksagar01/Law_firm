@extends('layouts.admin')

@section('title', 'Clients — Platform Console')

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
        <h1 class="text-3xl font-serif text-[#1b1c18] tracking-tight">Clients</h1>
        <p class="text-xs text-[#5e625e] mt-1 font-sans">
            Firm staff and client portal accounts, sorted by firm then name
        </p>
    </div>

    <!-- 1. Platform Administrators Card -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <h2 class="text-sm font-semibold text-[#1b1c18]">Platform administrators</h2>
            <p class="text-xs text-[#5e625e] mt-0.5">Not attached to any firm</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-2.5 px-4 font-normal">Name</th>
                        <th class="py-2.5 px-4 font-normal">Role</th>
                        <th class="py-2.5 px-4 font-normal">Status</th>
                        <th class="py-2.5 px-4 font-normal">Two-step</th>
                        <th class="py-2.5 px-4 font-normal text-right">Last sign-in</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($platformAdmins as $admin)
                    <tr onclick="window.location='{{ route('admin.users.show', $admin) }}'"
                        class="hover:bg-[#fbf9f5] cursor-pointer transition-colors group">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.users.show', $admin) }}" class="font-medium text-[#1b1c18] group-hover:underline text-[13px] block">
                                {{ $admin->name }}
                            </a>
                            <span class="text-[11px] text-[#5e625e] font-mono block mt-0.5">
                                {{ $admin->email }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18] font-sans">
                            Platform admin
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                Active
                            </span>
                        </td>
                        <td class="py-3 px-4 text-[#5e625e] font-sans">
                            {{ $admin->two_factor_confirmed_at ? 'On' : 'Off' }}
                        </td>
                        <td class="py-3 px-4 text-[#5e625e] font-sans text-right">
                            {{ $admin->updated_at ? $admin->updated_at->diffForHumans(null, true) : '2 hr ago' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 px-4 text-center text-xs text-[#5e625e]">
                            No platform administrators registered.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Firm Accounts Card & Filter Bar -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm shadow-none overflow-hidden">
        <div class="p-4 border-b border-[#e5e3dc] bg-white">
            <h2 class="text-sm font-semibold text-[#1b1c18]">Firm accounts</h2>
        </div>

        <!-- Filter Bar -->
        <div class="p-3 bg-[#fbf9f5] border-b border-[#e5e3dc]">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[240px]">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Staff name or email, or a client's full email"
                           class="w-full px-3 py-1.5 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] transition-colors" />
                </div>

                <!-- Firm Filter -->
                <div class="min-w-[140px]">
                    <div class="relative">
                        <select name="firm_id"
                                class="w-full px-3 py-1.5 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">All firms</option>
                            @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ request('firm_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Role Filter -->
                <div class="min-w-[130px]">
                    <div class="relative">
                        <select name="role"
                                class="w-full px-3 py-1.5 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">All roles</option>
                            <option value="firm_admin" {{ request('role') === 'firm_admin' ? 'selected' : '' }}>Firm administrator</option>
                            <option value="attorney" {{ request('role') === 'attorney' ? 'selected' : '' }}>Attorney</option>
                            <option value="paralegal" {{ request('role') === 'paralegal' ? 'selected' : '' }}>Paralegal / staff</option>
                            <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="min-w-[110px]">
                    <div class="relative">
                        <select name="status"
                                class="w-full px-3 py-1.5 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8 transition-colors">
                            <option value="all">Any</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="invited" {{ request('status') === 'invited' ? 'selected' : '' }}>Invited</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="px-4 py-1.5 bg-white border border-[#dcdad4] text-[#1b1c18] hover:bg-[#f5f3ed] text-xs font-medium rounded-sm transition-colors shadow-none">
                    Apply
                </button>
                @if(request('q') || request('firm_id') || request('role') || request('status'))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-[#5e625e] hover:underline px-1">
                    Clear
                </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e3dc] text-[11px] font-sans text-[#5e625e] bg-white font-normal">
                        <th class="py-2.5 px-4 font-normal">Name</th>
                        <th class="py-2.5 px-4 font-normal">Role</th>
                        <th class="py-2.5 px-4 font-normal">Status</th>
                        <th class="py-2.5 px-4 font-normal">Two-step</th>
                        <th class="py-2.5 px-4 font-normal">Last sign-in</th>
                        <th class="py-2.5 px-4 font-normal">Firm</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0eee8]">
                    @forelse($firmAccounts as $u)
                    @php
                        $roleDisplay = match($u->role) {
                            'partner' => 'Firm administrator',
                            'associate' => 'Attorney',
                            'paralegal', 'staff' => 'Paralegal / staff',
                            'client' => 'Client',
                            default => ucfirst($u->role),
                        };
                        $isClient = ($u->role === 'client');
                        $displayName = $isClient ? 'Client portal user' : $u->name;
                        $displayEmail = $isClient 
                            ? (strlen($u->email) > 3 ? substr($u->email, 0, 1) . '***@' . explode('@', $u->email)[1] : $u->email)
                            : $u->email;
                        $isInvited = ($u->status === 'invited' || empty($u->email_verified_at) && $u->created_at->diffInDays() < 7 && !$u->two_factor_confirmed_at);
                    @endphp
                    <tr onclick="window.location='{{ route('admin.users.show', $u) }}'"
                        class="hover:bg-[#fbf9f5] cursor-pointer transition-colors group">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-[#1b1c18] group-hover:underline text-[13px] block">
                                {{ $displayName }}
                            </a>
                            <span class="text-[11px] text-[#5e625e] font-mono block mt-0.5">
                                {{ $displayEmail }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18] font-sans">
                            {{ $roleDisplay }}
                        </td>
                        <td class="py-3 px-4">
                            @if($isInvited)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#e0f2fe] text-[#0369a1] border border-[#bae6fd]">
                                Invited
                            </span>
                            @elseif($u->status === 'suspended')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#fee2e2] text-[#991b1b] border border-[#fecaca]">
                                Suspended
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#166534] border border-[#bbf7d0]">
                                Active
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[#5e625e] font-sans">
                            {{ $u->two_factor_confirmed_at ? 'On' : 'Off' }}
                        </td>
                        <td class="py-3 px-4 text-[#5e625e] font-sans">
                            {{ $u->updated_at ? $u->updated_at->diffForHumans(null, true) : 'Never' }}
                        </td>
                        <td class="py-3 px-4 text-[#1b1c18] font-sans">
                            @if($u->firm)
                            <a href="{{ route('admin.firms.show', $u->firm) }}" class="hover:underline text-[#1b1c18]" onclick="event.stopPropagation()">
                                {{ $u->firm->name }}
                            </a>
                            @else
                            <span class="text-[#5e625e]">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 px-4 text-center text-xs text-[#5e625e]">
                            No firm accounts found matching the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($firmAccounts->hasPages())
        <div class="p-3 border-t border-[#e5e3dc] bg-white">
            {{ $firmAccounts->links() }}
        </div>
        @endif
    </div>

    <!-- 3. Invite a staff member Section -->
    <div class="bg-white border border-[#e5e3dc] rounded-sm p-6 shadow-none">
        <h2 class="text-sm font-semibold text-[#1b1c18]">Invite a staff member</h2>
        <p class="text-xs text-[#5e625e] mt-1 mb-5">
            Counts against the firm's seats. Clients are invited by their firm from the client record.
        </p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Firm Select -->
                <div>
                    <label for="inv_firm" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Firm</label>
                    <div class="relative">
                        <select name="firm_id" id="inv_firm" required
                                class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="">Select firm...</option>
                            @foreach($firms as $f)
                            <option value="{{ $f->id }}" {{ old('firm_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('firm_id')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Select -->
                <div>
                    <label for="inv_role" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Role</label>
                    <div class="relative">
                        <select name="role" id="inv_role" required
                                class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a] appearance-none pr-8">
                            <option value="associate" {{ old('role') === 'associate' ? 'selected' : '' }}>Attorney</option>
                            <option value="partner" {{ old('role') === 'partner' ? 'selected' : '' }}>Firm administrator</option>
                            <option value="paralegal" {{ old('role') === 'paralegal' ? 'selected' : '' }}>Paralegal / staff</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#5e625e]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <label for="inv_name" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Name</label>
                    <input type="text" name="name" id="inv_name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                    @error('name')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="inv_email" class="block text-xs font-medium text-[#1b1c18] mb-1.5">Email</label>
                    <input type="email" name="email" id="inv_email" required value="{{ old('email') }}"
                           class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                    @error('email')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title (Optional) -->
                <div class="md:col-span-2">
                    <label for="inv_title" class="block text-xs font-medium text-[#1b1c18] mb-1.5">
                        Title <span class="text-[#5e625e] font-normal">Optional.</span>
                    </label>
                    <input type="text" name="title" id="inv_title" value="{{ old('title') }}"
                           placeholder="e.g. Associate"
                           class="w-full px-3 py-2 text-xs rounded-sm border border-[#dcdad4] bg-white text-[#1b1c18] focus:outline-none focus:border-[#23493a]" />
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-[#23493a] text-white text-xs font-medium rounded-sm hover:bg-[#1a382c] transition-colors shadow-none">
                    Send invitation
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
