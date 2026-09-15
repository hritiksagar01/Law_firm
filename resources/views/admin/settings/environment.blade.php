<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cloud &amp; Environment Settings — Super Admin Console</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#faf9f6] font-sans text-[#1a1c1a] antialiased min-h-screen" x-data="{
    activeTab: 'storage',
    testingDb: false,
    dbResult: null,
    testingS3: false,
    s3Result: null,
    testingMail: false,
    mailResult: null,
    runningMigrations: false,
    migrationResult: null,
    showMigrationModal: false
}">
    
    <!-- Top Executive Navigation Bar -->
    <header class="h-16 bg-[#111714] text-white px-6 flex items-center justify-between shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <div class="w-9 h-9 rounded-lg bg-[#1a3c2a] text-[#fed977] flex items-center justify-center font-bold shadow-inner">
                <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
            </div>
            <div>
                <span class="font-serif text-base font-bold text-white tracking-wide">LexisCore Platform Console</span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-mono text-[#82a78f]">Super Administrator Access</span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#1a231e] p-1 rounded-lg border border-[#2b3830] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md text-[#a3b8aa] hover:text-white hover:bg-[#222e27] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md bg-[#1a3c2a] text-[#fed977] font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-xs bg-[#1a231e] hover:bg-[#26352d] text-[#c5ecd2] border border-[#2e3e34] px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Law Firm Chambers</span>
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-6 md:p-8">
        
        <!-- Header Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono text-[#727973] uppercase tracking-wider mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:underline">Super Admin</a>
                    <span>/</span>
                    <span class="text-[#1a3c2a] font-semibold">Environment Variables</span>
                </div>
                <h1 class="text-3xl font-serif font-bold text-[#1a1c1a]">Cloud &amp; Infrastructure Settings</h1>
                <p class="text-sm text-[#727973] mt-1">Configure AWS S3 storage buckets, PostgreSQL/MySQL database clusters, SMTP mail, and application parameters safely.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-[#e9e8e5] text-xs font-mono text-[#424843] shadow-sm">
                    <span class="material-symbols-outlined text-sm text-emerald-600">verified_user</span>
                    <span>Safe .env Manager v2.0</span>
                </span>
            </div>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-lg text-emerald-700">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <span class="text-[10px] font-mono uppercase text-emerald-700 font-bold">Applied &amp; Cached</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-lg text-red-700">error</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <span class="text-[10px] font-mono uppercase text-red-700 font-bold">Action Failed</span>
        </div>
        @endif

        <!-- Safeguard Notice Banner -->
        <div class="mb-6 p-4 rounded-xl bg-[#f5f7f5] border border-[#dce4dc] text-xs text-[#2c3d31] flex items-start gap-3 shadow-sm">
            <span class="material-symbols-outlined text-lg text-[#1a3c2a] shrink-0 mt-0.5">security</span>
            <div class="leading-relaxed">
                <strong class="font-semibold text-[#1a1c1a]">Automatic Snapshot Protection Active:</strong>
                Every save automatically writes a timestamped snapshot of your <code class="bg-white px-1.5 py-0.5 rounded border border-[#dce4dc] text-[#1a3c2a] font-mono">.env</code> to <code class="bg-white px-1.5 py-0.5 rounded border border-[#dce4dc] font-mono">storage/app/env-backups/</code>. Sensitive keys (AWS Secret Key, DB Password, SMTP Password) are masked with <span class="font-mono">••••••••</span> and will not be overwritten unless you type a replacement.
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 bg-[#efeeeb] rounded-xl mb-6 border border-[#e2e1dd]">
            <button @click="activeTab = 'storage'" 
                :class="activeTab === 'storage' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-amber-600">cloud_upload</span>
                <span>Cloud Storage (AWS S3 / R2)</span>
            </button>

            <button @click="activeTab = 'database'" 
                :class="activeTab === 'database' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-blue-600">database</span>
                <span>Database Engine</span>
            </button>

            <button @click="activeTab = 'mail'" 
                :class="activeTab === 'mail' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-purple-600">mail</span>
                <span>Email &amp; SMTP Relay</span>
            </button>

            <button @click="activeTab = 'app'" 
                :class="activeTab === 'app' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-emerald-600">settings_applications</span>
                <span>App &amp; Indian Localization</span>
            </button>

            <button @click="activeTab = 'backups'" 
                :class="activeTab === 'backups' ? 'bg-white text-[#1a1c1a] shadow-sm font-semibold' : 'text-[#727973] hover:text-[#1a1c1a] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-slate-600">history</span>
                <span>Backups &amp; Raw Editor ({{ count($backups) }})</span>
            </button>
        </div>

        <!-- TAB 1: CLOUD STORAGE (AWS S3 / CLOUDFLARE R2) -->
        <div x-show="activeTab === 'storage'" class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#efeeeb] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">cloud</span>
                        <span>Object Storage (Amazon S3 / Cloudflare R2 / MinIO)</span>
                    </h2>
                    <p class="text-xs text-[#727973] mt-1">Configure encrypted cloud bucket storage for legal case files, exhibits, affidavits, and invoice PDFs.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testS3Connection()"
                        :disabled="testingS3"
                        class="px-3.5 py-2 rounded-lg bg-[#f4f3f1] hover:bg-[#eae8e4] text-[#1a1c1a] text-xs font-semibold border border-[#dce4dc] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingS3" class="material-symbols-outlined text-sm text-amber-700">network_check</span>
                        <span x-show="testingS3" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingS3 ? 'Pinging Endpoint...' : 'Test S3 Connection'"></span>
                    </button>
                </div>
            </div>

            <!-- Live S3 Test Feedback Banner -->
            <template x-if="s3Result">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="s3Result.success ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-red-50 border border-red-200 text-red-900'">
                    <span class="material-symbols-outlined text-base shrink-0 mt-0.5" x-text="s3Result.success ? 'check_circle' : 'error'"></span>
                    <div>
                        <strong class="font-semibold block" x-text="s3Result.success ? 'Storage Connection Verified' : 'Storage Connection Failed'"></strong>
                        <span x-text="s3Result.message"></span>
                    </div>
                </div>
            </template>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="s3Form">
                @csrf
                <input type="hidden" name="_form_section" value="storage"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Default Filesystem Disk -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Active Storage Driver (FILESYSTEM_DISK)
                        </label>
                        <select name="FILESYSTEM_DISK" id="s3_disk" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="local" {{ ($settings['storage']['FILESYSTEM_DISK'] ?? '') === 'local' ? 'selected' : '' }}>local (Server Local Disk - Default)</option>
                            <option value="s3" {{ ($settings['storage']['FILESYSTEM_DISK'] ?? '') === 's3' ? 'selected' : '' }}>s3 (Amazon Web Services S3 / Cloudflare R2 / Supabase)</option>
                        </select>
                        <span class="text-[11px] text-[#727973] mt-1 block">Set to <code class="font-mono text-[#1a3c2a]">s3</code> when using an external cloud bucket.</span>
                    </div>

                    <!-- AWS Default Region -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Cloud Region (AWS_DEFAULT_REGION)
                        </label>
                        <input type="text" name="AWS_DEFAULT_REGION" id="s3_region" value="{{ $settings['storage']['AWS_DEFAULT_REGION'] ?? 'ap-south-1' }}" placeholder="ap-south-1" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block font-mono">Recommended: <span class="text-[#1a3c2a] font-bold">ap-south-1</span> (Mumbai, India) or <span class="text-[#1a3c2a]">auto</span> (Cloudflare R2).</span>
                    </div>

                    <!-- AWS Bucket Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            S3 Bucket Name (AWS_BUCKET)
                        </label>
                        <input type="text" name="AWS_BUCKET" id="s3_bucket" value="{{ $settings['storage']['AWS_BUCKET'] ?? '' }}" placeholder="lawfirm-vault-files" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">The exact name of your bucket created in AWS S3 or Cloudflare R2.</span>
                    </div>

                    <!-- AWS Custom Endpoint (For R2 / MinIO) -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Custom Endpoint (AWS_ENDPOINT) — Optional
                        </label>
                        <input type="text" name="AWS_ENDPOINT" id="s3_endpoint" value="{{ $settings['storage']['AWS_ENDPOINT'] ?? '' }}" placeholder="https://<account_id>.r2.cloudflarestorage.com" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">Leave blank for standard AWS S3. Fill in for Cloudflare R2, MinIO, or Supabase Storage.</span>
                    </div>

                    <!-- AWS Access Key ID -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Access Key ID (AWS_ACCESS_KEY_ID)
                        </label>
                        <input type="text" name="AWS_ACCESS_KEY_ID" id="s3_key" value="{{ $settings['storage']['AWS_ACCESS_KEY_ID'] ?? '' }}" placeholder="AKIAIOSFODNN7EXAMPLE" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- AWS Secret Access Key -->
                    <div x-data="{ showSecret: false }">
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Secret Access Key (AWS_SECRET_ACCESS_KEY)
                            @if($settings['storage']['AWS_HAS_SECRET'])
                                <span class="text-[10px] text-emerald-700 font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showSecret ? 'text' : 'password'" name="AWS_SECRET_ACCESS_KEY" id="s3_secret" value="{{ $settings['storage']['AWS_SECRET_ACCESS_KEY'] ?? '' }}" placeholder="{{ $settings['storage']['AWS_HAS_SECRET'] ? '••••••••' : 'Enter Secret Key' }}" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 pr-10 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                            <button type="button" @click="showSecret = !showSecret" class="absolute right-2.5 top-2.5 text-[#727973] hover:text-[#1a1c1a]">
                                <span class="material-symbols-outlined text-base" x-text="showSecret ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <span class="text-[11px] text-[#727973] mt-1 block">Leave as <code class="font-mono">••••••••</code> to keep existing secret unchanged.</span>
                    </div>

                    <!-- Path Style Endpoint -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Path Style Endpoint (AWS_USE_PATH_STYLE_ENDPOINT)
                        </label>
                        <select name="AWS_USE_PATH_STYLE_ENDPOINT" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="false" {{ ($settings['storage']['AWS_USE_PATH_STYLE_ENDPOINT'] ?? '') === 'false' ? 'selected' : '' }}>false (Default for standard AWS S3 / Cloudflare R2)</option>
                            <option value="true" {{ ($settings['storage']['AWS_USE_PATH_STYLE_ENDPOINT'] ?? '') === 'true' ? 'selected' : '' }}>true (Required for MinIO and self-hosted S3)</option>
                        </select>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#efeeeb] flex items-center justify-between">
                    <span class="text-xs text-[#727973]">Takes effect immediately across all file uploads.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1a3c2a] hover:bg-[#022616] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#fed977]">save</span>
                        <span>Save Cloud Storage Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: DATABASE ENGINE -->
        <div x-show="activeTab === 'database'" class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#efeeeb] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">database</span>
                        <span>Relational Database Configuration (Postgres / MySQL / SQLite)</span>
                    </h2>
                    <p class="text-xs text-[#727973] mt-1">Manage database connection pool. Supports cloud providers like Supabase, AWS RDS, Neon, or local SQLite.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testDbConnection()"
                        :disabled="testingDb"
                        class="px-3.5 py-2 rounded-lg bg-[#f4f3f1] hover:bg-[#eae8e4] text-[#1a1c1a] text-xs font-semibold border border-[#dce4dc] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingDb" class="material-symbols-outlined text-sm text-blue-700">cable</span>
                        <span x-show="testingDb" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingDb ? 'Connecting via PDO...' : 'Test DB Connection'"></span>
                    </button>

                    <button type="button" 
                        @click="runMigrations()"
                        :disabled="runningMigrations"
                        class="px-3.5 py-2 rounded-lg bg-[#1a3c2a] hover:bg-[#022616] text-white text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                        <span x-show="!runningMigrations" class="material-symbols-outlined text-sm text-[#fed977]">schema</span>
                        <span x-show="runningMigrations" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="runningMigrations ? 'Running Migrations...' : 'Run Migrations &amp; Seed'"></span>
                    </button>
                </div>
            </div>

            <!-- Caution Advisory -->
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
                <span class="material-symbols-outlined text-base text-amber-700 shrink-0 mt-0.5">warning</span>
                <div class="leading-relaxed">
                    <strong class="font-semibold text-amber-950">Important Database Safety Notice:</strong>
                    Always click <strong class="font-semibold">"Test DB Connection"</strong> before saving new database credentials. If you point to a fresh empty database (e.g. on Supabase or AWS RDS), click <strong class="font-semibold">"Run Migrations &amp; Seed"</strong> to provision all law firm tables.
                </div>
            </div>

            <!-- Live DB Test Feedback Banner -->
            <template x-if="dbResult">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="dbResult.success ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-red-50 border border-red-200 text-red-900'">
                    <span class="material-symbols-outlined text-base shrink-0 mt-0.5" x-text="dbResult.success ? 'check_circle' : 'error'"></span>
                    <div>
                        <strong class="font-semibold block" x-text="dbResult.success ? 'Database Connection Verified' : 'Database Connection Error'"></strong>
                        <span x-text="dbResult.message"></span>
                    </div>
                </div>
            </template>

            <!-- Migration Output Feedback Banner -->
            <template x-if="migrationResult">
                <div class="mb-6 p-4 rounded-xl text-xs border"
                    :class="migrationResult.success ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : 'bg-red-50 border-red-200 text-red-950'">
                    <div class="flex items-center justify-between mb-2">
                        <strong class="font-semibold" x-text="migrationResult.message"></strong>
                        <button type="button" @click="migrationResult = null" class="text-xs text-[#727973] hover:underline">Dismiss</button>
                    </div>
                    <pre class="bg-white/80 p-3 rounded-lg font-mono text-[11px] overflow-x-auto max-h-48 border border-emerald-200/50" x-text="migrationResult.output"></pre>
                </div>
            </template>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="dbForm">
                @csrf
                <input type="hidden" name="_form_section" value="database"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Database Driver -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Connection Driver (DB_CONNECTION)
                        </label>
                        <select name="DB_CONNECTION" id="db_driver" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="sqlite" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'sqlite' ? 'selected' : '' }}>sqlite (SQLite File — Zero Config / Local)</option>
                            <option value="pgsql" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'pgsql' ? 'selected' : '' }}>pgsql (PostgreSQL — Recommended for Supabase / Neon / AWS RDS)</option>
                            <option value="mysql" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'mysql' ? 'selected' : '' }}>mysql (MySQL / MariaDB / Amazon Aurora)</option>
                        </select>
                        <span class="text-[11px] text-[#727973] mt-1 block">Choose <code class="font-mono text-[#1a3c2a]">pgsql</code> for Supabase.</span>
                    </div>

                    <!-- Database Host -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Host (DB_HOST)
                        </label>
                        <input type="text" name="DB_HOST" id="db_host" value="{{ $settings['database']['DB_HOST'] ?? '127.0.0.1' }}" placeholder="aws-0-ap-south-1.pooler.supabase.com" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">Ignored when using SQLite. For Supabase, use your pooler or direct host.</span>
                    </div>

                    <!-- Database Port -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Port (DB_PORT)
                        </label>
                        <input type="text" name="DB_PORT" id="db_port" value="{{ $settings['database']['DB_PORT'] ?? '3306' }}" placeholder="5432" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block font-mono">Standard: <span class="font-bold">5432</span> (PostgreSQL/Supabase) or <span class="font-bold">3306</span> (MySQL).</span>
                    </div>

                    <!-- Database Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Name / Path (DB_DATABASE)
                        </label>
                        <input type="text" name="DB_DATABASE" id="db_database" value="{{ $settings['database']['DB_DATABASE'] ?? '' }}" placeholder="postgres or database/database.sqlite" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">For Supabase, usually <code class="font-mono">postgres</code>. For SQLite, the path to <code class="font-mono">.sqlite</code> file.</span>
                    </div>

                    <!-- Database Username -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Username (DB_USERNAME)
                        </label>
                        <input type="text" name="DB_USERNAME" id="db_username" value="{{ $settings['database']['DB_USERNAME'] ?? '' }}" placeholder="postgres.yourprojectid" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- Database Password -->
                    <div x-data="{ showDbPass: false }">
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Database Password (DB_PASSWORD)
                            @if($settings['database']['DB_HAS_PASSWORD'])
                                <span class="text-[10px] text-emerald-700 font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showDbPass ? 'text' : 'password'" name="DB_PASSWORD" id="db_password" value="{{ $settings['database']['DB_PASSWORD'] ?? '' }}" placeholder="{{ $settings['database']['DB_HAS_PASSWORD'] ? '••••••••' : 'Enter DB Password' }}" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 pr-10 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                            <button type="button" @click="showDbPass = !showDbPass" class="absolute right-2.5 top-2.5 text-[#727973] hover:text-[#1a1c1a]">
                                <span class="material-symbols-outlined text-base" x-text="showDbPass ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <span class="text-[11px] text-[#727973] mt-1 block">Leave as <code class="font-mono">••••••••</code> to keep existing password unchanged.</span>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#efeeeb] flex items-center justify-between">
                    <span class="text-xs text-[#727973]">Laravel cache will be cleared immediately upon saving.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1a3c2a] hover:bg-[#022616] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#fed977]">save</span>
                        <span>Save Database Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 3: EMAIL & SMTP RELAY -->
        <div x-show="activeTab === 'mail'" class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#efeeeb] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">mail</span>
                        <span>Outbound SMTP Email Gateway (Brevo / SES / Mailgun)</span>
                    </h2>
                    <p class="text-xs text-[#727973] mt-1">Configure transactional email delivery for client invitation links, retainer reminders, hearing alerts, and invoice billing.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testMailConnection()"
                        :disabled="testingMail"
                        class="px-3.5 py-2 rounded-lg bg-[#f4f3f1] hover:bg-[#eae8e4] text-[#1a1c1a] text-xs font-semibold border border-[#dce4dc] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingMail" class="material-symbols-outlined text-sm text-purple-700">forward_to_inbox</span>
                        <span x-show="testingMail" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingMail ? 'Testing SMTP Port...' : 'Test SMTP Connection'"></span>
                    </button>
                </div>
            </div>

            <!-- Live Mail Test Feedback Banner -->
            <template x-if="mailResult">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="mailResult.success ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-red-50 border border-red-200 text-red-900'">
                    <span class="material-symbols-outlined text-base shrink-0 mt-0.5" x-text="mailResult.success ? 'check_circle' : 'error'"></span>
                    <div>
                        <strong class="font-semibold block" x-text="mailResult.success ? 'SMTP Gateway Reachable' : 'SMTP Connection Error'"></strong>
                        <span x-text="mailResult.message"></span>
                    </div>
                </div>
            </template>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="mailForm">
                @csrf
                <input type="hidden" name="_form_section" value="mail"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Mail Mailer -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Mail Delivery Engine (MAIL_MAILER)
                        </label>
                        <select name="MAIL_MAILER" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="smtp" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'smtp' ? 'selected' : '' }}>smtp (Live SMTP Relay — Recommended)</option>
                            <option value="log" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'log' ? 'selected' : '' }}>log (Log to storage/logs/laravel.log — Local Testing)</option>
                            <option value="ses" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'ses' ? 'selected' : '' }}>ses (Amazon Simple Email Service)</option>
                        </select>
                        <span class="text-[11px] text-[#727973] mt-1 block">Set to <code class="font-mono text-[#1a3c2a]">smtp</code> for Brevo (Sendinblue), Mailgun, or Postmark.</span>
                    </div>

                    <!-- SMTP Host -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            SMTP Server Host (MAIL_HOST)
                        </label>
                        <input type="text" name="MAIL_HOST" id="mail_host" value="{{ $settings['mail']['MAIL_HOST'] ?? 'smtp.brevo.com' }}" placeholder="smtp-relay.brevo.com" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- SMTP Port -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            SMTP Port (MAIL_PORT)
                        </label>
                        <input type="text" name="MAIL_PORT" id="mail_port" value="{{ $settings['mail']['MAIL_PORT'] ?? '587' }}" placeholder="587" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block font-mono">Standard ports: <span class="font-bold">587</span> (TLS), <span class="font-bold">465</span> (SSL), or <span class="font-bold">2525</span>.</span>
                    </div>

                    <!-- SMTP Encryption -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Encryption Security (MAIL_ENCRYPTION)
                        </label>
                        <select name="MAIL_ENCRYPTION" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="tls" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'tls' ? 'selected' : '' }}>tls (Transport Layer Security — Recommended for port 587)</option>
                            <option value="ssl" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'ssl' ? 'selected' : '' }}>ssl (Secure Sockets Layer — Recommended for port 465)</option>
                            <option value="null" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'null' ? 'selected' : '' }}>none (Unencrypted)</option>
                        </select>
                    </div>

                    <!-- SMTP Username -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            SMTP Username (MAIL_USERNAME)
                        </label>
                        <input type="text" name="MAIL_USERNAME" value="{{ $settings['mail']['MAIL_USERNAME'] ?? '' }}" placeholder="7b3d9... or api-key-login" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- SMTP Password -->
                    <div x-data="{ showMailPass: false }">
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            SMTP Password / API Key (MAIL_PASSWORD)
                            @if($settings['mail']['MAIL_HAS_PASSWORD'])
                                <span class="text-[10px] text-emerald-700 font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showMailPass ? 'text' : 'password'" name="MAIL_PASSWORD" value="{{ $settings['mail']['MAIL_PASSWORD'] ?? '' }}" placeholder="{{ $settings['mail']['MAIL_HAS_PASSWORD'] ? '••••••••' : 'Enter SMTP Key' }}" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 pr-10 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                            <button type="button" @click="showMailPass = !showMailPass" class="absolute right-2.5 top-2.5 text-[#727973] hover:text-[#1a1c1a]">
                                <span class="material-symbols-outlined text-base" x-text="showMailPass ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- From Email Address -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Outgoing Sender Address (MAIL_FROM_ADDRESS)
                        </label>
                        <input type="email" name="MAIL_FROM_ADDRESS" value="{{ $settings['mail']['MAIL_FROM_ADDRESS'] ?? 'chambers@sharmalegal.in' }}" placeholder="notifications@sharmalegal.in" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">Must match the verified sending domain configured in your SMTP provider.</span>
                    </div>

                    <!-- From Sender Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Outgoing Sender Display Name (MAIL_FROM_NAME)
                        </label>
                        <input type="text" name="MAIL_FROM_NAME" value="{{ $settings['mail']['MAIL_FROM_NAME'] ?? 'Sharma & Associates, Advocates' }}" placeholder="Sharma & Associates, Advocates" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#efeeeb] flex items-center justify-between">
                    <span class="text-xs text-[#727973]">Applies to all client email notifications and billing dispatches.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1a3c2a] hover:bg-[#022616] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#fed977]">save</span>
                        <span>Save Email Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 4: GENERAL APP & INDIAN LOCALIZATION -->
        <div x-show="activeTab === 'app'" class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#efeeeb] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600">settings_applications</span>
                        <span>Application Identity &amp; Indian Legal Localization</span>
                    </h2>
                    <p class="text-xs text-[#727973] mt-1">Platform branding, Indian Rupee currency standards (₹ INR), timezones, and environment status.</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="appForm">
                @csrf
                <input type="hidden" name="_form_section" value="app"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- APP_NAME -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            System Application Name (APP_NAME)
                        </label>
                        <input type="text" name="APP_NAME" value="{{ $settings['app']['APP_NAME'] ?? 'Law Firm Management' }}" placeholder="Law Firm Management" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- LEGAL_APP_NAME -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Client-Facing Chambers Title (LEGAL_APP_NAME)
                        </label>
                        <input type="text" name="LEGAL_APP_NAME" value="{{ $settings['app']['LEGAL_APP_NAME'] ?? 'Sharma & Associates, Advocates' }}" placeholder="Sharma & Associates, Advocates & Solicitors" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">Displayed in sidebar, client portal headers, and legal document watermarks.</span>
                    </div>

                    <!-- APP_URL -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Root Application URL (APP_URL)
                        </label>
                        <input type="url" name="APP_URL" value="{{ $settings['app']['APP_URL'] ?? 'http://localhost:8000' }}" placeholder="https://chambers.sharmalegal.in" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                        <span class="text-[11px] text-[#727973] mt-1 block">Used to generate password reset links and client portal access invites.</span>
                    </div>

                    <!-- APP_ENV -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Deployment Environment (APP_ENV)
                        </label>
                        <select name="APP_ENV" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]">
                            <option value="local" {{ ($settings['app']['APP_ENV'] ?? '') === 'local' ? 'selected' : '' }}>local (Development Mode)</option>
                            <option value="production" {{ ($settings['app']['APP_ENV'] ?? '') === 'production' ? 'selected' : '' }}>production (Live Production Server)</option>
                            <option value="staging" {{ ($settings['app']['APP_ENV'] ?? '') === 'staging' ? 'selected' : '' }}>staging (Staging / Pre-Release)</option>
                        </select>
                    </div>

                    <!-- Currency Symbol -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            Default Currency Symbol (LEGAL_CURRENCY_SYMBOL)
                        </label>
                        <input type="text" name="LEGAL_CURRENCY_SYMBOL" value="{{ $settings['app']['LEGAL_CURRENCY_SYMBOL'] ?? '₹' }}" placeholder="₹" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-bold text-[#1a3c2a] focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- Currency Code -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1a1c1a] mb-1.5">
                            ISO Currency Code (LEGAL_CURRENCY_CODE)
                        </label>
                        <input type="text" name="LEGAL_CURRENCY_CODE" value="{{ $settings['app']['LEGAL_CURRENCY_CODE'] ?? 'INR' }}" placeholder="INR" class="w-full text-xs rounded-lg border border-[#c1c8c1] p-2.5 bg-[#faf9f6] focus:bg-white font-mono focus:ring-1 focus:ring-[#1a3c2a] focus:border-[#1a3c2a]"/>
                    </div>

                    <!-- Debug Mode -->
                    <div class="md:col-span-2 p-4 rounded-xl bg-[#faf9f6] border border-[#e9e8e5] flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-[#1a1c1a] block">Debug &amp; Verbose Diagnostics (APP_DEBUG)</span>
                            <span class="text-[11px] text-[#727973] block mt-0.5">When enabled, detailed stack traces are displayed on server errors. Turn OFF in production.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="APP_DEBUG" value="true" class="sr-only peer" {{ ($settings['app']['APP_DEBUG'] ?? '') === 'true' ? 'checked' : '' }}/>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1a3c2a]"></div>
                        </label>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#efeeeb] flex items-center justify-between">
                    <span class="text-xs text-[#727973]">Instant cache invalidation runs automatically upon saving.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1a3c2a] hover:bg-[#022616] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#fed977]">save</span>
                        <span>Save Application Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 5: BACKUPS & RAW .ENV EDITOR -->
        <div x-show="activeTab === 'backups'" class="space-y-6">
            
            <!-- Automated Backups List -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8">
                <div class="border-b border-[#efeeeb] pb-5 mb-6">
                    <h2 class="text-lg font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-600">history</span>
                        <span>Automated .env Configuration Snapshots</span>
                    </h2>
                    <p class="text-xs text-[#727973] mt-1">A safety backup is generated every time configuration is modified. You can revert to any previous state with 1-click restore.</p>
                </div>

                @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#f4f3f1] border-b border-[#e9e8e5] text-[11px] font-mono text-[#727973] uppercase tracking-wider">
                                <th class="py-3 px-4">Backup Snapshot File</th>
                                <th class="py-3 px-4">Created Timestamp</th>
                                <th class="py-3 px-4 text-center">File Size</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#efeeeb] text-xs">
                            @foreach($backups as $backup)
                            <tr class="hover:bg-[#faf9f6]">
                                <td class="py-3 px-4 font-mono font-medium text-[#1a1c1a]">
                                    {{ $backup['filename'] }}
                                </td>
                                <td class="py-3 px-4 text-[#727973]">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-[#727973]">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.settings.environment.restore-backup') }}" method="POST" onsubmit="return confirm('Revert .env to this snapshot? Current file will be backed up.');" class="inline">
                                        @csrf
                                        <input type="hidden" name="filename" value="{{ $backup['filename'] }}"/>
                                        <button type="submit" class="px-2.5 py-1 rounded bg-[#f4f3f1] hover:bg-amber-100 text-amber-900 border border-amber-300 font-semibold transition-all inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">settings_backup_restore</span>
                                            <span>Restore Snapshot</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-xs text-[#727973] bg-[#faf9f6] rounded-xl border border-dashed border-[#dce4dc]">
                    <span class="material-symbols-outlined text-3xl text-[#c1c8c1] block mb-2">folder_off</span>
                    <span>No automated backups created yet. The first backup will be created automatically upon your first update.</span>
                </div>
                @endif
            </div>

            <!-- Advanced Raw .env Editor -->
            <div class="bg-white rounded-xl border border-[#e9e8e5] shadow-sm p-6 md:p-8" x-data="{ showRaw: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-serif font-bold text-[#1a1c1a] flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-700">code</span>
                            <span>Advanced Direct .env Code Editor</span>
                        </h3>
                        <p class="text-xs text-[#727973] mt-0.5">Caution: Direct raw editing overrides automated validations. Use only when adding custom third-party environment flags.</p>
                    </div>
                    <button type="button" @click="showRaw = !showRaw" class="px-3 py-1.5 rounded-lg bg-[#f4f3f1] text-xs font-semibold text-[#1a1c1a] border border-[#dce4dc] hover:bg-[#eae8e4]">
                        <span x-text="showRaw ? 'Hide Raw Editor' : 'Show Raw Editor'"></span>
                    </button>
                </div>

                <div x-show="showRaw" class="mt-6 pt-6 border-t border-[#efeeeb]" x-transition>
                    <form action="{{ route('admin.settings.environment.update') }}" method="POST" onsubmit="return confirm('Save raw .env content? Ensure syntax is correct.');">
                        @csrf
                        <div class="mb-4">
                            <textarea name="raw_env_content" rows="18" class="w-full bg-[#111714] text-[#82a78f] font-mono text-xs p-4 rounded-xl border border-[#2b3830] focus:ring-1 focus:ring-emerald-500 leading-relaxed">{{ $rawEnv }}</textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-red-700 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                <span>A snapshot backup will be created automatically before saving.</span>
                            </span>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-red-800 hover:bg-red-900 text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">save</span>
                                <span>Save Raw .env File</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </main>

    <!-- Client-Side AJAX Helpers for Live Verification -->
    <script>
        function testDbConnection() {
            const body = {
                _token: '{{ csrf_token() }}',
                driver: document.getElementById('db_driver').value,
                host: document.getElementById('db_host').value,
                port: document.getElementById('db_port').value,
                database: document.getElementById('db_database').value,
                username: document.getElementById('db_username').value,
                password: document.getElementById('db_password').value,
            };

            const alpine = Alpine.$data(document.body);
            alpine.testingDb = true;
            alpine.dbResult = null;

            fetch('{{ route('admin.settings.environment.test-db') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(data => {
                alpine.testingDb = false;
                alpine.dbResult = data;
            })
            .catch(err => {
                alpine.testingDb = false;
                alpine.dbResult = { success: false, message: 'Request error: ' + err.message };
            });
        }

        function testS3Connection() {
            const body = {
                _token: '{{ csrf_token() }}',
                key: document.getElementById('s3_key').value,
                secret: document.getElementById('s3_secret').value,
                region: document.getElementById('s3_region').value,
                bucket: document.getElementById('s3_bucket').value,
                endpoint: document.getElementById('s3_endpoint').value,
            };

            const alpine = Alpine.$data(document.body);
            alpine.testingS3 = true;
            alpine.s3Result = null;

            fetch('{{ route('admin.settings.environment.test-s3') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(data => {
                alpine.testingS3 = false;
                alpine.s3Result = data;
            })
            .catch(err => {
                alpine.testingS3 = false;
                alpine.s3Result = { success: false, message: 'Request error: ' + err.message };
            });
        }

        function testMailConnection() {
            const body = {
                _token: '{{ csrf_token() }}',
                host: document.getElementById('mail_host').value,
                port: document.getElementById('mail_port').value,
            };

            const alpine = Alpine.$data(document.body);
            alpine.testingMail = true;
            alpine.mailResult = null;

            fetch('{{ route('admin.settings.environment.test-mail') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(data => {
                alpine.testingMail = false;
                alpine.mailResult = data;
            })
            .catch(err => {
                alpine.testingMail = false;
                alpine.mailResult = { success: false, message: 'Request error: ' + err.message };
            });
        }

        function runMigrations() {
            if (!confirm('Run database migrations now? This will execute "php artisan migrate --force" on the active database.')) {
                return;
            }

            const alpine = Alpine.$data(document.body);
            alpine.runningMigrations = true;
            alpine.migrationResult = null;

            fetch('{{ route('admin.settings.environment.run-migrations') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                alpine.runningMigrations = false;
                alpine.migrationResult = data;
            })
            .catch(err => {
                alpine.runningMigrations = false;
                alpine.migrationResult = { success: false, message: 'Migration execution error', output: err.message };
            });
        }
    </script>
</body>
</html>
