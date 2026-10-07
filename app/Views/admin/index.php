<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1>Admin <span>panel.</span></h1>
        <p class="heading-copy">Review accounts and the study activity connected to each one.</p>
    </div>
</section>

<section class="stats-grid admin-stats" aria-label="Platform totals">
    <article class="stat-card stat-focus"><span class="admin-stat-heading"><svg class="admin-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20zM16 5.2a3.5 3.5 0 0 1 0 6.6M17 14h.5a3.5 3.5 0 0 1 3.5 3.5V20h-4"/></svg><span class="stat-label">Accounts</span></span><strong><?= $stats['user_count'] ?></strong><span class="stat-caption">Registered users and admins</span></article>
    <article class="stat-card"><span class="admin-stat-heading"><svg class="admin-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 6h8M8 9h8"/></svg><span class="stat-label">Subjects</span></span><strong><?= $stats['subject_count'] ?></strong><span class="stat-caption">Across all accounts</span></article>
    <article class="stat-card"><span class="admin-stat-heading"><svg class="admin-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="m8 9 1.5 1.5L12 8M14 10h3m-9 5 1.5 1.5L12 14m2 2h3"/></svg><span class="stat-label">Study tasks</span></span><strong><?= $stats['task_count'] ?></strong><span class="stat-caption">Across all accounts</span></article>
    <article class="stat-card"><span class="admin-stat-heading"><svg class="admin-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg><span class="stat-label">Completed</span></span><strong><?= $stats['completed_count'] ?></strong><span class="stat-caption">Tasks marked complete</span></article>
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
