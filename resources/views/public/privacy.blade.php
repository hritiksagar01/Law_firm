@extends('public.layout')

@section('title', $data['privacy_headline'] ?? 'Advocate Privilege & Privacy Policy')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[12px] text-[#6b7280] mb-2">
            <a href="{{ route('login') }}" class="hover:text-[#093225]">Chambers Cloud</a>
            <span>/</span>
            <span class="text-[#111827] font-medium">Privacy</span>
        </div>
        <div class="flex items-center gap-2 mb-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]">
                <span class="material-symbols-outlined text-[13px]">lock</span>
                <span>Indian Evidence Act §§ 126–129 Guaranteed</span>
            </span>
        </div>
        <h1 class="text-[28px] sm:text-[34px] font-semibold text-[#111827] tracking-tight leading-tight">
            {{ $data['privacy_headline'] ?? 'Client Privilege & Legal Data Protection Policy' }}
        </h1>
        <p class="text-[14px] text-[#6b7280] mt-2 leading-relaxed">
            Statutory confidentiality covenants, cryptographic data isolation, and Bar Council of India standards of client protection.
        </p>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6 sm:p-8 space-y-6">
        <div class="text-[13.5px] text-[#374151] leading-relaxed space-y-6">
            @foreach(explode("\n\n", $data['privacy_content'] ?? '') as $section)
                @if(trim($section) !== '')
                    <div class="p-4 rounded-lg bg-[#f4f2ed] border border-[#e5e7eb]/80">
                        <p class="whitespace-pre-line">{{ trim($section) }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Security & Cipher Highlights -->
        <div class="pt-6 border-t border-[#f3f4f6] flex flex-col sm:flex-row items-center justify-between gap-4 text-[12px] text-[#6b7280]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-[#093225]">enhanced_encryption</span>
                <span>Encrypted at rest with AES-256 and transmitted exclusively via TLS 1.3</span>
            </div>
            <div>
                <a href="{{ route('public.contact') }}" class="text-[#093225] font-medium hover:underline">
                    Inquire with Data Protection Officer ↗
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
