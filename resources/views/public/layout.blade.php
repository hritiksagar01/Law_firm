<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Public Portal') — {{ $platformName ?? config('legal.app_name', 'Vennamraj Associates') }}</title>
    <link rel="icon" type="image/png" href="{{ $logoUrl ?? \App\Models\PlatformSetting::logoUrl() }}"/>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, input, textarea, button {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .font-mono, code, pre {
            font-family: 'JetBrains Mono', monospace !important;
        }
    </style>
</head>
<body class="bg-[#fbf9f5] text-[#1b1c18] antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-[#e5e7eb] sticky top-0 z-30 shadow-2xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('login') }}" class="flex items-center gap-3">
                <img src="{{ $logoUrl ?? \App\Models\PlatformSetting::logoUrl() }}" alt="{{ $platformName }}" class="h-9 w-auto object-contain rounded"/>
                <div class="flex flex-col">
                    <span class="text-[14.5px] font-semibold text-[#111827] leading-tight tracking-tight">{{ $platformName ?? 'Vennamraj Associates' }}</span>
                    <span class="text-[11px] text-[#6b7280] font-sans">Legal Chambers Cloud</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-[13px] font-medium text-[#4b5563]">
                <a href="{{ route('public.about') }}" class="hover:text-[#093225] transition-colors {{ request()->routeIs('public.about') ? 'text-[#093225] font-semibold' : '' }}">About</a>
                <a href="{{ route('public.contact') }}" class="hover:text-[#093225] transition-colors {{ request()->routeIs('public.contact') ? 'text-[#093225] font-semibold' : '' }}">Contact &amp; Registry</a>
                <a href="{{ route('public.privacy') }}" class="hover:text-[#093225] transition-colors {{ request()->routeIs('public.privacy') ? 'text-[#093225] font-semibold' : '' }}">Privilege &amp; Privacy</a>
                <a href="{{ route('public.terms') }}" class="hover:text-[#093225] transition-colors {{ request()->routeIs('public.terms') ? 'text-[#093225] font-semibold' : '' }}">Terms</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-md bg-[#093225] hover:bg-[#1b4332] text-white text-[12.5px] font-medium transition-colors shadow-2xs flex items-center gap-1.5">
                    <span>Sign In</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 py-8 sm:py-12">
        @yield('content')
    </main>

    <!-- Global Dynamic Footer -->
    <footer class="bg-white border-t border-[#e5e7eb] mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Col 1: Platform Brand & Tagline -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $logoUrl ?? \App\Models\PlatformSetting::logoUrl() }}" alt="{{ $platformName }}" class="h-8 w-auto object-contain rounded"/>
                        <span class="text-[14px] font-semibold text-[#111827]">{{ $platformName ?? 'Vennamraj Associates' }}</span>
                    </div>
                    <p class="text-[13px] font-medium text-[#1f2937] leading-snug">
                        {{ $data['footer_headline'] ?? 'Enterprise Legal Chambers Practice Management Platform' }}
                    </p>
                    <p class="text-[12px] text-[#6b7280] leading-relaxed max-w-md">
                        {{ $data['footer_description'] ?? 'High-security multi-tenant litigation dockets, client privilege isolation, and document repository for bar advocates.' }}
                    </p>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-2.5 text-[12.5px]">
                    <span class="font-semibold text-[#111827] text-[13px] block mb-1">Chambers Information</span>
                    <div><a href="{{ route('public.about') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">About the Platform</a></div>
                    <div><a href="{{ route('public.contact') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">Registry &amp; Grievances</a></div>
                    <div><a href="{{ route('login') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">Advocate Workspace Sign In</a></div>
                    <div><a href="{{ route('register') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">Register Practice Chambers</a></div>
                </div>

                <!-- Col 3: Compliance & Legal Policies -->
                <div class="space-y-2.5 text-[12.5px]">
                    <span class="font-semibold text-[#111827] text-[13px] block mb-1">Privilege &amp; Governance</span>
                    <div><a href="{{ route('public.privacy') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">Privilege &amp; Data Policy</a></div>
                    <div><a href="{{ route('public.terms') }}" class="text-[#6b7280] hover:text-[#093225] transition-colors">Terms of Practice Service</a></div>
                    <div class="text-[11.5px] text-[#6b7280] pt-1">
                        <span class="material-symbols-outlined text-[14px] text-[#093225] align-middle">lock</span>
                        <span class="align-middle">Sec. 126 Evidence Act Protected</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-6 border-t border-[#f3f4f6] flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-[#6b7280]">
                <div>
                    {{ $data['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformName ?? 'Vennamraj Associates') . '. All rights reserved.') }}
                </div>
                <div class="flex items-center gap-4">
                    <span>Multi-Chambers Legal Infrastructure</span>
                    <span>·</span>
                    <span>AES-256 Cloud Encrypted</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
