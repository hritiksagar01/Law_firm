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
        // 1. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->nullable()->constrained('firms')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. auth.login, document.downloaded, etc.
            $table->string('action_label'); // e.g. Signed in, Downloaded document
            $table->string('actor_name'); // e.g. Rowan Blake, Margaret Hartwell, Client portal user
            $table->string('actor_email')->nullable(); // e.g. admin@quire.example
            $table->string('record_type'); // e.g. User account, Document · matter 2026-0118
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 2. Sign-in History
        Schema::create('sign_in_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->nullable()->constrained('firms')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->boolean('is_client')->default(false);
            $table->string('result')->default('signed_in'); // signed_in, failed
            $table->string('failure_reason')->nullable(); // No such account, Wrong password
            $table->string('ip_address')->nullable();
            $table->string('device')->default('Chrome on Windows');
            $table->timestamps();
        });

        // 3. Outbound Notification Delivery Logs
        Schema::create('delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->nullable()->constrained('firms')->nullOnDelete();
            $table->string('channel')->default('email'); // email, sms
            $table->string('recipient');
            $table->string('notification');
            $table->string('provider')->default('None');
            $table->string('status')->default('logged_only'); // logged_only, sent, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_logs');
        Schema::dropIfExists('sign_in_histories');
        Schema::dropIfExists('audit_logs');
    }
};
