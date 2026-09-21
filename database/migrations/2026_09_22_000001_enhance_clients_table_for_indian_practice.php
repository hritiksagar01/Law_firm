<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Make email nullable for non-email / illiterate clients
            $table->string('email')->nullable()->change();

            // Entity Classification: individual, joint, corporate, institution, partnership, proprietorship
            $table->string('category')->default('individual');

            // Digital Literacy & Mode: portal_online, assisted_offline
            $table->string('onboarding_mode')->default('portal_online');

            // Indian KYC - Individual
            $table->string('father_husband_name')->nullable(); // S/o, D/o, W/o (Court Standard)
            $table->string('gender')->nullable(); // male, female, other
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('occupation')->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('aadhaar_last_four', 4)->nullable(); // Masked Aadhaar (UIDAI compliance)
            $table->string('voter_id')->nullable();
            $table->string('passport_number')->nullable();

            // Indian KYC - Corporate / Institutional
            $table->string('cin', 21)->nullable(); // Corporate Identity Number
            $table->string('llpin', 8)->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('registration_number')->nullable(); // Society / Trust / ROF Reg
            $table->string('roc_jurisdiction')->nullable();

            // Detailed Address for Service & Indian Court Jurisdiction
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('Delhi');
            $table->string('pincode', 6)->nullable();
            $table->string('police_station')->nullable(); // Thana jurisdiction

            // Representation & Assisted Mode (For Illiterate, Minors, POA holders)
            $table->string('representation_mode')->default('self'); // self, poa_holder, next_friend_guardian, authorized_signatory, relative
            $table->string('representative_name')->nullable();
            $table->string('representative_relation')->nullable();
            $table->string('representative_phone')->nullable();
            $table->string('representative_email')->nullable();
            $table->string('poa_registration_number')->nullable();
            $table->date('poa_date')->nullable();
            $table->string('poa_sub_registrar_office')->nullable();

            // Portal Invitation Token Security
            $table->string('invitation_token', 64)->nullable()->unique();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->string('portal_status')->default('not_invited'); // not_invited, invited, active, offline_only, suspended
            $table->text('internal_intake_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'onboarding_mode',
                'father_husband_name',
                'gender',
                'date_of_birth',
                'age',
                'occupation',
                'pan',
                'aadhaar_last_four',
                'voter_id',
                'passport_number',
                'cin',
                'llpin',
                'gstin',
                'registration_number',
                'roc_jurisdiction',
                'address_line_1',
                'address_line_2',
                'city',
                'district',
                'state',
                'pincode',
                'police_station',
                'representation_mode',
                'representative_name',
                'representative_relation',
                'representative_phone',
                'representative_email',
                'poa_registration_number',
                'poa_date',
                'poa_sub_registrar_office',
                'invitation_token',
                'invitation_sent_at',
                'invitation_expires_at',
                'portal_status',
                'internal_intake_notes',
            ]);
        });
    }
};
