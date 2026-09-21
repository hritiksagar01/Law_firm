<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            if (! Schema::hasColumn('firms', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('firms', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('email');
            }
            if (! Schema::hasColumn('firms', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('contact_name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'surname')) {
                $table->string('surname')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'secondary_email')) {
                $table->string('secondary_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'phone_type')) {
                $table->string('phone_type')->default('Home')->after('phone');
            }
            if (! Schema::hasColumn('users', 'id_type')) {
                $table->string('id_type')->nullable()->after('phone_type');
            }
            if (! Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable()->after('id_type');
            }
            if (! Schema::hasColumn('users', 'id_expiration')) {
                $table->date('id_expiration')->nullable()->after('id_number');
            }
            if (! Schema::hasColumn('users', 'id_document_path')) {
                $table->string('id_document_path')->nullable()->after('id_expiration');
            }
            if (! Schema::hasColumn('users', 'street')) {
                $table->string('street')->nullable()->after('id_document_path');
            }
            if (! Schema::hasColumn('users', 'user_city')) {
                $table->string('user_city')->nullable()->after('street');
            }
            if (! Schema::hasColumn('users', 'user_state')) {
                $table->string('user_state')->nullable()->after('user_city');
            }
            if (! Schema::hasColumn('users', 'user_country')) {
                $table->string('user_country')->nullable()->after('user_state');
            }
            if (! Schema::hasColumn('users', 'user_postal_code')) {
                $table->string('user_postal_code')->nullable()->after('user_country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $cols = ['display_name', 'contact_name', 'logo_path'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('firms', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'first_name',
                'surname',
                'secondary_email',
                'phone_type',
                'id_type',
                'id_number',
                'id_expiration',
                'id_document_path',
                'street',
                'user_city',
                'user_state',
                'user_country',
                'user_postal_code',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
