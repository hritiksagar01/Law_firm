@extends('layouts.app')

@section('title', 'My Profile & Professional Credentials — ' . config('legal.app_name', 'Lawyer Workspace'))
@section('header_title', 'My Profile')

@section('content')
<div class="max-w-6xl mx-auto p-4 sm:p-6 md:p-8 text-[#1a1a1a]">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-xs">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-xs">
        <div class="font-semibold mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-rose-600 text-sm">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-rose-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Profile Banner -->
    <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-xs mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4">
            <div class="relative w-18 h-18 rounded-xl bg-[#23493a]/10 border border-[#23493a]/20 flex items-center justify-center font-bold text-2xl text-[#23493a] shrink-0 overflow-hidden shadow-xs">
                @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover"/>
                @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
                <span class="absolute bottom-1 right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-2 ring-white" title="Active"></span>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">{{ $user->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#23493a]/10 text-[#23493a] border border-[#23493a]/20">
                        {{ $roles[$user->role] ?? ($user->roleRelation->name ?? ucfirst(str_replace('_', ' ', $user->role))) }}
                    </span>
                    @if($user->bar_number)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium bg-[#faf8f5] text-[#646864] border border-[#e5e3dc] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] text-[#23493a]">verified</span>
                        <span>Bar: {{ $user->bar_number }}</span>
                    </span>
                    @endif
                </div>
                <div class="text-xs text-[#646864] mt-1 font-medium">{{ $user->title ?? 'Legal Practitioner / Counsel' }}</div>
                
                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-[#646864]">
                    <span class="font-mono text-[11.5px] text-[#1a1a1a]">{{ $user->email }}</span>
                    @if($user->phone)
                    <span class="text-[#c1c8c3]">&middot;</span>
                    <span class="text-[11.5px]">{{ $user->phone }}</span>
                    @endif
                    @if($user->department)
                    <span class="text-[#c1c8c3]">&middot;</span>
                    <span class="text-[11.5px] font-medium text-[#23493a]">{{ $user->department }}</span>
                    @endif
                    @if($user->office_location)
                    <span class="text-[#c1c8c3]">&middot;</span>
                    <span class="text-[11.5px]">{{ $user->office_location }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-[#646864]">
                {{ $user->timezone ?? 'Asia/Kolkata' }}
            </span>
            <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-[#646864]">
                Admitted {{ $user->admission_date ? $user->admission_date->format('M Y') : 'Chamber Member' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Cols: Comprehensive Profile Update Form -->
        <div class="lg:col-span-8 space-y-6">
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- SECTION 1: Personal, Contact & Placement Particulars -->
                <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-5">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#23493a] text-lg">badge</span>
                            <h2 class="text-sm font-semibold text-[#1a1a1a]">Personal Particulars &amp; Placement</h2>
                        </div>
                        <span class="text-[11px] text-[#8a8a8a] font-mono">Chambers Directory Identity</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Full Legal Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Primary Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a] font-mono" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Secondary / Alternate Email</label>
                            <input type="email" name="secondary_email" value="{{ old('secondary_email', $user->secondary_email) }}" placeholder="e.g. counsel.clerk@gmail.com"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a] font-mono" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Direct Contact Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98111 22334"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Professional Title / Designation</label>
                            <input type="text" name="title" value="{{ old('title', $user->title) }}" placeholder="e.g. Senior Managing Partner"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Chamber Role <span class="text-rose-500">*</span></label>
                            <select name="role" required class="w-full px-3 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                                @foreach($roles as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}" {{ old('role', $user->role) === $roleKey ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Practice Department</label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g. Commercial Litigation &amp; Dispute Resolution"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Office Location</label>
                            <input type="text" name="office_location" value="{{ old('office_location', $user->office_location) }}" placeholder="e.g. High Court Chambers Block, New Delhi"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Timezone</label>
                            <select name="timezone" class="w-full px-3 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]">
                                @foreach($timezones as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}" {{ old('timezone', $user->timezone ?? 'Asia/Kolkata') === $tzValue ? 'selected' : '' }}>
                                        {{ $tzLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Upload Profile Photo / Avatar</label>
                            <input type="file" name="avatar" accept="image/*"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#23493a] file:text-white hover:file:bg-[#1a382c] transition-all" />
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Attorney Bar Licensure & Admissions -->
                <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-5">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#23493a] text-lg">gavel</span>
                            <h2 class="text-sm font-semibold text-[#1a1a1a]">Attorney Bar Number &amp; Admissions</h2>
                        </div>
                        <span class="text-[11px] text-[#8a8a8a] font-mono">Judicial Roll &amp; Council Records</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Attorney Bar Number</label>
                            <input type="text" name="bar_number" value="{{ old('bar_number', $user->bar_number) }}" placeholder="e.g. D/1428/2014"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a] font-mono" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Jurisdiction / Bar Council</label>
                            <input type="text" name="jurisdiction" value="{{ old('jurisdiction', $user->jurisdiction) }}" placeholder="e.g. Bar Council of Delhi / Supreme Court"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Admission Date</label>
                            <input type="date" name="admission_date" value="{{ old('admission_date', $user->admission_date ? $user->admission_date->format('Y-m-d') : '') }}"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a] font-mono" />
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Practice Areas Selection -->
                <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#23493a] text-lg">category</span>
                            <h2 class="text-sm font-semibold text-[#1a1a1a]">Practice Areas</h2>
                        </div>
                        <span class="text-[11px] text-[#8a8a8a] font-mono">Specialized Legal Disciplines</span>
                    </div>

                    <p class="text-xs text-[#646864] mb-4">Select legal disciplines you represent on cause lists, client briefs, and matter dossiers:</p>

                    @php
                        $userAreas = is_array($user->practice_areas) ? $user->practice_areas : [];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($availablePracticeAreas as $area)
                            @php
                                $isChecked = in_array($area, old('practice_areas', $userAreas));
                            @endphp
                            <label class="flex items-start gap-2.5 p-3 rounded-lg border border-[#e5e3dc] hover:border-[#23493a] hover:bg-[#faf9f5] cursor-pointer transition-colors {{ $isChecked ? 'bg-[#f5f9f6] border-[#23493a]/30' : 'bg-white' }}">
                                <input type="checkbox" name="practice_areas[]" value="{{ $area }}" {{ $isChecked ? 'checked' : '' }}
                                    class="mt-0.5 rounded text-[#23493a] focus:ring-[#23493a]" />
                                <span class="text-xs font-medium text-[#1a1a1a] leading-tight">{{ $area }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold bg-[#23493a] text-white rounded-lg hover:bg-[#1a382c] shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-[17px]">save</span>
                        <span>Save Profile &amp; Credentials</span>
                    </button>
                </div>
            </form>

            <!-- SECTION 4: Change Password & Security -->
            <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#f0eee8] mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#23493a] text-lg">lock_reset</span>
                        <h2 class="text-sm font-semibold text-[#1a1a1a]">Security &amp; Password</h2>
                    </div>
                    <span class="text-[11px] text-[#8a8a8a] font-mono">Encrypted Auth Credentials</span>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Current Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="current_password" required placeholder="Enter current password"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">New Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required placeholder="Min. 8 characters"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Confirm New Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password_confirmation" required placeholder="Repeat new password"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#1a1a1a] text-white rounded-lg hover:bg-black shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-[15px]">key</span>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right 4 Cols: Summary Widgets & Metadata -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Professional Credentials Summary Card -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#23493a]">fact_check</span>
                    <span>Credentials Summary</span>
                </h3>
                
                <div class="divide-y divide-[#f0eee8] text-xs">
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Chamber Role</span>
                        <span class="font-semibold text-[#1a1a1a]">{{ $roles[$user->role] ?? ($user->roleRelation->name ?? ucfirst(str_replace('_', ' ', $user->role))) }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Bar Number</span>
                        <span class="font-mono font-medium text-[#23493a]">{{ $user->bar_number ?? 'Not specified' }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Jurisdiction</span>
                        <span class="font-medium text-[#1a1a1a] text-right">{{ $user->jurisdiction ?? 'All Indian Courts' }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Admission Date</span>
                        <span class="font-mono text-[#1a1a1a]">{{ $user->admission_date ? $user->admission_date->format('d M Y') : 'N/A' }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Department</span>
                        <span class="font-medium text-[#1a1a1a] text-right">{{ $user->department ?? 'General Practice' }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Office Location</span>
                        <span class="font-medium text-[#1a1a1a] text-right">{{ $user->office_location ?? 'Main Chamber' }}</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[#646864]">Timezone</span>
                        <span class="font-mono text-[#1a1a1a]">{{ $user->timezone ?? 'Asia/Kolkata' }}</span>
                    </div>
                </div>
            </div>

            <!-- Active Practice Areas Tags -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#23493a]">cases</span>
                    <span>Your Practice Areas</span>
                </h3>
                <div class="flex flex-wrap gap-1.5">
                    @forelse(is_array($user->practice_areas) ? $user->practice_areas : [] as $area)
                    <span class="px-2.5 py-1 rounded-md text-[11.5px] bg-[#f5f3ed] border border-[#e5e3dc] text-[#23493a] font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#23493a]"></span>
                        <span>{{ $area }}</span>
                    </span>
                    @empty
                    <span class="text-xs text-[#8a8a8a] italic">No practice areas selected. Please check your areas on the left.</span>
                    @endforelse
                </div>
            </div>

            <!-- Assigned Practice Teams -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#23493a]">groups</span>
                    <span>Assigned Practice Teams</span>
                </h3>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->groups as $group)
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a] flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $group->color ?? '#23493a' }}"></span>
                        <span>{{ $group->name }}</span>
                    </span>
                    @empty
                    <span class="text-xs text-[#8a8a8a] italic">No chamber practice groups assigned</span>
                    @endforelse
                </div>
            </div>

            <!-- Role Permissions List -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#23493a]">shield</span>
                    <span>Active Role Privileges</span>
                </h3>
                <p class="text-[11px] text-[#646864] mb-3">System capabilities granted by your role ({{ $roles[$user->role] ?? ucfirst($user->role) }}):</p>
                <div class="max-h-60 overflow-y-auto space-y-1.5 text-xs text-[#1a1a1a]">
                    @php
                        $userPermissions = $user->roleRelation ? $user->roleRelation->permissions : collect();
                    @endphp
                    @forelse($userPermissions as $p)
                    <div class="flex items-center gap-2 py-0.5">
                        <span class="material-symbols-outlined text-xs text-emerald-600">check</span>
                        <span class="font-mono text-[11px] text-[#646864]">{{ $p->name }}</span>
                    </div>
                    @empty
                    <div class="text-xs text-[#646864] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-xs text-emerald-600">check</span>
                        <span>Standard {{ $roles[$user->role] ?? 'Lawyer' }} permissions active</span>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
