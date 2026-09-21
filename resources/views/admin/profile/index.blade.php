@extends('layouts.admin')

@section('title', 'Super Admin Profile Management')
@section('header_title', 'User Profile Management')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    <!-- Page Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Super Administrator Profile</h1>
            <p class="text-[13px] text-[#646864] mt-1">Configure credentials, identity verification documents, and administrative contact details</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-[#9F8349]/10 text-[#9F8349] border border-[#9F8349]/20">
                <span class="w-2 h-2 rounded-full bg-[#9F8349]"></span>
                <span>Role: Superadmin</span>
            </span>
        </div>
    </div>

    <!-- Main Grid: Profile Form + Side Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Primary User Profile Form (PDF Page 4 Exact Alignment) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-xs p-6 sm:p-8">
                <div class="flex items-center justify-between pb-5 border-b border-[#EFECE6] mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FAF8F5] border border-[#EFECE6] flex items-center justify-center text-[#9F8349]">
                            <span class="material-symbols-outlined text-2xl">badge</span>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">User Profile</h2>
                            <p class="text-xs text-[#766A5E]">Personal identification & contact configuration</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#554D45] mb-1">First Name *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                   placeholder="Super"/>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#554D45] mb-1">Surname *</label>
                            <input type="text" name="surname" value="{{ old('surname', $user->surname) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                   placeholder="Admin"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Account Display Name *</label>
                        <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                               placeholder="Super Administrator"/>
                    </div>

                    <!-- Identification Details (Page 4 Specification) -->
                    <div class="pt-4 border-t border-[#EFECE6]">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold block mb-3">Identity Document & Verification</span>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Id Type *</label>
                                <select name="id_type" class="w-full px-3 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none bg-white">
                                    <option value="">Select Id Type</option>
                                    <option value="Aadhar Card" {{ old('id_type', $user->id_type) === 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card</option>
                                    <option value="PAN Card" {{ old('id_type', $user->id_type) === 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                                    <option value="Passport" {{ old('id_type', $user->id_type) === 'Passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="Bar Council ID" {{ old('id_type', $user->id_type) === 'Bar Council ID' ? 'selected' : '' }}>Bar Council ID</option>
                                    <option value="Voter ID" {{ old('id_type', $user->id_type) === 'Voter ID' ? 'selected' : '' }}>Voter ID</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Id Number *</label>
                                <input type="text" name="id_number" value="{{ old('id_number', $user->id_number) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-xs font-mono focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                       placeholder="e.g. LMVE03434"/>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Expiration Date</label>
                                <input type="date" name="id_expiration" 
                                       value="{{ old('id_expiration', $user->id_expiration ? $user->id_expiration->format('Y-m-d') : '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"/>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-[#554D45] mb-1">Upload ID Document (PDF or Image, max 5MB)</label>
                            <input type="file" name="id_document" accept=".pdf,image/*" 
                                   class="w-full text-xs text-[#554D45] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FAF8F5] file:text-[#9F8349] hover:file:bg-[#9F8349] hover:file:text-white transition-all"/>
                            @if($user->id_document_path)
                            <div class="mt-2 flex items-center gap-2 text-xs text-emerald-700">
                                <span class="material-symbols-outlined text-sm">attachment</span>
                                <a href="{{ asset('storage/' . $user->id_document_path) }}" target="_blank" class="underline hover:text-emerald-900">
                                    View Uploaded ID Document
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- E-Mail Section (Page 4 Specification) -->
                    <div class="pt-4 border-t border-[#EFECE6]">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold block mb-3">E-Mail Addresses</span>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Primary E-Mail *</label>
                                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                       placeholder="admin@sharmalegal.in"/>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Secondary E-Mail (Recovery)</label>
                                <input type="email" name="secondary_email" value="{{ old('secondary_email', $user->secondary_email) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                       placeholder="backup@gmail.com"/>
                            </div>
                        </div>
                    </div>

                    <!-- Phone Numbers Section (Page 4 Specification) -->
                    <div class="pt-4 border-t border-[#EFECE6]">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold block mb-3">Phone Numbers</span>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Phone Number *</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                       placeholder="+91 99119 78651"/>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Type</label>
                                <select name="phone_type" class="w-full px-3 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none bg-white">
                                    <option value="Home" {{ old('phone_type', $user->phone_type) === 'Home' ? 'selected' : '' }}>Home</option>
                                    <option value="Mobile" {{ old('phone_type', $user->phone_type) === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                    <option value="Office" {{ old('phone_type', $user->phone_type) === 'Office' ? 'selected' : '' }}>Office</option>
                                    <option value="Fax" {{ old('phone_type', $user->phone_type) === 'Fax' ? 'selected' : '' }}>Fax</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section (Page 4 Specification) -->
                    <div class="pt-4 border-t border-[#EFECE6]">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#9F8349] font-semibold block mb-3">Residential / Official Address</span>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#554D45] mb-1">Street *</label>
                                <input type="text" name="street" value="{{ old('street', $user->street) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                       placeholder="Chambers Road, Bar Council Complex"/>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#554D45] mb-1">City *</label>
                                    <input type="text" name="user_city" value="{{ old('user_city', $user->user_city) }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                           placeholder="New Delhi"/>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#554D45] mb-1">State *</label>
                                    <input type="text" name="user_state" value="{{ old('user_state', $user->user_state) }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                           placeholder="Delhi"/>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Country *</label>
                                    <input type="text" name="user_country" value="{{ old('user_country', $user->user_country ?? 'India') }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                           placeholder="India"/>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#554D45] mb-1">Zip/Postal Code</label>
                                    <input type="text" name="user_postal_code" value="{{ old('user_postal_code', $user->user_postal_code) }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-sm font-mono focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                                           placeholder="110001"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-[#EFECE6] flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all">
                            <span class="material-symbols-outlined text-base">save</span>
                            <span>Update Profile</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Avatar & Password Management -->
        <div class="space-y-8">
            
            <!-- Avatar Card -->
            <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-xs p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-[#EFECE6] mb-5">
                    <span class="material-symbols-outlined text-[#9F8349]">account_circle</span>
                    <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Profile Avatar</h3>
                </div>

                <div class="flex flex-col items-center text-center">
                    @if($user->resolved_avatar)
                        <img src="{{ $user->resolved_avatar }}" alt="{{ $user->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-2 border-[#9F8349]/30 shadow-sm mb-3"/>
                    @else
                        <div class="w-24 h-24 rounded-full bg-[#9F8349] text-white flex items-center justify-center font-bold text-2xl shadow-sm mb-3">
                            {{ strtoupper(substr($user->first_name ?? $user->name, 0, 1)) }}{{ strtoupper(substr($user->surname ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <span class="font-semibold text-sm text-[#222222]">{{ $user->full_display_name }}</span>
                    <span class="text-xs font-mono text-[#766A5E]">{{ $user->email }}</span>

                    <form method="POST" action="{{ route('admin.profile.change-avatar') }}" enctype="multipart/form-data" class="w-full mt-5 space-y-3">
                        @csrf
                        <input type="file" name="avatar" accept="image/*" required 
                               class="w-full text-xs text-[#554D45] file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-[#FAF8F5] file:text-[#9F8349] hover:file:bg-[#9F8349] hover:file:text-white transition-all"/>
                        
                        <button type="submit" 
                                class="w-full py-2 rounded-xl text-xs font-semibold text-white bg-[#9F8349] hover:bg-[#856C36] shadow-xs transition-all">
                            Upload New Photo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Security Card -->
            <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-xs p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-[#EFECE6] mb-5">
                    <span class="material-symbols-outlined text-[#9F8349]">lock</span>
                    <h3 class="text-[14px] font-semibold text-[#1a1a1a]">Change Password</h3>
                </div>

                <form method="POST" action="{{ route('admin.profile.change-password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Current Password *</label>
                        <input type="password" name="current_password" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                               placeholder="Current password"/>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">New Password (Min 8 chars) *</label>
                        <input type="password" name="new_password" required minlength="8"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                               placeholder="New password"/>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#554D45] mb-1">Confirm New Password *</label>
                        <input type="password" name="new_password_confirmation" required minlength="8"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:ring-1 focus:ring-[#9F8349] outline-none"
                               placeholder="Confirm new password"/>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-[#222222] hover:bg-[#3A322B] shadow-xs transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">key</span>
                        <span>Update Password</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
