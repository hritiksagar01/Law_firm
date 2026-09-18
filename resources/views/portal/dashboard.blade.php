@extends('portal.layout')

@section('title', 'Litigation Dossier & Advisory')

@section('content')
<div class="flex flex-col w-full">
    
    <!-- Hero Client Dossier Header -->
    <section class="relative w-full rounded-xl bg-surface-card border border-border-hairline shadow-sm p-space-lg lg:p-space-xl overflow-hidden mb-space-xl">
        <div class="absolute -right-20 -top-24 w-96 h-96 rounded-full bg-tertiary-fixed/30 blur-3xl pointer-events-none"></div>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-space-lg relative z-10">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded bg-tertiary-fixed/40 text-pine-primary font-label-sm text-label-sm font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-pine-primary animate-pulse"></span>
                        Retainer Active • FY 2025-26
                    </span>
                    <span class="text-text-muted font-caption text-caption uppercase tracking-wider">Ref: SLC-APX-8820-ENT</span>
                </div>
                <h1 class="font-headline-xl text-headline-xl text-primary font-serif">
                    Welcome, {{ $client->name ?? auth()->user()->name }}
                </h1>
                <p class="font-body-md text-body-md text-text-secondary max-w-3xl">
                    Privileged dossier dashboard for litigation operations, advisory notes, and scheduled appearances before the High Court and Appellate Tribunals.
                </p>
            </div>

            <!-- Supervising Counsel Pill -->
            <div class="flex items-center gap-space-md bg-surface-subtle p-space-md rounded-lg self-start lg:self-auto shadow-sm border border-border-hairline">
                <div class="w-14 h-14 rounded-full bg-pine-primary text-[#c2ecd7] flex items-center justify-center font-serif text-xl font-bold shrink-0 shadow-sm">
                    AS
                </div>
                <div class="flex flex-col">
                    <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Supervising Counsel</span>
                    <span class="font-title-md text-title-md text-text-primary">Adv. A. Sharma</span>
                    <span class="font-body-sm text-body-sm text-pine-primary">Senior Managing Partner</span>
                </div>
                <button class="ml-2 w-10 h-10 rounded bg-pine-primary hover:bg-pine-hover text-on-primary flex items-center justify-center transition-colors shadow-sm cursor-pointer" onclick="document.getElementById('counsel-thread').scrollIntoView({behavior: 'smooth'})" title="Message Chambers" type="button">
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                </button>
            </div>
        </div>

        <!-- 4 Metrics Ledger Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md mt-space-xl pt-space-lg bg-surface-subtle/60 rounded-lg p-space-md border border-border-hairline">
            <div class="flex flex-col">
                <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Active Cases</span>
                <div class="flex items-baseline gap-space-xs mt-0.5">
                    <span class="font-headline-md text-headline-md text-primary font-serif">{{ str_pad($matters->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-label-sm text-label-sm text-pine-primary font-medium">Matters listed</span>
                </div>
            </div>
            <div class="flex flex-col">
                <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Vault Documents</span>
                <div class="flex items-baseline gap-space-xs mt-0.5">
                    <span class="font-headline-md text-headline-md text-primary font-serif">{{ str_pad($documentsCount ?? $documentRequests->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-label-sm text-label-sm text-text-secondary">Secured &amp; verified</span>
                </div>
            </div>
            <div class="flex flex-col">
                <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Next Retainer Cycle</span>
                <div class="flex items-baseline gap-space-xs mt-0.5">
                    <span class="font-headline-md text-headline-md text-primary font-serif">₹4,50,000</span>
                    <span class="font-label-sm text-label-sm text-text-secondary">Due Next Month</span>
                </div>
            </div>
            <div class="flex flex-col">
                <span class="font-caption text-caption text-text-muted uppercase tracking-wider">Filings Compliance</span>
                <div class="flex items-baseline gap-space-xs mt-0.5">
                    <span class="font-headline-md text-headline-md text-pine-primary font-serif">100%</span>
                    <span class="font-label-sm text-label-sm text-text-secondary">Zero defaults</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Two Column Grid for Matters & Counsel/Hearings -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl">
        
        <!-- Main Left Column (8 cols) -->
        <div class="lg:col-span-8 flex flex-col gap-space-xl">
            
            <!-- Active Matters Section -->
            <section class="flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-pine-primary"></span>
                        <h2 class="font-headline-md text-headline-md text-primary font-serif">Active Matters &amp; Live Hearing Tracker</h2>
                    </div>
                    <span class="font-caption text-caption text-text-muted">Real-time Chambers Registry Feed</span>
                </div>

                @forelse($matters as $matter)
                <!-- Matter Card -->
                <div class="bg-surface-card border border-border-hairline rounded-xl p-space-lg shadow-sm flex flex-col gap-space-lg">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-space-sm">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-space-xs">
                                <span class="px-space-sm py-0.5 bg-tertiary-fixed text-primary font-label-sm text-label-sm rounded font-medium">
                                    {{ $matter->practice_area ?? 'Commercial Litigation' }}
                                </span>
                                <span class="text-text-muted font-caption text-caption">CNR: {{ $matter->case_number }}</span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-text-primary mt-1">
                                {{ $matter->title }}
                            </h3>
                            <span class="font-body-sm text-body-sm text-text-secondary">
                                {{ $matter->court_name ?? 'High Court of Delhi' }} • {{ $matter->judge_name ?? "Hon'ble Presiding Bench" }}
                            </span>
                        </div>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="font-caption text-caption uppercase text-text-muted tracking-wider">Next Date of Hearing</span>
                            <span class="font-title-md text-title-md text-pine-primary font-medium">Scheduled Term Q2</span>
                            <span class="font-caption text-caption text-error font-medium">Final Hearing • Item #22</span>
                        </div>
                    </div>

                    <!-- Procedural Trajectory -->
                    <div class="bg-surface-subtle border border-border-hairline p-space-md rounded-lg">
                        <div class="flex items-center justify-between mb-space-sm">
                            <span class="font-caption text-caption uppercase tracking-wider text-text-muted">Procedural Trajectory</span>
                            <span class="font-caption text-caption text-pine-primary font-medium">Current Stage: {{ $matter->stage }}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 relative">
                            <div class="flex flex-col items-center text-center gap-1">
                                <div class="w-7 h-7 rounded-full bg-pine-primary text-on-primary flex items-center justify-center font-caption text-caption shadow-sm">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                </div>
                                <span class="font-caption text-caption text-text-primary font-medium">Petition Filed</span>
                                <span class="font-caption text-caption text-text-muted scale-90">Completed</span>
                            </div>
                            <div class="flex flex-col items-center text-center gap-1">
                                <div class="w-7 h-7 rounded-full bg-pine-primary text-on-primary flex items-center justify-center font-caption text-caption shadow-sm">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                </div>
                                <span class="font-caption text-caption text-text-primary font-medium">Notice Issued</span>
                                <span class="font-caption text-caption text-text-muted scale-90">Completed</span>
                            </div>
                            <div class="flex flex-col items-center text-center gap-1">
                                <div class="w-7 h-7 rounded-full bg-pine-primary text-on-primary flex items-center justify-center font-caption text-caption shadow-sm">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                </div>
                                <span class="font-caption text-caption text-text-primary font-medium">Counter Filed</span>
                                <span class="font-caption text-caption text-text-muted scale-90">Completed</span>
                            </div>
                            <div class="flex flex-col items-center text-center gap-1">
                                <div class="w-7 h-7 rounded-full bg-pine-primary text-on-primary ring-4 ring-tertiary-fixed/60 flex items-center justify-center font-caption text-caption font-bold shadow-sm">
                                    4
                                </div>
                                <span class="font-caption text-caption text-pine-primary font-semibold">{{ $matter->stage }}</span>
                                <span class="font-caption text-caption text-pine-primary scale-90 font-medium">Active Stage</span>
                            </div>
                            <div class="flex flex-col items-center text-center gap-1 opacity-50">
                                <div class="w-7 h-7 rounded-full bg-surface-card border border-border-hairline text-text-muted flex items-center justify-center font-caption text-caption">
                                    5
                                </div>
                                <span class="font-caption text-caption text-text-secondary">Disposition</span>
                                <span class="font-caption text-caption text-text-muted scale-90">Awaited</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-space-sm pt-space-xs">
                        <div class="flex items-center gap-space-sm text-text-secondary font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[18px] text-pine-primary">record_voice_over</span>
                            <span>Lead Arguing Counsel: <strong>{{ $matter->leadAttorney?->name ?? 'Adv. A. Sharma' }}</strong></span>
                        </div>
                        <a href="{{ route('portal.matters.show', $matter->id) }}" class="px-space-md py-2 rounded bg-surface-subtle hover:bg-surface-container-high text-primary font-title-sm text-title-sm transition-colors flex items-center gap-1 border border-border-hairline shadow-sm">
                            <span>View Certified Order Sheets</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-8 bg-surface-card rounded-xl border border-border-hairline text-center text-xs text-text-muted">
                    No active litigation dockets currently linked to your enterprise client account.
                </div>
                @endforelse
            </section>

            <!-- Case Opinions & Legal Memos Section -->
            <section class="flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-pine-primary"></span>
                        <h2 class="font-headline-md text-headline-md text-primary font-serif">Recent Case Opinions &amp; Legal Memos</h2>
                    </div>
                    <span class="font-caption text-caption text-text-muted">Digital Counsel Signature Authenticated</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <div class="bg-surface-card border border-border-hairline p-space-lg rounded-xl shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-space-sm">
                                <span class="px-space-sm py-0.5 bg-surface-subtle text-text-secondary font-label-sm text-label-sm rounded font-medium border border-border-hairline">Memo #OP-2025-089</span>
                                <span class="font-caption text-caption text-text-muted">Privileged Brief</span>
                            </div>
                            <h3 class="font-title-md text-title-md text-text-primary mb-space-xs">Precedent Analysis: Carriage of Goods &amp; Statutory Demurrage Waivers</h3>
                            <p class="font-body-sm text-body-sm text-text-secondary mb-space-md">
                                Detailed counsel advisory on section 43 with case law synthesis of the recent division bench appellate ruling.
                            </p>
                        </div>
                        <div class="flex items-center gap-space-sm pt-space-md border-t border-border-hairline">
                            <a href="{{ route('portal.documents.index') }}" class="flex-1 py-2 px-space-sm rounded bg-pine-primary hover:bg-pine-hover text-on-primary font-title-sm text-title-sm text-center flex items-center justify-center gap-1 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                                <span>PDF Memorandum (4.2 MB)</span>
                            </a>
                            <button class="p-2 rounded bg-surface-subtle hover:bg-surface-container-high text-primary transition-colors border border-border-hairline shadow-sm" title="View Citations" type="button">
                                <span class="material-symbols-outlined text-[18px]">format_quote</span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-surface-card border border-border-hairline p-space-lg rounded-xl shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-space-sm">
                                <span class="px-space-sm py-0.5 bg-surface-subtle text-text-secondary font-label-sm text-label-sm rounded font-medium border border-border-hairline">Memo #OP-2025-074</span>
                                <span class="font-caption text-caption text-text-muted">Advisory Note</span>
                            </div>
                            <h3 class="font-title-md text-title-md text-text-primary mb-space-xs">Risk Matrix: Proposed Joint Venture Agreement for Multi-Modal Hub</h3>
                            <p class="font-body-sm text-body-sm text-text-secondary mb-space-md">
                                Privileged review of dispute resolution clauses, seat designation risks, and indemnification caps with partner commentary.
                            </p>
                        </div>
                        <div class="flex items-center gap-space-sm pt-space-md border-t border-border-hairline">
                            <a href="{{ route('portal.documents.index') }}" class="flex-1 py-2 px-space-sm rounded bg-pine-primary hover:bg-pine-hover text-on-primary font-title-sm text-title-sm text-center flex items-center justify-center gap-1 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                                <span>PDF Memorandum (2.8 MB)</span>
                            </a>
                            <button class="p-2 rounded bg-surface-subtle hover:bg-surface-container-high text-primary transition-colors border border-border-hairline shadow-sm" title="View Citations" type="button">
                                <span class="material-symbols-outlined text-[18px]">format_quote</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- Right Column: Court Dates, Counsel Channel, Retainer Ledger (4 cols) -->
        <div class="lg:col-span-4 flex flex-col gap-space-xl">
            
            <!-- Upcoming Court Dates Section -->
            <section class="bg-surface-card border border-border-hairline rounded-xl p-space-lg shadow-sm flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-xs">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-pine-primary text-[20px]">event_upcoming</span>
                        <h2 class="font-headline-sm text-headline-sm text-primary font-serif">Upcoming Court Dates</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-tertiary-fixed text-primary font-caption text-caption font-semibold uppercase">LISTED</span>
                </div>

                <div class="flex flex-col gap-space-md">
                    @forelse($events->take(2) as $event)
                    <div class="bg-surface-subtle border border-border-hairline p-space-md rounded-lg flex flex-col gap-space-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-label-sm text-label-sm text-error font-semibold">{{ $event->start_time->format('D, d M · g:i A') }}</span>
                            <span class="font-caption text-caption text-text-muted">Court #14</span>
                        </div>
                        <span class="font-title-sm text-title-sm text-text-primary">{{ $event->title }}</span>
                        <p class="font-body-sm text-body-sm text-text-secondary">
                            <strong>Expected:</strong> Senior Counsel will lead oral arguments. Client in virtual attendance.
                        </p>
                        <div class="flex items-center justify-between mt-space-xs pt-space-xs">
                            <span class="font-caption text-caption text-pine-primary font-medium">Briefing Counsel: Adv. A. Sharma</span>
                            <span class="inline-flex items-center gap-1 font-caption text-caption text-text-secondary">
                                <span class="w-1.5 h-1.5 rounded-full bg-pine-primary"></span> VC Link Ready
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="bg-surface-subtle border border-border-hairline p-space-md rounded-lg text-center text-xs text-text-muted">
                        No upcoming trial hearings scheduled for this week.
                    </div>
                    @endforelse
                </div>

                <!-- High Court Registry Banner -->
                <div class="rounded-lg overflow-hidden relative mt-space-xs border border-border-hairline bg-surface-subtle p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-pine-primary text-2xl">account_balance</span>
                        <div class="flex flex-col">
                            <span class="font-title-sm text-title-sm text-text-primary">Judicial Registry Status</span>
                            <span class="font-caption text-caption text-text-muted">Electronic cause lists synchronized</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">Active</span>
                </div>
            </section>

            <!-- Counsel Channel Box -->
            <section class="bg-surface-card border border-border-hairline rounded-xl p-space-lg shadow-sm flex flex-col gap-space-md" id="counsel-thread">
                <div class="flex items-center justify-between pb-space-xs">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-pine-primary text-[20px]">forum</span>
                        <h2 class="font-headline-sm text-headline-sm text-primary font-serif">Counsel Channel</h2>
                    </div>
                    <span class="font-caption text-caption text-pine-primary font-medium">Privileged • Encrypted</span>
                </div>

                <div class="flex flex-col gap-space-sm max-h-72 overflow-y-auto pr-1" id="chat-stream">
                    <div class="flex flex-col bg-surface-subtle border border-border-hairline p-space-sm rounded-lg self-start max-w-[90%]">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="font-label-sm text-label-sm text-pine-primary font-medium">Adv. Priya Mathur</span>
                            <span class="font-caption text-caption text-text-muted">10:14 AM</span>
                        </div>
                        <p class="font-body-sm text-body-sm text-text-primary">
                            We have compiled the supplemental compilation for tomorrow’s High Court list. Please verify the annexed schedule of payments.
                        </p>
                    </div>

                    <div class="flex flex-col bg-primary-container text-on-primary p-space-sm rounded-lg self-end max-w-[90%]">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-label-sm text-label-sm text-on-primary font-medium">{{ auth()->user()->name }}</span>
                            <span class="font-caption text-caption text-on-primary-container">11:02 AM</span>
                        </div>
                        <p class="font-body-sm text-body-sm text-on-primary">
                            Accounts has cross-verified the figures. Signed authorization token uploaded to case folder.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-space-xs mt-space-xs">
                    <div class="relative">
                        <textarea class="w-full bg-surface-subtle border border-border-hairline rounded-lg p-space-sm font-body-sm text-body-sm text-text-primary placeholder:text-text-muted resize-none focus:outline-none focus:bg-surface-card focus:border-pine-primary focus:shadow-sm transition-all" id="query-input" placeholder="Send privileged message to Chambers associates..." rows="2"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="text-text-muted hover:text-text-primary flex items-center gap-1 font-caption text-caption cursor-pointer" type="button">
                            <span class="material-symbols-outlined text-[16px]">attach_file</span>
                            <span>Attach Addendum</span>
                        </button>
                        <button class="px-space-md py-1.5 rounded bg-pine-primary hover:bg-pine-hover text-on-primary font-title-sm text-title-sm transition-colors shadow-sm flex items-center gap-1 cursor-pointer" id="send-btn" type="button">
                            <span>Transmit</span>
                            <span class="material-symbols-outlined text-[16px]">send</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Retainer Ledger Pill -->
            <section class="bg-surface-card border border-border-hairline p-space-md rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-space-sm">
                    <div class="w-10 h-10 rounded-full bg-pine-primary/10 flex items-center justify-center text-pine-primary">
                        <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm text-text-primary font-semibold">Enterprise Retainer Ledger</span>
                        <span class="font-caption text-caption text-text-muted">Q2 Statement • Approved</span>
                    </div>
                </div>
                <a href="{{ route('portal.invoices.index') }}" class="font-label-sm text-label-sm text-pine-primary hover:text-pine-hover font-semibold underline">
                    Download Invoice
                </a>
            </section>

        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sendBtn = document.getElementById('send-btn');
        const input = document.getElementById('query-input');
        const chatStream = document.getElementById('chat-stream');

        if (sendBtn && input && chatStream) {
            sendBtn.addEventListener('click', function() {
                const text = input.value.trim();
                if (!text) return;

                const now = new Date();
                const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                const msgDiv = document.createElement('div');
                msgDiv.className = 'flex flex-col bg-primary-container text-on-primary p-space-sm rounded-lg self-end max-w-[90%] transition-all';
                msgDiv.innerHTML = `
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-label-sm text-label-sm text-on-primary font-medium">{{ auth()->user()->name }}</span>
                        <span class="font-caption text-caption text-on-primary-container">${timeStr}</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-primary">${text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                `;

                chatStream.appendChild(msgDiv);
                input.value = '';
                chatStream.scrollTop = chatStream.scrollHeight;
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendBtn.click();
                }
            });
        }
    });
</script>
@endsection
