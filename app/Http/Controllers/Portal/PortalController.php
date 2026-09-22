<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\Message;
use App\Services\LegalPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PortalController extends Controller
{
    /**
     * Retrieve the currently authenticated client record.
     */
    protected function getClient(): Client
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first()
            ?? Client::where('email', $user->email)->first();

        if (! $client) {
            abort(403, 'No client representation record is linked to this user account.');
        }

        return $client;
    }

    /**
     * Client Portal Dashboard Overview (F-08).
     */
    public function dashboard()
    {
        $client = $this->getClient();

        $matters = Matter::where('client_id', $client->id)
            ->with(['documents', 'leadAttorney', 'documentRequests', 'events'])
            ->latest()
            ->get();

        $documentRequests = DocumentRequest::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        $invoices = Invoice::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        $matterIds = $matters->pluck('id');
        $events = Event::whereIn('matter_id', $matterIds)
            ->with('matter')
            ->orderBy('start_time')
            ->get();

        $documentsCount = Document::whereIn('matter_id', $matterIds)
            ->where('is_client_visible', true)
            ->count();

        return view('portal.dashboard', compact('client', 'matters', 'documentRequests', 'invoices', 'events', 'documentsCount'));
    }

    /**
     * My Cases & Court Dockets List.
     */
    public function matters()
    {
        $client = $this->getClient();

        $matters = Matter::where('client_id', $client->id)
            ->with(['documents', 'leadAttorney', 'documentRequests', 'events'])
            ->latest()
            ->get();

        return view('portal.matters.index', compact('client', 'matters'));
    }

    /**
     * Case Dossier Detail View.
     */
    public function matterShow(Matter $matter)
    {
        $client = $this->getClient();

        if ($matter->client_id !== $client->id) {
            abort(403, 'Unauthorized case dossier.');
        }

        $matter->load([
            'documents' => fn ($q) => $q->where('is_client_visible', true),
            'leadAttorney',
            'documentRequests.requestedBy',
            'events',
            'messages.sender',
        ]);

        return view('portal.matters.show', compact('client', 'matter'));
    }

    /**
     * Document Requests Workflow (F-07).
     */
    public function requests(Request $request)
    {
        $client = $this->getClient();

        $requests = DocumentRequest::where('client_id', $client->id)
            ->with(['matter', 'requestedBy'])
            ->latest()
            ->get();

        return view('portal.requests.index', compact('client', 'requests'));
    }

    /**
     * Upload File Against Specific Document Request.
     */
    public function uploadRequest(Request $httpRequest, DocumentRequest $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        if ($request->client_id !== $client->id) {
            abort(403, 'Unauthorized document request.');
        }

        $httpRequest->validate([
            'file' => 'required|file|max:51200', // 50MB
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $file = $httpRequest->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $disk = config('filesystems.default', 'local');
        $path = $file->store('documents/client_uploads', $disk);

        $doc = Document::create([
            'firm_id' => $request->firm_id,
            'matter_id' => $request->matter_id,
            'user_id' => $user->id,
            'title' => $request->title.' (Client Submission)',
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'sha256' => $sha256,
            'category' => 'Client Submissions',
            'privilege' => 'Confidential',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        $request->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'document_id' => $doc->id,
            'client_notes' => $httpRequest->client_notes,
        ]);

        return back()->with('success', "Document '{$doc->filename}' submitted to legal counsel.");
    }

    /**
     * Case Documents Repository & Vault.
     */
    public function documents(Request $request)
    {
        $client = $this->getClient();

        $matterIds = Matter::where('client_id', $client->id)->pluck('id');
        $documents = Document::whereIn('matter_id', $matterIds)
            ->where('is_client_visible', true)
            ->with('matter')
            ->latest()
            ->get();
        $matters = Matter::where('client_id', $client->id)->get();

        return view('portal.documents.index', compact('client', 'documents', 'matters'));
    }

    /**
     * Client Supplementary Document Upload.
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        if ($request->hasFile('file') && ! $request->file('file')->isValid()) {
            $errorCode = $request->file('file')->getError();
            $maxUpload = ini_get('upload_max_filesize');
            $msg = match ($errorCode) {
                UPLOAD_ERR_INI_SIZE => "Uploaded file exceeds server limit ({$maxUpload}). Please select a file smaller than {$maxUpload}.",
                default => "File upload failed with error code {$errorCode}."
            };

            return back()->withInput()->with('error', $msg);
        }

        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'file' => 'required|file|max:51200',
        ]);

        $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $disk = config('filesystems.default', 'local');
        $path = $file->store('documents/client_uploads', $disk);

        Document::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'sha256' => $sha256,
            'category' => $request->category ?? 'Client Submissions',
            'privilege' => 'Confidential',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        return back()->with('success', 'Document uploaded to case file.');
    }

    /**
     * Download Privileged Document with Strict Access Isolation.
     */
    public function downloadDocument(Document $document)
    {
        $client = $this->getClient();

        // STRICT DATA ISOLATION: The document must belong to a matter owned by this client
        $matter = Matter::where('id', $document->matter_id)->where('client_id', $client->id)->first();
        if (! $matter) {
            abort(403, 'Unauthorized document access: This filing does not belong to your case dossiers.');
        }

        // Document must be designated client-visible
        if ($document->is_client_visible === false) {
            abort(403, 'Unauthorized: This filing is restricted to internal chambers work product.');
        }

        $defaultDisk = config('filesystems.default', 'local');
        if ($document->file_path) {
            try {
                if (Storage::disk($defaultDisk)->exists($document->file_path)) {
                    return Storage::disk($defaultDisk)->download($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                    ]);
                }
            } catch (Throwable $e) {
            }

            try {
                if ($defaultDisk !== 'local' && Storage::disk('local')->exists($document->file_path)) {
                    return Storage::disk('local')->download($document->file_path, $document->filename, [
                        'Content-Type' => $document->mime_type ?: 'application/pdf',
                    ]);
                }
            } catch (Throwable $e) {
            }
        }

        // Compliant legal PDF fallback stream
        $pdfContent = LegalPdfGenerator::forDocument($document);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$document->filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    /**
     * Privileged Counsel Messages Channel (F-09).
     */
    public function messages(Request $request)
    {
        $client = $this->getClient();

        $matters = Matter::where('client_id', $client->id)->with(['leadAttorney', 'messages.sender'])->get();
        $selectedMatterId = $request->input('matter_id', $matters->first()->id ?? null);
        $selectedMatter = $matters->firstWhere('id', $selectedMatterId);

        return view('portal.messages.index', compact('client', 'matters', 'selectedMatter'));
    }

    /**
     * Store Privileged Message to Counsel.
     */
    public function storeMessage(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClient();

        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'body' => 'required|string|max:1000',
        ]);

        $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

        Message::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'sender_id' => $user->id,
            'body' => $request->body,
            'is_privileged' => true,
        ]);

        return back()->with('success', 'Message dispatched to legal counsel.');
    }

    /**
     * Litigation Calendar & Cause List Appearances (F-13).
     */
    public function calendar()
    {
        $client = $this->getClient();

        $matterIds = Matter::where('client_id', $client->id)->pluck('id');
        $events = Event::whereIn('matter_id', $matterIds)
            ->with('matter')
            ->orderBy('start_time')
            ->get();

        return view('portal.calendar.index', compact('client', 'events'));
    }

    /**
     * Client Invoices & Advance Retainer Ledger (F-20).
     */
    public function invoices()
    {
        $client = $this->getClient();

        $invoices = Invoice::where('client_id', $client->id)
            ->with('matter')
            ->latest()
            ->get();

        return view('portal.invoices.index', compact('client', 'invoices'));
    }

    /**
     * Settle Outstanding Fee Bill.
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $client = $this->getClient();

        if ($invoice->client_id !== $client->id) {
            abort(403, 'Unauthorized invoice.');
        }

        if ($request->payment_method === 'retainer') {
            $client->decrement('trust_balance', $invoice->total_amount);
        }

        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->total_amount,
        ]);

        return back()->with('success', "Fee Bill {$invoice->invoice_number} settled successfully.");
    }

    /**
     * Client Account & KYC Profile Settings.
     */
    public function settings()
    {
        $client = $this->getClient();
        $client->load('members');

        return view('portal.settings', compact('client'));
    }

    /**
     * Update Client Account Preferences.
     */
    public function updateSettings(Request $request)
    {
        $client = $this->getClient();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $client->update($validated);

        return back()->with('success', 'Account contact details updated successfully.');
    }
}
