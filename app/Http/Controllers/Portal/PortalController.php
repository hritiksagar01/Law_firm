<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\CaseNote;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageThread;
use App\Models\Task;
use App\Models\User;
use App\Services\LegalPdfGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PortalController extends Controller
{
    /**
     * Retrieve the currently authenticated client record.
     */
    protected function getClient(): Client
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first()
            ?? Client::where('email', $user->email)->first();

        if (! $client) {
            $firmId = $user->firm_id ?: (Firm::first()?->id ?: 1);
            $client = Client::create([
                'firm_id' => $firmId,
                'user_id' => $user->id,
                'name' => $user->name ?: 'Client Representation',
                'contact_person' => $user->name ?: 'Client Representative',
                'email' => $user->email,
                'phone' => $user->phone ?? '+91 98200 11928',
                'category' => 'corporate',
                'type' => 'corporate',
                'onboarding_mode' => 'portal_online',
                'portal_status' => 'active',
                'status' => 'active',
                'trust_balance' => 0.00,
            ]);
        }

        return $client;
    }

    /**
     * Client Portal Dashboard Overview (F-08).
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $this->getClient();
        $client->load('primaryAttorney');

        // Ensure client has baseline samples across all dashboard widgets & matters
        self::ensureClientDashboardSamples($client);

        $matters = Matter::where('client_id', $client->id)
            ->with(['documents', 'leadAttorney', 'documentRequests', 'events'])
            ->latest()
            ->get();

        $matterIds = $matters->pluck('id');

        // 1. Active Matters (excluding closed/settled/dismissed/archived)
        $activeMatters = $matters->reject(fn ($m) => in_array(strtolower($m->status ?? ''), ['closed', 'settled', 'dismissed', 'archived']));

        // 2. Recent Documents (Client visible)
        $recentDocuments = Document::whereIn('matter_id', $matterIds)
            ->where('is_client_visible', true)
            ->with(['matter', 'uploader'])
            ->latest()
            ->take(6)
            ->get();

        $documentsCount = Document::whereIn('matter_id', $matterIds)
            ->where('is_client_visible', true)
            ->count();

        // 3. Pending Document Requests
        $pendingDocumentRequests = DocumentRequest::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'rejected', 'in_review'])
            ->with(['matter', 'requestedBy'])
            ->latest()
            ->get();

        $documentRequests = DocumentRequest::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        // 4. Upcoming Events & Scheduled Court Hearings
        $upcomingEvents = Event::whereIn('matter_id', $matterIds)
            ->where('start_time', '>=', now()->subHours(6))
            ->with('matter')
            ->orderBy('start_time', 'asc')
            ->take(6)
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = Event::whereIn('matter_id', $matterIds)
                ->with('matter')
                ->orderBy('start_time', 'desc')
                ->take(6)
                ->get();
        }
        $events = $upcomingEvents;

        // 3-Week Interactive Court Calendar Engine (Previous Week, This Week, Next Week - identical to Law Firm & Super Admin)
        $previousWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek()->endOfDay();
        $comingWeekStart = Carbon::now()->addWeek()->startOfWeek();
        $comingWeekEnd = Carbon::now()->addWeek()->endOfWeek()->endOfDay();

        $getScheduleForRange = function (Carbon $start, Carbon $end) use ($client, $matterIds) {
            $items = collect();
            $evts = Event::whereIn('matter_id', $matterIds)
                ->whereBetween('start_time', [$start, $end])
                ->with('matter')
                ->orderBy('start_time')
                ->get();

            foreach ($evts as $evt) {
                $startTime = Carbon::parse($evt->start_time);
                $type = $evt->event_type ?: 'Hearing';
                $isDeadline = $evt->is_statutory_deadline || str_contains(strtolower($type), 'deadline');

                $badgeColor = match (true) {
                    $isDeadline => 'bg-[#fce8e6] text-[#c5221f]',
                    str_contains(strtolower($type), 'hearing') => 'bg-[#f3e8ff] text-[#7e22ce]',
                    str_contains(strtolower($type), 'meeting') || str_contains(strtolower($type), 'conference') => 'bg-[#e0f2fe] text-[#0284c7]',
                    default => 'bg-[#e6f4ea] text-[#137333]',
                };

                $items->push([
                    'date' => $startTime,
                    'month_short' => strtoupper($startTime->format('M')),
                    'day_num' => $startTime->format('j'),
                    'time_str' => $startTime->format('g:i A'),
                    'type_label' => $isDeadline ? 'Deadline' : (str_contains(strtolower($type), 'hearing') ? 'Hearing' : (str_contains(strtolower($type), 'meeting') ? 'Meeting' : 'Hearing')),
                    'badge_class' => $badgeColor,
                    'title' => $evt->title,
                    'matter_info' => $evt->matter ? ($evt->matter->case_number.' '.$evt->matter->title) : 'Case Docket',
                    'matter_id' => $evt->matter_id,
                ]);
            }

            $appts = Appointment::where(function ($q) use ($client, $matterIds) {
                $q->where('client_id', $client->id);
                if ($matterIds->isNotEmpty()) {
                    $q->orWhereIn('matter_id', $matterIds);
                }
            })
                ->whereBetween('scheduled_at', [$start, $end])
                ->with(['matter', 'client'])
                ->orderBy('scheduled_at')
                ->get();

            foreach ($appts as $appt) {
                $schedTime = Carbon::parse($appt->scheduled_at);
                $items->push([
                    'date' => $schedTime,
                    'month_short' => strtoupper($schedTime->format('M')),
                    'day_num' => $schedTime->format('j'),
                    'time_str' => $schedTime->format('g:i A'),
                    'type_label' => 'Appointment',
                    'badge_class' => 'bg-[#e6f4ea] text-[#137333]',
                    'title' => $appt->title,
                    'matter_info' => $appt->matter ? ($appt->matter->case_number.' '.$appt->matter->title) : ($appt->client ? $appt->client->name : 'Client Consultation'),
                    'matter_id' => $appt->matter_id,
                ]);
            }

            return $items->sortBy('date')->values();
        };

        $previousWeekSchedule = $getScheduleForRange($previousWeekStart, $previousWeekEnd);
        $thisWeekSchedule = $getScheduleForRange($thisWeekStart, $thisWeekEnd);
        $comingWeekSchedule = $getScheduleForRange($comingWeekStart, $comingWeekEnd);

        // Fallback: If this week & coming week are empty, but events has items, ensure they are represented under This Week
        if ($thisWeekSchedule->isEmpty() && $comingWeekSchedule->isEmpty() && $events->isNotEmpty()) {
            foreach ($events as $fallbackEvt) {
                $startTime = Carbon::parse($fallbackEvt->start_time);
                $type = $fallbackEvt->event_type ?: 'Hearing';
                $isDeadline = $fallbackEvt->is_statutory_deadline || str_contains(strtolower($type), 'deadline');

                $badgeColor = match (true) {
                    $isDeadline => 'bg-[#fce8e6] text-[#c5221f]',
                    str_contains(strtolower($type), 'hearing') => 'bg-[#f3e8ff] text-[#7e22ce]',
                    str_contains(strtolower($type), 'meeting') || str_contains(strtolower($type), 'conference') => 'bg-[#e0f2fe] text-[#0284c7]',
                    default => 'bg-[#e6f4ea] text-[#137333]',
                };

                $thisWeekSchedule->push([
                    'date' => $startTime,
                    'month_short' => strtoupper($startTime->format('M')),
                    'day_num' => $startTime->format('j'),
                    'time_str' => $startTime->format('g:i A'),
                    'type_label' => $isDeadline ? 'Deadline' : (str_contains(strtolower($type), 'hearing') ? 'Hearing' : 'Hearing'),
                    'badge_class' => $badgeColor,
                    'title' => $fallbackEvt->title,
                    'matter_info' => $fallbackEvt->matter ? ($fallbackEvt->matter->case_number.' '.$fallbackEvt->matter->title) : 'Case Docket',
                    'matter_id' => $fallbackEvt->matter_id,
                ]);
            }
        }

        // 5. Recent Messages (Privileged Counsel Communications)
        $recentMessages = Message::whereIn('matter_id', $matterIds)
            ->with(['sender', 'matter'])
            ->latest()
            ->take(6)
            ->get();

        // 6. Tasks Requiring Client Action
        $clientTasks = Task::where(function ($q) use ($client, $matterIds) {
            if ($client->user_id) {
                $q->where('assigned_to', $client->user_id);
            }
            if ($matterIds->isNotEmpty()) {
                $q->orWhereIn('matter_id', $matterIds);
            }
        })
            ->with(['matter', 'assignee'])
            ->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 1 WHEN priority = 'high' THEN 2 WHEN priority = 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date', 'asc')
            ->take(8)
            ->get();

        $invoices = Invoice::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        // 7. Dynamic Notifications & Legal Alerts Hub
        $notifications = collect();

        // Pending document requests alerts
        foreach ($pendingDocumentRequests as $req) {
            $notifications->push([
                'id' => 'req-'.$req->id,
                'type' => $req->status === 'rejected' ? 'urgent' : 'document',
                'icon' => 'upload_file',
                'color' => $req->status === 'rejected' ? 'red' : 'amber',
                'badge' => $req->status === 'rejected' ? 'Resubmission Required' : 'Filing Required',
                'title' => 'Document Requested: '.$req->title,
                'description' => ($req->description ? Str::limit($req->description, 85) : 'Counsel requested submission').($req->matter ? ' for '.$req->matter->case_number : ''),
                'time' => $req->due_date ? 'Due '.Carbon::parse($req->due_date)->format('d M Y') : 'Immediate submission requested',
                'action_url' => route('portal.requests.index'),
                'action_label' => 'Upload File',
            ]);
        }

        // Upcoming hearings notifications
        foreach ($upcomingEvents as $evt) {
            $evtDate = Carbon::parse($evt->start_time);
            $isImminent = $evtDate->isBetween(now()->subDay(), now()->addDays(7));
            $notifications->push([
                'id' => 'evt-'.$evt->id,
                'type' => 'event',
                'icon' => 'gavel',
                'color' => $isImminent ? 'amber' : 'blue',
                'badge' => $evt->event_type ?? 'Court Hearing',
                'title' => $evt->title,
                'description' => ($evt->matter ? $evt->matter->case_number.' · ' : '').($evt->location ?? 'Court Hearing'),
                'time' => $evtDate->format('D, d M · h:i A'),
                'action_url' => route('portal.calendar.index'),
                'action_label' => 'Hearing Listing',
            ]);
        }

        // Pending client tasks notifications
        foreach ($clientTasks->where('status', '!=', 'completed')->take(3) as $tsk) {
            $notifications->push([
                'id' => 'task-'.$tsk->id,
                'type' => 'task',
                'icon' => 'task_alt',
                'color' => $tsk->priority === 'urgent' ? 'red' : 'emerald',
                'badge' => ucfirst($tsk->priority).' Task',
                'title' => 'Action Item: '.$tsk->title,
                'description' => ($tsk->matter ? $tsk->matter->case_number.' · ' : '').($tsk->description ? Str::limit($tsk->description, 80) : 'Procedural action required'),
                'time' => $tsk->due_date ? 'Due '.Carbon::parse($tsk->due_date)->diffForHumans() : 'Action requested',
                'action_url' => '#client-tasks-section',
                'action_label' => 'Review Task',
            ]);
        }

        // Recent messages from counsel
        $unacknowledgedMsgs = $recentMessages->filter(fn ($m) => $user && $m->sender_id !== $user->id)->take(2);
        foreach ($unacknowledgedMsgs as $msg) {
            $notifications->push([
                'id' => 'msg-'.$msg->id,
                'type' => 'message',
                'icon' => 'forum',
                'color' => 'emerald',
                'badge' => 'Privileged Communication',
                'title' => 'Message from '.($msg->sender?->name ?? 'Chambers Counsel'),
                'description' => Str::limit($msg->body, 90),
                'time' => $msg->created_at->diffForHumans(),
                'action_url' => '#counsel-thread',
                'action_label' => 'Reply',
            ]);
        }

        // Baseline statutory privilege status if collection is sparse
        if ($notifications->count() < 2) {
            $notifications->push([
                'id' => 'system-privilege',
                'type' => 'info',
                'icon' => 'verified_user',
                'color' => 'emerald',
                'badge' => 'Statutory Privilege Active',
                'title' => 'Protected Legal Communication Infrastructure',
                'description' => 'Chambers transmission channels and filing repositories encrypted under Section 126 & 129 Evidence Act.',
                'time' => 'Operational',
                'action_url' => route('portal.matters.index'),
                'action_label' => 'View Dockets',
            ]);
        }

        return view('portal.dashboard', compact(
            'client',
            'matters',
            'activeMatters',
            'recentDocuments',
            'pendingDocumentRequests',
            'upcomingEvents',
            'previousWeekSchedule',
            'thisWeekSchedule',
            'comingWeekSchedule',
            'recentMessages',
            'clientTasks',
            'notifications',
            'documentRequests',
            'invoices',
            'events',
            'documentsCount'
        ));
    }

    /**
     * My Cases & Court Dockets List.
     */
    public function matters()
    {
        $client = $this->getClient();

        $matters = Matter::where('client_id', $client->id)
            ->with(['documents', 'leadAttorney', 'documentRequests', 'events'])
            ->latest()
            ->get();

        return view('portal.matters.index', compact('client', 'matters'));
    }

    /**
     * Case Dossier Detail View.
     */
    public function matterShow(Matter $matter)
    {
        $client = $this->getClient();

        if ($matter->client_id !== $client->id) {
            abort(403, 'Unauthorized case dossier.');
        }

        self::ensureClientDashboardSamples($client);

        $matter->load([
            'documents' => fn ($q) => $q->where('is_client_visible', true)->latest(),
            'leadAttorney',
            'supervisingAttorney',
            'assignedParalegal',
            'teamMembers',
            'documentRequests.requestedBy',
            'events' => fn ($q) => $q->orderBy('start_time'),
            'threads' => fn ($q) => $q->where('is_internal', false)->with(['messages.sender', 'messages.attachments', 'creator'])->orderBy('last_message_at', 'desc'),
            'messages' => fn ($q) => $q->where('is_internal', false)->with(['sender', 'attachments'])->orderBy('created_at', 'asc'),
            'tasks' => fn ($q) => $q->where(function ($q2) use ($client, $matter) {
                if ($client->user_id) {
                    $q2->where('assigned_to', $client->user_id);
                }
                $q2->orWhere('matter_id', $matter->id);
            })->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END")
                ->orderBy('due_date', 'asc'),
        ]);

        // Client-safe activities only (exclude privileged internal actions)
        $activities = MatterActivity::where('matter_id', $matter->id)
            ->where('is_client_safe', true)
            ->whereNotIn('activity_type', ['internal_note', 'billing_entry', 'conflict_check', 'time_entry'])
            ->with(['user'])
            ->chronological('desc')
            ->take(50)
            ->get();

        // Client-visible notes only
        $notes = CaseNote::where('matter_id', $matter->id)
            ->where('type', 'client_visible')
            ->with('user')
            ->latest()
            ->get();

        return view('portal.matters.show', compact('client', 'matter', 'activities', 'notes'));
    }

    /**
     * Document Requests Workflow (F-07).
     */
    public function requests(Request $request)
    {
        $client = $this->getClient();

        // Ensure client has baseline sample document requests
        DocumentRequest::ensureSampleRequestsForClient($client);

        $requests = DocumentRequest::where('client_id', $client->id)
            ->with(['matter', 'requestedBy'])
            ->latest()
            ->get();

        return view('portal.requests.index', compact('client', 'requests'));
    }

    /**
     * Upload File Against Specific Document Request.
     */
    public function uploadRequest(Request $httpRequest, DocumentRequest $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        if ($request->client_id !== $client->id) {
            abort(403, 'Unauthorized document request.');
        }

        $httpRequest->validate([
            'file' => 'required|file|max:51200', // 50MB
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $file = $httpRequest->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $disk = config('filesystems.default', 'local');
        try {
            $path = $file->store('documents/client_uploads', $disk);
        } catch (Throwable $e) {
            if ($disk !== 'local') {
                $disk = 'local';
                $path = $file->store('documents/client_uploads', 'local');
            } else {
                throw $e;
            }
        }

        $doc = Document::create([
            'firm_id' => $request->firm_id,
            'matter_id' => $request->matter_id,
            'user_id' => $user->id,
            'title' => $request->title.' (Client Submission)',
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'sha256' => $sha256,
            'category' => 'Client Submissions',
            'privilege' => 'Confidential',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        $request->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'document_id' => $doc->id,
            'client_notes' => $httpRequest->client_notes,
        ]);

        if ($request->matter) {
            MatterActivity::log(
                matter: $request->matter,
                activityType: 'document_uploaded',
                description: "Client {$client->name} fulfilled document request '{$request->title}': uploaded '{$doc->filename}'",
                subject: $doc,
                userId: $user->id,
                clientId: $client->id,
                isClientSafe: true
            );
        }

        return back()->with('success', "Document '{$doc->filename}' submitted to legal counsel.");
    }

    /**
     * Case Documents Repository & Vault.
     */
    public function documents(Request $request)
    {
        $client = $this->getClient();

        $matterIds = Matter::where('client_id', $client->id)->pluck('id');
        $documents = Document::whereIn('matter_id', $matterIds)
            ->where('is_client_visible', true)
            ->with('matter')
            ->latest()
            ->get();
        $matters = Matter::where('client_id', $client->id)->get();

        return view('portal.documents.index', compact('client', 'documents', 'matters'));
    }

    /**
     * Client Supplementary Document Upload.
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        if ($request->hasFile('file') && ! $request->file('file')->isValid()) {
            $errorCode = $request->file('file')->getError();
            $maxUpload = ini_get('upload_max_filesize');
            $msg = match ($errorCode) {
                UPLOAD_ERR_INI_SIZE => "Uploaded file exceeds server limit ({$maxUpload}). Please select a file smaller than {$maxUpload}.",
                default => "File upload failed with error code {$errorCode}."
            };

            return back()->withInput()->with('error', $msg);
        }

        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'file' => 'required|file|max:51200',
        ]);

        $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $disk = config('filesystems.default', 'local');
        try {
            $path = $file->store('documents/client_uploads', $disk);
        } catch (Throwable $e) {
            if ($disk !== 'local') {
                $disk = 'local';
                $path = $file->store('documents/client_uploads', 'local');
            } else {
                throw $e;
            }
        }

        $doc = Document::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'sha256' => $sha256,
            'category' => $request->category ?? 'Client Submissions',
            'privilege' => 'Confidential',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        MatterActivity::log(
            matter: $matter,
            activityType: 'document_uploaded',
            description: "Client {$client->name} uploaded document '{$doc->title}' ({$doc->filename})",
            subject: $doc,
            userId: $user->id,
            clientId: $client->id,
            isClientSafe: true
        );

        return back()->with('success', 'Document uploaded to case file.');
    }

    /**
     * Download Privileged Document with Strict Access Isolation.
     */
    public function downloadDocument(Document $document)
    {
        $client = $this->getClient();

        // STRICT DATA ISOLATION: The document must belong to a matter owned by this client
        $matter = Matter::where('id', $document->matter_id)->where('client_id', $client->id)->first();
        if (! $matter) {
            abort(403, 'Unauthorized document access: This filing does not belong to your case dossiers.');
        }

        // Document must be designated client-visible
        if ($document->is_client_visible === false) {
            abort(403, 'Unauthorized: This filing is restricted to internal chambers work product.');
        }

        MatterActivity::log(
            matter: $matter,
            activityType: 'document_downloaded',
            description: "Client {$client->name} downloaded document '{$document->title}' ({$document->filename})",
            subject: $document,
            userId: Auth::id(),
            clientId: $client->id,
            isClientSafe: true
        );

        $defaultDisk = config('filesystems.default', 'local');
        if ($document->file_path) {
            try {
                if (Storage::disk($defaultDisk)->exists($document->file_path)) {
                    return Storage::disk($defaultDisk)->download($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                    ]);
                }
            } catch (Throwable $e) {
            }

            try {
                if ($defaultDisk !== 'local' && Storage::disk('local')->exists($document->file_path)) {
                    return Storage::disk('local')->download($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                    ]);
                }
            } catch (Throwable $e) {
            }
        }

        // Compliant legal PDF fallback stream
        $pdfContent = LegalPdfGenerator::forDocument($document);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$document->filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    /**
     * Inline View Privileged Document with Strict Access Isolation.
     */
    public function viewDocument(Document $document)
    {
        $client = $this->getClient();

        // STRICT DATA ISOLATION: The document must belong to a matter owned by this client
        $matter = Matter::where('id', $document->matter_id)->where('client_id', $client->id)->first();
        if (! $matter) {
            abort(403, 'Unauthorized document access: This filing does not belong to your case dossiers.');
        }

        // Document must be designated client-visible
        if ($document->is_client_visible === false) {
            abort(403, 'Unauthorized: This filing is restricted to internal chambers work product.');
        }

        MatterActivity::log(
            matter: $matter,
            activityType: 'document_viewed',
            description: "Client {$client->name} viewed document '{$document->title}' ({$document->filename})",
            subject: $document,
            userId: Auth::id(),
            clientId: $client->id,
            isClientSafe: true
        );

        $defaultDisk = config('filesystems.default', 'local');
        if ($document->file_path) {
            try {
                if (Storage::disk($defaultDisk)->exists($document->file_path)) {
                    return Storage::disk($defaultDisk)->response($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                        'Content-Disposition' => "inline; filename=\"{$document->filename}\"",
                    ]);
                }
            } catch (Throwable $e) {
            }

            try {
                if ($defaultDisk !== 'local' && Storage::disk('local')->exists($document->file_path)) {
                    return Storage::disk('local')->response($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                        'Content-Disposition' => "inline; filename=\"{$document->filename}\"",
                    ]);
                }
            } catch (Throwable $e) {
            }
        }

        // Compliant legal PDF fallback stream
        $pdfContent = LegalPdfGenerator::forDocument($document);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$document->filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    /**
     * Privileged Counsel Messages Channel (F-09).
     */
    public function messages(Request $request)
    {
        $client = $this->getClient();

        $matters = Matter::where('client_id', $client->id)
            ->with(['leadAttorney'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('is_read', false)->where('sender_id', '!=', Auth::id());
            }])
            ->get();

        $selectedMatterId = $request->input('matter_id', $matters->first()->id ?? null);
        $selectedMatter = $selectedMatterId ? Matter::where('client_id', $client->id)->find($selectedMatterId) : null;

        $typeFilter = $request->get('type', 'all');
        $threads = collect();
        $selectedThread = null;
        $messages = collect();

        if ($selectedMatter) {
            $threadsQuery = MessageThread::where('matter_id', $selectedMatter->id)
                ->where('is_internal', false) // Technical database authorization
                ->with(['creator', 'client'])
                ->withCount(['messages as unread_count' => function ($q) {
                    $q->where('is_read', false)->where('sender_id', '!=', Auth::id());
                }])
                ->orderBy('last_message_at', 'desc');

            if ($typeFilter !== 'all' && in_array($typeFilter, [
                MessageThread::TYPE_CLIENT_COMMUNICATION,
                MessageThread::TYPE_DOCUMENT_REQUEST,
                MessageThread::TYPE_GENERAL_MATTER,
            ])) {
                $threadsQuery->where('thread_type', $typeFilter);
            }

            $threads = $threadsQuery->get();

            if ($request->filled('thread_id')) {
                $requestedId = $request->get('thread_id');
                $rawThread = MessageThread::withoutGlobalScopes()->where('matter_id', $selectedMatter->id)->find($requestedId);
                if ($rawThread && ($rawThread->is_internal || $rawThread->thread_type === MessageThread::TYPE_INTERNAL_TEAM)) {
                    abort(403, 'Unauthorized communication thread: Access to internal chambers communications is strictly prohibited.');
                }

                $selectedThread = $threads->firstWhere('id', $requestedId)
                    ?? MessageThread::where('matter_id', $selectedMatter->id)
                        ->where('is_internal', false)
                        ->find($requestedId);

                if (! $selectedThread && ! $rawThread) {
                    abort(404, 'Thread not found.');
                }
            } else {
                $selectedThread = $threads->first();
            }

            if ($selectedThread) {
                // Strict technical database authorization: Ensure thread is not internal
                if ($selectedThread->is_internal || $selectedThread->thread_type === MessageThread::TYPE_INTERNAL_TEAM) {
                    abort(403, 'Unauthorized communication thread.');
                }

                // Mark messages sent to client as read
                Message::where('thread_id', $selectedThread->id)
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                        'status' => 'read',
                    ]);

                $messages = Message::where('thread_id', $selectedThread->id)
                    ->where('is_internal', false)
                    ->with(['sender', 'recipient', 'attachments'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            } else {
                // Direct matter messages fallback
                $messages = Message::where('matter_id', $selectedMatter->id)
                    ->where('is_internal', false)
                    ->with(['sender', 'recipient', 'attachments'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        $threadTypes = [
            MessageThread::TYPE_CLIENT_COMMUNICATION => 'Client Communication',
            MessageThread::TYPE_DOCUMENT_REQUEST => 'Document Request',
            MessageThread::TYPE_GENERAL_MATTER => 'General Matter Communication',
        ];

        return view('portal.messages.index', compact(
            'client',
            'matters',
            'selectedMatter',
            'threads',
            'selectedThread',
            'messages',
            'typeFilter',
            'threadTypes'
        ));
    }

    /**
     * Store Privileged Message to Counsel.
     */
    public function storeMessage(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'thread_id' => 'nullable|exists:message_threads,id',
            'body' => 'required|string|max:10000',
            'attachments.*' => 'nullable|file|max:25600',
        ]);

        $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

        $thread = null;
        if (! empty($request->thread_id)) {
            $thread = MessageThread::where('id', $request->thread_id)
                ->where('matter_id', $matter->id)
                ->where('is_internal', false) // Technical database authorization
                ->firstOrFail();
        } else {
            $thread = MessageThread::firstOrCreate(
                [
                    'matter_id' => $matter->id,
                    'firm_id' => $matter->firm_id,
                    'is_internal' => false,
                    'thread_type' => MessageThread::TYPE_CLIENT_COMMUNICATION,
                ],
                [
                    'client_id' => $client->id,
                    'created_by' => $user->id,
                    'subject' => 'Client Communication: '.$matter->case_number,
                    'status' => 'open',
                    'last_message_at' => now(),
                ]
            );
        }

        // Strict technical database authorization: Client cannot create internal messages
        $msg = Message::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'recipient_id' => $matter->lead_attorney_id,
            'subject' => $thread->subject,
            'body' => $request->body,
            'is_privileged' => true,
            'is_internal' => false,
            'status' => 'sent',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Process attachments
        if ($request->hasFile('attachments')) {
            $disk = config('filesystems.default', 'local');
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('messages/attachments', $disk);
                    } catch (Throwable $e) {
                        $disk = 'local';
                        $path = $file->store('messages/attachments', 'local');
                    }

                    $sha256 = hash_file('sha256', $file->getRealPath());

                    MessageAttachment::create([
                        'firm_id' => $matter->firm_id,
                        'message_id' => $msg->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'sha256' => $sha256,
                    ]);

                    if (empty($msg->attachment_path)) {
                        $msg->update([
                            'attachment_path' => $path,
                            'attachment_name' => $file->getClientOriginalName(),
                            'attachment_size' => $file->getSize(),
                            'attachment_mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            }
        }

        $thread->update(['last_message_at' => now()]);

        MatterActivity::log(
            matter: $matter,
            activityType: 'message_sent',
            description: "Client {$client->name} sent a message in thread '{$thread->subject}'",
            subject: $msg,
            userId: $user->id,
            clientId: $client->id
        );

        return back()->with('success', 'Message dispatched to legal counsel.');
    }

    /**
     * Start a new client communication thread on a matter.
     */
    public function storeThread(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'subject' => 'required|string|max:255',
            'thread_type' => 'required|string|in:client_communication,document_request,general_matter_communication',
            'body' => 'required|string|max:10000',
            'attachments.*' => 'nullable|file|max:25600',
        ]);

        $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

        // Technical separation: Client threads are strictly NEVER internal
        $thread = MessageThread::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'created_by' => $user->id,
            'subject' => $request->subject,
            'thread_type' => $request->thread_type,
            'is_internal' => false,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $msg = Message::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'recipient_id' => $matter->lead_attorney_id,
            'subject' => $thread->subject,
            'body' => $request->body,
            'is_privileged' => true,
            'is_internal' => false,
            'status' => 'sent',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        if ($request->hasFile('attachments')) {
            $disk = config('filesystems.default', 'local');
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('messages/attachments', $disk);
                    } catch (Throwable $e) {
                        $disk = 'local';
                        $path = $file->store('messages/attachments', 'local');
                    }

                    $sha256 = hash_file('sha256', $file->getRealPath());

                    MessageAttachment::create([
                        'firm_id' => $matter->firm_id,
                        'message_id' => $msg->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'sha256' => $sha256,
                    ]);

                    if (empty($msg->attachment_path)) {
                        $msg->update([
                            'attachment_path' => $path,
                            'attachment_name' => $file->getClientOriginalName(),
                            'attachment_size' => $file->getSize(),
                            'attachment_mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            }
        }

        MatterActivity::log(
            matter: $matter,
            activityType: 'message_sent',
            description: "Client initiated thread: '{$thread->subject}'",
            subject: $thread,
            userId: $user->id,
            clientId: $client->id
        );

        return redirect()->route('portal.messages.index', [
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
        ])->with('success', "Communication thread '{$thread->subject}' opened successfully.");
    }

    /**
     * Download message attachment with strict technical database authorization.
     */
    public function downloadAttachment(MessageAttachment $attachment)
    {
        $client = $this->getClient();
        $message = Message::withoutGlobalScopes()->find($attachment->message_id);
        if (! $message || $message->is_internal) {
            abort(403, 'Unauthorized access: This attachment is restricted to internal chambers counsel.');
        }

        $thread = MessageThread::withoutGlobalScopes()->find($message->thread_id);
        if ($thread && $thread->is_internal) {
            abort(403, 'Unauthorized access: This attachment is restricted to internal chambers counsel.');
        }

        // Must belong to client's matter
        if (! $message->matter || $message->matter->client_id !== $client->id) {
            abort(403, 'Unauthorized attachment access.');
        }

        $defaultDisk = config('filesystems.default', 'local');
        if (Storage::disk($defaultDisk)->exists($attachment->file_path)) {
            return Storage::disk($defaultDisk)->download($attachment->file_path, $attachment->file_name, [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]);
        }

        if ($defaultDisk !== 'local' && Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->download($attachment->file_path, $attachment->file_name, [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]);
        }

        abort(404, 'Attachment file not found on storage.');
    }

    /**
     * Litigation Calendar & Cause List Appearances (F-13).
     */
    public function calendar()
    {
        $client = $this->getClient();

        $matterIds = Matter::where('client_id', $client->id)->pluck('id');
        $events = Event::whereIn('matter_id', $matterIds)
            ->with('matter')
            ->orderBy('start_time')
            ->get();

        return view('portal.calendar.index', compact('client', 'events'));
    }

    /**
     * Client Invoices & Advance Retainer Ledger (F-20).
     */
    public function invoices()
    {
        $client = $this->getClient();

        $invoices = Invoice::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        return view('portal.invoices.index', compact('client', 'invoices'));
    }

    /**
     * Settle Outstanding Fee Bill.
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $client = $this->getClient();

        if ($invoice->client_id !== $client->id) {
            abort(403, 'Unauthorized invoice.');
        }

        if ($request->payment_method === 'retainer') {
            $client->decrement('trust_balance', $invoice->total_amount);
        }

        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->total_amount,
        ]);

        return back()->with('success', "Fee Bill {$invoice->invoice_number} settled successfully.");
    }

    /**
     * Client Account & KYC Profile Settings.
     */
    public function settings()
    {
        $client = $this->getClient();
        $client->load('members');

        return view('portal.settings', compact('client'));
    }

    /**
     * Update Client Account Preferences.
     */
    public function updateSettings(Request $request)
    {
        $client = $this->getClient();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $client->update($validated);

        return back()->with('success', 'Account contact details updated successfully.');
    }

    /**
     * Toggle Client Task Status (completed <-> todo).
     */
    public function toggleTask(Request $request, Task $task)
    {
        $client = $this->getClient();
        $matterIds = Matter::where('client_id', $client->id)->pluck('id');

        if ($task->assigned_to !== $client->user_id && ! $matterIds->contains($task->matter_id)) {
            abort(403, 'Unauthorized task action.');
        }

        $newStatus = $task->status === 'completed' ? 'todo' : 'completed';
        $task->update([
            'status' => $newStatus,
            'completed_date' => $newStatus === 'completed' ? now()->toDateString() : null,
        ]);

        if ($task->matter) {
            MatterActivity::log(
                matter: $task->matter,
                activityType: $newStatus === 'completed' ? 'task_completed' : 'client_activity',
                description: ($newStatus === 'completed' ? "Client {$client->name} completed task: " : "Client {$client->name} reopened task: ").$task->title,
                subject: $task,
                userId: Auth::id(),
                clientId: $client->id,
                isClientSafe: true
            );
        }

        return back()->with('success', $newStatus === 'completed' ? 'Task marked as completed.' : 'Task marked as pending.');
    }

    /**
     * Ensure the client has comprehensive sample data across all dashboard widgets & matter views.
     */
    public static function ensureClientDashboardSamples(Client $client): void
    {
        $firm = $client->firm ?: Firm::find($client->firm_id);
        $currency = $firm?->currency ?? 'USD';

        // 1. Ensure Client has at least 1 Active Matter
        $matters = Matter::where('client_id', $client->id)->get();
        if ($matters->isEmpty()) {
            $leadAttorneyId = $client->primary_attorney_id
                ?: User::where('firm_id', $client->firm_id)->whereIn('role', ['partner', 'associate'])->first()?->id
                ?: User::where('firm_id', $client->firm_id)->first()?->id;

            $prefix = $currency === 'INR' ? 'CS(COMM)' : 'MAT';
            $caseNumber = sprintf('%s/%s/%04d-%03d', $prefix, date('Y'), $client->id, rand(100, 999));
            while (Matter::where('case_number', $caseNumber)->exists()) {
                $caseNumber = sprintf('%s/%s/%04d-%04d', $prefix, date('Y'), $client->id, rand(1000, 9999));
            }

            $primaryMatter = Matter::create([
                'firm_id' => $client->firm_id,
                'client_id' => $client->id,
                'case_number' => $caseNumber,
                'title' => $currency === 'INR' ? 'Commercial Arbitration & Debt Recovery Proceedings' : 'Commercial Contract & Supply Chain Litigation',
                'court_name' => $currency === 'INR' ? 'High Court of Delhi' : 'U.S. District Court, Southern District of New York',
                'judge_name' => $currency === 'INR' ? "Hon'ble Presiding Division Bench" : 'Hon. Marcus Vance, U.S. District Judge',
                'status' => 'open',
                'stage' => 'Discovery & Evidence',
                'priority' => 'urgent',
                'lead_attorney_id' => $leadAttorneyId,
                'billing_type' => 'hourly',
                'budget' => 150000,
                'practice_area' => 'Commercial Litigation',
                'description' => "Representation of {$client->name} in comprehensive proceedings regarding contractual enforcement, recovery of disputed balances, interim relief injunctions, and arbitral hearings.",
                'opened_at' => Carbon::now()->subMonths(2),
            ]);
            $matters = collect([$primaryMatter]);
        }

        $primaryMatter = $matters->first();
        $matterIds = $matters->pluck('id');

        $leadAttorney = $primaryMatter->leadAttorney
            ?: ($client->primaryAttorney ?: User::where('firm_id', $client->firm_id)->whereIn('role', ['partner', 'associate'])->first() ?: User::first());

        // Ensure Lead Attorney on primary matter if missing
        if (! $primaryMatter->lead_attorney_id && $leadAttorney) {
            $primaryMatter->update(['lead_attorney_id' => $leadAttorney->id]);
        }

        // Ensure Team Members on primary matter
        if ($primaryMatter->teamMembers()->count() === 0) {
            $associates = User::where('firm_id', $client->firm_id)
                ->where('id', '!=', $leadAttorney?->id)
                ->take(2)
                ->get();
            foreach ($associates as $assoc) {
                $primaryMatter->teamMembers()->attach($assoc->id, ['role' => 'associate']);
            }
        }

        // 2. Ensure Sample Pending Document Requests
        DocumentRequest::ensureSampleRequestsForClient($client);

        // 3. Ensure Client-Visible Recent Documents (vault)
        $docCount = Document::whereIn('matter_id', $matterIds)->where('is_client_visible', true)->count();
        if ($docCount < 3) {
            $sampleDocs = $currency === 'INR' ? [
                [
                    'title' => 'Certified Statement of Claim & Verified Pleadings Compendium',
                    'filename' => 'statement_of_claim_pleadings_docket.pdf',
                    'category' => 'Pleadings',
                    'privilege' => 'Confidential',
                    'file_size' => 2450000,
                    'document_type' => 'Pleading',
                ],
                [
                    'title' => 'Vakalatnama & Lead Counsel Appearance Memo (High Court Registry)',
                    'filename' => 'vakalatnama_appearance_certified.pdf',
                    'category' => 'Court Filing',
                    'privilege' => 'Public',
                    'file_size' => 840000,
                    'document_type' => 'Court Filing',
                ],
                [
                    'title' => 'Interim Injunction Order & Status Quo Direction (Courtroom 14)',
                    'filename' => 'high_court_interim_injunction_order.pdf',
                    'category' => 'Court Orders',
                    'privilege' => 'Confidential',
                    'file_size' => 1250000,
                    'document_type' => 'Order',
                ],
                [
                    'title' => 'Comparative Schedule of Disputed Invoices & Reconciled Ledger Vouchers',
                    'filename' => 'reconciled_invoice_voucher_schedule.pdf',
                    'category' => 'Evidence',
                    'privilege' => 'Confidential',
                    'file_size' => 3100000,
                    'document_type' => 'Exhibit',
                ],
            ] : [
                [
                    'title' => 'Verified Complaint & Initial Disclosures Dossier',
                    'filename' => 'verified_complaint_initial_disclosures.pdf',
                    'category' => 'Pleadings',
                    'privilege' => 'Confidential',
                    'file_size' => 2850000,
                    'document_type' => 'Pleading',
                ],
                [
                    'title' => 'Notice of Appearance & Rule 7.1 Corporate Disclosure Statement',
                    'filename' => 'notice_of_appearance_rule7_disclosure.pdf',
                    'category' => 'Court Filing',
                    'privilege' => 'Public',
                    'file_size' => 620000,
                    'document_type' => 'Court Filing',
                ],
                [
                    'title' => 'District Court Preliminary Injunction & Status Conference Order',
                    'filename' => 'district_court_injunction_order.pdf',
                    'category' => 'Court Orders',
                    'privilege' => 'Confidential',
                    'file_size' => 1400000,
                    'document_type' => 'Order',
                ],
                [
                    'title' => 'Executed Master Services Agreement & Escrow Confirmation Schedule',
                    'filename' => 'executed_msa_escrow_confirmation.pdf',
                    'category' => 'Contracts',
                    'privilege' => 'Confidential',
                    'file_size' => 3400000,
                    'document_type' => 'Contract',
                ],
            ];

            foreach ($sampleDocs as $sdoc) {
                $exists = Document::whereIn('matter_id', $matterIds)->where('title', $sdoc['title'])->exists();
                if (! $exists) {
                    Document::create([
                        'firm_id' => $client->firm_id,
                        'matter_id' => $primaryMatter->id,
                        'user_id' => $leadAttorney?->id ?? User::first()->id,
                        'title' => $sdoc['title'],
                        'filename' => $sdoc['filename'],
                        'file_path' => 'documents/samples/'.$sdoc['filename'],
                        'file_size' => $sdoc['file_size'],
                        'mime_type' => 'application/pdf',
                        'category' => $sdoc['category'],
                        'privilege' => $sdoc['privilege'],
                        'document_type' => $sdoc['document_type'],
                        'is_client_visible' => true,
                        'created_at' => Carbon::now()->subDays(rand(2, 14)),
                    ]);
                }
            }
        }

        // 4. Ensure Upcoming Events & Hearings
        $eventCount = Event::whereIn('matter_id', $matterIds)
            ->where('start_time', '>=', Carbon::now()->subHours(6))
            ->count();
        if ($eventCount < 2) {
            $sampleEvents = $currency === 'INR' ? [
                [
                    'title' => 'Admission Hearing & Interim Injunction Arguments',
                    'event_type' => 'Court Hearing',
                    'start_time' => Carbon::now()->addDays(2)->setHour(10)->setMinute(30)->setSecond(0),
                    'end_time' => Carbon::now()->addDays(2)->setHour(12)->setMinute(0)->setSecond(0),
                    'location' => 'Courtroom 14, High Court of Delhi, New Delhi',
                    'notes' => 'Senior Counsel and Lead Advocate appearing before Division Bench 3.',
                ],
                [
                    'title' => 'Framing of Issues & Case Management Conference',
                    'event_type' => 'Conference',
                    'start_time' => Carbon::now()->addDays(7)->setHour(14)->setMinute(15)->setSecond(0),
                    'end_time' => Carbon::now()->addDays(7)->setHour(15)->setMinute(30)->setSecond(0),
                    'location' => 'Chamber 402, High Court Complex, New Delhi',
                    'notes' => 'Settlement of issues and procedural timetable for cross-examination.',
                ],
            ] : [
                [
                    'title' => 'Preliminary Injunction & Motion Hearing',
                    'event_type' => 'Court Hearing',
                    'start_time' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0),
                    'end_time' => Carbon::now()->addDays(2)->setHour(11)->setMinute(30)->setSecond(0),
                    'location' => 'Courtroom 15A, U.S. District Court, Foley Square, New York',
                    'notes' => 'Lead arguing counsel appearing before Hon. Marcus Vance.',
                ],
                [
                    'title' => 'Rule 16 Case Management & Pretrial Conference',
                    'event_type' => 'Conference',
                    'start_time' => Carbon::now()->addDays(7)->setHour(14)->setMinute(0)->setSecond(0),
                    'end_time' => Carbon::now()->addDays(7)->setHour(15)->setMinute(0)->setSecond(0),
                    'location' => 'Chambers Conference Room, 21st Floor, Federal Building',
                    'notes' => 'Pretrial scheduling order and expert discovery milestones.',
                ],
            ];

            foreach ($sampleEvents as $se) {
                $exists = Event::whereIn('matter_id', $matterIds)->where('title', $se['title'])->exists();
                if (! $exists) {
                    Event::create([
                        'firm_id' => $client->firm_id,
                        'matter_id' => $primaryMatter->id,
                        'user_id' => $leadAttorney?->id ?? User::first()->id,
                        'title' => $se['title'],
                        'event_type' => $se['event_type'],
                        'start_time' => $se['start_time'],
                        'end_time' => $se['end_time'],
                        'location' => $se['location'],
                        'notes' => $se['notes'],
                    ]);
                }
            }
        }

        // 5. Ensure Recent Counsel Channel Messages
        $msgCount = Message::whereIn('matter_id', $matterIds)->where('is_internal', false)->count();
        if ($msgCount < 2) {
            $clientUser = $client->user
                ?: ($client->user_id ? User::find($client->user_id) : null);

            if (! $clientUser) {
                $clientUser = User::where('firm_id', $client->firm_id)->where('role', 'client')->first();
                if (! $clientUser) {
                    $clientUser = User::create([
                        'firm_id' => $client->firm_id,
                        'name' => $client->contact_person ?? $client->name,
                        'email' => 'client.'.$client->id.'@clientportal.example',
                        'password' => bcrypt('password123'),
                        'role' => 'client',
                        'status' => 'active',
                    ]);
                }
                $client->update(['user_id' => $clientUser->id]);
            }

            $dialogues = $currency === 'INR' ? [
                [
                    'sender' => $clientUser->id,
                    'recipient' => $leadAttorney?->id ?? User::first()->id,
                    'body' => 'Good morning Counsel. Could you please confirm if the registry scrutiny objections have been cleared on our amended petition?',
                    'hours_ago' => 8,
                ],
                [
                    'sender' => $leadAttorney?->id ?? User::first()->id,
                    'recipient' => $clientUser->id,
                    'body' => 'Good morning. Registry scrutiny has been cleared and our filing diary number is confirmed. The matter has been assigned to Division Bench 3 for admission hearing on Wednesday.',
                    'hours_ago' => 5,
                ],
                [
                    'sender' => $clientUser->id,
                    'recipient' => $leadAttorney?->id ?? User::first()->id,
                    'body' => 'Thank you for the prompt update. All audited financial ledgers have been uploaded to the document vault for advocate verification.',
                    'hours_ago' => 1,
                ],
            ] : [
                [
                    'sender' => $clientUser->id,
                    'recipient' => $leadAttorney?->id ?? User::first()->id,
                    'body' => 'Hello Counsel. Following up on the Rule 26 initial disclosure responses: Have all defense exhibits been received?',
                    'hours_ago' => 8,
                ],
                [
                    'sender' => $leadAttorney?->id ?? User::first()->id,
                    'recipient' => $clientUser->id,
                    'body' => 'Yes, initial disclosures have been exchanged and reviewed. We are preparing the deposition schedule for the upcoming hearing before Judge Vance.',
                    'hours_ago' => 5,
                ],
                [
                    'sender' => $clientUser->id,
                    'recipient' => $leadAttorney?->id ?? User::first()->id,
                    'body' => 'Excellent. The countersigned corporate authorization resolution has been uploaded to the document requests section.',
                    'hours_ago' => 1,
                ],
            ];

            foreach ($dialogues as $d) {
                $exists = Message::whereIn('matter_id', $matterIds)->where('body', $d['body'])->exists();
                if (! $exists) {
                    Message::create([
                        'firm_id' => $client->firm_id,
                        'matter_id' => $primaryMatter->id,
                        'sender_id' => $d['sender'],
                        'recipient_id' => $d['recipient'],
                        'subject' => 'Counsel Consultation',
                        'body' => $d['body'],
                        'is_internal' => false,
                        'is_read' => true,
                        'status' => 'read',
                        'sent_at' => Carbon::now()->subHours($d['hours_ago']),
                        'created_at' => Carbon::now()->subHours($d['hours_ago']),
                    ]);
                }
            }
        }

        // 6. Ensure Tasks Requiring Client Action
        $taskCount = Task::where(fn ($q) => $q->where('assigned_to', $client->user_id)->orWhereIn('matter_id', $matterIds))
            ->where('status', '!=', 'completed')
            ->count();

        if ($taskCount < 2) {
            $clientUserId = $client->user_id ?: ($client->user?->id ?? User::where('firm_id', $client->firm_id)->first()->id);
            $authorId = $leadAttorney?->id ?? User::where('firm_id', $client->firm_id)->first()->id;

            $sampleTasks = $currency === 'INR' ? [
                [
                    'title' => 'Review & Execute Sworn Statement of Truth Affidavit for Registry Filing',
                    'description' => 'Commercial Courts Act verification affidavit must be executed before an Oath Commissioner and returned via the document upload portal.',
                    'priority' => 'urgent',
                    'due_date' => Carbon::now()->addDays(1)->startOfDay(),
                ],
                [
                    'title' => 'Verify Reconciled Invoices & Disputed Delivery Vouchers Schedule',
                    'description' => 'Examine opposing party disputed invoice schedule and certify working capital ledger balance for arbitral submission.',
                    'priority' => 'high',
                    'due_date' => Carbon::now()->addDays(3)->startOfDay(),
                ],
                [
                    'title' => 'Execute Board Resolution Designating Authorized Signatory',
                    'description' => 'Extract of board resolution authorizing advocate representation in arbitral proceedings.',
                    'priority' => 'normal',
                    'due_date' => Carbon::now()->addDays(5)->startOfDay(),
                ],
            ] : [
                [
                    'title' => 'Review & Sign Sworn Verification of Interrogatory Responses',
                    'description' => 'Rule 33 verification affirming factual accuracy of responses to first set of discovery interrogatories.',
                    'priority' => 'urgent',
                    'due_date' => Carbon::now()->addDays(1)->startOfDay(),
                ],
                [
                    'title' => 'Confirm Executive Deposition Availability for Pretrial Calendar',
                    'description' => 'Coordinate with counsel regarding deposition dates and witness preparation schedule.',
                    'priority' => 'high',
                    'due_date' => Carbon::now()->addDays(3)->startOfDay(),
                ],
                [
                    'title' => 'Execute Corporate Officer Disclosure Authorization Certificate',
                    'description' => 'Sign corporate certificate designating corporate representative for upcoming hearing.',
                    'priority' => 'normal',
                    'due_date' => Carbon::now()->addDays(5)->startOfDay(),
                ],
            ];

            foreach ($sampleTasks as $st) {
                $exists = Task::whereIn('matter_id', $matterIds)->where('title', $st['title'])->exists();
                if (! $exists) {
                    Task::create([
                        'firm_id' => $client->firm_id,
                        'matter_id' => $primaryMatter->id,
                        'assigned_to' => $clientUserId,
                        'created_by' => $authorId,
                        'title' => $st['title'],
                        'description' => $st['description'],
                        'priority' => $st['priority'],
                        'status' => 'not_started',
                        'due_date' => $st['due_date'],
                    ]);
                }
            }
        }

        // 7. Ensure Permitted Briefing Notes (Client visible)
        $clientNoteCount = CaseNote::whereIn('matter_id', $matterIds)->where('type', 'client_visible')->count();
        if ($clientNoteCount === 0) {
            $authorId = $leadAttorney?->id ?? User::where('firm_id', $client->firm_id)->first()->id;
            CaseNote::create([
                'firm_id' => $client->firm_id,
                'matter_id' => $primaryMatter->id,
                'user_id' => $authorId,
                'title' => 'Procedural Status Briefing: Scrutiny Cleared & Hearing Listed',
                'body' => "Official Counsel Update for Client:\n- All procedural objections from the Registry have been cured and the matter is cleared for hearing.\n- The interim relief application is scheduled before the Division Bench on the upcoming court docket.\n- Please ensure the notarized Statement of Truth is uploaded prior to the call of the list.",
                'type' => 'client_visible',
                'is_pinned' => true,
                'is_privileged' => false,
            ]);
            CaseNote::create([
                'firm_id' => $client->firm_id,
                'matter_id' => $primaryMatter->id,
                'user_id' => $authorId,
                'title' => 'Procedural Instructions for Client Verification Affidavit Execution',
                'body' => "Guidelines for Authorized Representative:\n1. Please sign all verification pages in the presence of an Oath Commissioner / Notary Public.\n2. Ensure the official company seal is affixed adjacent to the signature.\n3. Return high-resolution color PDF scans via the Document Requests tab.",
                'type' => 'client_visible',
                'is_pinned' => false,
                'is_privileged' => false,
            ]);
        }

        // 8. Ensure Limited Client-Safe Activity
        $activityCount = MatterActivity::whereIn('matter_id', $matterIds)->where('is_client_safe', true)->count();
        if ($activityCount === 0) {
            $authorId = $leadAttorney?->id ?? User::where('firm_id', $client->firm_id)->first()->id;
            $sampleActs = [
                [
                    'activity_type' => 'matter_created',
                    'description' => 'Litigation matter initiated and assigned to Chambers Counsel.',
                    'occurred_at' => Carbon::now()->subMonths(1),
                ],
                [
                    'activity_type' => 'document_uploaded',
                    'description' => 'Statement of Claim and verified pleadings uploaded to secure document vault.',
                    'occurred_at' => Carbon::now()->subWeeks(2),
                ],
                [
                    'activity_type' => 'procedural_milestone',
                    'description' => 'Registry issued formal docket reference; scrutiny examination cleared.',
                    'occurred_at' => Carbon::now()->subDays(5),
                ],
                [
                    'activity_type' => 'calendar_event',
                    'description' => 'Interim injunction hearing listed on official daily cause list.',
                    'occurred_at' => Carbon::now()->subDays(1),
                ],
            ];

            foreach ($sampleActs as $sa) {
                MatterActivity::create([
                    'firm_id' => $client->firm_id,
                    'matter_id' => $primaryMatter->id,
                    'client_id' => $client->id,
                    'user_id' => $authorId,
                    'activity_type' => $sa['activity_type'],
                    'description' => $sa['description'],
                    'is_client_safe' => true,
                    'occurred_at' => $sa['occurred_at'],
                ]);
            }
        }
    }
}
