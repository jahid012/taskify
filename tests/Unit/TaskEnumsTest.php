<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskEnumsTest extends TestCase
{
    public function test_task_status_labels_and_completion_flags_are_consistent(): void
    {
        $this->assertSame('Pending', TaskStatus::Pending->label());
        $this->assertSame('In Progress', TaskStatus::InProgress->label());
        $this->assertSame('Completed', TaskStatus::Completed->label());
        $this->assertFalse(TaskStatus::Pending->isCompleted());
        $this->assertTrue(TaskStatus::Completed->isCompleted());
    }

    public function test_task_priority_options_cover_expected_values(): void
    {
        $this->assertSame([
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High'],
        ], TaskPriority::options());
    }
}
