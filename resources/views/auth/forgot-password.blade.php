<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Recover Vault Password — {{ config('legal.app_name', 'Vennamraj Associates') }}</title>

    <!-- Industry Standard Readable Typography: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f2ed] text-[#222222] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4 relative">

    <div class="w-full max-w-md flex flex-col items-center my-8">
        
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#9F8349] flex items-center justify-center text-white shadow-md mb-3">
                <span class="material-symbols-outlined text-2xl text-[#B88B56]">lock_reset</span>
            </div>
            <h1 class="text-2xl font-serif font-bold text-[#222222] tracking-tight">Recover Chambers Access</h1>
            <p class="text-xs text-[#766A5E] mt-1">
                Enter your verified firm domain or client email to receive an encrypted reset token.
            </p>
        </div>

        @if(session('status'))
        <div class="w-full mb-4 p-3.5 rounded-xl bg-[#F8F4EE] border border-[#E8DAC8] text-xs text-[#856C36] flex flex-col gap-2">
            <div class="flex items-center gap-2 font-medium">
                <span class="material-symbols-outlined text-base text-[#9F8349]">mark_email_read</span>
                <span>{{ session('status') }}</span>
            </div>
            @if(session('reset_link'))
            <div class="mt-1 p-2.5 bg-white rounded-lg border border-[#EAE4DC] flex items-center justify-between gap-2">
                <span class="text-[11px] text-[#554D45] truncate">Direct Password Reset:</span>
                <a href="{{ session('reset_link') }}" class="px-3 py-1 bg-[#9F8349] text-white rounded-md text-[11px] font-semibold hover:bg-[#856C36] shrink-0">
                    Open Reset Form &rarr;
                </a>
            </div>
            @endif
        </div>
        @endif

        @if($errors->any())
        <div class="w-full mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex flex-col gap-1">
            @foreach($errors->all() as $error)
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm shrink-0">error</span>
                <span>{{ $error }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="w-full bg-white rounded-xl shadow-xl border border-[#EFECE6] p-6 flex flex-col gap-4">
            <form action="{{ route('password.email') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-[#222222]">Email Address</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-[#766A5E] text-lg pointer-events-none">mail</span>
                        <input name="email" type="email" required placeholder="counsel@chensterling.com" class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] outline-none focus:bg-white focus:border-[#9F8349]"/>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                    Send Encrypted Reset Dispatch
                </button>
            </form>

            <div class="pt-3 border-t border-[#F4EFEA] text-center">
                <a href="{{ route('login') }}" class="text-xs text-[#9F8349] hover:underline flex items-center justify-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Return to Chambers Sign In</span>
                </a>
            </div>
        </div>

    </div>

</body>
</html>
