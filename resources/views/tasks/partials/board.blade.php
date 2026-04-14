<div class="grid gap-5 xl:grid-cols-3">
    @foreach ($columns as $column)
        <section class="board-column" data-board-column>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="status-chip status-{{ $column['status'] }}">{{ $column['label'] }}</span>
                        <span class="text-sm font-semibold text-slate-400">{{ $column['tasks']->count() }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ $column['helper_text'] }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($column['tasks'] as $task)
                    @include('tasks.partials.card', ['task' => $task, 'statusOptions' => $statusOptions])
                @endforeach
            </div>

            <div data-column-empty class="board-empty-state mt-4 {{ $column['tasks']->isNotEmpty() ? 'hidden' : '' }}">
                No tasks in this lane yet.
            </div>
        </section>
    @endforeach
</div>

<div id="board-empty" class="board-empty-state mt-5 hidden">
    No tasks match your current search or status filter.
</div>
