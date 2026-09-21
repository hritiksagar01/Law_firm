<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Matter;
use App\Models\Opinion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OpinionController extends Controller
{
    /**
     * Display legal opinions & advisory memoranda.
     */
    public function index(Request $request): View
    {
        $firmId = Auth::user()->firm_id;

        $query = Opinion::where('firm_id', $firmId)
            ->with(['matter', 'client', 'author', 'reviewer']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('opinion_number', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->matter_id);
        }

        $opinions = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Opinion::where('firm_id', $firmId)->count(),
            'published' => Opinion::where('firm_id', $firmId)->where('status', 'published')->count(),
            'under_review' => Opinion::where('firm_id', $firmId)->where('status', 'under_review')->count(),
            'draft' => Opinion::where('firm_id', $firmId)->where('status', 'draft')->count(),
        ];

        return view('opinions.index', compact('opinions', 'stats'));
    }

    /**
     * Show form to draft new legal opinion.
     */
    public function create(Request $request): View
    {
        $firmId = Auth::user()->firm_id;

        $matters = Matter::where('firm_id', $firmId)->latest()->get();
        $clients = Client::where('firm_id', $firmId)->orderBy('name')->get();
        $reviewers = User::where('firm_id', $firmId)->whereIn('role', ['admin', 'partner', 'lawyer', 'associate'])->get();

        $selectedMatterId = $request->get('matter_id');

        return view('opinions.create', compact('matters', 'clients', 'reviewers', 'selectedMatterId'));
    }

    /**
     * Store newly drafted legal opinion.
     */
    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        $validated = $request->validate([
            'matter_id' => 'nullable|exists:matters,id',
            'client_id' => 'nullable|exists:clients,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:legal_advice,case_strategy,document_review,statutory_compliance',
            'summary' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'recommendations' => 'nullable|string',
            'precedents_cited' => 'nullable|string',
            'status' => 'required|in:draft,under_review,published',
        ]);

        $year = date('Y');
        $count = Opinion::where('firm_id', $firmId)->whereYear('created_at', $year)->count() + 1;
        $opinionNumber = sprintf('OP-%s-%04d', $year, $count);

        $publishedAt = ($validated['status'] === 'published') ? now() : null;

        $opinion = Opinion::create([
            'firm_id' => $firmId,
            'matter_id' => $validated['matter_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'author_id' => Auth::id(),
            'reviewer_id' => $validated['reviewer_id'] ?? null,
            'opinion_number' => $opinionNumber,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'recommendations' => $validated['recommendations'] ?? null,
            'precedents_cited' => $validated['precedents_cited'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('opinions.show', $opinion)
            ->with('success', "Legal opinion '{$opinion->opinion_number}' drafted successfully.");
    }

    /**
     * Show legal opinion document and dossier view.
     */
    public function show(Opinion $opinion): View
    {
        $firmId = Auth::user()->firm_id;

        if ($opinion->firm_id !== $firmId) {
            abort(403);
        }

        $opinion->load(['matter', 'client', 'author', 'reviewer']);

        return view('opinions.show', compact('opinion'));
    }

    /**
     * Show form to edit legal opinion.
     */
    public function edit(Opinion $opinion): View
    {
        $firmId = Auth::user()->firm_id;

        if ($opinion->firm_id !== $firmId) {
            abort(403);
        }

        $matters = Matter::where('firm_id', $firmId)->latest()->get();
        $clients = Client::where('firm_id', $firmId)->orderBy('name')->get();
        $reviewers = User::where('firm_id', $firmId)->whereIn('role', ['admin', 'partner', 'lawyer', 'associate'])->get();

        return view('opinions.edit', compact('opinion', 'matters', 'clients', 'reviewers'));
    }

    /**
     * Update legal opinion.
     */
    public function update(Request $request, Opinion $opinion): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($opinion->firm_id !== $firmId) {
            abort(403);
        }

        $validated = $request->validate([
            'matter_id' => 'nullable|exists:matters,id',
            'client_id' => 'nullable|exists:clients,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:legal_advice,case_strategy,document_review,statutory_compliance',
            'summary' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'recommendations' => 'nullable|string',
            'precedents_cited' => 'nullable|string',
            'status' => 'required|in:draft,under_review,approved,published',
        ]);

        $publishedAt = $opinion->published_at;
        if ($validated['status'] === 'published' && ! $opinion->published_at) {
            $publishedAt = now();
        }

        $opinion->update([
            'matter_id' => $validated['matter_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'reviewer_id' => $validated['reviewer_id'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'recommendations' => $validated['recommendations'] ?? null,
            'precedents_cited' => $validated['precedents_cited'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('opinions.show', $opinion)
            ->with('success', 'Legal opinion updated successfully.');
    }

    /**
     * Transition status (submit, approve, publish).
     */
    public function changeStatus(Request $request, Opinion $opinion): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($opinion->firm_id !== $firmId) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,under_review,approved,published',
        ]);

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === 'published' && ! $opinion->published_at) {
            $updateData['published_at'] = now();
        }
        if ($validated['status'] === 'approved' && ! $opinion->reviewer_id) {
            $updateData['reviewer_id'] = Auth::id();
        }

        $opinion->update($updateData);

        return back()->with('success', 'Opinion status updated to '.ucfirst(str_replace('_', ' ', $validated['status'])).'.');
    }

    /**
     * Delete draft opinion.
     */
    public function destroy(Opinion $opinion): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($opinion->firm_id !== $firmId) {
            abort(403);
        }

        if ($opinion->status === 'published') {
            return back()->with('error', 'Cannot delete published formal legal opinions.');
        }

        $opinion->delete();

        return redirect()->route('opinions.index')
            ->with('success', 'Opinion draft was deleted.');
    }
}
