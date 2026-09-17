<?php

use App\Http\Controllers\AuthController;
use App\Mail\DocumentRequestedMail;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

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

    // User Profile & Password Security (Available to all authenticated personnel and clients)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password');

    // Firm Workspace Routes (Strictly for Advocates & Staff)
    Route::middleware(['firm.staff'])->group(function () {

        // Personnel & Practice Groups Administration
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('user-groups', \App\Http\Controllers\UserGroupController::class);

        // Legal Opinions & Strategy Advisory
        Route::resource('opinions', \App\Http\Controllers\OpinionController::class);
        Route::post('/opinions/{opinion}/change-status', [\App\Http\Controllers\OpinionController::class, 'changeStatus'])->name('opinions.change-status');

        // Appointments, Consultations & Court Hearings
        Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
        Route::post('/appointments/{appointment}/status', [\App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('appointments.update-status');

        // Attorney Operations Hub
        Route::get('/dashboard', function () {
            $firmId = Auth::user()->firm_id ?? 1;
            $mattersCount = Matter::where('firm_id', $firmId)->count();
            $eventsCount = Event::where('firm_id', $firmId)->count();
            $tasksCount = Task::where('firm_id', $firmId)->count();
            $clientsCount = Client::where('firm_id', $firmId)->count();
            $matters = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney'])->latest()->get();
            $events = Event::where('firm_id', $firmId)->with('matter')->orderBy('start_time')->get();
            $tasks = Task::where('firm_id', $firmId)->with(['assignee', 'matter'])->get();

            return view('dashboard', compact(
                'mattersCount',
                'eventsCount',
                'tasksCount',
                'clientsCount',
                'matters',
                'events',
                'tasks'
            ));
        })->name('dashboard');

        // Matters Directory
        Route::get('/matters', function (Request $request) {
            $firmId = Auth::user()->firm_id ?? 1;
            $query = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney']);
            if ($request->has('stage') && $request->stage != 'all') {
                $query->where('stage', $request->stage);
            }
            if ($request->has('q') && !empty($request->q)) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('case_number', 'like', "%{$q}%");
                });
            }
            $user = Auth::user();
            if (!in_array($user->role, ['superadmin', 'partner'])) {
                // Associate / paralegal: only matters where they are lead attorney or team member
                $query->where(function ($q) use ($user) {
                    $q->where('lead_attorney_id', $user->id)
                        ->orWhereHas('users', fn($uq) => $uq->where('users.id', $user->id));
                });
            }
            $matters = $query->latest()->get();
            $totalCount = $matters->count();
            $discoveryCount = $matters->where('stage', 'Discovery')->count();
            $pleadingsCount = $matters->where('stage', 'Pleadings')->count();
            $preTrialCount = $matters->where('stage', 'Pre-Trial')->count();
            return view('matters.index', compact('matters', 'totalCount', 'discoveryCount', 'pleadingsCount', 'preTrialCount'));
        })->name('matters.index');

        // Create Matter
        Route::get('/matters/create', function () {
            $firmId = Auth::user()->firm_id ?? 1;
            $clients = Client::where('firm_id', $firmId)->get();
            $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate'])->get();
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
            $randomSeq = str_pad((string) (Matter::where('firm_id', Auth::user()->firm_id ?? 1)->count() + 1), 4, '0', STR_PAD_LEFT);
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

            // Auto-assign lead attorney to matter team
            $matter->users()->syncWithoutDetaching([$validated['lead_attorney_id']]);

            return redirect()->route('matters.show', $matter->id)
                ->with('success', "Matter {$caseNumber} ({$matter->title}) has been successfully opened.");
        })->name('matters.store');

        // Matter Detail Dossier
        Route::get('/matters/{matter}', function (Matter $matter) {
            $user = Auth::user();
            if ($matter->firm_id !== ($user->firm_id ?? 1)) {
                abort(403, 'Unauthorized case dossier.');
            }

            // Lawyer-level access restriction: non-partners must be assigned
            if (!in_array($user->role, ['superadmin', 'partner'])) {
                $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
                if (!$isAssigned) {
                    abort(403, 'Unauthorized: You are not assigned to this case dossier.');
                }
            }

            $matter->load([
                'client',
                'leadAttorney',
                'documents',
                'opinions.author',
                'appointments.attorney',
                'events',
                'tasks.assignee',
                'messages.sender'
            ]);
            return view('matters.show', compact('matter'));
        })->name('matters.show');

        // Clients Directory & Creation
        Route::get('/clients', function () {
            $user = Auth::user();
            $firmId = $user->firm_id ?? 1;

            if (in_array($user->role, ['superadmin', 'partner'])) {
                $clients = Client::where('firm_id', $firmId)->with(['matters', 'primaryAttorney'])->get();
            } else {
                // Associate / paralegal: only clients they consult or have assigned matters with
                $clients = Client::where('firm_id', $firmId)
                    ->where(function ($q) use ($user) {
                        $q->where('primary_attorney_id', $user->id)
                            ->orWhereHas('matters', function ($mq) use ($user) {
                                $mq->where('lead_attorney_id', $user->id)
                                    ->orWhereHas('users', fn($uq) => $uq->where('users.id', $user->id));
                            });
                    })
                    ->with(['matters', 'primaryAttorney'])->get();
            }

            $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate'])->get();
            return view('clients.index', compact('clients', 'attorneys'));
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
                'primary_attorney_id' => 'nullable|exists:users,id',
            ]);

            $firmId = Auth::user()->firm_id ?? 1;
            $attorneyId = $validated['primary_attorney_id'] ?? Auth::id();

            $client = Client::create([
                'firm_id' => $firmId,
                'primary_attorney_id' => $attorneyId,
                'type' => $validated['type'],
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'tax_id' => $validated['tax_id'],
                'trust_balance' => $validated['trust_balance'] ?? 0.00,
                'status' => 'active',
            ]);

            // Provision Client Portal user login automatically if requested
            if ($request->boolean('invite_portal', true)) {
                $portalUser = User::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'firm_id' => $firmId,
                        'name' => $validated['contact_person'] ?: $validated['name'],
                        'password' => Hash::make('Client@1234'),
                        'role' => 'client',
                        'title' => 'Client Representative',
                        'phone' => $validated['phone'],
                    ]
                );
                $client->update(['user_id' => $portalUser->id]);

                return redirect()->route('clients.index')
                    ->with('success', "Client '{$validated['name']}' onboarded and invited to Client Portal. Temporary Password: Client@1234");
            }

            return redirect()->route('clients.index')->with('success', "Client '{$validated['name']}' added to firm roster.");
        })->name('clients.store');

        Route::post('/clients/{client}/invite', function (Client $client) {
            $firmId = Auth::user()->firm_id ?? 1;
            if ($client->firm_id !== $firmId) {
                abort(403, 'Unauthorized.');
            }

            $portalUser = User::firstOrCreate(
                ['email' => $client->email],
                [
                    'firm_id' => $firmId,
                    'name' => $client->contact_person ?: $client->name,
                    'password' => Hash::make('Client@1234'),
                    'role' => 'client',
                    'title' => 'Client Representative',
                    'phone' => $client->phone,
                ]
            );

            $client->update(['user_id' => $portalUser->id]);

            return back()->with('success', "Portal access active for {$client->name} ({$client->email}). Password: Client@1234");
        })->name('clients.invite');

        // Documents Vault
        Route::get('/documents', function () {
            $user = Auth::user();
            $firmId = $user->firm_id ?? 1;

            if (in_array($user->role, ['superadmin', 'partner'])) {
                $matters = Matter::where('firm_id', $firmId)->get();
                $documents = Document::where('firm_id', $firmId)->with(['matter', 'uploader'])->latest()->get();
            } else {
                // Associate / paralegal: only documents for matters they are assigned to
                $matters = Matter::where('firm_id', $firmId)
                    ->where(function ($q) use ($user) {
                        $q->where('lead_attorney_id', $user->id)
                            ->orWhereHas('users', fn($uq) => $uq->where('users.id', $user->id));
                    })->get();

                $documents = Document::where('firm_id', $firmId)
                    ->whereIn('matter_id', $matters->pluck('id'))
                    ->with(['matter', 'uploader'])->latest()->get();
            }

            return view('documents.index', compact('documents', 'matters'));
        })->name('documents.index');

        Route::post('/documents/upload', function (Request $request) {
            // Detect PHP-level upload errors before Laravel validation
            if ($request->hasFile('file') && !$request->file('file')->isValid()) {
                $errorCode = $request->file('file')->getError();
                $maxUpload = ini_get('upload_max_filesize');
                $msg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE => "Uploaded file exceeds PHP server limit ({$maxUpload}). Please select a file smaller than {$maxUpload}.",
                    UPLOAD_ERR_FORM_SIZE => "Uploaded file exceeds form limit.",
                    UPLOAD_ERR_PARTIAL => "File was only partially uploaded. Please try again.",
                    UPLOAD_ERR_NO_FILE => "No file was selected for upload.",
                    UPLOAD_ERR_NO_TMP_DIR => "Temporary upload directory missing.",
                    UPLOAD_ERR_CANT_WRITE => "Failed to write file to storage disk.",
                    default => "File upload failed with error code {$errorCode}."
                };
                return back()->withInput()->with('error', $msg);
            }

            $request->validate([
                'matter_id' => 'required|exists:matters,id',
                'title' => 'required|string|max:255',
                'category' => 'required|string',
                'privilege' => 'required|string',
                'is_client_visible' => 'nullable',
                'file' => 'required|file|max:51200', // 50MB max
            ]);

            $user = Auth::user();
            $firmId = $user->firm_id ?? 1;
            $matter = Matter::where('id', $request->matter_id)->where('firm_id', $firmId)->firstOrFail();

            // Lawyer authorization check
            if (!in_array($user->role, ['superadmin', 'partner'])) {
                $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
                if (!$isAssigned) {
                    abort(403, 'Unauthorized: You are not assigned to this case dossier.');
                }
            }

            $file = $request->file('file');
            $sha256 = hash_file('sha256', $file->getRealPath());
            $disk = config('filesystems.default', 'local');
            $path = $file->store('documents', $disk);

            // Determine client visibility
            $isClientVisible = $request->has('is_client_visible')
                ? $request->boolean('is_client_visible')
                : ($request->privilege !== 'Work Product');

            Document::create([
                'firm_id' => $firmId,
                'matter_id' => $matter->id,
                'user_id' => $user->id,
                'title' => $request->title,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
                'sha256' => $sha256,
                'category' => $request->category,
                'privilege' => $request->privilege,
                'is_client_visible' => $isClientVisible,
                'version' => 1,
            ]);

            return back()->with('success', 'Filing securely uploaded and SHA-256 authenticated.');
        })->name('documents.upload');

        Route::get('/documents/{document}/download', function (Document $document) {
            $user = Auth::user();
            if ($document->firm_id !== ($user->firm_id ?? 1)) {
                abort(403, 'Unauthorized document access across firms.');
            }

            // Lawyer-level isolation: non-partners must be assigned to this matter or be the author/requester
            if (!in_array($user->role, ['superadmin', 'partner'])) {
                $isCreatorOrRequester = ($document->user_id === $user->id) ||
                    \App\Models\DocumentRequest::where('document_id', $document->id)->where('requested_by', $user->id)->exists();

                $assigned = $isCreatorOrRequester || ($document->matter && (
                    $document->matter->lead_attorney_id === $user->id ||
                    $document->matter->users()->where('users.id', $user->id)->exists()
                ));
                if (!$assigned) {
                    abort(403, 'Unauthorized: You are not assigned to this case dossier.');
                }
            }

            $defaultDisk = config('filesystems.default', 'local');
            if ($document->file_path) {
                try {
                    if (Storage::disk($defaultDisk)->exists($document->file_path)) {
                        return Storage::disk($defaultDisk)->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (\Throwable $e) {}

                try {
                    if ($defaultDisk !== 'local' && Storage::disk('local')->exists($document->file_path)) {
                        return Storage::disk('local')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (\Throwable $e) {}

                try {
                    if ($defaultDisk !== 's3' && class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class) && Storage::disk('s3')->exists($document->file_path)) {
                        return Storage::disk('s3')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (\Throwable $e) {}
            }

            // Dynamic, compliant PDF stream fallback (Guaranteed to open cleanly in Adobe Reader / browsers)
            $pdfContent = \App\Services\LegalPdfGenerator::forDocument($document);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$document->filename}\"",
                'Content-Length' => strlen($pdfContent),
            ]);
        })->name('documents.download');

        // Chambers Calendar & Court Docket
        Route::get('/calendar', function () {
            $firmId = Auth::user()->firm_id;
            $events = Event::where('firm_id', $firmId)->with('matter')->orderBy('start_time')->get();
            $appointments = \App\Models\Appointment::where('firm_id', $firmId)->with(['matter', 'client', 'attorney'])->orderBy('scheduled_at')->get();
            $matters = Matter::where('firm_id', $firmId)->get();
            return view('calendar.index', compact('events', 'appointments', 'matters'));
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

        // Billing, Invoicing, Expenses & Chambers Ledger (PDF Pages 16-20)
        Route::get('/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/time-entries', [\App\Http\Controllers\BillingController::class, 'storeTimeEntry'])->name('billing.time-entries.store');
        Route::post('/billing/expenses', [\App\Http\Controllers\BillingController::class, 'storeExpense'])->name('billing.expenses.store');
        Route::post('/billing/invoices/generate', [\App\Http\Controllers\BillingController::class, 'generateInvoice'])->name('billing.invoices.generate');
        Route::post('/billing/invoices/{invoice}/settle', [\App\Http\Controllers\BillingController::class, 'settleInvoice'])->name('billing.invoices.settle');
        Route::post('/billing/transactions', [\App\Http\Controllers\BillingController::class, 'storeTransaction'])->name('billing.transactions.store');

        // Operational Legal Reports & Cause Lists
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('/cases', [\App\Http\Controllers\ReportController::class, 'caseReports'])->name('cases');
            Route::get('/hearings', [\App\Http\Controllers\ReportController::class, 'hearingSchedule'])->name('hearings');
            Route::get('/clients', [\App\Http\Controllers\ReportController::class, 'clientActivity'])->name('clients');
            Route::get('/workload', [\App\Http\Controllers\ReportController::class, 'workload'])->name('workload');
            Route::get('/bank-activity', [\App\Http\Controllers\ReportController::class, 'bankActivity'])->name('bank-activity');
        });


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

        // Document Requests from Counsel to Client (F-07)
        Route::post('/document-requests', function (Request $request) {
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
                'category' => $validated['category'] ?? 'Financial Statements',
                'priority' => $validated['priority'] ?? 'normal',
                'due_date' => $validated['due_date'] ?? now()->addDays(7),
                'status' => 'pending',
            ]);

            if (!empty($client->email)) {
                try {
                    Mail::to($client->email)->send(new DocumentRequestedMail($docRequest));
                } catch (\Throwable $e) {
                    Log::warning("Could not dispatch document request email: " . $e->getMessage());
                }
            }

            return back()->with('success', "Document request '{$docRequest->title}' dispatched to {$client->name}.");
        })->name('document-requests.store');

        Route::post('/document-requests/{request}/review', function (Request $httpRequest, DocumentRequest $request) {
            $firmId = Auth::user()->firm_id ?? 1;
            if ($request->firm_id !== $firmId) {
                abort(403, 'Unauthorized document request review.');
            }

            $validated = $httpRequest->validate([
                'status' => 'required|in:under_review,completed,rejected',
                'review_notes' => 'nullable|string|max:1000',
            ]);

            $request->update([
                'status' => $validated['status'],
                'review_notes' => $validated['review_notes'] ?? null,
            ]);

            return back()->with('success', "Document submission marked as " . ucfirst($validated['status']) . ".");
        })->name('document-requests.review');


        // Tasks & Productivity Hub
        Route::get('/tasks', function () {
            $firmId = Auth::user()->firm_id ?? 1;
            $tasks = Task::where('firm_id', $firmId)->with(['assignee', 'matter'])->latest()->get();
            $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate', 'paralegal'])->get();
            $matters = Matter::where('firm_id', $firmId)->get();
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
            $firmId = Auth::user()->firm_id ?? 1;
            $q = trim($request->input('q', ''));
            if (empty($q)) {
                return redirect()->route('matters.index');
            }

            $matters = Matter::where('firm_id', $firmId)
                ->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('case_number', 'like', "%{$q}%")
                        ->orWhere('court_name', 'like', "%{$q}%");
                })
                ->with('client')->get();

            $clients = Client::where('firm_id', $firmId)
                ->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })->get();

            $documents = Document::where('firm_id', $firmId)
                ->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('filename', 'like', "%{$q}%");
                })
                ->with('matter')->get();

            $tasks = Task::where('firm_id', $firmId)
                ->where('title', 'like', "%{$q}%")
                ->with(['assignee', 'matter'])->get();

            return view('search', compact('q', 'matters', 'clients', 'documents', 'tasks'));
        })->name('search');

        // Dynamic Practice Settings Configuration Hub
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/firm', [\App\Http\Controllers\SettingsController::class, 'updateFirm'])->name('settings.firm.update');
        Route::post('/settings/practice-areas', [\App\Http\Controllers\SettingsController::class, 'storePracticeArea'])->name('settings.practice-areas.store');
        Route::post('/settings/practice-areas/{practiceArea}/toggle', [\App\Http\Controllers\SettingsController::class, 'togglePracticeArea'])->name('settings.practice-areas.toggle');
        Route::post('/settings/holidays', [\App\Http\Controllers\SettingsController::class, 'storeHoliday'])->name('settings.holidays.store');
        Route::delete('/settings/holidays/{holiday}', [\App\Http\Controllers\SettingsController::class, 'destroyHoliday'])->name('settings.holidays.destroy');
        Route::post('/settings/letters', [\App\Http\Controllers\SettingsController::class, 'storeLetterTemplate'])->name('settings.letters.store');
        Route::put('/settings/letters/{letterTemplate}', [\App\Http\Controllers\SettingsController::class, 'updateLetterTemplate'])->name('settings.letters.update');
        Route::put('/settings/emails/{emailTemplate}', [\App\Http\Controllers\SettingsController::class, 'updateEmailTemplate'])->name('settings.emails.update');
        Route::put('/settings/id-types/{idType}', [\App\Http\Controllers\SettingsController::class, 'updateIdType'])->name('settings.id-types.update');


        // Daily Broadsheet Executive Intelligence
        Route::get('/briefing', function () {
            $firmId = Auth::user()->firm_id ?? 1;
            $matters = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney'])->get();
            $events = Event::where('firm_id', $firmId)->with('matter')->orderBy('start_time')->get();
            $tasks = Task::where('firm_id', $firmId)->with(['assignee', 'matter'])->get();
            return view('briefing', compact('matters', 'events', 'tasks'));
        })->name('briefing');

    }); // End of Firm Workspace Routes Group

    // Client Portal Suite (F-08 & F-07)
    Route::middleware(['portal.client'])->prefix('portal')->name('portal.')->group(function () {

        $getClient = function () {
            $user = Auth::user();
            $client = Client::where('user_id', $user->id)->first()
                ?? Client::where('email', $user->email)->first();

            if (!$client) {
                abort(403, 'No client representation record is linked to this user account.');
            }
            return $client;
        };

        Route::get('/', function () {
            return redirect()->route('portal.dashboard');
        })->name('index');

        // Portal Dashboard Overview
        Route::get('/dashboard', function () use ($getClient) {
            $client = $getClient();

            $matters = Matter::where('client_id', $client->id)->with(['documents', 'leadAttorney', 'documentRequests'])->get();
            $documentRequests = DocumentRequest::where('client_id', $client->id)->with('matter')->latest()->get();
            $invoices = Invoice::where('client_id', $client->id)->latest()->get();
            $events = Event::whereIn('matter_id', $matters->pluck('id'))->orderBy('start_time')->get();
            $documentsCount = Document::whereIn('matter_id', $matters->pluck('id'))->where('is_client_visible', true)->count();

            return view('portal.dashboard', compact('client', 'matters', 'documentRequests', 'invoices', 'events', 'documentsCount'));
        })->name('dashboard');

        // My Cases / Matters
        Route::get('/matters', function () use ($getClient) {
            $client = $getClient();

            $matters = Matter::where('client_id', $client->id)->with(['documents', 'leadAttorney', 'documentRequests'])->latest()->get();
            return view('portal.matters.index', compact('client', 'matters'));
        })->name('matters.index');

        Route::get('/matters/{matter}', function (Matter $matter) use ($getClient) {
            $client = $getClient();

            if ($matter->client_id !== $client->id) {
                abort(403, 'Unauthorized case dossier.');
            }

            $matter->load([
                'documents' => fn($q) => $q->where('is_client_visible', true),
                'leadAttorney',
                'documentRequests',
                'events',
                'messages.sender'
            ]);
            return view('portal.matters.show', compact('client', 'matter'));
        })->name('matters.show');

        // Document Requests Workflow (F-07)
        Route::get('/requests', function () use ($getClient) {
            $client = $getClient();

            $requests = DocumentRequest::where('client_id', $client->id)->with(['matter', 'requestedBy'])->latest()->get();
            return view('portal.requests.index', compact('client', 'requests'));
        })->name('requests.index');

        Route::post('/requests/{request}/upload', function (Request $httpRequest, DocumentRequest $request) use ($getClient) {
            $user = Auth::user();
            $client = $getClient();

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
                'title' => $request->title . ' (Client Submission)',
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
        })->name('requests.upload');

        // Case Documents Repository
        Route::get('/documents', function () use ($getClient) {
            $client = $getClient();

            $matterIds = Matter::where('client_id', $client->id)->pluck('id');
            $documents = Document::whereIn('matter_id', $matterIds)
                ->where('is_client_visible', true)
                ->with('matter')->latest()->get();
            $matters = Matter::where('client_id', $client->id)->get();

            return view('portal.documents.index', compact('client', 'documents', 'matters'));
        })->name('documents.index');

        Route::post('/documents/upload', function (Request $request) use ($getClient) {
            $user = Auth::user();
            $client = $getClient();

            // Detect PHP-level upload errors before Laravel validation
            if ($request->hasFile('file') && !$request->file('file')->isValid()) {
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
        })->name('documents.upload');

        Route::get('/documents/{document}/download', function (Document $document) use ($getClient) {
            $client = $getClient();

            // STRICT DATA ISOLATION: The document must belong to a matter owned by this client
            $matter = Matter::where('id', $document->matter_id)->where('client_id', $client->id)->first();
            if (!$matter) {
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
                } catch (\Throwable $e) {}

                try {
                    if ($defaultDisk !== 'local' && Storage::disk('local')->exists($document->file_path)) {
                        return Storage::disk('local')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (\Throwable $e) {}

                try {
                    if ($defaultDisk !== 's3' && class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class) && Storage::disk('s3')->exists($document->file_path)) {
                        return Storage::disk('s3')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (\Throwable $e) {}
            }

            // Dynamic, compliant PDF stream fallback (Guaranteed to open cleanly in Adobe Reader / browsers)
            $pdfContent = \App\Services\LegalPdfGenerator::forDocument($document);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$document->filename}\"",
                'Content-Length' => strlen($pdfContent),
            ]);
        })->name('documents.download');

        // Counsel Communications / Messages
        Route::get('/messages', function (Request $request) use ($getClient) {
            $client = $getClient();

            $matters = Matter::where('client_id', $client->id)->with(['leadAttorney', 'messages.sender'])->get();
            $selectedMatterId = $request->input('matter_id', $matters->first()->id ?? null);
            $selectedMatter = $matters->firstWhere('id', $selectedMatterId);

            return view('portal.messages.index', compact('client', 'matters', 'selectedMatter'));
        })->name('messages.index');

        Route::post('/messages', function (Request $request) use ($getClient) {
            $user = Auth::user();
            $client = $getClient();

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

        // Hearings & Calendar
        Route::get('/calendar', function () use ($getClient) {
            $client = $getClient();

            $matterIds = Matter::where('client_id', $client->id)->pluck('id');
            $events = Event::whereIn('matter_id', $matterIds)->with('matter')->orderBy('start_time')->get();

            return view('portal.calendar.index', compact('client', 'events'));
        })->name('calendar.index');

        // Client Invoices & Retainer Ledger
        Route::get('/invoices', function () use ($getClient) {
            $client = $getClient();

            $invoices = Invoice::where('client_id', $client->id)->with('matter')->latest()->get();
            return view('portal.invoices.index', compact('client', 'invoices'));
        })->name('invoices.index');

        Route::post('/invoices/{invoice}/pay', function (Request $request, Invoice $invoice) use ($getClient) {
            $client = $getClient();

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
        })->name('invoices.pay');
    });
});

