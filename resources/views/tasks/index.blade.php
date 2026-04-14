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
        <section class="hero-panel">
            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-200">Daily work, clearly organized</p>
                    <div class="space-y-3">
                        <h1 class="font-[family-name:var(--font-display)] text-4xl font-semibold leading-tight sm:text-5xl">
                            A simple task board for teams that need clarity, not clutter.
                        </h1>
                        <p class="max-w-2xl text-base text-slate-300 sm:text-lg">
                            Create tasks, move them through progress states, spot overdue work, and keep the whole team aligned from one clean dashboard.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn-primary" data-open-task-modal>
                            New Task
                        </button>
                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-slate-200">
                            {{ $metrics['completion_rate'] }}% completion rate
                        </span>
                    </div>
                </div>
                <div class="relative z-10 rounded-[1.75rem] border border-white/12 bg-white/8 p-5 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">Board Snapshot</p>
                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-4xl font-semibold">{{ $metrics['total'] }}</p>
                            <p class="text-sm text-slate-300">tasks tracked across the team</p>
                        </div>
                        <div class="h-2 rounded-full bg-white/10">
                            <div class="h-2 rounded-full bg-teal-300" style="width: {{ $metrics['completion_rate'] }}%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm text-slate-200">
                            <div class="rounded-2xl border border-white/10 bg-white/6 px-3 py-3">
                                <p class="text-2xl font-semibold">{{ $metrics['in_progress'] }}</p>
                                <p>In progress</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/6 px-3 py-3">
                                <p class="text-2xl font-semibold">{{ $metrics['overdue'] }}</p>
                                <p>Overdue</p>
                            </div>
                        </div>
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
                    <p id="modal-copy" class="mt-2 text-sm text-slate-500">
                        Capture what the team needs to do next and keep the board moving.
                    </p>
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
                        <input id="title" name="title" type="text" class="form-control" maxlength="255" required placeholder="Prepare sprint planning agenda">
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
                            placeholder="Capture enough detail so anyone on the team can pick this up quickly."
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
