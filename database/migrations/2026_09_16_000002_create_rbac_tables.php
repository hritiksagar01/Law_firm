<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->nullable()->constrained('firms')->cascadeOnDelete();
            $table->string('name'); // e.g. "Firm Administrator", "Senior Advocate"
            $table->string('slug'); // e.g. "admin", "lawyer", "paralegal", "support_staff", "client"
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false); // system roles cannot be deleted
            $table->timestamps();

            $table->unique(['firm_id', 'slug']);
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Create Matters"
            $table->string('slug')->unique(); // e.g. "matters.create"
            $table->string('module'); // e.g. "Matters", "Opinions", "Clients"
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission pivot
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // 4. User Groups (Practice groups / departments e.g. "Litigation Practice Group")
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->default('#9F8349');
            $table->timestamps();
        });

        // 5. User-Group pivot
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_group_id', 'user_id']);
        });

        // 6. Group-Permission pivot (groups can grant additional permissions)
        Schema::create('group_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_group_id', 'permission_id']);
        });

        // 7. Add role_id and status to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('firm_id')->constrained('roles')->nullOnDelete();
            $table->string('status')->default('active')->after('role'); // active, suspended, inactive
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'status']);
        });

        Schema::dropIfExists('group_permission');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
