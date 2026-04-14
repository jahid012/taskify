<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = $this->loadTasks();

        return view('tasks.index', [
            'columns' => $this->columns($tasks),
            'metrics' => $this->metrics($tasks),
            'statusOptions' => TaskStatus::options(),
            'priorityOptions' => TaskPriority::options(),
        ]);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        Task::create($request->validated());

        return response()->json([
            'message' => 'Task created successfully.',
            'summaryHtml' => $this->renderSummary(),
            'boardHtml' => $this->renderBoard(),
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json([
            'message' => 'Task updated successfully.',
            'summaryHtml' => $this->renderSummary(),
            'boardHtml' => $this->renderBoard(),
        ]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json([
            'message' => 'Task status updated.',
            'summaryHtml' => $this->renderSummary(),
            'boardHtml' => $this->renderBoard(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
            'summaryHtml' => $this->renderSummary(),
            'boardHtml' => $this->renderBoard(),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Task>
     */
    private function loadTasks(): Collection
    {
        return Task::query()
            ->boardOrdered()
            ->get();
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Task> $tasks
     * @return array<int, array{status: string, label: string, helper_text: string, tasks: \Illuminate\Support\Collection<int, \App\Models\Task>}>
     */
    private function columns(Collection $tasks): array
    {
        $grouped = $tasks->groupBy(fn (Task $task) => $task->status->value);

        return array_map(
            fn (TaskStatus $status) => [
                'status' => $status->value,
                'label' => $status->label(),
                'helper_text' => $status->helperText(),
                'tasks' => $grouped->get($status->value, collect()),
            ],
            TaskStatus::cases(),
        );
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Task> $tasks
     * @return array<string, int>
     */
    private function metrics(Collection $tasks): array
    {
        $total = $tasks->count();
        $completed = $tasks->filter(fn (Task $task) => $task->status === TaskStatus::Completed)->count();

        return [
            'total' => $total,
            'pending' => $tasks->filter(fn (Task $task) => $task->status === TaskStatus::Pending)->count(),
            'in_progress' => $tasks->filter(fn (Task $task) => $task->status === TaskStatus::InProgress)->count(),
            'completed' => $completed,
            'due_today' => $tasks->filter(fn (Task $task) => $task->isDueToday())->count(),
            'overdue' => $tasks->filter(fn (Task $task) => $task->isOverdue())->count(),
            'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    private function renderSummary(): string
    {
        return view('tasks.partials.summary', [
            'metrics' => $this->metrics($this->loadTasks()),
        ])->render();
    }

    private function renderBoard(): string
    {
        return view('tasks.partials.board', [
            'columns' => $this->columns($this->loadTasks()),
            'statusOptions' => TaskStatus::options(),
        ])->render();
    }
}
