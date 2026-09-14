<x-app-layout>
    <x-slot name="title">Chambers Settings &amp; Team Management — Quire Legal</x-slot>

    <div x-data="{ openInviteModal: false }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#e9e8e5] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#727973] uppercase tracking-wider">Practice Configuration &amp; Governance</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#1a1c1a]">Firm Settings &amp; Administration</h1>
                <p class="text-sm text-[#727973]">Multi-tenant chambers profile, attorney hourly rates, and IOLTA compliance</p>
            </div>
            <button @click="openInviteModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Invite Team Member</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2-Cols: Team Members & Practice Roles -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Team Roster Table -->
                <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#1a3c2a]">badge</span>
                            <h3 class="text-sm font-semibold text-[#1a1c1a]">Chambers Counsel &amp; Staff Roster</h3>
                        </div>
                        <span class="font-mono text-xs text-[#727973]">{{ $users->count() }} Authorized Users</span>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#f4f3f1] border-b border-[#e9e8e5] text-[11px] font-mono text-[#727973] uppercase tracking-wider">
                                <th class="py-3 px-4">Counsel / Staff</th>
                                <th class="py-3 px-4">Practice Role</th>
                                <th class="py-3 px-4">Standard Rate</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#efeeeb]">
                            @foreach($users as $user)
                            <tr class="hover:bg-[#faf9f6]">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="{{ $user->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80' }}"/>
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-xs text-[#1a1c1a]">{{ $user->name }}</span>
                                            <span class="text-[11px] text-[#727973]">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded font-bold {{ $user->role === 'partner' ? 'bg-[#c5ecd2]/50 text-[#1a3c2a]' : 'bg-[#efeeeb] text-[#424843]' }}">
                                        {{ $user->title ?? ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-[#1a3c2a]">
                                    ${{ number_format($user->hourly_rate, 2) }}/hr
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Firm Identity Details Card -->
                <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-[#1a1c1a] mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a]">domain</span>
                        <span>Chambers Legal Entity Profile</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#727973] block">Official Firm Name</span>
                            <span class="font-semibold text-[#1a1c1a] text-sm">{{ $firm->name ?? 'Chen & Sterling LLP' }}</span>
                        </div>
                        <div>
                            <span class="text-[#727973] block">Dedicated Subdomain</span>
                            <span class="font-mono text-[#1a3c2a]">{{ $firm->domain ?? 'chensterling.quirelegal.io' }}</span>
                        </div>
                        <div>
                            <span class="text-[#727973] block">Base Billing Currency</span>
                            <span class="font-mono font-medium text-[#1a1c1a]">{{ $firm->currency ?? 'USD ($)' }}</span>
                        </div>
                        <div>
                            <span class="text-[#727973] block">Primary Timezone</span>
                            <span class="font-mono font-medium text-[#1a1c1a]">{{ $firm->timezone ?? 'America/New_York (EST)' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: IOLTA Escrow & Compliance -->
            <div class="flex flex-col gap-6">
                <!-- State Bar Compliance Box -->
                <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-[#1a3c2a]">verified_user</span>
                        <h3 class="text-xs font-mono uppercase tracking-wider text-[#1a3c2a] font-bold">Fiduciary Trust Account</h3>
                    </div>
                    <p class="text-xs text-[#424843] leading-relaxed mb-4">
                        Chen &amp; Sterling LLP operates under mandatory three-way reconciliation compliance in accordance with ABA Model Rule 1.15.
                    </p>
                    <div class="p-3 rounded-lg bg-[#f0f9f4] border border-[#c5ecd2] flex flex-col gap-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-[#2c4e3a]">Escrow Account:</span>
                            <span class="font-mono font-semibold text-[#002112]">****4902 (Chase Trust)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#2c4e3a]">Total Escrow Balance:</span>
                            <span class="font-mono font-bold text-emerald-800">${{ number_format(\App\Models\Client::sum('trust_balance'), 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#2c4e3a]">Audit Status:</span>
                            <span class="font-mono text-[10px] uppercase font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Reconciled Clean</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Logout Action -->
                <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-2 font-semibold">Chambers Terminal Access</h3>
                    <p class="text-xs text-[#727973] mb-4">Sign out of this session to return to the public login or registration portal.</p>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold border border-red-200 transition-colors flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">logout</span>
                            <span>Sign Out to Login Form</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Invite Team Member Modal -->
        <div x-show="openInviteModal" @click.away="openInviteModal = false" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-[#e9e8e5] w-full max-w-md p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#efeeeb] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a]">person_add</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Invite Attorney or Staff</h3>
                    </div>
                    <button type="button" @click="openInviteModal = false" class="text-[#727973] hover:text-[#1a1c1a]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('settings.team') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Legal Professional Full Name</label>
                        <input name="name" required type="text" placeholder="e.g. Rachel Hernandez, Esq." class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#1a1c1a]">Firm Email Address</label>
                        <input name="email" required type="email" placeholder="rhernandez@chensterling.com" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Practice Role</label>
                            <select name="role" class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]">
                                <option value="associate" selected>Associate Attorney</option>
                                <option value="partner">Partner</option>
                                <option value="paralegal">Litigation Paralegal</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#1a1c1a]">Billing Rate ($/hr)</label>
                            <input name="hourly_rate" type="number" step="25" value="450" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#efeeeb] flex items-center justify-end gap-2">
                        <button type="button" @click="openInviteModal = false" class="px-4 py-2 rounded-lg border border-[#c1c8c1] text-[#424843] hover:bg-[#faf9f6]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#1a3c2a] text-white font-semibold hover:bg-[#022616]">Authorize &amp; Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
