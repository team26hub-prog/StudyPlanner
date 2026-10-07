<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1>Manage <span>users.</span></h1>
        <p class="heading-copy">Create regular accounts or remove accounts and their study data.</p>
    </div>
</section>

<div class="admin-users-page">
    <details class="admin-create-user"<?= $error ? ' open' : '' ?>>
        <summary class="primary-button">Add User</summary>
        <section class="content-section admin-create-user-form">
        <div class="section-heading"><div><p class="eyebrow">NEW ACCOUNT</p><h2>Add a user</h2></div></div>
        <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="editor-form" method="post" action="<?= e(url('/admin/users')) ?>" data-password-confirmation>
            <?= csrf_field() ?>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" minlength="2" maxlength="100" autocomplete="name" required>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="190" autocomplete="email" required>
            <label for="password">Temporary password</label>
            <div class="password-field-control"><input id="password" name="password" type="password" minlength="8" maxlength="72" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,72}" title="Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol." aria-describedby="password-hint" autocomplete="new-password" required><button class="password-visibility-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><path class="password-eye-slash" d="m4 4 16 16"/></svg></button></div>
            <small id="password-hint" class="field-hint">Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol.</small>
            <label for="password_confirmation">Confirm password</label>
            <div class="password-field-control"><input id="password_confirmation" name="password_confirmation" type="password" maxlength="72" autocomplete="new-password" required><button class="password-visibility-toggle" type="button" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><path class="password-eye-slash" d="m4 4 16 16"/></svg></button></div>
            <p class="form-footnote">The password is stored as a secure hash. The new account will have the standard user role.</p>
            <div class="form-actions"><button class="primary-button" type="submit">Create user</button></div>
        </form>
        </section>
    </details>

    <section class="content-section admin-user-directory">
        <div class="section-heading"><div><p class="eyebrow">ACCOUNT DIRECTORY</p><h2><?= count($users) ?> <?= count($users) === 1 ? 'account' : 'accounts' ?></h2></div></div>
        <?php if ($users === []): ?>
            <div class="empty-state compact-empty"><h3>No accounts yet</h3><p>Accounts will appear here after creation.</p></div>
        <?php else: ?>
            <div class="admin-table-scroll">
                <table class="admin-table admin-manage-table">
                    <thead><tr><th scope="col">Account</th><th scope="col">Role</th><th scope="col"><span class="visually-hidden">Action</span></th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><span class="admin-user-name"><?= e($user['name']) ?></span><span class="admin-user-email"><?= e($user['email']) ?></span></td>
                                <td><span class="role-badge role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span></td>
                                <td>
                                    <?php if ($user['role'] === 'user'): ?>
                                        <form method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this account and all of its study data?')">
                                            <?= csrf_field() ?>
                                            <button class="danger-button admin-delete-button" type="submit">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted-copy">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
