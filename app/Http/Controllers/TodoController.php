<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    /**
     * Display personal todos and productivity metrics.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Todo::where('user_id', $user->id)->with('matter');

        // Filter by completion status
        $status = $request->query('status', 'all');
        if ($status === 'pending') {
            $query->where('is_completed', false);
        } elseif ($status === 'completed') {
            $query->where('is_completed', true);
        }

        // Filter by matter
        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->query('matter_id'));
        }

        $todos = $query->orderBy('is_completed')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Connected Metrics Ribbon Calculation
        $baseQuery = Todo::where('user_id', $user->id);
        $totalCount = (clone $baseQuery)->count();
        $completedCount = (clone $baseQuery)->where('is_completed', true)->count();
        $dueTodayCount = (clone $baseQuery)->where('is_completed', false)->whereDate('due_date', today())->count();
        $overdueCount = (clone $baseQuery)->where('is_completed', false)->whereDate('due_date', '<', today())->count();

        // Available matters for dropdown
        $matters = Matter::where('firm_id', $user->firm_id)->orderBy('case_number')->get(['id', 'case_number', 'title']);

        return view('todos.index', compact(
            'todos',
            'totalCount',
            'completedCount',
            'dueTodayCount',
            'overdueCount',
            'matters',
            'status'
        ));
    }

    /**
     * Store a new personal todo item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'matter_id' => 'nullable|exists:matters,id',
            'due_date' => 'nullable|date',
        ]);

        Todo::create([
            'user_id' => $request->user()->id,
            'matter_id' => $validated['matter_id'] ?? null,
            'title' => $validated['title'],
            'due_date' => $validated['due_date'] ?? null,
            'is_completed' => false,
        ]);

        return back()->with('success', 'Todo item created.');
    }

    /**
     * Toggle todo completion state.
     */
    public function toggle(Request $request, Todo $todo): RedirectResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $isCompleted = ! $todo->is_completed;
        $todo->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        return back()->with('success', $isCompleted ? 'Todo completed!' : 'Todo reopened.');
    }

    /**
     * Update an existing todo.
     */
    public function update(Request $request, Todo $todo): RedirectResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'matter_id' => 'nullable|exists:matters,id',
            'due_date' => 'nullable|date',
        ]);

        $todo->update($validated);

        return back()->with('success', 'Todo updated.');
    }

    /**
     * Delete a todo item.
     */
    public function destroy(Request $request, Todo $todo): RedirectResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $todo->delete();

        return back()->with('success', 'Todo deleted.');
    }
}
