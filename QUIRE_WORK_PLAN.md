# ⚖️ QUIRE — MVP Development Work Plan
## 14-Day Sprint Schedule + Daily Breakdown

> **Project:** Quire Legal Case & Document Management System
> **Stack:** Laravel 13 · PHP 8.3+ · MySQL 8 · Blade + Livewire + Alpine.js · Tailwind CSS
> **Hosting:** Hostinger Shared Hosting
> **Target:** 10 working days core development + 4 days QA/stabilization/UAT
> **Created:** September 14, 2026

---

## 📋 Sprint Overview

```
WEEK 1 (Days 1-7)  ══════════════════════════════════════════════════════
│ Day 1  │ Foundation & Auth Architecture                               │
│ Day 2  │ Auth Hardening, Users, Roles & Permissions                   │
│ Day 3  │ Clients & Matters — Core Business Entities                   │
│ Day 4  │ Document Upload, Categories & Secure Access                  │
│ Day 5  │ Document Versioning, Sharing & Request Workflow              │
│ Day 6  │ Client Portal & Mobile UX                                    │
│ Day 7  │ Matter-Based Chat & Messaging System                         │
═════════════════════════════════════════════════════════════════════════

WEEK 2 (Days 8-14) ══════════════════════════════════════════════════════
│ Day 8  │ Tasks, Todo List & Notes                                     │
│ Day 9  │ Internal Calendar + Google Calendar Integration              │
│ Day 10 │ Google Drive Integration                                     │
│ Day 11 │ Global Search + Billing Data Model                           │
│ Day 12 │ Analytics Dashboard & Charts                                 │
│ Day 13 │ Notifications, Audit Logs, Settings & Polish                 │
│ Day 14 │ Full QA, Security Testing, Deployment & Documentation        │
═════════════════════════════════════════════════════════════════════════
```

---

## 🔑 Pre-Sprint Checklist (Day 0 — Before Starting)

### Environment Setup
- [ ] Install PHP 8.3+, Composer, Node.js 20+, npm
- [ ] Install MySQL 8 locally
- [ ] Create GitHub repository: `quire-legal`
- [ ] Set up `.editorconfig`, `.gitignore`, `README.md`
- [ ] Configure IDE (PHPStorm/VS Code with Laravel, Tailwind, Livewire extensions)

### Hostinger Preparation
- [ ] Purchase/configure Hostinger hosting plan
- [ ] Set up MySQL database on Hostinger
- [ ] Configure domain/subdomain with SSL (HTTPS)
- [ ] Note SMTP credentials for email sending
- [ ] Configure SSH access (if available) or Git deployment

### Google API Setup
- [ ] Create Google Cloud Project
- [ ] Enable Google Calendar API
- [ ] Enable Google Drive API
- [ ] Create OAuth 2.0 credentials (client ID + secret)
- [ ] Configure authorized redirect URIs for local + production
- [ ] Download credentials JSON

### Design Assets
- [ ] Finalize color palette: beige/cream backgrounds, dark forest-green accents
- [ ] Choose fonts: Serif for brand (e.g., Playfair Display), Sans-serif for body (e.g., Inter)
- [ ] Prepare Quire logo variants (light/dark, icon-only)
- [ ] Screenshot reference from prototype at quire-legal.vercel.app

---

## 📅 Day 1 — Foundation, Architecture & Authentication

### Morning Session (4 hours)

#### 1.1 Laravel Project Setup
- [ ] Create Laravel 13 project: `composer create-project laravel/laravel quire`
- [ ] Configure `.env` with database, app URL, mail settings
- [ ] Install core dependencies:
  ```
  composer require livewire/livewire
  composer require laravel/fortify
  composer require laravel/sanctum
  composer require pragmarx/google2fa-laravel
  ```
- [ ] Install frontend dependencies:
  ```
  npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography
  npm install alpinejs chart.js
  ```
- [ ] Configure `tailwind.config.js` with Quire color palette
- [ ] Set up Vite configuration for asset compilation

#### 1.2 Database Architecture & ERD
- [ ] Create ERD diagram documenting all 32 tables
- [ ] Write initial migrations (run in order):
  - `create_firms_table`
  - `update_users_table` (add firm_id, role, 2FA fields, etc.)
  - `create_roles_table`
  - `create_permissions_table`
  - `create_role_permission_table`
  - `create_role_user_table`
- [ ] Run migrations: `php artisan migrate`
- [ ] Create database seeders for development data:
  - `FirmSeeder` — Hartwell & Okafor LLP
  - `RoleSeeder` — System roles (Super Admin, Firm Admin, Attorney, Paralegal, Client)
  - `PermissionSeeder` — All 30+ permissions grouped by module
  - `UserSeeder` — Demo users matching prototype

### Afternoon Session (4 hours)

#### 1.3 Base UI Layout & Design System
- [ ] Create master layout: `resources/views/layouts/app.blade.php`
  - Dark sidebar navigation (expandable/collapsible)
  - Top header bar with search input + notifications icon
  - Main content area with breadcrumbs
  - Mobile responsive hamburger menu
- [ ] Create portal layout: `resources/views/layouts/portal.blade.php`
  - Simplified navigation for clients
  - Mobile-first design
- [ ] Create admin layout: `resources/views/layouts/admin.blade.php`
  - Super admin navigation
- [ ] Create auth layout: `resources/views/layouts/auth.blade.php`
  - Centered card layout with Quire branding
- [ ] Build CSS design system in `resources/css/app.css`:
  - Color variables (CSS custom properties)
  - Typography scale
  - Button styles (primary, secondary, danger, ghost)
  - Card/panel styles
  - Form input styles
  - Badge/tag styles
  - Table styles
- [ ] Create reusable Blade components:
  - `x-sidebar-link` — Navigation link with icon + active state
  - `x-page-header` — Page title + breadcrumbs + action buttons
  - `x-card` — Content card with header/body/footer slots
  - `x-badge` — Status/priority badges
  - `x-modal` — Alpine.js powered modal dialog
  - `x-dropdown` — Dropdown menu
  - `x-alert` — Flash message alerts
  - `x-empty-state` — Empty list placeholder

#### 1.4 Basic Authentication
- [ ] Configure Laravel Fortify for login, registration (invitation-only), password reset
- [ ] Create login page (`resources/views/auth/login.blade.php`) matching prototype design
- [ ] Create forgot password page
- [ ] Create password reset page
- [ ] Implement demo account quick-login buttons (development only)
- [ ] Add login audit logging (user, IP, user agent, timestamp)

