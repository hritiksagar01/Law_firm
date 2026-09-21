<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship'); // Co-petitioner, Co-respondent, Spouse, Co-owner, Director, Partner, Managing Trustee, Legal Heir
            $table->string('father_husband_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('aadhaar_last_four', 4)->nullable();
            $table->string('din', 8)->nullable(); // For Corporate Directors
            $table->boolean('is_primary_signatory')->default(false);
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_members');
    }
};
