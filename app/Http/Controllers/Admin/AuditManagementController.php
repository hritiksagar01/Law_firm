<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Firm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditManagementController extends Controller
{
    /**
     * Display the append-only platform audit log.
     */
    public function index(Request $request): View
    {
        $firms = Firm::orderBy('name')->get(['id', 'name']);

        $query = AuditLog::with('firm');

        if ($request->filled('firm_id') && $request->firm_id !== 'all') {
            if ($request->firm_id === 'platform') {
                $query->whereNull('firm_id');
            } else {
                $query->where('firm_id', $request->firm_id);
            }
        }

        if ($request->filled('action') && ! in_array(strtolower($request->action), ['any', 'all', ''])) {
            $query->where('action', $request->action);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('actor_name', 'like', "%{$q}%")
                    ->orWhere('actor_email', 'like', "%{$q}%")
                    ->orWhere('record_type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('from')) {
            try {
                $from = Carbon::parse($request->from)->startOfDay();
                $query->where('created_at', '>=', $from);
            } catch (\Throwable) {
            }
        }

        if ($request->filled('to')) {
            try {
                $to = Carbon::parse($request->to)->endOfDay();
                $query->where('created_at', '<=', $to);
            } catch (\Throwable) {
            }
        }

        $logs = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        return view('admin.audit.index', compact('logs', 'firms'));
    }

    /**
     * Export the filtered audit logs to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with('firm');

        if ($request->filled('firm_id') && $request->firm_id !== 'all') {
            if ($request->firm_id === 'platform') {
                $query->whereNull('firm_id');
            } else {
                $query->where('firm_id', $request->firm_id);
            }
        }

        if ($request->filled('action') && ! in_array(strtolower($request->action), ['any', 'all', ''])) {
            $query->where('action', $request->action);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('actor_name', 'like', "%{$q}%")
                    ->orWhere('actor_email', 'like', "%{$q}%");
            });
        }

        $filename = 'audit_logs_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Time (UTC)', 'Action Code', 'Action Label', 'Actor Name', 'Actor Email', 'Firm', 'Record', 'IP Address']);

            $query->orderByDesc('created_at')->chunk(500, function ($batch) use ($handle) {
                foreach ($batch as $log) {
                    fputcsv($handle, [
                        $log->created_at->format('Y-m-d H:i:s').' UTC',
                        $log->action,
                        $log->action_label,
                        $log->actor_name,
                        $log->actor_email ?? '',
                        $log->firm ? $log->firm->name : 'Platform',
                        $log->record_type,
                        $log->ip_address ?? '—',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
