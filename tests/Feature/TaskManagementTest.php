<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_page_loads_successfully(): void
    {
        Task::factory()->pending()->create([
            'title' => 'Plan the daily stand-up',
            'priority' => TaskPriority::High,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Taskify | Team Task Board', false);
        $response->assertSee('Plan the daily stand-up');
        $response->assertSee('Task board');
    }

    public function test_can_create_task_through_dashboard_endpoint(): void
    {
        $payload = [
            'title' => 'Write project notes',
            'description' => 'Track daily work and priorities.',
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->postJson('/tasks', $payload);

        $response->assertCreated()
            ->assertJsonPath('message', 'Task created successfully.')
            ->assertJsonStructure(['summaryHtml', 'boardHtml']);

        $this->assertDatabaseHas('tasks', [
            'title' => $payload['title'],
            'status' => $payload['status'],
            'priority' => $payload['priority'],
        ]);
    }

    public function test_can_update_task_and_status(): void
    {
        $task = Task::factory()->pending()->create([
            'title' => 'Follow up with the team',
            'priority' => TaskPriority::Medium,
        ]);

        $updateResponse = $this->putJson("/tasks/{$task->id}", [
            'title' => 'Follow up with the product team',
            'description' => 'Keep progress visible.',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('message', 'Task updated successfully.');

        $statusResponse = $this->patchJson("/tasks/{$task->id}/status", [
            'status' => TaskStatus::Completed->value,
        ]);

        $statusResponse->assertOk()
            ->assertJsonPath('message', 'Task status updated.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Follow up with the product team',
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
        ]);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Remove old task',
        ]);

        $response = $this->deleteJson("/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Task deleted successfully.');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_validation_errors_are_returned_for_invalid_payloads(): void
    {
        $response = $this->postJson('/tasks', [
            'title' => '',
            'status' => 'invalid',
            'priority' => 'urgent',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'status', 'priority']);
    }
}