/*
|--------------------------------------------------------------------------
| Super Admin Platform Routes (Protected strictly by EnsureSuperAdmin)
|--------------------------------------------------------------------------
| Decoupled from Laravel's default 'auth' middleware so administrators can
| access environment settings even during emergency database outages.
*/
Route::middleware(['admin.super'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $firms = \App\Models\Firm::withCount(['users', 'matters', 'documents'])->get();
        return view('admin.dashboard', compact('firms'));
    })->name('dashboard');

    // ── Law Firm Tenant Management ──────────────────────────
    Route::resource('firms', \App\Http\Controllers\Admin\FirmManagementController::class);
    Route::post('/firms/{firm}/toggle-status', [\App\Http\Controllers\Admin\FirmManagementController::class, 'toggleStatus'])->name('firms.toggle-status');
    Route::post('/firms/{firm}/change-password', [\App\Http\Controllers\Admin\FirmManagementController::class, 'changePassword'])->name('firms.change-password');
    Route::post('/firms/{firm}/change-subscription', [\App\Http\Controllers\Admin\FirmManagementController::class, 'changeSubscription'])->name('firms.change-subscription');

    // ── SaaS Subscription Plans ─────────────────────────────
    Route::get('/plans', [\App\Http\Controllers\Admin\PlanManagementController::class, 'index'])->name('plans.index');
    Route::post('/plans', [\App\Http\Controllers\Admin\PlanManagementController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [\App\Http\Controllers\Admin\PlanManagementController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\Admin\PlanManagementController::class, 'destroy'])->name('plans.destroy');
    Route::post('/plans/{plan}/toggle-active', [\App\Http\Controllers\Admin\PlanManagementController::class, 'toggleActive'])->name('plans.toggle-active');
    Route::post('/plans/firms/{firm}/assign', [\App\Http\Controllers\Admin\PlanManagementController::class, 'assignPlan'])->name('plans.assign');

    // ── Subscription Governance & Renewals (PDF Pages 20, 21) ──
    Route::get('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{subscription}/renew', [\App\Http\Controllers\Admin\SubscriptionManagementController::class, 'renew'])->name('subscriptions.renew');

    // ── Super Admin Profile & Account (PDF Pages 4, 5) ──────
    Route::get('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/change-avatar', [\App\Http\Controllers\Admin\AdminProfileController::class, 'changeAvatar'])->name('profile.change-avatar');

    // ── Platform Mail & Communications Gateway ──────────────
    Route::get('/settings/mail', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'mailSettings'])->name('settings.mail');
    Route::post('/settings/mail', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateMailSettings'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'testMail'])->name('settings.mail.test');

    // ── Automated Test Management ───────────────────────────
    Route::get('/tests', [\App\Http\Controllers\Admin\TestManagementController::class, 'index'])->name('tests.index');
    Route::post('/tests/run', [\App\Http\Controllers\Admin\TestManagementController::class, 'runAll'])->name('tests.run');
});
