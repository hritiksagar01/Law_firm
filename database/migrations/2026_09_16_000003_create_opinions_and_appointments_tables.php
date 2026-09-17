<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Legal Opinions
        Schema::create('opinions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('opinion_number')->unique(); // e.g. OP-2026-0001
            $table->string('title');
            $table->string('type')->default('legal_advice'); // legal_advice, case_strategy, document_review, statutory_compliance
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->text('recommendations')->nullable();
            $table->text('precedents_cited')->nullable();
            $table->string('status')->default('draft'); // draft, under_review, approved, published
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 2. Appointments & Consultations
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Attorney in charge
            $table->string('title');
            $table->string('type')->default('client_consultation'); // client_consultation, court_appearance, case_conference, mediation, briefing
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(45);
            $table->string('location')->nullable(); // Room 3 / Court Hall 4 / Virtual
            $table->string('status')->default('scheduled'); // scheduled, completed, adjourned, cancelled
            $table->text('notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
        });

        // 3. Note Categories
        Schema::create('note_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#9F8349');
            $table->string('icon')->default('sticky_note_2');
            $table->timestamps();
        });

        // 4. Case & Practice Notes
        Schema::create('case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('note_categories')->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_notes');
        Schema::dropIfExists('note_categories');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('opinions');
    }
};
