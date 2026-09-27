<?php

namespace App\Http\Controllers;

use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MessageController extends Controller
{
    /**
     * Display matter-based message threads console for chambers staff.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        // Fetch matters with threads or messages
        $mattersWithMessages = Matter::where('firm_id', $firmId)
            ->where(function ($q) {
                $q->has('threads')->orHas('messages');
            })
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('is_read', false)->where('sender_id', '!=', $user->id);
            }])
            ->with(['client', 'leadAttorney'])
            ->get();

        $selectedMatterId = $request->get('matter_id', $mattersWithMessages->first()?->id);
        $selectedMatter = $selectedMatterId ? Matter::where('firm_id', $firmId)->find($selectedMatterId) : null;

        $typeFilter = $request->get('type', 'all');
        $threads = collect();
        $selectedThread = null;
        $messages = collect();

        if ($selectedMatter) {
            $threadsQuery = MessageThread::where('matter_id', $selectedMatter->id)
                ->where('firm_id', $firmId)
                ->with(['creator', 'client'])
                ->withCount(['messages as unread_count' => function ($q) use ($user) {
                    $q->where('is_read', false)->where('sender_id', '!=', $user->id);
                }])
                ->orderBy('last_message_at', 'desc');

            if ($typeFilter !== 'all') {
                if ($typeFilter === 'internal') {
                    $threadsQuery->where('is_internal', true);
                } elseif ($typeFilter === 'client') {
                    $threadsQuery->where('is_internal', false);
                } else {
                    $threadsQuery->where('thread_type', $typeFilter);
                }
            }

            $threads = $threadsQuery->get();

            $selectedThreadId = $request->get('thread_id', $threads->first()?->id);
            if ($selectedThreadId) {
                $selectedThread = $threads->firstWhere('id', $selectedThreadId)
                    ?? MessageThread::where('matter_id', $selectedMatter->id)->find($selectedThreadId);
            }

            if ($selectedThread) {
                // Mark incoming messages as read
                Message::where('thread_id', $selectedThread->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                        'status' => 'read',
                    ]);

                $messages = Message::where('thread_id', $selectedThread->id)
                    ->with(['sender', 'recipient', 'attachments'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        $allMatters = Matter::where('firm_id', $firmId)->select(['id', 'title', 'case_number', 'client_id', 'lead_attorney_id'])->get();

        $unreadTotal = Message::where('firm_id', $firmId)
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->count();

        $matterUsers = $selectedMatter
            ? $selectedMatter->teamMembers->push($selectedMatter->leadAttorney)->filter()->unique('id')
            : User::where('firm_id', $firmId)->get();

        return view('messages.index', compact(
            'mattersWithMessages',
            'selectedMatter',
            'threads',
            'selectedThread',
            'messages',
            'allMatters',
            'unreadTotal',
            'typeFilter',
            'matterUsers'
        ));
    }

    /**
     * Store a reply message in an existing thread.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'thread_id' => 'nullable|exists:message_threads,id',
            'recipient_id' => 'nullable|exists:users,id',
            'body' => 'required|string|max:10000',
            'is_privileged' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|max:25600', // 25MB max
        ]);

        /** @var User $user */
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;
        $matter = Matter::where('id', $validated['matter_id'])->where('firm_id', $firmId)->firstOrFail();

        // Resolve or create thread
        $thread = null;
        if (! empty($validated['thread_id'])) {
            $thread = MessageThread::where('id', $validated['thread_id'])
                ->where('matter_id', $matter->id)
                ->where('firm_id', $firmId)
                ->firstOrFail();
        } else {
            $thread = MessageThread::firstOrCreate(
                [
                    'matter_id' => $matter->id,
                    'firm_id' => $firmId,
                    'is_internal' => false,
                    'thread_type' => MessageThread::TYPE_GENERAL_MATTER,
                ],
                [
                    'client_id' => $matter->client_id,
                    'created_by' => $user->id,
                    'subject' => 'General Matter Communication',
                    'status' => 'open',
                    'last_message_at' => now(),
                ]
            );
        }

        $isInternal = $thread->is_internal;

        $msg = Message::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'recipient_id' => $validated['recipient_id'] ?? ($isInternal ? null : $matter->client?->user_id),
            'subject' => $thread->subject,
            'body' => $validated['body'],
            'is_privileged' => $request->boolean('is_privileged', true),
            'is_internal' => $isInternal,
            'status' => 'sent',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Process attachments
        if ($request->hasFile('attachments')) {
            $disk = config('filesystems.default', 'local');
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('messages/attachments', $disk);
                    } catch (Throwable $e) {
                        $disk = 'local';
                        $path = $file->store('messages/attachments', 'local');
                    }

                    $sha256 = hash_file('sha256', $file->getRealPath());

                    MessageAttachment::create([
                        'firm_id' => $firmId,
                        'message_id' => $msg->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'sha256' => $sha256,
                    ]);

                    if (empty($msg->attachment_path)) {
                        $msg->update([
                            'attachment_path' => $path,
                            'attachment_name' => $file->getClientOriginalName(),
                            'attachment_size' => $file->getSize(),
                            'attachment_mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            }
        }

        $thread->update(['last_message_at' => now()]);

        // Log procedural activity only if safe (not internal)
        if (! $isInternal) {
            MatterActivity::log(
                matter: $matter,
                activityType: 'message_sent',
                description: "Counsel {$user->name} sent a message in thread '{$thread->subject}'",
                subject: $msg,
                userId: $user->id,
                clientId: $matter->client_id
            );
        }

        return redirect()->route('messages.index', [
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
        ])->with('success', 'Message dispatched.');
    }

    /**
     * Start a new matter-based message thread.
     */
    public function storeThread(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'subject' => 'required|string|max:255',
            'thread_type' => 'required|string|in:client_communication,document_request,general_matter_communication,internal_team',
            'recipient_id' => 'nullable|exists:users,id',
            'body' => 'required|string|max:10000',
            'attachments.*' => 'nullable|file|max:25600',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;
        $matter = Matter::where('id', $validated['matter_id'])->where('firm_id', $firmId)->firstOrFail();

        // Technical separation: internal_team threads have is_internal = true
        $isInternal = ($validated['thread_type'] === MessageThread::TYPE_INTERNAL_TEAM);

        $thread = MessageThread::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'client_id' => $isInternal ? null : $matter->client_id,
            'created_by' => $user->id,
            'subject' => $validated['subject'],
            'thread_type' => $validated['thread_type'],
            'is_internal' => $isInternal,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $recipientId = $validated['recipient_id'] ?? ($isInternal ? null : $matter->client?->user_id);

        $msg = Message::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'subject' => $thread->subject,
            'body' => $validated['body'],
            'is_privileged' => true,
            'is_internal' => $isInternal,
            'status' => 'sent',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        if ($request->hasFile('attachments')) {
            $disk = config('filesystems.default', 'local');
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('messages/attachments', $disk);
                    } catch (Throwable $e) {
                        $disk = 'local';
                        $path = $file->store('messages/attachments', 'local');
                    }

                    $sha256 = hash_file('sha256', $file->getRealPath());

                    MessageAttachment::create([
                        'firm_id' => $firmId,
                        'message_id' => $msg->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'sha256' => $sha256,
                    ]);

                    if (empty($msg->attachment_path)) {
                        $msg->update([
                            'attachment_path' => $path,
                            'attachment_name' => $file->getClientOriginalName(),
                            'attachment_size' => $file->getSize(),
                            'attachment_mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            }
        }

        if (! $isInternal) {
            MatterActivity::log(
                matter: $matter,
                activityType: 'message_sent',
                description: "New thread initiated: '{$thread->subject}'",
                subject: $thread,
                userId: $user->id,
                clientId: $matter->client_id
            );
        }

        return redirect()->route('messages.index', [
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
        ])->with('success', "Thread '{$thread->subject}' opened successfully.");
    }

    /**
     * Download message attachment with firm authorization.
     */
    public function downloadAttachment(MessageAttachment $attachment): StreamedResponse|BinaryFileResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($attachment->firm_id !== ($user->firm_id ?? 1)) {
            abort(403, 'Unauthorized attachment access.');
        }

        $defaultDisk = config('filesystems.default', 'local');
        if (Storage::disk($defaultDisk)->exists($attachment->file_path)) {
            return Storage::disk($defaultDisk)->download($attachment->file_path, $attachment->file_name, [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]);
        }

        if ($defaultDisk !== 'local' && Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->download($attachment->file_path, $attachment->file_name, [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]);
        }

        abort(404, 'Attachment file not found on storage.');
    }
}
