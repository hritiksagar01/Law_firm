@extends('layouts.app')

@section('title', 'My Profile & Security — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-5xl mx-auto p-6 md:p-8">

    <!-- Flash Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
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
    <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="relative w-16 h-16 rounded-xl bg-[#23493a]/10 border border-[#23493a]/20 flex items-center justify-center font-bold text-xl text-[#23493a] shrink-0 overflow-hidden">
                @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover"/>
                @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
                <span class="absolute bottom-1 right-1 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-[#1a1a1a] tracking-tight">{{ $user->name }}</h1>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-[#23493a]/10 text-[#23493a] border border-[#23493a]/20">
                        {{ $user->roleRelation->name ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
                <div class="text-xs text-[#646864] mt-0.5">{{ $user->title ?? 'Advocate / Legal Professional' }}</div>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-[#646864]">
                    <span class="font-mono text-[11px]">{{ $user->email }}</span>
                    @if($user->phone)
                    <span>•</span>
                    <span class="text-[11px]">{{ $user->phone }}</span>
                    @endif
                    <span>•</span>
                    <span class="text-[11px]">{{ $user->firm->name ?? 'Chambers' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-[#646864]">
                Member since {{ $user->created_at->format('M Y') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Edit Profile Form & Change Password -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Update Particulars -->
            <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm">
                <h2 class="text-sm font-semibold text-[#1a1a1a] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-base">badge</span>
                    <span>Personal &amp; Contact Particulars</span>
                </h2>
                <p class="text-xs text-[#646864] mb-4">Update how your identity appears on case dossiers, cause lists, and legal opinions</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Full Legal Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Professional Title / Designation</label>
                        <input type="text" name="title" value="{{ old('title', $user->title) }}" placeholder="e.g. Senior Advocate, High Court"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Direct Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210"
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Upload Profile Avatar</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#23493a] file:text-white hover:file:bg-[#1a382c]" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#23493a] text-white rounded-lg hover:bg-[#1a382c] shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Save Profile</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm">
                <h2 class="text-sm font-semibold text-[#1a1a1a] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#23493a] text-base">lock_reset</span>
                    <span>Security &amp; Password</span>
                </h2>
                <p class="text-xs text-[#646864] mb-4">Ensure your account uses a strong password with letters, numbers, and symbols</p>

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
                            <input type="password" name="password" required placeholder="Min. 8 chars"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Confirm New Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password_confirmation" required placeholder="Repeat new password"
                                class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-xs font-medium bg-[#1a1a1a] text-white rounded-lg hover:bg-black shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">key</span>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Col: Role & Groups Summary -->
        <div class="space-y-6">
            
            <!-- Assigned Practice Groups -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-3">Your Practice Teams</h3>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->groups as $group)
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a] flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $group->color ?? '#23493a' }}"></span>
                        <span>{{ $group->name }}</span>
                    </span>
                    @empty
                    <span class="text-xs text-[#8a8a8a] italic">No practice groups assigned</span>
                    @endforelse
                </div>
            </div>

            <!-- Role Permissions List -->
            <div class="bg-white p-5 rounded-xl border border-[#e5e3dc] shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#646864] mb-2">Assigned Permissions</h3>
                <p class="text-[11px] text-[#646864] mb-3">Privileges granted by your role and group memberships:</p>
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
                    <div class="text-xs text-[#8a8a8a]">Standard user privileges active.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
