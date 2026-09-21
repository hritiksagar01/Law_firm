<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documents') && ! Schema::hasColumn('documents', 'is_client_visible')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->boolean('is_client_visible')->default(true)->after('privilege');
            });
        }

        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'primary_attorney_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('primary_attorney_id')->nullable()->after('firm_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents') && Schema::hasColumn('documents', 'is_client_visible')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('is_client_visible');
            });
        }

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'primary_attorney_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropConstrainedForeignId('primary_attorney_id');
            });
        }
    }
};
