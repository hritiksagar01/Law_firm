<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module')->nullable()->after('action_label');
            }
            if (! Schema::hasColumn('audit_logs', 'entity_type')) {
                $table->string('entity_type')->nullable()->after('record_type');
            }
            if (! Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            }
            if (! Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('entity_id');
            }
            if (! Schema::hasColumn('audit_logs', 'previous_value')) {
                $table->json('previous_value')->nullable()->after('description');
            }
            if (! Schema::hasColumn('audit_logs', 'new_value')) {
                $table->json('new_value')->nullable()->after('previous_value');
            }
            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        // Backfill UUIDs for any existing users
        foreach (User::whereNull('uuid')->cursor() as $existingUser) {
            $existingUser->updateQuietly(['uuid' => (string) Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['uuid', 'middle_name', 'username', 'mobile', 'last_login_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $cols = ['module', 'entity_type', 'entity_id', 'description', 'previous_value', 'new_value', 'user_agent'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('audit_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
