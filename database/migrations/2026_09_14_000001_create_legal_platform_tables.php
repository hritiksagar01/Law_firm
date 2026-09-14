<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Law Firms (Multi-tenancy)
        Schema::create('firms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('practice_areas')->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('default_hourly_rate', 10, 2)->default(450.00);
            $table->timestamps();
        });

        // 2. Update Users table with firm & role fields
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('firm_id')->nullable()->constrained('firms')->nullOnDelete();
            $table->string('role')->default('associate'); // superadmin, partner, associate, paralegal, finance, client
            $table->string('title')->nullable(); // e.g. "Senior Litigation Partner"
            $table->decimal('hourly_rate', 10, 2)->nullable()->default(450.00);
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        // 3. Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('corporate'); // individual, corporate
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('address')->nullable();
            $table->decimal('trust_balance', 12, 2)->default(0.00);
            $table->string('status')->default('active'); // active, inactive, conflict
            $table->timestamps();
        });

        // 4. Matters (Cases)
        Schema::create('matters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('case_number')->unique(); // e.g. HO-2026-0042
            $table->string('title'); // e.g. Marsh v. Castellan
            $table->string('practice_area')->default('Litigation');
            $table->string('court_name')->nullable();
            $table->string('judge_name')->nullable();
            $table->string('stage')->default('Discovery'); // Intake, Pleadings, Discovery, Pre-Trial, Trial, Appeal, Closed
            $table->string('status')->default('active'); // active, pending, settled, closed
            $table->foreignId('lead_attorney_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('billing_type')->default('hourly'); // hourly, flat_fee, contingency
            $table->decimal('budget', 12, 2)->nullable();
            $table->date('opened_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->timestamps();
        });

        // 5. Matter Users (Team assignment)
        Schema::create('matter_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('counsel');
            $table->timestamps();
        });

        // 6. Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->default('application/pdf');
            $table->string('sha256')->nullable();
            $table->string('category')->default('Pleadings'); // Pleadings, Court Orders, Discovery, Evidence, Work Product, Contracts
            $table->string('privilege')->default('Confidential'); // Public, Confidential, Attorney-Client Privileged
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 7. Time Entries (Stopwatch / Billing logs)
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('hours', 6, 2)->default(0.00);
            $table->decimal('rate', 10, 2)->default(450.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('activity_code')->default('L120'); // LEDES / UTBMS codes
            $table->string('activity_name')->default('Analysis & Strategy');
            $table->text('narrative')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->string('status')->default('unbilled'); // unbilled, billed, paid, written_off
            $table->date('entry_date');
            $table->timestamps();
        });

        // 8. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('invoice_number')->unique(); // e.g. INV-2026-0089
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->string('status')->default('sent'); // draft, sent, paid, overdue
            $table->timestamps();
        });

        // 9. Events & Court Docket
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('event_type')->default('Court Hearing'); // Court Hearing, Deposition, Filing Deadline, Client Meeting
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_statutory_deadline')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('todo'); // todo, in_progress, completed
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // 11. Real-time Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_privileged')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('events');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('matter_user');
        Schema::dropIfExists('matters');
        Schema::dropIfExists('clients');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['firm_id']);
            $table->dropColumn([
                'firm_id', 'role', 'title', 'hourly_rate', 'phone',
                'avatar_url', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'
            ]);
        });
        Schema::dropIfExists('firms');
    }
};
