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
        Schema::table('matters', function (Blueprint $table) {
            $table->string('matter_uuid')->nullable()->index()->after('id');
            $table->string('short_title')->nullable()->after('title');
            $table->json('matter_types')->nullable()->after('practice_area');
            $table->foreignId('supervising_attorney_id')->nullable()->after('lead_attorney_id')->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_paralegal_id')->nullable()->after('supervising_attorney_id')->constrained('users')->nullOnDelete();
            $table->text('description')->nullable()->after('status');
            $table->string('priority')->default('medium')->after('status'); // low, medium, high, urgent
            $table->string('court_type')->nullable()->after('court_name');
            $table->string('jurisdiction')->nullable()->after('court_type');
            $table->string('county')->nullable()->after('jurisdiction');
            $table->string('state')->nullable()->after('county');
            $table->string('docket_number')->nullable()->after('case_number');
            $table->json('judges')->nullable()->after('judge_name');
            $table->date('filing_date')->nullable()->after('closed_at');
            $table->date('hearing_date')->nullable()->after('filing_date');
            $table->date('trial_date')->nullable()->after('hearing_date');
            $table->text('statute_references')->nullable()->after('trial_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropForeign(['supervising_attorney_id']);
            $table->dropForeign(['assigned_paralegal_id']);
            $table->dropColumn([
                'matter_uuid',
                'short_title',
                'matter_types',
                'supervising_attorney_id',
                'assigned_paralegal_id',
                'description',
                'priority',
                'court_type',
                'jurisdiction',
                'county',
                'state',
                'docket_number',
                'judges',
                'filing_date',
                'hearing_date',
                'trial_date',
                'statute_references',
            ]);
        });
    }
};
