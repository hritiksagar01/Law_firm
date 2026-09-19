@extends('layouts.admin')

@section('title', 'System settings — Quire')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="font-serif text-[28px] text-[#191c1e] font-normal tracking-tight">System settings</h1>
        <p class="text-[13px] text-[#5c6063] mt-0.5">Platform-wide values</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 px-4 py-2.5 bg-[#ecfdf5] border border-[#a7f3d0] rounded-md text-[13px] text-[#065f46]">
        {{ session('success') }}
    </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="mb-6 px-4 py-3 bg-[#fef2f2] border border-[#fecaca] rounded-md text-[13px] text-[#991b1b]">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 2-Column Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: General Form Card (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
            <h2 class="text-[14.5px] font-semibold text-[#111827] mb-6">General</h2>

            <form action="{{ route('admin.system.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Platform Name -->
                <div>
                    <label for="platform_name" class="block text-[13px] font-medium text-[#374151] mb-1.5">Platform name</label>
                    <input type="text" 
                           id="platform_name" 
                           name="platform_name" 
                           value="{{ old('platform_name', $settings['platform_name']) }}" 
                           required
                           class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                    <p class="text-[12px] text-[#6b7280] mt-1.5">Shown in emails sent outside any firm, such as platform admin invitations.</p>
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
                    <p class="text-[12px] text-[#6b7280] mt-1.5">Shown to firms whose workspace is suspended.</p>
                </div>

                <!-- Maintenance Notice -->
                <div>
                    <label for="maintenance_notice" class="block text-[13px] font-medium text-[#374151] mb-1.5">Maintenance notice</label>
                    <textarea id="maintenance_notice" 
                              name="maintenance_notice" 
                              rows="3" 
                              placeholder="e.g. Scheduled maintenance Saturday 02:00–03:00 UTC. Sign-in may be unavailable."
                              class="w-full text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">{{ old('maintenance_notice', $settings['maintenance_notice']) }}</textarea>
                    <p class="text-[12px] text-[#6b7280] mt-1.5">Leave empty for no notice. Up to 300 characters.</p>
                </div>

                <!-- Idle Session Timeout -->
                <div>
                    <label for="idle_timeout" class="block text-[13px] font-medium text-[#374151] mb-1.5">Idle session timeout (hours)</label>
                    <input type="number" 
                           id="idle_timeout" 
                           name="idle_timeout" 
                           min="1" 
                           max="72" 
                           value="{{ old('idle_timeout', $settings['idle_timeout']) }}" 
                           required
                           class="w-28 text-[13.5px] px-3.5 py-2 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                    <p class="text-[12px] text-[#6b7280] mt-1.5">Between 1 and 72.</p>
                </div>

                <!-- Save Settings Button -->
                <div class="pt-2">
                    <button type="submit" class="bg-[#093225] hover:bg-[#1b4332] text-white text-[13px] font-medium px-4 py-2 rounded-md shadow-xs transition-colors cursor-pointer">
                        Save settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Deployment Card (1 col) -->
        <div class="lg:col-span-1 bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
            <h2 class="text-[14.5px] font-semibold text-[#111827] mb-6">Deployment</h2>

            <div class="space-y-5">
                <!-- Public URL -->
                <div>
                    <div class="text-[12px] text-[#6b7280] mb-0.5">Public URL</div>
                    <div class="text-[13px] font-mono text-[#111827] break-all">{{ $deployment['public_url'] }}</div>
                </div>

                <!-- File Storage -->
                <div>
                    <div class="text-[12px] text-[#6b7280] mb-0.5">File storage</div>
                    <div class="text-[13px] text-[#111827]">{{ $deployment['file_storage'] }}</div>
                </div>

                <!-- Demo Mode -->
                <div>
                    <div class="text-[12px] text-[#6b7280] mb-0.5">Demo mode</div>
                    <div class="text-[13px] text-[#111827]">{{ $deployment['demo_mode'] }}</div>
                </div>

                <!-- Scheduled Reminders -->
                <div>
                    <div class="text-[12px] text-[#6b7280] mb-0.5">Scheduled reminders</div>
                    <div class="text-[13px] text-[#111827]">{{ $deployment['scheduled_reminders'] }}</div>
                </div>

                <!-- Encryption Key -->
                <div>
                    <div class="text-[12px] text-[#6b7280] mb-0.5">Encryption key</div>
                    <div class="text-[13px] text-[#111827]">{{ $deployment['encryption_key'] }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
