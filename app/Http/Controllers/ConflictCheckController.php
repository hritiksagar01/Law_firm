<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientMember;
use App\Models\ConflictCheck;
use App\Models\Matter;
use App\Models\MatterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConflictCheckController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        $query = ConflictCheck::where('firm_id', $firmId)
            ->with(['client', 'matter', 'checker', 'reviewer']);

        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($matterId = $request->get('matter_id')) {
            $query->where('matter_id', $matterId);
        }

        if ($search = trim($request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('search_terms', 'like', "%{$search}%")
                    ->orWhere('conflict_description', 'like', "%{$search}%")
                    ->orWhere('resolution', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('matter', fn ($mq) => $mq->where('title', 'like', "%{$search}%")->orWhere('case_number', 'like', "%{$search}%"));
            });
        }

        $checks = $query->latest()->paginate(15)->withQueryString();

        $clients = Client::where('firm_id', $firmId)->select(['id', 'name', 'status'])->orderBy('name')->get();
        $matters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number'])->orderBy('title')->get();

        $statusCounts = [
            'all' => ConflictCheck::where('firm_id', $firmId)->count(),
            'pending' => ConflictCheck::where('firm_id', $firmId)->where('status', 'pending')->count(),
            'clear' => ConflictCheck::where('firm_id', $firmId)->where('status', 'clear')->count(),
            'conflict_identified' => ConflictCheck::where('firm_id', $firmId)->whereIn('status', ['conflict_identified', 'potential_conflict'])->count(),
            'waiver_required' => ConflictCheck::where('firm_id', $firmId)->where('status', 'waiver_required')->count(),
            'cleared_by_attorney' => ConflictCheck::where('firm_id', $firmId)->where('status', 'cleared_by_attorney')->count(),
            'rejected' => ConflictCheck::where('firm_id', $firmId)->where('status', 'rejected')->count(),
        ];

        return view('conflict-checks.index', compact('checks', 'clients', 'matters', 'statusCounts'));
    }

    /**
     * Run an interactive or API conflict search across clients, opposing parties, and members.
     */
    public function search(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;
        $term = trim($request->get('term', $request->get('q', '')));

        if (strlen($term) < 2) {
            return response()->json([
                'term' => $term,
                'search_term' => $term,
                'matches' => [],
                'results' => [],
                'total' => 0,
                'matches_found' => 0,
            ]);
        }

        $clients = Client::where('firm_id', $firmId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('contact_person', 'like', "%{$term}%")
                    ->orWhere('pan', 'like', "%{$term}%")
                    ->orWhere('cin', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->select(['id', 'name', 'status', 'category', 'type', 'created_at'])
            ->take(10)
            ->get()
            ->map(fn ($c) => [
                'type' => 'Client ('.$c->status_label.')',
                'name' => $c->name,
                'status' => $c->status,
                'details' => "Category: {$c->category}, Joined: ".$c->created_at->format('M Y'),
                'url' => route('clients.show', $c->id),
            ]);

        $matters = Matter::where('firm_id', $firmId)
            ->where(function ($q) use ($term) {
                $q->where('opposing_party', 'like', "%{$term}%")
                    ->orWhere('opposing_counsel', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%");
            })
            ->select(['id', 'title', 'case_number', 'opposing_party', 'opposing_counsel', 'status'])
            ->take(10)
            ->get()
            ->map(fn ($m) => [
                'type' => 'Matter Record / Opposing Party',
                'name' => $m->opposing_party ?: $m->title,
                'status' => $m->status,
                'details' => "Case {$m->case_number}: {$m->title} (Opposing Counsel: ".($m->opposing_counsel ?: 'N/A').')',
                'url' => route('matters.show', $m->id),
            ]);

        $members = ClientMember::where('firm_id', $firmId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('pan', 'like', "%{$term}%");
            })
            ->with('client:id,name')
            ->take(10)
            ->get()
            ->map(fn ($mb) => [
                'type' => 'Related Party / Witness / Signatory',
                'name' => $mb->name,
                'status' => 'active',
                'details' => "Relation: {$mb->relationship} of Client '{$mb->client?->name}'",
                'url' => $mb->client ? route('clients.show', $mb->client->id) : '#',
            ]);

        $all = $clients->concat($matters)->concat($members);

        return response()->json([
            'term' => $term,
            'search_term' => $term,
            'matches' => $all,
            'results' => $all,
            'total' => $all->count(),
            'matches_found' => $all->count(),
            'conflict_potential' => $all->count() > 0,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'matter_id' => 'nullable|exists:matters,id',
            'search_terms' => 'required|string|max:1000',
            'search_results' => 'nullable|string',
            'conflict_identified' => 'nullable|boolean',
            'conflict_description' => 'nullable|string|max:3000',
            'resolution' => 'nullable|string|max:3000',
            'status' => 'required|in:pending,clear,potential_conflict,conflict_identified,waiver_required,cleared_by_attorney,rejected',
        ]);

        $conflictIdentified = ! empty($validated['conflict_identified']) || in_array($validated['status'], ['conflict_identified', 'potential_conflict']);

        $check = ConflictCheck::create([
            'firm_id' => $firmId,
            'client_id' => $validated['client_id'],
            'matter_id' => $validated['matter_id'] ?? null,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
            'search_terms' => $validated['search_terms'],
            'search_results' => $validated['search_results'] ?? null,
            'conflict_identified' => $conflictIdentified,
            'conflict_description' => $validated['conflict_description'] ?? null,
            'resolution' => $validated['resolution'] ?? null,
            'status' => $validated['status'],
        ]);

        // Sync client conflict_check_status
        $client = Client::where('id', $validated['client_id'])->where('firm_id', $firmId)->first();
        if ($client) {
            $mappedStatus = match ($validated['status']) {
                'clear' => 'clear',
                'potential_conflict' => 'potential_conflict',
                'conflict_identified' => 'conflict_identified',
                'waiver_required' => 'waiver_required',
                'cleared_by_attorney' => 'cleared',
                'rejected' => 'rejected',
                default => 'pending',
            };
            $client->update(['conflict_check_status' => $mappedStatus]);
        }

        // Log matter activity if matter is linked
        if (! empty($validated['matter_id'])) {
            $matter = Matter::find($validated['matter_id']);
            if ($matter) {
                MatterActivity::log(
                    matter: $matter,
                    activityType: 'conflict_check_completed',
                    description: "Conflict check performed for search '{$validated['search_terms']}'. Result: {$check->status_label}",
                    subject: $check,
                    userId: Auth::id(),
                    clientId: $validated['client_id'],
                    metadata: ['status' => $check->status, 'conflict_identified' => $conflictIdentified]
                );
            }
        }

        return redirect()->route('conflict-checks.show', $check->id)
            ->with('success', "Conflict Check {$check->uuid} filed successfully. Status: {$check->status_label}.");
    }

    public function show(ConflictCheck $conflictCheck): View
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($conflictCheck->firm_id !== $firmId) {
            abort(403, 'Unauthorized access to conflict check record.');
        }

        $conflictCheck->load(['client', 'matter', 'checker', 'reviewer']);

        return view('conflict-checks.show', compact('conflictCheck'));
    }

    public function update(Request $request, ConflictCheck $conflictCheck): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($conflictCheck->firm_id !== $firmId) {
            abort(403, 'Unauthorized update.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,clear,potential_conflict,conflict_identified,waiver_required,cleared_by_attorney,rejected',
            'conflict_description' => 'nullable|string|max:3000',
            'resolution' => 'nullable|string|max:3000',
            'conflict_identified' => 'nullable|boolean',
        ]);

        $conflictIdentified = ! empty($validated['conflict_identified']) || in_array($validated['status'], ['conflict_identified', 'potential_conflict']);

        $conflictCheck->update([
            'status' => $validated['status'],
            'conflict_description' => $validated['conflict_description'] ?? $conflictCheck->conflict_description,
            'resolution' => $validated['resolution'] ?? $conflictCheck->resolution,
            'conflict_identified' => $conflictIdentified,
            'reviewer_id' => Auth::id(),
            'review_date' => now(),
        ]);

        // Sync client status
        if ($conflictCheck->client) {
            $mappedStatus = match ($validated['status']) {
                'clear' => 'clear',
                'potential_conflict' => 'potential_conflict',
                'conflict_identified' => 'conflict_identified',
                'waiver_required' => 'waiver_required',
                'cleared_by_attorney' => 'cleared',
                'rejected' => 'rejected',
                default => 'pending',
            };
            $conflictCheck->client->update(['conflict_check_status' => $mappedStatus]);
        }

        if ($conflictCheck->matter) {
            MatterActivity::log(
                matter: $conflictCheck->matter,
                activityType: 'conflict_check_completed',
                description: 'Conflict check updated by Advocate '.Auth::user()->name." to: {$conflictCheck->status_label}",
                subject: $conflictCheck,
                userId: Auth::id(),
                clientId: $conflictCheck->client_id,
                metadata: ['status' => $conflictCheck->status, 'reviewer' => Auth::user()->name]
            );
        }

        return back()->with('success', "Conflict Check updated. Determination: {$conflictCheck->status_label}.");
    }
}
