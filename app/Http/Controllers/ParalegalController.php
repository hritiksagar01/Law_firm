<?php

namespace App\Http\Controllers;

use App\Models\CaseNote;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Message;
use App\Models\Task;
use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParalegalController extends Controller
{
    /**
     * Display the Assigned Matters Dashboard for Paralegals & Legal Assistants / Staff.
     */
    public function dashboard(Request $request): View
    {
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        // Base query for matters assigned to this user
        $mattersQuery = Matter::where('firm_id', $firmId);
        if (! in_array($user->role, ['superadmin', 'partner'])) {
            $mattersQuery->where(function ($q) use ($user) {
                $q->where('lead_attorney_id', $user->id)
                    ->orWhere('assigned_paralegal_id', $user->id)
                    ->orWhereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
            });
        }

        $assignedMatters = (clone $mattersQuery)
            ->with(['client', 'leadAttorney', 'assignedParalegal', 'events' => fn ($e) => $e->where('start_time', '>=', now()->subHours(2))->orderBy('start_time')])
            ->withCount([
                'tasks as open_tasks_count' => fn ($t) => $t->where('status', '!=', 'completed'),
                'tasks as overdue_tasks_count' => fn ($t) => $t->where('status', '!=', 'completed')->where('due_date', '<', now()->toDateString()),
                'documents',
            ])
            ->latest()
            ->get();

        $assignedMatterIds = $assignedMatters->pluck('id');

        // Open & Overdue Tasks
        $tasksQuery = Task::where('firm_id', $firmId)
            ->where('status', '!=', 'completed')
            ->where(function ($q) use ($user, $assignedMatterIds) {
                $q->where('assigned_to', $user->id)
                    ->orWhereIn('matter_id', $assignedMatterIds);
            })
            ->with(['matter.client', 'assignee']);

        $openTasks = (clone $tasksQuery)
            ->orderByRaw('CASE WHEN due_date < ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('due_date', 'asc')
            ->take(20)
            ->get();

        $overdueTasks = $openTasks->filter(function ($t) {
            return $t->due_date && Carbon::parse($t->due_date)->isPast() && ! Carbon::parse($t->due_date)->isToday();
        })->values();

        $dueThisWeekTasks = $openTasks->filter(function ($t) {
            if (! $t->due_date) {
                return false;
            }
            $due = Carbon::parse($t->due_date);

            return $due->isBetween(now()->startOfWeek(), now()->endOfWeek()->addDays(2));
        })->values();

        // Pending Client Documents
        $pendingClientDocs = DocumentRequest::where('firm_id', $firmId)
            ->whereIn('matter_id', $assignedMatterIds)
            ->whereIn('status', ['pending', 'submitted', 'under_review'])
            ->with(['matter', 'client', 'requestedBy', 'document'])
            ->latest()
            ->take(15)
            ->get();

        // Upcoming Deadlines and Hearings
        $upcomingHearingsAndDeadlines = Event::where('firm_id', $firmId)
            ->whereIn('matter_id', $assignedMatterIds)
            ->where('start_time', '>=', now()->subHours(2))
            ->with('matter')
            ->orderBy('start_time', 'asc')
            ->take(12)
            ->get();

        // Recent Documents
        $recentDocuments = Document::where('firm_id', $firmId)
            ->whereIn('matter_id', $assignedMatterIds)
            ->with(['matter', 'uploader'])
            ->latest()
            ->take(10)
            ->get();

        // Unread Messages (where permitted)
        $unreadMessages = Message::where('firm_id', $firmId)
            ->whereIn('matter_id', $assignedMatterIds)
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->with(['matter', 'sender'])
            ->latest()
            ->take(10)
            ->get();

        // Safe Case Notes (Privileged notes strictly excluded for staff who lack access)
        $notesQuery = CaseNote::where('firm_id', $firmId)
            ->whereIn('matter_id', $assignedMatterIds);
        if (! $user->canAccessPrivilegedNotes()) {
            $notesQuery->where('is_privileged', false)
                ->whereNotIn('type', ['privileged', 'attorney_only']);
        }
        $recentNotes = $notesQuery->with(['matter', 'user'])->latest()->take(6)->get();

        // Personal Productivity Todos
        $todos = Todo::where('user_id', $user->id)
            ->orderBy('is_completed')
            ->latest()
            ->take(8)
            ->get();

        // Counts for metric cards
        $metrics = [
            'assigned_matters' => $assignedMatters->count(),
            'open_tasks' => $openTasks->count(),
            'overdue_tasks' => $overdueTasks->count(),
            'pending_docs' => $pendingClientDocs->count(),
            'upcoming_hearings' => $upcomingHearingsAndDeadlines->count(),
            'unread_messages' => $unreadMessages->count(),
        ];

        return view('paralegal.dashboard', compact(
            'assignedMatters',
            'openTasks',
            'overdueTasks',
            'dueThisWeekTasks',
            'pendingClientDocs',
            'upcomingHearingsAndDeadlines',
            'recentDocuments',
            'unreadMessages',
            'recentNotes',
            'todos',
            'metrics'
        ));
    }

    /**
     * Update permitted administrative matter fields (Court, case number, dates, stage, priority, docket number).
     */
    public function updateMatterAdministrative(Request $request, Matter $matter): RedirectResponse
    {
        $user = Auth::user();
        if ($matter->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized case dossier.');
        }

        // Verify assignment for non-partners
        if (! in_array($user->role, ['superadmin', 'partner'])) {
            $isAssigned = ($matter->lead_attorney_id === $user->id) ||
                ($matter->assigned_paralegal_id === $user->id) ||
                $matter->users()->where('users.id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized: You are not assigned to this case dossier.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_title' => 'nullable|string|max:100',
            'case_number' => 'nullable|string|max:100',
            'docket_number' => 'nullable|string|max:100',
            'court_name' => 'nullable|string|max:255',
            'court_type' => 'nullable|string|max:100',
            'jurisdiction' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'judge_name' => 'nullable|string|max:255',
            'stage' => 'required|string|max:100',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'filing_date' => 'nullable|date',
            'hearing_date' => 'nullable|date',
            'trial_date' => 'nullable|date',
            'statute_references' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $matter->update([
            'title' => $validated['title'],
            'short_title' => $validated['short_title'] ?? $matter->short_title,
            'case_number' => ! empty($validated['case_number']) ? $validated['case_number'] : $matter->case_number,
            'docket_number' => $validated['docket_number'] ?? null,
            'court_name' => $validated['court_name'] ?? null,
            'court_type' => $validated['court_type'] ?? null,
            'jurisdiction' => $validated['jurisdiction'] ?? null,
            'county' => $validated['county'] ?? null,
            'state' => $validated['state'] ?? null,
            'judge_name' => $validated['judge_name'] ?? null,
            'stage' => $validated['stage'],
            'priority' => $validated['priority'] ?? $matter->priority,
            'filing_date' => $validated['filing_date'] ?? null,
            'hearing_date' => $validated['hearing_date'] ?? null,
            'trial_date' => $validated['trial_date'] ?? null,
            'statute_references' => $validated['statute_references'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        MatterActivity::log(
            matter: $matter,
            activityType: 'matter_updated',
            description: "{$user->name} (".ucfirst(str_replace('_', ' ', $user->role)).") updated case administrative metadata: {$matter->case_number} ({$matter->title})",
            subject: $matter,
            userId: $user->id,
            clientId: $matter->client_id,
            isClientSafe: true
        );

        return back()->with('success', "Administrative matter details updated for {$matter->case_number}.");
    }
}
