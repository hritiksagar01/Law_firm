<x-app-layout>
    <x-slot name="title">{{ $matter->case_number }} — {{ $matter->title }}</x-slot>

    <!-- Dossier Breadcrumb & Top Bar -->
    <div class="flex flex-col gap-4 pb-6 border-b border-[#e9e8e5] mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#727973] hover:text-[#1a3c2a]">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Matters Directory</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs px-2.5 py-1 rounded bg-[#c5ecd2]/50 text-[#002112] font-semibold border border-[#a9cfb6]">
                    {{ $matter->stage }}
                </span>
                <span class="font-mono text-xs px-2.5 py-1 rounded bg-[#efeeeb] text-[#424843] border border-[#c1c8c1]">
                    {{ $matter->case_number }}
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-3xl font-serif font-bold text-[#1a1c1a] tracking-tight">{{ $matter->title }}</h1>
                <p class="text-sm text-[#727973] mt-0.5">
                    {{ $matter->court_name }} <span class="mx-1">·</span> Presiding: {{ $matter->judge_name }} <span class="mx-1">·</span> Opened {{ $matter->opened_at?->format('M d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="document.getElementById('quickTimeModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-lg bg-[#1a3c2a] text-white text-xs font-medium hover:bg-[#022616] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-base text-[#fed977]">timer</span>
                    <span>Log Time on Matter</span>
                </button>
                <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-lg bg-white border border-[#c1c8c1] text-[#1a1c1a] text-xs font-medium hover:bg-[#efeeeb] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[#727973] text-base">upload_file</span>
                    <span>Upload Filing</span>
                </a>
            </div>
        </div>

        <!-- Dossier Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-[#efeeeb] pt-2 -mb-6">
            <button class="px-4 py-2 text-xs font-semibold border-b-2 border-[#1a3c2a] text-[#1a3c2a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">dashboard</span>
                <span>Case Overview</span>
            </button>
            <button class="px-4 py-2 text-xs font-medium text-[#727973] hover:text-[#1a1c1a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">description</span>
                <span>Filings &amp; Evidence ({{ $matter->documents->count() }})</span>
            </button>
            <button class="px-4 py-2 text-xs font-medium text-[#727973] hover:text-[#1a1c1a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">timer</span>
                <span>Time &amp; Ledger (${{ number_format($matter->totalBilledAmount(), 2) }})</span>
            </button>
            <button class="px-4 py-2 text-xs font-medium text-[#727973] hover:text-[#1a1c1a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">calendar_month</span>
                <span>Docket &amp; Hearings ({{ $matter->events->count() }})</span>
            </button>
            <button class="px-4 py-2 text-xs font-medium text-[#727973] hover:text-[#1a1c1a] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">forum</span>
                <span>Privileged Messages ({{ $matter->messages->count() }})</span>
            </button>
        </div>
    </div>

    <!-- Dossier Content Columns -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4">
        <!-- Left 2-Columns: Milestones, Filings & Time -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Procedural Stage Tracker -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-3">Litigation Procedural Stages</h3>
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2 rounded bg-[#efeeeb] text-[#727973]">
                        <span class="font-mono text-[10px] block">01</span> Intake
                    </div>
                    <div class="p-2 rounded bg-[#efeeeb] text-[#727973]">
                        <span class="font-mono text-[10px] block">02</span> Pleadings
                    </div>
                    <div class="p-2 rounded bg-[#1a3c2a] text-white font-semibold shadow-sm ring-2 ring-[#c5ecd2]">
                        <span class="font-mono text-[10px] block text-[#fed977]">03 ACTIVE</span> Discovery
                    </div>
                    <div class="p-2 rounded bg-[#f4f3f1] text-[#727973]">
                        <span class="font-mono text-[10px] block">04</span> Pre-Trial
                    </div>
                    <div class="p-2 rounded bg-[#f4f3f1] text-[#727973]">
                        <span class="font-mono text-[10px] block">05</span> Trial
                    </div>
                </div>
            </div>

            <!-- Documents & Evidence in Matter -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a] text-xl">folder</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Matter Evidence &amp; Filings</h3>
                    </div>
                    <a href="{{ route('documents.index') }}" class="text-xs text-[#1a3c2a] hover:underline font-medium">Vault View</a>
                </div>

                <div class="divide-y divide-[#efeeeb]">
                    @forelse($matter->documents as $doc)
                    <div class="p-4 flex items-center justify-between hover:bg-[#faf9f6] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-red-50 text-red-700 flex items-center justify-center font-mono text-[10px] font-bold shrink-0">
                                PDF
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#1a1c1a] hover:underline cursor-pointer">{{ $doc->title }}</span>
                                <span class="text-[11px] text-[#727973] font-mono mt-0.5">
                                    {{ $doc->filename }} <span class="mx-1">·</span> {{ $doc->formattedSize() }} <span class="mx-1">·</span> v{{ $doc->version }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#fed977]/60 text-[#785d00] font-semibold">{{ $doc->privilege }}</span>
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#727973] hover:text-[#1a3c2a] rounded inline-flex" title="Download Filing">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#727973]">No documents filed in this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Matter Time Ledger -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#efeeeb] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#755b00] text-xl">payments</span>
                        <h3 class="text-sm font-semibold text-[#1a1c1a]">Matter Time &amp; Ledger Entries</h3>
                    </div>
                    <span class="font-mono text-xs text-[#1a1c1a] font-bold">Total: ${{ number_format($matter->totalBilledAmount(), 2) }}</span>
                </div>

                <div class="divide-y divide-[#efeeeb]">
                    @forelse($matter->timeEntries as $time)
                    <div class="p-4 flex items-start justify-between gap-4 hover:bg-[#faf9f6]">
                        <div class="flex items-start gap-3">
                            <span class="font-mono text-xs font-bold text-[#1a3c2a] w-12">{{ $time->hours }}h</span>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-[#efeeeb] text-[#1a3c2a] font-semibold">{{ $time->activity_code }}</span>
                                    <span class="text-xs font-semibold text-[#1a1c1a]">{{ $time->activity_name }}</span>
                                    <span class="text-xs text-[#727973]">· {{ $time->user->name }}</span>
                                </div>
                                <p class="text-xs text-[#424843] mt-1">{{ $time->narrative }}</p>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-semibold text-[#1a1c1a] shrink-0">${{ number_format($time->total_amount, 2) }}</span>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#727973]">No billable hours recorded for this matter.</div>
                    @endforelse
                </div>
            </div>

            <!-- Privileged Attorney-Client Communications (Stitch: secure_communications_client_messages) -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#efeeeb] bg-[#fdfdfc] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a3c2a] text-xl">lock</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1a1c1a]">Privileged Matter Dispatch</h3>
                            <span class="text-[10px] font-mono text-[#785d00] bg-[#fed977]/50 px-1.5 py-0.5 rounded font-bold uppercase">ABA Model Rule 1.6 Protected</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-[#727973]">{{ $matter->messages->count() }} Dispatches</span>
                </div>

                <!-- Messages Stream -->
                <div class="p-4 flex flex-col gap-4 max-h-96 overflow-y-auto bg-[#faf9f6]">
                    @forelse($matter->messages as $msg)
                    <div class="flex items-start gap-3 {{ $msg->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        <img alt="{{ $msg->sender->name }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#c1c8c1] shrink-0" src="{{ $msg->sender->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80' }}"/>
                        <div class="flex flex-col max-w-lg {{ $msg->sender_id === auth()->id() ? 'items-end' : '' }}">
                            <div class="flex items-center gap-2 mb-1 text-[11px]">
                                <span class="font-semibold text-[#1a1c1a]">{{ $msg->sender->name }}</span>
                                <span class="text-[#727973] font-mono">{{ $msg->created_at->format('M d, g:i A') }}</span>
                            </div>
                            <div class="p-3 rounded-xl text-xs leading-relaxed {{ $msg->sender_id === auth()->id() ? 'bg-[#1a3c2a] text-white rounded-tr-none' : 'bg-white border border-[#e9e8e5] text-[#1a1c1a] rounded-tl-none shadow-xs' }}">
                                {{ $msg->body }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#727973]">No encrypted dispatches sent under this matter yet.</div>
                    @endforelse
                </div>

                <!-- Message Composer -->
                <form action="{{ route('messages.store') }}" method="POST" class="p-3 bg-white border-t border-[#efeeeb] flex gap-2">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                    <input name="body" required placeholder="Type privileged counsel communication or discovery inquiry..." class="flex-1 px-3 py-2 text-xs rounded-lg border border-[#c1c8c1] outline-none focus:border-[#1a3c2a]"/>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#1a3c2a] text-white text-xs font-semibold hover:bg-[#022616] flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-sm">send</span>
                        <span>Send</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Right 1-Column: Judicial Information & Team -->
        <div class="flex flex-col gap-6">

            <!-- Forum & Judge Card -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-3">Judicial Assignment</h3>
                <div class="flex flex-col gap-3 text-xs">
                    <div>
                        <span class="text-[#727973] block text-[11px]">Court Forum</span>
                        <span class="font-medium text-[#1a1c1a]">{{ $matter->court_name ?? 'Forum Not Assigned' }}</span>
                    </div>
                    <div>
                        <span class="text-[#727973] block text-[11px]">Presiding Judge</span>
                        <span class="font-medium text-[#1a1c1a]">{{ $matter->judge_name ?? 'Magistrate Pending' }}</span>
                    </div>
                    <div>
                        <span class="text-[#727973] block text-[11px]">Billing Structure</span>
                        <span class="font-mono text-[#1a3c2a] font-medium uppercase">{{ $matter->billing_type }} (Budget: ${{ number_format($matter->budget, 0) }})</span>
                    </div>
                </div>
            </div>

            <!-- Client & Trust Card -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-3">Client Information</h3>
                <div class="flex flex-col gap-2.5 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-[#1a3c2a]">business</span>
                        <span class="font-semibold text-sm text-[#1a1c1a]">{{ $matter->client->name }}</span>
                    </div>
                    <div class="text-[#424843]">
                        <span class="text-[#727973] block text-[11px]">Primary Contact</span>
                        <span>{{ $matter->client->contact_person }}</span>
                    </div>
                    <div class="text-[#424843]">
                        <span class="text-[#727973] block text-[11px]">Email &amp; Direct Phone</span>
                        <span>{{ $matter->client->email }}</span>
                    </div>
                    <div class="pt-3 border-t border-[#efeeeb] flex items-center justify-between">
                        <span class="text-xs text-[#727973]">Retainer in Trust</span>
                        <span class="font-mono text-sm font-bold text-emerald-800">${{ number_format($matter->client->trust_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Assigned Legal Team -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#727973] mb-3">Litigation Team</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img alt="{{ $matter->leadAttorney?->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#c1c8c1]" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#1a1c1a]">{{ $matter->leadAttorney?->name }}</span>
                            <span class="text-[10px] text-[#727973]">Lead Trial Counsel · ${{ $matter->leadAttorney?->hourly_rate }}/hr</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
