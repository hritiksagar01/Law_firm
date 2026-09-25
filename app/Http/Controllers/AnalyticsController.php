<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ConflictCheck;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $firmId = Auth::user()->firm_id ?? 1;

        // Matter breakdown
        $activeMatters = Matter::where('firm_id', $firmId)->where('status', 'active')->count();
        $closedMatters = Matter::where('firm_id', $firmId)->where('status', 'closed')->count();
        $pendingMatters = Matter::where('firm_id', $firmId)->whereIn('status', ['pending', 'open'])->count();
        $newMattersThisMonth = Matter::where('firm_id', $firmId)->where('created_at', '>=', now()->startOfMonth())->count();
        $totalMatters = Matter::where('firm_id', $firmId)->count();

        // Client Lifecycle Funnel
        $leadsCount = Client::where('firm_id', $firmId)->where('status', 'lead')->count();
        $intakeCount = Client::where('firm_id', $firmId)->where('status', 'intake')->count();
        $conflictCheckCount = Client::where('firm_id', $firmId)->where('status', 'conflict_check')->count();
        $prospectiveCount = Client::where('firm_id', $firmId)->where('status', 'prospective')->count();
        $activeClientsCount = Client::where('firm_id', $firmId)->where('status', 'active')->count();
        $inactiveClientsCount = Client::where('firm_id', $firmId)->where('status', 'inactive')->count();
        $formerClientsCount = Client::where('firm_id', $firmId)->where('status', 'former')->count();
        $totalClients = Client::where('firm_id', $firmId)->count();

        // Task & Productivity Metrics
        $totalTasks = Task::where('firm_id', $firmId)->count();
        $completedTasks = Task::where('firm_id', $firmId)->where('status', 'completed')->count();
        $overdueTasks = Task::where('firm_id', $firmId)->where('status', '!=', 'completed')->where('due_date', '<', now()->toDateString())->count();
        $pendingTasks = Task::where('firm_id', $firmId)->where('status', '!=', 'completed')->count();

        // Document & Filing Metrics
        $totalDocs = Document::where('firm_id', $firmId)->count();
        $pendingDocRequests = DocumentRequest::where('firm_id', $firmId)->whereIn('status', ['pending', 'submitted', 'under_review'])->count();
        $completedDocRequests = DocumentRequest::where('firm_id', $firmId)->where('status', 'completed')->count();

        // Conflict Checks
        $totalConflictChecks = ConflictCheck::where('firm_id', $firmId)->count();
        $clearedConflictChecks = ConflictCheck::where('firm_id', $firmId)->whereIn('status', ['clear', 'cleared_by_attorney'])->count();
        $flaggedConflictChecks = ConflictCheck::where('firm_id', $firmId)->whereIn('status', ['conflict_identified', 'potential_conflict'])->count();

        // Team Workload Distribution
        $teamWorkload = User::where('firm_id', $firmId)
            ->whereIn('role', ['partner', 'associate', 'paralegal'])
            ->withCount([
                'leadMatters as active_cases_count' => fn ($q) => $q->where('status', 'active'),
                'tasks as pending_tasks_count' => fn ($q) => $q->where('status', '!=', 'completed'),
            ])
            ->get();

        return view('analytics.index', compact(
            'activeMatters',
            'closedMatters',
            'pendingMatters',
            'newMattersThisMonth',
            'totalMatters',
            'leadsCount',
            'intakeCount',
            'conflictCheckCount',
            'prospectiveCount',
            'activeClientsCount',
            'inactiveClientsCount',
            'formerClientsCount',
            'totalClients',
            'totalTasks',
            'completedTasks',
            'overdueTasks',
            'pendingTasks',
            'totalDocs',
            'pendingDocRequests',
            'completedDocRequests',
            'totalConflictChecks',
            'clearedConflictChecks',
            'flaggedConflictChecks',
            'teamWorkload'
        ));
    }
}
