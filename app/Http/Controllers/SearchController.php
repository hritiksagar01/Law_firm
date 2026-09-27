<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CaseNote;
use App\Models\Client;
use App\Models\Document;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Unified Global Search Engine across 8 Practice Entities:
     * Clients, Matters, Documents, Parties, Messages, Tasks, Notes, Calendar Events.
     *
     * Critical Security Rule:
     * Search results are strictly authorization-aware.
     * - Firm Multi-Tenancy: Staff can only search within their authenticated firm_id.
     * - Client Isolation: Clients can ONLY discover records explicitly authorized for them
     *   (own client profile, own matters, client-visible documents, own matter parties,
     *    external client messages, own matter tasks, client-visible notes, and own calendar events).
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $isClient = $user->isClient();
        $firmId = $user->firm_id ?: (Firm::first()?->id ?: 1);

        // Resolve client instance for client users
        $client = null;
        if ($isClient) {
            $client = Client::where('user_id', $user->id)->first()
                ?? Client::where('email', $user->email)->first();
        }

        $clientId = $client?->id;

        // Query parameters
        $q = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', 'all');

        // Comprehensive Filters
        $filterClient = $request->input('client');
        $filterMatter = $request->input('matter');
        $filterMatterNumber = $request->input('matter_number');
        $filterDocument = $request->input('document');
        $filterDocType = $request->input('document_type', $request->input('category'));
        $filterAttorney = $request->input('attorney');
        $filterParalegal = $request->input('paralegal');
        $filterStatus = $request->input('status');
        $filterTag = $request->input('tag');
        $filterDate = $request->input('date');
        $filterDateFrom = $request->input('date_from');
        $filterDateTo = $request->input('date_to');

        // Check if any search or filter parameter is active
        $hasFilters = $q !== ''
            || ! empty($filterClient)
            || ! empty($filterMatter)
            || ! empty($filterMatterNumber)
            || ! empty($filterDocument)
            || ! empty($filterDocType)
            || ! empty($filterAttorney)
            || ! empty($filterParalegal)
            || ! empty($filterStatus)
            || ! empty($filterTag)
            || ! empty($filterDate)
            || ! empty($filterDateFrom)
            || ! empty($filterDateTo);

        // -------------------------------------------------------------
        // 1. CLIENTS SEARCH QUERY
        // -------------------------------------------------------------
        $clientsQuery = Client::query();
        if ($isClient) {
            if (! $clientId) {
                $clientsQuery->whereRaw('1 = 0');
            } else {
                $clientsQuery->where('id', $clientId);
            }
        } else {
            $clientsQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $clientsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%")
                    ->orWhere('pan_number', 'like', "%{$q}%")
                    ->orWhere('tax_id', 'like', "%{$q}%");
            });
        }

        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $clientsQuery->where('id', (int) $filterClient);
            } else {
                $clientsQuery->where('name', 'like', "%{$filterClient}%");
            }
        }
        if (! empty($filterStatus)) {
            $clientsQuery->where('status', $filterStatus);
        }
        if (! empty($filterDocType)) {
            $clientsQuery->where('category', 'like', "%{$filterDocType}%");
        }
        if (! empty($filterAttorney)) {
            $clientsQuery->where(function ($sub) use ($filterAttorney) {
                $sub->where('primary_attorney_id', $filterAttorney)
                    ->orWhere('preferred_attorney_id', $filterAttorney);
            });
        }
        if (! empty($filterParalegal)) {
            $clientsQuery->where('assigned_paralegal_id', $filterParalegal);
        }
        if (! empty($filterDate)) {
            $clientsQuery->whereDate('created_at', $filterDate);
        }
        if (! empty($filterDateFrom)) {
            $clientsQuery->whereDate('created_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $clientsQuery->whereDate('created_at', '<=', $filterDateTo);
        }

        $clientsCount = $clientsQuery->count();
        $clients = ($type === 'clients' || $type === 'all') ? $clientsQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 2. MATTERS SEARCH QUERY
        // -------------------------------------------------------------
        $mattersQuery = Matter::query()->with(['client', 'leadAttorney', 'assignedParalegal']);
        if ($isClient) {
            if (! $clientId) {
                $mattersQuery->whereRaw('1 = 0');
            } else {
                $mattersQuery->where('client_id', $clientId);
            }
        } else {
            $mattersQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $mattersQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('case_number', 'like', "%{$q}%")
                    ->orWhere('court_name', 'like', "%{$q}%")
                    ->orWhere('opposing_party', 'like', "%{$q}%")
                    ->orWhere('opposing_counsel', 'like', "%{$q}%")
                    ->orWhere('judge', 'like', "%{$q}%")
                    ->orWhere('case_type', 'like', "%{$q}%")
                    ->orWhere('stage', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $mattersQuery->where('client_id', (int) $filterClient);
            } else {
                $mattersQuery->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
            }
        }
        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $mattersQuery->where('id', (int) $filterMatter);
            } else {
                $mattersQuery->where('title', 'like', "%{$filterMatter}%");
            }
        }
        if (! empty($filterMatterNumber)) {
            $mattersQuery->where('case_number', 'like', "%{$filterMatterNumber}%");
        }
        if (! empty($filterAttorney)) {
            $mattersQuery->where(function ($sub) use ($filterAttorney) {
                $sub->where('lead_attorney_id', $filterAttorney)
                    ->orWhere('supervising_attorney_id', $filterAttorney);
            });
        }
        if (! empty($filterParalegal)) {
            $mattersQuery->where('assigned_paralegal_id', $filterParalegal);
        }
        if (! empty($filterStatus)) {
            $mattersQuery->where(function ($sub) use ($filterStatus) {
                $sub->where('status', $filterStatus)
                    ->orWhere('stage', $filterStatus);
            });
        }
        if (! empty($filterDocType)) {
            $mattersQuery->where('case_type', 'like', "%{$filterDocType}%");
        }
        if (! empty($filterDate)) {
            $mattersQuery->where(function ($sub) use ($filterDate) {
                $sub->whereDate('filing_date', $filterDate)
                    ->orWhereDate('created_at', $filterDate);
            });
        }
        if (! empty($filterDateFrom)) {
            $mattersQuery->whereDate('created_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $mattersQuery->whereDate('created_at', '<=', $filterDateTo);
        }

        $mattersCount = $mattersQuery->count();
        $matters = ($type === 'matters' || $type === 'all') ? $mattersQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 3. DOCUMENTS SEARCH QUERY
        // -------------------------------------------------------------
        $docsQuery = Document::query()->with(['matter', 'client', 'uploader']);
        if ($isClient) {
            if (! $clientId) {
                $docsQuery->whereRaw('1 = 0');
            } else {
                $docsQuery->where(function ($sub) use ($clientId) {
                    $sub->where('client_id', $clientId)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', $clientId));
                })
                    ->where('is_client_visible', true)
                    ->whereNotIn('visibility', ['internal_only', 'attorney_only', 'legal_team', 'restricted']);
            }
        } else {
            $docsQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $docsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('filename', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('document_type', 'like', "%{$q}%");
            });
        }

        if (! empty($filterDocument)) {
            $docsQuery->where(function ($sub) use ($filterDocument) {
                $sub->where('title', 'like', "%{$filterDocument}%")
                    ->orWhere('filename', 'like', "%{$filterDocument}%");
            });
        }
        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $docsQuery->where(function ($sub) use ($filterClient) {
                    $sub->where('client_id', (int) $filterClient)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
                });
            } else {
                $docsQuery->where(function ($sub) use ($filterClient) {
                    $sub->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"))
                        ->orWhereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
                });
            }
        }
        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $docsQuery->where('matter_id', (int) $filterMatter);
            } else {
                $docsQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
            }
        }
        if (! empty($filterMatterNumber)) {
            $docsQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
        }
        if (! empty($filterDocType)) {
            $docsQuery->where(function ($sub) use ($filterDocType) {
                $sub->where('category', 'like', "%{$filterDocType}%")
                    ->orWhere('document_type', 'like', "%{$filterDocType}%");
            });
        }
        if (! empty($filterAttorney)) {
            $docsQuery->where(function ($sub) use ($filterAttorney) {
                $sub->where('user_id', $filterAttorney)
                    ->orWhereHas('matter', fn ($m) => $m->where('lead_attorney_id', $filterAttorney));
            });
        }
        if (! empty($filterStatus)) {
            $docsQuery->where('status', $filterStatus);
        }
        if (! empty($filterTag)) {
            $docsQuery->where(function ($sub) use ($filterTag) {
                $sub->whereJsonContains('tags', $filterTag)
                    ->orWhere('tags', 'like', "%{$filterTag}%");
            });
        }
        if (! empty($filterDate)) {
            $docsQuery->whereDate('created_at', $filterDate);
        }
        if (! empty($filterDateFrom)) {
            $docsQuery->whereDate('created_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $docsQuery->whereDate('created_at', '<=', $filterDateTo);
        }

        $documentsCount = $docsQuery->count();
        $documents = ($type === 'documents' || $type === 'all') ? $docsQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 4. PARTIES SEARCH QUERY
        // Opposing parties, opposing counsel, client parties, task parties
        // -------------------------------------------------------------
        $parties = $this->searchParties(
            $firmId,
            $isClient,
            $clientId,
            $q,
            $filterClient,
            $filterMatter,
            $filterMatterNumber
        );
        $partiesCount = $parties->count();
        if ($type !== 'parties' && $type !== 'all') {
            $parties = collect();
        }

        // -------------------------------------------------------------
        // 5. MESSAGES SEARCH QUERY
        // -------------------------------------------------------------
        $messagesQuery = Message::withoutGlobalScopes()->with(['matter', 'sender', 'recipient', 'thread']);
        if ($isClient) {
            if (! $clientId) {
                $messagesQuery->whereRaw('1 = 0');
            } else {
                $messagesQuery->where('is_internal', false)
                    ->where(function ($sub) use ($clientId, $user) {
                        $sub->whereHas('matter', fn ($m) => $m->where('client_id', $clientId))
                            ->orWhere('sender_id', $user->id)
                            ->orWhere('recipient_id', $user->id);
                    });
            }
        } else {
            $messagesQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $messagesQuery->where(function ($sub) use ($q) {
                $sub->where('subject', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%")
                    ->orWhere('message_number', 'like', "%{$q}%");
            });
        }

        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $messagesQuery->where('matter_id', (int) $filterMatter);
            } else {
                $messagesQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
            }
        }
        if (! empty($filterMatterNumber)) {
            $messagesQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
        }
        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $messagesQuery->whereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
            } else {
                $messagesQuery->whereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
            }
        }
        if (! empty($filterAttorney)) {
            $messagesQuery->where(function ($sub) use ($filterAttorney) {
                $sub->where('sender_id', $filterAttorney)
                    ->orWhere('recipient_id', $filterAttorney);
            });
        }
        if (! empty($filterStatus)) {
            $messagesQuery->where('status', $filterStatus);
        }
        if (! empty($filterDate)) {
            $messagesQuery->whereDate('sent_at', $filterDate);
        }
        if (! empty($filterDateFrom)) {
            $messagesQuery->whereDate('sent_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $messagesQuery->whereDate('sent_at', '<=', $filterDateTo);
        }

        $messagesCount = $messagesQuery->count();
        $messages = ($type === 'messages' || $type === 'all') ? $messagesQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 6. TASKS SEARCH QUERY
        // -------------------------------------------------------------
        $tasksQuery = Task::query()->with(['matter', 'assignee', 'creator', 'relatedClient', 'relatedDocument']);
        if ($isClient) {
            if (! $clientId) {
                $tasksQuery->whereRaw('1 = 0');
            } else {
                $tasksQuery->where(function ($sub) use ($clientId) {
                    $sub->where('related_client_id', $clientId)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', $clientId));
                });
            }
        } else {
            $tasksQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $tasksQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('task_number', 'like', "%{$q}%")
                    ->orWhere('priority', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhere('related_party_name', 'like', "%{$q}%");
            });
        }

        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $tasksQuery->where('matter_id', (int) $filterMatter);
            } else {
                $tasksQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
            }
        }
        if (! empty($filterMatterNumber)) {
            $tasksQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
        }
        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $tasksQuery->where(function ($sub) use ($filterClient) {
                    $sub->where('related_client_id', (int) $filterClient)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
                });
            } else {
                $tasksQuery->where(function ($sub) use ($filterClient) {
                    $sub->whereHas('relatedClient', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"))
                        ->orWhereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
                });
            }
        }
        if (! empty($filterAttorney)) {
            $tasksQuery->where(function ($sub) use ($filterAttorney) {
                $sub->where('assigned_to', $filterAttorney)
                    ->orWhere('created_by', $filterAttorney);
            });
        }
        if (! empty($filterStatus)) {
            $tasksQuery->where('status', $filterStatus);
        }
        if (! empty($filterTag)) {
            $tasksQuery->where(function ($sub) use ($filterTag) {
                $sub->whereJsonContains('tags', $filterTag)
                    ->orWhere('tags', 'like', "%{$filterTag}%");
            });
        }
        if (! empty($filterDate)) {
            $tasksQuery->where(function ($sub) use ($filterDate) {
                $sub->whereDate('due_date', $filterDate)
                    ->orWhereDate('created_at', $filterDate);
            });
        }
        if (! empty($filterDateFrom)) {
            $tasksQuery->whereDate('due_date', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $tasksQuery->whereDate('due_date', '<=', $filterDateTo);
        }

        $tasksCount = $tasksQuery->count();
        $tasks = ($type === 'tasks' || $type === 'all') ? $tasksQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 7. NOTES (CaseNote) SEARCH QUERY
        // Strict Isolation: Clients CAN NEVER discover internal notes.
        // -------------------------------------------------------------
        $notesQuery = CaseNote::query()->with(['matter', 'user', 'category']);
        if ($isClient) {
            if (! $clientId) {
                $notesQuery->whereRaw('1 = 0');
            } else {
                $notesQuery->where('type', 'client_visible')
                    ->whereHas('matter', fn ($m) => $m->where('client_id', $clientId));
            }
        } else {
            $notesQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $notesQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $notesQuery->where('matter_id', (int) $filterMatter);
            } else {
                $notesQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
            }
        }
        if (! empty($filterMatterNumber)) {
            $notesQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
        }
        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $notesQuery->whereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
            } else {
                $notesQuery->whereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
            }
        }
        if (! empty($filterAttorney)) {
            $notesQuery->where('user_id', $filterAttorney);
        }
        if (! empty($filterStatus)) {
            $notesQuery->where('type', $filterStatus);
        }
        if (! empty($filterDocType)) {
            $notesQuery->whereHas('category', fn ($c) => $c->where('name', 'like', "%{$filterDocType}%"));
        }
        if (! empty($filterDate)) {
            $notesQuery->whereDate('created_at', $filterDate);
        }
        if (! empty($filterDateFrom)) {
            $notesQuery->whereDate('created_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $notesQuery->whereDate('created_at', '<=', $filterDateTo);
        }

        $notesCount = $notesQuery->count();
        $notes = ($type === 'notes' || $type === 'all') ? $notesQuery->take(50)->get() : collect();

        // -------------------------------------------------------------
        // 8. CALENDAR EVENTS & APPOINTMENTS SEARCH QUERY
        // -------------------------------------------------------------
        $eventsQuery = Event::query()->with(['matter', 'user']);
        $appointmentsQuery = Appointment::query()->with(['matter', 'client', 'attorney']);

        if ($isClient) {
            if (! $clientId) {
                $eventsQuery->whereRaw('1 = 0');
                $appointmentsQuery->whereRaw('1 = 0');
            } else {
                $eventsQuery->whereHas('matter', fn ($m) => $m->where('client_id', $clientId));
                $appointmentsQuery->where(function ($sub) use ($clientId) {
                    $sub->where('client_id', $clientId)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', $clientId));
                });
            }
        } else {
            $eventsQuery->where('firm_id', $firmId);
            $appointmentsQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $eventsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('event_type', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });

            $appointmentsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $eventsQuery->where('matter_id', (int) $filterMatter);
                $appointmentsQuery->where('matter_id', (int) $filterMatter);
            } else {
                $eventsQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
                $appointmentsQuery->whereHas('matter', fn ($m) => $m->where('title', 'like', "%{$filterMatter}%"));
            }
        }
        if (! empty($filterMatterNumber)) {
            $eventsQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
            $appointmentsQuery->whereHas('matter', fn ($m) => $m->where('case_number', 'like', "%{$filterMatterNumber}%"));
        }
        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $eventsQuery->whereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
                $appointmentsQuery->where(function ($sub) use ($filterClient) {
                    $sub->where('client_id', (int) $filterClient)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', (int) $filterClient));
                });
            } else {
                $eventsQuery->whereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
                $appointmentsQuery->where(function ($sub) use ($filterClient) {
                    $sub->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"))
                        ->orWhereHas('matter.client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
                });
            }
        }
        if (! empty($filterDocType)) {
            $eventsQuery->where('event_type', 'like', "%{$filterDocType}%");
            $appointmentsQuery->where('type', 'like', "%{$filterDocType}%");
        }
        if (! empty($filterAttorney)) {
            $eventsQuery->where('user_id', $filterAttorney);
            $appointmentsQuery->where('user_id', $filterAttorney);
        }
        if (! empty($filterStatus)) {
            $appointmentsQuery->where('status', $filterStatus);
        }
        if (! empty($filterDate)) {
            $eventsQuery->whereDate('start_time', $filterDate);
            $appointmentsQuery->whereDate('scheduled_at', $filterDate);
        }
        if (! empty($filterDateFrom)) {
            $eventsQuery->whereDate('start_time', '>=', $filterDateFrom);
            $appointmentsQuery->whereDate('scheduled_at', '>=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $eventsQuery->whereDate('start_time', '<=', $filterDateTo);
            $appointmentsQuery->whereDate('scheduled_at', '<=', $filterDateTo);
        }

        $eventsCount = $eventsQuery->count() + $appointmentsQuery->count();
        $calendarItems = collect();
        if ($type === 'events' || $type === 'all') {
            $evs = $eventsQuery->take(30)->get()->map(function ($ev) {
                return (object) [
                    'source_type' => 'event',
                    'id' => $ev->id,
                    'title' => $ev->title,
                    'category' => $ev->event_type ?: 'Court Event',
                    'date' => $ev->start_time,
                    'end_date' => $ev->end_time,
                    'location' => $ev->location ?: 'Chambers / Virtual',
                    'notes' => $ev->notes,
                    'matter' => $ev->matter,
                    'user' => $ev->user,
                    'is_deadline' => (bool) $ev->is_statutory_deadline,
                ];
            });

            $apps = $appointmentsQuery->take(30)->get()->map(function ($app) {
                return (object) [
                    'source_type' => 'appointment',
                    'id' => $app->id,
                    'title' => $app->title,
                    'category' => $app->type ?: 'Consultation',
                    'date' => $app->scheduled_at,
                    'end_date' => null,
                    'location' => 'Lawyer Workspace Office',
                    'notes' => $app->notes,
                    'matter' => $app->matter,
                    'user' => $app->attorney,
                    'client' => $app->client,
                    'status' => $app->status ?: 'scheduled',
                    'is_deadline' => false,
                ];
            });

            $calendarItems = $evs->concat($apps)->sortBy('date')->values();
        }

        $totalMatches = $clientsCount + $mattersCount + $documentsCount + $partiesCount
            + $messagesCount + $tasksCount + $notesCount + $eventsCount;

        // Load filter metadata options for dropdowns
        $availableClients = $isClient
            ? ($client ? collect([$client]) : collect())
            : Client::where('firm_id', $firmId)->orderBy('name')->get(['id', 'name']);

        $availableMatters = $isClient
            ? Matter::where('client_id', $clientId)->orderBy('title')->get(['id', 'title', 'case_number'])
            : Matter::where('firm_id', $firmId)->orderBy('title')->get(['id', 'title', 'case_number']);

        $availableAttorneys = $isClient
            ? collect()
            : User::where('firm_id', $firmId)
                ->whereIn('role', ['admin', 'partner', 'attorney', 'associate', 'managing_attorney', 'lawyer'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']);

        $availableParalegals = $isClient
            ? collect()
            : User::where('firm_id', $firmId)
                ->whereIn('role', ['paralegal', 'legal_assistant', 'staff'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']);

        $documentTypes = Document::DOCUMENT_TYPES;

        return view('search', compact(
            'q',
            'type',
            'hasFilters',
            'isClient',
            'totalMatches',
            'clientsCount',
            'mattersCount',
            'documentsCount',
            'partiesCount',
            'messagesCount',
            'tasksCount',
            'notesCount',
            'eventsCount',
            'clients',
            'matters',
            'documents',
            'parties',
            'messages',
            'tasks',
            'notes',
            'calendarItems',
            'availableClients',
            'availableMatters',
            'availableAttorneys',
            'availableParalegals',
            'documentTypes',
            'filterClient',
            'filterMatter',
            'filterMatterNumber',
            'filterDocument',
            'filterDocType',
            'filterAttorney',
            'filterParalegal',
            'filterStatus',
            'filterTag',
            'filterDate',
            'filterDateFrom',
            'filterDateTo'
        ));
    }

    /**
     * Search and aggregate Party entities with strict multi-tenant / client isolation.
     *
     * @return Collection<int, object>
     */
    protected function searchParties(
        int $firmId,
        bool $isClient,
        ?int $clientId,
        string $q,
        mixed $filterClient,
        mixed $filterMatter,
        mixed $filterMatterNumber
    ): Collection {
        $results = collect();

        // 1. Opposing Parties & Counsel from Matters
        $matterQuery = Matter::query()->with('client');
        if ($isClient) {
            if (! $clientId) {
                $matterQuery->whereRaw('1 = 0');
            } else {
                $matterQuery->where('client_id', $clientId);
            }
        } else {
            $matterQuery->where('firm_id', $firmId);
        }

        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $matterQuery->where('client_id', (int) $filterClient);
            } else {
                $matterQuery->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$filterClient}%"));
            }
        }
        if (! empty($filterMatter)) {
            if (is_numeric($filterMatter)) {
                $matterQuery->where('id', (int) $filterMatter);
            } else {
                $matterQuery->where('title', 'like', "%{$filterMatter}%");
            }
        }
        if (! empty($filterMatterNumber)) {
            $matterQuery->where('case_number', 'like', "%{$filterMatterNumber}%");
        }

        $mattersWithParties = $matterQuery->where(function ($sub) {
            $sub->whereNotNull('opposing_party')->orWhereNotNull('opposing_counsel');
        })->get();

        foreach ($mattersWithParties as $m) {
            if (! empty($m->opposing_party)) {
                if ($q === '' || stripos($m->opposing_party, $q) !== false || stripos($m->title, $q) !== false) {
                    $results->push((object) [
                        'party_name' => $m->opposing_party,
                        'party_role' => 'Opposing Party / Litigant',
                        'matter_title' => $m->title,
                        'matter_id' => $m->id,
                        'matter_number' => $m->case_number,
                        'client_name' => $m->client?->name,
                        'client_id' => $m->client_id,
                        'counsel' => $m->opposing_counsel,
                        'context' => 'Contested Litigation Docket: '.$m->court_name,
                    ]);
                }
            }

            if (! empty($m->opposing_counsel)) {
                if ($q === '' || stripos($m->opposing_counsel, $q) !== false || stripos($m->title, $q) !== false) {
                    $results->push((object) [
                        'party_name' => $m->opposing_counsel,
                        'party_role' => 'Opposing Counsel',
                        'matter_title' => $m->title,
                        'matter_id' => $m->id,
                        'matter_number' => $m->case_number,
                        'client_name' => $m->client?->name,
                        'client_id' => $m->client_id,
                        'counsel' => 'Representing '.$m->opposing_party,
                        'context' => 'Counsel of Record in '.$m->case_number,
                    ]);
                }
            }
        }

        // 2. Client Parties (Client Representatives & Key Contacts)
        $clientQuery = Client::query();
        if ($isClient) {
            if (! $clientId) {
                $clientQuery->whereRaw('1 = 0');
            } else {
                $clientQuery->where('id', $clientId);
            }
        } else {
            $clientQuery->where('firm_id', $firmId);
        }

        if (! empty($filterClient)) {
            if (is_numeric($filterClient)) {
                $clientQuery->where('id', (int) $filterClient);
            } else {
                $clientQuery->where('name', 'like', "%{$filterClient}%");
            }
        }

        if ($q !== '') {
            $clientQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%");
            });
        }

        foreach ($clientQuery->take(20)->get() as $c) {
            $results->push((object) [
                'party_name' => $c->name,
                'party_role' => 'Retained Client / Litigant',
                'matter_title' => null,
                'matter_id' => null,
                'matter_number' => null,
                'client_name' => $c->name,
                'client_id' => $c->id,
                'counsel' => $c->contact_person ? 'Contact: '.$c->contact_person : null,
                'context' => 'Client Entity ('.ucfirst($c->category ?: 'individual').')',
            ]);
        }

        // 3. Related Parties from Tasks
        $taskQuery = Task::query()->whereNotNull('related_party_name')->with(['matter', 'relatedClient']);
        if ($isClient) {
            if (! $clientId) {
                $taskQuery->whereRaw('1 = 0');
            } else {
                $taskQuery->where(function ($sub) use ($clientId) {
                    $sub->where('related_client_id', $clientId)
                        ->orWhereHas('matter', fn ($m) => $m->where('client_id', $clientId));
                });
            }
        } else {
            $taskQuery->where('firm_id', $firmId);
        }

        if ($q !== '') {
            $taskQuery->where('related_party_name', 'like', "%{$q}%");
        }

        foreach ($taskQuery->take(20)->get() as $t) {
            $results->push((object) [
                'party_name' => $t->related_party_name,
                'party_role' => 'Related Party / Witness',
                'matter_title' => $t->matter?->title,
                'matter_id' => $t->matter_id,
                'matter_number' => $t->matter?->case_number,
                'client_name' => $t->relatedClient?->name,
                'client_id' => $t->related_client_id,
                'counsel' => null,
                'context' => 'Designated on Task #'.$t->task_number.': '.$t->title,
            ]);
        }

        // Deduplicate by party_name and matter_id
        return $results->unique(function ($item) {
            return $item->party_name.'_'.$item->party_role.'_'.($item->matter_id ?? 0);
        })->values();
    }
}
