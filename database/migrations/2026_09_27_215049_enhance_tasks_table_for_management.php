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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_number')->nullable()->index()->after('id');
            $table->date('start_date')->nullable()->after('status');
            $table->date('completed_date')->nullable()->after('due_date');
            $table->json('tags')->nullable()->after('completed_date');
            $table->foreignId('related_document_id')->nullable()->constrained('documents')->nullOnDelete()->after('tags');
            $table->foreignId('related_client_id')->nullable()->constrained('clients')->nullOnDelete()->after('related_document_id');
            $table->string('related_party_name')->nullable()->after('related_client_id');
        });

        // Backfill existing tasks
        $tasks = DB::table('tasks')->get();
        foreach ($tasks as $task) {
            $year = $task->created_at ? substr($task->created_at, 0, 4) : date('Y');
            $taskNumber = sprintf('TSK-%s-%04d', $year, $task->id);

            $status = $task->status;
            if ($status === 'todo') {
                $status = 'not_started';
            }

            $priority = $task->priority;
            if ($priority === 'medium') {
                $priority = 'normal';
            }

            $completedDate = null;
            if ($status === 'completed') {
                $completedDate = $task->updated_at ? substr($task->updated_at, 0, 10) : date('Y-m-d');
            }

            // Derive related client from matter if matter exists
            $relatedClientId = null;
            if (! empty($task->matter_id)) {
                $matter = DB::table('matters')->where('id', $task->matter_id)->first();
                $relatedClientId = $matter?->client_id;
            }

            DB::table('tasks')->where('id', $task->id)->update([
                'task_number' => $taskNumber,
                'status' => $status,
                'priority' => $priority,
                'completed_date' => $completedDate,
                'related_client_id' => $relatedClientId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['related_document_id']);
            $table->dropForeign(['related_client_id']);
            $table->dropColumn([
                'task_number',
                'start_date',
                'completed_date',
                'tags',
                'related_document_id',
                'related_client_id',
                'related_party_name',
            ]);
        });
    }
};
