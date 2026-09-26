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
        Schema::table('document_versions', function (Blueprint $table) {
            $table->string('filename')->nullable()->after('version_number');
            $table->string('version_status', 50)->default('Draft')->after('filename');
            $table->text('change_description')->nullable()->after('change_summary');
            $table->foreignId('previous_version_id')->nullable()->after('change_description')->constrained('document_versions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropForeign(['previous_version_id']);
            $table->dropColumn(['previous_version_id', 'change_description', 'version_status', 'filename']);
        });
    }
};
