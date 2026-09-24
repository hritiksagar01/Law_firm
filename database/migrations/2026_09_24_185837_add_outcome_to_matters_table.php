<?php

use App\Models\Matter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('matters', 'outcome')) {
            Schema::table('matters', function (Blueprint $table) {
                $table->string('outcome')->nullable();
            });
        }

        // Set realistic won/lost outcomes on sample matters for analytics
        try {
            $matters = Matter::all();
            if ($matters->isNotEmpty()) {
                foreach ($matters as $idx => $matter) {
                    if ($matter->status === 'closed' || $matter->stage === 'Resolved') {
                        $matter->update(['outcome' => ($idx % 3 === 0) ? 'lost' : 'won']);
                    } elseif ($idx % 5 === 0) {
                        $matter->update(['outcome' => 'won']);
                    }
                }
            }
        } catch (Throwable $e) {
            // Non-critical seeding fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropColumn('outcome');
        });
    }
};
