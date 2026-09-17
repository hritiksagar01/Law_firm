<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>404 — Record Not Found · SJM Law Firm Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl border border-[#EFECE6] p-8 shadow-sm text-center">
        <div class="w-16 h-16 rounded-2xl bg-[#F4EFEA] text-[#9F8349] flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-3xl">gavel</span>
        </div>
        <span class="font-mono text-xs font-bold uppercase tracking-wider text-[#9F8349] bg-[#F8F4EE] px-3 py-1 rounded-full border border-[#E8DAC8]">Error 404 · Docket Not Found</span>
        <h1 class="font-serif text-3xl font-bold text-[#222222] mt-4 mb-2">Matter or Page Not Found</h1>
        <p class="text-sm text-[#766A5E] leading-relaxed mb-6">
            The judicial record, case dossier, or platform page you requested does not exist in the active chambers registry or has been archived.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Return to Dashboard</span>
            </a>
            <button onclick="window.history.back()" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-white border border-[#EAE4DC] text-[#554D45] text-xs font-semibold hover:bg-[#F4EFEA] transition-all">
                Previous Page
            </button>
        </div>
    </div>
</body>
</html>