### Day 1 Definition of Done ✅
- [ ] Laravel project runs locally with `php artisan serve`
- [ ] Database migrations run without errors
- [ ] Seeded demo data loads correctly
- [ ] Login/logout works with demo accounts
- [ ] Base layouts render correctly on desktop and mobile
- [ ] All styles match the prototype aesthetic (beige/green/serif)
- [ ] Git: First commit with clean project structure

---

## 📅 Day 2 — Auth Hardening, Users, Roles & Permissions

### Morning Session (4 hours)

#### 2.1 Authentication Hardening
- [ ] Implement progressive lockout (`PreventBruteForce` middleware):
  - 5 failures → 15min lock
  - 10 failures → 1hr lock
  - 15 failures → 24hr lock
- [ ] Implement rate limiting on login route (5 attempts/minute)
- [ ] Add email verification flow (Fortify)
- [ ] Build 2FA setup flow:
  - `app/Actions/Auth/Enable2FA.php` — Generate TOTP secret + QR code
  - `app/Actions/Auth/Verify2FA.php` — Validate code, mark confirmed
  - `resources/views/auth/two-factor-setup.blade.php` — QR code display + confirmation
  - `resources/views/auth/two-factor-challenge.blade.php` — Code input
- [ ] Implement `EnforceTwoFactor` middleware
- [ ] Configure secure session settings in `.env`

#### 2.2 User Management
- [ ] Create `app/Models/User.php` enhancements:
  - `firm()` relationship
  - `roles()` relationship
  - `hasPermission()` method with caching
  - `isAdmin()`, `isAttorney()`, `isClient()` helpers
  - `accessibleMatterIds()` method
- [ ] Create `app/Policies/UserPolicy.php`
- [ ] Create user profile page (`/app/account`):
  - Profile details (name, email, phone, avatar)
  - Notification preferences (JSON checkboxes)
  - Security settings (change password, 2FA toggle)

### Afternoon Session (4 hours)

#### 2.3 Role & Permission System
- [ ] Create `app/Models/Role.php` with firm scope
- [ ] Create `app/Models/Permission.php`
- [ ] Implement permission checking:
  ```php
  // app/Traits/HasPermissions.php
  public function hasPermission(string $permission): bool
  {
      return Cache::remember("user.{$this->id}.permissions", 900, function () {
          return $this->roles->flatMap->permissions->pluck('slug')->unique();
      })->contains($permission);
  }
  ```
- [ ] Create `app/Http/Livewire/Firm/TeamManagement.php`:
  - Team member list with role, email, status, last active
  - "Invite Team Member" modal
  - Deactivate/reactivate toggle
- [ ] Create `app/Http/Livewire/Firm/RolePermissionMatrix.php`:
  - Interactive checkbox grid (roles × permissions)
  - Group permissions by module
  - Save with immediate cache invalidation
- [ ] Create invitation system:
  - `app/Actions/Users/InviteUser.php`
  - `app/Notifications/UserInvited.php`
  - `app/Http/Controllers/Auth/InvitationController.php`
  - Invitation acceptance page

#### 2.4 Write Tests
- [ ] `tests/Feature/Auth/LoginTest.php` — 10 test cases
- [ ] `tests/Feature/Auth/TwoFactorTest.php` — 5 test cases
- [ ] `tests/Feature/Auth/LockoutTest.php` — 3 test cases
- [ ] `tests/Feature/Permissions/RolePermissionTest.php` — 8 test cases

### Day 2 Definition of Done ✅
- [ ] Brute force lockout triggers correctly
- [ ] 2FA can be enabled, verified, and enforced
- [ ] Email verification flow works end-to-end
- [ ] Team page shows all firm members with correct roles
- [ ] Permission matrix saves and applies correctly
- [ ] User invitation sends email and creates account on acceptance
- [ ] All 26 tests pass

---

## 📅 Day 3 — Clients & Matters

### Morning Session (4 hours)

#### 3.1 Client Management
- [ ] Create migrations: `create_clients_table`
- [ ] Create `app/Models/Client.php` with `BelongsToFirm` trait
- [ ] Create `app/Actions/Clients/CreateClient.php`
- [ ] Create `app/Actions/Clients/UpdateClient.php`
- [ ] Create `app/Actions/Clients/InviteClientToPortal.php`
- [ ] Create `app/Actions/Clients/ArchiveClient.php`
- [ ] Create `app/Policies/ClientPolicy.php`
- [ ] Create `app/Http/Livewire/Clients/ClientList.php`:
  - Filterable by status (active/inactive/archived)
  - Searchable by name/email
  - Paginated (20 per page)
- [ ] Create `app/Http/Livewire/Clients/CreateClientForm.php`:
  - Individual/Company type toggle
  - Dynamic form fields based on type
  - Validation
- [ ] Create `app/Http/Livewire/Clients/ClientDetail.php`:
  - Contact information
  - Associated matters list
  - Portal access status
  - Activity history

### Afternoon Session (4 hours)

#### 3.2 Matter/Case Management
- [ ] Create migrations: `create_matters_table`, `create_matter_users_table`
- [ ] Create `app/Models/Matter.php` with 15+ relationships
- [ ] Create `app/Actions/Matters/CreateMatter.php` with reference number generation
- [ ] Create `app/Actions/Matters/TransitionMatterStatus.php` (state machine)
- [ ] Create `app/Actions/Matters/AssignTeamMember.php`
- [ ] Create `app/Policies/MatterPolicy.php` (firm scope + matter_users check)
- [ ] Create `app/Http/Middleware/EnsureMatterAccess.php`
- [ ] Create `app/Http/Livewire/Matters/MatterList.php`:
  - Filter by practice area, status, attorney
  - Search by title, reference number
  - Paginated
- [ ] Create `app/Http/Livewire/Matters/CreateMatterForm.php`:
  - Client selector, practice area, description, assign lead attorney
- [ ] Create `app/Http/Livewire/Matters/MatterDashboard.php`:
  - Overview tab with case summary, team, client info, key dates
  - Tabbed navigation (9 tabs — content for other tabs comes in later days)

