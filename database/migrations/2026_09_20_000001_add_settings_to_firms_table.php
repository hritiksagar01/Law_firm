<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            if (! Schema::hasColumn('firms', 'timezone')) {
                $table->string('timezone')->default('America/New_York')->after('country');
            }
            if (! Schema::hasColumn('firms', 'accent_color')) {
                $table->string('accent_color')->default('#24503f')->after('timezone');
            }
            if (! Schema::hasColumn('firms', 'allow_client_signup')) {
                $table->boolean('allow_client_signup')->default(true)->after('accent_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('firms', 'timezone')) {
                $columnsToDrop[] = 'timezone';
            }
            if (Schema::hasColumn('firms', 'accent_color')) {
                $columnsToDrop[] = 'accent_color';
            }
            if (Schema::hasColumn('firms', 'allow_client_signup')) {
                $columnsToDrop[] = 'allow_client_signup';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
