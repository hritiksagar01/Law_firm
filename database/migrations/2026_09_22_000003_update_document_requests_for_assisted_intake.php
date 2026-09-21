<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->boolean('is_assisted_submission')->default(false);
            $table->foreignId('assisted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropForeign(['assisted_by_user_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'is_assisted_submission',
                'assisted_by_user_id',
                'rejection_reason',
                'reviewed_at',
                'reviewed_by',
            ]);
        });
    }
};
