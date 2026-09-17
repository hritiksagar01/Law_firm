@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    activeTab: '{{ $tab ?? 'practice-areas' }}',
    addAreaModal: false,
    addHolidayModal: false,
    addLetterModal: false
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#EFECE6] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#9F8349] uppercase tracking-wider mb-1">
                <span class="material-symbols-outlined text-sm">settings</span>
                <span>Chambers Governance &amp; Configuration</span>
            </div>
            <h1 class="text-3xl font-serif font-semibold text-[#222222] tracking-tight">Firm Practice Settings</h1>
            <p class="text-sm text-[#766A5E] mt-1">Configure practice domains, court holiday calendars, automated legal notice templates, and file numbering masks.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#EFECE6] text-xs font-medium text-[#554D45] hover:bg-[#FAF8F5] transition-all shadow-xs">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Chambers Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-[#EFECE6] overflow-x-auto pb-2">
        <button @click="activeTab = 'practice-areas'" :class="activeTab === 'practice-areas' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">category</span>
            <span>Practice Areas</span>
        </button>
        <button @click="activeTab = 'holidays'" :class="activeTab === 'holidays' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">beach_access</span>
            <span>Court Vacations &amp; Holidays</span>
        </button>
        <button @click="activeTab = 'letters'" :class="activeTab === 'letters' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">history_edu</span>
            <span>Letter &amp; Notice Templates</span>
        </button>
        <button @click="activeTab = 'emails'" :class="activeTab === 'emails' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">mail</span>
            <span>Communication Templates</span>
        </button>
        <button @click="activeTab = 'numbering'" :class="activeTab === 'numbering' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">pin</span>
            <span>File &amp; Case Numbering</span>
        </button>
        <button @click="activeTab = 'firm-profile'" :class="activeTab === 'firm-profile' ? 'bg-[#9F8349] text-white shadow-xs font-semibold' : 'text-[#554D45] hover:bg-white hover:text-[#9F8349]'" class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">business</span>
            <span>Chambers Profile</span>
        </button>
    </div>

    <!-- TAB 1: PRACTICE AREAS -->
    <div x-show="activeTab === 'practice-areas'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-serif font-semibold text-base text-[#222222]">Chambers Practice Areas</h3>
                <p class="text-xs text-[#766A5E]">Taxonomy used for categorizing matters, briefs, and client engagements.</p>
            </div>
            <button @click="addAreaModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Add Practice Area</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Area Name</th>
                        <th class="py-3 px-4 font-semibold">Taxonomy Code</th>
                        <th class="py-3 px-4 font-semibold">Description</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($practiceAreas as $pa)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $pa->name }}</td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-[#9F8349] font-bold">{{ $pa->code ?? 'GEN' }}</td>
                        <td class="py-3.5 px-4 text-[#554D45] max-w-md">{{ $pa->description ?? 'Standard practice domain' }}</td>
                        <td class="py-3.5 px-4">
                            @if($pa->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Active</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-100 text-stone-600 border border-stone-200">
                                <span>Inactive</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('settings.practice-areas.toggle', $pa->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-[11px] font-semibold text-[#9F8349] hover:underline">
                                    {{ $pa->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#766A5E]">No practice areas registered.</td>
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
                <h3 class="font-serif font-semibold text-base text-[#222222]">Court Vacations &amp; Gazetted Holidays</h3>
                <p class="text-xs text-[#766A5E]">Tracks court recess periods to prevent scheduling conflicts and highlight vacation benches.</p>
            </div>
            <button @click="addHolidayModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Add Holiday / Recess</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFECE6] shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] border-b border-[#EFECE6] text-[#766A5E] font-mono uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Holiday / Recess Title</th>
                        <th class="py-3 px-4 font-semibold">Classification</th>
                        <th class="py-3 px-4 font-semibold">Remarks</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFECE6]">
                    @forelse($holidays as $holiday)
                    <tr class="hover:bg-[#FAF8F5] transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#222222]">
                            {{ \Carbon\Carbon::parse($holiday->date)->format('d M, Y (l)') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#222222]">{{ $holiday->name }}</td>
                        <td class="py-3.5 px-4">
                            @if($holiday->is_court_vacation)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                Court Vacation / Recess
                            </span>
                            @else
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-[#FAF8F5] text-[#554D45] border border-[#EAE4DC]">
                                Gazetted Holiday
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[#554D45]">{{ $holiday->description ?? 'Courts closed' }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('settings.holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Remove this holiday?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 text-xs hover:underline">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#766A5E]">No court holidays registered.</td>
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
                <h3 class="font-serif font-semibold text-base text-[#222222]">Legal Notices &amp; Pleadings Templates</h3>
                <p class="text-xs text-[#766A5E]">Pre-drafted legal notices under NI Act, IP cease &amp; desist, and advocate retainer agreements.</p>
            </div>
            <button @click="addLetterModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36] shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>New Notice Template</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($letterTemplates as $template)
            <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h4 class="font-semibold text-sm text-[#222222]">{{ $template->name }}</h4>
                        <span class="font-mono text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-[#FAF8F5] text-[#9F8349] border border-[#EAE4DC]">
                            {{ str_replace('_', ' ', $template->category) }}
                        </span>
                    </div>
                    <div class="p-3 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-xs text-[#554D45] font-serif max-h-36 overflow-y-auto mb-3">
                        {!! $template->body_html !!}
                    </div>
                </div>
                <div class="pt-3 border-t border-[#FAF8F5] flex items-center justify-between text-xs">
                    <span class="font-mono text-[10px] text-[#766A5E]">Dynamic placeholders supported</span>
                    <span class="text-[#9F8349] font-medium">Ready for dispatch</span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-[#766A5E]">No letter templates found.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: COMMUNICATION TEMPLATES -->
    <div x-show="activeTab === 'emails'" x-cloak class="space-y-4">
        <div>
            <h3 class="font-serif font-semibold text-base text-[#222222]">Client Communications &amp; Cause List Alerts</h3>
            <p class="text-xs text-[#766A5E]">Automated email notifications dispatched to clients for hearing appearances and consultation bookings.</p>
        </div>

        <div class="space-y-4">
            @forelse($emailTemplates as $email)
            <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
                <form action="{{ route('settings.emails.update', $email->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-sm text-[#222222]">{{ $email->name }}</div>
                        <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded bg-[#FAF8F5] text-[#9F8349] border border-[#EAE4DC] font-bold">
                            {{ $email->type }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Email Subject Line</label>
                        <input type="text" name="subject" value="{{ $email->subject }}" class="w-full h-8 px-3 rounded-lg border border-[#EAE4DC] text-xs focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Email Content Template (HTML &amp; Placeholders)</label>
                        <textarea name="body_html" rows="4" class="w-full p-3 rounded-lg border border-[#EAE4DC] text-xs font-mono focus:border-[#9F8349] focus:outline-none">{{ $email->body_html }}</textarea>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[11px] text-[#766A5E]">Variables: @if($email->variables) @foreach($email->variables as $v) <code class="text-[#9F8349]">&#123;&#123;{{ $v }}&#125;&#125;</code> @endforeach @endif</span>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#9F8349] text-white text-xs font-semibold hover:bg-[#856C36]">Save Changes</button>
                    </div>
                </form>
            </div>
            @empty
            <div class="py-8 text-center text-[#766A5E]">No communication templates found.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 5: NUMBERING SCHEMES -->
    <div x-show="activeTab === 'numbering'" x-cloak class="space-y-4">
        <div>
            <h3 class="font-serif font-semibold text-base text-[#222222]">Automated ID &amp; File Numbering Formats</h3>
            <p class="text-xs text-[#766A5E]">Custom prefix masks and sequential counters for case files, clients, and legal opinions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($idTypes as $idType)
            <div class="p-5 rounded-xl bg-white border border-[#EFECE6] shadow-xs">
                <form action="{{ route('settings.id-types.update', $idType->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="font-semibold text-sm text-[#222222]">{{ $idType->name }}</div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Prefix</label>
                        <input type="text" name="prefix" value="{{ $idType->prefix }}" class="w-full h-8 px-3 rounded-lg border border-[#EAE4DC] text-xs font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Mask Pattern</label>
                        <input type="text" name="format_mask" value="{{ $idType->format_mask }}" class="w-full h-8 px-3 rounded-lg border border-[#EAE4DC] text-xs font-mono focus:border-[#9F8349] focus:outline-none"/>
                        <span class="text-[10px] text-[#766A5E] block mt-0.5">&#123;PREFIX&#125;-&#123;YYYY&#125;-&#123;SEQ&#125;</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Next Counter Sequence</label>
                        <input type="number" name="next_number" value="{{ $idType->next_number }}" class="w-full h-8 px-3 rounded-lg border border-[#EAE4DC] text-xs font-mono focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-2 rounded-lg bg-[#FAF8F5] border border-[#EAE4DC] text-[#222222] text-xs font-semibold hover:bg-[#F8F4EE] hover:text-[#9F8349] transition-colors">
                            Update Numbering Mask
                        </button>
                    </div>
                </form>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-[#766A5E]">No numbering schemes configured.</div>
            @endforelse
        </div>
    </div>

    <!-- TAB 6: FIRM PROFILE -->
    <div x-show="activeTab === 'firm-profile'" x-cloak class="space-y-4">
        <div class="p-6 rounded-xl bg-white border border-[#EFECE6] shadow-xs max-w-2xl">
            <h3 class="font-serif font-semibold text-base text-[#222222] mb-1">Chambers Legal Entity Profile</h3>
            <p class="text-xs text-[#766A5E] mb-4">Official chambers details used on cause lists, letterheads, and formal legal opinions.</p>

            <form action="{{ route('settings.firm.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Firm / Chambers Name</label>
                    <input type="text" name="name" value="{{ $firm->name ?? 'Vennamraj Associates' }}" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-semibold"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Chambers Email</label>
                        <input type="email" name="email" value="{{ $firm->email ?? 'counsel@vennamraj.com' }}" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Contact Telephone</label>
                        <input type="text" name="phone" value="{{ $firm->phone ?? '+91 98765 43210' }}" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none font-mono"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Physical Chambers Address</label>
                    <textarea name="address" rows="3" class="w-full p-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">{{ $firm->address ?? 'High Court Advocates Chambers, Hyderabad, Telangana' }}</textarea>
                </div>
                <div class="pt-3 border-t border-[#FAF8F5]">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36] shadow-xs">
                        Save Chambers Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD PRACTICE AREA MODAL -->
    <div x-show="addAreaModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addAreaModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Add Practice Domain</h3>
                <button @click="addAreaModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.practice-areas.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Area Name</label>
                    <input type="text" name="name" required placeholder="e.g. Insolvency &amp; Bankruptcy (IBC)" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Taxonomy Code</label>
                    <input type="text" name="code" placeholder="e.g. IBC" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] font-mono uppercase focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief scope of practice" class="w-full p-2.5 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="addAreaModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Create Practice Area</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD COURT HOLIDAY MODAL -->
    <div x-show="addHolidayModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addHolidayModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-md p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">Add Court Holiday / Recess</h3>
                <button @click="addHolidayModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.holidays.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Holiday / Recess Title</label>
                    <input type="text" name="name" required placeholder="e.g. Dussehra Court Recess" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Date</label>
                    <input type="date" name="date" required class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_court_vacation" name="is_court_vacation" class="rounded border-[#EAE4DC] text-[#9F8349] focus:ring-[#9F8349]"/>
                    <label for="is_court_vacation" class="text-xs text-[#222222] font-medium">Judicial Vacation / Recess (Urgent Benches only)</label>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Notes</label>
                    <input type="text" name="description" placeholder="Remarks" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="addHolidayModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Register Holiday</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD LETTER TEMPLATE MODAL -->
    <div x-show="addLetterModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.away="addLetterModal = false" class="bg-white rounded-xl shadow-xl border border-[#EFECE6] w-full max-w-lg p-6">
            <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3 mb-4">
                <h3 class="font-serif font-bold text-base text-[#222222]">New Notice / Letter Template</h3>
                <button @click="addLetterModal = false" class="text-[#766A5E] hover:text-[#222222]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('settings.letters.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Template Title</label>
                    <input type="text" name="name" required placeholder="e.g. Legal Notice for Specific Performance" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Category</label>
                    <select name="category" class="w-full h-9 px-3 rounded-lg border border-[#EAE4DC] focus:border-[#9F8349] focus:outline-none">
                        <option value="legal_notice">Legal Notice</option>
                        <option value="cease_and_desist">Cease &amp; Desist</option>
                        <option value="retainer_agreement">Advocate Retainer Agreement</option>
                        <option value="affidavit">Affidavit of Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-[#766A5E] mb-1">Draft Content (HTML supported)</label>
                    <textarea name="body_html" rows="5" required placeholder="Enter formal legal text..." class="w-full p-3 rounded-lg border border-[#EAE4DC] font-serif focus:border-[#9F8349] focus:outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-[#EFECE6] flex justify-end gap-2">
                    <button type="button" @click="addLetterModal = false" class="px-3.5 py-1.5 rounded-lg border border-[#EAE4DC] text-[#554D45]">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#9F8349] text-white font-semibold hover:bg-[#856C36]">Save Notice Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
