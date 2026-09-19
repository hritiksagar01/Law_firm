<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firm;
use App\Models\Matter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatterManagementController extends Controller
{
    /**
     * Display a listing of all matters across all firms for platform administration.
     */
    public function index(Request $request): View
    {
        $firms = Firm::orderBy('name')->get();

        $query = Matter::with('firm')->withCount(['documents', 'messages']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('case_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        if ($request->filled('firm_id') && $request->firm_id !== 'all') {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('status') && ! in_array(strtolower($request->status), ['any', 'all', ''])) {
            $status = strtolower($request->status);
            if ($status === 'open') {
                $query->whereIn('status', ['open', 'active']);
            } else {
                $query->where('status', $status);
            }
        }

        $matters = $query->orderByDesc('opened_at')->orderByDesc('updated_at')->get();

        return view('admin.matters.index', compact('matters', 'firms'));
    }
}
