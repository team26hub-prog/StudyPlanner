<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1>Activity <span>log.</span></h1>
        <p class="heading-copy">Recent account and study-plan changes across the platform.</p>
    </div>
</section>

<section class="content-section">
    <div class="section-heading"><div><p class="eyebrow">LATEST EVENTS</p><h2><?= count($activities) ?> recent <?= count($activities) === 1 ? 'event' : 'events' ?></h2></div></div>
    <?php if ($activities === []): ?>
        <div class="empty-state compact-empty"><h3>No activity yet</h3><p>Account and study activity will appear here.</p></div>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table admin-activity-table">
                <thead><tr><th scope="col">When</th><th scope="col">Account</th><th scope="col">Event</th><th scope="col">Details</th></tr></thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td class="admin-activity-date"><?= e(date('M j, Y g:i a', strtotime($activity['created_at']))) ?></td>
                            <td><?= e($activity['actor_name']) ?></td>
                            <td><span class="role-badge"><?= e(ucwords(str_replace(['.', '_'], ' ', $activity['event']))) ?></span></td>
                            <td><?= e($activity['description']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>