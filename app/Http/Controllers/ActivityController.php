<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Firm-wide audit trail and activity feed.
     */
    public function index(Request $request): View
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $query = MatterActivity::where('firm_id', $firmId)
            ->with(['matter', 'user', 'client']);

        if ($matterId = $request->get('matter_id')) {
            $query->where('matter_id', $matterId);
        }

        if ($type = $request->get('activity_type')) {
            if ($type !== 'all') {
                $query->where('activity_type', $type);
            }
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('activity_type', 'like', "%{$search}%");
            });
        }

        $activities = $query->chronological('desc')->paginate(25)->withQueryString();

        $matters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number'])->orderBy('title')->get();
        $users = User::where('firm_id', $firmId)->select(['id', 'name'])->orderBy('name')->get();

        return view('activities.index', compact('activities', 'matters', 'users'));
    }

    /**
     * Matter-specific Case Chronology & Procedural Milestone Timeline.
     */
    public function matterChronology(Request $request, Matter $matter): View
    {
        $user = Auth::user();
        if ($matter->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized case dossier.');
        }

        // Lawyer-level access restriction
        if (! in_array($user->role, ['superadmin', 'partner'])) {
            $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized: You are not assigned to this case dossier.');
            }
        }

        $direction = $request->get('order', 'asc'); // default chronological asc (story of the case)
        $filterType = $request->get('type', 'all');
        $filterVisibility = $request->get('visibility', 'all');
        $search = $request->get('q');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = MatterActivity::where('matter_id', $matter->id)
            ->with(['user', 'client', 'subject']);

        if ($filterType && $filterType !== 'all') {
            if ($filterType === 'documents') {
                $query->whereIn('activity_type', ['document_uploaded', 'document_viewed', 'document_downloaded', 'document_shared']);
            } elseif ($filterType === 'hearings') {
                $query->whereIn('activity_type', ['calendar_event', 'hearing_scheduled']);
            } elseif ($filterType === 'tasks') {
                $query->whereIn('activity_type', ['task_created', 'task_completed', 'task_updated']);
            } elseif ($filterType === 'communications') {
                $query->whereIn('activity_type', ['message_sent', 'client_activity']);
            } elseif ($filterType === 'notes') {
                $query->whereIn('activity_type', ['note_added']);
            } elseif ($filterType === 'milestones') {
                $query->whereIn('activity_type', ['milestone', 'procedural_milestone', 'complaint_drafted', 'complaint_filed', 'hearing_scheduled', 'medical_records_reviewed', 'matter_created', 'status_changed']);
            } elseif ($filterType === 'team') {
                $query->whereIn('activity_type', ['assignment_changed']);
            } else {
                $query->where('activity_type', $filterType);
            }
        }

        if ($filterVisibility === 'client_safe') {
            $query->where('is_client_safe', true);
        } elseif ($filterVisibility === 'internal') {
            $query->where('is_client_safe', false);
        }

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($dateFrom) {
            $query->whereDate('occurred_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('occurred_at', '<=', $dateTo);
        }

        $activities = $query->chronological($direction)->get();

        // Key progression milestones (for the visual pipeline bar: Client uploaded medical records → Attorney reviewed documents → Complaint drafted → Complaint filed → Hearing scheduled)
        $keyMilestones = MatterActivity::where('matter_id', $matter->id)
            ->whereIn('activity_type', [
                'matter_created',
                'client_activity',
                'document_uploaded',
                'document_viewed',
                'medical_records_reviewed',
                'complaint_drafted',
                'complaint_filed',
                'hearing_scheduled',
                'milestone',
                'status_changed',
            ])
            ->chronological('asc')
            ->take(8)
            ->get();

        $stats = [
            'total_events' => MatterActivity::where('matter_id', $matter->id)->count(),
            'client_safe_events' => MatterActivity::where('matter_id', $matter->id)->where('is_client_safe', true)->count(),
            'internal_events' => MatterActivity::where('matter_id', $matter->id)->where('is_client_safe', false)->count(),
            'document_events' => MatterActivity::where('matter_id', $matter->id)->whereIn('activity_type', ['document_uploaded', 'document_viewed', 'document_downloaded', 'document_shared'])->count(),
            'hearing_events' => MatterActivity::where('matter_id', $matter->id)->whereIn('activity_type', ['calendar_event', 'hearing_scheduled'])->count(),
            'tasks_events' => MatterActivity::where('matter_id', $matter->id)->whereIn('activity_type', ['task_created', 'task_completed'])->count(),
            'first_event_at' => MatterActivity::where('matter_id', $matter->id)->chronological('asc')->first()?->effective_occurred_at,
            'last_event_at' => MatterActivity::where('matter_id', $matter->id)->chronological('desc')->first()?->effective_occurred_at,
        ];

        return view('matters.chronology', compact('matter', 'activities', 'keyMilestones', 'stats', 'direction', 'filterType', 'filterVisibility'));
    }

    /**
     * Record a procedural milestone or case chronology event manually.
     */
    public function storeMatterChronology(Request $request, Matter $matter): RedirectResponse
    {
        $user = Auth::user();
        if ($matter->firm_id !== ($user->firm_id ?? 1)) {
            abort(403);
        }

        if (! in_array($user->role, ['superadmin', 'partner'])) {
            $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized: You are not assigned to this case dossier.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'activity_type' => 'required|string',
            'occurred_at' => 'required|date',
            'description' => 'nullable|string|max:2000',
            'is_client_safe' => 'nullable',
        ]);

        $isClientSafe = $request->boolean('is_client_safe');
        $occurredAt = Carbon::parse($validated['occurred_at']);

        $desc = $validated['title'];
        if (! empty($validated['description'])) {
            $desc .= " — {$validated['description']}";
        }

        MatterActivity::log(
            matter: $matter,
            activityType: $validated['activity_type'],
            description: $desc,
            subject: null,
            userId: $user->id,
            clientId: $matter->client_id,
            metadata: ['manual_milestone' => true, 'title' => $validated['title']],
            isClientSafe: $isClientSafe,
            occurredAt: $occurredAt
        );

        return back()->with('success', "Case chronology milestone '{$validated['title']}' recorded successfully.");
    }
}
