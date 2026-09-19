<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SignInHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SignInHistoryController extends Controller
{
    /**
     * Display every sign-in attempt across the platform.
     */
    public function index(Request $request): View
    {
        $query = SignInHistory::with('firm');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('email', 'like', "%{$q}%");
        }

        if ($request->filled('result') && ! in_array(strtolower($request->result), ['any', 'all', ''])) {
            $result = strtolower($request->result);
            if ($result === 'signed in' || $result === 'signed_in') {
                $query->where('result', 'signed_in');
            } elseif ($result === 'failed') {
                $query->where('result', 'failed');
            }
        }

        $signIns = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        $failedAttempts24h = SignInHistory::where('result', 'failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        return view('admin.sign_ins.index', compact('signIns', 'failedAttempts24h'));
    }
}
