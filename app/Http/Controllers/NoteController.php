<?php

namespace App\Http\Controllers;

use App\Models\CaseNote;
use App\Models\Matter;
use App\Models\MatterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * Display a listing of case notes across all matters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $firmId = $user->firm_id ?? 1;

        $query = CaseNote::where('firm_id', $firmId)->with(['matter', 'user']);

        // Staff / Legal Assistant protection: Privileged notes are strictly hidden by default
        if (! $user->canAccessPrivilegedNotes()) {
            $query->where('is_privileged', false)
                ->whereNotIn('type', ['privileged', 'attorney_only']);
        }

        if ($matterId = $request->get('matter_id')) {
            $query->where('matter_id', $matterId);
        }

        if ($type = $request->get('type')) {
            if ($type !== 'all') {
                $query->where('type', $type);
            }
        }

        if ($request->boolean('pinned')) {
            $query->where('is_pinned', true);
        }

        if ($search = trim($request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $notes = $query->orderByDesc('is_pinned')->latest()->paginate(15)->withQueryString();
        $matters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number'])->get();

        return view('notes.index', compact('notes', 'matters'));
    }

    /**
     * Store a new case / practice note.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matter_id' => 'nullable|exists:matters,id',
            'category_id' => 'nullable|exists:note_categories,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:internal,client_visible,privileged,attorney_only',
            'is_pinned' => 'nullable|boolean',
            'is_privileged' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isPrivileged = $request->boolean('is_privileged') || in_array($validated['type'] ?? '', ['privileged', 'attorney_only']);

        // Privileged attorney notes restriction: staff cannot author or access privileged notes
        if ($isPrivileged && ! $user->canAccessPrivilegedNotes()) {
            abort(403, 'Unauthorized: Staff members do not have permission to author or access privileged attorney notes.');
        }

        $note = CaseNote::create([
            'firm_id' => $user->firm_id,
            'matter_id' => $validated['matter_id'] ?? null,
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'] ?? 'internal',
            'is_pinned' => $request->boolean('is_pinned'),
            'is_privileged' => $isPrivileged,
        ]);

        if (! empty($note->matter_id)) {
            $matter = Matter::find($note->matter_id);
            if ($matter) {
                MatterActivity::log(
                    matter: $matter,
                    activityType: 'note_added',
                    description: "Advocate {$user->name} recorded case note: '{$note->title}'",
                    subject: $note,
                    userId: $user->id,
                    clientId: $matter->client_id,
                    isClientSafe: ($note->type === 'client_visible' && ! $note->is_privileged)
                );
            }
        }

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * Update an existing note.
     */
    public function update(Request $request, CaseNote $note): RedirectResponse
    {
        if ($note->firm_id !== $request->user()->firm_id) {
            abort(403);
        }

        if (($note->is_privileged || in_array($note->type, ['privileged', 'attorney_only'])) && ! $request->user()->canAccessPrivilegedNotes()) {
            abort(403, 'Unauthorized: Staff members do not have permission to access privileged attorney notes.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:internal,client_visible,privileged,attorney_only',
            'category_id' => 'nullable|exists:note_categories,id',
            'is_privileged' => 'nullable|boolean',
        ]);

        if ($request->has('is_privileged') || in_array($validated['type'] ?? '', ['privileged', 'attorney_only'])) {
            $willBePrivileged = $request->boolean('is_privileged') || in_array($validated['type'] ?? '', ['privileged', 'attorney_only']);
            if ($willBePrivileged && ! $request->user()->canAccessPrivilegedNotes()) {
                abort(403, 'Unauthorized: Staff members do not have permission to mark notes as privileged.');
            }
            $validated['is_privileged'] = $willBePrivileged;
        }

        $note->update($validated);

        return back()->with('success', 'Note updated successfully.');
    }

    /**
     * Toggle the pinned status of a note.
     */
    public function togglePin(Request $request, CaseNote $note): RedirectResponse
    {
        if ($note->firm_id !== $request->user()->firm_id) {
            abort(403);
        }

        if (($note->is_privileged || in_array($note->type, ['privileged', 'attorney_only'])) && ! $request->user()->canAccessPrivilegedNotes()) {
            abort(403, 'Unauthorized: Staff members do not have permission to access privileged attorney notes.');
        }

        $note->update([
            'is_pinned' => ! $note->is_pinned,
        ]);

        return back()->with('success', $note->is_pinned ? 'Note pinned to top.' : 'Note unpinned.');
    }

    /**
     * Delete a note.
     */
    public function destroy(Request $request, CaseNote $note): RedirectResponse
    {
        if ($note->firm_id !== $request->user()->firm_id) {
            abort(403);
        }

        if (($note->is_privileged || in_array($note->type, ['privileged', 'attorney_only'])) && ! $request->user()->canAccessPrivilegedNotes()) {
            abort(403, 'Unauthorized: Staff members do not have permission to delete privileged attorney notes.');
        }

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
