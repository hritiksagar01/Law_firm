@extends('layouts.app')

@section('title', 'Edit ' . $user->name . ' — ' . config('legal.app_name', 'Vennamraj Associates'))

@section('content')
<div class="max-w-4xl mx-auto p-6 md:p-8">

    <div class="mb-8">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1 text-xs text-[#766A5E] hover:text-[#9F8349] mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Back to Personnel Directory</span>
        </a>
        <h1 class="text-3xl font-serif font-bold text-[#222222]">Edit Team Member</h1>
        <p class="text-sm text-[#766A5E] mt-1">Update particulars, role, practice group memberships, or reset password</p>
    </div>

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

    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Professional Identity -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">person</span>
                <span>Personal Particulars</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Full Legal Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Official Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Professional Title / Designation</label>
                    <input type="text" name="title" value="{{ old('title', $user->title) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Direct Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Reset Password (leave empty to keep current)</label>
                    <input type="password" name="password" placeholder="New password"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] focus:outline-none focus:border-[#9F8349]" />
                </div>
            </div>
        </div>

        <!-- Role & Practice Groups -->
        <div class="bg-white p-6 rounded-2xl border border-[#EFECE6] shadow-xs">
            <h2 class="text-base font-serif font-bold text-[#222222] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#9F8349]">security</span>
                <span>Role &amp; Practice Permissions</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Assigned Role *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($roles as $r)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-[#EAE4DC] bg-[#FAF8F5] hover:bg-white cursor-pointer transition-colors">
                            <input type="radio" name="role_id" value="{{ $r->id }}" {{ old('role_id', $user->role_id) == $r->id ? 'checked' : '' }}
                                class="mt-0.5 text-[#9F8349] focus:ring-[#9F8349]" />
                            <div>
                                <span class="font-semibold text-xs text-[#222222] block">{{ $r->name }}</span>
                                <span class="text-[11px] text-[#766A5E] block mt-0.5">{{ $r->description }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                @if($groups->count() > 0)
                <div class="pt-4 border-t border-[#EFECE6]">
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Practice Groups</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($groups as $g)
                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#EAE4DC] bg-[#FAF8F5] hover:bg-white cursor-pointer text-xs transition-colors">
                            <input type="checkbox" name="groups[]" value="{{ $g->id }}"
                                {{ in_array($g->id, old('groups', $assignedGroupIds)) ? 'checked' : '' }}
                                class="rounded text-[#9F8349] focus:ring-[#9F8349]" />
                            <span class="text-[#222222] font-medium">{{ $g->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-lg border border-[#EAE4DC] bg-white text-[#222222] text-xs font-semibold hover:bg-[#FAF8F5]">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-sm transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">save</span>
                <span>Save Changes</span>
            </button>
        </div>
    </form>

</div>
@endsection
