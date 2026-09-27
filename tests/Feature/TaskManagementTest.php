<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test staff can view tasks index with metrics and filtering.
     */
    public function test_staff_can_view_tasks_index_with_metrics_and_filters(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $response = $this->actingAs($staff)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Litigation Tasks &amp; Deadlines', false);
        $response->assertSee('Total Tasks');
        $response->assertSee('In Progress');
        $response->assertSee('Waiting / Blocked');
        $response->assertSee('Log Litigation Task');
    }

    /**
     * Test creating a task with all fields specified in requirements:
     * - Task ID, matter, title, description
     * - Assigned to, created by, priority, status
     * - Start date, due date, completed date
     * - Tags, related document, client, party
     */
    public function test_staff_can_create_litigation_task_with_full_specification(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);
        $assignee = User::factory()->create(['firm_id' => $firm->id, 'role' => 'associate']);

        $clientUser = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Godrej Consumer Products',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'CS(COMM)/880/2026',
            'title' => 'Godrej vs Regional Retail Syndicate',
            'status' => 'Open',
        ]);

        $document = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => $staff->id,
            'title' => 'Plaint & Injunction Application',
            'filename' => 'plaint_injunction.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'category' => 'Pleading',
            'version' => 1,
        ]);

        $postData = [
            'title' => 'Prepare List of Dates & Synopses for Interim Hearing',
            'description' => 'Extract chronological correspondence and tabulate index for Senior Counsel.',
            'matter_id' => $matter->id,
            'assigned_to' => $assignee->id,
            'priority' => Task::PRIORITY_CRITICAL,
            'status' => Task::STATUS_NOT_STARTED,
            'start_date' => '2026-09-28',
            'due_date' => '2026-10-02',
            'tags' => ['Hearing Prep', 'Court Filing', 'Pleading Draft'],
            'related_document_id' => $document->id,
            'related_client_id' => $client->id,
            'related_party_name' => 'Opposing Counsel: M/s Lex Chambers',
        ];

        $response = $this->actingAs($staff)->post(route('tasks.store'), $postData);

        $response->assertRedirect(route('tasks.index'));

        $task = Task::where('title', 'Prepare List of Dates & Synopses for Interim Hearing')->first();
        $this->assertNotNull($task);
        $this->assertStringStartsWith('TSK-', $task->task_number);
        $this->assertEquals($matter->id, $task->matter_id);
        $this->assertEquals($assignee->id, $task->assigned_to);
        $this->assertEquals($staff->id, $task->created_by);
        $this->assertEquals(Task::PRIORITY_CRITICAL, $task->priority);
        $this->assertEquals(Task::STATUS_NOT_STARTED, $task->status);
        $this->assertEquals('2026-09-28', $task->start_date->format('Y-m-d'));
        $this->assertEquals('2026-10-02', $task->due_date->format('Y-m-d'));
        $this->assertEquals(['Hearing Prep', 'Court Filing', 'Pleading Draft'], $task->tags);
        $this->assertEquals($document->id, $task->related_document_id);
        $this->assertEquals($client->id, $task->related_client_id);
        $this->assertEquals('Opposing Counsel: M/s Lex Chambers', $task->related_party_name);
    }

    /**
     * Test all 6 statuses: Not Started, In Progress, Waiting, Blocked, Completed, Cancelled
     * and completed_date management.
     */
    public function test_task_status_lifecycle_and_completed_date_management(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $task = Task::create([
            'firm_id' => $firm->id,
            'created_by' => $staff->id,
            'assigned_to' => $staff->id,
            'title' => 'Obtain Certified Copy of Court Order',
            'priority' => Task::PRIORITY_HIGH,
            'status' => Task::STATUS_NOT_STARTED,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->assertNull($task->completed_date);
        $this->assertEquals('Not Started', $task->status_label);

        // Transition: In Progress
        $this->actingAs($staff)->patch(route('tasks.status.update', $task->id), [
            'status' => Task::STATUS_IN_PROGRESS,
        ])->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $task->status);
        $this->assertNull($task->completed_date);

        // Transition: Waiting
        $this->actingAs($staff)->patch(route('tasks.status.update', $task->id), [
            'status' => Task::STATUS_WAITING,
        ])->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_WAITING, $task->status);

        // Transition: Blocked
        $this->actingAs($staff)->patch(route('tasks.status.update', $task->id), [
            'status' => Task::STATUS_BLOCKED,
        ])->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_BLOCKED, $task->status);

        // Transition: Completed (must auto set completed_date)
        $this->actingAs($staff)->patch(route('tasks.status.update', $task->id), [
            'status' => Task::STATUS_COMPLETED,
        ])->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->completed_date);

        // Transition: Cancelled (must clear completed_date)
        $this->actingAs($staff)->patch(route('tasks.status.update', $task->id), [
            'status' => Task::STATUS_CANCELLED,
        ])->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_CANCELLED, $task->status);
        $this->assertNull($task->completed_date);
    }

    /**
     * Test task toggle action toggles between completed and in_progress.
     */
    public function test_task_toggle_action(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $task = Task::create([
            'firm_id' => $firm->id,
            'created_by' => $staff->id,
            'assigned_to' => $staff->id,
            'title' => 'Serve Caveat Notice on Respondent',
            'priority' => Task::PRIORITY_NORMAL,
            'status' => Task::STATUS_IN_PROGRESS,
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($staff)->post(route('tasks.toggle', $task->id))->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->completed_date);

        // Toggle back
        $this->actingAs($staff)->post(route('tasks.toggle', $task->id))->assertRedirect();
        $task->refresh();
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $task->status);
        $this->assertNull($task->completed_date);
    }

    /**
     * Test task progress updates / comments.
     */
    public function test_task_comments_can_be_logged(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $task = Task::create([
            'firm_id' => $firm->id,
            'created_by' => $staff->id,
            'assigned_to' => $staff->id,
            'title' => 'File Valuation Report with Registry',
            'priority' => Task::PRIORITY_NORMAL,
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($staff)->post(route('tasks.comments.store', $task->id), [
            'comment' => 'Registry counter token number 44 obtained. Awaiting clearance.',
        ])->assertRedirect();

        $this->assertCount(1, $task->comments);
        $this->assertEquals('Registry counter token number 44 obtained. Awaiting clearance.', $task->comments->first()->comment);
    }
}
