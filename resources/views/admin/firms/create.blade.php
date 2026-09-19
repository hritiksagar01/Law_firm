@extends('layouts.admin')

@section('title', 'Provision Law Firm')
@section('header_title', 'Provision Law Firm')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="mb-6">
        <a href="{{ route('admin.firms.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2 font-medium">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>All Law Firms</span>
        </a>
        <h1 class="text-3xl font-serif font-bold text-[#222222]">Provision New Law Firm</h1>
        <p class="text-sm text-[#766A5E] mt-1">Register a new independent law practice or chambers organization on the platform</p>
    </div>

    <form method="POST" action="{{ route('admin.firms.store') }}" class="space-y-6">
        @csrf

        <!-- Section 1: Firm Identity -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">domain</span>
                <span>Firm Identity & Branding</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Legal Registered Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Vennamraj &amp; Associates Advocates"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Display Name (Short / Brand Name)</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" placeholder="e.g. Vennamraj Associates"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Subdomain Slug (URL identifier)</label>
                    <div class="flex items-center">
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="e.g. vennamraj"
                            class="w-full px-3.5 py-2.5 text-sm rounded-l-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                        <span class="px-3 py-2.5 text-xs font-mono bg-[#EFECE6] text-[#766A5E] border border-l-0 border-[#EAE4DC] rounded-r-xl">.lexiscore.app</span>
                    </div>
                    <p class="text-[11px] text-[#766A5E] mt-1">Leave empty to automatically generate from firm name.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Contact Person Name</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" placeholder="e.g. Adv. Rajesh Vennamraj"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Official Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="office@firm.com"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Official Website</label>
                    <input type="url" name="website" value="{{ old('website') }}" placeholder="https://www.lawfirm.com"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>
            </div>
        </div>

        <!-- Section 2: Location & Jurisdiction -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">location_on</span>
                <span>Address &amp; Jurisdiction</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Chamber / Office Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Suite / Chamber No., High Court Compound"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" placeholder="Hyderabad"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">State / Province</label>
                    <input type="text" name="state" value="{{ old('state') }}" placeholder="Telangana"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Postal Code / PIN</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="500001"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Country</label>
                    <input type="text" name="country" value="{{ old('country', 'India') }}"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Currency</label>
                    <select name="currency" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>INR (₹) — Indian Rupee</option>
                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD ($) — US Dollar</option>
                        <option value="GBP" {{ old('currency', 'GBP') == 'GBP' ? 'selected' : '' }}>GBP (£) — British Pound</option>
                        <option value="EUR" {{ old('currency', 'EUR') == 'EUR' ? 'selected' : '' }}>EUR (€) — Euro</option>
                        <option value="AED" {{ old('currency', 'AED') == 'AED' ? 'selected' : '' }}>AED (د.إ) — UAE Dirham</option>
                        <option value="SGD" {{ old('currency', 'SGD') == 'SGD' ? 'selected' : '' }}>SGD (S$) — Singapore Dollar</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Practice Areas -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">gavel</span>
                <span>Practice Areas</span>
            </h2>
            <p class="text-xs text-[#766A5E] mb-4">Select the legal disciplines practiced by this firm (can be modified later by firm admin)</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($defaultPracticeAreas as $area)
                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] hover:bg-white cursor-pointer text-xs transition-colors">
                    <input type="checkbox" name="practice_areas[]" value="{{ $area }}" checked
                        class="rounded text-[#9F8349] focus:ring-[#9F8349]" />
                    <span class="text-[#222222]">{{ $area }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Section 4: Initial Managing Partner Account (Optional) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">person_add</span>
                <span>Initial Firm Administrator / Managing Partner</span>
            </h2>
            <p class="text-xs text-[#766A5E] mb-4">Provision the primary partner login who will manage staff and cases in this firm</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Full Name</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Adv. Rajesh Vennamraj"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Partner Email</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="rajesh@firm.com"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Temporary Password</label>
                    <input type="password" name="admin_password" placeholder="Min. 8 characters"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.firms.index') }}" class="px-5 py-2.5 rounded-xl border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">check</span>
                <span>Provision Law Firm</span>
            </button>
        </div>
    </form>

</div>
@endsection
