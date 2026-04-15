<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Taskify | Team Task Board</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main
        class="page-shell"
        data-task-app
        data-store-url="{{ route('tasks.store') }}"
        data-show-url-template="{{ route('tasks.show', '__TASK__') }}"
        data-update-url-template="{{ route('tasks.update', '__TASK__') }}"
        data-delete-url-template="{{ route('tasks.destroy', '__TASK__') }}"
        data-status-url-template="{{ route('tasks.status', '__TASK__') }}"
    >
        <section>
            <div class="rounded-3xl border border-slate-200/70 bg-slate-50 p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Team task board</p>
                        <h1 class="mt-2 text-3xl font-semibold text-slate-950">Keep the team moving forward</h1>
                    </div>
                    <button type="button" class="btn-primary" data-open-task-modal>
                        New Task
                    </button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white px-4 py-4 text-sm text-slate-700 shadow-sm">
                        <p class="font-semibold text-slate-950">Completion rate</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $metrics['completion_rate'] }}%</p>
                    </div>
                    <div class="rounded-2xl bg-white px-4 py-4 text-sm text-slate-700 shadow-sm">
                        <p class="font-semibold text-slate-950">Total tasks</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $metrics['total'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white px-4 py-4 text-sm text-slate-700 shadow-sm">
                        <p class="font-semibold text-slate-950">In progress</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $metrics['in_progress'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div id="feedback" class="mt-6 hidden rounded-2xl border px-4 py-3 text-sm font-medium"></div>

        <section id="summary-panel" class="mt-6">
            @include('tasks.partials.summary', ['metrics' => $metrics])
        </section>

        <section class="toolbar-panel mt-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl">
                    <p class="font-[family-name:var(--font-display)] text-xl font-semibold text-slate-950">Task board</p>
                    <p class="mt-1 text-sm text-slate-500">Search the board instantly or focus on a single status lane.</p>
                </div>
                <div class="w-full max-w-md">
                    <input
                        id="task-search"
                        type="search"
                        class="form-control"
                        placeholder="Search title, description, priority, or status"
                    >
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" class="filter-pill is-active" data-status-filter="all">All tasks</button>
                @foreach ($statusOptions as $statusOption)
                    <button type="button" class="filter-pill" data-status-filter="{{ $statusOption['value'] }}">
                        {{ $statusOption['label'] }}
                    </button>
                @endforeach
            </div>
        </section>

        <section id="board-panel" class="mt-6">
            @include('tasks.partials.board', ['columns' => $columns, 'statusOptions' => $statusOptions])
        </section>
    </main>

    <div id="task-modal" class="modal-scrim hidden">
        <div class="modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-500">Task details</p>
                    <h2 id="modal-title" class="mt-2 font-[family-name:var(--font-display)] text-3xl font-semibold text-slate-950">
                        Create a task
                    </h2>
                </div>
                <button type="button" class="btn-secondary" data-close-modal>Close</button>
            </div>

            <div id="form-errors" class="mt-5 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900"></div>

            <form id="task-form" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" id="task_id" name="task_id">

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Title</label>
                        <input id="title" name="title" type="text" class="form-control" maxlength="255" required placeholder="Task Title">
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="mb-2 block text-sm font-semibold text-slate-700">Priority</label>
                        <select id="priority" name="priority" class="form-control" required>
                            @foreach ($priorityOptions as $priorityOption)
                                <option value="{{ $priorityOption['value'] }}" @selected($priorityOption['value'] === 'medium')>
                                    {{ $priorityOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="due_date" class="mb-2 block text-sm font-semibold text-slate-700">Due date</label>
                        <input id="due_date" name="due_date" type="date" class="form-control">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control min-h-32"
                            maxlength="1000"
                            placeholder="Task Details"
                        ></textarea>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <button type="button" class="btn-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="btn-primary" data-submit-button>Save task</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
