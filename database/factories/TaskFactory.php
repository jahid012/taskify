<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(TaskStatus::cases()),
            'priority' => $this->faker->randomElement(TaskPriority::cases()),
            'due_date' => $this->faker->optional()->dateTimeBetween('today', '+10 days'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Pending,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::InProgress,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn () => [
            'priority' => TaskPriority::High,
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn () => [
            'priority' => TaskPriority::Low,
        ]);
    }
}
