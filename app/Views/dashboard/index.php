<section class="page-heading">
    <div><p class="eyebrow">YOUR STUDY SPACE</p><h1>Good to see you,<br><span><?= e($currentUser['name']) ?>.</span></h1><p class="heading-copy">A little progress, made consistently, adds up.</p></div>
    <a class="primary-button heading-action" href="<?= e(url('/tasks/create')) ?>">+ Add a task</a>
</section>

<section class="stats-grid" aria-label="Study summary">
    <article class="stat-card stat-focus"><span class="stat-label">Tasks in your plan</span><strong><?= $counts['total'] ?></strong><span class="stat-caption">Across all subjects</span></article>
    <article class="stat-card"><span class="stat-label">Still to do</span><strong><?= $counts['pending'] ?></strong><span class="stat-caption">Pending or in progress</span></article>
    <article class="stat-card"><span class="stat-label">Completed</span><strong><?= $counts['completed'] ?></strong><span class="stat-caption"><a href="<?= e(url('/progress')) ?>">See your progress</a></span></article>
    <article class="stat-card"><span class="stat-label">Subjects</span><strong><?= count($subjects) ?></strong><span class="stat-caption"><a href="<?= e(url('/subjects')) ?>">Organize your plan</a></span></article>
</section>

<section class="dashboard-columns">
    <div class="content-section">
        <div class="section-heading"><div><p class="eyebrow">COMING UP</p><h2>Study tasks</h2></div><a class="quiet-link" href="<?= e(url('/tasks')) ?>">View all tasks</a></div>
        <?php if ($upcomingTasks === []): ?>
            <div class="empty-state compact-empty"><span class="empty-symbol" aria-hidden="true">+</span><h3>Your plan starts here</h3><p>Add a task and give your next session a clear focus.</p><a class="quiet-link" href="<?= e(url('/tasks/create')) ?>">Create your first task</a></div>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($upcomingTasks as $task): ?>
                    <li class="task-row<?= $task['status'] === 'completed' ? ' is-complete' : '' ?>"><span class="subject-dot" style="--subject-color: <?= e($task['subject_color'] ?? '#26745c') ?>"></span><div class="task-details"><a class="task-title task-link" href="<?= e(url('/tasks/' . $task['id'] . '/edit')) ?>"><?= e($task['title']) ?></a><span class="task-meta"><?= e($task['subject_name'] ?? 'No subject') ?><?php if ($task['due_date']): ?><span class="dot-separator">·</span><?= e(date('M j', strtotime($task['due_date']))) ?><?php endif; ?></span></div><span class="status-pill status-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <aside class="content-section subject-preview">
        <div class="section-heading"><div><p class="eyebrow">YOUR COLLECTION</p><h2>Subjects</h2></div><a class="quiet-link" href="<?= e(url('/subjects')) ?>">All subjects</a></div>
        <?php if ($subjects === []): ?>
            <p class="muted-copy">Group tasks by subject to keep your workload easy to scan.</p><a class="secondary-button" href="<?= e(url('/subjects/create')) ?>">+ Add a subject</a>
        <?php else: ?>
            <ul class="subject-mini-list"><?php foreach (array_slice($subjects, 0, 5) as $subject): ?><li><span class="subject-dot" style="--subject-color: <?= e($subject['color']) ?>"></span><a href="<?= e(url('/subjects/' . $subject['id'])) ?>"><?= e($subject['name']) ?></a><span class="mini-count"><?= (int) $subject['task_count'] ?></span></li><?php endforeach; ?></ul>
            <a class="secondary-button" href="<?= e(url('/subjects/create')) ?>">+ Add a subject</a>
        <?php endif; ?>
    </aside>
</section>