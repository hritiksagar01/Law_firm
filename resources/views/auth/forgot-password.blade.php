<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Recover Vault Password — Quire Legal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f6] text-[#1a1c1a] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative">

    <div class="w-full max-w-md flex flex-col items-center my-8">
        
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#1a3c2a] flex items-center justify-center text-white shadow-md mb-3">
                <span class="material-symbols-outlined text-2xl text-[#fed977]">lock_reset</span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#1a1c1a] tracking-tight">Recover Chambers Access</h1>
            <p class="text-xs text-[#727973] mt-1">
                Enter your verified firm domain or client email to receive an encrypted reset token.
            </p>
        </div>

        @if(session('status'))
        <div class="w-full mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">mark_email_read</span>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        <div class="w-full bg-white rounded-xl shadow-xl border border-[#e9e8e5] p-6 flex flex-col gap-4">
            <form action="{{ route('password.email') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-[#1a1c1a]">Email Address</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#727973] text-lg pointer-events-none">mail</span>
                        <input name="email" type="email" required placeholder="counsel@chensterling.com" class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-[#f4f3f1] border border-[#c1c8c1] text-xs text-[#1a1c1a] outline-none focus:bg-white focus:border-[#1a3c2a]"/>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] shadow-sm transition-all">
                    Send Encrypted Reset Dispatch
                </button>
            </form>

            <div class="pt-3 border-t border-[#efeeeb] text-center">
                <a href="{{ route('login') }}" class="text-xs text-[#1a3c2a] hover:underline flex items-center justify-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Return to Chambers Sign In</span>
                </a>
            </div>
        </div>

    </div>

</body>
</html>
