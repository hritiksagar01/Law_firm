<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('default_document_categories')) {
            Schema::create('default_document_categories', function (Blueprint $table) {
                $table->id();
                $table->integer('sort_order')->default(0);
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('default_document_categories');
        Schema::dropIfExists('platform_settings');
    }
};