#### 3.3 Write Tests
- [ ] `tests/Feature/Clients/ClientCrudTest.php` — 8 test cases
- [ ] `tests/Feature/Clients/ClientIsolationTest.php` — 5 test cases (cross-firm, cross-client)
- [ ] `tests/Feature/Matters/MatterCrudTest.php` — 10 test cases
- [ ] `tests/Feature/Matters/MatterAccessTest.php` — 8 test cases

### Day 3 Definition of Done ✅
- [ ] Clients can be created (individual + company), edited, archived
- [ ] Client portal invitation sends email
- [ ] Matters can be created with auto-generated reference numbers
- [ ] Matter status transitions work correctly
- [ ] Team members can be assigned to matters
- [ ] Matter dashboard shows overview with 9 tab placeholders
- [ ] Cross-firm/cross-client isolation verified by tests
- [ ] All 31 tests pass

---

## 📅 Day 4 — Document Upload, Categories & Secure Access

### Morning Session (4 hours)

#### 4.1 Document Infrastructure
- [ ] Create migrations:
  - `create_document_categories_table`
  - `create_documents_table`
  - `create_document_versions_table`
- [ ] Create category seeder with 8 default categories:
  - Engagement, Pleadings, Court Orders, Correspondence, Work Product, Discovery, Evidence, Contracts
- [ ] Create `app/Models/Document.php`
- [ ] Create `app/Models/DocumentVersion.php`
- [ ] Create `app/Models/DocumentCategory.php`
- [ ] Configure private storage disk in `config/filesystems.php`
- [ ] Create `app/Actions/Documents/UploadDocument.php`:
  - Compute SHA-256 hash
  - Store in `private/documents/{firm_uuid}/{matter_uuid}/{hash}.{ext}`
  - Create document + version records
  - Record audit log

#### 4.2 Document UI
- [ ] Create `app/Http/Livewire/Documents/DocumentList.php`:
  - Global document repository with category filter tabs
  - Search by document name
  - Sort by date, name, size
  - Paginated
- [ ] Create `app/Http/Livewire/Documents/DocumentUpload.php`:
  - Drag-and-drop zone
  - File type validation (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, HEIC)
  - Size validation (max 50MB)
  - Progress bar
  - Matter + category selector
  - Visibility selector (Private/Internal/Client Shared)

### Afternoon Session (4 hours)

#### 4.3 Secure Download & Access Control
- [ ] Create `app/Http/Controllers/DocumentDownloadController.php`:
  - Stream file through authenticated route
  - Check authorization via Policy
  - Record download in audit log
  - Set proper Content-Type and Content-Disposition headers
- [ ] Create `app/Policies/DocumentPolicy.php`:
  - `view` — firm scope + matter access + visibility check
  - `download` — same as view
  - `upload` — matter team member
  - `share` — attorney/admin only
  - `delete` — owner or admin
- [ ] Create document preview component (in-browser for PDF/images)
- [ ] Wire documents into Matter Dashboard "Work Product" and "Client Files" tabs

#### 4.4 Write Tests
- [ ] `tests/Feature/Documents/DocumentUploadTest.php` — 10 test cases
- [ ] `tests/Feature/Documents/DocumentAccessTest.php` — 12 test cases (visibility, cross-matter, direct URL)
- [ ] `tests/Feature/Documents/DocumentDownloadTest.php` — 5 test cases

### Day 4 Definition of Done ✅
- [ ] Documents upload with drag-and-drop
- [ ] Files stored securely outside `public/` directory
- [ ] SHA-256 hash computed for deduplication
- [ ] Categories filter works
- [ ] Visibility levels (Private/Internal/Client Shared) enforced
- [ ] Secure download streams through authenticated route
- [ ] Direct file URL access returns 403
- [ ] All 27 tests pass

---

## 📅 Day 5 — Document Versioning, Sharing & Request Workflow

### Morning Session (4 hours)

#### 5.1 Document Versioning
- [ ] Create `app/Actions/Documents/CreateNewVersion.php`:
  - Increment version number
  - Store new file without overwriting previous
  - Update `documents.current_version_id` and `version_count`
  - Record audit log
- [ ] Create version history UI in document detail:
  - List all versions with version number, date, uploader, file size
  - Download any specific version
  - View change notes

#### 5.2 Document Sharing
- [ ] Create migration: `create_document_shares_table`
- [ ] Create `app/Actions/Documents/ShareDocument.php`:
  - Create share record with permission level (view/download)
  - Optional expiration date
  - Send notification to recipient
- [ ] Create sharing UI:
  - Share modal with user selector
  - Permission level dropdown
  - Expiration date picker
  - List of current shares with revoke option

### Afternoon Session (4 hours)

#### 5.3 Document Request Workflow
- [ ] Create migration: `create_document_requests_table`
- [ ] Create `app/Models/DocumentRequest.php` with status state machine
- [ ] Create `app/Actions/DocumentRequests/CreateRequest.php`
- [ ] Create `app/Actions/DocumentRequests/SubmitDocument.php` (client uploads)
- [ ] Create `app/Actions/DocumentRequests/ReviewSubmission.php` (accept/reject)
- [ ] Create `app/Http/Livewire/DocumentRequests/RequestList.php` (attorney view)
- [ ] Create `app/Http/Livewire/DocumentRequests/CreateRequestForm.php`
- [ ] Create `app/Http/Livewire/DocumentRequests/ReviewRequest.php`
- [ ] Wire into Matter Dashboard "Requests" tab
- [ ] Create notifications:
  - `DocumentRequestCreated` → client
  - `DocumentSubmitted` → requesting attorney
  - `DocumentAccepted` → client
  - `DocumentRejected` → client

#### 5.4 Write Tests
- [ ] `tests/Feature/Documents/DocumentVersionTest.php` — 5 test cases
- [ ] `tests/Feature/Documents/DocumentShareTest.php` — 6 test cases
- [ ] `tests/Feature/DocumentRequests/RequestWorkflowTest.php` — 10 test cases

