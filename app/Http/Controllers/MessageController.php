<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        // Fetch matters with messages
        $mattersWithMessages = Matter::where('firm_id', $firmId)
            ->whereHas('messages')
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('is_read', false)->where('sender_id', '!=', $user->id);
            }])
            ->with(['client', 'leadAttorney'])
            ->get();

        $selectedMatterId = $request->get('matter_id', $mattersWithMessages->first()?->id);
        $selectedMatter = $selectedMatterId ? Matter::find($selectedMatterId) : null;

        $messages = collect();
        if ($selectedMatter) {
            // Mark incoming messages as read
            Message::where('matter_id', $selectedMatter->id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            $messages = Message::where('matter_id', $selectedMatter->id)
                ->with('sender')
                ->oldest()
                ->get();
        }

        $allMatters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number'])->get();

        $unreadTotal = Message::where('firm_id', $firmId)
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->count();

        return view('messages.index', compact(
            'mattersWithMessages',
            'selectedMatter',
            'messages',
            'allMatters',
            'unreadTotal'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'body' => 'required|string|max:5000',
            'is_privileged' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;
        $matter = Matter::where('id', $validated['matter_id'])->where('firm_id', $firmId)->firstOrFail();

        $msg = Message::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'is_privileged' => $request->boolean('is_privileged', true),
            'is_read' => false,
        ]);

        MatterActivity::log(
            matter: $matter,
            activityType: 'message_sent',
            description: "Advocate {$user->name} sent a privileged case dispatch",
            subject: $msg,
            userId: $user->id,
            clientId: $matter->client_id
        );

        return redirect()->route('messages.index', ['matter_id' => $matter->id])
            ->with('success', 'Message dispatched.');
    }
}
