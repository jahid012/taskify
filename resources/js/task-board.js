import $ from 'jquery';

window.$ = window.jQuery = $;

const hiddenClass = 'hidden';

function endpoint(template, taskId) {
    return template.replace('__TASK__', String(taskId));
}

function setButtonBusy($button, isBusy, fallbackLabel) {
    if (! $button.length) {
        return;
    }

    const originalLabel = $button.data('original-label') ?? $button.text();

    if (! $button.data('original-label')) {
        $button.data('original-label', originalLabel);
    }

    $button.prop('disabled', isBusy);
    $button.text(isBusy ? fallbackLabel : $button.data('original-label'));
}

$(function () {
    const $app = $('[data-task-app]');

    if (! $app.length) {
        return;
    }

    const routes = {
        store: $app.data('storeUrl'),
        showTemplate: $app.data('showUrlTemplate'),
        updateTemplate: $app.data('updateUrlTemplate'),
        deleteTemplate: $app.data('deleteUrlTemplate'),
        statusTemplate: $app.data('statusUrlTemplate'),
    };

    const state = {
        query: '',
        status: 'all',
    };

    const $modal = $('#task-modal');
    const $form = $('#task-form');
    const $summaryPanel = $('#summary-panel');
    const $boardPanel = $('#board-panel');
    const $feedback = $('#feedback');
    const $errors = $('#form-errors');
    const $submitButton = $('[data-submit-button]');

    $.ajaxSetup({
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    function resetForm() {
        $form.trigger('reset');
        $('#task_id').val('');
        $('#modal-title').text('Create a task');
        $('#modal-copy').text('Capture what the team needs to do next and keep the board moving.');
        clearErrors();
        $('[name="status"]').val('pending');
        $('[name="priority"]').val('medium');
        setButtonBusy($submitButton, false, 'Saving...');
    }

    function openModal() {
        $modal.removeClass(hiddenClass);
        $('body').addClass('overflow-hidden');
    }

    function closeModal() {
        $modal.addClass(hiddenClass);
        $('body').removeClass('overflow-hidden');
    }

    function clearErrors() {
        $errors.addClass(hiddenClass).empty();
    }

    function showErrors(errors) {
        const messages = Object.values(errors).flat();
        const items = messages.map((message) => `<li>${message}</li>`).join('');
        $errors.html(`<ul class="space-y-1">${items}</ul>`).removeClass(hiddenClass);
    }

    function showFeedback(message, tone = 'success') {
        const toneClass = tone === 'error'
            ? 'border-rose-300/70 bg-rose-50 text-rose-900'
            : 'border-emerald-300/70 bg-emerald-50 text-emerald-900';

        $feedback
            .removeClass('hidden border-emerald-300/70 bg-emerald-50 text-emerald-900 border-rose-300/70 bg-rose-50 text-rose-900')
            .addClass(toneClass)
            .text(message);

        window.clearTimeout(window.taskFeedbackTimer);
        window.taskFeedbackTimer = window.setTimeout(() => {
            $feedback.addClass(hiddenClass);
        }, 3500);
    }

    function refreshBoard(payload) {
        $summaryPanel.html(payload.summaryHtml);
        $boardPanel.html(payload.boardHtml);
        applyFilters();
    }

    function applyFilters() {
        const query = state.query.trim().toLowerCase();
        let visibleCount = 0;

        $('[data-task-card]').each(function () {
            const $card = $(this);
            const haystack = [
                $card.data('title'),
                $card.data('description'),
                $card.data('priority'),
                $card.data('status'),
            ].join(' ').toLowerCase();

            const matchesStatus = state.status === 'all' || $card.data('status') === state.status;
            const matchesQuery = query === '' || haystack.includes(query);
            const visible = matchesStatus && matchesQuery;

            $card.toggleClass(hiddenClass, ! visible);

            if (visible) {
                visibleCount += 1;
            }
        });

        $('[data-board-column]').each(function () {
            const $column = $(this);
            const visibleCards = $column.find('[data-task-card]').not(`.${hiddenClass}`).length;
            $column.find('[data-column-empty]').toggleClass(hiddenClass, visibleCards > 0);
        });

        $('#board-empty').toggleClass(hiddenClass, visibleCount > 0);
    }

    function fillForm(task) {
        $('#task_id').val(task.id);
        $('[name="title"]').val(task.title);
        $('[name="description"]').val(task.description ?? '');
        $('[name="status"]').val(task.status);
        $('[name="priority"]').val(task.priority);
        $('[name="due_date"]').val(task.due_date ?? '');
        $('#modal-title').text('Edit task');
        $('#modal-copy').text('Adjust details, change priority, or move work forward without leaving the board.');
    }

    function requestFailed(xhr, fallbackMessage) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            showErrors(xhr.responseJSON.errors);
            return;
        }

        const message = xhr.responseJSON?.message ?? fallbackMessage;
        showFeedback(message, 'error');
    }

    $('[data-open-task-modal]').on('click', function () {
        resetForm();
        openModal();
    });

    $(document).on('click', '[data-close-modal]', function () {
        closeModal();
    });

    $(document).on('click', '[data-edit-task]', function () {
        const taskId = $(this).data('editTask');
        clearErrors();
        setButtonBusy($submitButton, true, 'Loading...');

        $.get(endpoint(routes.showTemplate, taskId))
            .done(({ task }) => {
                resetForm();
                fillForm(task);
                openModal();
            })
            .fail((xhr) => requestFailed(xhr, 'Unable to load the task right now.'))
            .always(() => setButtonBusy($submitButton, false, 'Saving...'));
    });

    $(document).on('click', '[data-delete-task]', function () {
        const taskId = $(this).data('deleteTask');

        if (! window.confirm('Delete this task? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: endpoint(routes.deleteTemplate, taskId),
            type: 'DELETE',
        })
            .done((payload) => {
                refreshBoard(payload);
                showFeedback(payload.message);
            })
            .fail((xhr) => requestFailed(xhr, 'Unable to delete the task right now.'));
    });

    $(document).on('change', '[data-quick-status]', function () {
        const taskId = $(this).data('quickStatus');
        const status = $(this).val();

        $.ajax({
            url: endpoint(routes.statusTemplate, taskId),
            type: 'PATCH',
            data: { status },
        })
            .done((payload) => {
                refreshBoard(payload);
                showFeedback(payload.message);
            })
            .fail((xhr) => requestFailed(xhr, 'Unable to update the task status right now.'));
    });

    $form.on('submit', function (event) {
        event.preventDefault();

        const taskId = $('#task_id').val();
        const isEditing = taskId !== '';
        const url = isEditing ? endpoint(routes.updateTemplate, taskId) : routes.store;
        const method = isEditing ? 'PUT' : 'POST';

        clearErrors();
        setButtonBusy($submitButton, true, 'Saving...');

        $.ajax({
            url,
            type: method,
            data: $form.serialize(),
        })
            .done((payload) => {
                refreshBoard(payload);
                closeModal();
                resetForm();
                showFeedback(payload.message);
            })
            .fail((xhr) => requestFailed(xhr, 'Unable to save the task right now.'))
            .always(() => setButtonBusy($submitButton, false, 'Saving...'));
    });

    $('#task-search').on('input', function () {
        state.query = $(this).val();
        applyFilters();
    });

    $(document).on('click', '[data-status-filter]', function () {
        state.status = $(this).data('statusFilter');
        $('[data-status-filter]').removeClass('is-active');
        $(this).addClass('is-active');
        applyFilters();
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    $modal.on('click', function (event) {
        if ($(event.target).is('#task-modal')) {
            closeModal();
        }
    });

    applyFilters();
});
