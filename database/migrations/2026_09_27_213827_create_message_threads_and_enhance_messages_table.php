<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create message_threads table
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->string('thread_number')->nullable()->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained('matters')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->string('thread_type')->default('general_matter_communication'); // client_communication, document_request, general_matter_communication, internal_team
            $table->boolean('is_internal')->default(false); // CRITICAL: Strict technical database separation
            $table->string('status')->default('open'); // open, closed, archived
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['matter_id', 'thread_type']);
            $table->index(['matter_id', 'is_internal']);
            $table->index(['firm_id', 'is_internal']);
        });

        // 2. Enhance messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('thread_id')->nullable()->after('matter_id')->constrained('message_threads')->cascadeOnDelete();
            $table->string('message_number')->nullable()->after('id');
            $table->foreignId('recipient_id')->nullable()->after('sender_id')->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable()->after('recipient_id');
            $table->string('status')->default('sent')->after('body'); // draft, sent, delivered, read
            $table->timestamp('sent_at')->nullable()->after('status');
            $table->boolean('is_internal')->default(false)->after('is_privileged'); // Technical DB authorization flag
            $table->string('attachment_path')->nullable()->after('is_internal');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_name');
            $table->string('attachment_mime')->nullable()->after('attachment_size');

            $table->index(['thread_id', 'is_internal']);
            $table->index(['matter_id', 'is_internal']);
        });

        // 3. Create message_attachments table for multi-file support
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->string('sha256')->nullable();
            $table->timestamps();
        });

        // 4. Backfill existing messages into default threads
        $matterIds = DB::table('messages')->whereNull('thread_id')->distinct()->pluck('matter_id');
        foreach ($matterIds as $matterId) {
            $matter = DB::table('matters')->where('id', $matterId)->first();
            if ($matter) {
                $firstMsg = DB::table('messages')->where('matter_id', $matterId)->orderBy('id')->first();
                $threadId = DB::table('message_threads')->insertGetId([
                    'thread_number' => 'THR-'.str_pad($matterId, 4, '0', STR_PAD_LEFT).'-01',
                    'firm_id' => $matter->firm_id,
                    'matter_id' => $matterId,
                    'client_id' => $matter->client_id,
                    'created_by' => $firstMsg?->sender_id ?? 1,
                    'subject' => 'General Matter Communication',
                    'thread_type' => 'general_matter_communication',
                    'is_internal' => false,
                    'status' => 'open',
                    'last_message_at' => $firstMsg?->created_at ?? now(),
                    'created_at' => $firstMsg?->created_at ?? now(),
                    'updated_at' => now(),
                ]);

                DB::table('messages')
                    ->where('matter_id', $matterId)
                    ->whereNull('thread_id')
                    ->update([
                        'thread_id' => $threadId,
                        'message_number' => DB::raw("'MSG-' || id"),
                        'status' => 'delivered',
                        'sent_at' => DB::raw('created_at'),
                        'is_internal' => false,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['thread_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropColumn([
                'thread_id',
                'message_number',
                'recipient_id',
                'subject',
                'status',
                'sent_at',
                'is_internal',
                'attachment_path',
                'attachment_name',
                'attachment_size',
                'attachment_mime',
            ]);
        });

        Schema::dropIfExists('message_threads');
    }
};
