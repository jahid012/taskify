<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
    <article class="metric-card">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Total</p>
        <p class="mt-3 font-[family-name:var(--font-display)] text-4xl font-semibold text-slate-950">{{ $metrics['total'] }}</p>
        <p class="mt-2 text-sm text-slate-500">All tracked tasks on the board.</p>
    </article>

    <article class="metric-card">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Pending</p>
        <p class="mt-3 font-[family-name:var(--font-display)] text-4xl font-semibold text-amber-700">{{ $metrics['pending'] }}</p>
        <p class="mt-2 text-sm text-slate-500">Work that has not started yet.</p>
    </article>

    <article class="metric-card">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">In Progress</p>
        <p class="mt-3 font-[family-name:var(--font-display)] text-4xl font-semibold text-cyan-700">{{ $metrics['in_progress'] }}</p>
        <p class="mt-2 text-sm text-slate-500">Tasks actively moving forward.</p>
    </article>

    <article class="metric-card">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Completed</p>
        <p class="mt-3 font-[family-name:var(--font-display)] text-4xl font-semibold text-emerald-700">{{ $metrics['completed'] }}</p>
        <p class="mt-2 text-sm text-slate-500">Closed items with finished work.</p>
    </article>

    <article class="metric-card">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Today & Risk</p>
                <p class="mt-3 font-[family-name:var(--font-display)] text-4xl font-semibold text-slate-950">{{ $metrics['due_today'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Due today, with {{ $metrics['overdue'] }} overdue.</p>
            </div>
            <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white">
                {{ $metrics['completion_rate'] }}%
            </span>
        </div>
        <div class="mt-4 h-2 rounded-full bg-slate-100">
            <div class="h-2 rounded-full bg-teal-500" style="width: {{ $metrics['completion_rate'] }}%"></div>
        </div>
    </article>
</div>
