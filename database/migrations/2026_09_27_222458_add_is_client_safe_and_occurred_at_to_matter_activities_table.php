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
        Schema::table('matter_activities', function (Blueprint $table) {
            $table->boolean('is_client_safe')->default(true)->after('metadata');
            $table->timestamp('occurred_at')->nullable()->after('is_client_safe');

            $table->index(['matter_id', 'is_client_safe']);
            $table->index(['matter_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matter_activities', function (Blueprint $table) {
            $table->dropIndex(['matter_id', 'is_client_safe']);
            $table->dropIndex(['matter_id', 'occurred_at']);
            $table->dropColumn(['is_client_safe', 'occurred_at']);
        });
    }
};
