<?php

namespace App\Http\Controllers;

use App\Models\CaseNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
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
            'type' => 'nullable|in:internal,client_visible',
            'is_pinned' => 'nullable|boolean',
        ]);

        $user = $request->user();

        CaseNote::create([
            'firm_id' => $user->firm_id,
            'matter_id' => $validated['matter_id'] ?? null,
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'] ?? 'internal',
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:internal,client_visible',
            'category_id' => 'nullable|exists:note_categories,id',
        ]);

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

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
