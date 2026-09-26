<?php

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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'practice_areas')) {
                $table->json('practice_areas')->nullable()->after('title');
            }
            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('practice_areas');
            }
            if (! Schema::hasColumn('users', 'office_location')) {
                $table->string('office_location')->nullable()->after('department');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Asia/Kolkata')->after('office_location');
            }
            if (! Schema::hasColumn('users', 'bar_number')) {
                $table->string('bar_number')->nullable()->after('timezone');
            }
            if (! Schema::hasColumn('users', 'jurisdiction')) {
                $table->string('jurisdiction')->nullable()->after('bar_number');
            }
            if (! Schema::hasColumn('users', 'admission_date')) {
                $table->date('admission_date')->nullable()->after('jurisdiction');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'practice_areas',
                'department',
                'office_location',
                'timezone',
                'bar_number',
                'jurisdiction',
                'admission_date',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
