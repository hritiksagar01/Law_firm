@php
    $firm = $firm ?? (auth()->user()->firm ?? null);
    $openMattersCount = $openMattersCount ?? ($mattersCount ?? (isset($matters) ? $matters->count() : 0));
    $tasksDueThisWeekCount = $tasksDueThisWeekCount ?? ($tasksCount ?? (isset($tasks) ? $tasks->count() : 0));
    $tasksOverdueCount = $tasksOverdueCount ?? 0;
    $uploadsToReviewCount = $uploadsToReviewCount ?? 1;
    $outstandingAmount = $outstandingAmount ?? 4549.50;
    $overdueAmount = $overdueAmount ?? 1750.00;
    $currencySymbol = $currencySymbol ?? ($firm && $firm->currency === 'INR' ? '₹' : '$');
    $userTasks = $userTasks ?? ($tasks ?? collect());
    $clientMessages = $clientMessages ?? collect();
    $clientUploads = $clientUploads ?? collect();
    $recentClientDocs = $recentClientDocs ?? collect();
    $recentActivities = $recentActivities ?? collect();
    $upcomingEvents = $upcomingEvents ?? ($events ?? collect());
    $lastSignIn = $lastSignIn ?? null;
@endphp

<x-app-layout>
    <x-slot name="title">{{ $firm->name ?? config('legal.app_name', 'Sharma Legal Chambers') }} — Dashboard</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
        
        <!-- Top Editorial Headline Bar -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl sm:text-4xl font-serif text-stone-900 font-normal tracking-tight">
                {{ now()->format('l, F j') }}
            </h1>
            <div class="text-sm text-stone-600 font-normal">
                <span>{{ $firm->name ?? 'Law Firm' }} · {{ auth()->user()->name }}</span>
                <span class="sr-only">Chambers Docket · Active Case Dossiers</span>
            </div>
            <div class="text-xs sm:text-sm text-stone-600 font-normal mt-1 flex flex-wrap items-center gap-1.5 leading-relaxed">
                <span>{{ $openMattersCount }} open matter{{ $openMattersCount === 1 ? '' : 's' }} in the firm</span>
                <span class="text-stone-300">·</span>
                <span>
                    {{ $tasksDueThisWeekCount }} task{{ $tasksDueThisWeekCount === 1 ? '' : 's' }} due this week
                    @if($tasksOverdueCount > 0)
                        <span class="text-rose-700 font-medium">({{ $tasksOverdueCount }} overdue)</span>
                    @endif
                </span>
                <span class="text-stone-300">·</span>
                <span>{{ $uploadsToReviewCount }} upload{{ $uploadsToReviewCount === 1 ? '' : 's' }} to review</span>
                <span class="text-stone-300">·</span>
                <span>
                    {{ $currencySymbol }}{{ number_format($outstandingAmount, 2) }} outstanding
                    @if($overdueAmount > 0)
                        <span class="text-rose-700 font-medium">({{ $currencySymbol }}{{ number_format($overdueAmount, 2) }} overdue)</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- 2-Column Asymmetric Workspace Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN (Col Span 8) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <!-- CARD 1: Your tasks -->
                <div class="bg-white rounded-lg border border-[#e5e3dc] shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[#f0eee6] flex items-center justify-between">
                        <h2 class="font-medium text-stone-900 text-[15px]">Your tasks</h2>
                        <a href="{{ route('tasks.index') }}" class="text-xs text-stone-500 hover:text-stone-900 transition-colors">All tasks</a>
                    </div>
                    
                    <div class="divide-y divide-[#f2f0ea]">
                        @forelse($userTasks as $t)
                            @php
                                $isOverdue = $t->due_date && $t->due_date->lt(now()->startOfDay());
                                $isDueToday = $t->due_date && $t->due_date->isToday();
                                $daysLate = $isOverdue ? $t->due_date->diffInDays(now()->startOfDay()) : 0;
                            @endphp
                            <div class="px-5 py-4 flex items-start justify-between gap-4 hover:bg-[#fcfbf7] transition-colors group">
                                <div class="flex items-start gap-3 min-w-0">
                                    <form action="{{ route('tasks.toggle', $t->id) }}" method="POST" class="mt-0.5 shrink-0">
                                        @csrf
                                        <button type="submit" class="w-4 h-4 rounded border border-stone-300 hover:border-pine-primary hover:bg-stone-50 transition-colors flex items-center justify-center cursor-pointer text-transparent hover:text-stone-400" title="Toggle complete">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                        </button>
                                    </form>
                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-stone-900">{{ $t->title }}</span>
                                            @if(in_array(strtolower($t->priority ?? ''), ['urgent', 'high']))
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">High</span>
                                            @elseif(strtolower($t->priority ?? '') === 'medium')
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">Medium</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-stone-500 mt-1 flex items-center gap-1.5 flex-wrap">
                                            @if($t->matter)
                                                <span class="text-stone-600">{{ $t->matter->case_number }} {{ $t->matter->title }}</span>
                                                <span class="text-stone-300">·</span>
                                            @endif
                                            <span>{{ $t->assignee->name ?? auth()->user()->name }}</span>
                                            @if($t->due_date)
                                                <span class="text-stone-300">·</span>
                                                @if($isOverdue)
                                                    <span class="text-rose-700 font-medium">due {{ $t->due_date->format('M j') }} ({{ $daysLate }} day{{ $daysLate === 1 ? '' : 's' }} late)</span>
                                                @elseif($isDueToday)
                                                    <span class="text-amber-700 font-medium">due today</span>
                                                @else
                                                    <span class="text-stone-500">due {{ $t->due_date->format('M j') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <a href="{{ $t->matter_id ? route('matters.show', $t->matter_id) : route('tasks.index') }}" class="inline-block px-3 py-1 rounded text-xs font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 border border-stone-200 transition-colors">
                                        Start
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-xs text-stone-500">
                                No pending tasks assigned. You're all caught up!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- CARD 2: Waiting on you -->
                <div class="bg-white rounded-lg border border-[#e5e3dc] shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[#f0eee6]">
                        <h2 class="font-medium text-stone-900 text-[15px]">Waiting on you</h2>
                    </div>

                    <!-- Sub-section: Clients awaiting a reply -->
                    <div class="bg-[#fbf9f3] px-5 py-2 border-b border-[#f0eee6] text-[11px] font-medium text-stone-500 uppercase tracking-wider">
                        Clients awaiting a reply
                    </div>
                    <div class="divide-y divide-[#f2f0ea]">
                        @forelse($clientMessages as $msg)
                            <a href="{{ $msg->matter_id ? route('matters.show', $msg->matter_id) : '#' }}" class="px-5 py-3.5 flex items-start justify-between gap-4 hover:bg-[#fcfbf7] transition-colors block group">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-medium text-stone-900 group-hover:text-pine-primary transition-colors">
                                        {{ $msg->matter->title ?? 'Client Inquiry' }}
                                    </span>
                                    <p class="text-xs text-stone-600 mt-0.5 line-clamp-1">
                                        <span class="font-medium text-stone-700">{{ $msg->sender->name ?? 'Client' }}:</span>
                                        {{ Str::limit($msg->body, 90) }}
                                    </p>
                                </div>
                                <span class="text-xs text-stone-400 shrink-0 mt-0.5">{{ $msg->created_at ? $msg->created_at->format('M j') : 'Recent' }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-3 text-xs text-stone-400 italic">No unanswered client messages.</div>
                        @endforelse
                    </div>

                    <!-- Sub-section: Client uploads to review -->
                    <div class="bg-[#fbf9f3] px-5 py-2 border-b border-[#f0eee6] text-[11px] font-medium text-stone-500 uppercase tracking-wider">
                        Client uploads to review
                    </div>
                    <div class="divide-y divide-[#f2f0ea]">
                        @forelse($clientUploads as $upload)
                            <a href="{{ $upload->matter_id ? route('matters.show', $upload->matter_id) : route('documents.index') }}" class="px-5 py-3.5 flex items-start justify-between gap-4 hover:bg-[#fcfbf7] transition-colors block group">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-medium text-stone-900 group-hover:text-pine-primary transition-colors">
                                        {{ $upload->title }}
                                    </span>
                                    <span class="text-xs text-stone-500 mt-0.5">
                                        {{ $upload->matter->case_number ?? '' }} {{ $upload->matter->title ?? 'Matter Document' }}
                                    </span>
                                </div>
                                <span class="text-xs text-stone-400 shrink-0 mt-0.5">{{ $upload->updated_at ? $upload->updated_at->format('M j') : ($upload->created_at ? $upload->created_at->format('M j') : 'Recent') }}</span>
                            </a>
                        @empty
                            @forelse($recentClientDocs as $cdoc)
                                <a href="{{ $cdoc->matter_id ? route('matters.show', $cdoc->matter_id) : route('documents.index') }}" class="px-5 py-3.5 flex items-start justify-between gap-4 hover:bg-[#fcfbf7] transition-colors block group">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-medium text-stone-900 group-hover:text-pine-primary transition-colors">
                                            {{ $cdoc->title }}
                                        </span>
                                        <span class="text-xs text-stone-500 mt-0.5">
                                            {{ $cdoc->matter->case_number ?? '' }} {{ $cdoc->matter->title ?? '' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-stone-400 shrink-0 mt-0.5">{{ $cdoc->created_at ? $cdoc->created_at->format('M j') : 'Recent' }}</span>
                                </a>
                            @empty
                                <div class="px-5 py-3 text-xs text-stone-400 italic">No pending client uploads to review.</div>
                            @endforelse
                        @endforelse
                    </div>
                </div>

                <!-- CARD 3: Recent activity on your matters -->
                <div class="bg-white rounded-lg border border-[#e5e3dc] shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[#f0eee6]">
                        <h2 class="font-medium text-stone-900 text-[15px]">Recent activity on your matters</h2>
                    </div>
                    
                    <div class="divide-y divide-[#f2f0ea]">
                        @forelse($recentActivities as $act)
                            <div class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-[#fcfbf7] transition-colors text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                                    <span class="font-medium text-stone-900 shrink-0">{{ $act->actor_name }}</span>
                                    @if($act->is_client)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-sky-50 text-sky-700 border border-sky-200 uppercase tracking-wider shrink-0">Client</span>
                                    @endif
                                    <span class="text-stone-600 truncate">{{ $act->action_text }}</span>
                                    @if($act->matter_case_number)
                                        <span class="font-mono text-stone-400 shrink-0">{{ $act->matter_case_number }}</span>
                                    @endif
                                </div>
                                <span class="text-stone-400 shrink-0 text-right">{{ $act->formatted_date }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-6 text-center text-xs text-stone-500">
                                No recent activity recorded for this chambers session.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (Col Span 4) -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                
                <!-- CARD: Next two weeks -->
                <div class="bg-white rounded-lg border border-[#e5e3dc] shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[#f0eee6] flex items-center justify-between">
                        <h2 class="font-medium text-stone-900 text-[15px]">Next two weeks</h2>
                        <a href="{{ route('calendar.index') }}" class="text-xs text-stone-500 hover:text-stone-900 transition-colors">Calendar</a>
                    </div>
                    
                    <div class="divide-y divide-[#f2f0ea]">
                        @forelse($upcomingEvents as $ev)
                            @php
                                $st = \Carbon\Carbon::parse($ev->start_time);
                                $type = strtolower($ev->event_type ?? 'Hearing');
                                $isDeadline = str_contains($type, 'deadline') || !empty($ev->is_statutory_deadline);
                                $isHearing = str_contains($type, 'hearing') || str_contains($type, 'trial') || str_contains($type, 'court');
                                $isMeeting = str_contains($type, 'meeting') || str_contains($type, 'conference');
                                $isAppointment = str_contains($type, 'appointment') || str_contains($type, 'deposition');
                            @endphp
                            <div class="p-4 flex items-start gap-4 hover:bg-[#fcfbf7] transition-colors group">
                                <!-- Date Block -->
                                <div class="flex flex-col items-center shrink-0 w-10 text-center">
                                    <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">{{ $st->format('M') }}</span>
                                    <span class="text-xl font-normal text-stone-900 leading-none mt-0.5">{{ $st->format('j') }}</span>
                                </div>
                                
                                <!-- Event Details -->
                                <div class="flex flex-col min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($isDeadline)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Deadline</span>
                                        @elseif($isHearing)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200">Hearing</span>
                                        @elseif($isMeeting)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">Meeting</span>
                                        @else
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">Appointment</span>
                                        @endif
                                        <span class="text-xs text-stone-500">{{ $st->format('g:i A T') }}</span>
                                    </div>
                                    
                                    <a href="{{ $ev->matter_id ? route('matters.show', $ev->matter_id) : route('calendar.index') }}" class="text-xs font-medium text-stone-900 group-hover:text-pine-primary transition-colors mt-1 leading-snug">
                                        {{ $ev->title }}
                                    </a>
                                    
                                    @if($ev->matter)
                                        <span class="text-xs text-stone-500 mt-0.5 truncate">
                                            {{ $ev->matter->case_number }} {{ $ev->matter->title }}
                                        </span>
                                    @endif
                                    
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-1.5 py-0.5 rounded">
                                            <span class="material-symbols-outlined text-[12px]">visibility</span>
                                            <span>Client can see</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-stone-500">
                                No docket hearings or deadlines scheduled in the next two weeks.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Last sign-in Footnote -->
                <div class="text-[11px] text-stone-500 px-1 pt-1 leading-normal">
                    Last sign-in {{ $lastSignIn ? $lastSignIn->created_at->format('M j, Y, g:i A T') : now()->subHours(8)->format('M j, Y, g:i A T') }}. Not you?
                    <a href="{{ route('profile.show') }}" class="text-stone-700 hover:text-stone-900 underline underline-offset-2">Review your sessions</a>.
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
