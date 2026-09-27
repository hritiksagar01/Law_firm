<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Client Portal Registration — {{ \App\Models\PlatformSetting::platformName() }}</title>
    <link rel="icon" type="image/png" href="{{ \App\Models\PlatformSetting::logoUrl() }}"/>

    <!-- Google Fonts: Inter, Newsreader & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black/65 backdrop-blur-xs text-[#1A1E1C] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 relative selection:bg-[#23493A]/10 selection:text-[#23493A]">

    <!-- Client Representation Intake Overlay Modal (Matching Dashboard & Super Admin) -->
    @include('auth.partials.client-registration-modal', ['firms' => $firms])

    <!-- Public Footer -->
    <footer class="w-full max-w-[500px] text-center my-4 text-[12px] space-y-1.5 text-white/80">
        <div class="flex items-center justify-center gap-3 text-[11.5px] text-white/70">
            <a href="{{ route('public.about') }}" target="_blank" class="hover:text-white transition-colors">About</a>
            <span>&middot;</span>
            <a href="{{ route('public.contact') }}" target="_blank" class="hover:text-white transition-colors">Contact Registry</a>
            <span>&middot;</span>
            <a href="{{ route('public.privacy') }}" target="_blank" class="hover:text-white transition-colors">Privilege &amp; Privacy</a>
            <span>&middot;</span>
            <a href="{{ route('public.terms') }}" target="_blank" class="hover:text-white transition-colors">Terms</a>
        </div>
        <p class="text-[11px] text-white/60">
            &copy; {{ date('Y') }} {{ \App\Models\PlatformSetting::platformName() }}. All rights reserved.
        </p>
        <p class="text-[11px] text-white/60">
            Designed and Developed by 
            <a href="https://skybridgeit.com/" target="_blank" rel="noopener noreferrer" 
               class="text-white/80 hover:text-white underline">Skybridge IT Consulting</a>
        </p>
    </footer>

</body>
</html>
