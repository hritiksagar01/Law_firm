@extends('layouts.admin')

@section('title', 'Roles & categories — Platform Console')

@section('content')
<div class="py-4">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-[28px] sm:text-[32px] font-semibold text-[#1a1a1a] tracking-tight leading-tight">Roles &amp; categories</h1>
        <p class="text-[13px] text-[#646864] mt-1">Starting configuration for new firms</p>
    </div>

    <!-- Alert / Notice Banner -->
    <div class="bg-[#f5f3ed] border border-[#eae8e2] rounded-md px-4 py-3 text-[13px] text-[#414844] mb-8 leading-relaxed">
        These defaults are copied into a firm when it is created. Changing them does not alter existing firms; each firm's administrator manages their own roles and categories.
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 px-4 py-2.5 bg-[#ecfdf5] border border-[#a7f3d0] rounded-md text-[13px] text-[#065f46]">
        {{ session('success') }}
    </div>
    @endif

    <!-- 1. Default Role Permissions Form & Matrix -->
    <form action="{{ route('admin.roles.permissions') }}" method="POST" class="bg-white rounded-lg border border-[#e5e7eb] shadow-xs overflow-hidden mb-10">
        @csrf
        <div class="px-6 py-4 border-b border-[#f3f4f6]">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Default role permissions</h2>
            <p class="text-[12px] text-[#6b7280] mt-0.5">Firm administrators always hold every permission.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#e5e7eb] text-[12px] text-[#6b7280]">
                        <th class="px-6 py-2.5 font-medium">Permission</th>
                        <th class="px-4 py-2.5 font-medium text-center w-28">Administrator</th>
                        <th class="px-4 py-2.5 font-medium text-center w-28">Attorney</th>
                        <th class="px-4 py-2.5 font-medium text-center w-28">Paralegal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f3f4f6]">
                    @foreach($groupedPermissions as $module => $permissions)
                        <!-- Module Group Header -->
                        <tr class="bg-[#fafafa]">
                            <td colspan="4" class="px-6 py-2 text-[11.5px] font-semibold text-[#6b7280] uppercase tracking-wider border-y border-[#f3f4f6]">
                                {{ $module }}
                            </td>
                        </tr>

                        <!-- Permissions in this Module -->
                        @foreach($permissions as $perm)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="text-[13px] font-normal text-[#1f2937] leading-tight">{{ $perm->name }}</div>
                                <div class="font-mono text-[11px] text-[#8e8e8e] mt-0.5">{{ $perm->slug }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-[12px] text-[#6b7280]">
                                Always
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" 
                                       name="attorney_permissions[]" 
                                       value="{{ $perm->slug }}" 
                                       {{ in_array($perm->slug, $attorneyPermSlugs) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-[#093225] focus:ring-[#093225] w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" 
                                       name="paralegal_permissions[]" 
                                       value="{{ $perm->slug }}" 
                                       {{ in_array($perm->slug, $paralegalPermSlugs) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-[#093225] focus:ring-[#093225] w-4 h-4 cursor-pointer">
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-white border-t border-[#f3f4f6]">
            <button type="submit" class="bg-[#093225] hover:bg-[#1b4332] text-white text-[13px] font-medium px-4 py-2 rounded-md shadow-xs transition-colors cursor-pointer">
                Save default permissions
            </button>
        </div>
    </form>

    <!-- 2. Default Document Categories Card -->
    <div class="bg-white rounded-lg border border-[#e5e7eb] shadow-xs p-6">
        <div class="mb-4">
            <h2 class="text-[14px] font-semibold text-[#1a1a1a]">Default document categories</h2>
            <p class="text-[12px] text-[#6b7280] mt-0.5">Order controls how categories are listed in upload forms.</p>
        </div>

        <!-- Categories List -->
        <div class="flex flex-col gap-2 mb-6">
            @foreach($categories as $cat)
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.roles.categories.update', $cat->id) }}" method="POST" class="flex-1 flex items-center gap-3">
                    @csrf
                    @method('PUT')
                    <!-- Sort Order -->
                    <input type="number" 
                           name="sort_order" 
                           value="{{ $cat->sort_order }}" 
                           class="w-14 text-center text-[13px] px-2 py-1.5 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#374151]">
                    
                    <!-- Name -->
                    <input type="text" 
                           name="name" 
                           value="{{ $cat->name }}" 
                           class="w-52 text-[13px] px-3 py-1.5 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#111827]">
                    
                    <!-- Description -->
                    <input type="text" 
                           name="description" 
                           value="{{ $cat->description }}" 
                           class="flex-1 text-[13px] px-3 py-1.5 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225] text-[#4b5563]">

                    <!-- Save Button -->
                    <button type="submit" class="text-[12.5px] font-medium text-[#374151] hover:text-[#111827] px-3 py-1.5 border border-gray-300 hover:border-gray-400 rounded bg-white hover:bg-gray-50 transition-colors shadow-xs shrink-0 cursor-pointer">
                        Save
                    </button>
                </form>

                <!-- Remove Button -->
                <form action="{{ route('admin.roles.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Remove category {{ $cat->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[12.5px] text-[#ba1a1a] hover:underline px-2 py-1.5 shrink-0 cursor-pointer">
                        Remove
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <!-- Add Category Row -->
        <div class="pt-4 border-t border-[#f3f4f6]">
            <form action="{{ route('admin.roles.categories.store') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <div class="w-14"></div>
                <div class="w-52">
                    <input type="text" 
                           name="name" 
                           placeholder="e.g. Immigration filings" 
                           required
                           class="w-full text-[13px] px-3 py-1.5 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225]">
                </div>
                <div class="flex-1">
                    <input type="text" 
                           name="description" 
                           placeholder="e.g. USCIS forms and notices" 
                           class="w-full text-[13px] px-3 py-1.5 border border-gray-300 rounded focus:border-[#093225] focus:ring-[#093225]">
                </div>
                <button type="submit" class="text-[12.5px] font-medium text-[#374151] hover:text-[#111827] px-4 py-1.5 border border-gray-300 hover:border-gray-400 rounded bg-white hover:bg-gray-50 transition-colors shadow-xs shrink-0 cursor-pointer">
                    Add category
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
