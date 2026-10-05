<section class="page-heading compact-heading">
    <div><p class="eyebrow">YOUR PLAN</p><h1>Study <span>tasks.</span></h1><p class="heading-copy">Keep the next steps visible and manageable.</p></div>
    <a class="primary-button heading-action" href="<?= e(url('/tasks/create')) ?>">+ Add a task</a>
</section>

<section class="content-section">
    <div class="section-heading"><div><p class="eyebrow">TASK LIST</p><h2><?= count($tasks) ?> <?= count($tasks) === 1 ? 'task' : 'tasks' ?></h2></div>
        <form class="filter-form" method="get" action="<?= e(url('/tasks')) ?>">
            <label class="visually-hidden" for="status-filter">Filter by status</label>
            <select id="status-filter" name="status"><option value="">All statuses</option><option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option><option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option></select>
            <label class="visually-hidden" for="subject-filter">Filter by subject</label>
            <select id="subject-filter" name="subject_id"><option value="">All subjects</option><?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>" <?= (string) $filters['subject_id'] === (string) $subject['id'] ? 'selected' : '' ?>><?= e($subject['name']) ?></option><?php endforeach; ?></select>
            <button class="secondary-button filter-button" type="submit">Filter</button>
        </form>
    </div>
    <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if ($tasks === []): ?>
        <div class="empty-state"><span class="empty-symbol" aria-hidden="true">✓</span><h3>No tasks match this view</h3><p>Try another filter, or add a study task to your plan.</p><a class="quiet-link" href="<?= e(url('/tasks/create')) ?>">Add a task</a></div>
    <?php else: ?>
        <ul class="task-list full-task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-row<?= $task['status'] === 'completed' ? ' is-complete' : '' ?>">
                    <span class="subject-dot" style="--subject-color: <?= e($task['subject_color'] ?? '#26745c') ?>"></span>
                    <div class="task-details"><a class="task-title task-link" href="<?= e(url('/tasks/' . $task['id'] . '/edit')) ?>"><?= e($task['title']) ?></a><span class="task-meta"><?= e($task['subject_name'] ?? 'No subject') ?><?php if ($task['due_date']): ?><span class="dot-separator">·</span>Due <?= e(date('M j, Y', strtotime($task['due_date']))) ?><?php endif; ?><?php if ($currentUser['role'] === 'admin'): ?><span class="dot-separator">·</span><?= e($task['owner_name'] ?? 'Legacy task') ?><?php endif; ?></span></div>
                    <span class="priority priority-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span>
                    <form class="inline-status-form" method="post" action="<?= e(url('/tasks/' . $task['id'] . '/status')) ?>"><?= csrf_field() ?><label class="visually-hidden" for="status-<?= (int) $task['id'] ?>">Update status for <?= e($task['title']) ?></label><select id="status-<?= (int) $task['id'] ?>" name="status" aria-label="Task status" onchange="this.form.submit()"><option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option><option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option></select></form>
                    <a class="icon-link" href="<?= e(url('/tasks/' . $task['id'] . '/edit')) ?>" aria-label="Edit <?= e($task['title']) ?>">Edit</a>
                    <form method="post" action="<?= e(url('/tasks/' . $task['id'] . '/delete')) ?>"><?= csrf_field() ?><button class="delete-button" type="submit" aria-label="Delete <?= e($task['title']) ?>" data-confirm="Delete this task?">×</button></form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>