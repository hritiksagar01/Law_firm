@extends('public.layout')

@section('title', $data['terms_headline'] ?? 'Terms of Service')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[12px] text-[#6b7280] mb-2">
            <a href="{{ route('login') }}" class="hover:text-[#093225]">Chambers Cloud</a>
            <span>/</span>
            <span class="text-[#111827] font-medium">Terms</span>
        </div>
        <h1 class="text-[28px] sm:text-[34px] font-semibold text-[#111827] tracking-tight leading-tight">
            {{ $data['terms_headline'] ?? 'Terms of Practice Service & Platform Governance' }}
        </h1>
        <p class="text-[14px] text-[#6b7280] mt-2 leading-relaxed">
            Legal terms governing chamber representation, authentic electronic filings, notice service, and multi-tenant portal usage.
        </p>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6 sm:p-8 space-y-6">
        <div class="text-[13.5px] text-[#374151] leading-relaxed space-y-6">
            @foreach(explode("\n\n", $data['terms_content'] ?? '') as $section)
                @if(trim($section) !== '')
                    <div class="p-4 rounded-lg bg-[#f4f2ed] border border-[#e5e7eb]/80">
                        <p class="whitespace-pre-line">{{ trim($section) }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Acceptance & Sign Off -->
        <div class="pt-6 border-t border-[#f3f4f6] flex flex-col sm:flex-row items-center justify-between gap-4 text-[12px] text-[#6b7280]">
            <div>
                <span>Governed under the Laws of the Republic of India</span>
            </div>
            <div>
                <a href="{{ route('public.contact') }}" class="text-[#093225] font-medium hover:underline">
                    Chambers Legal Counsel ↗
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
