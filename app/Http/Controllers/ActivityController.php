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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function matterChronology(Request $request, Matter $matter): View|StreamedResponse
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
                $query->whereIn('activity_type', ['task_created', 'task_completed', 'task_updated', 'task_reopened']);
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

        // CSV Export Option
        if ($request->get('export') === 'csv') {
            $filename = 'Chronology_'.$matter->case_number.'_'.now()->format('Ymd_His').'.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($activities, $matter) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Matter Case Number', 'Matter Title', 'Date & Time', 'Event Type', 'Classification', 'Description', 'Actor', 'Visibility']);
                foreach ($activities as $act) {
                    fputcsv($handle, [
                        $matter->case_number,
                        $matter->title,
                        $act->effective_occurred_at->format('Y-m-d H:i:s'),
                        $act->activity_type,
                        $act->type_label,
                        $act->description,
                        $act->user?->name ?? ($act->client?->name ?? 'Chambers'),
                        $act->is_client_safe ? 'Client Safe' : 'Internal Privileged',
                    ]);
                }
                fclose($handle);
            }, 200, $headers);
        }

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

        // Dynamic 5-Stage Case Chronology Pipeline Detection
        $hasStage1 = MatterActivity::where('matter_id', $matter->id)
            ->where(function ($q) {
                $q->whereIn('activity_type', ['client_activity'])
                    ->orWhere(function ($sub) {
                        $sub->where('activity_type', 'document_uploaded')
                            ->where('is_client_safe', true);
                    })
                    ->orWhere('description', 'like', '%medical%')
                    ->orWhere('description', 'like', '%records%')
                    ->orWhere('description', 'like', '%Client uploaded%');
            })->first();

        $hasStage2 = MatterActivity::where('matter_id', $matter->id)
            ->where(function ($q) {
                $q->whereIn('activity_type', ['medical_records_reviewed', 'document_viewed'])
                    ->orWhere('description', 'like', '%reviewed%')
                    ->orWhere('description', 'like', '%counsel review%');
            })->first();

        $hasStage3 = MatterActivity::where('matter_id', $matter->id)
            ->where(function ($q) {
                $q->where('activity_type', 'complaint_drafted')
                    ->orWhere('description', 'like', '%complaint drafted%')
                    ->orWhere('description', 'like', '%pleading drafted%');
            })->first();

        $hasStage4 = MatterActivity::where('matter_id', $matter->id)
            ->where(function ($q) {
                $q->where('activity_type', 'complaint_filed')
                    ->orWhere('description', 'like', '%complaint filed%')
                    ->orWhere('description', 'like', '%filed with court%');
            })->first() ?: ($matter->filing_date ? (object) ['effective_occurred_at' => Carbon::parse($matter->filing_date)] : null);

        $hasStage5 = MatterActivity::where('matter_id', $matter->id)
            ->where(function ($q) {
                $q->where('activity_type', 'hearing_scheduled')
                    ->orWhere('description', 'like', '%hearing scheduled%')
                    ->orWhere('description', 'like', '%hearing%');
            })->first() ?: ($matter->events()->where('event_type', 'hearing')->first() ? (object) ['effective_occurred_at' => Carbon::parse($matter->events()->where('event_type', 'hearing')->first()->start_time)] : null);

        $pipelineStages = [
            'stage1' => [
                'title' => 'Client Uploads Records',
                'desc' => 'Medical records, dispute contracts, and evidentiary exhibits provided.',
                'completed' => (bool) $hasStage1,
                'occurred_at' => $hasStage1?->effective_occurred_at,
                'template_title' => 'Client uploaded medical records',
                'type' => 'client_activity',
                'is_safe' => true,
            ],
            'stage2' => [
                'title' => 'Attorney Review',
                'desc' => 'Advising counsel reviews documents, legal precedents, and damages.',
                'completed' => (bool) $hasStage2,
                'occurred_at' => $hasStage2?->effective_occurred_at,
                'template_title' => 'Attorney reviewed documents',
                'type' => 'medical_records_reviewed',
                'is_safe' => false,
            ],
            'stage3' => [
                'title' => 'Complaint Drafted',
                'desc' => 'Primary pleading, affidavit, and prayer for relief formulated.',
                'completed' => (bool) $hasStage3,
                'occurred_at' => $hasStage3?->effective_occurred_at,
                'template_title' => 'Complaint drafted and verified',
                'type' => 'complaint_drafted',
                'is_safe' => false,
            ],
            'stage4' => [
                'title' => 'Complaint Filed',
                'desc' => 'Substantive filing lodged with registry; case number issued.',
                'completed' => (bool) $hasStage4,
                'occurred_at' => $hasStage4?->effective_occurred_at,
                'template_title' => 'Complaint filed with Court Registry',
                'type' => 'complaint_filed',
                'is_safe' => true,
            ],
            'stage5' => [
                'title' => 'Hearing Scheduled',
                'desc' => 'Court summons, preliminary hearing, or motion on docket.',
                'completed' => (bool) $hasStage5,
                'occurred_at' => $hasStage5?->effective_occurred_at,
                'template_title' => 'Preliminary Hearing scheduled',
                'type' => 'hearing_scheduled',
                'is_safe' => true,
            ],
        ];

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

        return view('matters.chronology', compact('matter', 'activities', 'keyMilestones', 'pipelineStages', 'stats', 'direction', 'filterType', 'filterVisibility'));
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
