@extends('layouts.app')

@section('title', 'Firm Practice Settings — ' . config('legal.app_name', 'Lawyer Workspace'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    activeTab: '{{ $tab ?? 'practice-areas' }}',
    addAreaModal: false,
    addHolidayModal: false,
    addLetterModal: false
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e3dc] pb-5">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-mono text-[#23493a] uppercase tracking-wider mb-1 font-semibold">
                <span class="material-symbols-outlined text-sm">settings</span>
                <span>Chambers Governance &amp; Configuration</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1a1a1a] tracking-tight">Firm Practice Settings</h1>
            <p class="text-xs text-[#646864] mt-0.5">Configure practice domains, court holiday calendars, automated legal notice templates, and file numbering masks.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-[#e5e3dc] text-xs font-medium text-[#1a1a1a] hover:bg-[#faf8f5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Chambers Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-[#e5e3dc] overflow-x-auto pb-2">
        <button @click="activeTab = 'practice-areas'" :class="activeTab === 'practice-areas' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">category</span>
            <span>Practice Areas</span>
        </button>
        <button @click="activeTab = 'holidays'" :class="activeTab === 'holidays' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">beach_access</span>
            <span>Court Vacations &amp; Holidays</span>
        </button>
        <button @click="activeTab = 'letters'" :class="activeTab === 'letters' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">history_edu</span>
            <span>Letter &amp; Notice Templates</span>
        </button>
        <button @click="activeTab = 'emails'" :class="activeTab === 'emails' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">mail</span>
            <span>Communication Templates</span>
        </button>
        <button @click="activeTab = 'numbering'" :class="activeTab === 'numbering' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">pin</span>
            <span>File &amp; Case Numbering</span>
        </button>
        <button @click="activeTab = 'firm-profile'" :class="activeTab === 'firm-profile' ? 'bg-[#23493a] text-white shadow-xs font-medium' : 'text-[#646864] hover:bg-white hover:text-[#1a1a1a] font-normal'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">business</span>
            <span>Chambers Profile</span>
        </button>
    </div>

    <!-- TAB 1: PRACTICE AREAS -->
    <div x-show="activeTab === 'practice-areas'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-sm text-[#1a1a1a]">Chambers Practice Areas</h3>
                <p class="text-xs text-[#646864]">Taxonomy used for categorizing matters, briefs, and client engagements.</p>
            </div>
            <button @click="addAreaModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-xs transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Add Practice Area</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-4">Area Name</th>
                        <th class="py-3 px-4">Taxonomy Code</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($practiceAreas as $pa)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#1a1a1a]">{{ $pa->name }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#23493a] font-bold">{{ $pa->code ?? 'GEN' }}</td>
                        <td class="py-3.5 px-4 text-[#646864] max-w-md">{{ $pa->description ?? 'Standard practice domain' }}</td>
                        <td class="py-3.5 px-4">
                            @if($pa->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Active</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-stone-100 text-stone-600 border border-stone-200">
                                <span>Inactive</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('settings.practice-areas.toggle', $pa->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-[11px] font-medium text-[#23493a] hover:underline">
                                    {{ $pa->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#646864]">No practice areas registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: COURT VACATIONS & HOLIDAYS -->
    <div x-show="activeTab === 'holidays'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-sm text-[#1a1a1a]">Court Vacations &amp; Gazetted Holidays</h3>
                <p class="text-xs text-[#646864]">Tracks court recess periods to prevent scheduling conflicts and highlight vacation benches.</p>
            </div>
            <button @click="addHolidayModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-xs transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Add Holiday / Recess</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e3dc] shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#faf8f5] border-b border-[#e5e3dc] text-[#646864] uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Holiday / Recess Title</th>
                        <th class="py-3 px-4">Classification</th>
                        <th class="py-3 px-4">Remarks</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e3dc]">
                    @forelse($holidays as $holiday)
                    <tr class="hover:bg-[#faf8f5]/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#1a1a1a]">
                            {{ \Carbon\Carbon::parse($holiday->date)->format('d M, Y (l)') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#1a1a1a]">{{ $holiday->name }}</td>
                        <td class="py-3.5 px-4">
                            @if($holiday->is_court_vacation)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                Court Vacation / Recess
                            </span>
                            @else
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#faf8f5] text-[#646864] border border-[#e5e3dc]">
                                Gazetted Holiday
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#646864]">{{ $holiday->description ?? 'Courts closed' }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('settings.holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Remove this holiday?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-700 text-xs hover:underline">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#646864]">No court holidays registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: LETTER & NOTICE TEMPLATES -->
    <div x-show="activeTab === 'letters'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-sm text-[#1a1a1a]">Legal Notices &amp; Pleadings Templates</h3>
                <p class="text-xs text-[#646864]">Pre-drafted legal notices under NI Act, IP cease &amp; desist, and advocate retainer agreements.</p>
            </div>
            <button @click="addLetterModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] shadow-xs transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>New Notice Template</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($letterTemplates as $template)
            <div class="p-5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h4 class="font-semibold text-xs text-[#1a1a1a]">{{ $template->name }}</h4>
                        <span class="font-mono text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-[#faf8f5] text-[#23493a] border border-[#e5e3dc]">
                            {{ str_replace('_', ' ', $template->category) }}
                        </span>
                    </div>
                    <div class="p-3 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-xs text-[#646864] max-h-36 overflow-y-auto mb-3">
                        {!! $template->body_html !!}
                    </div>
                </div>
                <div class="pt-3 border-t border-[#e5e3dc] flex items-center justify-between text-xs">
                    <span class="font-mono text-[10px] text-[#646864]">Dynamic placeholders supported</span>
                    <span class="text-[#23493a] font-medium text-xs">Ready for dispatch</span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-[#646864]">No letter templates found.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: COMMUNICATION TEMPLATES -->
    <div x-show="activeTab === 'emails'" x-cloak class="space-y-4">
        <div>
            <h3 class="font-bold text-sm text-[#1a1a1a]">Client Communications &amp; Cause List Alerts</h3>
            <p class="text-xs text-[#646864]">Automated email notifications dispatched to clients for hearing appearances and consultation bookings.</p>
        </div>

        <div class="space-y-4">
            @forelse($emailTemplates as $email)
            <div class="p-5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm">
                <form action="{{ route('settings.emails.update', $email->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-xs text-[#1a1a1a]">{{ $email->name }}</div>
                        <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-[#faf8f5] text-[#23493a] border border-[#e5e3dc] font-bold">
                            {{ $email->type }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Email Subject Line</label>
                        <input type="text" name="subject" value="{{ $email->subject }}" class="w-full h-8 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white text-xs focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Email Content Template (HTML &amp; Placeholders)</label>
                        <textarea name="body_html" rows="4" class="w-full p-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white text-xs font-mono focus:border-[#23493a] focus:outline-none">{{ $email->body_html }}</textarea>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[11px] text-[#646864]">Variables: @if($email->variables) @foreach($email->variables as $v) <code class="text-[#23493a]">&#123;&#123;{{ $v }}&#125;&#125;</code> @endforeach @endif</span>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#23493a] text-white text-xs font-medium hover:bg-[#1a382c] transition-all">Save Changes</button>
                    </div>
                </form>
            </div>
            @empty
            <div class="py-8 text-center text-[#646864]">No communication templates found.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 5: NUMBERING SCHEMES -->
    <div x-show="activeTab === 'numbering'" x-cloak class="space-y-4">
        <div>
            <h3 class="font-bold text-sm text-[#1a1a1a]">Automated ID &amp; File Numbering Formats</h3>
            <p class="text-xs text-[#646864]">Custom prefix masks and sequential counters for case files, clients, and legal opinions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($idTypes as $idType)
            <div class="p-5 rounded-xl bg-white border border-[#e5e3dc] shadow-sm">
                <form action="{{ route('settings.id-types.update', $idType->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="font-semibold text-xs text-[#1a1a1a]">{{ $idType->name }}</div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Prefix</label>
                        <input type="text" name="prefix" value="{{ $idType->prefix }}" class="w-full h-8 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white text-xs font-mono focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Mask Pattern</label>
                        <input type="text" name="format_mask" value="{{ $idType->format_mask }}" class="w-full h-8 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white text-xs font-mono focus:border-[#23493a] focus:outline-none"/>
                        <span class="text-[10px] text-[#646864] block mt-0.5">&#123;PREFIX&#125;-&#123;YYYY&#125;-&#123;SEQ&#125;</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Next Counter Sequence</label>
                        <input type="number" name="next_number" value="{{ $idType->next_number }}" class="w-full h-8 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white text-xs font-mono focus:border-[#23493a] focus:outline-none"/>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-2 rounded-lg bg-[#faf8f5] border border-[#e5e3dc] text-[#1a1a1a] text-xs font-medium hover:bg-white hover:border-[#23493a] hover:text-[#23493a] transition-all">
                            Update Numbering Mask
                        </button>
                    </div>
                </form>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-[#646864]">No numbering schemes configured.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 6: FIRM PROFILE -->
    <div x-show="activeTab === 'firm-profile'" x-cloak class="space-y-4">
        <div class="p-6 rounded-xl bg-white border border-[#e5e3dc] shadow-sm max-w-2xl">
            <h3 class="font-bold text-sm text-[#1a1a1a] mb-1">Chambers Legal Entity Profile</h3>
            <p class="text-xs text-[#646864] mb-4">Official chambers details used on cause lists, letterheads, and formal legal opinions.</p>

            <form action="{{ route('settings.firm.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Firm / Chambers Name</label>
                    <input type="text" name="name" value="{{ $firm->name ?? 'Lawyer Practice' }}" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none font-semibold text-xs"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Chambers Email</label>
                        <input type="email" name="email" value="{{ $firm->email ?? 'counsel@firm.com' }}" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none text-xs"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Contact Telephone</label>
                        <input type="text" name="phone" value="{{ $firm->phone ?? '+91 98765 43210' }}" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none font-mono text-xs"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Physical Chambers Address</label>
                    <textarea name="address" rows="3" class="w-full p-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none text-xs">{{ $firm->address ?? 'High Court Advocates Chambers' }}</textarea>
                </div>
                <div class="pt-3 border-t border-[#e5e3dc]">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#23493a] text-white font-medium hover:bg-[#1a382c] shadow-xs text-xs transition-all">
                        Save Chambers Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD PRACTICE AREA MODAL -->
    <div x-show="addAreaModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addAreaModal = false" class="bg-white rounded-xl shadow-xl border border-[#e5e3dc] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#e5e3dc] pb-3 mb-4">
                <h3 class="font-bold text-sm text-[#1a1a1a]">Add Practice Domain</h3>
                <button @click="addAreaModal = false" class="text-[#646864] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.practice-areas.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Area Name</label>
                    <input type="text" name="name" required placeholder="e.g. Insolvency &amp; Bankruptcy (IBC)" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Taxonomy Code</label>
                    <input type="text" name="code" placeholder="e.g. IBC" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white font-mono uppercase focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief scope of practice" class="w-full p-2.5 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#e5e3dc] flex justify-end gap-2">
                    <button type="button" @click="addAreaModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf8f5]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#23493a] text-white font-medium hover:bg-[#1a382c]">Create Practice Area</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD COURT HOLIDAY MODAL -->
    <div x-show="addHolidayModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addHolidayModal = false" class="bg-white rounded-xl shadow-xl border border-[#e5e3dc] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#e5e3dc] pb-3 mb-4">
                <h3 class="font-bold text-sm text-[#1a1a1a]">Add Court Holiday / Recess</h3>
                <button @click="addHolidayModal = false" class="text-[#646864] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.holidays.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Holiday / Recess Title</label>
                    <input type="text" name="name" required placeholder="e.g. Dussehra Court Recess" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Date</label>
                    <input type="date" name="date" required class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_court_vacation" name="is_court_vacation" class="rounded border-[#e5e3dc] text-[#23493a] focus:ring-[#23493a]"/>
                    <label for="is_court_vacation" class="text-xs text-[#1a1a1a] font-medium">Judicial Vacation / Recess (Urgent Benches only)</label>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Notes</label>
                    <input type="text" name="description" placeholder="Remarks" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#e5e3dc] flex justify-end gap-2">
                    <button type="button" @click="addHolidayModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf8f5]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#23493a] text-white font-medium hover:bg-[#1a382c]">Register Holiday</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD LETTER TEMPLATE MODAL -->
    <div x-show="addLetterModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addLetterModal = false" class="bg-white rounded-xl shadow-xl border border-[#e5e3dc] w-full max-w-lg p-6">
            <div class="flex items-center justify-between border-b border-[#e5e3dc] pb-3 mb-4">
                <h3 class="font-bold text-sm text-[#1a1a1a]">New Notice / Letter Template</h3>
                <button @click="addLetterModal = false" class="text-[#646864] hover:text-[#1a1a1a]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.letters.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Template Title</label>
                    <input type="text" name="name" required placeholder="e.g. Legal Notice for Specific Performance" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Category</label>
                    <select name="category" class="w-full h-9 px-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none">
                        <option value="legal_notice">Legal Notice</option>
                        <option value="cease_and_desist">Cease &amp; Desist</option>
                        <option value="retainer_agreement">Advocate Retainer Agreement</option>
                        <option value="affidavit">Affidavit of Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#646864] mb-1">Draft Content (HTML supported)</label>
                    <textarea name="body_html" rows="5" required placeholder="Enter formal legal text..." class="w-full p-3 rounded-lg border border-[#e5e3dc] bg-[#faf8f5] focus:bg-white focus:border-[#23493a] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#e5e3dc] flex justify-end gap-2">
                    <button type="button" @click="addLetterModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#e5e3dc] text-[#1a1a1a] hover:bg-[#faf8f5]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#23493a] text-white font-medium hover:bg-[#1a382c]">Save Notice Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
