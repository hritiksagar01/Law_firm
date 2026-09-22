@extends('public.layout')

@section('title', $data['contact_headline'] ?? 'Contact & Registry')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[12px] text-[#6b7280] mb-2">
            <a href="{{ route('login') }}" class="hover:text-[#093225]">Chambers Cloud</a>
            <span>/</span>
            <span class="text-[#111827] font-medium">Contact</span>
        </div>
        <h1 class="text-[28px] sm:text-[34px] font-semibold text-[#111827] tracking-tight leading-tight">
            {{ $data['contact_headline'] ?? 'Chambers Registry & Grievance Redressal' }}
        </h1>
        <p class="text-[14px] text-[#6b7280] mt-2 leading-relaxed">
            Get in touch with the practice administration, court registry desk, or submit institutional inquiries.
        </p>
    </div>

    <!-- Contact Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card 1: Direct Email -->
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-lg bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[22px]">mail</span>
                </div>
                <h3 class="text-[15px] font-semibold text-[#111827]">Registry &amp; Support Email</h3>
                <p class="text-[12.5px] text-[#6b7280] mt-1 mb-4">For matter filings, document submissions, or account support:</p>
                <div class="font-mono text-[13.5px] text-[#093225] font-medium bg-[#fbf9f5] p-2.5 rounded border border-[#e5e7eb] break-all">
                    {{ $data['contact_email'] ?? 'contact@vennamraj.com' }}
                </div>
            </div>
            <div class="mt-6">
                <a href="mailto:{{ $data['contact_email'] ?? 'contact@vennamraj.com' }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#093225] hover:underline">
                    <span>Send Direct Email</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- Card 2: Telephone & Registry Desk -->
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-lg bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[22px]">call</span>
                </div>
                <h3 class="text-[15px] font-semibold text-[#111827]">Direct Chambers Telephone</h3>
                <p class="text-[12.5px] text-[#6b7280] mt-1 mb-4">Urgent consultation inquiries and hearing date status:</p>
                <div class="font-mono text-[13.5px] text-[#093225] font-medium bg-[#fbf9f5] p-2.5 rounded border border-[#e5e7eb]">
                    {{ $data['contact_phone'] ?? '+91 11 2338 4567' }}
                </div>
            </div>
            <div class="mt-6">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $data['contact_phone'] ?? '+911123384567') }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#093225] hover:underline">
                    <span>Call Registry Line</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- Card 3: Physical Chambers Address -->
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6">
            <div class="w-10 h-10 rounded-lg bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[22px]">location_on</span>
            </div>
            <h3 class="text-[15px] font-semibold text-[#111827]">Chambers &amp; Court Office Address</h3>
            <p class="text-[12.5px] text-[#6b7280] mt-1 mb-3">Official registered physical location:</p>
            <div class="text-[13px] text-[#374151] leading-relaxed whitespace-pre-line bg-[#fbf9f5] p-3.5 rounded border border-[#e5e7eb]">
                {{ $data['contact_address'] ?? "Chamber Block C-14, Lawyers Chambers Complex\nHigh Court of Delhi, Sher Shah Road\nNew Delhi, DL 110003, India" }}
            </div>
        </div>

        <!-- Card 4: Operating Hours -->
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-xs p-6">
            <div class="w-10 h-10 rounded-lg bg-[#093225]/10 text-[#093225] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[22px]">schedule</span>
            </div>
            <h3 class="text-[15px] font-semibold text-[#111827]">Registry Timings &amp; Operating Hours</h3>
            <p class="text-[12.5px] text-[#6b7280] mt-1 mb-3">Court working hours and consultation windows:</p>
            <div class="text-[13px] text-[#374151] leading-relaxed bg-[#fbf9f5] p-3.5 rounded border border-[#e5e7eb]">
                <div class="font-medium text-[#111827]">{{ $data['contact_hours'] ?? 'Monday – Saturday: 09:30 AM – 07:00 PM IST (Court Days)' }}</div>
                <div class="text-[12px] text-[#6b7280] mt-2">Emergency filing contacts remain active for pending High Court and Supreme Court urgent stay listings.</div>
            </div>
        </div>

    </div>
</div>
@endsection
