<section class="auth-layout">
    <div class="auth-intro">
        <p class="eyebrow">A FRESH START</p>
        <h1>Small steps.<br><span>Real progress.</span></h1>
        <p class="heading-copy">Build a study plan that works for you.</p>
    </div>
    <div class="auth-panel">
        <h2>Create your account</h2>
        <p class="muted-copy">Your plan stays private to your account.</p>
        <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="stack-form" method="post" action="<?= e(url('/signup')) ?>" data-password-confirmation>
            <?= csrf_field() ?>
            <label for="name">Your name</label>
            <input id="name" name="name" type="text" minlength="2" maxlength="100" autocomplete="name" required>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" maxlength="190" autocomplete="email" required>
            <label for="password">Password</label>
            <div class="password-field-control"><input id="password" name="password" type="password" minlength="8" maxlength="72" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,72}" title="Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol." aria-describedby="password-hint" autocomplete="new-password" required><button class="password-visibility-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><path class="password-eye-slash" d="m4 4 16 16"/></svg></button></div>
            <small id="password-hint" class="field-hint">Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol.</small>
            <label for="password_confirmation">Confirm password</label>
            <div class="password-field-control"><input id="password_confirmation" name="password_confirmation" type="password" maxlength="72" autocomplete="new-password" required><button class="password-visibility-toggle" type="button" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><path class="password-eye-slash" d="m4 4 16 16"/></svg></button></div>
            <button class="primary-button" type="submit">Create account</button>
        </form>
        <p class="auth-switch">Already registered? <a href="<?= e(url('/login')) ?>">Sign in</a></p>
    </div>
</section>
