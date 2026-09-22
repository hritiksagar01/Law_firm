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
        Schema::table('clients', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index(['firm_id', 'created_at']);
            if (Schema::hasColumn('clients', 'primary_attorney_id')) {
                $table->index('primary_attorney_id');
            }
            if (Schema::hasColumn('clients', 'portal_status')) {
                $table->index('portal_status');
            }
        });

        Schema::table('matters', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index('client_id');
            $table->index(['firm_id', 'status']);
            if (Schema::hasColumn('matters', 'lead_attorney_id')) {
                $table->index('lead_attorney_id');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index('matter_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index(['firm_id', 'status']);
            $table->index('due_date');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index('matter_id');
            $table->index('start_time');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index('client_id');
            $table->index('status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->index('firm_id');
            $table->index('matter_id');
        });

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'firm_id')) {
                    $table->index('firm_id');
                }
                if (Schema::hasColumn('audit_logs', 'user_id')) {
                    $table->index('user_id');
                }
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['firm_id', 'created_at']);
            if (Schema::hasColumn('clients', 'primary_attorney_id')) {
                $table->dropIndex(['primary_attorney_id']);
            }
            if (Schema::hasColumn('clients', 'portal_status')) {
                $table->dropIndex(['portal_status']);
            }
        });

        Schema::table('matters', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['firm_id', 'status']);
            if (Schema::hasColumn('matters', 'lead_attorney_id')) {
                $table->dropIndex(['lead_attorney_id']);
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['matter_id']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['status']);
            $table->dropIndex(['firm_id', 'status']);
            $table->dropIndex(['due_date']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['matter_id']);
            $table->dropIndex(['start_time']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
            $table->dropIndex(['matter_id']);
        });

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'firm_id')) {
                    $table->dropIndex(['firm_id']);
                }
                if (Schema::hasColumn('audit_logs', 'user_id')) {
                    $table->dropIndex(['user_id']);
                }
                $table->dropIndex(['created_at']);
            });
        }
    }
};
