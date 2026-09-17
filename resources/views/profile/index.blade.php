@extends('layouts.app')

@section('title', 'My Profile & Security — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-5xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
        <div class="font-semibold mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-rose-600">error</span>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Profile Banner -->
    <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="relative w-20 h-20 rounded-2xl bg-[#FAF8F5] border border-[#EAE4DC] flex items-center justify-center font-serif font-bold text-2xl text-[#9F8349] shrink-0 overflow-hidden">
                @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover"/>
                @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
                <span class="absolute bottom-1 right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-serif font-bold text-[#222222]">{{ $user->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-[#9F8349]/10 text-[#9F8349] border border-[#9F8349]/30">
                        {{ $user->roleRelation->name ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
                <div class="text-xs text-[#766A5E] mt-1">{{ $user->title ?? 'Advocate / Legal Professional' }}</div>
                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-[#766A5E]">
                    <span class="font-mono">{{ $user->email }}</span>
                    @if($user->phone)
                    <span>•</span>
                    <span>{{ $user->phone }}</span>
                    @endif
                    <span>•</span>
                    <span>{{ $user->firm->name ?? 'Chambers' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-[#766A5E]">
                Member since {{ $user->created_at->format('M Y') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Edit Profile Form & Change Password -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Update Particulars -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">badge</span>
                    <span>Personal &amp; Contact Particulars</span>
                </h2>
                <p class="text-xs text-[#766A5E] mb-4">Update how your identity appears on case dossiers, cause lists, and legal opinions</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Full Legal Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Professional Title / Designation</label>
                        <input type="text" name="title" value="{{ old('title', $user->title) }}" placeholder="e.g. Senior Advocate, High Court"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Direct Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Upload Profile Avatar</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#9F8349] file:text-white hover:file:bg-[#856C36]" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-semibold bg-[#9F8349] text-white rounded-lg hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">save</span>
                            <span>Save Profile</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h2 class="text-base font-serif font-bold text-[#222222] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#9F8349]">lock_reset</span>
                    <span>Security &amp; Password</span>
                </h2>
                <p class="text-xs text-[#766A5E] mb-4">Ensure your account uses a strong password with letters, numbers, and symbols</p>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Current Password *</label>
                        <input type="password" name="current_password" required placeholder="Enter current password"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#222222] mb-1">New Password *</label>
                            <input type="password" name="password" required placeholder="Min. 8 chars (mixed case &amp; numbers)"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#222222] mb-1">Confirm New Password *</label>
                            <input type="password" name="password_confirmation" required placeholder="Repeat new password"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-semibold bg-[#222222] text-white rounded-lg hover:bg-black shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">key</span>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Col: Role & Groups Summary -->
        <div class="space-y-6">
            
            <!-- Assigned Practice Groups -->
            <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-3">Your Practice Teams</h3>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->groups as $group)
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222] flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $group->color ?? '#9F8349' }}"></span>
                        <span>{{ $group->name }}</span>
                    </span>
                    @empty
                    <span class="text-xs text-gray-400 italic">No practice groups assigned</span>
                    @endforelse
                </div>
            </div>

            <!-- Role Permissions List -->
            <div class="bg-white p-5 rounded-2xl border border-[#EFECE6] shadow-xs">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[#766A5E] mb-2">Assigned Permissions</h3>
                <p class="text-[11px] text-[#766A5E] mb-3">Privileges granted by your role and group memberships:</p>
                <div class="max-h-60 overflow-y-auto space-y-1.5 text-xs text-[#222222]">
                    @php
                        $userPermissions = $user->roleRelation ? $user->roleRelation->permissions : collect();
                    @endphp
                    @forelse($userPermissions as $p)
                    <div class="flex items-center gap-2 py-0.5">
                        <span class="material-symbols-outlined text-xs text-emerald-600">check</span>
                        <span class="font-mono text-[11px] text-[#766A5E]">{{ $p->name }}</span>
                    </div>
                    @empty
                    <div class="text-xs text-gray-400">Standard user privileges active.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
