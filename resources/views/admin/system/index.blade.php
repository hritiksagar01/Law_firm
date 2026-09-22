@extends('layouts.admin')

@section('title', 'System Settings — Platform Console')

@section('content')
<div class="py-4" x-data="{ 
    activeTab: 'branding',
    s3Testing: false,
    s3Result: null,
    s3Success: false,
    showSecret: false,
    logoPreview: null,
    storageDriver: '{{ old('storage_driver', $storage['storage_driver'] ?? 'local') }}',
    testS3Connection() {
        this.s3Testing = true;
        this.s3Result = null;
        
        const form = document.getElementById('system-settings-form');
        const formData = new FormData(form);
        
        fetch('{{ route('admin.system.test-s3') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            this.s3Testing = false;
            this.s3Success = !!data.success;
            this.s3Result = data.message + (data.latency_ms ? ' (' + data.latency_ms + 'ms)' : '');
        })
        .catch(err => {
            this.s3Testing = false;
            this.s3Success = false;
            this.s3Result = 'Error executing connection test: ' + err.message;
        });
    },
    handleLogoSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.logoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[26px] sm:text-[30px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">System settings</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $storage['storage_driver'] === 's3' ? 'bg-[#ecfdf5] text-[#065f46] border border-[#a7f3d0]' : 'bg-[#f3f4f6] text-[#374151] border border-[#e5e7eb]' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $storage['storage_driver'] === 's3' ? 'bg-[#059669]' : 'bg-[#6b7280]' }}"></span>
                    {{ $storage['storage_driver'] === 's3' ? 'S3 Storage Active' : 'Local Disk Active' }}
                </span>
            </div>
            <p class="text-[13px] text-[#646864] mt-1">Platform-wide values — branding, Amazon S3 / R2 object storage, public legal content, and governance parameters</p>
        </div>

        <!-- Quick Public Pages Dropdown / Links -->
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11.5px] text-[#6b7280] hidden md:inline">Public CMS Previews:</span>
            <a href="{{ route('public.about') }}" target="_blank" class="px-2.5 py-1.5 rounded bg-white border border-[#e5e7eb] hover:border-[#093225] hover:text-[#093225] text-[#374151] text-[12px] font-medium transition-colors shadow-2xs flex items-center gap-1">
                <span>About</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
            <a href="{{ route('public.contact') }}" target="_blank" class="px-2.5 py-1.5 rounded bg-white border border-[#e5e7eb] hover:border-[#093225] hover:text-[#093225] text-[#374151] text-[12px] font-medium transition-colors shadow-2xs flex items-center gap-1">
                <span>Contact</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
            <a href="{{ route('public.privacy') }}" target="_blank" class="px-2.5 py-1.5 rounded bg-white border border-[#e5e7eb] hover:border-[#093225] hover:text-[#093225] text-[#374151] text-[12px] font-medium transition-colors shadow-2xs flex items-center gap-1">
                <span>Privacy</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
            <a href="{{ route('public.terms') }}" target="_blank" class="px-2.5 py-1.5 rounded bg-white border border-[#e5e7eb] hover:border-[#093225] hover:text-[#093225] text-[#374151] text-[12px] font-medium transition-colors shadow-2xs flex items-center gap-1">
                <span>Terms</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-[#ecfdf5] border border-[#a7f3d0] rounded-md text-[13px] text-[#065f46] flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 px-4 py-3 bg-[#fef2f2] border border-[#fecaca] rounded-md text-[13px] text-[#991b1b]">
        <div class="font-medium mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-lg">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc pl-5 space-y-1 text-[12.5px]">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex items-center border-b border-[#e5e7eb] mb-6 gap-2 overflow-x-auto">
        <button type="button" 
                @click="activeTab = 'branding'" 
                :class="activeTab === 'branding' ? 'border-[#093225] text-[#093225] font-semibold' : 'border-transparent text-[#6b7280] hover:text-[#111827]'" 
                class="px-4 py-2.5 text-[13.5px] border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">palette</span>
            <span>Brand &amp; Platform Logo</span>
        </button>
        <button type="button" 
                @click="activeTab = 'storage'" 
                :class="activeTab === 'storage' ? 'border-[#093225] text-[#093225] font-semibold' : 'border-transparent text-[#6b7280] hover:text-[#111827]'" 
                class="px-4 py-2.5 text-[13.5px] border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">cloud_sync</span>
            <span>Amazon S3 / Cloud Storage</span>
        </button>
        <button type="button" 
                @click="activeTab = 'content'" 
                :class="activeTab === 'content' ? 'border-[#093225] text-[#093225] font-semibold' : 'border-transparent text-[#6b7280] hover:text-[#111827]'" 
                class="px-4 py-2.5 text-[13.5px] border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">article</span>
            <span>Public Content &amp; Footer CMS</span>
        </button>
        <button type="button" 
                @click="activeTab = 'general'" 
                :class="activeTab === 'general' ? 'border-[#093225] text-[#093225] font-semibold' : 'border-transparent text-[#6b7280] hover:text-[#111827]'" 
                class="px-4 py-2.5 text-[13.5px] border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
            <span>General &amp; Governance</span>
        </button>
    </div>

    <!-- Main Form -->
    <form id="system-settings-form" action="{{ route('admin.system.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- TAB 1: Brand & Platform Logo -->
        <div x-show="activeTab === 'branding'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: Branding Settings (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 border-b border-[#f3f4f6] pb-3">
                        <div>
                            <h2 class="text-[15px] font-semibold text-[#1a1a1a]">General Platform Brand &amp; Identity</h2>
                            <p class="text-[12px] text-[#6b7280] mt-0.5">Customize the platform name, logo mark, and legal practice title</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Platform Name -->
                        <div>
                            <label for="platform_name" class="block text-[13px] font-medium text-[#374151] mb-1.5">Platform name</label>
                            <input type="text" 
                                   id="platform_name" 
                                   name="platform_name" 
                                   value="{{ old('platform_name', $settings['platform_name']) }}" 
                                   required
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                            <p class="text-[12px] text-[#6b7280] mt-1.5">Shown across the Super Admin portal, Practice console header, and official platform notifications.</p>
                        </div>

                        <!-- Support Email -->
                        <div>
                            <label for="support_email" class="block text-[13px] font-medium text-[#374151] mb-1.5">Support email</label>
                            <input type="email" 
                                   id="support_email" 
                                   name="support_email" 
                                   value="{{ old('support_email', $settings['support_email']) }}" 
                                   required
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                            <p class="text-[12px] text-[#6b7280] mt-1.5">Used for administrative communications, support inquiries, and system notifications.</p>
                        </div>

                        <!-- Logo Upload Section -->
                        <div class="pt-2 border-t border-[#f3f4f6]">
                            <label class="block text-[13px] font-medium text-[#374151] mb-1.5">Platform Logo Image</label>
                            <p class="text-[12px] text-[#6b7280] mb-3">Upload a high-resolution PNG, SVG, JPG, or WebP (transparent background recommended, max 4MB). This logo updates the Super Admin sidebar, Lawyer workspace, Client Portal, and login screens.</p>
                            
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 px-4 py-2 bg-[#f9fafb] hover:bg-[#f3f4f6] text-[#374151] border border-[#d1d5db] rounded-md text-[13px] font-medium transition-colors cursor-pointer shadow-2xs">
                                    <span class="material-symbols-outlined text-[18px] text-[#093225]">upload_file</span>
                                    <span>Choose New Logo</span>
                                    <input type="file" 
                                           id="logo" 
                                           name="logo" 
                                           accept="image/png,image/jpeg,image/svg+xml,image/webp" 
                                           class="hidden" 
                                           @change="handleLogoSelect($event)">
                                </label>

                                @if($settings['platform_logo'])
                                <button type="submit" 
                                        formaction="{{ route('admin.system.reset-logo') }}" 
                                        formmethod="POST" 
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-[#fef2f2] text-[#b91c1c] border border-[#fecaca] rounded-md text-[12.5px] font-medium transition-colors cursor-pointer shadow-2xs">
                                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                    <span>Reset to Default Logo</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Logo Live Previews (1 col) -->
                <div class="lg:col-span-1 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-1">Logo Visual Preview</h3>
                    <p class="text-[12px] text-[#6b7280] mb-4">Preview how the logo renders against dark and light platform surfaces.</p>

                    <!-- Dark Backdrop (Mimics Sidebar #161718) -->
                    <div class="mb-4">
                        <span class="text-[11px] font-medium text-[#6b7280] uppercase tracking-wider block mb-1.5">Dark Sidebar Backdrop</span>
                        <div class="bg-[#161718] p-5 rounded-lg border border-[#26282a] flex items-center justify-center min-h-[90px]">
                            <img :src="logoPreview ? logoPreview : '{{ $settings['platform_logo_url'] }}'" 
                                 alt="Platform Logo" 
                                 class="h-10 w-auto object-contain max-w-[180px] transition-all">
                        </div>
                    </div>

                    <!-- Light Backdrop (Mimics Header & Cards) -->
                    <div>
                        <span class="text-[11px] font-medium text-[#6b7280] uppercase tracking-wider block mb-1.5">Light Canvas Backdrop</span>
                        <div class="bg-[#fbf9f5] p-5 rounded-lg border border-[#e5e7eb] flex items-center justify-center min-h-[90px]">
                            <img :src="logoPreview ? logoPreview : '{{ $settings['platform_logo_url'] }}'" 
                                 alt="Platform Logo" 
                                 class="h-10 w-auto object-contain max-w-[180px] transition-all">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#f3f4f6] text-[11.5px] text-[#6b7280] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#093225]">info</span>
                        <span>Current file: <code class="font-mono text-[11px] text-[#374151]">{{ $settings['platform_logo'] ?: 'logo.png (default)' }}</code></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: Amazon S3 & Cloud Storage -->
        <div x-show="activeTab === 'storage'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: S3 Configuration (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 border-b border-[#f3f4f6] pb-3">
                        <div>
                            <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Cloud Storage &amp; Amazon S3 / R2</h2>
                            <p class="text-[12px] text-[#6b7280] mt-0.5">Configure encrypted legal vault storage via Amazon Web Services S3 or Cloudflare R2 / MinIO</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Active Driver Selection -->
                        <div>
                            <label class="block text-[13px] font-medium text-[#374151] mb-2">Primary Storage Driver</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-3.5 rounded-lg border transition-all cursor-pointer"
                                       :class="storageDriver === 'local' ? 'border-[#093225] bg-[#f7faf8] shadow-2xs' : 'border-[#e5e7eb] hover:bg-[#fafafa]'">
                                    <input type="radio" 
                                           name="storage_driver" 
                                           value="local" 
                                           x-model="storageDriver"
                                           class="mt-1 text-[#093225] focus:ring-[#093225]">
                                    <div class="text-[12.5px]">
                                        <div class="font-medium text-[#111827]">Local Private Disk</div>
                                        <div class="text-[#6b7280] mt-0.5 text-[11.5px]">Files stored in local server private directory (`storage/app/private`). Zero cloud latency.</div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-lg border transition-all cursor-pointer"
                                       :class="storageDriver === 's3' ? 'border-[#093225] bg-[#f7faf8] shadow-2xs' : 'border-[#e5e7eb] hover:bg-[#fafafa]'">
                                    <input type="radio" 
                                           name="storage_driver" 
                                           value="s3" 
                                           x-model="storageDriver"
                                           class="mt-1 text-[#093225] focus:ring-[#093225]">
                                    <div class="text-[12.5px]">
                                        <div class="font-medium text-[#111827]">Amazon S3 / Cloudflare R2</div>
                                        <div class="text-[#6b7280] mt-0.5 text-[11.5px]">Distributed multi-region cloud object storage with infinite scalability and AES-256 server-side encryption.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- S3 Credentials Section -->
                        <div class="space-y-4 pt-3 border-t border-[#f3f4f6]">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Access Key ID -->
                                <div>
                                    <label for="aws_access_key_id" class="block text-[13px] font-medium text-[#374151] mb-1">AWS Access Key ID</label>
                                    <input type="text" 
                                           id="aws_access_key_id" 
                                           name="aws_access_key_id" 
                                           value="{{ old('aws_access_key_id', $storage['aws_access_key_id']) }}" 
                                           placeholder="e.g. AKIAIOSFODNN7EXAMPLE" 
                                           class="w-full text-[13px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                                </div>

                                <!-- Secret Access Key -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="aws_secret_access_key" class="block text-[13px] font-medium text-[#374151]">AWS Secret Access Key</label>
                                        <button type="button" @click="showSecret = !showSecret" class="text-[11.5px] text-[#093225] hover:underline cursor-pointer">
                                            <span x-text="showSecret ? 'Hide' : 'Reveal'"></span>
                                        </button>
                                    </div>
                                    <input :type="showSecret ? 'text' : 'password'" 
                                           id="aws_secret_access_key" 
                                           name="aws_secret_access_key" 
                                           value="{{ old('aws_secret_access_key', $storage['aws_secret_access_key']) }}" 
                                           placeholder="{{ $storage['aws_has_secret'] ? '•••••••••••••••• (Leave blank to keep)' : 'Enter AWS secret key' }}" 
                                           class="w-full text-[13px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- AWS Region -->
                                <div>
                                    <label for="aws_default_region" class="block text-[13px] font-medium text-[#374151] mb-1">AWS Default Region</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               id="aws_default_region" 
                                               name="aws_default_region" 
                                               value="{{ old('aws_default_region', $storage['aws_default_region'] ?: 'ap-south-1') }}" 
                                               placeholder="ap-south-1" 
                                               class="w-full text-[13px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                                    </div>
                                    <p class="text-[11px] text-[#6b7280] mt-1">Common: <code class="font-mono">ap-south-1</code> (Mumbai), <code class="font-mono">us-east-1</code> (N. Virginia), <code class="font-mono">eu-central-1</code> (Frankfurt)</p>
                                </div>

                                <!-- Bucket Name -->
                                <div>
                                    <label for="aws_bucket" class="block text-[13px] font-medium text-[#374151] mb-1">S3 Bucket Name</label>
                                    <input type="text" 
                                           id="aws_bucket" 
                                           name="aws_bucket" 
                                           value="{{ old('aws_bucket', $storage['aws_bucket']) }}" 
                                           placeholder="e.g. legal-matter-vault" 
                                           class="w-full text-[13px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                                    <p class="text-[11px] text-[#6b7280] mt-1">Exact name of your private S3 bucket.</p>
                                </div>
                            </div>

                            <!-- Custom Endpoint (R2 / MinIO) -->
                            <div>
                                <label for="aws_endpoint" class="block text-[13px] font-medium text-[#374151] mb-1">Custom S3 Endpoint (Optional)</label>
                                <input type="text" 
                                       id="aws_endpoint" 
                                       name="aws_endpoint" 
                                       value="{{ old('aws_endpoint', $storage['aws_endpoint']) }}" 
                                       placeholder="e.g. https://<account_id>.r2.cloudflarestorage.com or https://minio.yourfirm.in" 
                                       class="w-full text-[13px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                                <p class="text-[11.5px] text-[#6b7280] mt-1">Leave empty for standard AWS S3. Fill in if using Cloudflare R2, MinIO, Wasabi, or DigitalOcean Spaces.</p>
                            </div>

                            <!-- Path Style Endpoint -->
                            <div class="pt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           id="aws_use_path_style_endpoint" 
                                           name="aws_use_path_style_endpoint" 
                                           value="1" 
                                           {{ old('aws_use_path_style_endpoint', $storage['aws_use_path_style_endpoint']) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-[#093225] focus:ring-[#093225]">
                                    <span class="text-[13px] text-[#374151]">Use Path-Style Endpoints (Recommended for MinIO / self-hosted S3)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Live S3 Test Button & Diagnostic Panel -->
                        <div class="pt-4 border-t border-[#f3f4f6] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                        @click="testS3Connection()" 
                                        :disabled="s3Testing"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-[#f9fafb] text-[#093225] border border-[#093225] rounded-md text-[13px] font-medium transition-colors cursor-pointer shadow-2xs disabled:opacity-50">
                                    <span class="material-symbols-outlined text-[18px]" :class="s3Testing ? 'animate-spin' : ''">
                                        <span x-text="s3Testing ? 'refresh' : 'network_ping'"></span>
                                    </span>
                                    <span x-text="s3Testing ? 'Testing S3 Connection...' : 'Test S3 Connection'"></span>
                                </button>
                                <span class="text-[12px] text-[#6b7280]">Verifies SigV4 credentials and bucket reachability without saving</span>
                            </div>
                        </div>

                        <!-- Diagnostic Result Banner -->
                        <div x-show="s3Result" x-cloak class="p-3.5 rounded-md text-[13px] border transition-all"
                             :class="s3Success ? 'bg-[#ecfdf5] border-[#a7f3d0] text-[#065f46]' : 'bg-[#fef2f2] border-[#fecaca] text-[#991b1b]'">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-base shrink-0 mt-0.5" x-text="s3Success ? 'check_circle' : 'warning'"></span>
                                <span x-text="s3Result" class="break-words"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: S3 Info & Best Practices (1 col) -->
                <div class="lg:col-span-1 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6 space-y-4">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a]">S3 Vault Guidelines</h3>
                    
                    <div class="text-[12.5px] text-[#374151] space-y-3">
                        <div class="p-3 bg-[#f9fafb] rounded border border-[#e5e7eb]">
                            <div class="font-medium text-[#111827] flex items-center gap-1.5 mb-1">
                                <span class="material-symbols-outlined text-[16px] text-[#093225]">security</span>
                                <span>Advocate Privacy Rules</span>
                            </div>
                            <p class="text-[11.5px] text-[#6b7280]">Ensure your S3 bucket has <strong>Block Public Access</strong> fully enabled. Litigant documents must never be publicly readable via raw URLs.</p>
                        </div>

                        <div class="p-3 bg-[#f9fafb] rounded border border-[#e5e7eb]">
                            <div class="font-medium text-[#111827] flex items-center gap-1.5 mb-1">
                                <span class="material-symbols-outlined text-[16px] text-[#093225]">vpn_key</span>
                                <span>Minimum IAM Permissions</span>
                            </div>
                            <p class="text-[11.5px] text-[#6b7280]">The IAM policy only requires: <code class="font-mono text-[11px]">s3:PutObject</code>, <code class="font-mono text-[11px]">s3:GetObject</code>, <code class="font-mono text-[11px]">s3:DeleteObject</code>, and <code class="font-mono text-[11px]">s3:ListBucket</code>.</p>
                        </div>

                        <div class="p-3 bg-[#f9fafb] rounded border border-[#e5e7eb]">
                            <div class="font-medium text-[#111827] flex items-center gap-1.5 mb-1">
                                <span class="material-symbols-outlined text-[16px] text-[#093225]">bolt</span>
                                <span>Cloudflare R2 Support</span>
                            </div>
                            <p class="text-[11.5px] text-[#6b7280]">For zero egress fees, enter your Cloudflare R2 endpoint URL and use <code class="font-mono text-[11px]">auto</code> or <code class="font-mono text-[11px]">us-east-1</code> as region.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 3: Public Content & Footer CMS -->
        <div x-show="activeTab === 'content'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: Footer and Content Fields (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6 space-y-6">
                    <div>
                        <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Public Content, Footers &amp; Legal CMS</h2>
                        <p class="text-[12px] text-[#6b7280] mt-0.5">Manage footer headlines, chambers information, privacy disclosures, and terms of service</p>
                    </div>

                    <!-- Footer Headline & Subtext -->
                    <div class="space-y-4 pt-3 border-t border-[#f3f4f6]">
                        <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px] text-[#093225]">dock</span>
                            <span>Global Footer Tagline &amp; Copyright</span>
                        </h3>

                        <div>
                            <label for="footer_headline" class="block text-[13px] font-medium text-[#374151] mb-1">Footer Headline / Motto</label>
                            <input type="text" 
                                   id="footer_headline" 
                                   name="footer_headline" 
                                   value="{{ old('footer_headline', $footer['footer_headline']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>

                        <div>
                            <label for="footer_description" class="block text-[13px] font-medium text-[#374151] mb-1">Footer Description Summary</label>
                            <textarea id="footer_description" 
                                      name="footer_description" 
                                      rows="2" 
                                      class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('footer_description', $footer['footer_description']) }}</textarea>
                        </div>

                        <div>
                            <label for="footer_copyright" class="block text-[13px] font-medium text-[#374151] mb-1">Copyright Line</label>
                            <input type="text" 
                                   id="footer_copyright" 
                                   name="footer_copyright" 
                                   value="{{ old('footer_copyright', $footer['footer_copyright']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>
                    </div>

                    <!-- About Us Section -->
                    <div class="space-y-4 pt-4 border-t border-[#f3f4f6]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#093225]">domain</span>
                                <span>About Us Section</span>
                            </h3>
                            <a href="{{ route('public.about') }}" target="_blank" class="text-[12px] text-[#093225] hover:underline flex items-center gap-0.5">
                                <span>Preview Page</span>
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                            </a>
                        </div>

                        <div>
                            <label for="about_headline" class="block text-[13px] font-medium text-[#374151] mb-1">About Us Headline</label>
                            <input type="text" 
                                   id="about_headline" 
                                   name="about_headline" 
                                   value="{{ old('about_headline', $footer['about_headline']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>

                        <div>
                            <label for="about_content" class="block text-[13px] font-medium text-[#374151] mb-1">About Us Detailed Narrative</label>
                            <textarea id="about_content" 
                                      name="about_content" 
                                      rows="5" 
                                      class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('about_content', $footer['about_content']) }}</textarea>
                            <p class="text-[11.5px] text-[#6b7280] mt-1">Paragraphs separated by new lines will automatically render as distinct readable sections.</p>
                        </div>
                    </div>

                    <!-- Contact Us Section -->
                    <div class="space-y-4 pt-4 border-t border-[#f3f4f6]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#093225]">contact_phone</span>
                                <span>Chambers Contact &amp; Registry</span>
                            </h3>
                            <a href="{{ route('public.contact') }}" target="_blank" class="text-[12px] text-[#093225] hover:underline flex items-center gap-0.5">
                                <span>Preview Page</span>
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                            </a>
                        </div>

                        <div>
                            <label for="contact_headline" class="block text-[13px] font-medium text-[#374151] mb-1">Contact Page Headline</label>
                            <input type="text" 
                                   id="contact_headline" 
                                   name="contact_headline" 
                                   value="{{ old('contact_headline', $footer['contact_headline']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="contact_email" class="block text-[13px] font-medium text-[#374151] mb-1">Public Registry Email</label>
                                <input type="email" 
                                       id="contact_email" 
                                       name="contact_email" 
                                       value="{{ old('contact_email', $footer['contact_email']) }}" 
                                       class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-[13px] font-medium text-[#374151] mb-1">Direct Chambers Phone</label>
                                <input type="text" 
                                       id="contact_phone" 
                                       name="contact_phone" 
                                       value="{{ old('contact_phone', $footer['contact_phone']) }}" 
                                       class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                            </div>
                        </div>

                        <div>
                            <label for="contact_address" class="block text-[13px] font-medium text-[#374151] mb-1">Physical Chambers / Court Address</label>
                            <textarea id="contact_address" 
                                      name="contact_address" 
                                      rows="3" 
                                      class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('contact_address', $footer['contact_address']) }}</textarea>
                        </div>

                        <div>
                            <label for="contact_hours" class="block text-[13px] font-medium text-[#374151] mb-1">Operating Hours / Registry Timings</label>
                            <input type="text" 
                                   id="contact_hours" 
                                   name="contact_hours" 
                                   value="{{ old('contact_hours', $footer['contact_hours']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>
                    </div>

                    <!-- Privacy Policy Section -->
                    <div class="space-y-4 pt-4 border-t border-[#f3f4f6]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#093225]">policy</span>
                                <span>Privacy Policy &amp; Advocate Privilege</span>
                            </h3>
                            <a href="{{ route('public.privacy') }}" target="_blank" class="text-[12px] text-[#093225] hover:underline flex items-center gap-0.5">
                                <span>Preview Page</span>
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                            </a>
                        </div>

                        <div>
                            <label for="privacy_headline" class="block text-[13px] font-medium text-[#374151] mb-1">Privacy Headline</label>
                            <input type="text" 
                                   id="privacy_headline" 
                                   name="privacy_headline" 
                                   value="{{ old('privacy_headline', $footer['privacy_headline']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>

                        <div>
                            <label for="privacy_content" class="block text-[13px] font-medium text-[#374151] mb-1">Privacy Policy &amp; Privilege Statement</label>
                            <textarea id="privacy_content" 
                                      name="privacy_content" 
                                      rows="7" 
                                      class="w-full text-[12.5px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('privacy_content', $footer['privacy_content']) }}</textarea>
                        </div>
                    </div>

                    <!-- Terms of Service Section -->
                    <div class="space-y-4 pt-4 border-t border-[#f3f4f6]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#093225]">gavel</span>
                                <span>Terms of Service &amp; Platform Governance</span>
                            </h3>
                            <a href="{{ route('public.terms') }}" target="_blank" class="text-[12px] text-[#093225] hover:underline flex items-center gap-0.5">
                                <span>Preview Page</span>
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                            </a>
                        </div>

                        <div>
                            <label for="terms_headline" class="block text-[13px] font-medium text-[#374151] mb-1">Terms Headline</label>
                            <input type="text" 
                                   id="terms_headline" 
                                   name="terms_headline" 
                                   value="{{ old('terms_headline', $footer['terms_headline']) }}" 
                                   class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                        </div>

                        <div>
                            <label for="terms_content" class="block text-[13px] font-medium text-[#374151] mb-1">Terms of Service Content</label>
                            <textarea id="terms_content" 
                                      name="terms_content" 
                                      rows="7" 
                                      class="w-full text-[12.5px] font-mono px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('terms_content', $footer['terms_content']) }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- Right: Public Pages Links & Card (1 col) -->
                <div class="lg:col-span-1 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6 space-y-4">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a]">Public Legal Directory</h3>
                    <p class="text-[12px] text-[#6b7280]">These public URLs are accessible without login and link seamlessly into litigant notifications and login footers:</p>

                    <div class="space-y-2.5">
                        <a href="{{ route('public.about') }}" target="_blank" class="p-3 rounded-lg border border-[#e5e7eb] hover:border-[#093225] bg-[#fafafa] hover:bg-white flex items-center justify-between transition-colors block">
                            <div>
                                <div class="text-[13px] font-medium text-[#111827]">/about</div>
                                <div class="text-[11px] text-[#6b7280]">Chambers Mission &amp; Overview</div>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-[#093225]">open_in_new</span>
                        </a>

                        <a href="{{ route('public.contact') }}" target="_blank" class="p-3 rounded-lg border border-[#e5e7eb] hover:border-[#093225] bg-[#fafafa] hover:bg-white flex items-center justify-between transition-colors block">
                            <div>
                                <div class="text-[13px] font-medium text-[#111827]">/contact</div>
                                <div class="text-[11px] text-[#6b7280]">Registry, Map &amp; Direct Phone</div>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-[#093225]">open_in_new</span>
                        </a>

                        <a href="{{ route('public.privacy') }}" target="_blank" class="p-3 rounded-lg border border-[#e5e7eb] hover:border-[#093225] bg-[#fafafa] hover:bg-white flex items-center justify-between transition-colors block">
                            <div>
                                <div class="text-[13px] font-medium text-[#111827]">/privacy</div>
                                <div class="text-[11px] text-[#6b7280]">Advocate Privilege &amp; AES-256 Vault</div>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-[#093225]">open_in_new</span>
                        </a>

                        <a href="{{ route('public.terms') }}" target="_blank" class="p-3 rounded-lg border border-[#e5e7eb] hover:border-[#093225] bg-[#fafafa] hover:bg-white flex items-center justify-between transition-colors block">
                            <div>
                                <div class="text-[13px] font-medium text-[#111827]">/terms</div>
                                <div class="text-[11px] text-[#6b7280]">Practice Engagement &amp; Filings</div>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-[#093225]">open_in_new</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 4: Governance, Security & Deployment -->
        <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: Session & Maintenance (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6 space-y-6">
                    <div>
                        <h2 class="text-[15px] font-semibold text-[#1a1a1a]">Governance &amp; Session Policies</h2>
                        <p class="text-[12px] text-[#6b7280] mt-0.5">Platform session timeout limits and global system broadcast notices</p>
                    </div>

                    <!-- Maintenance Notice -->
                    <div>
                        <label for="maintenance_notice" class="block text-[13px] font-medium text-[#374151] mb-1.5">Maintenance announcement banner</label>
                        <textarea id="maintenance_notice" 
                                  name="maintenance_notice" 
                                  rows="3" 
                                  placeholder="e.g. Scheduled high court registry synchronization on Saturday 02:00–03:00 UTC. Sign-in may be briefly interrupted."
                                  class="w-full text-[13px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('maintenance_notice', $settings['maintenance_notice']) }}</textarea>
                        <p class="text-[12px] text-[#6b7280] mt-1.5">Shown across all logged-in consoles when populated. Leave empty to disable.</p>
                    </div>

                    <!-- Idle Session Timeout -->
                    <div>
                        <label for="idle_timeout" class="block text-[13px] font-medium text-[#374151] mb-1.5">Idle session timeout (hours)</label>
                        <div class="flex items-center gap-3">
                            <input type="number" 
                                   id="idle_timeout" 
                                   name="idle_timeout" 
                                   min="1" 
                                   max="72" 
                                   value="{{ old('idle_timeout', $settings['idle_timeout']) }}" 
                                   required
                                   class="w-32 text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                            <span class="text-[12px] text-[#6b7280]">Valid range: 1 to 72 hours. Default is 12 hours.</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Deployment Telemetry Card (1 col) -->
                <div class="lg:col-span-1 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
                    <h3 class="text-[13.5px] font-semibold text-[#1a1a1a] mb-4">Deployment</h3>

                    <div class="space-y-4 text-[12.5px]">
                        <div>
                            <div class="text-[11.5px] text-[#6b7280] mb-0.5">Public Host URL</div>
                            <div class="font-mono text-[12px] text-[#111827] break-all bg-[#f9fafb] p-1.5 rounded border border-[#e5e7eb]">{{ $deployment['public_url'] }}</div>
                        </div>

                        <div>
                            <div class="text-[11.5px] text-[#6b7280] mb-0.5">Active Vault Storage</div>
                            <div class="text-[#111827] font-medium flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $deployment['storage_driver'] === 's3' ? 'bg-[#059669]' : 'bg-[#6b7280]' }}"></span>
                                <span>{{ $deployment['file_storage'] }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="text-[11.5px] text-[#6b7280] mb-0.5">Application Demo Mode</div>
                            <div class="text-[#111827]">{{ $deployment['demo_mode'] }}</div>
                        </div>

                        <div>
                            <div class="text-[11.5px] text-[#6b7280] mb-0.5">Scheduled Reminders Cron</div>
                            <div class="text-[#111827]">{{ $deployment['scheduled_reminders'] }}</div>
                        </div>

                        <div>
                            <div class="text-[11.5px] text-[#6b7280] mb-0.5">Encryption Master Key</div>
                            <div class="text-[#065f46] font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">verified_user</span>
                                <span>{{ $deployment['encryption_key'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Global Save Action Bar (Sticky at Bottom) -->
        <div class="mt-8 pt-4 border-t border-[#e5e7eb] flex items-center justify-between bg-[#fbf9f5]">
            <div class="text-[12px] text-[#6b7280]">
                Changes saved here take immediate effect across the multi-tenant legal platform.
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="bg-[#093225] hover:bg-[#1b4332] text-white text-[13.5px] font-medium px-5 py-2.5 rounded-md shadow-xs transition-colors cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Save System Settings</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
