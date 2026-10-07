<a class="back-button admin-account-back" href="<?= e(url('/admin')) ?>" data-history-back aria-label="Back to admin overview">
    <span aria-hidden="true">←</span>
</a>

<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">ACCOUNT DETAILS</p>
        <h1><?= e($managedUser['name']) ?>.</h1>
        <p class="heading-copy"><?= e($managedUser['email']) ?> <span class="role-badge role-<?= e($managedUser['role']) ?>"><?= e(ucfirst($managedUser['role'])) ?></span></p>
    </div>
</section>

<section class="stats-grid admin-stats" aria-label="Account study totals">
    <article class="stat-card stat-focus"><span class="stat-label">Subjects</span><strong><?= (int) $managedUser['subject_count'] ?></strong><span class="stat-caption">Created by this account</span></article>
    <article class="stat-card"><span class="stat-label">All tasks</span><strong><?= (int) $managedUser['task_count'] ?></strong><span class="stat-caption">Across this account's subjects</span></article>
    <article class="stat-card"><span class="stat-label">Completed</span><strong><?= (int) $managedUser['completed_count'] ?></strong><span class="stat-caption">Marked complete</span></article>
    <article class="stat-card"><span class="stat-label">Pending</span><strong><?= max(0, (int) $managedUser['task_count'] - (int) $managedUser['completed_count']) ?></strong><span class="stat-caption">Pending or in progress</span></article>
</section>

<section class="admin-detail-grid">
    <div class="content-section">
        <div class="section-heading"><div><p class="eyebrow">SUBJECTS</p><h2><?= count($subjects) ?> subjects</h2></div></div>
        <?php if ($subjects === []): ?>
            <div class="empty-state compact-empty"><h3>No subjects</h3><p>This account has not added any subjects.</p></div>
        <?php else: ?>
            <ul class="admin-related-list">
                <?php foreach ($subjects as $subject): ?>
                    <li><span class="subject-dot" style="--subject-color: <?= e($subject['color']) ?>"></span><div class="task-details"><span class="task-title"><?= e($subject['name']) ?></span><?php if ($subject['description'] !== ''): ?><span class="task-meta"><?= e($subject['description']) ?></span><?php endif; ?></div><span class="admin-related-count"><?= (int) $subject['task_count'] ?> tasks</span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="content-section">
        <div class="section-heading"><div><p class="eyebrow">STUDY PLAN</p><h2><?= count($tasks) ?> tasks</h2></div></div>
        <?php if ($tasks === []): ?>
            <div class="empty-state compact-empty"><h3>No study tasks</h3><p>This account has not added any tasks.</p></div>
        <?php else: ?>
            <ul class="admin-related-list admin-task-list">
                <?php foreach ($tasks as $task): ?>
                    <li><span class="subject-dot" style="--subject-color: <?= e($task['subject_color'] ?? '#26745c') ?>"></span><div class="task-details"><span class="task-title"><?= e($task['title']) ?></span><span class="task-meta"><?= e($task['subject_name'] ?? 'No subject') ?><?php if ($task['due_date']): ?><span class="dot-separator">·</span>Due <?= e(date('M j, Y', strtotime($task['due_date']))) ?><?php endif; ?></span></div><span class="priority priority-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span><span class="status-pill status-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
