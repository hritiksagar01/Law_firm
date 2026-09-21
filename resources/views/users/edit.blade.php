@extends('layouts.app')

@section('title', 'Edit ' . $user->name . ' — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#646864] hover:text-[#23493a] transition-colors mb-2 font-medium">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Personnel Directory</span>
        </a>
        <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Edit Team Member</h1>
        <p class="text-xs text-[#646864] mt-0.5">Update particulars, role, practice group memberships, or reset password</p>
    </div>

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

    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Professional Identity -->
        <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm">
            <h2 class="text-sm font-semibold text-[#1a1a1a] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-base">person</span>
                <span>Personal Particulars</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Full Legal Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Official Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Professional Title / Designation</label>
                    <input type="text" name="title" value="{{ old('title', $user->title) }}"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Direct Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Status</label>
                    <select name="status" class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] text-[#1a1a1a]">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1.5">Reset Password (leave empty to keep current)</label>
                    <input type="password" name="password" placeholder="New password"
                        class="w-full px-3.5 py-2 text-xs rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:outline-none focus:border-[#23493a] focus:ring-1 focus:ring-[#23493a] text-[#1a1a1a]" />
                </div>
            </div>
        </div>

        <!-- Role & Practice Groups -->
        <div class="bg-white p-6 rounded-xl border border-[#e5e3dc] shadow-sm">
            <h2 class="text-sm font-semibold text-[#1a1a1a] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#23493a] text-base">security</span>
                <span>Role &amp; Practice Permissions</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-2">Assigned Role <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($roles as $r)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] hover:bg-white cursor-pointer transition-colors">
                            <input type="radio" name="role_id" value="{{ $r->id }}" {{ old('role_id', $user->role_id) == $r->id ? 'checked' : '' }}
                                class="mt-0.5 text-[#23493a] focus:ring-[#23493a]" />
                            <div>
                                <span class="font-semibold text-xs text-[#1a1a1a] block">{{ $r->name }}</span>
                                <span class="text-[11px] text-[#646864] block mt-0.5">{{ $r->description }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                @if($groups->count() > 0)
                <div class="pt-4 border-t border-[#e5e3dc]">
                    <label class="block text-xs font-medium text-[#1a1a1a] mb-1">Practice Groups</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($groups as $g)
                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] hover:bg-white cursor-pointer text-xs transition-colors">
                            <input type="checkbox" name="groups[]" value="{{ $g->id }}"
                                {{ in_array($g->id, old('groups', $assignedGroupIds)) ? 'checked' : '' }}
                                class="rounded text-[#23493a] focus:ring-[#23493a]" />
                            <span class="text-[#1a1a1a] font-medium">{{ $g->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg border border-[#e5e3dc] bg-white text-[#1a1a1a] text-xs font-medium hover:bg-[#faf8f5] transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">save</span>
                <span>Save Changes</span>
            </button>
        </div>
    </form>

</div>
@endsection
