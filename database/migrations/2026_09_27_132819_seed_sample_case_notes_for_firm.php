<?php

use App\Models\CaseNote;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $firm = Firm::first();
        if (! $firm) {
            return;
        }

        $user = User::where('firm_id', $firm->id)->whereIn('role', ['partner', 'associate'])->first() ?: User::first();
        $matters = Matter::where('firm_id', $firm->id)->get();

        if ($matters->isEmpty()) {
            return;
        }

        if (CaseNote::where('firm_id', $firm->id)->count() === 0) {
            CaseNote::create([
                'firm_id' => $firm->id,
                'matter_id' => $matters->first()?->id,
                'user_id' => $user?->id,
                'title' => 'Urgent: Section 9 Interim Injunction Written Synopsis Protocol',
                'body' => 'Senior Counsel consultation concluded. Verify bank guarantee invocations before tomorrow\'s bench listing. Supplementary synopsis to be tendered directly in court.',
                'type' => 'internal',
                'is_pinned' => true,
            ]);

            CaseNote::create([
                'firm_id' => $firm->id,
                'matter_id' => $matters->skip(1)->first()?->id ?? $matters->first()?->id,
                'user_id' => $user?->id,
                'title' => 'Registry Defect Memo & Financial Creditor Statement Reconciliation',
                'body' => 'NCLT Registry raised defect memo regarding ledger statements. Associate counsel to file supplementary rejoinder by Wednesday 2:00 PM.',
                'type' => 'internal',
                'is_pinned' => true,
            ]);

            CaseNote::create([
                'firm_id' => $firm->id,
                'matter_id' => $matters->skip(2)->first()?->id ?? $matters->first()?->id,
                'user_id' => $user?->id,
                'title' => 'Commercial Settlement Terms & Concurrence Record',
                'body' => 'Draft settlement parameters shared with client for concurrence. Clause 14 dispute resolution mechanism will govern outstanding payments.',
                'type' => 'client_visible',
                'is_pinned' => false,
            ]);
        }

        // 2. Ensure firm 1 has events for this week and coming week
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $comingWeekStart = Carbon::now()->addWeek()->startOfWeek();
        $comingWeekEnd = Carbon::now()->addWeek()->endOfWeek();

        if (Event::where('firm_id', $firm->id)->whereBetween('start_time', [$thisWeekStart, $thisWeekEnd])->count() === 0) {
            Event::create([
                'firm_id' => $firm->id,
                'matter_id' => $matters->first()?->id,
                'user_id' => $user?->id,
                'title' => 'Commercial Division Case Management & Pleadings Hearing',
                'event_type' => 'Court Hearing',
                'start_time' => Carbon::now()->startOfDay()->addHours(14),
                'end_time' => Carbon::now()->startOfDay()->addHours(15)->addMinutes(30),
                'location' => 'Courtroom 24, High Court of Delhi',
                'notes' => 'Directions hearing on production of original bank guarantee ledgers.',
            ]);
        }

        if (Event::where('firm_id', $firm->id)->whereBetween('start_time', [$comingWeekStart, $comingWeekEnd])->count() === 0) {
            Event::create([
                'firm_id' => $firm->id,
                'matter_id' => $matters->skip(1)->first()?->id ?? $matters->first()?->id,
                'user_id' => $user?->id,
                'title' => 'Statutory Notice & Rejoinder Filing Deadline',
                'event_type' => 'Filing Deadline',
                'is_statutory_deadline' => true,
                'start_time' => Carbon::now()->addWeek()->startOfWeek()->addDays(2)->addHours(16)->addMinutes(30),
                'end_time' => Carbon::now()->addWeek()->startOfWeek()->addDays(2)->addHours(17),
                'location' => 'Registry Block, High Court of Delhi',
                'notes' => 'Filing of verified reply affidavit along with annexures.',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
