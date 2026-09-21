<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentScheduledMail;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display list of appointments, consultations, and court hearings.
     */
    public function index(Request $request): View
    {
        $firmId = Auth::user()->firm_id;

        $query = Appointment::where('firm_id', $firmId)
            ->with(['matter', 'client', 'attorney']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('time_filter')) {
            if ($request->time_filter === 'today') {
                $query->whereDate('scheduled_at', today());
            } elseif ($request->time_filter === 'upcoming') {
                $query->where('scheduled_at', '>=', now());
            } elseif ($request->time_filter === 'past') {
                $query->where('scheduled_at', '<', now());
            }
        } else {
            // default to upcoming
            $query->orderBy('scheduled_at', 'asc');
        }

        $appointments = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Appointment::where('firm_id', $firmId)->count(),
            'today' => Appointment::where('firm_id', $firmId)->whereDate('scheduled_at', today())->count(),
            'upcoming' => Appointment::where('firm_id', $firmId)->where('scheduled_at', '>=', now())->count(),
            'hearings' => Appointment::where('firm_id', $firmId)->where('type', 'court_appearance')->count(),
        ];

        return view('appointments.index', compact('appointments', 'stats'));
    }

    /**
     * Show form to schedule appointment.
     */
    public function create(Request $request): View
    {
        $firmId = Auth::user()->firm_id;

        $matters = Matter::where('firm_id', $firmId)->latest()->get();
        $clients = Client::where('firm_id', $firmId)->orderBy('name')->get();
        $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['admin', 'partner', 'lawyer', 'associate'])->get();

        $selectedMatterId = $request->get('matter_id');
        $selectedClientId = $request->get('client_id');

        return view('appointments.create', compact('matters', 'clients', 'attorneys', 'selectedMatterId', 'selectedClientId'));
    }

    /**
     * Store scheduled appointment.
     */
    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        $validated = $request->validate([
            'matter_id' => 'nullable|exists:matters,id',
            'client_id' => 'nullable|exists:clients,id',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:client_consultation,court_appearance,case_conference,mediation,briefing',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            'firm_id' => $firmId,
            'matter_id' => $validated['matter_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'type' => $validated['type'],
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        // Dispatch email notification to client if client exists
        if ($appointment->client_id) {
            $client = Client::find($appointment->client_id);
            if ($client && ! empty($client->email)) {
                try {
                    Mail::to($client->email)->send(new AppointmentScheduledMail($appointment));
                } catch (\Throwable $e) {
                    Log::warning('Could not dispatch appointment email: '.$e->getMessage());
                }
            }
        }

        return redirect()->route('appointments.index')
            ->with('success', "Appointment '{$appointment->title}' was successfully booked.");
    }

    /**
     * Show appointment details.
     */
    public function show(Appointment $appointment): View
    {
        $firmId = Auth::user()->firm_id;

        if ($appointment->firm_id !== $firmId) {
            abort(403);
        }

        $appointment->load(['matter', 'client', 'attorney']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Update appointment status (complete, adjourn, cancel).
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($appointment->firm_id !== $firmId) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,adjourned,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        // Dispatch cancellation notice if appointment is cancelled
        if ($validated['status'] === 'cancelled' && $appointment->client_id) {
            $client = Client::find($appointment->client_id);
            if ($client && ! empty($client->email)) {
                try {
                    Mail::to($client->email)->send(new AppointmentCancelledMail($appointment));
                } catch (\Throwable $e) {
                    Log::warning('Could not dispatch appointment cancellation email: '.$e->getMessage());
                }
            }
        }

        return back()->with('success', 'Appointment status updated to '.ucfirst($validated['status']).'.');
    }

    /**
     * Delete appointment.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $firmId = Auth::user()->firm_id;

        if ($appointment->firm_id !== $firmId) {
            abort(403);
        }

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment was removed from docket.');
    }
}
