<x-app-layout>
    <x-slot name="title">{{ $matter->case_number }} — {{ $matter->title }}</x-slot>

    <!-- Dossier Breadcrumb & Top Bar -->
    <div class="flex flex-col gap-4 pb-6 border-b border-[#EFECE6] mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('matters.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349]">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Matters Directory</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs px-2.5 py-1 rounded bg-[#F4ECE1]/50 text-[#222222] font-semibold border border-[#E8DAC8]">
                    {{ $matter->stage }}
                </span>
                <span class="font-mono text-xs px-2.5 py-1 rounded bg-[#F4EFEA] text-[#554D45] border border-[#EAE4DC]">
                    {{ $matter->case_number }}
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col">
                <h1 class="text-3xl font-serif font-bold text-[#222222] tracking-tight">{{ $matter->title }}</h1>
                <p class="text-sm text-[#766A5E] mt-0.5">
                    {{ $matter->court_name }} <span class="mx-1">·</span> Presiding: {{ $matter->judge_name }} <span class="mx-1">·</span> Opened {{ $matter->opened_at?->format('M d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-lg border border-[#EAE4DC] bg-white text-xs font-semibold text-[#222222] hover:bg-[#FAF8F5] shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">draw</span>
                    <span>Draft Opinion</span>
                </a>
                <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-lg border border-[#EAE4DC] bg-white text-xs font-semibold text-[#222222] hover:bg-[#FAF8F5] shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">event</span>
                    <span>Book Hearing</span>
                </a>
                <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-base">upload_file</span>
                    <span>Upload Filing</span>
                </a>
            </div>
        </div>

        <!-- Dossier Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 border-b border-[#F4EFEA] pt-2 -mb-6">
            <button class="px-4 py-2 text-xs font-semibold border-b-2 border-[#9F8349] text-[#9F8349] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">dashboard</span>
                <span>Case Overview</span>
            </button>
            <a href="#opinions-section" class="px-4 py-2 text-xs font-medium text-[#766A5E] hover:text-[#222222] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">draw</span>
                <span>Opinions &amp; Strategy ({{ $matter->opinions->count() }})</span>
            </a>
            <a href="#hearings-section" class="px-4 py-2 text-xs font-medium text-[#766A5E] hover:text-[#222222] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">calendar_month</span>
                <span>Hearings &amp; Docket ({{ $matter->appointments->count() + $matter->events->count() }})</span>
            </a>
            <a href="#filings-section" class="px-4 py-2 text-xs font-medium text-[#766A5E] hover:text-[#222222] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">description</span>
                <span>Filings &amp; Evidence ({{ $matter->documents->count() }})</span>
            </a>
            <a href="#dispatches-section" class="px-4 py-2 text-xs font-medium text-[#766A5E] hover:text-[#222222] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">forum</span>
                <span>Privileged Messages ({{ $matter->messages->count() }})</span>
            </a>
        </div>
    </div>

    <!-- Dossier Content Columns -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-4">
        <!-- Left 2-Columns: Milestones, Filings & Time -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Procedural Stage Tracker -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Litigation Procedural Stages</h3>
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2 rounded bg-[#F4EFEA] text-[#766A5E]">
                        <span class="font-mono text-[10px] block">01</span> Intake
                    </div>
                    <div class="p-2 rounded bg-[#F4EFEA] text-[#766A5E]">
                        <span class="font-mono text-[10px] block">02</span> Pleadings
                    </div>
                    <div class="p-2 rounded bg-[#9F8349] text-white font-semibold shadow-sm ring-2 ring-[#F4ECE1]">
                        <span class="font-mono text-[10px] block text-[#B88B56]">03 ACTIVE</span> Discovery
                    </div>
                    <div class="p-2 rounded bg-[#FAF8F5] text-[#766A5E]">
                        <span class="font-mono text-[10px] block">04</span> Pre-Trial
                    </div>
                    <div class="p-2 rounded bg-[#FAF8F5] text-[#766A5E]">
                        <span class="font-mono text-[10px] block">05</span> Trial
                    </div>
                </div>
            </div>

            <!-- Legal Opinions & Strategy Memoranda -->
            <div id="opinions-section" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">draw</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Legal Opinions &amp; Case Strategy</h3>
                    </div>
                    <a href="{{ route('opinions.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#9F8349] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Draft Opinion</span>
                    </a>
                </div>

                <div class="divide-y divide-[#F4EFEA]">
                    @forelse($matter->opinions as $op)
                    <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors">
                        <div>
                            <a href="{{ route('opinions.show', $op) }}" class="text-xs font-semibold text-[#222222] hover:text-[#9F8349] hover:underline">
                                {{ $op->title }}
                            </a>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#766A5E]">
                                <span class="font-mono text-[#9F8349]">{{ $op->opinion_number }}</span>
                                <span>•</span>
                                <span class="capitalize">{{ str_replace('_', ' ', $op->type) }}</span>
                                <span>•</span>
                                <span>{{ $op->author->name ?? 'Advocate' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                                @if($op->status === 'published') bg-emerald-50 text-emerald-700
                                @elseif($op->status === 'under_review') bg-amber-50 text-amber-700
                                @elseif($op->status === 'approved') bg-blue-50 text-blue-700
                                @else bg-gray-50 text-gray-700 @endif">
                                {{ $op->status }}
                            </span>
                            <a href="{{ route('opinions.show', $op) }}" class="p-1 rounded text-[#766A5E] hover:text-[#9F8349]">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#766A5E]">No formal legal opinions drafted for this matter yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Hearings & Court Docket Appearances -->
            <div id="hearings-section" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">calendar_month</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Scheduled Hearings &amp; Appearances</h3>
                    </div>
                    <a href="{{ route('appointments.create', ['matter_id' => $matter->id]) }}" class="text-xs text-[#9F8349] hover:underline font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Book Appearance</span>
                    </a>
                </div>

                <div class="divide-y divide-[#F4EFEA]">
                    @forelse($matter->appointments as $apt)
                    <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] flex flex-col items-center justify-center shrink-0">
                                <span class="font-mono text-[8px] uppercase font-bold text-[#9F8349]">{{ $apt->scheduled_at->format('M') }}</span>
                                <span class="font-serif font-bold text-sm text-[#222222]">{{ $apt->scheduled_at->format('d') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('appointments.show', $apt) }}" class="text-xs font-semibold text-[#222222] hover:text-[#9F8349] hover:underline">
                                    {{ $apt->title }}
                                </a>
                                <div class="text-[11px] text-[#766A5E] mt-0.5">
                                    <span>{{ $apt->scheduled_at->format('h:i A') }}</span>
                                    @if($apt->location) <span>• {{ $apt->location }}</span> @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono capitalize 
                            @if($apt->status === 'completed') bg-emerald-50 text-emerald-700
                            @elseif($apt->status === 'adjourned') bg-amber-50 text-amber-700
                            @else bg-blue-50 text-blue-700 @endif">
                            {{ $apt->status }}
                        </span>
                    </div>
                    @empty
                    <div class="p-5 text-center text-xs text-[#766A5E]">No court appearances currently scheduled.</div>
                    @endforelse
                </div>
            </div>

            <!-- Documents & Evidence in Matter -->
            <div id="filings-section" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">folder</span>
                        <h3 class="text-sm font-semibold text-[#222222]">Matter Evidence &amp; Filings</h3>
                    </div>
                    <a href="{{ route('documents.index') }}" class="text-xs text-[#9F8349] hover:underline font-medium">Vault View</a>
                </div>

                <div class="divide-y divide-[#F4EFEA]">
                    @forelse($matter->documents as $doc)
                    <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-red-50 text-red-700 flex items-center justify-center font-mono text-[10px] font-bold shrink-0">
                                PDF
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-semibold text-[#222222] hover:underline cursor-pointer">{{ $doc->title }}</span>
                                <span class="text-[11px] text-[#766A5E] font-mono mt-0.5">
                                    {{ $doc->filename }} <span class="mx-1">·</span> {{ $doc->formattedSize() }} <span class="mx-1">·</span> v{{ $doc->version }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-[#B88B56]/60 text-[#9F8349] font-semibold">{{ $doc->privilege }}</span>
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 text-[#766A5E] hover:text-[#9F8349] rounded inline-flex" title="Download Filing">
                                <span class="material-symbols-outlined text-lg">download</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#766A5E]">No documents filed in this matter yet.</div>
                    @endforelse
                </div>
            </div>



            <!-- Privileged Attorney-Client Communications (Stitch: secure_communications_client_messages) -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#F4EFEA] bg-[#fdfdfc] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349] text-xl">lock</span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#222222]">Privileged Matter Dispatch</h3>
                            <span class="text-[10px] font-mono text-[#9F8349] bg-[#B88B56]/50 px-1.5 py-0.5 rounded font-bold uppercase">ABA Model Rule 1.6 Protected</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-[#766A5E]">{{ $matter->messages->count() }} Dispatches</span>
                </div>

                <!-- Messages Stream -->
                <div class="p-4 flex flex-col gap-4 max-h-96 overflow-y-auto bg-[#FAF8F5]">
                    @forelse($matter->messages as $msg)
                    <div class="flex items-start gap-3 {{ $msg->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        <img alt="{{ $msg->sender->name }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-[#EAE4DC] shrink-0" src="{{ $msg->sender->avatar_url ?? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80' }}"/>
                        <div class="flex flex-col max-w-lg {{ $msg->sender_id === auth()->id() ? 'items-end' : '' }}">
                            <div class="flex items-center gap-2 mb-1 text-[11px]">
                                <span class="font-semibold text-[#222222]">{{ $msg->sender->name }}</span>
                                <span class="text-[#766A5E] font-mono">{{ $msg->created_at->format('M d, g:i A') }}</span>
                            </div>
                            <div class="p-3 rounded-xl text-xs leading-relaxed {{ $msg->sender_id === auth()->id() ? 'bg-[#9F8349] text-white rounded-tr-none' : 'bg-white border border-[#EFECE6] text-[#222222] rounded-tl-none shadow-xs' }}">
                                {{ $msg->body }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-[#766A5E]">No encrypted dispatches sent under this matter yet.</div>
                    @endforelse
                </div>

                <!-- Message Composer -->
                <form action="{{ route('messages.store') }}" method="POST" class="p-3 bg-white border-t border-[#F4EFEA] flex gap-2">
                    @csrf
                    <input type="hidden" name="matter_id" value="{{ $matter->id }}"/>
                    <input name="body" required placeholder="Type privileged counsel communication or discovery inquiry..." class="flex-1 px-3 py-2 text-xs rounded-lg border border-[#EAE4DC] outline-none focus:border-[#9F8349]"/>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-sm">send</span>
                        <span>Send</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Right 1-Column: Judicial Information & Team -->
        <div class="flex flex-col gap-6">

            <!-- Forum & Judge Card -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Judicial Assignment</h3>
                <div class="flex flex-col gap-3 text-xs">
                    <div>
                        <span class="text-[#766A5E] block text-[11px]">Court Forum</span>
                        <span class="font-medium text-[#222222]">{{ $matter->court_name ?? 'Forum Not Assigned' }}</span>
                    </div>
                    <div>
                        <span class="text-[#766A5E] block text-[11px]">Presiding Judge</span>
                        <span class="font-medium text-[#222222]">{{ $matter->judge_name ?? 'Magistrate Pending' }}</span>
                    </div>
                </div>
            </div>

            <!-- Client & Trust Card -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Client Information</h3>
                <div class="flex flex-col gap-2.5 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-[#9F8349]">business</span>
                        <span class="font-semibold text-sm text-[#222222]">{{ $matter->client->name }}</span>
                    </div>
                    <div class="text-[#554D45]">
                        <span class="text-[#766A5E] block text-[11px]">Primary Contact</span>
                        <span>{{ $matter->client->contact_person }}</span>
                    </div>
                    <div class="text-[#554D45]">
                        <span class="text-[#766A5E] block text-[11px]">Email &amp; Direct Phone</span>
                        <span>{{ $matter->client->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Assigned Legal Team -->
            <div class="bg-white rounded-xl border border-[#EFECE6] p-5 shadow-sm">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Litigation Team</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img alt="{{ $matter->leadAttorney?->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-[#EAE4DC]" src="{{ $matter->leadAttorney?->avatar_url }}"/>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#222222]">{{ $matter->leadAttorney?->name }}</span>
                            <span class="text-[10px] text-[#766A5E]">Lead Trial Counsel</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
