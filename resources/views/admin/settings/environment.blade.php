<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cloud &amp; Environment Settings — Super Admin Console</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#FAF8F5] font-sans text-[#222222] antialiased min-h-screen" x-data="{
    activeTab: '{{ request('tab', 'storage') }}',
    testingDb: false,
    dbResult: null,
    testingS3: false,
    s3Result: null,
    testingMail: false,
    mailResult: null,
    runningMigrations: false,
    seedingDb: false,
    migrationResult: null,
    showMigrationModal: false,
    showRestoreModal: false,
    restoreTargetFile: '',
    restoringBackup: false,
    restoreFeedback: null,
    showCleanModal: false,
    cleaningData: false,
    cleanConfirmPhrase: '',
    cleanResult: null
}">
    
    <!-- Top Executive Navigation Bar -->
    <header class="h-16 bg-[#222222] text-white px-6 flex items-center justify-between shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="{{ config('legal.app_name', 'Vennamraj Associates') }}" class="h-9 w-auto object-contain rounded-md bg-white p-0.5"/>
            </a>
            <div>
                <span class="font-serif text-base font-bold text-white tracking-wide">Platform Administration Console</span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-mono text-[#8C7F72]">Super Administrator Access</span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#9F8349] animate-pulse"></span>
                </div>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-[#2E2823] p-1 rounded-lg border border-[#3E352E] text-xs">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md text-[#8C7F72] hover:text-white hover:bg-[#2E2823] transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Tenant Telemetry</span>
            </a>
            <a href="{{ route('admin.settings.environment') }}" class="px-3 py-1.5 rounded-md bg-[#9F8349] text-[#B88B56] font-semibold shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">tune</span>
                <span>Environment &amp; Cloud</span>
            </a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-xs bg-[#2E2823] hover:bg-[#3E352E] text-[#F4ECE1] border border-[#3E352E] px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">balance</span>
                <span>Chambers</span>
            </a>
            <a href="{{ route('logout') }}" class="text-xs bg-red-950/40 hover:bg-red-900/60 text-red-200 border border-red-900/50 px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">logout</span>
                <span>Sign Out</span>
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-6 md:p-8">
        
        <!-- Header Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono text-[#766A5E] uppercase tracking-wider mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:underline">Super Admin</a>
                    <span>/</span>
                    <span class="text-[#9F8349] font-semibold">Environment Variables</span>
                </div>
                <h1 class="text-3xl font-serif font-bold text-[#222222]">Cloud &amp; Infrastructure Settings</h1>
                <p class="text-sm text-[#766A5E] mt-1">Configure AWS S3 storage buckets, PostgreSQL/MySQL database clusters, SMTP mail, and application parameters safely.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-[#EFECE6] text-xs font-mono text-[#554D45] shadow-sm">
                    <span class="material-symbols-outlined text-sm text-[#9F8349]">verified_user</span>
                    <span>Safe .env Manager v2.0</span>
                </span>
            </div>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-[#F8F4EE] border border-[#E8DAC8] text-xs text-[#442E15] flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-lg text-[#9F8349]">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <span class="text-[10px] font-mono uppercase text-[#9F8349] font-bold">Applied &amp; Cached</span>
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
        <div class="mb-6 p-4 rounded-xl bg-[#FAF8F5] border border-[#E8DAC8] text-xs text-[#3E352E] flex items-start gap-3 shadow-sm">
            <span class="material-symbols-outlined text-lg text-[#9F8349] shrink-0 mt-0.5">security</span>
            <div class="leading-relaxed">
                <strong class="font-semibold text-[#222222]">Automatic Snapshot Protection Active:</strong>
                Every save automatically writes a timestamped snapshot of your <code class="bg-white px-1.5 py-0.5 rounded border border-[#E8DAC8] text-[#9F8349] font-mono">.env</code> to <code class="bg-white px-1.5 py-0.5 rounded border border-[#E8DAC8] font-mono">storage/app/env-backups/</code>. Sensitive keys (AWS Secret Key, DB Password, SMTP Password) are masked with <span class="font-mono">••••••••</span> and will not be overwritten unless you type a replacement.
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 bg-[#F4EFEA] rounded-xl mb-6 border border-[#e2e1dd]">
            <button @click="activeTab = 'storage'; history.replaceState(null, null, '?tab=storage')" 
                :class="activeTab === 'storage' ? 'bg-white text-[#222222] shadow-sm font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-amber-600">cloud_upload</span>
                <span>Cloud Storage (AWS S3 / R2)</span>
            </button>

            <button @click="activeTab = 'database'; history.replaceState(null, null, '?tab=database')" 
                :class="activeTab === 'database' ? 'bg-white text-[#222222] shadow-sm font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-blue-600">database</span>
                <span>Database Engine</span>
            </button>

            <button @click="activeTab = 'mail'; history.replaceState(null, null, '?tab=mail')" 
                :class="activeTab === 'mail' ? 'bg-white text-[#222222] shadow-sm font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-purple-600">mail</span>
                <span>Email &amp; SMTP Relay</span>
            </button>

            <button @click="activeTab = 'app'; history.replaceState(null, null, '?tab=app')" 
                :class="activeTab === 'app' ? 'bg-white text-[#222222] shadow-sm font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-[#9F8349]">settings_applications</span>
                <span>App &amp; Indian Localization</span>
            </button>

            <button @click="activeTab = 'backups'; history.replaceState(null, null, '?tab=backups')" 
                :class="activeTab === 'backups' ? 'bg-white text-[#222222] shadow-sm font-semibold' : 'text-[#766A5E] hover:text-[#222222] font-medium'"
                class="py-2.5 px-4 rounded-lg text-xs flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-[#554D45]">history</span>
                <span>Backups &amp; Raw Editor ({{ count($backups) }})</span>
            </button>
        </div>

        <!-- TAB 1: CLOUD STORAGE (AWS S3 / CLOUDFLARE R2) -->
        <div x-show="activeTab === 'storage'" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#F4EFEA] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">cloud</span>
                        <span>Object Storage (Amazon S3 / Cloudflare R2 / MinIO)</span>
                    </h2>
                    <p class="text-xs text-[#766A5E] mt-1">Configure encrypted cloud bucket storage for legal case files, exhibits, affidavits, and invoice PDFs.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testS3Connection()"
                        :disabled="testingS3"
                        class="px-3.5 py-2 rounded-lg bg-[#FAF8F5] hover:bg-[#eae8e4] text-[#222222] text-xs font-semibold border border-[#E8DAC8] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingS3" class="material-symbols-outlined text-sm text-amber-700">network_check</span>
                        <span x-show="testingS3" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingS3 ? 'Pinging Endpoint...' : 'Test S3 Connection'"></span>
                    </button>
                </div>
            </div>

            <!-- Live S3 Test Feedback Banner -->
            <template x-if="s3Result">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="s3Result.success ? 'bg-[#F8F4EE] border border-[#E8DAC8] text-[#442E15]' : 'bg-red-50 border border-red-200 text-red-900'">
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
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Active Storage Driver (FILESYSTEM_DISK)
                        </label>
                        <select name="FILESYSTEM_DISK" id="s3_disk" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="local" {{ ($settings['storage']['FILESYSTEM_DISK'] ?? '') === 'local' ? 'selected' : '' }}>local (Server Local Disk - Default)</option>
                            <option value="s3" {{ ($settings['storage']['FILESYSTEM_DISK'] ?? '') === 's3' ? 'selected' : '' }}>s3 (Amazon Web Services S3 / Cloudflare R2 / Supabase)</option>
                        </select>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Set to <code class="font-mono text-[#9F8349]">s3</code> when using an external cloud bucket.</span>
                    </div>

                    <!-- AWS Default Region -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Cloud Region (AWS_DEFAULT_REGION)
                        </label>
                        <input type="text" name="AWS_DEFAULT_REGION" id="s3_region" value="{{ $settings['storage']['AWS_DEFAULT_REGION'] ?? 'ap-south-1' }}" placeholder="ap-south-1" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block font-mono">Recommended: <span class="text-[#9F8349] font-bold">ap-south-1</span> (Mumbai, India) or <span class="text-[#9F8349]">auto</span> (Cloudflare R2).</span>
                    </div>

                    <!-- AWS Bucket Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            S3 Bucket Name (AWS_BUCKET)
                        </label>
                        <input type="text" name="AWS_BUCKET" id="s3_bucket" value="{{ $settings['storage']['AWS_BUCKET'] ?? '' }}" placeholder="lawfirm-vault-files" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">The exact name of your bucket created in AWS S3 or Cloudflare R2.</span>
                    </div>

                    <!-- AWS Custom Endpoint (For R2 / MinIO) -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Custom Endpoint (AWS_ENDPOINT) — Optional
                        </label>
                        <input type="text" name="AWS_ENDPOINT" id="s3_endpoint" value="{{ $settings['storage']['AWS_ENDPOINT'] ?? '' }}" placeholder="https://<account_id>.r2.cloudflarestorage.com" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Leave blank for standard AWS S3. Fill in for Cloudflare R2, MinIO, or Supabase Storage.</span>
                    </div>

                    <!-- AWS Access Key ID -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Access Key ID (AWS_ACCESS_KEY_ID)
                        </label>
                        <input type="text" name="AWS_ACCESS_KEY_ID" id="s3_key" value="{{ $settings['storage']['AWS_ACCESS_KEY_ID'] ?? '' }}" placeholder="AKIAIOSFODNN7EXAMPLE" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- AWS Secret Access Key -->
                    <div x-data="{ showSecret: false }">
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Secret Access Key (AWS_SECRET_ACCESS_KEY)
                            @if($settings['storage']['AWS_HAS_SECRET'])
                                <span class="text-[10px] text-[#9F8349] font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showSecret ? 'text' : 'password'" name="AWS_SECRET_ACCESS_KEY" id="s3_secret" value="{{ $settings['storage']['AWS_SECRET_ACCESS_KEY'] ?? '' }}" placeholder="{{ $settings['storage']['AWS_HAS_SECRET'] ? '••••••••' : 'Enter Secret Key' }}" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 pr-10 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                            <button type="button" @click="showSecret = !showSecret" class="absolute right-2.5 top-2.5 text-[#766A5E] hover:text-[#222222]">
                                <span class="material-symbols-outlined text-base" x-text="showSecret ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Leave as <code class="font-mono">••••••••</code> to keep existing secret unchanged.</span>
                    </div>

                    <!-- Path Style Endpoint -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Path Style Endpoint (AWS_USE_PATH_STYLE_ENDPOINT)
                        </label>
                        <select name="AWS_USE_PATH_STYLE_ENDPOINT" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="false" {{ ($settings['storage']['AWS_USE_PATH_STYLE_ENDPOINT'] ?? '') === 'false' ? 'selected' : '' }}>false (Default for standard AWS S3 / Cloudflare R2)</option>
                            <option value="true" {{ ($settings['storage']['AWS_USE_PATH_STYLE_ENDPOINT'] ?? '') === 'true' ? 'selected' : '' }}>true (Required for MinIO and self-hosted S3)</option>
                        </select>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#F4EFEA] flex items-center justify-between">
                    <span class="text-xs text-[#766A5E]">Takes effect immediately across all file uploads.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#B88B56]">save</span>
                        <span>Save Cloud Storage Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: DATABASE ENGINE -->
        <div x-show="activeTab === 'database'" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#F4EFEA] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">database</span>
                        <span>Relational Database Configuration (Postgres / MySQL / SQLite)</span>
                    </h2>
                    <p class="text-xs text-[#766A5E] mt-1">Manage database connection pool. Supports cloud providers like Supabase, AWS RDS, Neon, or local SQLite.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testDbConnection()"
                        :disabled="testingDb"
                        class="px-3.5 py-2 rounded-lg bg-[#FAF8F5] hover:bg-[#eae8e4] text-[#222222] text-xs font-semibold border border-[#E8DAC8] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingDb" class="material-symbols-outlined text-sm text-blue-700">cable</span>
                        <span x-show="testingDb" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingDb ? 'Connecting via PDO...' : 'Test DB Connection'"></span>
                    </button>

                    <button type="button" 
                        @click="runMigrations()"
                        :disabled="runningMigrations"
                        class="px-3.5 py-2 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                        <span x-show="!runningMigrations" class="material-symbols-outlined text-sm text-[#B88B56]">schema</span>
                        <span x-show="runningMigrations" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="runningMigrations ? 'Running Migrations...' : 'Run Migrations'"></span>
                    </button>

                    <button type="button" 
                        @click="seedDatabase()"
                        :disabled="seedingDb"
                        class="px-3.5 py-2 rounded-lg bg-[#F8F4EE] hover:bg-[#F0EAE0] text-[#9F8349] border border-[#E8DAC8] text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                        <span x-show="!seedingDb" class="material-symbols-outlined text-sm text-[#9F8349]">group_add</span>
                        <span x-show="seedingDb" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="seedingDb ? 'Seeding Practice Data...' : 'Seed Practice Data &amp; Users'"></span>
                    </button>

                    <button type="button" 
                        @click="showCleanModal = true; cleanConfirmPhrase = ''; cleanResult = null;"
                        class="px-3.5 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-semibold flex items-center gap-1.5 transition-all shadow-xs">
                        <span class="material-symbols-outlined text-sm text-red-600">delete_sweep</span>
                        <span>Wipe Sample Data (Fresh Slate)</span>
                    </button>
                </div>
            </div>

            <!-- Caution Advisory -->
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
                <span class="material-symbols-outlined text-base text-amber-700 shrink-0 mt-0.5">warning</span>
                <div class="leading-relaxed">
                    <strong class="font-semibold text-amber-950">Important Database Safety Notice:</strong>
                    Always click <strong class="font-semibold">"Test DB Connection"</strong> before saving new database credentials. If you point to a fresh empty database (e.g. on Supabase or AWS RDS), click <strong class="font-semibold">"Run Migrations"</strong> and then <strong class="font-semibold">"Seed Practice Data &amp; Users"</strong> to provision all law firm tables and user profiles.
                </div>
            </div>

            <!-- Live DB Test Feedback Banner -->
            <template x-if="dbResult">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="dbResult.success ? 'bg-[#F8F4EE] border border-[#E8DAC8] text-[#442E15]' : 'bg-red-50 border border-red-200 text-red-900'">
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
                    :class="migrationResult.success ? 'bg-[#F8F4EE] border-[#E8DAC8] text-[#442E15]' : 'bg-red-50 border-red-200 text-red-950'">
                    <div class="flex items-center justify-between mb-2">
                        <strong class="font-semibold" x-text="migrationResult.message"></strong>
                        <button type="button" @click="migrationResult = null" class="text-xs text-[#766A5E] hover:underline">Dismiss</button>
                    </div>
                    <pre class="bg-white/80 p-3 rounded-lg font-mono text-[11px] overflow-x-auto max-h-48 border border-[#E8DAC8]/50" x-text="migrationResult.output"></pre>
                </div>
            </template>

            <!-- Supabase on AWS EC2 IPv4 Advisory -->
            <div class="mb-6 p-4 rounded-xl bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#222222] flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-[#9F8349] text-lg shrink-0 mt-0.5">info</span>
                <div class="space-y-1">
                    <strong class="font-bold text-[#9F8349] block">Connecting to Supabase PostgreSQL from AWS EC2?</strong>
                    <p class="text-[#554D45] leading-relaxed">
                        Supabase direct connection hosts (<code class="font-mono text-[#9F8349]">db.[ref].supabase.co</code>) resolve to <strong>IPv6-only</strong> addresses, which standard AWS EC2 instances cannot route to directly, causing <code class="font-mono text-red-700">Network is unreachable</code>.
                    </p>
                    <p class="text-[#554D45] leading-relaxed font-medium">
                        Always use the <strong>Supabase Connection Pooler (IPv4)</strong> in your configuration:
                    </p>
                    <ul class="list-disc list-inside text-[11px] font-mono text-[#766A5E] space-y-1 mt-1 bg-white p-2.5 rounded-lg border border-[#EFECE6]">
                        <li>Host: <span class="text-[#9F8349] font-bold">aws-0-[region].pooler.supabase.com</span> (e.g. <span class="text-[#9F8349]">aws-0-ap-northeast-1.pooler.supabase.com</span>)</li>
                        <li>Port: <span class="text-emerald-700 font-bold">6543</span> <span class="text-xs font-sans text-emerald-800 font-semibold">(Transaction Mode — Super Fast &amp; High Scalability with Emulated Prepares)</span> or <span class="text-[#9F8349] font-bold">5432</span> (Session Mode)</li>
                        <li>Username: <span class="text-[#9F8349] font-bold">postgres.[project-ref]</span> (Must include <span class="text-[#9F8349]">.[project-ref]</span>)</li>
                        <li>Database: <span class="text-[#9F8349] font-bold">postgres</span></li>
                    </ul>
                    <div class="p-2 bg-emerald-50 border border-emerald-200 rounded text-[11px] text-emerald-900 mt-2">
                        <strong>Performance Optimization Note:</strong> Port <code class="font-bold">6543</code> (Transaction Pooler) connects to warm PgBouncer pools on Supabase without spawning heavy backend processes. Combined with local file sessions (<code class="font-mono">SESSION_DRIVER=file</code>), this eliminates network roundtrips and makes Supabase lightning fast!
                    </div>
                </div>
            </div>


            <!-- Quick 1-Click Database Presets -->
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3 p-3.5 bg-[#FAF8F5] rounded-xl border border-[#EAE4DC]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-[#9F8349]">auto_fix_high</span>
                    <span class="text-xs text-[#222222] font-semibold">1-Click Configuration Presets:</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="applyPreset('supabase-tokyo')" class="px-3 py-1.5 rounded-lg text-xs bg-[#9F8349] hover:bg-[#856C36] text-white border border-[#856C36] font-semibold flex items-center gap-1.5 shadow-xs transition-colors">
                        <span class="material-symbols-outlined text-sm text-[#E8DAC8]">verified</span>
                        <span>Supabase Pooler (Tokyo — Your Project)</span>
                    </button>
                    <button type="button" @click="applyPreset('sqlite')" class="px-3 py-1.5 rounded-lg text-xs bg-white hover:bg-[#FAF8F5] text-[#554D45] border border-[#EAE4DC] font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="material-symbols-outlined text-sm text-[#766A5E]">save_as</span>
                        <span>Local SQLite (Safe Failsafe)</span>
                    </button>
                    <button type="button" @click="applyPreset('supabase-mumbai')" class="px-2.5 py-1.5 rounded-lg text-xs bg-white hover:bg-[#F8F4EE] text-[#766A5E] border border-[#E8DAC8] font-medium flex items-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-sm text-[#B88B56]">cloud_sync</span>
                        <span>Supabase (Mumbai)</span>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="dbForm">
                @csrf
                <input type="hidden" name="_form_section" value="database"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Database Driver -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Connection Driver (DB_CONNECTION)
                        </label>
                        <select name="DB_CONNECTION" id="db_driver" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="sqlite" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'sqlite' ? 'selected' : '' }}>sqlite (SQLite File — Zero Config / Local)</option>
                            <option value="pgsql" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'pgsql' ? 'selected' : '' }}>pgsql (PostgreSQL — Recommended for Supabase / Neon / AWS RDS)</option>
                            <option value="mysql" {{ ($settings['database']['DB_CONNECTION'] ?? '') === 'mysql' ? 'selected' : '' }}>mysql (MySQL / MariaDB / Amazon Aurora)</option>
                        </select>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Choose <code class="font-mono text-[#9F8349]">pgsql</code> for Supabase.</span>
                    </div>

                    <!-- Database Host -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Host (DB_HOST)
                        </label>
                        <input type="text" name="DB_HOST" id="db_host" value="{{ $settings['database']['DB_HOST'] ?? '127.0.0.1' }}" placeholder="aws-0-ap-south-1.pooler.supabase.com" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Ignored when using SQLite. For Supabase, use your pooler or direct host.</span>
                    </div>

                    <!-- Database Port -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Port (DB_PORT)
                        </label>
                        <input type="text" name="DB_PORT" id="db_port" value="{{ $settings['database']['DB_PORT'] ?? '3306' }}" placeholder="5432" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block font-mono">Standard: <span class="font-bold">5432</span> (PostgreSQL/Supabase) or <span class="font-bold">3306</span> (MySQL).</span>
                    </div>

                    <!-- Database Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Name / Path (DB_DATABASE)
                        </label>
                        <input type="text" name="DB_DATABASE" id="db_database" value="{{ $settings['database']['DB_DATABASE'] ?? '' }}" placeholder="postgres or database/database.sqlite" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">For Supabase, usually <code class="font-mono">postgres</code>. For SQLite, the path to <code class="font-mono">.sqlite</code> file.</span>
                    </div>

                    <!-- Database Username -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Username (DB_USERNAME)
                        </label>
                        <input type="text" name="DB_USERNAME" id="db_username" value="{{ $settings['database']['DB_USERNAME'] ?? '' }}" placeholder="postgres.yourprojectid" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- Database Password -->
                    <div x-data="{ showDbPass: false }">
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Database Password (DB_PASSWORD)
                            @if($settings['database']['DB_HAS_PASSWORD'])
                                <span class="text-[10px] text-[#9F8349] font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showDbPass ? 'text' : 'password'" name="DB_PASSWORD" id="db_password" value="{{ $settings['database']['DB_PASSWORD'] ?? '' }}" placeholder="{{ $settings['database']['DB_HAS_PASSWORD'] ? '••••••••' : 'Enter DB Password' }}" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 pr-10 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                            <button type="button" @click="showDbPass = !showDbPass" class="absolute right-2.5 top-2.5 text-[#766A5E] hover:text-[#222222]">
                                <span class="material-symbols-outlined text-base" x-text="showDbPass ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Leave as <code class="font-mono">••••••••</code> to keep existing password unchanged.</span>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#F4EFEA] flex items-center justify-between">
                    <span class="text-xs text-[#766A5E]">Laravel cache will be cleared immediately upon saving.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#B88B56]">save</span>
                        <span>Save Database Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 3: EMAIL & SMTP RELAY -->
        <div x-show="activeTab === 'mail'" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#F4EFEA] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">mail</span>
                        <span>Outbound SMTP Email Gateway (Brevo / SES / Mailgun)</span>
                    </h2>
                    <p class="text-xs text-[#766A5E] mt-1">Configure transactional email delivery for client invitation links, consultation confirmations, hearing alerts, and document requests.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="testMailConnection()"
                        :disabled="testingMail"
                        class="px-3.5 py-2 rounded-lg bg-[#FAF8F5] hover:bg-[#eae8e4] text-[#222222] text-xs font-semibold border border-[#E8DAC8] flex items-center gap-1.5 transition-all">
                        <span x-show="!testingMail" class="material-symbols-outlined text-sm text-purple-700">forward_to_inbox</span>
                        <span x-show="testingMail" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                        <span x-text="testingMail ? 'Testing SMTP Port...' : 'Test SMTP Connection'"></span>
                    </button>
                </div>
            </div>

            <!-- Live Mail Test Feedback Banner -->
            <template x-if="mailResult">
                <div class="mb-6 p-4 rounded-xl text-xs flex items-start gap-2.5" 
                    :class="mailResult.success ? 'bg-[#F8F4EE] border border-[#E8DAC8] text-[#442E15]' : 'bg-red-50 border border-red-200 text-red-900'">
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
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Mail Delivery Engine (MAIL_MAILER)
                        </label>
                        <select name="MAIL_MAILER" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="smtp" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'smtp' ? 'selected' : '' }}>smtp (Live SMTP Relay — Recommended)</option>
                            <option value="log" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'log' ? 'selected' : '' }}>log (Log to storage/logs/laravel.log — Local Testing)</option>
                            <option value="ses" {{ ($settings['mail']['MAIL_MAILER'] ?? '') === 'ses' ? 'selected' : '' }}>ses (Amazon Simple Email Service)</option>
                        </select>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Set to <code class="font-mono text-[#9F8349]">smtp</code> for Brevo (Sendinblue), Mailgun, or Postmark.</span>
                    </div>

                    <!-- SMTP Host -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            SMTP Server Host (MAIL_HOST)
                        </label>
                        <input type="text" name="MAIL_HOST" id="mail_host" value="{{ $settings['mail']['MAIL_HOST'] ?? 'smtp.brevo.com' }}" placeholder="smtp-relay.brevo.com" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- SMTP Port -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            SMTP Port (MAIL_PORT)
                        </label>
                        <input type="text" name="MAIL_PORT" id="mail_port" value="{{ $settings['mail']['MAIL_PORT'] ?? '587' }}" placeholder="587" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block font-mono">Standard ports: <span class="font-bold">587</span> (TLS), <span class="font-bold">465</span> (SSL), or <span class="font-bold">2525</span>.</span>
                    </div>

                    <!-- SMTP Encryption -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Encryption Security (MAIL_ENCRYPTION)
                        </label>
                        <select name="MAIL_ENCRYPTION" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="tls" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'tls' ? 'selected' : '' }}>tls (Transport Layer Security — Recommended for port 587)</option>
                            <option value="ssl" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'ssl' ? 'selected' : '' }}>ssl (Secure Sockets Layer — Recommended for port 465)</option>
                            <option value="null" {{ ($settings['mail']['MAIL_ENCRYPTION'] ?? '') === 'null' ? 'selected' : '' }}>none (Unencrypted)</option>
                        </select>
                    </div>

                    <!-- SMTP Username -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            SMTP Username (MAIL_USERNAME)
                        </label>
                        <input type="text" name="MAIL_USERNAME" value="{{ $settings['mail']['MAIL_USERNAME'] ?? '' }}" placeholder="7b3d9... or api-key-login" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- SMTP Password -->
                    <div x-data="{ showMailPass: false }">
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            SMTP Password / API Key (MAIL_PASSWORD)
                            @if($settings['mail']['MAIL_HAS_PASSWORD'])
                                <span class="text-[10px] text-[#9F8349] font-mono font-normal">(Configured)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input :type="showMailPass ? 'text' : 'password'" name="MAIL_PASSWORD" value="{{ $settings['mail']['MAIL_PASSWORD'] ?? '' }}" placeholder="{{ $settings['mail']['MAIL_HAS_PASSWORD'] ? '••••••••' : 'Enter SMTP Key' }}" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 pr-10 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                            <button type="button" @click="showMailPass = !showMailPass" class="absolute right-2.5 top-2.5 text-[#766A5E] hover:text-[#222222]">
                                <span class="material-symbols-outlined text-base" x-text="showMailPass ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- From Email Address -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Outgoing Sender Address (MAIL_FROM_ADDRESS)
                        </label>
                        <input type="email" name="MAIL_FROM_ADDRESS" value="{{ $settings['mail']['MAIL_FROM_ADDRESS'] ?? 'chambers@sharmalegal.in' }}" placeholder="notifications@sharmalegal.in" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Must match the verified sending domain configured in your SMTP provider.</span>
                    </div>

                    <!-- From Sender Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Outgoing Sender Display Name (MAIL_FROM_NAME)
                        </label>
                        <input type="text" name="MAIL_FROM_NAME" value="{{ $settings['mail']['MAIL_FROM_NAME'] ?? 'Sharma & Associates, Advocates' }}" placeholder="Sharma & Associates, Advocates" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#F4EFEA] flex items-center justify-between">
                    <span class="text-xs text-[#766A5E]">Applies to all client email notifications and case dispatches.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#B88B56]">save</span>
                        <span>Save Email Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 4: GENERAL APP & INDIAN LOCALIZATION -->
        <div x-show="activeTab === 'app'" class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8">
            <div class="flex items-start justify-between border-b border-[#F4EFEA] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-serif font-bold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#9F8349]">settings_applications</span>
                        <span>Application Identity &amp; Indian Legal Localization</span>
                    </h2>
                    <p class="text-xs text-[#766A5E] mt-1">Platform branding, Indian Rupee currency standards (₹ INR), timezones, and environment status.</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.environment.update') }}" method="POST" id="appForm">
                @csrf
                <input type="hidden" name="_form_section" value="app"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- APP_NAME -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            System Application Name (APP_NAME)
                        </label>
                        <input type="text" name="APP_NAME" value="{{ $settings['app']['APP_NAME'] ?? 'Vennamraj Associates' }}" placeholder="Vennamraj Associates" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- LEGAL_APP_NAME -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Client-Facing Chambers Title (LEGAL_APP_NAME)
                        </label>
                        <input type="text" name="LEGAL_APP_NAME" value="{{ $settings['app']['LEGAL_APP_NAME'] ?? 'Vennamraj Associates' }}" placeholder="Vennamraj Associates, Advocates &amp; Legal Consultants" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Displayed in sidebar, client portal headers, and legal document watermarks.</span>
                    </div>

                    <!-- APP_URL -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Root Application URL (APP_URL)
                        </label>
                        <input type="url" name="APP_URL" value="{{ $settings['app']['APP_URL'] ?? 'http://localhost:8000' }}" placeholder="https://chambers.sharmalegal.in" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                        <span class="text-[11px] text-[#766A5E] mt-1 block">Used to generate password reset links and client portal access invites.</span>
                    </div>

                    <!-- APP_ENV -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Deployment Environment (APP_ENV)
                        </label>
                        <select name="APP_ENV" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]">
                            <option value="local" {{ ($settings['app']['APP_ENV'] ?? '') === 'local' ? 'selected' : '' }}>local (Development Mode)</option>
                            <option value="production" {{ ($settings['app']['APP_ENV'] ?? '') === 'production' ? 'selected' : '' }}>production (Live Production Server)</option>
                            <option value="staging" {{ ($settings['app']['APP_ENV'] ?? '') === 'staging' ? 'selected' : '' }}>staging (Staging / Pre-Release)</option>
                        </select>
                    </div>

                    <!-- Currency Symbol -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            Default Currency Symbol (LEGAL_CURRENCY_SYMBOL)
                        </label>
                        <input type="text" name="LEGAL_CURRENCY_SYMBOL" value="{{ $settings['app']['LEGAL_CURRENCY_SYMBOL'] ?? '₹' }}" placeholder="₹" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-bold text-[#9F8349] focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- Currency Code -->
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1.5">
                            ISO Currency Code (LEGAL_CURRENCY_CODE)
                        </label>
                        <input type="text" name="LEGAL_CURRENCY_CODE" value="{{ $settings['app']['LEGAL_CURRENCY_CODE'] ?? 'INR' }}" placeholder="INR" class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] focus:bg-white font-mono focus:ring-1 focus:ring-[#9F8349] focus:border-[#9F8349]"/>
                    </div>

                    <!-- Debug Mode -->
                    <div class="md:col-span-2 p-4 rounded-xl bg-[#FAF8F5] border border-[#EFECE6] flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-[#222222] block">Debug &amp; Verbose Diagnostics (APP_DEBUG)</span>
                            <span class="text-[11px] text-[#766A5E] block mt-0.5">When enabled, detailed stack traces are displayed on server errors. Turn OFF in production.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="APP_DEBUG" value="true" class="sr-only peer" {{ ($settings['app']['APP_DEBUG'] ?? '') === 'true' ? 'checked' : '' }}/>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#9F8349]"></div>
                        </label>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-[#F4EFEA] flex items-center justify-between">
                    <span class="text-xs text-[#766A5E]">Instant cache invalidation runs automatically upon saving.</span>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#9F8349] hover:bg-[#856C36] text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#B88B56]">save</span>
                        <span>Save Application Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 5: BACKUPS & RAW .ENV EDITOR -->
        <div x-show="activeTab === 'backups'" class="space-y-6">
            
            <!-- Automated Backups List -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8">
                <div class="border-b border-[#F4EFEA] pb-5 mb-6">
                    <h2 class="text-lg font-serif font-bold text-[#222222] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#554D45]">history</span>
                        <span>Automated .env Configuration Snapshots</span>
                    </h2>
                    <p class="text-xs text-[#766A5E] mt-1">A safety backup is generated every time configuration is modified. You can revert to any previous state with 1-click restore.</p>
                </div>

                @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[11px] font-mono text-[#766A5E] uppercase tracking-wider">
                                <th class="py-3 px-4">Backup Snapshot File</th>
                                <th class="py-3 px-4">Created Timestamp</th>
                                <th class="py-3 px-4 text-center">File Size</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4EFEA] text-xs">
                            @foreach($backups as $backup)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="py-3 px-4 font-mono font-medium text-[#222222]">
                                    {{ $backup['filename'] }}
                                </td>
                                <td class="py-3 px-4 text-[#766A5E]">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-[#766A5E]">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" @click="confirmRestore('{{ $backup['filename'] }}')" class="px-2.5 py-1 rounded bg-[#FAF8F5] hover:bg-amber-100 text-amber-900 border border-amber-300 font-semibold transition-all inline-flex items-center gap-1 shadow-sm">
                                        <span class="material-symbols-outlined text-sm text-[#9F8349]">settings_backup_restore</span>
                                        <span>Restore Snapshot</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-xs text-[#766A5E] bg-[#FAF8F5] rounded-xl border border-dashed border-[#E8DAC8]">
                    <span class="material-symbols-outlined text-3xl text-[#EAE4DC] block mb-2">folder_off</span>
                    <span>No automated backups created yet. The first backup will be created automatically upon your first update.</span>
                </div>
                @endif
            </div>

            <!-- Advanced Raw .env Editor -->
            <div class="bg-white rounded-xl border border-[#EFECE6] shadow-sm p-6 md:p-8" x-data="{ showRaw: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-serif font-bold text-[#222222] flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-700">code</span>
                            <span>Advanced Direct .env Code Editor</span>
                        </h3>
                        <p class="text-xs text-[#766A5E] mt-0.5">Caution: Direct raw editing overrides automated validations. Use only when adding custom third-party environment flags.</p>
                    </div>
                    <button type="button" @click="showRaw = !showRaw" class="px-3 py-1.5 rounded-lg bg-[#FAF8F5] text-xs font-semibold text-[#222222] border border-[#E8DAC8] hover:bg-[#eae8e4]">
                        <span x-text="showRaw ? 'Hide Raw Editor' : 'Show Raw Editor'"></span>
                    </button>
                </div>

                <div x-show="showRaw" class="mt-6 pt-6 border-t border-[#F4EFEA]" x-transition>
                    <form action="{{ route('admin.settings.environment.update') }}" method="POST" onsubmit="return confirm('Save raw .env content? Ensure syntax is correct.');">
                        @csrf
                        <div class="mb-4">
                            <textarea name="raw_env_content" rows="18" class="w-full bg-[#222222] text-[#8C7F72] font-mono text-xs p-4 rounded-xl border border-[#3E352E] focus:ring-1 focus:ring-[#9F8349] leading-relaxed">{{ $rawEnv }}</textarea>
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

        <!-- Restore Snapshot Confirmation Modal -->
        <div x-show="showRestoreModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="if(!restoringBackup) showRestoreModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-[#E8DAC8]" @click.away="if(!restoringBackup) showRestoreModal = false">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl text-[#9F8349]">settings_backup_restore</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-serif font-bold text-[#222222]">Restore Environment Snapshot</h3>
                            <p class="text-xs text-[#766A5E] mt-1">
                                Are you sure you want to revert your active <code class="font-mono bg-[#FAF8F5] px-1.5 py-0.5 rounded border border-[#E8DAC8] text-[#222222]">.env</code> to:
                            </p>
                            <div class="mt-2.5 p-2.5 rounded-lg bg-[#FAF8F5] border border-[#E8DAC8] font-mono text-xs font-semibold text-[#222222] flex items-center justify-between">
                                <span x-text="restoreTargetFile"></span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300 font-sans font-medium">Snapshot</span>
                            </div>
                            <div class="mt-3 p-3 rounded-lg bg-amber-50/70 border border-amber-200/80 text-[11px] text-amber-900 leading-relaxed">
                                <strong class="font-semibold">Safety Assurance:</strong> An automated pre-restore backup of your current <code class="font-mono">.env</code> will be created instantly before restoring, and application configuration caches will be reloaded.
                            </div>

                            <!-- Inline Feedback Message -->
                            <template x-if="restoreFeedback">
                                <div class="mt-3 p-3 rounded-lg text-xs font-medium" :class="restoreFeedback.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base" x-text="restoreFeedback.success ? 'check_circle' : 'error'"></span>
                                        <span x-text="restoreFeedback.message"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-[#F4EFEA]">
                        <button type="button" @click="showRestoreModal = false" :disabled="restoringBackup" class="px-4 py-2 rounded-lg text-xs font-medium text-[#766A5E] hover:bg-[#FAF8F5] border border-[#E8DAC8] transition-all">
                            Cancel
                        </button>
                        <button type="button" @click="executeRestore()" :disabled="restoringBackup" class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#8A713E] shadow-sm flex items-center gap-2 transition-all">
                            <span x-show="restoringBackup" class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span class="material-symbols-outlined text-sm" x-show="!restoringBackup">check</span>
                            <span x-text="restoringBackup ? 'Restoring Snapshot...' : 'Confirm Restore'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clean Slate / Sample Data Wipe Modal -->
        <div x-show="showCleanModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="if(!cleaningData) showCleanModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-[#EAE4DC]">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                                <span class="material-symbols-outlined text-xl">delete_sweep</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#222222]">Wipe Sample &amp; Seeded Practice Data</h3>
                                <p class="text-xs text-[#766A5E]">Start fresh with a clean chambers slate</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-950 space-y-2 mb-4 leading-relaxed">
                            <p class="font-semibold text-red-950">This action will permanently purge:</p>
                            <ul class="list-disc list-inside space-y-1 text-red-900 font-mono text-[11px]">
                                <li>All sample client profiles &amp; portal logins</li>
                                <li>All matters, case dossiers, and court filings</li>
                                <li>All physical document files from storage</li>
                                <li>All billing invoices, time logs, tasks, and hearings</li>
                            </ul>
                            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded text-emerald-900 text-[11px] font-medium mt-2">
                                <strong>Protected:</strong> Your Law Firm tenant structure, subscription tier, and advocate/admin staff login credentials are completely preserved so you can immediately begin live casework.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-[#222222] mb-1">
                                Type <span class="font-mono text-red-600 font-bold">RESET</span> to confirm:
                            </label>
                            <input type="text" x-model="cleanConfirmPhrase" placeholder="RESET" 
                                class="w-full text-xs rounded-lg border border-[#EAE4DC] p-2.5 bg-[#FAF8F5] font-mono focus:bg-white focus:ring-1 focus:ring-red-500 focus:border-red-500 uppercase"/>
                        </div>

                        <template x-if="cleanResult">
                            <div class="mb-4 p-3 rounded-lg text-xs" :class="cleanResult.success ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-red-50 text-red-900 border border-red-200'">
                                <span x-text="cleanResult.message"></span>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EAE4DC]">
                            <button type="button" @click="showCleanModal = false" :disabled="cleaningData" 
                                class="px-4 py-2 text-xs font-semibold rounded-lg bg-[#FAF8F5] text-[#554D45] hover:bg-[#F4EFEA] border border-[#EAE4DC] transition-colors">
                                Cancel
                            </button>
                            <button type="button" @click="executeCleanData()" :disabled="cleaningData || cleanConfirmPhrase.trim().toUpperCase() !== 'RESET'" 
                                class="px-4 py-2 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5 shadow-sm transition-all">
                                <span x-show="!cleaningData" class="material-symbols-outlined text-sm">delete_forever</span>
                                <span x-show="cleaningData" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                                <span x-text="cleaningData ? 'Purging Sample Data...' : 'Confirm Purge (Clean Slate)'"></span>
                            </button>
                        </div>
                    </div>
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

            fetch('/admin/settings/environment/test-db', {
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

            fetch('/admin/settings/environment/test-s3', {
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

            fetch('/admin/settings/environment/test-mail', {
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

            fetch('/admin/settings/environment/run-migrations', {
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

        function seedDatabase() {
            if (!confirm('Seed chambers practice data now? This will insert default legal matters, client files, and user profiles (Rajesh Sharma, Priya Nair, Platform Super Admin) into the active database.')) {
                return;
            }

            const alpine = Alpine.$data(document.body);
            alpine.seedingDb = true;
            alpine.migrationResult = null;

            fetch('/admin/settings/environment/seed-db', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                alpine.seedingDb = false;
                alpine.migrationResult = data;
            })
            .catch(err => {
                alpine.seedingDb = false;
                alpine.migrationResult = { success: false, message: 'Database seeding error', output: err.message };
            });
        }

        function applyPreset(type) {
            if (type === 'supabase-tokyo' || type === 'supabase') {
                document.getElementById('db_driver').value = 'pgsql';
                document.getElementById('db_host').value = 'aws-0-ap-northeast-1.pooler.supabase.com';
                document.getElementById('db_port').value = '5432';
                document.getElementById('db_database').value = 'postgres';
                document.getElementById('db_username').value = 'postgres.vfeqqwdewvqpjieqktml';
                alert('Supabase Tokyo Pooler (ap-northeast-1) preset applied!\n\nHost: aws-0-ap-northeast-1.pooler.supabase.com\nUsername: postgres.vfeqqwdewvqpjieqktml\n\nEnter your Supabase database password, then click "Test DB Connection".');
            } else if (type === 'supabase-mumbai') {
                document.getElementById('db_driver').value = 'pgsql';
                document.getElementById('db_host').value = 'aws-0-ap-south-1.pooler.supabase.com';
                document.getElementById('db_port').value = '5432';
                document.getElementById('db_database').value = 'postgres';
                document.getElementById('db_username').value = 'postgres.vfeqqwdewvqpjieqktml';
                alert('Supabase Mumbai Pooler (ap-south-1) preset applied! Note: Project vfeqqwdewvqpjieqktml is hosted in Tokyo.');
            } else if (type === 'sqlite') {
                document.getElementById('db_driver').value = 'sqlite';
                document.getElementById('db_host').value = '127.0.0.1';
                document.getElementById('db_port').value = '3306';
                document.getElementById('db_database').value = 'database/database.sqlite';
                document.getElementById('db_username').value = '';
                document.getElementById('db_password').value = '';
                alert('Local SQLite (Safe Failsafe) preset applied! Click "Test DB Connection" or "Save Database Settings".');
            }
        }

        function confirmRestore(filename) {
            const alpine = Alpine.$data(document.body);
            alpine.restoreTargetFile = filename;
            alpine.restoreFeedback = null;
            alpine.restoringBackup = false;
            alpine.showRestoreModal = true;
        }

        function executeRestore() {
            const alpine = Alpine.$data(document.body);
            if (!alpine.restoreTargetFile) return;

            alpine.restoringBackup = true;
            alpine.restoreFeedback = null;

            fetch('{{ route('admin.settings.environment.restore-backup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ filename: alpine.restoreTargetFile })
            })
            .then(res => res.json())
            .then(data => {
                alpine.restoringBackup = false;
                alpine.restoreFeedback = data;
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = '{{ route('admin.settings.environment') }}?tab=backups';
                    }, 800);
                }
            })
            .catch(err => {
                alpine.restoringBackup = false;
                alpine.restoreFeedback = { success: false, message: 'Restore request error: ' + err.message };
            });
        }

        function executeCleanData() {
            const alpine = Alpine.$data(document.body);
            alpine.cleaningData = true;
            alpine.cleanResult = null;

            fetch('{{ route('admin.settings.environment.clean-data') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ confirm_phrase: alpine.cleanConfirmPhrase })
            })
            .then(res => res.json())
            .then(data => {
                alpine.cleaningData = false;
                alpine.cleanResult = data;
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = '{{ route('admin.settings.environment') }}?tab=database';
                    }, 1200);
                }
            })
            .catch(err => {
                alpine.cleaningData = false;
                alpine.cleanResult = { success: false, message: 'Purge error: ' + err.message };
            });
        }
    </script>
</body>
</html>
