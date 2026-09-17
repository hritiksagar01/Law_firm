<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->string('website')->nullable()->after('phone');
            $table->string('status')->default('active')->after('currency'); // active, inactive, suspended
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->default('India')->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->string('registration_number')->nullable()->after('postal_code');
            $table->string('tax_number')->nullable()->after('registration_number');
            $table->text('notes')->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'status',
                'city',
                'state',
                'country',
                'postal_code',
                'registration_number',
                'tax_number',
                'notes',
            ]);
        });
    }
};
