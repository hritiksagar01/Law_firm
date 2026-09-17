@extends('layouts.app')

@section('title', 'Book Appointment / Hearing — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <div class="mb-8">
        <a href="{{ route('appointments.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Appointments</span>
        </a>
        <h1 class="text-3xl font-serif font-bold text-[#222222]">Book Appointment / Court Appearance</h1>
        <p class="text-sm text-[#766A5E] mt-1">Schedule a court appearance, client consultation, or case strategy conference</p>
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

    <form method="POST" action="{{ route('appointments.store') }}" class="space-y-6">
        @csrf

        <!-- Appointment Particulars -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">event</span>
                <span>Session Particulars</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Session Title / Subject *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Preliminary Arguments before Bench 4 / Initial Case Briefing"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Session Type *</label>
                    <select name="type" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="client_consultation" {{ old('type') === 'client_consultation' ? 'selected' : '' }}>Client Consultation (In-Chambers)</option>
                        <option value="court_appearance" {{ old('type') === 'court_appearance' ? 'selected' : '' }}>Court Appearance / Bench Hearing</option>
                        <option value="case_conference" {{ old('type') === 'case_conference' ? 'selected' : '' }}>Internal Case Strategy Conference</option>
                        <option value="mediation" {{ old('type') === 'mediation' ? 'selected' : '' }}>Mediation / Conciliation Hearing</option>
                        <option value="briefing" {{ old('type') === 'briefing' ? 'selected' : '' }}>Senior Counsel Briefing</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Assigned Advocate / Host *</label>
                    <select name="user_id" required class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        @foreach($attorneys as $atty)
                        <option value="{{ $atty->id }}" {{ (old('user_id') == $atty->id || auth()->id() == $atty->id) ? 'selected' : '' }}>
                            {{ $atty->name }} ({{ $atty->title ?? $atty->role }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Date &amp; Time *</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\TH:00')) }}" required
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Allocated Duration (Minutes)</label>
                    <select name="duration_minutes" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="15" {{ old('duration_minutes') == 15 ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30" {{ old('duration_minutes') == 30 ? 'selected' : '' }}>30 Minutes</option>
                        <option value="45" {{ old('duration_minutes', 45) == 45 ? 'selected' : '' }}>45 Minutes</option>
                        <option value="60" {{ old('duration_minutes') == 60 ? 'selected' : '' }}>1 Hour</option>
                        <option value="90" {{ old('duration_minutes') == 90 ? 'selected' : '' }}>1.5 Hours</option>
                        <option value="120" {{ old('duration_minutes') == 120 ? 'selected' : '' }}>2 Hours</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Venue / Location / Courtroom / Link</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Court Hall 4, High Court of Telangana / Conference Room A / Google Meet Link"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>
            </div>
        </div>

        <!-- Case & Client Association -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">link</span>
                <span>Case &amp; Client Association</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Link to Case / Matter</label>
                    <select name="matter_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="">-- No Specific Matter --</option>
                        @foreach($matters as $m)
                        <option value="{{ $m->id }}" {{ (old('matter_id') == $m->id || $selectedMatterId == $m->id) ? 'selected' : '' }}>
                            {{ $m->case_number }} — {{ $m->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Client Attendee</label>
                    <select name="client_id" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="">-- No Specific Client --</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ (old('client_id') == $c->id || $selectedClientId == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Agenda &amp; Notes</label>
                    <textarea name="notes" rows="3" placeholder="Key points to cover, required legal documents, witness prep notes..."
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('appointments.index') }}" class="px-5 py-2.5 rounded-lg border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">check</span>
                <span>Confirm Booking</span>
            </button>
        </div>
    </form>

</div>
@endsection
