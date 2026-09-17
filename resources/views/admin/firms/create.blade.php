<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Provision Law Firm — Super Admin Console</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen">
    
    <!-- Executive Top Header -->
    <header class="h-16 bg-[#222222] text-white px-6 flex items-center justify-between shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Platform Logo" class="h-9 w-auto object-contain rounded-md bg-white p-0.5"/>
            </a>
            <div>
                <span class="font-serif text-base font-bold text-white tracking-wide">Multi-Tenant Platform Console</span>
                <span class="text-[10px] font-mono text-[#8C7F72] block -mt-1">Super Administrator Access</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#2E2823] p-1 rounded-lg border border-[#3E352E] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.firms.index') }}" class="px-3 py-1.5 rounded-md bg-[#9F8349] text-white font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">corporate_fare</span>
                <span>Law Firms</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#FAF8F5] hover:text-[#B88B56] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Firms</span>
        </a>
    </header>

    <main class="max-w-4xl mx-auto p-6 md:p-8">
        
        <div class="mb-8">
            <a href="{{ route('admin.firms.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>All Law Firms</span>
            </a>
            <h1 class="text-3xl font-serif font-bold text-[#222222]">Provision New Law Firm</h1>
            <p class="text-sm text-[#766A5E] mt-1">Register a new independent law practice or chambers organization on the platform</p>
        </div>

        @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.firms.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Firm Identity -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">domain</span>
                    <span>Firm Information</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Law Firm / Chamber Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Vennamraj &amp; Associates Advocates"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Subdomain Slug (URL identifier)</label>
                        <div class="flex items-center">
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="e.g. vennamraj"
                                class="w-full px-3.5 py-2 text-sm rounded-l-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                            <span class="px-3 py-2 text-xs font-mono bg-[#EFECE6] text-[#766A5E] border border-l-0 border-[#EAE4DC] rounded-r-lg">.lexiscore.app</span>
                        </div>
                        <p class="text-[11px] text-[#766A5E] mt-1">Leave empty to automatically generate from firm name.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Official Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="office@firm.com"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Official Website</label>
                        <input type="url" name="website" value="{{ old('website') }}" placeholder="https://www.lawfirm.com"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>
                </div>
            </div>

            <!-- Section 2: Location & Jurisdiction -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">location_on</span>
                    <span>Address &amp; Jurisdiction</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Chamber / Office Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Suite / Chamber No., High Court Compound"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" placeholder="Hyderabad"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">State / Province</label>
                        <input type="text" name="state" value="{{ old('state') }}" placeholder="Telangana"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Postal Code / PIN</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="500001"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'India') }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Currency</label>
                        <select name="currency" class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                            <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>INR (₹) — Indian Rupee</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($) — US Dollar</option>
                            <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP (£) — British Pound</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (€) — Euro</option>
                            <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED (د.إ) — UAE Dirham</option>
                            <option value="SGD" {{ old('currency') == 'SGD' ? 'selected' : '' }}>SGD (S$) — Singapore Dollar</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Practice Areas -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">gavel</span>
                    <span>Practice Areas</span>
                </h2>
                <p class="text-xs text-[#766A5E] mb-4">Select the legal disciplines practiced by this firm (can be modified later by firm admin)</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($defaultPracticeAreas as $area)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] hover:bg-white cursor-pointer text-xs transition-colors">
                        <input type="checkbox" name="practice_areas[]" value="{{ $area }}" checked
                            class="rounded text-[#9F8349] focus:ring-[#9F8349]" />
                        <span class="text-[#222222]">{{ $area }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Section 4: Initial Managing Partner Account (Optional) -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">person_add</span>
                    <span>Initial Firm Administrator / Managing Partner</span>
                </h2>
                <p class="text-xs text-[#766A5E] mb-4">Provision the primary partner login who will manage staff and cases in this firm</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Full Name</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Adv. Rajesh Vennamraj"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Partner Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="rajesh@firm.com"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Temporary Password</label>
                        <input type="password" name="admin_password" placeholder="Min. 8 characters"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.firms.index') }}" class="px-5 py-2.5 rounded-lg border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Provision Law Firm</span>
                </button>
            </div>
        </form>

    </main>
</body>
</html>
