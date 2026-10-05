<section class="auth-layout">
    <div class="auth-intro">
        <span class="brand auth-brand"><span class="brand-mark">S</span> StudyPlanner</span>
        <p class="eyebrow">WELCOME BACK</p>
        <h1>Make time<br>for <span>your goals.</span></h1>
        <p class="heading-copy">A calmer way to keep your study plans moving.</p>
    </div>
    <div class="auth-panel">
        <h2>Sign in</h2>
        <p class="muted-copy">Pick up where you left off.</p>
        <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="stack-form" method="post" action="<?= e(url('/login')) ?>">
            <?= csrf_field() ?>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" maxlength="190" autocomplete="email" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" maxlength="1024" autocomplete="current-password" required>
            <button class="primary-button" type="submit">Sign in</button>
        </form>
        <p class="auth-switch">New to StudyPlanner? <a href="<?= e(url('/signup')) ?>">Create an account</a></p>
    </div>
</section>