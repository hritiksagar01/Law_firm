<?php

namespace App\Http\Controllers;

use App\Mail\DocumentRequestedMail;
use App\Mail\DocumentReviewResultMail;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Matter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DocumentRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $matter = Matter::where('id', $validated['matter_id'])->where('firm_id', $firmId)->firstOrFail();
        $client = Client::where('id', $validated['client_id'])->where('firm_id', $firmId)->firstOrFail();

        $docRequest = DocumentRequest::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'requested_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'KYC & Identification',
            'priority' => $validated['priority'] ?? 'normal',
            'due_date' => $validated['due_date'] ?? now()->addDays(7),
            'status' => 'pending',
        ]);

        if (! empty($client->email)) {
            try {
                Mail::to($client->email)->send(new DocumentRequestedMail($docRequest));
            } catch (Throwable $e) {
                Log::warning('Could not dispatch document request email: '.$e->getMessage());
            }
        }

        return back()->with('success', "Document request '{$docRequest->title}' dispatched for case {$matter->case_number}.");
    }

    public function review(Request $httpRequest, DocumentRequest $documentRequest): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($documentRequest->firm_id !== $firmId) {
            abort(403, 'Unauthorized document request review.');
        }

        $validated = $httpRequest->validate([
            'status' => 'required|in:under_review,completed,rejected',
            'review_notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $documentRequest->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'rejection_reason' => $validated['status'] === 'rejected' ? ($validated['rejection_reason'] ?: $validated['review_notes']) : null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        // If completed and document exists, ensure file is permanently organized into Matter Vault
        if ($validated['status'] === 'completed' && $documentRequest->document) {
            $documentRequest->document->update([
                'category' => $documentRequest->category ?: 'Client Submissions',
                'is_client_visible' => true,
            ]);
        }

        // Notify client if email exists
        if (! empty($documentRequest->client->email)) {
            try {
                $action = $validated['status'] === 'completed' ? 'accepted' : 'rejected';
                Mail::to($documentRequest->client->email)->send(new DocumentReviewResultMail($documentRequest, $action));
            } catch (Throwable $e) {
                Log::warning('Could not dispatch document review email: '.$e->getMessage());
            }
        }

        $statusMsg = $validated['status'] === 'completed' ? 'accepted and filed into Matter Vault' : 'marked as '.ucfirst($validated['status']);

        return back()->with('success', "Document submission {$statusMsg}.");
    }

    public function assistedUpload(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($documentRequest->firm_id !== $firmId) {
            abort(403, 'Unauthorized document intake.');
        }

        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'clerk_notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $disk = config('filesystems.default', 'local');
        $path = $file->store('documents/assisted_intake', $disk);

        $doc = Document::create([
            'firm_id' => $firmId,
            'matter_id' => $documentRequest->matter_id,
            'user_id' => Auth::id(),
            'title' => $documentRequest->title.' (Chamber Physical Intake)',
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'sha256' => $sha256,
            'category' => $documentRequest->category ?: 'Client Submissions',
            'privilege' => 'Attorney-Client',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        $note = 'Physically submitted by client in chamber and scanned by '.Auth::user()->name.'.';
        if ($request->clerk_notes) {
            $note .= ' Note: '.$request->clerk_notes;
        }

        $documentRequest->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'document_id' => $doc->id,
            'is_assisted_submission' => true,
            'assisted_by_user_id' => Auth::id(),
            'client_notes' => $note,
        ]);

        return back()->with('success', "Physical document '{$doc->filename}' scanned, verified (SHA-256), and linked to request.");
    }
}
