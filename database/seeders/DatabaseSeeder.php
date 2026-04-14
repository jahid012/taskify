<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Task::query()->delete();

        Task::insert([
            [
                'title' => 'Review yesterday\'s blockers',
                'description' => 'Capture anything that might hold up the team before stand-up.',
                'status' => TaskStatus::Pending->value,
                'priority' => TaskPriority::High->value,
                'due_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ship the dashboard polish',
                'description' => 'Finish the last UI fixes and confirm the AJAX flows feel smooth.',
                'status' => TaskStatus::InProgress->value,
                'priority' => TaskPriority::Medium->value,
                'due_date' => now()->addDay()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Archive completed sprint notes',
                'description' => 'Move the finished notes into the team knowledge base.',
                'status' => TaskStatus::Completed->value,
                'priority' => TaskPriority::Low->value,
                'due_date' => now()->subDay()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