### Day 5 Definition of Done ✅
- [ ] New document versions upload without overwriting old ones
- [ ] Version history shows all versions with download links
- [ ] Documents can be shared with specific users (view/download)
- [ ] Document request full lifecycle: create → notify → submit → review → accept/reject
- [ ] Status transitions enforced (can't accept without submission)
- [ ] All 21 tests pass

---

## 📅 Day 6 — Client Portal & Mobile UX

### Morning Session (4 hours)

#### 6.1 Client Portal Core
- [ ] Create portal routes in `routes/web.php` with `role:client` middleware
- [ ] Create `app/Http/Controllers/Portal/PortalDashboardController.php`
- [ ] Create portal layout (`resources/views/layouts/portal.blade.php`):
  - Simplified top navigation bar
  - Mobile-first responsive design
  - Quire branding with client-friendly language
- [ ] Build portal dashboard:
  - Active matters summary
  - Action-required items (pending document requests, unread messages)
  - Upcoming events (client-visible only)
  - Recent documents shared with client
- [ ] Build portal matter view:
  - Case stage tracker (visual progress bar — Stage X of 7)
  - Client-shared documents
  - Pending document requests
  - Messages with legal team
  - Assigned tasks

#### 6.2 Portal Document Requests
- [ ] Create portal request list showing pending requests from attorney
- [ ] Create portal upload interface:
  - File upload button
  - Camera capture button (mobile)
  - Preview before submission
  - Retake option for camera capture

### Afternoon Session (4 hours)

#### 6.3 Mobile Camera Capture
- [ ] Implement camera capture component with Alpine.js:
  - `navigator.mediaDevices.getUserMedia()` for rear camera
  - Live video preview in viewport
  - Capture button with photo preview
  - Retake / Confirm workflow
  - Convert to JPEG blob, attach to upload form
- [ ] Test on iOS Safari and Android Chrome
- [ ] Add PWA manifest (`public/manifest.json`):
  - App name, icons, theme color, display: standalone
  - Service worker registration (offline page only for MVP)

#### 6.4 Client Portal Security Testing
- [ ] Verify: Client cannot access other clients' matters
- [ ] Verify: Client cannot see internal notes
- [ ] Verify: Client cannot see internal message threads
- [ ] Verify: Client cannot access documents with visibility != 'client_shared'
- [ ] Verify: Client cannot modify matter status

#### 6.5 Write Tests
- [ ] `tests/Feature/Portal/PortalDashboardTest.php` — 5 test cases
- [ ] `tests/Feature/Portal/PortalIsolationTest.php` — 10 test cases (critical security)
- [ ] `tests/Feature/Portal/PortalDocumentRequestTest.php` — 5 test cases

### Day 6 Definition of Done ✅
- [ ] Client portal dashboard shows correct data for logged-in client
- [ ] Case stage tracker visualizes matter progress
- [ ] Client can upload documents for requests (file + camera)
- [ ] Camera capture works on mobile (iOS + Android)
- [ ] Client isolation is complete — zero information leakage
- [ ] PWA manifest installed, app can be added to home screen
- [ ] All 20 tests pass

---

## 📅 Day 7 — Matter-Based Chat & Messaging

### Morning Session (4 hours)

#### 7.1 Messaging Infrastructure
- [ ] Create migrations:
  - `create_message_threads_table`
  - `create_messages_table`
  - `create_message_attachments_table`
  - `create_message_reads_table`
- [ ] Create models: `MessageThread`, `Message`, `MessageAttachment`
- [ ] Create `app/Actions/Messages/CreateThread.php`
- [ ] Create `app/Actions/Messages/SendMessage.php`
- [ ] Create `app/Actions/Messages/MarkThreadAsRead.php`
- [ ] Create `app/Policies/MessageThreadPolicy.php`:
  - Internal threads: only firm staff on assigned matter
  - Client threads: staff + client for that matter

#### 7.2 Messaging UI
- [ ] Create `app/Http/Livewire/Messages/MessageInbox.php`:
  - Thread list with unread indicators
  - Filter by matter
  - Thread type badges (Internal / Client)
  - Last message preview, sender, timestamp
- [ ] Create `app/Http/Livewire/Messages/ThreadView.php`:
  - Message bubbles with sender avatar, timestamp
  - New message input with send button
  - Attachment upload
  - Livewire polling (5s refresh)
  - Auto-mark as read on view

### Afternoon Session (4 hours)

#### 7.3 Message Notifications & Integration
- [ ] Create `app/Notifications/NewMessage.php` (email + in-app)
- [ ] Wire messaging into Matter Dashboard "Messages" tab
- [ ] Add unread message count to sidebar navigation badge
- [ ] Implement client-facing messaging in portal:
  - Client can only see/send in `type=client` threads
  - Client cannot see `type=internal` threads
- [ ] Create new thread from matter dashboard

#### 7.4 Write Tests
- [ ] `tests/Feature/Messages/MessageCrudTest.php` — 8 test cases
- [ ] `tests/Feature/Messages/MessageAccessTest.php` — 10 test cases (internal vs client, cross-matter)
- [ ] `tests/Feature/Messages/MessageReadTest.php` — 3 test cases

### Day 7 Definition of Done ✅
- [ ] Message threads can be created (internal + client-facing)
- [ ] Messages sent with text and attachments
- [ ] Read/unread tracking works correctly
- [ ] Livewire polling refreshes thread view every 5s
- [ ] Unread badge shows in sidebar
- [ ] Client ONLY sees client-facing threads
- [ ] Client CANNOT see internal threads
- [ ] New message notification sent (email + in-app)
- [ ] All 21 tests pass

---

## 📅 Day 8 — Tasks, Todo List & Notes

### Morning Session (4 hours)

#### 8.1 Tasks
- [ ] Create migrations: `create_tasks_table`, `create_task_comments_table`
- [ ] Create `app/Models/Task.php`, `app/Models/TaskComment.php`
- [ ] Create `app/Actions/Tasks/CreateTask.php`
- [ ] Create `app/Actions/Tasks/CompleteTask.php`
- [ ] Create `app/Actions/Tasks/AssignTask.php`
- [ ] Create `app/Policies/TaskPolicy.php`
- [ ] Create `app/Http/Livewire/Tasks/TaskList.php`:
  - Filter by status, priority, assignee, matter
  - Sort by due date, priority
  - Completion checkbox toggle
  - Priority badge colors (low=gray, normal=blue, high=orange, urgent=red)
- [ ] Create `app/Http/Livewire/Tasks/CreateTaskForm.php`
- [ ] Create `app/Http/Livewire/Tasks/TaskDetail.php` with comments
- [ ] Wire into Matter Dashboard "Tasks" tab

#### 8.2 Todo List
- [ ] Create migration: `create_todos_table`
- [ ] Create `app/Models/Todo.php`
- [ ] Create `app/Http/Livewire/Todos/TodoList.php`:
  - Personal todo items with checkbox
  - Inline create (type and press Enter)
  - Optional matter reference
  - Drag-and-drop reorder (Alpine.js SortableJS)

### Afternoon Session (4 hours)

#### 8.3 Notes
- [ ] Create migration: `create_notes_table`
- [ ] Create `app/Models/Note.php`
- [ ] Create `app/Policies/NotePolicy.php` (critical: internal note isolation)
- [ ] Create `app/Http/Livewire/Notes/NoteList.php`:
  - Filter by matter, type (internal/client-visible)
  - Type badge (🔒 Internal / 👤 Client Visible)
- [ ] Create `app/Http/Livewire/Notes/NoteEditor.php`:
  - Rich text editor (Trix)
  - Type selector with warning: "⚠️ Client-visible notes will be shown to the client"
  - Pin/unpin toggle
- [ ] Wire into Matter Dashboard "Internal Notes" tab

#### 8.4 Wire Portal Components
- [ ] Add client-visible tasks to portal
- [ ] Add client-visible notes to portal
- [ ] Verify internal notes NEVER appear in portal

#### 8.5 Write Tests
- [ ] `tests/Feature/Tasks/TaskCrudTest.php` — 8 test cases
- [ ] `tests/Feature/Notes/NoteIsolationTest.php` — 8 test cases (critical: internal notes)
- [ ] `tests/Feature/Todos/TodoTest.php` — 3 test cases

### Day 8 Definition of Done ✅
- [ ] Tasks can be created, assigned, completed with priority levels
- [ ] Task comments work
- [ ] Overdue tasks highlighted
- [ ] Todo list works with inline create and checkbox
- [ ] Notes support internal and client-visible types
- [ ] Rich text editing works (Trix)
- [ ] Internal notes are NEVER visible in client portal
- [ ] All 19 tests pass

---

## 📅 Day 9 — Internal Calendar + Google Calendar Integration

### Morning Session (4 hours)

#### 9.1 Internal Calendar
- [ ] Create migrations: `create_calendar_events_table`, `create_calendar_event_attendees_table`
- [ ] Create `app/Models/CalendarEvent.php`
- [ ] Create `app/Actions/Calendar/CreateEvent.php`
- [ ] Create `app/Actions/Calendar/UpdateEvent.php`
- [ ] Install FullCalendar.js via npm
- [ ] Create `app/Http/Livewire/Calendar/CalendarView.php`:
  - Month/Week/Day/Agenda views (FullCalendar.js)
  - Color-coded by event type
  - Client visibility badge on events
  - Click event to view details
  - Click date to create new event
- [ ] Create event creation/edit modal:
  - Title, description, location
  - Start/end datetime pickers
  - All-day toggle
  - Matter selector
  - Attendees selector
  - Client visible checkbox
  - Reminder dropdown (15min, 30min, 1hr, 1 day)
  - Event type selector (hearing, meeting, deadline, filing, etc.)

### Afternoon Session (4 hours)

#### 9.2 Google Calendar Integration
- [ ] Install `google/apiclient` via Composer
- [ ] Create migration: `create_google_calendar_connections_table`
- [ ] Create `app/Services/GoogleCalendarService.php`:
  - `connect()` — initiate OAuth flow
  - `handleCallback()` — exchange code for tokens, encrypt and store
  - `refreshTokenIfNeeded()` — auto-refresh expired tokens
  - `createEvent()` — push Quire event to Google Calendar
  - `updateEvent()` — update synced event
  - `deleteEvent()` — remove synced event
  - `importEvents()` — pull events from Google Calendar
- [ ] Create OAuth routes:
  - `GET /google/calendar/connect` — redirect to Google
  - `GET /google/calendar/callback` — handle OAuth callback
  - `POST /google/calendar/disconnect` — revoke access
- [ ] Create connection UI in Account Settings:
  - "Connect Google Calendar" button
  - Connected account display with email
  - Sync toggle
  - Disconnect button
- [ ] Add sync logic to calendar event actions (push on create/update/delete)

#### 9.3 Wire Calendar to Portal
- [ ] Show client-visible events in portal calendar
- [ ] Wire calendar into Matter Dashboard

#### 9.4 Write Tests
- [ ] `tests/Feature/Calendar/CalendarCrudTest.php` — 8 test cases
- [ ] `tests/Feature/Calendar/CalendarAccessTest.php` — 5 test cases
- [ ] `tests/Feature/Google/GoogleCalendarTest.php` — 5 test cases (mocked API)

### Day 9 Definition of Done ✅
- [ ] Internal calendar renders with FullCalendar.js (4 views)
- [ ] Events can be created, edited, deleted
- [ ] Events color-coded by type
- [ ] Client visibility flag respected in portal
- [ ] Google Calendar OAuth connection works
- [ ] Events push to Google Calendar on create/update/delete
- [ ] Token refresh works automatically
- [ ] All 18 tests pass

---

## 📅 Day 10 — Google Drive Integration

### Morning Session (4 hours)

#### 10.1 Google Drive Connection
- [ ] Create migrations: `create_google_drive_connections_table`, `create_google_drive_files_table`
- [ ] Create `app/Services/GoogleDriveService.php`:
  - `connect()` — initiate OAuth flow (Drive scope)
  - `handleCallback()` — exchange code, encrypt tokens
  - `refreshTokenIfNeeded()`
  - `listFolders()` — browse Drive folder structure
  - `listFiles()` — list files in a folder
  - `importFile()` — download from Drive → store in Quire
  - `exportFile()` — upload from Quire → Drive
  - `linkFolder()` — associate a Drive folder with a matter
- [ ] Create OAuth routes for Drive
- [ ] Create connection UI in Account Settings

#### 10.2 Matter-Level Drive Integration
- [ ] Create `app/Http/Livewire/Drive/DriveFolderBrowser.php`:
  - Tree view of Drive folders
  - File list within selected folder
  - "Link this folder to matter" button
  - "Import selected files" button
- [ ] Create `app/Http/Livewire/Drive/DriveExport.php`:
  - Select Quire documents to export
  - Choose destination folder in Drive
  - Export progress indicator

### Afternoon Session (4 hours)

#### 10.3 Integration Polish
- [ ] Wire Drive into Matter Dashboard (separate sub-tab or within documents)
- [ ] Show linked Drive folder status on matter
- [ ] Create `app/Jobs/ImportDriveFile.php` (queued job for large files)
- [ ] Create `app/Jobs/ExportToDrive.php` (queued job)
- [ ] Add audit logging for all Drive operations

#### 10.4 Write Tests
- [ ] `tests/Feature/Google/GoogleDriveTest.php` — 8 test cases (mocked API)
- [ ] `tests/Feature/Google/DriveImportExportTest.php` — 5 test cases

### Day 10 Definition of Done ✅
- [ ] Google Drive OAuth connection works
- [ ] Drive folder browser displays folder tree
- [ ] Folders can be linked to matters
- [ ] Files can be imported from Drive → Quire document management
- [ ] Files can be exported from Quire → Drive
- [ ] Import/export runs as background jobs
- [ ] All 13 tests pass

---

## 📅 Day 11 — Global Search + Billing Data Model

### Morning Session (4 hours)

#### 11.1 Global Search
- [ ] Create `app/Services/GlobalSearchService.php`:
  - Search clients (name, email, company)
  - Search matters (title, description, court case number)
  - Search documents (title, description)
  - Search messages (body)
  - Search tasks (title, description)
  - Search notes (title, body — respecting internal/client-visible for clients)
  - All queries filtered by firm_id AND matter access
- [ ] Create `app/Http/Livewire/GlobalSearch.php`:
  - Search input in top header bar
  - Debounced input (300ms)
  - Minimum 3 characters
  - Results dropdown grouped by type (clients, matters, documents, etc.)
  - Keyboard navigation (arrow keys + Enter)
  - Click result to navigate
  - "See all results" link to full search page
- [ ] Create full search results page with filters

### Afternoon Session (4 hours)

#### 11.2 Billing Data Model & UI
- [ ] Create migrations:
  - `create_invoices_table`
  - `create_invoice_line_items_table`
  - `create_time_entries_table`
  - `create_trust_accounts_table`
- [ ] Create models: `Invoice`, `InvoiceLineItem`, `TimeEntry`, `TrustAccount`
- [ ] Create `app/Http/Livewire/Billing/InvoiceList.php`:
  - Filter by status (draft, sent, paid, overdue)
  - Filter by client, matter
  - Summary totals (outstanding, overdue, collected)
- [ ] Create `app/Http/Livewire/Billing/InvoiceDetail.php`:
  - Line items table
  - Totals (subtotal, tax, total, paid, balance due)
  - Status badge
  - Payment history
- [ ] Create `app/Http/Livewire/Billing/CreateInvoice.php`:
  - Client + matter selector
  - Add line items (description, quantity, rate)
  - Pull unbilled time entries
  - Auto-calculate totals
- [ ] Create `app/Http/Livewire/Billing/TimeEntryList.php`:
  - Log time entries per matter
  - Billable/non-billable toggle
- [ ] Wire billing into portal (client can view + pay)

#### 11.3 Write Tests
- [ ] `tests/Feature/Search/GlobalSearchTest.php` — 10 test cases (auth-aware filtering)
- [ ] `tests/Feature/Billing/InvoiceTest.php` — 8 test cases

### Day 11 Definition of Done ✅
- [ ] Global search returns results across all modules
- [ ] Search is authorization-aware (firm + matter filtered)
- [ ] Search debounces and shows grouped results
- [ ] Invoices can be created with line items
- [ ] Time entries can be logged per matter
- [ ] Client can view invoices in portal
- [ ] All 18 tests pass

---

## 📅 Day 12 — Analytics Dashboard & Charts

### Morning Session (4 hours)

#### 12.1 Dashboard Metrics
- [ ] Create `app/Services/DashboardService.php`:
  - Open/active/closed matter counts
  - Documents uploaded/shared/requested counts
  - Pending/completed/overdue task counts
  - Active/new client counts
  - Revenue metrics (invoiced, collected, outstanding)
  - Matter status distribution
  - Task completion rate (last 30 days)
  - Document upload trend (last 30 days)
- [ ] Add 5-minute caching on all dashboard metrics
- [ ] Create `app/Http/Livewire/Dashboard/FirmDashboard.php`

#### 12.2 Dashboard UI
- [ ] Build KPI cards (top row):
  - Open Matters (with trend arrow)
  - Tasks Due This Week
  - Uploads to Review
  - Outstanding Balance
- [ ] Build "Your Tasks" checklist (checkbox + matter link + due date)
- [ ] Build "Waiting on You" section:
  - Clients awaiting reply (unread messages)
  - Client uploads to review (submitted document requests)
- [ ] Build "Recent Activity" feed (timeline of audit log events)
- [ ] Build "Next Two Weeks" calendar summary

### Afternoon Session (4 hours)

#### 12.3 Charts (Chart.js)
- [ ] Matter Status Distribution — Doughnut chart
- [ ] Task Completion Rate — Bar chart (last 12 weeks)
- [ ] Document Activity — Line chart (uploads per week)
- [ ] Client Growth — Area chart (new clients per month)
- [ ] Revenue Trend — Line chart (monthly invoiced vs collected)
- [ ] Create dedicated Analytics page (`/app/analytics`) with all charts

#### 12.4 Admin Dashboard
- [ ] Create Super Admin dashboard:
  - Platform-wide metrics (total firms, users, matters)
  - Subscription revenue breakdown
  - Active vs inactive firms
  - Storage usage per firm
  - Recent sign-in log

#### 12.5 Write Tests
- [ ] `tests/Feature/Dashboard/DashboardMetricsTest.php` — 5 test cases
- [ ] `tests/Feature/Analytics/AnalyticsAccessTest.php` — 3 test cases

### Day 12 Definition of Done ✅
- [ ] Firm dashboard shows all KPI cards with live data
- [ ] "Your Tasks" and "Waiting on You" sections populated
- [ ] Activity feed shows recent actions
- [ ] All 5 charts render correctly with real data
- [ ] Analytics page accessible to attorneys/admins
- [ ] Admin dashboard shows platform-wide metrics
- [ ] Dashboard loads in <2 seconds (cached)
- [ ] All 8 tests pass

---

## 📅 Day 13 — Notifications, Audit Logs, Settings & Polish

### Morning Session (4 hours)

#### 13.1 Notification System
- [ ] Create all notification classes:
  - `UserInvited`, `DocumentUploaded`, `DocumentRequestCreated`, `DocumentSubmitted`
  - `DocumentAccepted`, `DocumentRejected`, `NewMessage`, `TaskAssigned`
  - `TaskOverdue`, `TaskCompleted`, `MatterStatusChanged`, `CalendarEventCreated`
- [ ] Create `app/Http/Livewire/Notifications/NotificationCenter.php`:
  - Bell icon with unread count badge
  - Dropdown list of recent notifications
  - Mark as read / Mark all as read
  - Click notification to navigate to relevant page
- [ ] Create notifications page (`/app/notifications`) with full history
- [ ] Implement user notification preferences (email toggle per event type)
- [ ] Configure queue for email notifications (ShouldQueue on all)

#### 13.2 Audit Logging Polish
- [ ] Create migration: `create_audit_logs_table`
- [ ] Create `app/Models/AuditLog.php` with static `record()` method
- [ ] Verify audit logging in all existing Actions:
  - Login/logout ✓
  - Client create/update ✓
  - Matter create/update/status change ✓
  - Document upload/download/share/delete ✓
  - Message creation ✓
  - Task creation/completion ✓
  - Permission changes ✓
- [ ] Create `app/Http/Livewire/Firm/AuditLogViewer.php`:
  - Filterable by action, user, entity type, date range
  - Paginated (50 per page)
  - Export to CSV

### Afternoon Session (4 hours)

#### 13.3 Settings Pages
- [ ] Create `app/Http/Livewire/Firm/FirmProfile.php`:
  - Firm name, address, phone, email, website
  - Logo upload
  - Timezone selector
  - Date format selector
  - Currency selector
- [ ] Create `app/Http/Livewire/Firm/DocumentCategoryManager.php`:
  - Add/edit/delete/reorder categories
  - Color picker
  - System categories can't be deleted
- [ ] Create `app/Http/Livewire/Account/ProfileSettings.php`
- [ ] Create `app/Http/Livewire/Account/NotificationSettings.php`
- [ ] Create `app/Http/Livewire/Account/SecuritySettings.php`

#### 13.4 UI/UX Polish
- [ ] Review all pages for responsive design (mobile, tablet, desktop)
- [ ] Add loading states (skeleton screens) to all Livewire components
- [ ] Add toast notifications for success/error feedback
- [ ] Review and fix any accessibility issues (ARIA labels, focus management)
- [ ] Add empty states with helpful messages ("No matters yet. Create your first matter →")
- [ ] Polish transitions and micro-animations
- [ ] Verify consistent styling across all pages

### Day 13 Definition of Done ✅
- [ ] All 12 notification types send correctly (email + in-app)
- [ ] Notification center shows unread count and list
- [ ] Notification preferences save and apply
- [ ] Audit log viewer works with filters and pagination
- [ ] All settings pages save correctly
- [ ] UI is polished, responsive, and consistent
- [ ] No broken layouts on mobile

---

## 📅 Day 14 — Full QA, Security Testing, Deployment & Documentation

### Morning Session (4 hours)

#### 14.1 Full Test Suite Run
- [ ] Run complete test suite: `php artisan test`
- [ ] Fix any failing tests
- [ ] Target: 200+ tests passing, 0 failures
- [ ] Review test coverage: `php artisan test --coverage`

#### 14.2 Security Testing Checklist
- [ ] **Cross-Client Isolation:** Client A cannot access Client B's data
- [ ] **Cross-Firm Isolation:** Firm A cannot access Firm B's data
- [ ] **Internal Note Exposure:** Client cannot see internal notes via any route
- [ ] **Direct Document URL:** Typing a document file path returns 403
- [ ] **UUID Manipulation:** Changing UUIDs in URLs doesn't bypass authorization
- [ ] **IDOR Testing:** Incrementing IDs doesn't reveal other users' data
- [ ] **CSRF Protection:** All forms have CSRF tokens
- [ ] **Rate Limiting:** Login rate limiting triggers correctly
- [ ] **Session Security:** Sessions invalidate on password change
- [ ] **2FA Enforcement:** 2FA required routes redirect properly
- [ ] **Message Thread Access:** Internal threads invisible to clients
- [ ] **Calendar Visibility:** Client can't see firm-only events
- [ ] **File Upload Validation:** Only allowed file types accepted
- [ ] **XSS Prevention:** User input rendered safely in all views

### Afternoon Session (4 hours)

#### 14.3 Hostinger Deployment
- [ ] Prepare production `.env`:
  - `APP_ENV=production`, `APP_DEBUG=false`
  - `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`
  - Database credentials
  - SMTP credentials
  - Google OAuth credentials with production redirect URIs
  - `APP_KEY` generated securely
- [ ] Upload codebase to Hostinger (Git or SFTP)
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm run build` (compile assets for production)
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan db:seed --class=ProductionSeeder`
- [ ] Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Set up cron job: `* * * * * php /path/to/artisan schedule:run`
- [ ] Set `storage/` directory permissions correctly
- [ ] Verify HTTPS works with SSL certificate
- [ ] Test login flow on production
- [ ] Test document upload/download on production
- [ ] Test email sending on production

#### 14.4 Documentation
- [ ] Create `/docs/01-product-requirements.md`
- [ ] Create `/docs/02-architecture.md` (ARCHITECTURE.md)
- [ ] Create `/docs/03-database.md` with ERD
- [ ] Create `/docs/04-roles-permissions.md`
- [ ] Create `/docs/07-installation.md`
- [ ] Create `/docs/08-deployment.md`
- [ ] Create `/docs/09-environment-variables.md`
- [ ] Update `README.md` with project overview and setup instructions

### Day 14 Definition of Done ✅
- [ ] 200+ tests pass with 0 failures
- [ ] All 14 security tests pass
- [ ] Application deployed and running on Hostinger
- [ ] HTTPS working
- [ ] Login, document upload, email sending verified on production
- [ ] Cron job running scheduled commands
- [ ] Core documentation written
- [ ] Git tagged as `v1.0.0-mvp`

---

## 📊 Sprint Metrics Tracker

### Daily Progress

| Day | Module | Planned Files | Tests Planned | Tests Passing | Status |
|-----|--------|:------------:|:-------------:|:-------------:|--------|
| 1 | Foundation & Auth | ~25 | 0 | 0 | ⬜ Not Started |
| 2 | Auth Hardening, Users, Roles | ~15 | 26 | 0 | ⬜ Not Started |
| 3 | Clients & Matters | ~20 | 31 | 0 | ⬜ Not Started |
| 4 | Document Upload & Access | ~15 | 27 | 0 | ⬜ Not Started |
| 5 | Versioning, Sharing, Requests | ~15 | 21 | 0 | ⬜ Not Started |
| 6 | Client Portal & Mobile | ~12 | 20 | 0 | ⬜ Not Started |
| 7 | Messaging | ~10 | 21 | 0 | ⬜ Not Started |
| 8 | Tasks, Todo, Notes | ~15 | 19 | 0 | ⬜ Not Started |
| 9 | Calendar + Google Cal | ~12 | 18 | 0 | ⬜ Not Started |
| 10 | Google Drive | ~10 | 13 | 0 | ⬜ Not Started |
| 11 | Search + Billing | ~12 | 18 | 0 | ⬜ Not Started |
| 12 | Analytics | ~8 | 8 | 0 | ⬜ Not Started |
| 13 | Notifications, Audit, Settings | ~15 | 0 | 0 | ⬜ Not Started |
| 14 | QA, Security, Deploy, Docs | ~10 | 14 | 0 | ⬜ Not Started |
| **TOTAL** | | **~194** | **236** | **0** | |

### Risk Register

| Risk | Probability | Impact | Mitigation |
|------|:-----------:|:------:|-----------|
| Google OAuth configuration issues | 🟡 Medium | 🟡 Medium | Set up early (Day 0); test locally before Day 9 |
| Hostinger PHP version/extension limitations | 🟡 Medium | 🔴 High | Verify PHP 8.3+ and required extensions on Day 0 |
| File upload size limits on shared hosting | 🟡 Medium | 🟡 Medium | Check `upload_max_filesize` and `post_max_size` early |
| Livewire performance on complex pages | 🟢 Low | 🟡 Medium | Use lazy-loading, wire:init, and pagination |
| Camera capture browser compatibility | 🟡 Medium | 🟢 Low | Test on iOS Safari + Android Chrome on Day 6 |
| Scope creep — adding features beyond MVP | 🔴 High | 🔴 High | Strict adherence to this work plan; log requests for Phase 2 |
| Database queue reliability on shared hosting | 🟡 Medium | 🟡 Medium | Monitor failed_jobs table; retry mechanism |
| FullCalendar.js bundle size | 🟢 Low | 🟢 Low | Tree-shake unused plugins; lazy-load calendar route |

### Dependencies Between Days

```
Day 1 (Foundation) ──────► Day 2 (Auth + Roles) ──────► Day 3 (Clients + Matters)
                                                              │
                    ┌─────────────────────────────────────────┤
                    ▼                                         ▼
              Day 4 (Documents) ──► Day 5 (Versioning + Requests)
                    │                     │
                    ▼                     ▼
              Day 6 (Portal) ◄──── Day 7 (Messaging)
                    │
                    ▼
              Day 8 (Tasks + Notes)
                    │
                    ▼
              Day 9 (Calendar + Google Cal) ──► Day 10 (Google Drive)
                    │
                    ▼
              Day 11 (Search + Billing) ──► Day 12 (Analytics)
                    │
                    ▼
              Day 13 (Notifications + Polish) ──► Day 14 (QA + Deploy)
```

---

## 🔄 Development Workflow (Per Feature)

Follow this workflow for every feature within each day:

```
1. REQUIREMENT    → Review this work plan + feature spec
2. DATABASE       → Write and run migration
3. MODEL          → Create Eloquent model with relationships
4. POLICY         → Write authorization policy
5. ACTION/SERVICE → Implement business logic
6. CONTROLLER     → Create thin controller/Livewire component
7. UI             → Build Blade views with Tailwind
8. TESTS          → Write feature tests
9. REVIEW         → Manual testing + code review
10. COMMIT        → Descriptive commit message, push to GitHub
```

### Git Commit Convention
```
feat(module): short description

Examples:
feat(auth): implement progressive lockout after failed attempts
feat(documents): add camera capture upload for mobile clients
fix(portal): prevent client from viewing internal notes
test(security): add cross-firm isolation tests
docs(architecture): update ERD with billing tables
```

### Branch Strategy
```
main                    ← production (deployed to Hostinger)
├── develop             ← integration branch
│   ├── feat/day-01-foundation
│   ├── feat/day-02-auth-roles
│   ├── feat/day-03-clients-matters
│   └── ...
```

---

## 📞 End-of-Day Checklist (Every Day)

- [ ] All planned features implemented
- [ ] All planned tests pass
- [ ] Code committed and pushed to GitHub
- [ ] No `dd()` or `dump()` left in code
- [ ] No `console.log()` left in JavaScript
- [ ] Mobile responsiveness verified
- [ ] Demo data updated if needed
- [ ] Brief daily log entry in `CHANGELOG.md`

---

## 🎯 MVP Success Criteria (Final Validation)

The MVP is complete when this end-to-end workflow works smoothly:

```
1. ✅ Attorney logs in (email + password + optional 2FA)
2. ✅ Attorney creates a new client (Elena Marsh, individual)
3. ✅ Attorney creates a new matter ("Marsh v. Castellan Property Group")
4. ✅ Attorney assigns paralegal to the matter
5. ✅ Attorney creates a document request ("Please upload your government ID")
6. ✅ Client receives email notification about the request
7. ✅ Client logs into portal
8. ✅ Client sees the document request on their dashboard
9. ✅ Client uses camera to capture their ID photo
10. ✅ Client previews and submits the photo
11. ✅ Attorney receives notification of submission
12. ✅ Attorney reviews and accepts the document
13. ✅ Attorney sends a message: "Thank you, received your ID"
14. ✅ Client receives message notification
15. ✅ Attorney creates a task: "Prepare discovery response" (assigned to paralegal)
16. ✅ Paralegal receives task notification
17. ✅ Attorney schedules a court hearing on the calendar
18. ✅ Event syncs to Google Calendar (if connected)
19. ✅ Client sees the hearing on their portal calendar (client-visible)
20. ✅ All actions appear in the audit log
21. ✅ All of the above works on mobile
22. ✅ Attorney from Firm B cannot see ANY of the above data
```

---

> **This work plan is the project's execution roadmap. Every day references the [Feature Specification](./QUIRE_FEATURE_SPECIFICATION.md) for detailed implementation guidance. Deviations from this plan should be documented and approved.**
