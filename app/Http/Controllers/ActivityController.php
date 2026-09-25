<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityController extends Controller
{
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

        $activities = $query->latest()->paginate(25)->withQueryString();

        $matters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number'])->orderBy('title')->get();
        $users = User::where('firm_id', $firmId)->select(['id', 'name'])->orderBy('name')->get();

        return view('activities.index', compact('activities', 'matters', 'users'));
    }
}
