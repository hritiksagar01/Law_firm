@extends('public.layout')

@section('title', $data['about_headline'] ?? 'About Us')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[12px] text-[#6b7280] mb-2">
            <a href="{{ route('login') }}" class="hover:text-[#093225]">Chambers Cloud</a>
            <span>/</span>
            <span class="text-[#111827] font-medium">About</span>
        </div>
        <h1 class="text-[28px] sm:text-[34px] font-semibold text-[#111827] tracking-tight leading-tight">
            {{ $data['about_headline'] ?? 'About the Chambers & Practice Platform' }}
        </h1>
        <p class="text-[14px] text-[#6b7280] mt-2 leading-relaxed">
            Institutional-grade legal workflow and litigation management infrastructure for bar advocates and litigating clients.
        </p>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6 sm:p-8 space-y-6">
        <div class="prose max-w-none text-[14px] text-[#374151] leading-relaxed space-y-4">
            @foreach(explode("\n\n", $data['about_content'] ?? '') as $paragraph)
                @if(trim($paragraph) !== '')
                    <p class="whitespace-pre-line">{{ trim($paragraph) }}</p>
                @endif
            @endforeach
        </div>

        <!-- Trust & Pillars Feature Grid -->
        <div class="pt-6 border-t border-[#f3f4f6] grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg bg-[#f4f2ed] border border-[#e5e7eb]">
                <div class="w-8 h-8 rounded-full bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-2.5">
                    <span class="material-symbols-outlined text-[19px]">gavel</span>
                </div>
                <h3 class="text-[13.5px] font-semibold text-[#111827]">Litigation Dockets</h3>
                <p class="text-[12px] text-[#6b7280] mt-1">Multi-stage case trajectory tracking from initial notice through Supreme Court appeals.</p>
            </div>

            <div class="p-4 rounded-lg bg-[#f4f2ed] border border-[#e5e7eb]">
                <div class="w-8 h-8 rounded-full bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-2.5">
                    <span class="material-symbols-outlined text-[19px]">verified_user</span>
                </div>
                <h3 class="text-[13.5px] font-semibold text-[#111827]">Statutory Privilege</h3>
                <p class="text-[12px] text-[#6b7280] mt-1">Bound by Indian Evidence Act §§ 126–129 and strict multi-tenant encrypted segregation.</p>
            </div>

            <div class="p-4 rounded-lg bg-[#f4f2ed] border border-[#e5e7eb]">
                <div class="w-8 h-8 rounded-full bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-2.5">
                    <span class="material-symbols-outlined text-[19px]">cloud_done</span>
                </div>
                <h3 class="text-[13.5px] font-semibold text-[#111827]">Certified Vaults</h3>
                <p class="text-[12px] text-[#6b7280] mt-1">Cryptographic SHA-256 document hashing with verified legal download certification.</p>
            </div>
        </div>
    </div>

    <!-- Direct Action Card -->
    <div class="p-6 rounded-xl bg-[#093225] text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-[16px] font-semibold">Need to access your case dossier or consultations?</h3>
            <p class="text-[12.5px] text-white/80 mt-0.5">Sign in to your authorized chamber workspace or litigant portal.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('public.contact') }}" class="px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-[13px] font-medium transition-colors">
                Contact Registry
            </a>
            <a href="{{ route('login') }}" class="px-4 py-2 rounded-md bg-white text-[#093225] hover:bg-gray-100 text-[13px] font-semibold transition-colors shadow-xs">
                Sign In
            </a>
        </div>
    </div>
</div>
@endsection
