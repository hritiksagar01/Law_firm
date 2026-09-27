<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with filters, metrics, and related legal entities.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        $statusFilter = $request->get('status', 'all');
        $priorityFilter = $request->get('priority', 'all');
        $matterId = $request->get('matter_id');
        $assigneeId = $request->get('assigned_to');
        $tagFilter = $request->get('tag');
        $search = trim($request->get('q', ''));

        $query = Task::where('firm_id', $firmId)
            ->with([
                'assignee',
                'creator',
                'matter.client',
                'relatedDocument',
                'relatedClient',
                'comments.user',
            ]);

        // Search Filter
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('task_number', 'like', "%{$search}%")
                    ->orWhere('related_party_name', 'like', "%{$search}%")
                    ->orWhereHas('matter', function ($mq) use ($search) {
                        $mq->where('case_number', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'pending') {
                $query->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED]);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Priority Filter
        if ($priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }

        // Matter Filter
        if (! empty($matterId)) {
            $query->where('matter_id', $matterId);
        }

        // Assignee Filter
        if (! empty($assigneeId)) {
            $query->where('assigned_to', $assigneeId);
        }

        // Tag Filter
        if (! empty($tagFilter)) {
            $query->whereJsonContains('tags', $tagFilter);
        }

        // Order by due date & priority
        $tasks = $query->orderByRaw("
            CASE 
                WHEN status IN ('completed', 'cancelled') THEN 2
                ELSE 1 
            END ASC
        ")->orderByRaw("
            CASE priority
                WHEN 'critical' THEN 1
                WHEN 'urgent' THEN 2
                WHEN 'high' THEN 3
                WHEN 'normal' THEN 4
                WHEN 'low' THEN 5
                ELSE 6
            END ASC
        ")->orderBy('due_date', 'asc')->latest()->get();

        // Metrics computation for Ribbon
        $baseMetricsQuery = Task::where('firm_id', $firmId);
        $totalCount = (clone $baseMetricsQuery)->count();
        $inProgressCount = (clone $baseMetricsQuery)->where('status', Task::STATUS_IN_PROGRESS)->count();
        $waitingBlockedCount = (clone $baseMetricsQuery)->whereIn('status', [Task::STATUS_WAITING, Task::STATUS_BLOCKED])->count();
        $urgentCriticalCount = (clone $baseMetricsQuery)->whereIn('priority', [Task::PRIORITY_URGENT, Task::PRIORITY_CRITICAL])
            ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])->count();
        $overdueCount = (clone $baseMetricsQuery)->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
            ->where('due_date', '<', Carbon::today())->count();
        $completedCount = (clone $baseMetricsQuery)->where('status', Task::STATUS_COMPLETED)->count();

        // Form Support Data
        $matters = Matter::where('firm_id', $firmId)->select(['id', 'case_number', 'title', 'client_id'])->get();
        $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate', 'paralegal', 'admin', 'advocate'])->get();
        if ($attorneys->isEmpty()) {
            $attorneys = User::where('firm_id', $firmId)->get();
        }
        $clients = Client::where('firm_id', $firmId)->select(['id', 'name'])->get();
        $documents = Document::where('firm_id', $firmId)->select(['id', 'title', 'filename', 'matter_id'])->take(50)->get();

        $recommendedTags = [
            'Court Filing',
            'Pleading Draft',
            'Discovery / Production',
            'Evidence Review',
            'Hearing Prep',
            'Client Follow-up',
            'Statutory Deadline',
            'Settlement Discussion',
            'Legal Research',
            'Inspection & Certified Copies',
        ];

        return view('tasks.index', compact(
            'tasks',
            'matters',
            'attorneys',
            'clients',
            'documents',
            'totalCount',
            'inProgressCount',
            'waitingBlockedCount',
            'urgentCriticalCount',
            'overdueCount',
            'completedCount',
            'statusFilter',
            'priorityFilter',
            'matterId',
            'assigneeId',
            'tagFilter',
            'search',
            'recommendedTags'
        ));
    }

    /**
     * Store a newly created litigation task.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'matter_id' => 'nullable|exists:matters,id',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,normal,high,urgent,critical',
            'status' => 'required|in:not_started,in_progress,waiting,blocked,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'tags' => 'nullable',
            'related_document_id' => 'nullable|exists:documents,id',
            'related_client_id' => 'nullable|exists:clients,id',
            'related_party_name' => 'nullable|string|max:255',
        ]);

        // Process tags
        $tags = [];
        if (! empty($validated['tags'])) {
            if (is_array($validated['tags'])) {
                $tags = array_values(array_filter(array_map('trim', $validated['tags'])));
            } else {
                $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
            }
        }

        // Auto-derive related client from matter if omitted
        $relatedClientId = $validated['related_client_id'] ?? null;
        if (empty($relatedClientId) && ! empty($validated['matter_id'])) {
            $matter = Matter::find($validated['matter_id']);
            $relatedClientId = $matter?->client_id;
        }

        $completedDate = $validated['completed_date'] ?? null;
        if ($validated['status'] === Task::STATUS_COMPLETED && empty($completedDate)) {
            $completedDate = now()->toDateString();
        }

        $task = Task::create([
            'firm_id' => $firmId,
            'matter_id' => $validated['matter_id'] ?? null,
            'assigned_to' => $validated['assigned_to'],
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'completed_date' => $completedDate,
            'tags' => $tags,
            'related_document_id' => $validated['related_document_id'] ?? null,
            'related_client_id' => $relatedClientId,
            'related_party_name' => $validated['related_party_name'] ?? null,
        ]);

        if ($task->matter) {
            MatterActivity::log(
                matter: $task->matter,
                activityType: 'task_created',
                description: "Created task [{$task->formatted_id}]: {$task->title} (Priority: {$task->priority_label})",
                subject: $task,
                userId: $user->id,
                clientId: $task->matter->client_id
            );
        }

        return redirect()->route('tasks.index')->with('success', "Task [{$task->formatted_id}] successfully scheduled.");
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($task->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized task access.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'matter_id' => 'nullable|exists:matters,id',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,normal,high,urgent,critical',
            'status' => 'required|in:not_started,in_progress,waiting,blocked,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'tags' => 'nullable',
            'related_document_id' => 'nullable|exists:documents,id',
            'related_client_id' => 'nullable|exists:clients,id',
            'related_party_name' => 'nullable|string|max:255',
        ]);

        $tags = [];
        if (! empty($validated['tags'])) {
            if (is_array($validated['tags'])) {
                $tags = array_values(array_filter(array_map('trim', $validated['tags'])));
            } else {
                $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
            }
        }

        $completedDate = $validated['completed_date'] ?? null;
        if ($validated['status'] === Task::STATUS_COMPLETED && empty($completedDate)) {
            $completedDate = $task->completed_date ?? now()->toDateString();
        } elseif ($validated['status'] !== Task::STATUS_COMPLETED) {
            $completedDate = null;
        }

        $oldStatus = $task->status;

        $task->update([
            'matter_id' => $validated['matter_id'] ?? null,
            'assigned_to' => $validated['assigned_to'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'completed_date' => $completedDate,
            'tags' => $tags,
            'related_document_id' => $validated['related_document_id'] ?? null,
            'related_client_id' => $validated['related_client_id'] ?? null,
            'related_party_name' => $validated['related_party_name'] ?? null,
        ]);

        if ($task->matter && $oldStatus !== $task->status) {
            MatterActivity::log(
                matter: $task->matter,
                activityType: $task->status === Task::STATUS_COMPLETED ? 'task_completed' : 'task_updated',
                description: "Task [{$task->formatted_id}] status changed from '{$oldStatus}' to '{$task->status_label}'",
                subject: $task,
                userId: $user->id,
                clientId: $task->matter->client_id
            );
        }

        return redirect()->route('tasks.index')->with('success', "Task [{$task->formatted_id}] updated successfully.");
    }

    /**
     * Quick status transition update.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($task->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized task access.');
        }

        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,waiting,blocked,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $task->status;

        $completedDate = ($newStatus === Task::STATUS_COMPLETED) ? now()->toDateString() : null;

        $task->update([
            'status' => $newStatus,
            'completed_date' => $completedDate,
        ]);

        if ($task->matter) {
            MatterActivity::log(
                matter: $task->matter,
                activityType: $newStatus === Task::STATUS_COMPLETED ? 'task_completed' : 'task_updated',
                description: "Updated task [{$task->formatted_id}] status to {$task->status_label}",
                subject: $task,
                userId: $user->id,
                clientId: $task->matter->client_id
            );
        }

        return back()->with('success', "Task [{$task->formatted_id}] status changed to {$task->status_label}.");
    }

    /**
     * Toggle Task completion status.
     */
    public function toggle(Task $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($task->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized task access.');
        }

        $isCompleted = ($task->status === Task::STATUS_COMPLETED);
        $task->status = $isCompleted ? Task::STATUS_IN_PROGRESS : Task::STATUS_COMPLETED;
        $task->completed_date = $isCompleted ? null : now()->toDateString();
        $task->save();

        if ($task->matter) {
            MatterActivity::log(
                matter: $task->matter,
                activityType: $task->status === Task::STATUS_COMPLETED ? 'task_completed' : 'task_reopened',
                description: ($task->status === Task::STATUS_COMPLETED ? 'Completed task: ' : 'Re-opened task: ').$task->title,
                subject: $task,
                userId: $user->id,
                clientId: $task->matter->client_id
            );
        }

        return back()->with('success', 'Task status updated.');
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($task->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized task access.');
        }

        $taskId = $task->formatted_id;
        $task->delete();

        return redirect()->route('tasks.index')->with('success', "Task [{$taskId}] has been deleted.");
    }

    /**
     * Add a comment to the task discussion.
     */
    public function addComment(Request $request, Task $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($task->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized task access.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Progress update logged.');
    }
}
