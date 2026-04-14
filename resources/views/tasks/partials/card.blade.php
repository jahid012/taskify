@php
    $statusClass = 'status-' . $task->status->value;
    $priorityClass = 'priority-' . $task->priority->value;
@endphp

<article
    class="task-card {{ $task->isOverdue() ? 'is-overdue' : '' }}"
    data-task-card
    data-status="{{ $task->status->value }}"
    data-priority="{{ $task->priority->value }}"
    data-title="{{ $task->title }}"
    data-description="{{ $task->description }}"
>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="status-chip {{ $statusClass }}">{{ $task->status->label() }}</span>
                <span class="priority-chip {{ $priorityClass }}">{{ $task->priority->label() }} priority</span>
            </div>
            <h3 class="font-[family-name:var(--font-display)] text-lg font-semibold text-slate-950">
                {{ $task->title }}
            </h3>
        </div>
        <div class="text-right text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
            #{{ $task->id }}
        </div>
    </div>

    @if ($task->description)
        <p class="mt-3 text-sm leading-6 text-slate-600">
            {{ $task->description }}
        </p>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <span>Created {{ $task->created_at->format('M d, Y') }}</span>
        @if ($task->due_date)
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                Due {{ $task->due_date->format('M d') }}
            </span>
        @endif
        @if ($task->isDueToday())
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">Due today</span>
        @endif
        @if ($task->isOverdue())
            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-900">Overdue</span>
        @endif
    </div>

    <div class="mt-5 grid gap-3">
        <label class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
            Quick status
            <select class="form-control mt-2" data-quick-status="{{ $task->id }}">
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption['value'] }}" @selected($task->status->value === $statusOption['value'])>
                        {{ $statusOption['label'] }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-secondary" data-edit-task="{{ $task->id }}">Edit</button>
            <button type="button" class="btn-danger" data-delete-task="{{ $task->id }}">Delete</button>
        </div>
    </div>
</article>
