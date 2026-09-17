<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Edit {{ $firm->name }} — Super Admin Console</title>
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
            <a href="{{ route('admin.plans.index') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">subscriptions</span>
                <span>Plans</span>
            </a>
            <a href="{{ route('admin.tests.index') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">verified_user</span>
                <span>Audit Tests</span>
            </a>
            <a href="{{ route('admin.settings.mail') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#3A322B] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">mail</span>
                <span>Mail Gateway</span>
            </a>
        </nav>

        <a href="{{ route('admin.firms.index') }}" class="text-xs text-[#FAF8F5] hover:text-[#B88B56] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Firms</span>
        </a>
    </header>

    <main class="max-w-4xl mx-auto p-6 md:p-8">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.firms.show', $firm) }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Firm Overview</span>
                </a>
                <h1 class="text-3xl font-serif font-bold text-[#222222]">Edit {{ $firm->name }}</h1>
                <p class="text-sm text-[#766A5E] mt-1">Update organizational details, status, contact information, and practice jurisdiction</p>
            </div>
            <div>
                @if($firm->status === 'active')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Active Practice
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Inactive
                </span>
                @endif
            </div>
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

        <form method="POST" action="{{ route('admin.firms.update', $firm) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Firm Identity -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">domain</span>
                    <span>Firm Information</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Law Firm / Chamber Name *</label>
                        <input type="text" name="name" value="{{ old('name', $firm->name) }}" required
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Subdomain Slug</label>
                        <div class="flex items-center">
                            <input type="text" name="slug" value="{{ old('slug', $firm->slug) }}" required
                                class="w-full px-3.5 py-2 text-sm rounded-l-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                            <span class="px-3 py-2 text-xs font-mono bg-[#EFECE6] text-[#766A5E] border border-l-0 border-[#EAE4DC] rounded-r-lg">.lexiscore.app</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Operational Status *</label>
                        <select name="status" class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                            <option value="active" {{ old('status', $firm->status) == 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                            <option value="inactive" {{ old('status', $firm->status) == 'inactive' ? 'selected' : '' }}>Inactive (Restricted)</option>
                            <option value="suspended" {{ old('status', $firm->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Official Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $firm->email) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $firm->phone) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Official Website</label>
                        <input type="url" name="website" value="{{ old('website', $firm->website) }}" placeholder="https://www.lawfirm.com"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>
                </div>
            </div>

            <!-- Section 2: Location -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">location_on</span>
                    <span>Address &amp; Currency</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Chamber / Office Address</label>
                        <input type="text" name="address" value="{{ old('address', $firm->address) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $firm->city) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">State / Province</label>
                        <input type="text" name="state" value="{{ old('state', $firm->state) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $firm->postal_code) }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', $firm->country ?? 'India') }}"
                            class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Currency</label>
                        <select name="currency" class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                            <option value="INR" {{ old('currency', $firm->currency) == 'INR' ? 'selected' : '' }}>INR (₹) — Indian Rupee</option>
                            <option value="USD" {{ old('currency', $firm->currency) == 'USD' ? 'selected' : '' }}>USD ($) — US Dollar</option>
                            <option value="GBP" {{ old('currency', $firm->currency) == 'GBP' ? 'selected' : '' }}>GBP (£) — British Pound</option>
                            <option value="EUR" {{ old('currency', $firm->currency) == 'EUR' ? 'selected' : '' }}>EUR (€) — Euro</option>
                            <option value="AED" {{ old('currency', $firm->currency) == 'AED' ? 'selected' : '' }}>AED (د.إ) — UAE Dirham</option>
                            <option value="SGD" {{ old('currency', $firm->currency) == 'SGD' ? 'selected' : '' }}>SGD (S$) — Singapore Dollar</option>
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
                <p class="text-xs text-[#766A5E] mb-4">Select the legal disciplines handled by this firm</p>

                @php
                    $currentAreas = is_array($firm->practice_areas) ? $firm->practice_areas : [];
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($defaultPracticeAreas as $area)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] hover:bg-white cursor-pointer text-xs transition-colors">
                        <input type="checkbox" name="practice_areas[]" value="{{ $area }}"
                            {{ in_array($area, $currentAreas) ? 'checked' : '' }}
                            class="rounded text-[#9F8349] focus:ring-[#9F8349]" />
                        <span class="text-[#222222]">{{ $area }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.firms.show', $firm) }}" class="px-5 py-2.5 rounded-lg border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>

    </main>
</body>
</html>
