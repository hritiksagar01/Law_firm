<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Enhance clients table: lifecycle statuses, conflict-check & intake status, referral ──
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'intake_status')) {
                $table->string('intake_status')->nullable()->after('status');
                // Values: pending, in_progress, completed, declined
            }
            if (! Schema::hasColumn('clients', 'conflict_check_status')) {
                $table->string('conflict_check_status')->nullable()->after('intake_status');
                // Values: not_checked, pending, clear, potential_conflict, conflict_identified, waiver_required, cleared, rejected
            }
            if (! Schema::hasColumn('clients', 'preferred_attorney_id')) {
                $table->foreignId('preferred_attorney_id')->nullable()->after('primary_attorney_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('clients', 'assigned_paralegal_id')) {
                $table->foreignId('assigned_paralegal_id')->nullable()->after('preferred_attorney_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('clients', 'referral_source')) {
                $table->string('referral_source')->nullable()->after('assigned_paralegal_id');
                // Values: walk_in, referral, website, social_media, court_appointed, bar_association, returning, other
            }
            if (! Schema::hasColumn('clients', 'client_type')) {
                $table->string('client_type')->nullable()->after('type');
                // Values: plaintiff, defendant, petitioner, respondent, appellant, complainant, accused, applicant, other
            }
        });

        // ── 2. Enhance matter_user pivot: access levels, dates, active flag ──
        Schema::table('matter_user', function (Blueprint $table) {
            if (! Schema::hasColumn('matter_user', 'access_level')) {
                $table->string('access_level')->default('read')->after('role');
                // Values: read, write, admin
            }
            if (! Schema::hasColumn('matter_user', 'assignment_date')) {
                $table->date('assignment_date')->nullable()->after('access_level');
            }
            if (! Schema::hasColumn('matter_user', 'removal_date')) {
                $table->date('removal_date')->nullable()->after('assignment_date');
            }
            if (! Schema::hasColumn('matter_user', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('removal_date');
            }
        });

        // ── 3. Enhance documents: classification, types, tags, visibility ──
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'document_number')) {
                $table->string('document_number')->nullable()->after('id');
            }
            if (! Schema::hasColumn('documents', 'document_type')) {
                $table->string('document_type')->nullable()->after('category');
                // Pleading, Motion, Brief, Order, Judgment, Contract, Agreement, Correspondence,
                // Discovery, Deposition, Evidence, Exhibit, Affidavit, Declaration, Court Filing,
                // Notice, Legal Research, Memorandum, Client Document, Financial Document,
                // Medical Record, Photograph, Other
            }
            if (! Schema::hasColumn('documents', 'subcategory')) {
                $table->string('subcategory')->nullable()->after('document_type');
            }
            if (! Schema::hasColumn('documents', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('matter_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('documents', 'tags')) {
                $table->json('tags')->nullable()->after('privilege');
                // ['confidential', 'privileged', 'work-product', 'discovery', 'evidence', 'court-filing',
                //  'client-provided', 'opposing-counsel', 'draft', 'final', 'urgent', 'hearing',
                //  'settlement', 'contract', 'deposition', 'review-required', 'follow-up']
            }
            if (! Schema::hasColumn('documents', 'visibility')) {
                $table->string('visibility')->default('internal_only')->after('is_client_visible');
                // internal_only, attorney_only, legal_team, client_visible, specific_users, restricted
            }
            if (! Schema::hasColumn('documents', 'classification')) {
                $table->string('classification')->default('internal')->after('visibility');
                // public, internal, confidential, highly_confidential, attorney_client_privileged, attorney_work_product
            }
            if (! Schema::hasColumn('documents', 'document_status')) {
                $table->string('document_status')->default('draft')->after('classification');
                // draft, final, archived
            }
        });

        // ── 4. Enhance messages: read tracking ──
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('is_privileged');
            }
            if (! Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });

        // ── 5. Create conflict_checks table ──
        if (! Schema::hasTable('conflict_checks')) {
            Schema::create('conflict_checks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('check_number')->nullable()->index();
                $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
                $table->foreignId('matter_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('checked_at');
                $table->text('search_terms');
                $table->text('search_results')->nullable();
                $table->boolean('conflict_identified')->default(false);
                $table->text('conflict_description')->nullable();
                $table->text('resolution')->nullable();
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('review_date')->nullable();
                $table->string('status')->default('pending');
                // pending, clear, potential_conflict, conflict_identified, waiver_required, cleared_by_attorney, rejected
                $table->timestamps();
            });
        } else {
            if (! Schema::hasColumn('conflict_checks', 'check_number')) {
                Schema::table('conflict_checks', function (Blueprint $table) {
                    $table->string('check_number')->nullable()->index()->after('uuid');
                });
            }
        }

        // ── 6. Create matter_activities table (timeline) ──
        if (! Schema::hasTable('matter_activities')) {
            Schema::create('matter_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
                $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
                $table->string('activity_type');
                // matter_created, document_uploaded, document_viewed, document_downloaded,
                // document_shared, message_sent, task_created, task_completed,
                // note_added, status_changed, client_activity, calendar_event,
                // assignment_changed, conflict_check_completed
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['matter_id', 'created_at']);
                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('matter_activities');
        Schema::dropIfExists('conflict_checks');

        Schema::table('messages', function (Blueprint $table) {
            $columns = ['is_read', 'read_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            $columns = ['document_number', 'document_type', 'subcategory', 'tags', 'visibility', 'classification', 'document_status'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('documents', 'client_id')) {
                $table->dropConstrainedForeignId('client_id');
            }
        });

        Schema::table('matter_user', function (Blueprint $table) {
            $columns = ['access_level', 'assignment_date', 'removal_date', 'is_active'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('matter_user', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            $columns = ['intake_status', 'conflict_check_status', 'referral_source', 'client_type'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('clients', 'preferred_attorney_id')) {
                $table->dropConstrainedForeignId('preferred_attorney_id');
            }
            if (Schema::hasColumn('clients', 'assigned_paralegal_id')) {
                $table->dropConstrainedForeignId('assigned_paralegal_id');
            }
        });
    }
};
