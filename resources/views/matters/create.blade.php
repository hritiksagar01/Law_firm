@extends('layouts.app')

@section('title', 'Initiate New Case Dossier — ' . (config('legal.app_name', 'Sharma Legal Chambers')))
@section('header_title', 'Initiate Matter')

@section('content')
<div class="max-w-3xl mx-auto pb-12 text-[#1a1a1a]">
    <div class="mb-6">
        <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#646864] hover:text-[#23493a] mb-2 transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Matters Directory</span>
        </a>
        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Initiate New Case Dossier</h1>
        <p class="text-[13px] text-[#646864] mt-1">Enter court parameters, client relationship, judicial forum, and procedural stage</p>
    </div>

    <form action="{{ route('matters.store') }}" method="POST" class="border border-[#e5e3dc] bg-white rounded-md shadow-xs p-6 flex flex-col gap-5">
        @csrf

        <!-- Client & Title -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Client / Corporate Entity *</label>
                <select name="client_id" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    <option value="">Select an existing client...</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Practice Discipline *</label>
                <select name="practice_area" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    @foreach(config('legal.practice_areas', ['Commercial Litigation & Arbitration', 'Corporate & Insolvency (IBC / NCLT)', 'Criminal Defense & Bail Matters']) as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#1a1a1a]">Case Title (Caption) *</label>
            <input name="title" required placeholder="e.g. Malhotra Enterprises v. Apex Commercial Bank" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
        </div>

        <!-- Court Forum & Judge -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Court / Judicial Forum</label>
                <input name="court_name" placeholder="e.g. High Court of Delhi, New Delhi" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Presiding Judge / Bench</label>
                <input name="judge_name" placeholder="e.g. Hon. Justice C. Hari Shankar" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]"/>
            </div>
        </div>

        <!-- Lead Counsel & Procedural Stage -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Lead Advocate *</label>
                <select name="lead_attorney_id" required class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    @foreach($attorneys as $at)
                        <option value="{{ $at->id }}">{{ $at->name }} ({{ $at->title ?? $at->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-[#1a1a1a]">Procedural Stage</label>
                <select name="stage" class="w-full h-10 px-3 rounded-md bg-white border border-[#e5e3dc] text-xs text-[#1a1a1a] focus:outline-none focus:border-[#23493a]">
                    <option value="Filing / Pre-Admission">Filing / Pre-Admission</option>
                    <option value="Notice & Pleadings" selected>Notice &amp; Pleadings</option>
                    <option value="Evidence & Arguments">Evidence &amp; Arguments</option>
                    <option value="Final Hearing & Order">Final Hearing &amp; Order</option>
                    <option value="Execution / Decree">Execution / Decree</option>
                </select>
            </div>
        </div>

        <!-- System Matter Attributes -->
        <input type="hidden" name="billing_type" value="flat_fee"/>
        <input type="hidden" name="budget" value="0"/>

        <div class="pt-4 border-t border-[#f0eee8] flex items-center justify-end gap-3">
            <a href="{{ route('matters.index') }}" class="btn-secondary h-9 px-4 text-xs">Cancel</a>
            <button type="submit" class="btn-primary h-9 px-4 text-xs">
                Create &amp; Open Case Dossier
            </button>
        </div>
    </form>
</div>
@endsection
