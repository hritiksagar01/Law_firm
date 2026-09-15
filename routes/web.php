<?php

use App\Http\Controllers\AuthController;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\Message;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('demo-login');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Practice & Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Default redirect based on role
    Route::get('/', function () {
        if (Auth::user()->isClient()) {
            return redirect()->route('portal.dashboard');
        }
        return redirect()->route('dashboard');
    });

    // Firm Workspace Routes (Strictly for Advocates & Staff)
    Route::middleware(['firm.staff'])->group(function () {

        // Attorney Operations Hub
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

    // Matters Directory
    Route::get('/matters', function (Request $request) {
        $query = Matter::with(['client', 'leadAttorney']);
        if ($request->has('stage') && $request->stage != 'all') {
            $query->where('stage', $request->stage);
        }
        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('case_number', 'like', "%{$q}%");
            });
        }
        $matters = $query->latest()->get();
        return view('matters.index', compact('matters'));
    })->name('matters.index');

    // Create Matter
    Route::get('/matters/create', function () {
        $clients = Client::all();
        $attorneys = User::whereIn('role', ['partner', 'associate'])->get();
        return view('matters.create', compact('clients', 'attorneys'));
    })->name('matters.create');

    Route::post('/matters', function (Request $request) {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'practice_area' => 'required|string',
            'court_name' => 'nullable|string',
            'judge_name' => 'nullable|string',
            'stage' => 'required|string',
            'lead_attorney_id' => 'required|exists:users,id',
            'billing_type' => 'required|string',
            'budget' => 'nullable|numeric',
        ]);

        $year = date('Y');
        $randomSeq = str_pad((string) (Matter::count() + 1), 4, '0', STR_PAD_LEFT);
        $caseNumber = "HO-{$year}-{$randomSeq}";

        $matter = Matter::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'client_id' => $validated['client_id'],
            'case_number' => $caseNumber,
            'title' => $validated['title'],
            'practice_area' => $validated['practice_area'],
            'court_name' => $validated['court_name'],
            'judge_name' => $validated['judge_name'],
            'stage' => $validated['stage'],
            'status' => 'active',
            'lead_attorney_id' => $validated['lead_attorney_id'],
            'billing_type' => $validated['billing_type'],
            'budget' => $validated['budget'] ?? 100000,
            'opened_at' => now()->toDateString(),
        ]);

        return redirect()->route('matters.show', $matter->id)
            ->with('success', "Matter {$caseNumber} ({$matter->title}) has been successfully opened.");
    })->name('matters.store');

    // Matter Detail Dossier
    Route::get('/matters/{matter}', function (Matter $matter) {
        $matter->load([
            'client', 
            'leadAttorney', 
            'documents', 
            'timeEntries.user', 
            'events', 
            'tasks.assignee', 
            'messages.sender'
        ]);
        return view('matters.show', compact('matter'));
    })->name('matters.show');

    // Clients Directory & Creation
    Route::get('/clients', function () {
        $clients = Client::with('matters')->get();
        return view('clients.index', compact('clients'));
    })->name('clients.index');

    Route::post('/clients', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:corporate,individual',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'trust_balance' => 'nullable|numeric|min:0',
        ]);

        Client::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'tax_id' => $validated['tax_id'],
            'trust_balance' => $validated['trust_balance'] ?? 0.00,
            'status' => 'active',
        ]);

        return redirect()->route('clients.index')->with('success', "Client '{$validated['name']}' added to firm roster.");
    })->name('clients.store');

    // Documents Vault
    Route::get('/documents', function () {
        $documents = Document::with(['matter', 'uploader'])->latest()->get();
        $matters = Matter::all();
        return view('documents.index', compact('documents', 'matters'));
    })->name('documents.index');

    Route::post('/documents/upload', function (Request $request) {
        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'privilege' => 'required|string',
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $path = $file->store('documents', 'local');

        Document::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $request->matter_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'sha256' => $sha256,
            'category' => $request->category,
            'privilege' => $request->privilege,
            'version' => 1,
        ]);

        return back()->with('success', 'Filing securely uploaded and SHA-256 authenticated.');
    })->name('documents.upload');

    Route::get('/documents/{document}/download', function (Document $document) {
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->filename);
        }

        // Demo sample fallback
        return response("DEMO VERIFIED LEGAL FILING\nDocument: {$document->title}\nSHA256: {$document->sha256}\nChambers: Chen & Sterling LLP", 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$document->filename}.txt\"",
        ]);
    })->name('documents.download');

    // Chambers Calendar & Court Docket
    Route::get('/calendar', function () {
        $events = Event::with('matter')->orderBy('start_time')->get();
        $matters = Matter::all();
        return view('calendar.index', compact('events', 'matters'));
    })->name('calendar.index');

    Route::post('/calendar', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'start_time' => 'required|date',
            'matter_id' => 'nullable|exists:matters,id',
            'location' => 'nullable|string',
            'is_statutory_deadline' => 'nullable',
        ]);

        Event::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $validated['matter_id'] ?? null,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'event_type' => $validated['event_type'],
            'start_time' => $validated['start_time'],
            'location' => $validated['location'],
            'is_statutory_deadline' => $request->has('is_statutory_deadline'),
        ]);

        return back()->with('success', 'Docket event registered on chambers calendar.');
    })->name('calendar.store');

    // Billing & Trust Accounting
    Route::get('/billing', function () {
        $timeEntries = TimeEntry::with(['matter', 'user'])->latest()->get();
        $invoices = Invoice::with(['client', 'matter'])->latest()->get();
        $clients = Client::all();
        $matters = Matter::all();
        return view('billing.index', compact('timeEntries', 'invoices', 'clients', 'matters'));
    })->name('billing.index');

    // Log Billable Time Entry
    Route::post('/time-entries', function (Request $request) {
        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'hours' => 'required|numeric|min:0.1',
            'activity_code' => 'required|string',
            'narrative' => 'nullable|string',
            'is_billable' => 'nullable',
        ]);

        $activityNames = [
            'L120' => 'Analysis & Strategy',
            'L110' => 'Fact Investigation',
            'L330' => 'Depositions & Prep',
            'A104' => 'Document Review',
            'B110' => 'Written Pleadings',
        ];

        $rate = Auth::user()->hourly_rate ?? 550.00;
        $hours = (float) $validated['hours'];

        TimeEntry::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $validated['matter_id'],
            'user_id' => Auth::id(),
            'hours' => $hours,
            'rate' => $rate,
            'total_amount' => $hours * $rate,
            'activity_code' => $validated['activity_code'],
            'activity_name' => $activityNames[$validated['activity_code']] ?? 'Legal Services',
            'narrative' => $validated['narrative'] ?? 'Professional legal services rendered.',
            'is_billable' => $request->has('is_billable') ? true : false,
            'status' => 'unbilled',
            'entry_date' => now()->toDateString(),
        ]);

        return back()->with('success', "Logged {$hours} hrs billable under {$validated['activity_code']}.");
    })->name('time-entries.store');

    // Generate Invoice from WIP unbilled entries
    Route::post('/invoices/generate', function (Request $request) {
        $matter = Matter::with('client')->find($request->matter_id) ?? Matter::first();
        
        $unbilledAmount = TimeEntry::where('matter_id', $matter->id)
            ->where('status', 'unbilled')
            ->sum('total_amount');

        if ($unbilledAmount <= 0) {
            $unbilledAmount = 2412.50; // default sample
        }

        $invNum = 'INV-2026-00' . rand(50, 99);

        $invoice = Invoice::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'invoice_number' => $invNum,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $unbilledAmount,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => $unbilledAmount,
            'amount_paid' => 0.00,
            'status' => 'sent',
        ]);

        TimeEntry::where('matter_id', $matter->id)->where('status', 'unbilled')->update(['status' => 'billed']);

        return back()->with('success', "Invoice {$invNum} generated for \${$unbilledAmount} and dispatched to {$matter->client->name}.");
    })->name('invoices.generate');

    // Toggle Task status
    Route::post('/tasks/{task}/toggle', function (Task $task) {
        $task->status = ($task->status === 'completed') ? 'todo' : 'completed';
        $task->save();
        return back()->with('success', "Task status updated.");
    })->name('tasks.toggle');

    // Send Matter Privileged Message
    Route::post('/messages', function (Request $request) {
        $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'body' => 'required|string|max:1000',
        ]);

        Message::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $request->matter_id,
            'sender_id' => Auth::id(),
            'body' => $request->body,
            'is_privileged' => true,
        ]);

        return back()->with('success', 'Privileged attorney-client communication dispatched.');
    })->name('messages.store');

    // Pay / Settle Invoice
    Route::post('/invoices/{invoice}/pay', function (Invoice $invoice) {
        $invoice->status = 'paid';
        $invoice->amount_paid = $invoice->total_amount;
        $invoice->save();
        return back()->with('success', "Invoice {$invoice->invoice_number} settled in full ($" . number_format($invoice->total_amount, 2) . ").");
    })->name('invoices.pay');

    // Client Trust Retainer Deposit
    Route::post('/clients/{client}/trust-deposit', function (Request $request, Client $client) {
        $request->validate(['amount' => 'required|numeric|min:50']);
        $client->trust_balance += (float) $request->amount;
        $client->save();
        return back()->with('success', "IOLTA escrow deposit of $" . number_format($request->amount, 2) . " credited to {$client->name}.");
    })->name('clients.trust-deposit');

    // Tasks & Productivity Hub
    Route::get('/tasks', function () {
        $tasks = Task::with(['assignee', 'matter'])->latest()->get();
        $attorneys = User::whereIn('role', ['partner', 'associate', 'paralegal'])->get();
        $matters = Matter::all();
        return view('tasks.index', compact('tasks', 'attorneys', 'matters'));
    })->name('tasks.index');

    Route::post('/tasks', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:urgent,high,normal,low',
            'due_date' => 'required|date',
            'matter_id' => 'nullable|exists:matters,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        Task::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'matter_id' => $validated['matter_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? Auth::id(),
            'created_by' => Auth::id() ?? 1,
            'title' => $validated['title'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'],
            'status' => 'todo',
        ]);

        return back()->with('success', 'Litigation task successfully logged.');
    })->name('tasks.store');

    // Global Search Engine
    Route::get('/search', function (Request $request) {
        $q = trim($request->input('q', ''));
        if (empty($q)) {
            return redirect()->route('matters.index');
        }

        $matters = Matter::where('title', 'like', "%{$q}%")
            ->orWhere('case_number', 'like', "%{$q}%")
            ->orWhere('court_name', 'like', "%{$q}%")
            ->with('client')->get();

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")->get();

        $documents = Document::where('title', 'like', "%{$q}%")
            ->orWhere('filename', 'like', "%{$q}%")
            ->with('matter')->get();

        $tasks = Task::where('title', 'like', "%{$q}%")->with(['assignee', 'matter'])->get();

        return view('search', compact('q', 'matters', 'clients', 'documents', 'tasks'));
    })->name('search');

    // Firm & Team Settings
    Route::get('/settings', function () {
        $firm = Auth::user()->firm ?? \App\Models\Firm::first();
        $users = User::where('firm_id', $firm->id)->get();
        return view('settings.index', compact('firm', 'users'));
    })->name('settings.index');

    Route::post('/settings/team', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:partner,associate,paralegal',
            'hourly_rate' => 'required|numeric|min:50',
        ]);

        $user = User::create([
            'firm_id' => Auth::user()->firm_id ?? 1,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => $validated['role'],
            'title' => ucfirst($validated['role']),
            'hourly_rate' => $validated['hourly_rate'],
            'is_active' => true,
        ]);

        return back()->with('success', "Team member {$user->name} added with billing rate \${$user->hourly_rate}/hr.");
    })->name('settings.team');

    // Daily Broadsheet Executive Intelligence
    Route::get('/briefing', function () {
        $matters = Matter::with(['client', 'leadAttorney'])->get();
        $events = Event::with('matter')->orderBy('start_time')->get();
        $tasks = Task::with(['assignee', 'matter'])->get();
        return view('briefing', compact('matters', 'events', 'tasks'));
    })->name('briefing');

    }); // End of Firm Workspace Routes Group

    // Client Portal Suite (F-08 & F-07)
    Route::middleware(['portal.client'])->prefix('portal')->name('portal.')->group(function () {
        
        Route::get('/', function () {
            return redirect()->route('portal.dashboard');
        })->name('index');

        // Portal Dashboard Overview
        Route::get('/dashboard', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $matters = Matter::where('client_id', $client->id)->with(['documents', 'leadAttorney', 'documentRequests'])->get();
            $documentRequests = DocumentRequest::where('client_id', $client->id)->with('matter')->latest()->get();
            $invoices = Invoice::where('client_id', $client->id)->latest()->get();
            $events = Event::whereIn('matter_id', $matters->pluck('id'))->orderBy('start_time')->get();

            return view('portal.dashboard', compact('client', 'matters', 'documentRequests', 'invoices', 'events'));
        })->name('dashboard');

        // My Cases / Matters
        Route::get('/matters', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $matters = Matter::where('client_id', $client->id)->with(['documents', 'leadAttorney', 'documentRequests'])->latest()->get();
            return view('portal.matters.index', compact('client', 'matters'));
        })->name('matters.index');

        Route::get('/matters/{matter}', function (Matter $matter) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            if ($matter->client_id !== $client->id) {
                abort(403, 'Unauthorized case dossier.');
            }

            $matter->load(['documents', 'leadAttorney', 'documentRequests', 'events', 'messages.sender']);
            return view('portal.matters.show', compact('client', 'matter'));
        })->name('matters.show');

        // Document Requests Workflow (F-07)
        Route::get('/requests', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $requests = DocumentRequest::where('client_id', $client->id)->with(['matter', 'requestedBy'])->latest()->get();
            return view('portal.requests.index', compact('client', 'requests'));
        })->name('requests.index');

        Route::post('/requests/{request}/upload', function (Request $httpRequest, DocumentRequest $request) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            if ($request->client_id !== $client->id) {
                abort(403, 'Unauthorized document request.');
            }

            $httpRequest->validate([
                'file' => 'required|file|max:51200', // 50MB
                'client_notes' => 'nullable|string|max:1000',
            ]);

            $file = $httpRequest->file('file');
            $sha256 = hash_file('sha256', $file->getRealPath());
            $path = $file->store('documents/client_uploads', 'local');

            $doc = Document::create([
                'firm_id' => $request->firm_id,
                'matter_id' => $request->matter_id,
                'user_id' => $user->id,
                'title' => $request->title . ' (Client Submission)',
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'sha256' => $sha256,
                'category' => 'Client Submissions',
                'privilege' => 'Confidential',
                'version' => 1,
            ]);

            $request->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'document_id' => $doc->id,
                'client_notes' => $httpRequest->client_notes,
            ]);

            return back()->with('success', "Document '{$doc->filename}' submitted to legal counsel.");
        })->name('requests.upload');

        // Case Documents Repository
        Route::get('/documents', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $matterIds = Matter::where('client_id', $client->id)->pluck('id');
            $documents = Document::whereIn('matter_id', $matterIds)->with('matter')->latest()->get();
            $matters = Matter::where('client_id', $client->id)->get();

            return view('portal.documents.index', compact('client', 'documents', 'matters'));
        })->name('documents.index');

        Route::post('/documents/upload', function (Request $request) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $request->validate([
                'matter_id' => 'required|exists:matters,id',
                'title' => 'required|string|max:255',
                'category' => 'required|string',
                'file' => 'required|file|max:51200',
            ]);

            $matter = Matter::where('id', $request->matter_id)->where('client_id', $client->id)->firstOrFail();

            $file = $request->file('file');
            $sha256 = hash_file('sha256', $file->getRealPath());
            $path = $file->store('documents/client_uploads', 'local');

            Document::create([
                'firm_id' => $matter->firm_id,
                'matter_id' => $matter->id,
                'user_id' => $user->id,
                'title' => $request->title,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'sha256' => $sha256,
                'category' => $request->category ?? 'Client Submissions',
                'privilege' => 'Confidential',
                'version' => 1,
            ]);

            return back()->with('success', 'Document uploaded to case file.');
        })->name('documents.upload');

        // Counsel Communications / Messages
        Route::get('/messages', function (Request $request) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $matters = Matter::where('client_id', $client->id)->with(['leadAttorney', 'messages.sender'])->get();
            $selectedMatterId = $request->input('matter_id', $matters->first()->id ?? null);
            $selectedMatter = $matters->firstWhere('id', $selectedMatterId);

            return view('portal.messages.index', compact('client', 'matters', 'selectedMatter'));
        })->name('messages.index');

        Route::post('/messages', function (Request $request) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

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
        })->name('messages.store');

        // Invoices & Retainer Ledger
        Route::get('/invoices', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $invoices = Invoice::where('client_id', $client->id)->with('matter')->latest()->get();
            return view('portal.invoices.index', compact('client', 'invoices'));
        })->name('invoices.index');

        Route::post('/invoices/{invoice}/pay', function (Request $request, Invoice $invoice) {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

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

            return back()->with('success', "Invoice {$invoice->invoice_number} settled in full.");
        })->name('invoices.pay');

        // Hearings & Calendar
        Route::get('/calendar', function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first() 
                ?? Client::where('email', $user->email)->first() 
                ?? Client::first();

            $matterIds = Matter::where('client_id', $client->id)->pluck('id');
            $events = Event::whereIn('matter_id', $matterIds)->with('matter')->orderBy('start_time')->get();

            return view('portal.calendar.index', compact('client', 'events'));
        })->name('calendar.index');
    });

    // Super Admin Platform Routes (Protected by auth and admin.super)
    Route::middleware(['admin.super'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            $firms = \App\Models\Firm::withCount(['users', 'matters', 'documents'])->get();
            return view('admin.dashboard', compact('firms'));
        })->name('dashboard');

        Route::get('/settings/environment', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.environment');
        Route::post('/settings/environment', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.environment.update');
        Route::post('/settings/environment/test-db', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'testDatabase'])->name('settings.environment.test-db');
        Route::post('/settings/environment/test-s3', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'testS3'])->name('settings.environment.test-s3');
        Route::post('/settings/environment/test-mail', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'testMail'])->name('settings.environment.test-mail');
        Route::post('/settings/environment/run-migrations', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'runMigrations'])->name('settings.environment.run-migrations');
        Route::post('/settings/environment/restore-backup', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'restoreBackup'])->name('settings.environment.restore-backup');
    });
});
