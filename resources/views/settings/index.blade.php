<x-app-layout>
    <x-slot name="title">Chambers Settings &amp; Team Management — Quire Legal</x-slot>

    <div x-data="{ openInviteModal: false }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#EFECE6] mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-[#766A5E] uppercase tracking-wider">Practice Configuration &amp; Governance</span>
                </div>
                <h1 class="text-2xl font-serif font-bold text-[#222222]">Firm Settings &amp; Administration</h1>
                <p class="text-sm text-[#766A5E]">Multi-tenant chambers profile, attorney hourly rates, and IOLTA compliance</p>
            </div>
            <button @click="openInviteModal = true" class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-[#845D33] text-white text-xs font-medium hover:bg-[#6D4B27] shadow-sm transition-colors">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Invite Team Member</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2-Cols: Team Members & Practice Roles -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Team Roster Table -->
                <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#845D33]">badge</span>
                            <h3 class="text-sm font-semibold text-[#222222]">Chambers Counsel &amp; Staff Roster</h3>
                        </div>
                        <span class="font-mono text-xs text-[#766A5E]">{{ $users->count() }} Authorized Users</span>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                                <th class="py-3 px-4">Counsel / Staff</th>
                                <th class="py-3 px-4">Practice Role</th>
                                <th class="py-3 px-4">Standard Rate</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4EFEA]">
                            @foreach($users as $user)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#EAE4DC]" src="{{ $user->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80' }}"/>
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-xs text-[#222222]">{{ $user->name }}</span>
                                            <span class="text-[11px] text-[#766A5E]">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded font-bold {{ $user->role === 'partner' ? 'bg-[#F4ECE1]/50 text-[#845D33]' : 'bg-[#F4EFEA] text-[#554D45]' }}">
                                        {{ $user->title ?? ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-[#845D33]">
                                    ${{ number_format($user->hourly_rate, 2) }}/hr
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#845D33]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#F8F4EE]0"></span> Active
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Firm Identity Details Card -->
                <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-[#222222] mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33]">domain</span>
                        <span>Chambers Legal Entity Profile</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#766A5E] block">Official Firm Name</span>
                            <span class="font-semibold text-[#222222] text-sm">{{ $firm->name ?? 'Chen & Sterling LLP' }}</span>
                        </div>
                        <div>
                            <span class="text-[#766A5E] block">Dedicated Subdomain</span>
                            <span class="font-mono text-[#845D33]">{{ $firm->domain ?? 'chensterling.quirelegal.io' }}</span>
                        </div>
                        <div>
                            <span class="text-[#766A5E] block">Base Billing Currency</span>
                            <span class="font-mono font-medium text-[#222222]">{{ $firm->currency ?? 'USD ($)' }}</span>
                        </div>
                        <div>
                            <span class="text-[#766A5E] block">Primary Timezone</span>
                            <span class="font-mono font-medium text-[#222222]">{{ $firm->timezone ?? 'America/New_York (EST)' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: IOLTA Escrow & Compliance -->
            <div class="flex flex-col gap-6">
                <!-- State Bar Compliance Box -->
                <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-[#845D33]">verified_user</span>
                        <h3 class="text-xs font-mono uppercase tracking-wider text-[#845D33] font-bold">Fiduciary Trust Account</h3>
                    </div>
                    <p class="text-xs text-[#554D45] leading-relaxed mb-4">
                        Chen &amp; Sterling LLP operates under mandatory three-way reconciliation compliance in accordance with ABA Model Rule 1.15.
                    </p>
                    <div class="p-3 rounded-lg bg-[#F8F4EE] border border-[#F4ECE1] flex flex-col gap-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-[#845D33]">Escrow Account:</span>
                            <span class="font-mono font-semibold text-[#222222]">****4902 (Chase Trust)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#845D33]">Total Escrow Balance:</span>
                            <span class="font-mono font-bold text-[#6D4B27]">${{ number_format(\App\Models\Client::sum('trust_balance'), 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#845D33]">Audit Status:</span>
                            <span class="font-mono text-[10px] uppercase font-bold text-[#845D33] bg-[#F4ECE1] px-1.5 py-0.5 rounded">Reconciled Clean</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Logout Action -->
                <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                    <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-2 font-semibold">Chambers Terminal Access</h3>
                    <p class="text-xs text-[#766A5E] mb-4">Sign out of this session to return to the public login or registration portal.</p>
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
            <div class="bg-white rounded-xl shadow-2xl border border-[#EFECE6] w-full max-w-md p-6 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4EFEA] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#845D33]">person_add</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Invite Attorney or Staff</h3>
                    </div>
                    <button type="button" @click="openInviteModal = false" class="text-[#766A5E] hover:text-[#222222]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('settings.team') }}" method="POST" class="flex flex-col gap-3.5 text-xs">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Legal Professional Full Name</label>
                        <input name="name" required type="text" placeholder="e.g. Rachel Hernandez, Esq." class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-[#222222]">Firm Email Address</label>
                        <input name="email" required type="email" placeholder="rhernandez@chensterling.com" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Practice Role</label>
                            <select name="role" class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]">
                                <option value="associate" selected>Associate Attorney</option>
                                <option value="partner">Partner</option>
                                <option value="paralegal">Litigation Paralegal</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-[#222222]">Billing Rate ($/hr)</label>
                            <input name="hourly_rate" type="number" step="25" value="450" required class="w-full px-3 py-2 rounded-lg bg-white border border-[#EAE4DC] outline-none focus:border-[#845D33]"/>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#F4EFEA] flex items-center justify-end gap-2">
                        <button type="button" @click="openInviteModal = false" class="px-4 py-2 rounded-lg border border-[#EAE4DC] text-[#554D45] hover:bg-[#FAF8F5]">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#845D33] text-white font-semibold hover:bg-[#6D4B27]">Authorize &amp; Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
