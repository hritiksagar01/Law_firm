<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Event;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Executive Overview of Operational Practice Metrics
     */
    public function index(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        // Key counts
        $totalMatters = Matter::where('firm_id', $firmId)->count();
        $activeMatters = Matter::where('firm_id', $firmId)->where('status', 'active')->count();
        $totalClients = Client::where('firm_id', $firmId)->count();
        $upcomingHearings = Event::where('firm_id', $firmId)
            ->where('start_time', '>=', now()->startOfDay())
            ->count();
        $pendingTasks = Task::where('firm_id', $firmId)->where('status', '!=', 'completed')->count();

        // Stage Distribution
        $stages = Matter::where('firm_id', $firmId)
            ->selectRaw('stage, count(*) as count')
            ->groupBy('stage')
            ->pluck('count', 'stage')
            ->toArray();

        // Practice Area Distribution
        $practiceAreas = Matter::where('firm_id', $firmId)
            ->selectRaw('practice_area, count(*) as count')
            ->groupBy('practice_area')
            ->pluck('count', 'practice_area')
            ->toArray();

        // Court / Forum Distribution
        $courts = Matter::where('firm_id', $firmId)
            ->whereNotNull('court_name')
            ->where('court_name', '!=', '')
            ->selectRaw('court_name, count(*) as count')
            ->groupBy('court_name')
            ->pluck('count', 'court_name')
            ->toArray();

        // Recent Hearings / Cause List Entries
        $recentHearings = Event::where('firm_id', $firmId)
            ->where('start_time', '>=', now()->startOfDay())
            ->with('matter.client')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return view('reports.index', compact(
            'totalMatters',
            'activeMatters',
            'totalClients',
            'upcomingHearings',
            'pendingTasks',
            'stages',
            'practiceAreas',
            'courts',
            'recentHearings'
        ));
    }

    /**
     * Case Progress & Stage Lifecycle Report
     */
    public function caseReports(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $query = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney']);

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('practice_area') && $request->practice_area !== 'all') {
            $query->where('practice_area', $request->practice_area);
        }

        if ($request->filled('court') && $request->court !== 'all') {
            $query->where('court_name', $request->court);
        }

        if ($request->filled('attorney_id') && $request->attorney_id !== 'all') {
            $query->where('lead_attorney_id', $request->attorney_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('case_number', 'like', "%{$q}%");
            });
        }

        $matters = $query->latest('opened_at')->get();

        // Distinct filters
        $allStages = Matter::where('firm_id', $firmId)->distinct()->pluck('stage')->filter();
        $allPracticeAreas = Matter::where('firm_id', $firmId)->distinct()->pluck('practice_area')->filter();
        $allCourts = Matter::where('firm_id', $firmId)->whereNotNull('court_name')->distinct()->pluck('court_name')->filter();
        $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate', 'admin'])->get();

        return view('reports.cases', compact('matters', 'allStages', 'allPracticeAreas', 'allCourts', 'attorneys'));
    }

    /**
     * Cause List & Daily Court Schedule
     */
    public function hearingSchedule(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $dateFilter = $request->input('range', 'week'); // today, tomorrow, week, month, all
        $query = Event::where('firm_id', $firmId)->with(['matter.client', 'matter.leadAttorney']);

        if ($dateFilter === 'today') {
            $query->whereDate('start_time', now()->toDateString());
        } elseif ($dateFilter === 'tomorrow') {
            $query->whereDate('start_time', now()->addDay()->toDateString());
        } elseif ($dateFilter === 'week') {
            $query->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'month') {
            $query->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        if ($request->filled('court') && $request->court !== 'all') {
            $court = $request->court;
            $query->whereHas('matter', function ($m) use ($court) {
                $m->where('court_name', $court);
            });
        }

        $hearings = $query->orderBy('start_time')->get();

        // Distinct courts for filtering
        $allCourts = Matter::where('firm_id', $firmId)->whereNotNull('court_name')->distinct()->pluck('court_name')->filter();

        return view('reports.hearings', compact('hearings', 'allCourts', 'dateFilter'));
    }

    /**
     * Client Engagement & Case Portfolio Report
     */
    public function clientActivity(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $clients = Client::where('firm_id', $firmId)
            ->withCount(['matters', 'documentRequests'])
            ->with(['matters' => function ($q) {
                $q->latest()->take(3);
            }])
            ->get();

        return view('reports.clients', compact('clients'));
    }

    /**
     * Advocate & Team Workload Allocation Report
     */
    public function workload(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $advocates = User::where('firm_id', $firmId)
            ->whereIn('role', ['admin', 'partner', 'associate', 'paralegal'])
            ->withCount([
                'tasks as total_tasks_count',
                'tasks as pending_tasks_count' => function ($q) {
                    $q->where('status', '!=', 'completed');
                },
                'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 'completed');
                },
            ])
            ->get()
            ->map(function ($advocate) {
                $advocate->matters_count = Matter::where('lead_attorney_id', $advocate->id)->count();
                $advocate->active_matters_count = Matter::where('lead_attorney_id', $advocate->id)
                    ->where('status', 'active')
                    ->count();
                return $advocate;
            });

        return view('reports.workload', compact('advocates'));
    }

    /**
     * Bank Activity & Financial Transactions Report (PDF Page 22)
     */
    public function bankActivity(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $bankAccounts = \App\Models\BankAccount::where('firm_id', $firmId)->get();

        $transactions = \App\Models\Transaction::where('firm_id', $firmId)
            ->with(['matter.client', 'client', 'invoice'])
            ->latest('date')
            ->get();

        $totalInflow = $transactions->whereIn('type', ['payment', 'advance_deposit'])->sum('amount');
        $totalOutflow = \App\Models\Expense::where('firm_id', $firmId)->sum('amount');
        $netOperatingBalance = $totalInflow - $totalOutflow;

        return view('reports.bank-activity', compact('bankAccounts', 'transactions', 'totalInflow', 'totalOutflow', 'netOperatingBalance'));
    }
}

