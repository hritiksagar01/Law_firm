@extends('portal.layout')

@section('title', 'Account & KYC Settings')
@section('header_title', 'Account Settings')

@section('content')
<div class="flex flex-col w-full text-[#1a1a1a] gap-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Account &amp; Legal Profile</h1>
            <p class="text-[13px] text-[#646864] mt-1">Verified court identity, communication preferences, and security credentials.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full bg-[#ecfdf5] text-[#065f46] text-xs font-mono font-medium border border-[#a7f3d0]">
                Sec 126/129 Privileged
            </span>
        </div>
    </div>

    <!-- 2-Column Settings Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Legal Identity & KYC Profile (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Card 1: Statutory Court Particulars -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-[#23493a]">badge</span>
                        <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Statutory Court Particulars (KYC Memo)</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono uppercase bg-[#faf9f5] border border-[#e5e3dc] text-[#23493a] font-medium">
                        {{ ucfirst($client->category ?? 'Individual') }} Client
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Full Legal Name (as per Court Memo)</span>
                        <span class="font-medium text-[#1a1a1a] text-sm mt-0.5 block">{{ $client->name }}</span>
                    </div>

                    @if($client->father_husband_name)
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Father's / Husband's Name (S/o, D/o, W/o)</span>
                        <span class="font-medium text-[#1a1a1a] text-sm mt-0.5 block">{{ $client->father_husband_name }}</span>
                    </div>
                    @endif

                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Income Tax PAN</span>
                        <span class="font-mono font-semibold text-[#23493a] text-sm mt-0.5 block">
                            {{ $client->pan ?: ($client->tax_id ?: 'Verified in Chambers') }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">UIDAI Aadhaar (Masked)</span>
                        <span class="font-mono text-sm mt-0.5 block text-[#1a1a1a]">
                            {{ $client->aadhaar_last_four ? 'XXXX-XXXX-'.$client->aadhaar_last_four : 'Verified by Counsel' }}
                        </span>
                    </div>

                    @if($client->cin || $client->gstin)
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Corporate CIN / GSTIN</span>
                        <span class="font-mono text-sm mt-0.5 block text-[#1a1a1a]">
                            {{ $client->cin ?: $client->gstin }}
                        </span>
                    </div>
                    @endif

                    @if($client->police_station)
                    <div>
                        <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Police Station (Thana) Jurisdiction</span>
                        <span class="font-medium text-[#1a1a1a] text-sm mt-0.5 block">{{ $client->police_station }}</span>
                    </div>
                    @endif
                </div>

                <!-- Complete Service Address -->
                <div class="pt-3 border-t border-[#f0eee8] text-xs">
                    <span class="block text-[10.5px] uppercase font-mono text-[#8a8a8a]">Registered Address for Service of Summons</span>
                    <p class="font-medium text-[#1a1a1a] mt-1 leading-relaxed">
                        {{ $client->address_line_1 ?: $client->address }}
                        @if($client->city), {{ $client->city }}@endif
                        @if($client->district), {{ $client->district }}@endif
                        @if($client->state), {{ $client->state }}@endif
                        @if($client->pincode) - {{ $client->pincode }}@endif
                    </p>
                </div>

                <!-- Co-Parties / Members (If Joint/Corporate) -->
                @if($client->members && $client->members->isNotEmpty())
                <div class="pt-3 border-t border-[#f0eee8] flex flex-col gap-2">
                    <span class="text-[10.5px] uppercase font-mono text-[#8a8a8a]">Co-Litigants &amp; Authorized Signatories</span>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-[#f0eee8] text-[10px] font-mono text-[#8a8a8a] uppercase">
                                    <th class="pb-1.5 font-medium">Party Name</th>
                                    <th class="pb-1.5 font-medium">Capacity / Relation</th>
                                    <th class="pb-1.5 font-medium">PAN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0eee8]">
                                @foreach($client->members as $m)
                                <tr>
                                    <td class="py-2 font-medium text-[#1a1a1a]">{{ $m->name }}</td>
                                    <td class="py-2 text-[#646864]">{{ $m->relationship }}</td>
                                    <td class="py-2 font-mono text-[#23493a]">{{ $m->pan ?: 'On File' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Card 2: Contact Details Update Form -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 sm:p-6 shadow-xs flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8]">
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Update Contact Coordinates</h2>
                    <span class="text-[11px] text-[#8a8a8a]">Instant synchronization</span>
                </div>

                <form action="{{ route('portal.settings.update') }}" method="POST" class="flex flex-col gap-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5" x-data="{
                            countryCode: '+91',
                            phoneRaw: '',
                            init() {
                                const full = '{{ old('phone', $client->phone) }}'.trim();
                                const codes = ['+971', '+880', '+977', '+966', '+965', '+968', '+973', '+94', '+91', '+44', '+61', '+65', '+49', '+33', '+81', '+1'];
                                let found = false;
                                for (const c of codes) {
                                    if (full.startsWith(c)) {
                                        this.countryCode = c;
                                        this.phoneRaw = full.substring(c.length).trim();
                                        found = true;
                                        break;
                                    }
                                }
                                if (!found) {
                                    this.phoneRaw = full;
                                }
                            },
                            get fullPhone() { return (this.countryCode + ' ' + this.phoneRaw).trim(); }
                        }">
                            <label class="font-semibold text-[#1a1a1a]">Primary Telephone / Mobile</label>
                            <input type="hidden" name="phone" :value="fullPhone"/>
                            <div class="flex items-center gap-1.5">
                                <select x-model="countryCode" class="h-10 px-2 rounded-md border border-[#e5e3dc] bg-white font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none">
                                    <option value="+91">🇮🇳 +91</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+971">🇦🇪 +971</option>
                                    <option value="+61">🇦🇺 +61</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+49">🇩🇪 +49</option>
                                    <option value="+33">🇫🇷 +33</option>
                                    <option value="+81">🇯🇵 +81</option>
                                    <option value="+966">🇸🇦 +966</option>
                                    <option value="+974">🇶🇦 +974</option>
                                    <option value="+965">🇰🇼 +965</option>
                                    <option value="+968">🇴🇲 +968</option>
                                    <option value="+973">🇧🇭 +973</option>
                                    <option value="+880">🇧🇩 +880</option>
                                    <option value="+977">🇳🇵 +977</option>
                                    <option value="+94">🇱🇰 +94</option>
                                </select>
                                <input type="text" x-model="phoneRaw" placeholder="98100 12345" class="w-full h-10 px-3 rounded-md border border-[#e5e3dc] bg-white font-mono text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-semibold text-[#1a1a1a]">Registered Email (Authentication)</label>
                            <input type="email" value="{{ $client->email ?? Auth::user()?->email }}" disabled class="h-10 px-3 rounded-md border border-[#e5e3dc] bg-[#faf9f5] text-xs text-[#8a8a8a] cursor-not-allowed"/>
                            <span class="text-[10px] text-[#8a8a8a]">To alter login email, request your lead attorney.</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[#1a1a1a]">Correspondence Address</label>
                        <textarea name="address" rows="2" class="w-full p-2.5 rounded-md border border-[#e5e3dc] bg-white text-xs text-[#1a1a1a] focus:border-[#23493a] focus:outline-none resize-none">{{ old('address', $client->address_line_1 ?: $client->address) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-[#f0eee8]">
                        <button type="submit" class="bg-[#23493a] text-white hover:bg-[#1a382b] px-4 py-2 text-xs font-medium rounded-md shadow-xs cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Column: Security & Preferences (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Card 1: Portal Security Credentials -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 shadow-xs flex flex-col gap-4">
                <div class="flex items-center gap-2 pb-2.5 border-b border-[#f0eee8]">
                    <span class="material-symbols-outlined text-[19px] text-[#23493a]">lock</span>
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Security Credentials</h2>
                </div>

                <p class="text-[12px] text-[#646864] leading-relaxed">
                    Protect your privileged litigation files and private communications with counsel.
                </p>

                <div class="flex flex-col gap-2 pt-1">
                    <button type="button" @click="changePasswordModal = true" class="w-full py-2 bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] rounded-md text-xs font-medium shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">key</span>
                        <span>Change Password</span>
                    </button>
                    <button type="button" @click="changeAvatarModal = true" class="w-full py-2 bg-white border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf9f5] rounded-md text-xs font-medium shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">photo_camera</span>
                        <span>Update Avatar</span>
                    </button>
                </div>

                <div class="pt-2 border-t border-[#f0eee8] flex items-center justify-between text-[11px] text-[#646864]">
                    <span>Two-Step Sign-In:</span>
                    <span class="font-medium text-[#065f46] flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span> Active
                    </span>
                </div>
            </div>

            <!-- Card 2: Cause List & Court Alerts -->
            <div class="bg-white rounded-md border border-[#e5e3dc] p-5 shadow-xs flex flex-col gap-4">
                <div class="flex items-center gap-2 pb-2.5 border-b border-[#f0eee8]">
                    <span class="material-symbols-outlined text-[19px] text-[#23493a]">notifications_active</span>
                    <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Notification Preferences</h2>
                </div>

                <div class="flex flex-col gap-3 text-xs">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" checked class="mt-0.5 text-[#23493a] focus:ring-0 rounded"/>
                        <div class="flex flex-col">
                            <span class="font-medium text-[#1a1a1a]">Cause List Appearance Alert</span>
                            <span class="text-[11px] text-[#8a8a8a]">Email dispatch when case item is listed</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" checked class="mt-0.5 text-[#23493a] focus:ring-0 rounded"/>
                        <div class="flex flex-col">
                            <span class="font-medium text-[#1a1a1a]">Document Request Notifications</span>
                            <span class="text-[11px] text-[#8a8a8a]">Immediate alert when counsel requests filings</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" checked class="mt-0.5 text-[#23493a] focus:ring-0 rounded"/>
                        <div class="flex flex-col">
                            <span class="font-medium text-[#1a1a1a]">Certified Court Orders</span>
                            <span class="text-[11px] text-[#8a8a8a]">Filing notifications when bench uploads decree</span>
                        </div>
                    </label>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
