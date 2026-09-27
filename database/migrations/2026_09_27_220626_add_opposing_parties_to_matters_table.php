<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->string('opposing_party')->nullable()->after('client_id');
            $table->string('opposing_counsel')->nullable()->after('opposing_party');
        });

        // Derive opposing party from matter title if it contains " v " or " vs "
        $matters = DB::table('matters')->get();
        foreach ($matters as $matter) {
            $title = $matter->title;
            $opposingParty = null;
            if (preg_match('/(?:vs\.?|v\.?)\s+(.+)$/i', $title, $matches)) {
                $opposingParty = trim($matches[1]);
            }

            if ($opposingParty) {
                DB::table('matters')->where('id', $matter->id)->update([
                    'opposing_party' => $opposingParty,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropColumn(['opposing_party', 'opposing_counsel']);
        });
    }
};
