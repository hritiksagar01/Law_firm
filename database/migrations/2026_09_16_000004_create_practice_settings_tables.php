<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Dynamic Practice Areas
        Schema::create('practice_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Firm Holidays & Court Vacations
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->boolean('is_court_vacation')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->longText('body_html');
            $table->json('variables')->nullable();
            $table->string('type')->default('general'); // client_welcome, hearing_alert, appointment_reminder, task_alert
            $table->timestamps();
        });

        // 4. Legal Notice & Letter Templates
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('legal_notice'); // legal_notice, cease_and_desist, retainer_agreement, affidavit
            $table->longText('body_html');
            $table->json('variables')->nullable();
            $table->timestamps();
        });

        // 5. Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('channel')->default('in_app'); // in_app, email, sms
            $table->string('trigger_event'); // hearing_scheduled, opinion_published, task_assigned
            $table->text('body');
            $table->json('variables')->nullable();
            $table->timestamps();
        });

        // 6. Activity Categories
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('color')->default('#9F8349');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 7. Practice Area Task Templates (predefined task workflows)
        Schema::create('practice_area_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('practice_area_id')->nullable()->constrained('practice_areas')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('default_priority')->default('medium'); // low, medium, high, urgent
            $table->integer('default_due_days')->default(7);
            $table->timestamps();
        });

        // 8. Case & Client ID Numbering Schemes
        Schema::create('id_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name'); // e.g. "Civil Suit Case Number", "Client Registration ID"
            $table->string('prefix')->default('HO');
            $table->string('format_mask')->default('{PREFIX}-{YYYY}-{SEQ}');
            $table->integer('next_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_types');
        Schema::dropIfExists('practice_area_tasks');
        Schema::dropIfExists('activity_categories');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('practice_areas');
    }
};
