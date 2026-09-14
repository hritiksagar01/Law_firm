# ⚖️ Legal Case & Document Management System
## Complete Feature Specification & Implementation Blueprint

> **Version:** MVP v1.0 &nbsp;|&nbsp; **Feature Reference:** [quire-legal.vercel.app](https://quire-legal.vercel.app/login) *(used as feature/scope reference only — this is an entirely original product with its own design, architecture, and codebase)*
> **Stack:** Laravel 13 · PHP 8.3+ · MySQL 8 / PostgreSQL 16 (database-agnostic) · Blade + Livewire + Alpine.js · Tailwind CSS 4
> **Infrastructure:** Hetzner / Railway (app) · Neon / Supabase (DB) · Amazon S3 / Cloudflare R2 (files) · Upstash Redis · Brevo (email)
> **Target:** 2.5 weeks (~17 working days) + stabilization/UAT
> **Last Updated:** September 14, 2026
>
> ⚠️ **Note:** Quire (quire-legal.vercel.app) was reviewed as a **feature reference** — to understand what modules and workflows a legal practice management platform needs. The UI/UX design, architecture, code, and branding of this product are entirely original and independent. We are NOT cloning Quire.

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Technology Stack Deep-Dive](#2-technology-stack-deep-dive)
3. [Architecture & Design Philosophy](#3-architecture--design-philosophy)
4. [User Roles & Permission Matrix](#4-user-roles--permission-matrix)
5. [Feature Modules — Detailed Specification](#5-feature-modules--detailed-specification)
   - [F-01: Authentication & Security](#f-01-authentication--security)
   - [F-02: Users & Role Management](#f-02-users--role-management)
   - [F-03: Firm Management](#f-03-firm-management)
   - [F-04: Client Management](#f-04-client-management)
   - [F-05: Matter/Case Management](#f-05-mattercase-management)
   - [F-06: Document Management](#f-06-document-management)
   - [F-07: Document Request Workflow](#f-07-document-request-workflow)
   - [F-08: Client Portal](#f-08-client-portal)
   - [F-09: Matter-Based Chat/Messaging](#f-09-matter-based-chatmessaging)
   - [F-10: Tasks](#f-10-tasks)
   - [F-11: Todo List](#f-11-todo-list)
   - [F-12: Notes](#f-12-notes)
   - [F-13: Internal Calendar](#f-13-internal-calendar)
   - [F-14: Google Calendar Integration](#f-14-google-calendar-integration)
   - [F-15: Google Drive Integration](#f-15-google-drive-integration)
   - [F-16: Global Search](#f-16-global-search)
   - [F-17: Analytics & Dashboard](#f-17-analytics--dashboard)
   - [F-18: Notifications (Email + In-App)](#f-18-notifications-email--in-app)
   - [F-19: Audit Logging](#f-19-audit-logging)
   - [F-20: Billing & Invoicing](#f-20-billing--invoicing)
   - [F-21: Settings & Configuration](#f-21-settings--configuration)
6. [Database Architecture](#6-database-architecture)
7. [Security Architecture](#7-security-architecture)
8. [Scalability & Maintenance Assessment](#8-scalability--maintenance-assessment)
9. [Testing Strategy](#9-testing-strategy)
10. [Documentation Deliverables](#10-documentation-deliverables)

---

## 1. Executive Summary

**Quire** is a multi-tenant legal practice management SaaS platform designed for law firms of all sizes. The MVP delivers a complete end-to-end workflow: an attorney onboards a client → creates a matter → requests documents → the client receives a notification, logs in, captures/uploads documents → the attorney reviews, communicates, assigns tasks, and schedules calendar events — all audited and secured.

### Feature Scope (Derived from Quire Reference Analysis)

We reviewed [quire-legal.vercel.app](https://quire-legal.vercel.app/login) to understand the **feature scope** a legal platform needs. The following modules were identified as essential:

| Portal | Features Identified |
|--------|--------------------|
| **Law Firm Workspace** | Dashboard, Matters (list + detail with tabbed views), Clients (list + detail), Documents (with category filters), Messages, Tasks, Calendar, Billing (invoices + time entries + trust), Firm Settings (profile, team, roles matrix, categories, forms, audit), Account Settings, Notifications |
| **Client Portal** | Overview dashboard, document requests, secure messaging, case stage tracker, invoice payment |
| **Super Admin** | Platform dashboard, multi-firm management, seat allocations, audit logs, system settings |

### Design Language Observed
- **Color Palette:** Warm beige/cream backgrounds (`#fbfaf7`, `#f3efe6`), dark forest-green primary accents, dark sidebar (`#1a1d1a`)
- **Typography:** Serif for brand/headings ("Quire"), sans-serif for body/labels
- **Aesthetic:** Clean, minimal, professional legal SaaS — premium feel without visual clutter
- **Tagline:** "matters · documents · billing"

---

## 2. Technology Stack Deep-Dive

### Why This Stack? — Justification & Modern Alternatives

| Layer | Chosen Technology | Why This Choice | Considered Alternative | Why This Over the Alternative |
|-------|-------------------|-----------------|------------------------|-------------------------------|
| **Backend** | Laravel 13 / PHP 8.3+ | Mature ecosystem, built-in auth/authorization/queues/mail, rapid development, massive community. Laravel 13 (2026) includes native type declarations, improved performance, and modern PHP 8.3 features (readonly classes, typed constants, `json_validate()`) | Spring Boot (Java), NestJS (Node), Django | Laravel's Blade+Livewire stack eliminates the need for a separate API layer; Spring Boot would need a full React/Vue frontend adding complexity |
| **Frontend** | Blade + Livewire 3 + Alpine.js | Server-rendered with reactive islands; Livewire 3 supports lazy-loading, SPA-like navigation (`wire:navigate`), real-time via `wire:stream`. No REST/GraphQL API overhead | React/Next.js, Vue/Nuxt, Inertia.js | SPA frameworks require separate API, CORS, token management; Livewire 3 delivers SPA-equivalent UX from server with zero JavaScript build complexity |
| **UI Framework** | Tailwind CSS 4 | Latest version with Lightning CSS engine (up to 100x faster builds), native `@theme` directive, automatic content detection, zero-config setup. Utility-first for pixel-perfect control | Bootstrap 5, Chakra UI, shadcn/ui | Tailwind 4 is the industry standard; tree-shakes to ~10KB in production |
| **Database** | MySQL 8 **OR** PostgreSQL 16 *(database-agnostic)* | Laravel's Eloquent ORM abstracts all database operations. Code is written once using Eloquent and works identically on both. **Choice is made at deployment time**, not at code time. PostgreSQL is recommended (JSONB, better FTS, partial indexes) but MySQL works perfectly if the hosting requires it | MariaDB, SQLite | Both MySQL 8 and PostgreSQL 16 support JSON, full-text search, CTEs, and window functions — either is production-ready |
| **File Storage** | Amazon S3 *(or Cloudflare R2 as cheaper alternative)* | S3: Industry standard, 99.999999999% durability, versioning, server-side encryption, pre-signed URLs for secure downloads. **R2 alternative:** Same S3-compatible API, **zero egress fees** (saves 60-80% vs S3 for download-heavy apps), 10GB free tier | GCP Cloud Storage, MinIO, Supabase Storage | Laravel `Storage::disk('s3')` works with both S3 and R2 (same API). R2 is significantly cheaper for projects with heavy downloads |
| **Real-time** | Laravel Reverb (WebSockets) | Laravel's first-party WebSocket server (released 2024). Self-hosted, free, no per-message pricing, handles 10K+ concurrent connections | Pusher (free: 200K msg/day), Soketi, Ably | Reverb is free forever and first-party — no vendor lock-in; Pusher's free tier works as fallback |
| **Search** | Database Full-Text Search *(agnostic)* | PostgreSQL: `tsvector`/`tsquery` with `ts_rank()` ranking, GIN indexes. MySQL: `MATCH AGAINST` in Boolean mode. Both handle 500K+ rows efficiently. `laravel/scout` abstracts the driver so switching to Meilisearch later is a one-line config change | Meilisearch, Algolia, Elasticsearch | Zero infrastructure overhead — search lives in the same database; external search adds cost and sync complexity |
| **Queue/Cache** | Upstash Redis *(serverless, free tier)* | Serverless Redis with **10K commands/day free**, pay-per-request pricing after that. No server to manage. Global replication available. Works as queue driver, cache, and session store for Laravel | Self-hosted Redis, Database Queue, SQS | Upstash is zero-ops — no Redis server to maintain; free tier covers MVP easily; database queue is fallback if Redis isn't needed |
| **Charts** | Chart.js 4 | Lightweight (~60KB gzipped), Canvas-based, responsive, 8 chart types, tree-shakeable ESM modules | ApexCharts, D3.js, Recharts | Best size-to-capability ratio; D3 is overkill for dashboards |
| **Email** | Brevo (formerly Sendinblue) | **300 free emails/day**, DKIM/SPF authentication, delivery tracking, bounce handling, email templates, analytics dashboard. Laravel integration via `symfony/brevo-mailer` | SendGrid (100/day free), Postmark, Resend | Brevo has the most generous free tier; excellent deliverability; no credit card required to start |
| **App Hosting** | Hetzner VPS *(or Railway / Render)* | **Hetzner:** €3.79/mo for 2 vCPU, 2GB RAM, 40GB SSD — best price/performance in the market. **Railway:** $5/mo free credits, auto-deploy from GitHub, built-in metrics. **Render:** Free tier for web services | AWS EC2 ($15-30/mo), DigitalOcean ($6/mo), Hostinger | AWS is overkill and expensive for MVP; Hetzner gives 4x the resources at 1/4 the price; Railway is the simplest deploy experience |
| **Database Hosting** | Neon *(or Supabase)* for PostgreSQL | **Neon:** Free tier with 512MB storage, autoscaling, branching (create DB branches like Git!), sleep-on-idle. **Supabase:** Free tier with 500MB PostgreSQL + built-in Auth + Storage + Realtime. **For MySQL:** PlanetScale or Aiven free tiers | Self-hosted, AWS RDS ($15+/mo) | Neon/Supabase give managed PostgreSQL with **zero cost** and zero ops; RDS minimum is ~$15/mo |
| **Testing** | Pest 3 + PHPUnit 11 | Pest 3 (2024) with architecture testing, type coverage, mutation testing, parallel execution. Expressive `expect()` API | Codeception, Cypress | Pest 3 is the most modern PHP testing framework; architecture tests enforce code structure automatically |

### 💰 Monthly Cost Breakdown — Budget-Friendly MVP

| Service | Provider | Free Tier | Paid (if needed) |
|---------|----------|-----------|-------------------|
| **App Server** | Hetzner CX22 | — | **€3.79/mo** (~₹350/mo) |
| **Database** | Neon / Supabase | ✅ 512MB free | $0 (free tier covers MVP) |
| **File Storage** | Cloudflare R2 | ✅ 10GB free + zero egress | $0.015/GB after 10GB |
| **Redis** | Upstash | ✅ 10K cmd/day free | $0 (free tier covers MVP) |
| **Email** | Brevo | ✅ 300 emails/day free | $0 (free tier covers MVP) |
| **WebSockets** | Laravel Reverb | ✅ Self-hosted (free) | $0 |
| **Domain** | Any registrar | — | ~₹800/year |
| **SSL** | Let's Encrypt | ✅ Free | $0 |
| | | **TOTAL MVP COST:** | **~₹400/month** |

> 💡 Compare with AWS: EC2 ($15) + RDS ($15) + S3 ($3) + ElastiCache ($13) = **~$46/month (~₹3,800/mo)** — nearly 10x more expensive.

### Infrastructure Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                    Budget-Friendly Cloud                          │
│                                                                  │
│  ┌───────────────┐         ┌─────────────────────────────────┐   │
│  │  Cloudflare   │         │  Cloudflare R2 / Amazon S3      │   │
│  │  (DNS + CDN   │────────▶│  (Legal Documents, Attachments) │   │
│  │   + SSL)      │         │  Pre-signed URLs for downloads  │   │
│  └───────┬───────┘         └─────────────────────────────────┘   │
│          │                                                        │
│          ▼                                                        │
│  ┌───────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │  Hetzner VPS  │  │   Upstash    │  │ Neon / Supabase      │   │
│  │  (Laravel 13  │  │   Redis      │  │ PostgreSQL 16        │   │
│  │  + Reverb     │◀▶│  (Cache +    │  │ (Managed, free tier) │   │
│  │  WebSocket)   │  │   Queue +    │  │ Auto-backup          │   │
│  │               │  │   Sessions)  │  │ Sleep-on-idle        │   │
│  └───────┬───────┘  └──────────────┘  └──────────────────────┘   │
│          │                                                        │
│          ▼                                                        │
│  ┌───────────────┐                                                │
│  │    Brevo      │  (300 free emails/day)                         │
│  └───────────────┘                                                │
│                                                                    │
│  💰 Total: ~₹400/month (vs ₹3,800/month on AWS)                   │
└──────────────────────────────────────────────────────────────────┘
```

### Database-Agnostic Strategy

The entire codebase uses **Laravel Eloquent ORM** — no raw SQL queries are written. This means the same code runs on both MySQL and PostgreSQL:

```php
// This works identically on MySQL 8 and PostgreSQL 16:
Matter::where('firm_id', $firmId)
    ->whereIn('status', ['active', 'discovery'])
    ->with('client', 'leadAttorney')
    ->orderByDesc('updated_at')
    ->paginate(20);
```

**Database choice is a `.env` configuration, not a code decision:**
```
# .env — switch by changing one line:
DB_CONNECTION=pgsql    # PostgreSQL (recommended)
# DB_CONNECTION=mysql  # MySQL (also fully supported)
```

**Differences handled by Laravel migrations:**
- `ENUM` types → Laravel casts handle both MySQL native ENUMs and PostgreSQL ENUMs
- `JSON` columns → MySQL JSON and PostgreSQL JSONB both work with Eloquent `->casts(['tags' => 'array'])`
- Full-Text Search → abstracted via `laravel/scout` (database driver auto-detects MySQL/PostgreSQL)

### Scalability Trajectory

```
MVP (Now — ~₹400/mo)            Phase 2 (3-6 months)              Phase 3 (6-12 months)
──────────────────────────       ──────────────────────────         ──────────────────────────
Hetzner CX22 (€3.79/mo)         Hetzner CX32 or Railway Pro        AWS EC2 Auto Scaling / ECS
Neon/Supabase free tier          Neon Pro / Supabase Pro             AWS RDS / Aurora Serverless
Upstash Redis free               Upstash Pro                        AWS ElastiCache
Cloudflare R2 (10GB free)       R2 Pro or S3 Standard               S3 + CloudFront CDN
Reverb (single server)           Reverb + Redis pub/sub             Reverb horizontally scaled
Brevo (300/day free)             Brevo Starter (20K/mo)             AWS SES + Brevo transactional
DB Full-Text Search              + Meilisearch Cloud (free)         Meilisearch dedicated
Monolith                         Modular Monolith (refined)         Optional service extraction
~₹400/month                      ~₹2,000-4,000/month               ~₹8,000-15,000/month
```

---

## 3. Architecture & Design Philosophy

### Modular Laravel Monolith

```
app/
├── Actions/           # Single-responsibility business operations
│   ├── Matters/       # CreateMatter, CloseMatter, AssignAttorney
│   ├── Documents/     # UploadDocument, ShareDocument, VersionDocument
│   ├── Auth/          # LoginUser, VerifyEmail, Enable2FA
│   └── ...
├── Models/            # Eloquent models with relationships & scopes
├── Policies/          # Authorization policies (source of truth)
├── Http/
│   ├── Controllers/   # Thin controllers — delegate to Actions
│   ├── Requests/      # Form Request validation
│   ├── Middleware/     # FirmScope, MatterAccess, RateLimit
│   └── Livewire/      # Reactive components
├── Services/          # Google services, notification services
├── Jobs/              # Queued async work
├── Notifications/     # Centralized notification definitions
├── Events/            # Domain events
├── Listeners/         # Event handlers
└── Observers/         # Model lifecycle hooks
```

### Design Principles

| Principle | Implementation | Why It Matters |
|-----------|---------------|----------------|
| **Thin Controllers** | Controllers call Actions/Services; max ~15 lines per method | Prevents "God controllers"; keeps logic testable and reusable |
| **No Business Logic in Blade** | Blade templates only display data; conditions use `@can` directives tied to Policies | Security boundaries stay in PHP, not JavaScript |
| **Policies as Authorization Source of Truth** | Every `authorize()` call goes through a Policy | Single audit point for access control; no scattered `if` checks |
| **Private by Default** | Documents default to `PRIVATE` visibility; stored outside `public/` | Legal files must never be web-crawlable or URL-guessable |
| **Firm Scoping** | Global scope `FirmScope` auto-filters all queries by `firm_id` | Prevents cross-tenant data leaks at the ORM level |
| **Matter Authorization** | Middleware checks `matter_users` pivot before any matter resource | Even if a user guesses a UUID, they can't access unauthorized matters |
| **Isolated Google Services** | `GoogleCalendarService`, `GoogleDriveService` encapsulate all API calls | Swappable; if Google APIs change, only one file changes |
| **Migration-Ready Storage** | `Storage::disk('documents')` — config points to `local` now, `s3` later | Zero application code changes when migrating storage backends |

---

## 4. User Roles & Permission Matrix

### Role Hierarchy

```
Super Admin
    └── Firm Admin
            ├── Attorney
            ├── Staff / Paralegal
            └── Client (limited, portal-only)
```

### Permission Matrix

| Permission | Super Admin | Firm Admin | Attorney | Staff/Paralegal | Client |
|------------|:-----------:|:----------:|:--------:|:---------------:|:------:|
| `firm.manage` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `user.invite` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `user.manage_roles` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `client.create` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `client.view` | ✅ | ✅ | ✅ | 🔶 Assigned | 🔶 Own |
| `matter.create` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `matter.view` | ✅ | ✅ | 🔶 Assigned | 🔶 Assigned | 🔶 Own |
| `matter.edit` | ✅ | ✅ | 🔶 Assigned | ❌ | ❌ |
| `matter.close` | ✅ | ✅ | 🔶 Lead | ❌ | ❌ |
| `document.upload` | ✅ | ✅ | ✅ | 🔶 Assigned | 🔶 Requests |
| `document.view` | ✅ | ✅ | 🔶 Assigned | 🔶 Assigned | 🔶 Shared |
| `document.share` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `document.delete` | ✅ | ✅ | 🔶 Owner | ❌ | ❌ |
| `document.request` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `message.create` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `message.view` | ✅ | ✅ | 🔶 Assigned | 🔶 Assigned | 🔶 Own threads |
| `task.create` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `task.assign` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `task.complete` | ✅ | ✅ | ✅ | ✅ | 🔶 Assigned |
| `calendar.manage` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `calendar.view` | ✅ | ✅ | ✅ | ✅ | 🔶 Client-visible |
| `note.internal` | ✅ | ✅ | ✅ | ✅ | ❌ |
| `note.client_visible` | ✅ | ✅ | ✅ | ✅ | 🔶 Own matters |
| `billing.manage` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `billing.view_own` | ❌ | ❌ | ❌ | ❌ | ✅ |
| `analytics.view` | ✅ | ✅ | ✅ | ❌ | ❌ |
| `audit.view` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `settings.manage` | ✅ | ✅ | ❌ | ❌ | ❌ |
| `platform.manage` | ✅ | ❌ | ❌ | ❌ | ❌ |

> 🔶 = Conditional access — checked against resource-level authorization (firm scope + matter assignment)

---

## 5. Feature Modules — Detailed Specification

---

### F-01: Authentication & Security

#### 🎯 Why This Feature Exists
Legal data is among the most sensitive categories of information. A breach exposes privileged attorney-client communications, case strategies, and personal documents. Authentication is the **first gate** — it must be hardened beyond typical SaaS standards.

#### 📋 What It Does
Provides secure sign-in, sign-up (invitation-only), password reset, email verification, optional 2FA, session management, and account lockout.

#### 🌐 Current State (from Website)
- ✅ Login page with email/password form exists
- ✅ "Forgot password?" flow exists
- ✅ Demo account quick-login buttons
- ✅ Footer badges: "Two-step sign-in available", "Every access is logged"
- ❌ No public registration (by design — invitation-only)

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
-- users table (extends Laravel default)
ALTER TABLE users ADD COLUMN firm_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'client';
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULLABLE;
ALTER TABLE users ADD COLUMN avatar_path VARCHAR(500) NULLABLE;
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULLABLE;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULLABLE;
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULLABLE;
ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULLABLE;
ALTER TABLE users ADD COLUMN last_login_ip VARCHAR(45) NULLABLE;
ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULLABLE;
ALTER TABLE users ADD COLUMN force_password_change BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN notification_preferences JSON NULLABLE;
```

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Actions/Auth/LoginUser.php` | Validates credentials, checks firm status, checks lockout, records login audit |
| `app/Actions/Auth/RegisterInvitedUser.php` | Accepts invitation token, sets password, activates account |
| `app/Actions/Auth/Enable2FA.php` | Generates TOTP secret, stores encrypted, returns QR code |
| `app/Actions/Auth/Verify2FA.php` | Validates TOTP code, marks as confirmed |
| `app/Http/Controllers/Auth/LoginController.php` | Thin controller calling LoginUser action |
| `app/Http/Controllers/Auth/ForgotPasswordController.php` | Password reset request |
| `app/Http/Controllers/Auth/ResetPasswordController.php` | Password reset execution |
| `app/Http/Requests/LoginRequest.php` | Validates email, password, optional 2fa_code |
| `app/Http/Middleware/EnsureEmailIsVerified.php` | Redirects unverified users |
| `app/Http/Middleware/EnforceTwoFactor.php` | Redirects to 2FA input if enabled but not passed |
| `app/Http/Middleware/PreventBruteForce.php` | Rate limits + progressive lockout |
| `resources/views/auth/login.blade.php` | Login form with Quire branding |
| `resources/views/auth/forgot-password.blade.php` | Password reset request form |
| `resources/views/auth/two-factor-challenge.blade.php` | 2FA code input |
| `tests/Feature/Auth/LoginTest.php` | 15+ test cases for auth flows |
| `tests/Feature/Auth/TwoFactorTest.php` | 2FA enable/verify/recovery tests |
| `tests/Feature/Auth/PasswordResetTest.php` | Reset flow tests |

**Security Measures:**

| Measure | Implementation | Maintenance Burden |
|---------|---------------|-------------------|
| **Rate Limiting** | `ThrottleRequests::class` — 5 attempts/minute per IP+email | Low — built into Laravel |
| **Progressive Lockout** | 5 failures → 15min lock, 10 → 1hr, 15 → 24hr | Low — simple counter |
| **CSRF Protection** | `@csrf` token on all forms | Zero — Laravel automatic |
| **Secure Sessions** | `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SAME_SITE=lax` | Low — `.env` config |
| **Password Hashing** | bcrypt with cost factor 12 (Laravel default) | Zero — framework handles |
| **2FA (TOTP)** | `pragmarx/google2fa-laravel` package | Low — well-maintained package |
| **Session Invalidation** | Regenerate session ID on login; invalidate on password change | Low — one-line calls |

**🔀 Better Way (Industry Best Practice):**
- Use **Laravel Fortify + Sanctum** instead of hand-rolling auth — provides email verification, 2FA, password confirmation, and session management out of the box
- Consider **WebAuthn/Passkeys** for Phase 2 (passwordless login using biometrics) — increasingly adopted by enterprise legal SaaS
- Add **IP allowlisting** for firm admin accounts — whitelist known office IPs
- Consider **Magic Links** for client portal (simpler than passwords for non-technical users)

**📊 Scalability Assessment:**
- ⬜ **Low maintenance** — Laravel's auth scaffolding is battle-tested
- ⬜ **Scales to 10,000+ users** without changes
- ⬜ **Future: SSO/SAML** can be added via `socialite` package without refactoring
- ⬜ **Future: OAuth2 Provider** — Quire could become an identity provider for third-party integrations

---

### F-02: Users & Role Management

#### 🎯 Why This Feature Exists
A law firm has distinct roles (partners, associates, paralegals, clients) with vastly different data access needs. A paralegal should never see billing; a client should never see internal notes. RBAC enforces these boundaries at the application layer.

#### 📋 What It Does
CRUD for users, role assignment, permission matrix management, team invitation workflow, profile management, and activity tracking per user.

#### 🌐 Current State (from Website)
- ✅ Firm Settings → Team page shows team members with roles, email, status, last active
- ✅ Firm Settings → Roles & Permissions shows a full permission matrix grid
- ✅ Account Settings page with profile details, notification preferences, security settings

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    is_system BOOLEAN DEFAULT FALSE,
    description TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id),
    UNIQUE (firm_id, slug)
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    group_name VARCHAR(100) NOT NULL,
    description TEXT NULLABLE
);

CREATE TABLE role_permission (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE user_invitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    invited_by BIGINT UNSIGNED NOT NULL,
    accepted_at TIMESTAMP NULLABLE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP
);
```

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Models/Role.php` | Eloquent model with `permissions` relationship |
| `app/Models/Permission.php` | Eloquent model with seeder data |
| `app/Actions/Users/InviteUser.php` | Creates invitation, sends email, logs audit |
| `app/Actions/Users/UpdateUserRole.php` | Changes role, recalculates permissions, logs audit |
| `app/Actions/Users/DeactivateUser.php` | Soft-deactivates user, revokes sessions |
| `app/Policies/UserPolicy.php` | `viewAny`, `update`, `invite`, `deactivate` |
| `app/Http/Livewire/Firm/TeamManagement.php` | Real-time team member list with invite modal |
| `app/Http/Livewire/Firm/RolePermissionMatrix.php` | Interactive permission grid (checkbox matrix) |
| `database/seeders/PermissionSeeder.php` | Seeds all 30+ permissions grouped by module |
| `resources/views/livewire/firm/team-management.blade.php` | Team UI |
| `resources/views/livewire/firm/role-permission-matrix.blade.php` | Matrix UI |

**Permission Checking Flow:**
```
Request → Middleware(FirmScope) → Controller → Policy::authorize()
                                                    ↓
                                            $user->hasPermission('matter.view')
                                                    ↓
                                            Check role_permission pivot
                                                    ↓
                                            + Resource-level check (matter_users pivot)
```

**Caching Strategy:** Cache permissions per user for 15 minutes:
```php
Cache::remember("user.{$id}.permissions", 900, fn() => $user->loadPermissions())
```
Bust cache on role/permission change.

**🔀 Better Way:**
- Use `spatie/laravel-permission` for battle-tested RBAC instead of hand-rolling — saves 2-3 days
- However, custom implementation gives more control over firm-scoped roles (Spatie doesn't natively support multi-tenancy)
- **Recommendation:** Custom implementation with Spatie-inspired API surface

**📊 Scalability:**
- ⬜ **Low maintenance** — Permission data changes rarely
- ⬜ **Scales well** — Permission checks are cached; permission matrix is small (<100 rows)
- ⚠️ **Watch for:** N+1 queries when eager-loading permissions; use `with('roles.permissions')` globally

---

### F-03: Firm Management

#### 🎯 Why This Feature Exists
Quire is a **multi-tenant** SaaS. Each law firm operates in complete data isolation. Firm management provides the administrative foundation for tenant configuration, branding, and billing.

#### 📋 What It Does
Firm profile management, firm-level settings, subscription management (Super Admin), team size limits, and firm-scoped data isolation.

#### 🌐 Current State (from Website)
- ✅ Firm Settings page with profile details (name, address, phone, email, website, timezone)
- ✅ Super Admin → Firms page showing all registered firms with seat counts and status
- ✅ Subscription revenue tracking in admin dashboard

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE firms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NULLABLE,
    phone VARCHAR(20) NULLABLE,
    address_line_1 VARCHAR(255) NULLABLE,
    address_line_2 VARCHAR(255) NULLABLE,
    city VARCHAR(100) NULLABLE,
    state VARCHAR(100) NULLABLE,
    postal_code VARCHAR(20) NULLABLE,
    country VARCHAR(100) DEFAULT 'US',
    website VARCHAR(255) NULLABLE,
    logo_path VARCHAR(500) NULLABLE,
    timezone VARCHAR(50) DEFAULT 'America/New_York',
    date_format VARCHAR(20) DEFAULT 'M d, Y',
    currency VARCHAR(3) DEFAULT 'USD',
    max_seats INT DEFAULT 5,
    subscription_plan VARCHAR(50) DEFAULT 'trial',
    subscription_status VARCHAR(50) DEFAULT 'active',
    trial_ends_at TIMESTAMP NULLABLE,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE
);
```

**Global Scope for Multi-Tenancy:**
```php
// app/Scopes/FirmScope.php
class FirmScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->firm_id) {
            $builder->where($model->getTable() . '.firm_id', auth()->user()->firm_id);
        }
    }
}
```

**Why Global Scope (not middleware)?** Middleware can be bypassed by direct Eloquent queries in Jobs, Commands, or Services. A Global Scope on the Model ensures **every single query** is firm-filtered, even in background processes.

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Models/Firm.php` | Eloquent model with users, clients, matters relationships |
| `app/Scopes/FirmScope.php` | Global scope for multi-tenancy isolation |
| `app/Traits/BelongsToFirm.php` | Trait that auto-applies FirmScope + sets firm_id on create |
| `app/Actions/Firms/UpdateFirmProfile.php` | Updates firm details, validates, logs audit |
| `app/Actions/Firms/ManageSubscription.php` | Super Admin: change plan, seat count |
| `app/Policies/FirmPolicy.php` | Only firm admins can manage; super admin can manage all |
| `app/Http/Livewire/Firm/FirmProfile.php` | Firm profile edit form |
| `app/Http/Livewire/Admin/FirmManagement.php` | Super admin firm list + detail |

**🔀 Better Way:**
- Consider using `stancl/tenancy` Laravel package for more sophisticated multi-tenancy (database-per-tenant, domain identification, automatic tenant switching)
- **Recommendation for MVP:** Custom FirmScope is simpler and sufficient for shared-database tenancy

**📊 Scalability:**
- ⬜ **Low maintenance** — Firm data changes rarely
- ⬜ **Shared-database multi-tenancy** works up to ~500 firms; beyond that, consider database-per-tenant
- ⬜ **Future: Custom domains** per firm can be added via `firm_domains` table

---

### F-04: Client Management

#### 🎯 Why This Feature Exists
Clients are the core business entity of a law firm. Every matter, document, and communication revolves around a client. Proper client management ensures intake tracking, portal access, and relationship oversight.

#### 📋 What It Does
Client CRUD (individual + company), portal invitation, client-matter association, contact details, notes, activity history, and portal access management.

#### 🌐 Current State (from Website)
- ✅ Client list page with active/inactive filter
- ✅ Client detail view showing name, contact info, associated matters, portal status
- ✅ New client form (individual/company type selector visible)
- ✅ Client types include individuals (Elena Marsh) and companies (Sam Whitaker)

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULLABLE,
    type ENUM('individual', 'company') NOT NULL DEFAULT 'individual',
    
    -- Individual fields
    first_name VARCHAR(100) NULLABLE,
    last_name VARCHAR(100) NULLABLE,
    
    -- Company fields
    company_name VARCHAR(255) NULLABLE,
    contact_person VARCHAR(255) NULLABLE,
    
    -- Shared fields
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULLABLE,
    address TEXT NULLABLE,
    city VARCHAR(100) NULLABLE,
    state VARCHAR(100) NULLABLE,
    postal_code VARCHAR(20) NULLABLE,
    country VARCHAR(100) DEFAULT 'US',
    date_of_birth DATE NULLABLE,
    tax_id VARCHAR(50) NULLABLE,
    
    -- Portal
    portal_invited_at TIMESTAMP NULLABLE,
    portal_activated_at TIMESTAMP NULLABLE,
    
    -- Meta
    source VARCHAR(100) NULLABLE,
    notes TEXT NULLABLE,
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (firm_id) REFERENCES firms(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_firm_status (firm_id, status),
    FULLTEXT INDEX ft_client_search (first_name, last_name, company_name, email)
);
```

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Models/Client.php` | Eloquent model with `FirmScope`, `matters()`, `user()`, `documents()` |
| `app/Actions/Clients/CreateClient.php` | Validates uniqueness within firm, creates record, logs audit |
| `app/Actions/Clients/InviteClientToPortal.php` | Creates user account, sends invitation email |
| `app/Actions/Clients/UpdateClient.php` | Updates client details, logs changes |
| `app/Actions/Clients/ArchiveClient.php` | Soft-archives, preserves all associated data |
| `app/Policies/ClientPolicy.php` | `viewAny`, `view`, `create`, `update`, `archive`, `inviteToPortal` |
| `app/Http/Livewire/Clients/ClientList.php` | Filterable, searchable client list with pagination |
| `app/Http/Livewire/Clients/ClientDetail.php` | Client profile with tabbed interface |
| `app/Http/Livewire/Clients/CreateClientForm.php` | Dynamic form switching between individual/company |

**🔀 Better Way:**
- Consider a `contacts` polymorphic approach if the system may later support opposing counsel, judges, witnesses
- **Recommendation for MVP:** Keep `clients` table separate — simpler and semantically different

**📊 Scalability:**
- ⬜ **Low maintenance** — CRUD operations are straightforward
- ⬜ **Full-text index** handles search up to ~100K clients efficiently
- ⬜ **Future: CRM features** (intake forms, lead tracking) can extend this model

---

### F-05: Matter/Case Management

#### 🎯 Why This Feature Exists
A "matter" (legal term for a case/engagement) is the **central organizing entity** of the entire system. Documents, messages, tasks, calendar events, notes, and billing all hang off a matter. This is the most critical module.

#### 📋 What It Does
Matter CRUD, status lifecycle (Open → Active → Discovery → Trial → Settled → Closed → Archived), attorney/staff assignment, client association, practice area categorization, matter-level dashboard with 9 tabs, and linked matter references.

#### 🌐 Current State (from Website)
- ✅ Matter list with practice area filters (Civil Litigation, Real Estate)
- ✅ Status-based filtering
- ✅ New matter form with fields for title, client, practice area, status, description
- ✅ Matter detail page with 9 tabs: Overview, Work Product, Internal Notes, Client Files, Requests, Messages, Tasks, Timeline, Billing
- ✅ Matter dashboard showing case summary, assigned team, client info, key dates

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE matters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    reference_number VARCHAR(50) NULLABLE,
    practice_area VARCHAR(100) NOT NULL,
    case_type VARCHAR(100) NULLABLE,
    description TEXT NULLABLE,
    
    status ENUM('draft','open','active','discovery','negotiation','trial','settled','closed','archived') 
           DEFAULT 'open',
    
    date_opened DATE NOT NULL,
    date_closed DATE NULLABLE,
    statute_of_limitations DATE NULLABLE,
    next_court_date DATE NULLABLE,
    
    court_name VARCHAR(255) NULLABLE,
    court_case_number VARCHAR(100) NULLABLE,
    judge_name VARCHAR(255) NULLABLE,
    opposing_counsel VARCHAR(255) NULLABLE,
    
    billing_method ENUM('hourly', 'flat_fee', 'contingency', 'retainer') DEFAULT 'hourly',
    estimated_value DECIMAL(15,2) NULLABLE,
    
    is_confidential BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    lead_attorney_id BIGINT UNSIGNED NULLABLE,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (firm_id) REFERENCES firms(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_firm_status (firm_id, status),
    INDEX idx_client (client_id),
    FULLTEXT INDEX ft_matter_search (title, description, court_case_number)
);

CREATE TABLE matter_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matter_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(50) DEFAULT 'team_member',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (matter_id) REFERENCES matters(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE (matter_id, user_id)
);
```

**Auto-Generated Reference Numbers:**
```php
// app/Actions/Matters/GenerateMatterReference.php
public function execute(Firm $firm): string
{
    $prefix = strtoupper(substr($firm->name, 0, 2));
    $year = now()->year;
    $sequence = Matter::where('firm_id', $firm->id)
        ->whereYear('created_at', $year)
        ->count() + 1;
    
    return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
}
```

**Status Transition State Machine:**
```
  ┌───────┐    ┌──────┐    ┌────────┐    ┌───────────┐    ┌───────────┐
  │ Draft │───▶│ Open │───▶│ Active │───▶│ Discovery │───▶│Negotiation│
  └───────┘    └──────┘    └────────┘    └───────────┘    └─────┬─────┘
                                                                │
       ┌──────────┐    ┌─────────┐    ┌───────┐    ┌──────┐    │
       │ Archived │◀───│ Closed  │◀───│Settled│◀───│Trial │◀───┘
       └──────────┘    └─────────┘    └───────┘    └──────┘
```

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Models/Matter.php` | Central model with 15+ relationships |
| `app/Actions/Matters/CreateMatter.php` | Generates reference, assigns lead attorney, audit |
| `app/Actions/Matters/TransitionMatterStatus.php` | State machine with validation |
| `app/Actions/Matters/AssignTeamMember.php` | Adds to matter_users, sends notification |
| `app/Policies/MatterPolicy.php` | Checks firm scope + matter_users membership |
| `app/Http/Middleware/EnsureMatterAccess.php` | Route middleware |
| `app/Http/Livewire/Matters/MatterList.php` | Filterable matter list |
| `app/Http/Livewire/Matters/MatterDashboard.php` | 9-tab matter detail |
| `app/Http/Livewire/Matters/MatterTimeline.php` | Chronological activity feed |
| `app/Http/Livewire/Matters/CreateMatterForm.php` | New matter form |

**🔀 Better Way:**
- Use a **State Machine pattern** (`spatie/laravel-model-states`) for status transitions — enforces valid transitions (can't go from "Draft" to "Closed" directly) and triggers events on each transition
- **Recommendation:** Implement custom state machine — Spatie package adds minimal value for a simple linear workflow

**📊 Scalability:**
- ⬜ **Medium maintenance** — Status transitions and business rules evolve as firm workflows mature
- ⬜ **Scales well** — Indexed queries on firm_id + status handle millions of matters
- ⚠️ **Watch for:** Matter detail page can become slow with many related records; use lazy-loaded tabs (Livewire `wire:init`)

---

### F-06: Document Management

#### 🎯 Why This Feature Exists
Documents are the **lifeblood of legal work**. Contracts, pleadings, court orders, evidence — everything is a document. A secure, versioned, categorized document system with proper access controls is non-negotiable for any legal platform.

#### 📋 What It Does
Upload (drag-and-drop + camera capture), preview, download, categorize, tag, describe, version, share (with permission levels), activity history, and secure access through authenticated routes.

#### 🌐 Current State (from Website)
- ✅ Central document repository with 8 category filters: Engagement, Pleadings, Court Orders, Correspondence, Work Product, Discovery, Evidence, Contracts
- ✅ Document entries show: name, category, matter, uploaded by, date, privilege indicator
- ✅ Matter detail has separate "Work Product", "Client Files" tabs
- ✅ Visibility indicators (privilege badges)
- ✅ Version history visible in document detail

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE document_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280',
    icon VARCHAR(50) DEFAULT 'folder',
    sort_order INT DEFAULT 0,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    UNIQUE (firm_id, slug)
);

CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULLABLE,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    
    visibility ENUM('private', 'internal', 'client_shared') DEFAULT 'private',
    is_privileged BOOLEAN DEFAULT FALSE,
    
    current_version_id BIGINT UNSIGNED NULLABLE,
    version_count INT DEFAULT 1,
    
    tags JSON NULLABLE,
    
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (firm_id) REFERENCES firms(id),
    FOREIGN KEY (matter_id) REFERENCES matters(id),
    FOREIGN KEY (category_id) REFERENCES document_categories(id),
    INDEX idx_firm_matter (firm_id, matter_id),
    INDEX idx_visibility (visibility),
    FULLTEXT INDEX ft_document_search (title, description)
);

CREATE TABLE document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number INT NOT NULL,
    
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    
    change_notes TEXT NULLABLE,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE (document_id, version_number)
);

CREATE TABLE document_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    shared_with_user_id BIGINT UNSIGNED NOT NULL,
    shared_by_user_id BIGINT UNSIGNED NOT NULL,
    permission ENUM('view', 'download', 'edit') DEFAULT 'view',
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (document_id) REFERENCES documents(id),
    UNIQUE (document_id, shared_with_user_id)
);
```

**Secure File Storage Architecture:**
```
storage/
└── app/
    └── private/                          ← OUTSIDE public/ directory
        └── documents/
            └── {firm_uuid}/
                └── {matter_uuid}/
                    └── {sha256_hash}.{ext}   ← Content-addressable storage
```

**Why Content-Addressable Storage?**
- **Deduplication:** Same file uploaded twice → stored once
- **Integrity verification:** Hash comparison detects corruption
- **Non-guessable paths:** Attackers can't guess the hash

**Secure Download Route:**
```php
// routes/web.php
Route::get('/documents/{document:uuid}/download/{version?}', 
    [DocumentDownloadController::class, 'download'])
    ->middleware(['auth', 'verified', 'can:download,document']);

// app/Http/Controllers/DocumentDownloadController.php
public function download(Document $document, ?int $version = null): StreamedResponse
{
    $this->authorize('download', $document);
    
    $docVersion = $version 
        ? $document->versions()->where('version_number', $version)->firstOrFail()
        : $document->currentVersion;
    
    AuditLog::record('document.downloaded', $document);
    
    return Storage::disk('private')->download(
        $docVersion->file_path,
        $docVersion->original_filename,
        ['Content-Type' => $docVersion->mime_type]
    );
}
```

**Mobile Camera Capture (Alpine.js):**
```javascript
Alpine.data('cameraCapture', () => ({
    stream: null,
    photoBlob: null,
    previewUrl: null,
    
    async startCamera() {
        this.stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1920 } }
        });
        this.$refs.video.srcObject = this.stream;
    },
    
    capturePhoto() {
        const canvas = document.createElement('canvas');
        canvas.width = this.$refs.video.videoWidth;
        canvas.height = this.$refs.video.videoHeight;
        canvas.getContext('2d').drawImage(this.$refs.video, 0, 0);
        canvas.toBlob(blob => {
            this.photoBlob = blob;
            this.previewUrl = URL.createObjectURL(blob);
            this.stopCamera();
        }, 'image/jpeg', 0.85);
    },
    
    retake() {
        this.photoBlob = null;
        this.previewUrl = null;
        this.startCamera();
    }
}));
```

**Files to Create:**

| File | Purpose |
|------|---------|
| `app/Models/Document.php` | Model with versions, shares, category relationships |
| `app/Models/DocumentVersion.php` | Version model with file path accessor |
| `app/Actions/Documents/UploadDocument.php` | Handles upload, hash, store, create version |
| `app/Actions/Documents/CreateNewVersion.php` | New version without overwriting |
| `app/Actions/Documents/ShareDocument.php` | Creates share record, sends notification |
| `app/Actions/Documents/DeleteDocument.php` | Soft-delete + audit log |
| `app/Policies/DocumentPolicy.php` | Full authorization with visibility checks |
| `app/Http/Controllers/DocumentDownloadController.php` | Secure download streaming |
| `app/Http/Livewire/Documents/DocumentList.php` | Filterable document repository |
| `app/Http/Livewire/Documents/DocumentUpload.php` | Drag-drop + camera upload |
| `app/Http/Livewire/Documents/DocumentDetail.php` | Version history, shares, activity |

**🔀 Better Way:**
- Use **Livewire file uploads** with temporary URLs for preview — handles chunked uploads, progress bars, and validation automatically
- Consider **virus scanning** via ClamAV on upload (Phase 2)
- Add **watermarking** for shared documents (Phase 2)
- **Thumbnail generation** using Intervention Image for image/PDF preview

**📊 Scalability:**
- ⬜ **Medium maintenance** — File storage needs monitoring; disk space on Hostinger is limited
- ⚠️ **Critical migration path:** Move to S3/R2 when storage exceeds 10GB — change one config value
- ⚠️ **Watch for:** Large file uploads on shared hosting (`upload_max_filesize`, `post_max_size`, `max_execution_time`)
- ⬜ **Future: CDN** for thumbnails/previews via CloudFront

---

### F-07: Document Request Workflow

#### 🎯 Why This Feature Exists
Attorneys frequently need clients to provide specific documents (ID, financial statements, medical records). Without a formal request system, this happens via email — creating tracking nightmares, missed documents, and no audit trail.

#### 📋 What It Does
Attorney creates request → client receives email + in-app notification → client uploads → status transitions: **Pending → Submitted → Under Review → Accepted/Rejected → Completed**

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE document_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    category_id BIGINT UNSIGNED NULLABLE,
    
    status ENUM('pending','submitted','under_review','accepted','rejected','completed') 
           DEFAULT 'pending',
    
    due_date DATE NULLABLE,
    submitted_at TIMESTAMP NULLABLE,
    reviewed_at TIMESTAMP NULLABLE,
    completed_at TIMESTAMP NULLABLE,
    
    reviewer_id BIGINT UNSIGNED NULLABLE,
    review_notes TEXT NULLABLE,
    rejection_reason TEXT NULLABLE,
    
    document_id BIGINT UNSIGNED NULLABLE,
    
    requested_by BIGINT UNSIGNED NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (matter_id) REFERENCES matters(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (document_id) REFERENCES documents(id),
    INDEX idx_matter_status (matter_id, status),
    INDEX idx_client_status (client_id, status)
);
```

**State Machine Diagram:**
```
                    ┌──────────────────────────────────┐
                    │          (re-submit)             │
  ┌─────────┐   ┌──▼──────┐   ┌────────────┐   ┌─────┴────┐   ┌───────────┐
  │ Pending  │──▶│Submitted│──▶│Under Review│──▶│ Accepted  │──▶│ Completed │
  └─────────┘   └─────────┘   └─────┬──────┘   └──────────┘   └───────────┘
                                    │
                                    ▼
                              ┌──────────┐
                              │ Rejected │
                              └──────────┘
```

**🔀 Better Way:**
- Add **batch requests** — attorney requests 5 documents at once
- Add **request templates** — common document sets per practice area ("Personal Injury Intake" → ID, Medical Records, Insurance, Photos)
- Add **smart reminders** — auto-email clients 3 days before deadline, 1 day after overdue

**📊 Scalability:**
- ⬜ **Low maintenance** — Simple state machine with clear transitions

---

### F-08: Client Portal

#### 🎯 Why This Feature Exists
Clients need a simple, non-intimidating way to interact with their legal team. A good client portal reduces phone calls and emails by 60-80%.

#### 📋 What It Does
Separate, simplified interface at `/portal/*`. Shows: dashboard with action items, matter overview with stage tracker, document requests, secure messaging, tasks, calendar events (client-visible only), invoice viewing and payment.

#### 🌐 Current State (from Website)
- ✅ Client portal at `/portal/*` with distinct, simpler navigation
- ✅ Case stage tracker (Stage 3 of 7: Discovery) — visual progress bar
- ✅ Document request fulfillment interface
- ✅ Secure messaging with legal team
- ✅ Invoice viewing with payment options (credit card, bank wire transfer)

#### 🏗️ Detailed Implementation Plan

**Route Structure:**
```php
Route::prefix('portal')->middleware(['auth', 'verified', 'role:client'])->group(function () {
    Route::get('/', [PortalDashboardController::class, 'index']);
    Route::get('/matters/{matter:uuid}', [PortalMatterController::class, 'show']);
    Route::get('/documents', [PortalDocumentController::class, 'index']);
    Route::get('/requests', [PortalRequestController::class, 'index']);
    Route::post('/requests/{request:uuid}/upload', [PortalRequestController::class, 'upload']);
    Route::get('/messages', [PortalMessageController::class, 'index']);
    Route::get('/invoices', [PortalInvoiceController::class, 'index']);
    Route::get('/invoices/{invoice:uuid}', [PortalInvoiceController::class, 'show']);
});
```

**Critical Isolation Rules:**
1. Client ONLY sees matters where `clients.user_id = auth()->id()`
2. Client ONLY sees documents with `visibility = 'client_shared'`
3. Client NEVER sees notes with `type = 'internal'`
4. Client ONLY sees calendar events with `client_visible = true`
5. Client ONLY sees their own invoices
6. Client CANNOT see other clients' data — even within the same firm

**Mobile-First Design:**
- Single-column layout on mobile
- Large touch targets (48px minimum)
- Camera capture button prominently placed
- PWA manifest for home screen installation

**🔀 Better Way:**
- Add **real-time progress updates** via Livewire polling (every 10s)
- Add **client onboarding wizard** — first-login tour
- Consider **SMS notifications** in Phase 2 for urgent items

**📊 Scalability:**
- ⬜ **Low maintenance** — Read-heavy, simple UI
- ⬜ **Scales to thousands of clients** — narrow queries (own matters only)
- ⬜ **Future: White-label** portal with firm branding per `firms.settings` JSON

---

### F-09: Matter-Based Chat/Messaging

#### 🎯 Why This Feature Exists
Legal communication needs to be **matter-specific, auditable, and access-controlled**. Email threads get lost, WhatsApp is insecure, and generic chat products don't understand privileged communication.

#### 📋 What It Does
Matter-scoped message threads, text with attachments, timestamps, sender identification, read/unread tracking, and distinction between **internal** (staff-only) and **client** (client-visible) threads.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE message_threads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NOT NULL,
    
    subject VARCHAR(255) NOT NULL,
    type ENUM('internal', 'client') NOT NULL DEFAULT 'client',
    
    last_message_at TIMESTAMP NULLABLE,
    last_message_preview VARCHAR(255) NULLABLE,
    message_count INT DEFAULT 0,
    
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_matter_type (matter_id, type),
    INDEX idx_firm_recent (firm_id, last_message_at DESC)
);

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    thread_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
    INDEX idx_thread_created (thread_id, created_at)
);

CREATE TABLE message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
);

CREATE TABLE message_reads (
    thread_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    last_read_at TIMESTAMP NOT NULL,
    PRIMARY KEY (thread_id, user_id)
);
```

**Livewire Polling for Real-Time Feel:**
```php
class ThreadView extends Component
{
    public MessageThread $thread;
    
    #[Polling('5s')]
    public function render()
    {
        $messages = $this->thread->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at')
            ->get();
        
        $this->thread->markAsReadBy(auth()->user());
        
        return view('livewire.messages.thread-view', compact('messages'));
    }
}
```

**🔀 Better Way:**
- Use **Laravel Echo + Soketi** for true real-time (Phase 2)
- Add **message reactions** (👍, ✅)
- Add **@mentions** to tag team members
- Consider **message threading** (replies to specific messages)

**📊 Scalability:**
- ⬜ **Medium maintenance** — Polling increases server load; tune intervals
- ⚠️ **Watch for:** Threads with 1000+ messages — paginate with infinite scroll
- ⬜ **Future: WebSockets** eliminate polling overhead entirely

---

### F-10: Tasks

#### 🎯 Why This Feature Exists
Legal work involves dozens of action items per matter. Tasks ensure nothing falls through the cracks, with clear ownership, deadlines, and priority levels.

#### 📋 What It Does
Task CRUD, assignment to attorneys/staff/clients, priority levels, due dates, status tracking, matter association, comments, completion logging, and overdue alerts.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NULLABLE,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    
    assigned_to BIGINT UNSIGNED NULLABLE,
    assigned_by BIGINT UNSIGNED NOT NULL,
    
    due_date DATE NULLABLE,
    completed_at TIMESTAMP NULLABLE,
    completed_by BIGINT UNSIGNED NULLABLE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    INDEX idx_assigned_status (assigned_to, status),
    INDEX idx_matter_status (matter_id, status),
    INDEX idx_due_date (due_date)
);

CREATE TABLE task_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
```

**Overdue Detection (Scheduled Command):**
```php
// Runs daily via Laravel Scheduler
$overdueTasks = Task::where('status', '!=', 'completed')
    ->where('due_date', '<', now()->toDateString())
    ->whereNull('completed_at')
    ->with('assignee', 'matter')
    ->get();

foreach ($overdueTasks as $task) {
    $task->assignee->notify(new TaskOverdueNotification($task));
}
```

**📊 Scalability:**
- ⬜ **Low maintenance** — Simple CRUD with status tracking
- ⬜ **Future: Kanban board** view with drag-and-drop
- ⬜ **Future: Time tracking** — start/stop timer for billing integration

---

### F-11: Todo List

#### 🎯 Why This Feature Exists
Unlike tasks (matter-related, assignable), todos are **personal productivity items**. They may optionally reference a matter but are inherently personal.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE todos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NULLABLE,
    title VARCHAR(255) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULLABLE,
    due_date DATE NULLABLE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_completed (user_id, is_completed)
);
```

**📊 Scalability:**
- ⬜ **Very low maintenance** — Personal data, simple operations, negligible scaling concerns

---

### F-12: Notes

#### 🎯 Why This Feature Exists
Attorneys take notes during meetings, depositions, and research. Some are **internal** (strategy, case weaknesses) and must NEVER be exposed to clients. Others are **client-visible** (meeting summaries, agreed action items).

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    type ENUM('internal', 'client_visible') NOT NULL DEFAULT 'internal',
    is_pinned BOOLEAN DEFAULT FALSE,
    
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (matter_id) REFERENCES matters(id),
    INDEX idx_matter_type (matter_id, type),
    FULLTEXT INDEX ft_note_search (title, body)
);
```

**Critical Security Rule:**
```php
// app/Policies/NotePolicy.php
public function view(User $user, Note $note): bool
{
    if ($user->isClient()) {
        return $note->type === 'client_visible' 
            && $note->matter->client->user_id === $user->id;
    }
    return $note->matter->hasTeamMember($user);
}
```

**🔀 Better Way:**
- Use **Trix** (built into Laravel) or **TipTap** for rich-text editing
- Add **note templates** for common note types
- Add **@mentions** in notes

---

### F-13: Internal Calendar

#### 🎯 Why This Feature Exists
Legal deadlines are non-negotiable — missing a statute of limitations or a filing deadline can result in malpractice. A centralized calendar provides visibility across all firm events and deadlines.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE calendar_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NULLABLE,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    location VARCHAR(255) NULLABLE,
    
    event_type ENUM('hearing','meeting','deadline','filing','deposition','mediation','other') 
               DEFAULT 'other',
    
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    is_all_day BOOLEAN DEFAULT FALSE,
    client_visible BOOLEAN DEFAULT FALSE,
    
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_rule VARCHAR(255) NULLABLE,
    reminder_minutes INT NULLABLE,
    color VARCHAR(7) DEFAULT '#3B82F6',
    
    created_by BIGINT UNSIGNED NOT NULL,
    
    google_event_id VARCHAR(255) NULLABLE,
    google_calendar_id VARCHAR(255) NULLABLE,
    last_synced_at TIMESTAMP NULLABLE,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    INDEX idx_firm_date (firm_id, starts_at),
    INDEX idx_matter (matter_id),
    INDEX idx_google_event (google_event_id)
);

CREATE TABLE calendar_event_attendees (
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'tentative') DEFAULT 'pending',
    PRIMARY KEY (event_id, user_id)
);
```

**🔀 Better Way:**
- Use **FullCalendar.js** (MIT license) — supports drag-and-drop, resize, multiple views, saves 3-5 days of frontend work
- **Recommendation:** FullCalendar with Alpine.js adapter

**📊 Scalability:**
- ⬜ **Low maintenance** — Calendar queries are date-range filtered (inherently limited result sets)
- ⬜ **Future: Recurring events** with iCal RRULE parsing (`simshaun/recurr` package)

---

### F-14: Google Calendar Integration

#### 🎯 Why This Feature Exists
Attorneys already use Google Calendar. Syncing Quire events eliminates double-entry and ensures court dates appear alongside personal appointments.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE google_calendar_connections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    google_account_email VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    token_expires_at TIMESTAMP NOT NULL,
    default_calendar_id VARCHAR(255) DEFAULT 'primary',
    sync_enabled BOOLEAN DEFAULT TRUE,
    last_synced_at TIMESTAMP NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Token Encryption:**
```php
protected $casts = [
    'access_token' => 'encrypted',
    'refresh_token' => 'encrypted',
    'token_expires_at' => 'datetime',
];
```

**OAuth Flow:** User clicks "Connect" → Google consent screen → redirect back with auth code → exchange for tokens → encrypt and store → enable sync.

**MVP Scope:**
- ✅ Push: Create/update/delete Quire events → Google Calendar
- ✅ Import: Pull Google Calendar events → Quire (manual)
- ❌ Full two-way sync (Phase 2) — conflict resolution is extremely complex

**📊 Scalability:**
- ⚠️ **Medium maintenance** — Token refresh, API deprecations, scope changes, quota limits
- ⬜ **Future: Batch sync** using Google Calendar Push Notifications (webhooks)

---

### F-15: Google Drive Integration

#### 🎯 Why This Feature Exists
Many firms store documents in Google Drive. Integration allows linking Drive folders to matters, importing files, and exporting documents.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE google_drive_connections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    google_account_email VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    token_expires_at TIMESTAMP NOT NULL,
    root_folder_id VARCHAR(255) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE google_drive_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matter_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NULLABLE,
    drive_file_id VARCHAR(255) NOT NULL,
    drive_folder_id VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    sync_direction ENUM('import', 'export', 'linked') NOT NULL,
    last_synced_at TIMESTAMP NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_matter (matter_id),
    INDEX idx_drive_file (drive_file_id)
);
```

**MVP Scope:**
- ✅ Connect Drive via OAuth, browse folders, link folder to matter, import files, export files
- ❌ Two-way sync (Phase 2), real-time folder watching (Phase 2)

---

### F-16: Global Search

#### 🎯 Why This Feature Exists
Attorneys need to find information fast — across clients, matters, documents, messages, tasks, and notes. A single search bar saves significant time.

#### 🏗️ Detailed Implementation Plan

```php
// app/Services/GlobalSearchService.php
class GlobalSearchService
{
    public function search(string $query, User $user, int $limit = 25): array
    {
        $firmId = $user->firm_id;
        $matterIds = $user->accessibleMatterIds();
        
        return [
            'clients' => $this->searchClients($query, $firmId, $limit),
            'matters' => $this->searchMatters($query, $firmId, $matterIds, $limit),
            'documents' => $this->searchDocuments($query, $firmId, $matterIds, $limit),
            'messages' => $this->searchMessages($query, $firmId, $matterIds, $limit),
            'tasks' => $this->searchTasks($query, $firmId, $matterIds, $limit),
            'notes' => $this->searchNotes($query, $firmId, $matterIds, $user, $limit),
        ];
    }
}
```

**🔀 Better Way:**
- Use `laravel/scout` with the database driver for MVP — makes future migration to Meilisearch a one-line config change
- MySQL Full-Text Search works up to ~500K records per table

**📊 Scalability:**
- ⬜ **Low maintenance for MVP** — MySQL FTS is zero-infrastructure
- ⚠️ **Migration trigger:** When search latency exceeds 200ms, switch to Meilisearch

---

### F-17: Analytics & Dashboard

#### 🎯 Why This Feature Exists
Firm administrators and attorneys need operational visibility without manual reporting.

#### 🌐 Current State (from Website)
- ✅ Dashboard: open matters, tasks due, uploads to review, outstanding balance
- ✅ Activity feed with user badges and timestamps
- ✅ "Next two weeks" calendar summary
- ✅ "Waiting on you" section (client replies + uploads)

#### 🏗️ Detailed Implementation Plan

```php
// app/Services/DashboardService.php
class DashboardService
{
    public function getMetrics(User $user): array
    {
        $firmId = $user->firm_id;
        return [
            'open_matters' => Matter::where('firm_id', $firmId)
                ->whereNotIn('status', ['closed', 'archived'])->count(),
            'tasks_due_this_week' => Task::where('firm_id', $firmId)
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'pending_document_requests' => DocumentRequest::where('firm_id', $firmId)
                ->whereIn('status', ['pending', 'submitted'])->count(),
            'overdue_tasks' => Task::where('firm_id', $firmId)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now()->toDateString())->count(),
            'matter_status_distribution' => Matter::where('firm_id', $firmId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')->pluck('count', 'status'),
        ];
    }
}
```

**Charts:** Chart.js 4 for matter status donut, task completion bar, document activity line, client growth area.

**🔀 Better Way:**
- Cache dashboard metrics for 5-10 minutes (`Cache::remember()`)
- Use **Laravel Pulse** for real-time application performance insights
- Use **Livewire wire:poll.30s** for auto-refreshing

---

### F-18: Notifications (Email + In-App)

#### 🎯 Why This Feature Exists
Users need to be informed about events relevant to them without constantly checking the application.

#### 🏗️ Detailed Implementation Plan

**Notification Events Matrix:**

| Event | Email | In-App | Recipient |
|-------|:-----:|:------:|-----------|
| Account Invitation | ✅ | ❌ | Invited user |
| New Document Uploaded | ✅ | ✅ | Matter team |
| Document Request Created | ✅ | ✅ | Client |
| Document Request Submitted | ❌ | ✅ | Requesting attorney |
| Document Accepted/Rejected | ✅ | ✅ | Client |
| New Message | ✅ | ✅ | Thread participants |
| Task Assigned | ✅ | ✅ | Assignee |
| Task Overdue Reminder | ✅ | ✅ | Assignee |
| Task Completed | ❌ | ✅ | Task creator |
| Matter Status Changed | ✅ | ✅ | Matter team + client |
| Calendar Event Created | ✅ | ✅ | Attendees |
| Calendar Event Reminder | ✅ | ❌ | Attendees |

**Implementation Pattern:**
```php
class DocumentRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;
    
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->wantsEmail('document_request')) {
            $channels[] = 'mail';
        }
        return $channels;
    }
    
    public function toMail(object $notifiable): MailMessage { /* ... */ }
    public function toArray(object $notifiable): array { /* ... */ }
}
```

**🔀 Better Way:**
- Use **queued notifications** (ShouldQueue) for ALL email notifications
- Implement **notification batching** — 5 messages in 1 minute → 1 digest email
- Add **notification center** UI with mark-as-read, filters

**📊 Scalability:**
- ⬜ **Low maintenance** — Laravel Notifications is mature
- ⚠️ **Watch for:** Notification spam — add throttling (max 1 email per event type per 15min)

---

### F-19: Audit Logging

#### 🎯 Why This Feature Exists
Legal ethics require tracking who accessed what and when. Audit trails are essential for disputes, breaches, and malpractice claims.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NULLABLE,
    user_id BIGINT UNSIGNED NULLABLE,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULLABLE,
    entity_id BIGINT UNSIGNED NULLABLE,
    matter_id BIGINT UNSIGNED NULLABLE,
    description TEXT NULLABLE,
    metadata JSON NULLABLE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) NULLABLE,
    created_at TIMESTAMP NOT NULL,
    
    INDEX idx_firm_action (firm_id, action),
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);
```

**Usage:**
```php
AuditLog::record('document.uploaded', $document, [
    'original_filename' => $file->getClientOriginalName(),
    'file_size' => $file->getSize(),
]);
```

**Critical Rule:** Audit records are **append-only**. No `update` or `delete` routes. Retention: keep forever.

**📊 Scalability:**
- ⚠️ **Medium maintenance** — Fastest-growing table; implement partitioning by month after 1M rows
- ⬜ **Future: Export to S3** for long-term archival; keep only last 90 days in active database

---

### F-20: Billing & Invoicing

#### 🎯 Why This Feature Exists
Billing is the revenue engine of a law firm. Time tracking, invoice generation, and trust account management are essential.

> **Note:** Requirements document excludes Stripe/Razorpay from MVP. **Recommendation:** Build billing data model + invoice UI in MVP, defer payment processing to Phase 2.

#### 🏗️ Detailed Implementation Plan

**Database Schema:**
```sql
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    firm_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NULLABLE,
    invoice_number VARCHAR(50) NOT NULL,
    status ENUM('draft','sent','viewed','partially_paid','paid','overdue','void') DEFAULT 'draft',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(15,2) DEFAULT 0,
    balance_due DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'USD',
    issued_date DATE NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULLABLE,
    notes TEXT NULLABLE,
    payment_instructions TEXT NULLABLE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_firm_status (firm_id, status),
    INDEX idx_client (client_id)
);

CREATE TABLE invoice_line_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    rate DECIMAL(15,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    type ENUM('time_entry', 'expense', 'flat_fee', 'other') DEFAULT 'other',
    time_entry_id BIGINT UNSIGNED NULLABLE,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE time_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NOT NULL,
    matter_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(500) NOT NULL,
    duration_minutes INT NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    date DATE NOT NULL,
    is_billable BOOLEAN DEFAULT TRUE,
    is_invoiced BOOLEAN DEFAULT FALSE,
    invoice_id BIGINT UNSIGNED NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_matter_invoiced (matter_id, is_invoiced)
);

CREATE TABLE trust_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**📊 Scalability:**
- ⬜ **Medium maintenance** — Financial calculations must be exact (use DECIMAL, not FLOAT)
- ⬜ **Future: Stripe/Razorpay** for online payments
- ⬜ **Future: Automated billing** from unbilled time entries

---

### F-21: Settings & Configuration

#### 🎯 Why This Feature Exists
Every firm operates differently. Settings allow customization without code changes.

#### 🌐 Current State (from Website)
- ✅ Firm Settings: Profile, Team, Roles & Permissions, Document Categories, Forms, Payment Gateways, Audit Logs
- ✅ Account Settings: Profile, Notifications, Security
- ✅ Super Admin Settings: Platform settings, payment gateways, system configuration

**Storage Strategy:**
- Firm table columns → core config (timezone, currency, date format)
- Firm `settings` JSON column → feature flags and preferences
- User table columns → user-specific preferences
- User `notification_preferences` JSON → notification toggles

---

## 6. Database Architecture

### Entity-Relationship Overview

```
FIRMS ──┬── USERS ──── ROLES ──── PERMISSIONS
        ├── CLIENTS ──── MATTERS
        │                   ├── MATTER_USERS
        │                   ├── DOCUMENTS ──── DOCUMENT_VERSIONS
        │                   │                  DOCUMENT_SHARES
        │                   ├── DOCUMENT_REQUESTS
        │                   ├── MESSAGE_THREADS ──── MESSAGES ──── MESSAGE_ATTACHMENTS
        │                   ├── TASKS ──── TASK_COMMENTS
        │                   ├── NOTES
        │                   ├── CALENDAR_EVENTS ──── CALENDAR_EVENT_ATTENDEES
        │                   ├── INVOICES ──── INVOICE_LINE_ITEMS
        │                   └── TIME_ENTRIES
        └── DOCUMENT_CATEGORIES

USERS ──── GOOGLE_CALENDAR_CONNECTIONS
       ──── GOOGLE_DRIVE_CONNECTIONS
       ──── TODOS
       ──── USER_INVITATIONS

GLOBAL: AUDIT_LOGS, NOTIFICATIONS, GOOGLE_DRIVE_FILES
```

### Total Table Count: ~32 tables

### Estimated Row Growth (Year 1, 50-firm deployment):

| Table | Estimated Rows | Growth Rate |
|-------|---------------|-------------|
| `audit_logs` | 500,000+ | 🔴 Highest — every action logged |
| `notifications` | 200,000+ | 🔴 High — multi-channel |
| `messages` | 100,000+ | 🟡 High — daily communication |
| `document_versions` | 50,000 | 🟡 Medium — file uploads |
| `tasks` | 30,000 | 🟡 Medium — daily task creation |
| `time_entries` | 20,000 | 🟡 Medium — hourly tracking |
| `matters` | 5,000 | 🟢 Low — new matters weekly |
| `clients` | 3,000 | 🟢 Low — new clients weekly |
| `users` | 500 | 🟢 Lowest — invited users |

---

## 7. Security Architecture

### Defense-in-Depth Layers

```
Layer 1:  Network         → HTTPS, HSTS, CSP headers
Layer 2:  Rate Limiting   → 60 req/min per IP (API), 5 login attempts/min
Layer 3:  Authentication  → Email + Password + Optional 2FA (TOTP)
Layer 4:  Session         → Secure cookies, HTTP-only, SameSite=Lax, 120min timeout
Layer 5:  Firm Scope      → Global Eloquent scope filters ALL queries by firm_id
Layer 6:  Matter Access   → Middleware checks matter_users pivot table
Layer 7:  Resource Auth   → Laravel Policies on every controller action
Layer 8:  Input Valid.    → Form Requests with strict validation rules
Layer 9:  Output Enc.     → Blade {{ }} auto-escapes; {!! !!} never used with user input
Layer 10: File Security   → Private storage, authenticated downloads, no direct URLs
Layer 11: Audit Trail     → Every critical action logged with user, IP, timestamp
Layer 12: Data Encrypt.   → Google OAuth tokens encrypted at rest (APP_KEY encryption)
```

### OWASP Top 10 Mitigation

| OWASP Risk | Mitigation |
|------------|-----------|
| A01 Broken Access Control | Policies, FirmScope, MatterAccess middleware |
| A02 Cryptographic Failures | bcrypt passwords, encrypted tokens, HTTPS |
| A03 Injection | Eloquent ORM (parameterized queries), Form Requests |
| A04 Insecure Design | Private-by-default documents, least-privilege roles |
| A05 Security Misconfiguration | APP_DEBUG=false, secure .env, no default credentials |
| A06 Vulnerable Components | `composer audit`, Dependabot alerts |
| A07 Auth Failures | Rate limiting, progressive lockout, 2FA |
| A08 Data Integrity Failures | Signed URLs, CSRF tokens, version immutability |
| A09 Logging Failures | Comprehensive audit logging with retention |
| A10 SSRF | No user-supplied URLs processed server-side |

---

## 8. Scalability & Maintenance Assessment

### Overall Ratings

| Dimension | Rating | Details |
|-----------|--------|---------|
| **Horizontal Scaling** | ⭐⭐⭐ Medium | Monolith limits horizontal scaling; session affinity + DB scaling handles most growth |
| **Vertical Scaling** | ⭐⭐⭐⭐ Good | PHP is stateless; adding CPU/RAM directly improves throughput |
| **Database Scaling** | ⭐⭐⭐ Medium | MySQL read replicas for analytics; partition audit_logs by month |
| **Storage Scaling** | ⭐⭐⭐⭐⭐ Excellent | Filesystem abstraction → zero-code migration to S3 |
| **Search Scaling** | ⭐⭐⭐ Medium | MySQL FTS to ~500K rows; then swap Scout driver to Meilisearch |
| **Queue Scaling** | ⭐⭐⭐⭐ Good | Database driver to Redis/SQS is a config change |
| **Real-time Scaling** | ⭐⭐ Limited | Livewire polling doesn't scale beyond ~500 concurrent users |
| **Multi-tenancy** | ⭐⭐⭐ Medium | Shared DB works to ~500 tenants; then database-per-tenant |
| **Maintenance Burden** | ⭐⭐⭐⭐ Low | Laravel conventions minimize boilerplate |

### Maintenance Hotspots

| Area | Effort | Why |
|------|--------|-----|
| Google API Integrations | 🔴 High | Token refresh, API deprecations, scope changes, quotas |
| Audit Log Archival | 🟡 Medium | Fastest-growing table; needs partition/archive strategy |
| File Storage Monitoring | 🟡 Medium | Hostinger disk limits; migration planning |
| Security Patches | 🟡 Medium | `composer update` monthly; review CVEs |
| Database Migrations | 🟢 Low | Laravel migrations are repeatable and versioned |
| UI/UX Updates | 🟢 Low | Tailwind + Livewire makes UI changes fast |

---

## 9. Testing Strategy

### Test Pyramid

```
                    ┌─────────┐
                    │ Browser │  ← 5% — Cypress/Dusk (Phase 2)
                   ┌┴─────────┴┐
                   │  Feature   │  ← 60% — Laravel Feature Tests
                  ┌┴───────────┴┐
                  │  Unit Tests  │  ← 35% — PHPUnit/Pest
                  └──────────────┘
```

### Critical Test Scenarios

| Category | Test | Priority |
|----------|------|----------|
| **Access Control** | Client A cannot access Client B's matters | 🔴 Critical |
| **Access Control** | Attorney cannot access other firm's data | 🔴 Critical |
| **Access Control** | Client cannot see internal notes | 🔴 Critical |
| **Access Control** | Deleted documents cannot be downloaded | 🔴 Critical |
| **Access Control** | Modified UUIDs in URLs don't bypass policies | 🔴 Critical |
| **Document Security** | Direct file path access returns 403 | 🔴 Critical |
| **Auth** | Login with correct/incorrect credentials | 🔴 Critical |
| **Auth** | 2FA enforcement when enabled | 🟡 High |
| **Auth** | Account lockout after failures | 🟡 High |
| **Workflow** | Document request full lifecycle | 🟡 High |
| **Workflow** | Matter status transitions | 🟡 High |
| **Google** | OAuth token refresh | 🟡 High |
| **Search** | Authorization-aware results | 🟡 High |
| **Notifications** | Correct recipients for each event | 🟢 Medium |

---

## 10. Documentation Deliverables

| # | Document | Path | Status |
|---|----------|------|--------|
| 01 | Product Requirements | `/docs/01-product-requirements.md` | 📋 To create |
| 02 | Architecture | `/docs/02-architecture.md` | 📋 To create |
| 03 | Database Schema | `/docs/03-database.md` | 📋 To create |
| 04 | Roles & Permissions | `/docs/04-roles-permissions.md` | 📋 To create |
| 05 | Document Security | `/docs/05-document-security.md` | 📋 To create |
| 06 | Google Integrations | `/docs/06-google-integrations.md` | 📋 To create |
| 07 | Installation Guide | `/docs/07-installation.md` | 📋 To create |
| 08 | Deployment Guide | `/docs/08-deployment.md` | 📋 To create |
| 09 | Environment Variables | `/docs/09-environment-variables.md` | 📋 To create |
| 10 | Testing Guide | `/docs/10-testing.md` | 📋 To create |
| 11 | Attorney User Guide | `/docs/11-attorney-user-guide.md` | 📋 To create |
| 12 | Client User Guide | `/docs/12-client-user-guide.md` | 📋 To create |
| 13 | Admin Guide | `/docs/13-admin-guide.md` | 📋 To create |
| 14 | API Documentation | `/docs/14-api-documentation.md` | 📋 To create |
| 15 | Troubleshooting | `/docs/15-troubleshooting.md` | 📋 To create |
| 16 | Change Log | `/docs/16-change-log.md` | 📋 To create |

---

> **This document serves as the single source of truth for all Quire features. Every implementation decision should reference back to this specification.**
