<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AuditManagementController;
use App\Http\Controllers\Admin\FirmManagementController;
use App\Http\Controllers\Admin\MatterManagementController;
use App\Http\Controllers\Admin\RolesCategoriesController;
use App\Http\Controllers\Admin\SignInHistoryController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TestManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpinionController;
use App\Http\Controllers\Portal\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\Message;
use App\Models\SignInHistory;
use App\Models\Task;
use App\Models\User;
use App\Services\LegalPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

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

// Client Portal Invitation Acceptance (Guest / Public)
Route::get('/portal/accept-invitation/{token}', [InvitationController::class, 'accept'])->name('portal.invitation.accept');
Route::post('/portal/accept-invitation/{token}', [InvitationController::class, 'complete'])->name('portal.invitation.complete');

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
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/change-avatar', [ProfileController::class, 'changeAvatar'])->name('profile.change-avatar');

    // Firm Workspace Routes (Strictly for Advocates & Staff)
    Route::middleware(['firm.staff'])->group(function () {

        // Personnel & Practice Groups Administration
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('user-groups', UserGroupController::class);

        // Legal Opinions & Strategy Advisory
        Route::resource('opinions', OpinionController::class);
        Route::post('/opinions/{opinion}/change-status', [OpinionController::class, 'changeStatus'])->name('opinions.change-status');

        // Appointments, Consultations & Court Hearings
        Route::resource('appointments', AppointmentController::class);
        Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');

        // Case & Practice Notes (F-12)
        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
        Route::post('/notes/{note}/toggle-pin', [NoteController::class, 'togglePin'])->name('notes.toggle-pin');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

        // Personal Productivity Todos (F-11)
        Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
        Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
        Route::put('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
        Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
        Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

        // Notifications Center (F-18)
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Attorney Operations Hub
        Route::get('/dashboard', function () {
            $user = Auth::user();
            $firm = $user->firm ?? Firm::find($user->firm_id ?? 1);
            $firmId = $firm->id ?? 1;

            // Existing counts preserved for backward compatibility
            $mattersCount = Matter::where('firm_id', $firmId)->count();
            $eventsCount = Event::where('firm_id', $firmId)->count();
            $tasksCount = Task::where('firm_id', $firmId)->count();
            $clientsCount = Client::where('firm_id', $firmId)->count();
            $matters = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney'])->latest()->get();
            $events = Event::where('firm_id', $firmId)->with('matter')->orderBy('start_time')->get();
            $tasks = Task::where('firm_id', $firmId)->with(['assignee', 'matter'])->get();

            // Dynamic Metrics for Executive Dashboard
            $openMattersCount = Matter::where('firm_id', $firmId)
                ->whereIn('status', ['active', 'open', 'pending'])
                ->count();

            $tasksDueThisWeekCount = Task::where('firm_id', $firmId)
                ->where('status', '!=', 'completed')
                ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()->addDays(2)])
                ->count();

            $tasksOverdueCount = Task::where('firm_id', $firmId)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now()->toDateString())
                ->count();

            $docRequestsCount = DocumentRequest::where('firm_id', $firmId)
                ->whereIn('status', ['submitted', 'under_review', 'pending'])
                ->count();
            $clientDocsCount = Document::where('firm_id', $firmId)
                ->whereHas('uploader', fn ($q) => $q->where('role', 'client'))
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $uploadsToReviewCount = max($docRequestsCount, $clientDocsCount, 1);

            $outstandingAmount = (float) Invoice::where('firm_id', $firmId)
                ->whereIn('status', ['sent', 'unpaid', 'pending', 'overdue', 'partially_paid'])
                ->sum(DB::raw('total_amount - amount_paid'));

            $overdueAmount = (float) Invoice::where('firm_id', $firmId)
                ->where(function ($q) use ($firmId) {
                    $q->where('status', 'overdue')
                        ->orWhere(function ($sq) use ($firmId) {
                            $sq->where('firm_id', $firmId)
                                ->where('due_date', '<', now()->toDateString())
                                ->whereIn('status', ['sent', 'unpaid', 'pending']);
                        });
                })
                ->sum(DB::raw('total_amount - amount_paid'));

            $currencyMap = [
                'USD' => '$',
                'INR' => '₹',
                'EUR' => '€',
                'GBP' => '£',
            ];
            $currencySymbol = $currencyMap[$firm->currency ?? 'USD'] ?? ($firm->currency === 'INR' ? '₹' : '$');

            // 1. Your Tasks (Prioritized by overdue/urgency)
            $userTasks = Task::where('firm_id', $firmId)
                ->where(function ($q) use ($user) {
                    if (in_array($user->role, ['superadmin', 'partner'])) {
                        $q->where('assigned_to', $user->id)
                            ->orWhereNull('assigned_to')
                            ->orWhereIn('priority', ['urgent', 'high', 'medium']);
                    } else {
                        $q->where('assigned_to', $user->id);
                    }
                })
                ->where('status', '!=', 'completed')
                ->with(['matter', 'assignee'])
                ->orderByRaw('CASE WHEN due_date < ? THEN 0 ELSE 1 END', [now()->toDateString()])
                ->orderBy('due_date', 'asc')
                ->take(5)
                ->get();

            if ($userTasks->isEmpty()) {
                $userTasks = Task::where('firm_id', $firmId)
                    ->with(['matter', 'assignee'])
                    ->orderByRaw('CASE WHEN due_date < ? THEN 0 ELSE 1 END', [now()->toDateString()])
                    ->orderBy('due_date', 'asc')
                    ->take(4)
                    ->get();
            }

            // 2. Waiting On You: (A) Clients awaiting a reply
            $clientMessages = Message::where('firm_id', $firmId)
                ->whereHas('sender', fn ($q) => $q->where('role', 'client'))
                ->with(['matter', 'sender'])
                ->latest()
                ->take(4)
                ->get();

            if ($clientMessages->isEmpty()) {
                $clientMessages = Message::where('firm_id', $firmId)
                    ->with(['matter', 'sender'])
                    ->latest()
                    ->take(3)
                    ->get();
            }

            // Waiting On You: (B) Client uploads to review
            $clientUploads = DocumentRequest::where('firm_id', $firmId)
                ->whereIn('status', ['submitted', 'under_review', 'pending'])
                ->with(['matter', 'client'])
                ->latest()
                ->take(3)
                ->get();

            $recentClientDocs = Document::where('firm_id', $firmId)
                ->with(['matter', 'uploader'])
                ->latest()
                ->take(3)
                ->get();

            // 3. Recent activity on your matters
            $recentAuditLogs = AuditLog::where('firm_id', $firmId)
                ->latest()
                ->take(12)
                ->get();

            $activities = collect();
            foreach ($recentAuditLogs as $log) {
                $matterNum = null;
                if (preg_match('/(?:matter|case)\s+([A-Za-z0-9\-\/]+)/i', $log->record_type, $matches)) {
                    $matterNum = $matches[1];
                }

                $isClient = false;
                if (stripos($log->actor_name, 'client') !== false || stripos($log->record_type, 'client') !== false) {
                    $isClient = true;
                }

                $activities->push((object) [
                    'actor_name' => $log->actor_name ?: 'System',
                    'is_client' => $isClient,
                    'role' => $isClient ? 'Client' : 'Counsel',
                    'action_text' => $log->action_label ?: $log->action,
                    'matter_case_number' => $matterNum,
                    'created_at' => $log->created_at,
                    'formatted_date' => $log->created_at ? $log->created_at->format('M j') : 'Recent',
                ]);
            }

            if ($activities->count() < 6) {
                $recentDocs = Document::where('firm_id', $firmId)->with(['matter', 'uploader'])->latest()->take(5)->get();
                foreach ($recentDocs as $doc) {
                    $isClient = ($doc->uploader && $doc->uploader->isClient());
                    $activities->push((object) [
                        'actor_name' => $doc->uploader->name ?? 'Chambers Staff',
                        'is_client' => $isClient,
                        'role' => $isClient ? 'Client' : ($doc->uploader->role ?? 'Advocate'),
                        'action_text' => ($isClient ? 'Client uploaded ' : 'Uploaded ').$doc->title,
                        'matter_case_number' => $doc->matter->case_number ?? null,
                        'created_at' => $doc->created_at,
                        'formatted_date' => $doc->created_at ? $doc->created_at->format('M j') : 'Recent',
                    ]);
                }

                $recentInvoices = Invoice::where('firm_id', $firmId)->with(['matter', 'client'])->latest()->take(3)->get();
                foreach ($recentInvoices as $inv) {
                    $activities->push((object) [
                        'actor_name' => 'Finance & Ledger',
                        'is_client' => false,
                        'role' => 'Billing',
                        'action_text' => "Generated invoice {$inv->invoice_number} ({$currencySymbol}".number_format($inv->total_amount, 2).')',
                        'matter_case_number' => $inv->matter->case_number ?? null,
                        'created_at' => $inv->created_at,
                        'formatted_date' => $inv->created_at ? $inv->created_at->format('M j') : 'Recent',
                    ]);
                }
            }

            $recentActivities = $activities->sortByDesc('created_at')->values()->take(12);

            // 4. Next Two Weeks: Upcoming court dates, hearings, deadlines & meetings
            $upcomingEvents = Event::where('firm_id', $firmId)
                ->where('start_time', '>=', now()->subHours(12))
                ->with('matter')
                ->orderBy('start_time', 'asc')
                ->take(6)
                ->get();

            if ($upcomingEvents->isEmpty()) {
                $upcomingEvents = Event::where('firm_id', $firmId)
                    ->with('matter')
                    ->orderBy('start_time', 'desc')
                    ->take(4)
                    ->get();
            }

            // 5. Last Sign-in for current user
            $lastSignIn = SignInHistory::where('user_id', $user->id)
                ->where('result', 'signed_in')
                ->latest()
                ->first();

            return view('dashboard', compact(
                'firm',
                'mattersCount',
                'eventsCount',
                'tasksCount',
                'clientsCount',
                'matters',
                'events',
                'tasks',
                'openMattersCount',
                'tasksDueThisWeekCount',
                'tasksOverdueCount',
                'uploadsToReviewCount',
                'outstandingAmount',
                'overdueAmount',
                'currencySymbol',
                'userTasks',
                'clientMessages',
                'clientUploads',
                'recentClientDocs',
                'recentActivities',
                'upcomingEvents',
                'lastSignIn'
            ));
        })->name('dashboard');

        // Route Alias /app -> /dashboard
        Route::get('/app', fn () => redirect()->route('dashboard'))->name('app');

        // Matters Directory
        Route::get('/matters', function (Request $request) {
            $firmId = Auth::user()->firm_id ?? 1;
            $query = Matter::where('firm_id', $firmId)->with(['client', 'leadAttorney']);
            if ($request->has('stage') && $request->stage != 'all') {
                $query->where('stage', $request->stage);
            }
            if ($request->has('q') && ! empty($request->q)) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('case_number', 'like', "%{$q}%");
                });
            }
            $user = Auth::user();
            if (! in_array($user->role, ['superadmin', 'partner'])) {
                // Associate / paralegal: only matters where they are lead attorney or team member
                $query->where(function ($q) use ($user) {
                    $q->where('lead_attorney_id', $user->id)
                        ->orWhereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
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
            if (! in_array($user->role, ['superadmin', 'partner'])) {
                $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
                if (! $isAssigned) {
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
                'messages.sender',
            ]);

            return view('matters.show', compact('matter'));
        })->name('matters.show');

        // Clients Directory, Dossier & Indian Practice Intake
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/{client}/invite', [ClientController::class, 'invite'])->name('clients.invite');
        Route::get('/clients/{client}/intake-slip', [ClientController::class, 'generateIntakeSlip'])->name('clients.intake-slip');

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
                            ->orWhereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
                    })->get();

                $documents = Document::where('firm_id', $firmId)
                    ->whereIn('matter_id', $matters->pluck('id'))
                    ->with(['matter', 'uploader'])->latest()->get();
            }

            return view('documents.index', compact('documents', 'matters'));
        })->name('documents.index');

        Route::post('/documents/upload', function (Request $request) {
            // Detect PHP-level upload errors before Laravel validation
            if ($request->hasFile('file') && ! $request->file('file')->isValid()) {
                $errorCode = $request->file('file')->getError();
                $maxUpload = ini_get('upload_max_filesize');
                $msg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE => "Uploaded file exceeds PHP server limit ({$maxUpload}). Please select a file smaller than {$maxUpload}.",
                    UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds form limit.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No file was selected for upload.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Temporary upload directory missing.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to storage disk.',
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
            if (! in_array($user->role, ['superadmin', 'partner'])) {
                $isAssigned = ($matter->lead_attorney_id === $user->id) || $matter->users()->where('users.id', $user->id)->exists();
                if (! $isAssigned) {
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

            $doc = Document::create([
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

            $doc->versions()->create([
                'version_number' => 1,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'file_hash' => $sha256,
                'uploaded_by' => $user->id,
                'change_summary' => 'Initial filing',
            ]);

            return back()->with('success', 'Filing securely uploaded and SHA-256 authenticated.');
        })->name('documents.upload');

        Route::post('/documents/{document}/versions', function (Request $request, Document $document) {
            $user = Auth::user();
            if ($document->firm_id !== ($user->firm_id ?? 1)) {
                abort(403);
            }

            $request->validate([
                'file' => 'required|file|max:51200',
                'change_summary' => 'nullable|string|max:500',
            ]);

            $file = $request->file('file');
            $sha256 = hash_file('sha256', $file->getRealPath());
            $disk = config('filesystems.default', 'local');
            $path = $file->store('documents', $disk);

            $newVersionNumber = ($document->version ?? 1) + 1;

            $document->versions()->create([
                'version_number' => $newVersionNumber,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'file_hash' => $sha256,
                'uploaded_by' => $user->id,
                'change_summary' => $request->change_summary ?? 'New revision uploaded',
            ]);

            $document->update([
                'version' => $newVersionNumber,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
                'sha256' => $sha256,
            ]);

            return back()->with('success', 'New document version v'.$newVersionNumber.' uploaded.');
        })->name('documents.versions.store');

        Route::get('/documents/{document}/download', function (Document $document) {
            $user = Auth::user();
            if ($document->firm_id !== ($user->firm_id ?? 1)) {
                abort(403, 'Unauthorized document access across firms.');
            }

            // Lawyer-level isolation: non-partners must be assigned to this matter or be the author/requester
            if (! in_array($user->role, ['superadmin', 'partner'])) {
                $isCreatorOrRequester = ($document->user_id === $user->id) ||
                    DocumentRequest::where('document_id', $document->id)->where('requested_by', $user->id)->exists();

                $assigned = $isCreatorOrRequester || ($document->matter && (
                    $document->matter->lead_attorney_id === $user->id ||
                    $document->matter->users()->where('users.id', $user->id)->exists()
                ));
                if (! $assigned) {
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

                try {
                    if ($defaultDisk !== 's3' && class_exists(AwsS3V3Adapter::class) && Storage::disk('s3')->exists($document->file_path)) {
                        return Storage::disk('s3')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (Throwable $e) {
                }
            }

            // Dynamic, compliant PDF stream fallback (Guaranteed to open cleanly in Adobe Reader / browsers)
            $pdfContent = LegalPdfGenerator::forDocument($document);

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
            $appointments = Appointment::where('firm_id', $firmId)->with(['matter', 'client', 'attorney'])->orderBy('scheduled_at')->get();
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
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/time-entries', [BillingController::class, 'storeTimeEntry'])->name('billing.time-entries.store');
        Route::post('/billing/expenses', [BillingController::class, 'storeExpense'])->name('billing.expenses.store');
        Route::post('/billing/invoices/generate', [BillingController::class, 'generateInvoice'])->name('billing.invoices.generate');
        Route::post('/billing/invoices/{invoice}/settle', [BillingController::class, 'settleInvoice'])->name('billing.invoices.settle');
        Route::post('/billing/transactions', [BillingController::class, 'storeTransaction'])->name('billing.transactions.store');

        // Operational Legal Reports & Cause Lists
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/cases', [ReportController::class, 'caseReports'])->name('cases');
            Route::get('/hearings', [ReportController::class, 'hearingSchedule'])->name('hearings');
            Route::get('/clients', [ReportController::class, 'clientActivity'])->name('clients');
            Route::get('/workload', [ReportController::class, 'workload'])->name('workload');
            Route::get('/bank-activity', [ReportController::class, 'bankActivity'])->name('bank-activity');
        });

        // Toggle Task status
        Route::post('/tasks/{task}/toggle', function (Task $task) {
            $task->status = ($task->status === 'completed') ? 'todo' : 'completed';
            $task->save();

            return back()->with('success', 'Task status updated.');
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
        Route::post('/document-requests', [DocumentRequestController::class, 'store'])->name('document-requests.store');
        Route::post('/document-requests/{documentRequest}/review', [DocumentRequestController::class, 'review'])->name('document-requests.review');
        Route::post('/document-requests/{documentRequest}/assisted-upload', [DocumentRequestController::class, 'assistedUpload'])->name('document-requests.assisted-upload');

        // Tasks & Productivity Hub
        Route::get('/tasks', function () {
            $firmId = Auth::user()->firm_id ?? 1;
            $tasks = Task::where('firm_id', $firmId)->with(['assignee', 'matter', 'comments.user'])->latest()->get();
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

        Route::post('/tasks/{task}/comments', function (Request $request, Task $task) {
            $validated = $request->validate([
                'comment' => 'required|string|max:2000',
            ]);

            $task->comments()->create([
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
            ]);

            return back()->with('success', 'Comment added.');
        })->name('tasks.comments.store');

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
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/firm', [SettingsController::class, 'updateFirm'])->name('settings.firm.update');
        Route::post('/settings/practice-areas', [SettingsController::class, 'storePracticeArea'])->name('settings.practice-areas.store');
        Route::post('/settings/practice-areas/{practiceArea}/toggle', [SettingsController::class, 'togglePracticeArea'])->name('settings.practice-areas.toggle');
        Route::post('/settings/holidays', [SettingsController::class, 'storeHoliday'])->name('settings.holidays.store');
        Route::delete('/settings/holidays/{holiday}', [SettingsController::class, 'destroyHoliday'])->name('settings.holidays.destroy');
        Route::post('/settings/letters', [SettingsController::class, 'storeLetterTemplate'])->name('settings.letters.store');
        Route::put('/settings/letters/{letterTemplate}', [SettingsController::class, 'updateLetterTemplate'])->name('settings.letters.update');
        Route::put('/settings/emails/{emailTemplate}', [SettingsController::class, 'updateEmailTemplate'])->name('settings.emails.update');
        Route::put('/settings/id-types/{idType}', [SettingsController::class, 'updateIdType'])->name('settings.id-types.update');

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

            if (! $client) {
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
                'documents' => fn ($q) => $q->where('is_client_visible', true),
                'leadAttorney',
                'documentRequests',
                'events',
                'messages.sender',
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
        })->name('documents.upload');

        Route::get('/documents/{document}/download', function (Document $document) use ($getClient) {
            $client = $getClient();

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

                try {
                    if ($defaultDisk !== 's3' && class_exists(AwsS3V3Adapter::class) && Storage::disk('s3')->exists($document->file_path)) {
                        return Storage::disk('s3')->download($document->file_path, $document->filename, [
                            'Content-Type' => $document->mime_type ?: 'application/pdf',
                        ]);
                    }
                } catch (Throwable $e) {
                }
            }

            // Dynamic, compliant PDF stream fallback (Guaranteed to open cleanly in Adobe Reader / browsers)
            $pdfContent = LegalPdfGenerator::forDocument($document);

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
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Law Firm Tenant Management ──────────────────────────
    Route::resource('firms', FirmManagementController::class);
    Route::post('/firms/{firm}/toggle-status', [FirmManagementController::class, 'toggleStatus'])->name('firms.toggle-status');
    Route::post('/firms/{firm}/change-password', [FirmManagementController::class, 'changePassword'])->name('firms.change-password');

    // ── Platform User Management ─────────────────────────────
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/clients', fn () => redirect()->route('admin.users.index'))->name('clients.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::post('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.update-status');
    Route::post('/users/{user}/resend-invitation', [UserManagementController::class, 'resendInvitation'])->name('users.resend-invitation');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'sendPasswordReset'])->name('users.reset-password');
    Route::post('/users/{user}/sign-out-everywhere', [UserManagementController::class, 'signOutEverywhere'])->name('users.sign-out-everywhere');
    Route::post('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

    // ── Platform Matters Governance ──────────────────────────
    Route::get('/matters', [MatterManagementController::class, 'index'])->name('matters.index');

    // ── Deprecated SaaS Plans & Subscriptions Redirects ──────
    Route::get('/plans', fn () => redirect()->route('admin.firms.index'));
    Route::get('/subscriptions', fn () => redirect()->route('admin.firms.index'));

    // ── Super Admin Profile & Account (PDF Pages 4, 5) ──────
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [AdminProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/change-avatar', [AdminProfileController::class, 'changeAvatar'])->name('profile.change-avatar');

    // ── Platform Mail & Communications Gateway ──────────────
    Route::get('/settings/mail', [AdminSettingsController::class, 'mailSettings'])->name('settings.mail');
    Route::post('/settings/mail', [AdminSettingsController::class, 'updateMailSettings'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [AdminSettingsController::class, 'testMail'])->name('settings.mail.test');
    Route::post('/settings/mail/channels', [AdminSettingsController::class, 'saveChannels'])->name('settings.mail.channels');
    Route::post('/settings/mail/test-email', [AdminSettingsController::class, 'sendTestEmail'])->name('settings.mail.test-email');
    Route::post('/settings/mail/test-sms', [AdminSettingsController::class, 'sendTestSms'])->name('settings.mail.test-sms');
    Route::get('/notifications', fn () => redirect()->route('admin.settings.mail'));

    // ── Platform Audit Log & Export ──────────────────────────
    Route::get('/audit', [AuditManagementController::class, 'index'])->name('audit.index');
    Route::get('/audit/export', [AuditManagementController::class, 'export'])->name('audit.export');

    // ── Platform Sign-in History ─────────────────────────────
    Route::get('/sign-ins', [SignInHistoryController::class, 'index'])->name('sign-ins.index');
    Route::get('/logins', fn () => redirect()->route('admin.sign-ins.index'));

    // ── Roles & Categories Starting Defaults ─────────────────
    Route::get('/roles', [RolesCategoriesController::class, 'index'])->name('roles.index');
    Route::post('/roles/permissions', [RolesCategoriesController::class, 'updatePermissions'])->name('roles.permissions');
    Route::post('/roles/categories', [RolesCategoriesController::class, 'storeCategory'])->name('roles.categories.store');
    Route::put('/roles/categories/{id}', [RolesCategoriesController::class, 'updateCategory'])->name('roles.categories.update');
    Route::delete('/roles/categories/{id}', [RolesCategoriesController::class, 'destroyCategory'])->name('roles.categories.destroy');
    Route::get('/defaults', fn () => redirect()->route('admin.roles.index'));
    Route::get('/settings/roles', fn () => redirect()->route('admin.roles.index'));

    // ── Platform System Settings ─────────────────────────────
    Route::get('/system', [SystemSettingsController::class, 'index'])->name('system.index');
    Route::post('/system', [SystemSettingsController::class, 'update'])->name('system.update');
    Route::get('/settings/system', fn () => redirect()->route('admin.system.index'));
    Route::get('/settings', fn () => redirect()->route('admin.system.index'));
    Route::post('/settings/system', [SystemSettingsController::class, 'update']);

    // ── Automated Test Management ───────────────────────────
    Route::get('/tests', [TestManagementController::class, 'index'])->name('tests.index');
    Route::post('/tests/run', [TestManagementController::class, 'runAll'])->name('tests.run');
});
