<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1>Admin <span>panel.</span></h1>
        <p class="heading-copy">Review accounts and the study activity connected to each one.</p>
    </div>
</section>

<section class="stats-grid admin-stats" aria-label="Platform totals">
    <article class="stat-card stat-focus"><span class="stat-label">Accounts</span><strong><?= $stats['user_count'] ?></strong><span class="stat-caption">Registered users and admins</span></article>
    <article class="stat-card"><span class="stat-label">Subjects</span><strong><?= $stats['subject_count'] ?></strong><span class="stat-caption">Across all accounts</span></article>
    <article class="stat-card"><span class="stat-label">Study tasks</span><strong><?= $stats['task_count'] ?></strong><span class="stat-caption">Across all accounts</span></article>
    <article class="stat-card"><span class="stat-label">Completed</span><strong><?= $stats['completed_count'] ?></strong><span class="stat-caption">Tasks marked complete</span></article>
</section>

<section class="content-section admin-user-section">
    <div class="section-heading">
        <div><p class="eyebrow">ACCOUNT DIRECTORY</p><h2><?= count($users) ?> <?= count($users) === 1 ? 'account' : 'accounts' ?></h2></div>
    </div>
    <?php if ($users === []): ?>
        <div class="empty-state compact-empty"><h3>No accounts yet</h3><p>Registered users will appear here.</p></div>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead><tr><th scope="col">Account</th><th scope="col">Role</th><th scope="col">Subjects</th><th scope="col">Tasks</th><th scope="col">Completed</th><th scope="col"><span class="visually-hidden">Details</span></th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><span class="admin-user-name"><?= e($user['name']) ?></span><span class="admin-user-email"><?= e($user['email']) ?></span></td>
                            <td><span class="role-badge role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span></td>
                            <td><?= (int) $user['subject_count'] ?></td>
                            <td><?= (int) $user['task_count'] ?></td>
                            <td><?= (int) $user['completed_count'] ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="secondary-button admin-view-button admin-self-marker">You</span>
                                <?php else: ?>
                                    <a class="secondary-button admin-view-button" href="<?= e(url('/admin/users/' . $user['id'])) ?>">View account</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>