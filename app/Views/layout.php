<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyPlanner</title>
    <link rel="stylesheet" href="<?= e(url('/public/assets/css/app.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="<?= e(url('/public/assets/js/app.js')) ?>" defer></script>
</head>
<body>
    <?php
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $currentPath = '/' . trim(substr($requestPath, strlen($scriptBase)), '/');
    $currentPath = $currentPath === '//' ? '/' : $currentPath;
    $isAdmin = ($currentUser['role'] ?? null) === 'admin';
    $autoDismissNotice = $notice === 'You have signed out.';
    ?>
    <?php if ($currentUser): ?>
        <header class="topbar<?= $isAdmin ? ' admin-header' : ' app-header' ?>">
            <?php if ($isAdmin): ?>
                <div class="desktop-topbar admin-topbar">
                    <div class="account-menu desktop-account">
                        <span class="account-name"><?= e($currentUser['name']) ?> <small><?= e(ucfirst($currentUser['role'])) ?></small></span>
                        <form method="post" action="<?= e(url('/logout')) ?>">
                            <?= csrf_field() ?>
                            <button class="text-button signout-button" type="submit">Sign out</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="mobile-topbar app-mobile-topbar">
                    <a class="brand" href="<?= e(url('/')) ?>"><span class="brand-mark">S</span> StudyPlanner</a>
                    <button class="menu-toggle app-menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" aria-label="Open navigation">
                        <span></span><span></span><span></span>
                    </button>
                    <button class="app-nav-backdrop" type="button" aria-label="Close navigation" tabindex="-1"></button>
                    <nav id="mobile-nav" class="mobile-nav app-mobile-nav" aria-label="Main navigation">
                        <div class="app-mobile-nav-heading">
                            <span>Navigation</span>
                            <button class="app-mobile-close" type="button" aria-label="Close navigation">×</button>
                        </div>
                        <a class="<?= $currentPath === '/' ? 'is-active' : '' ?>" href="<?= e(url('/')) ?>"><svg class="app-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7"/><path d="M5 9v11h14V9"/><path d="M9 20v-6h6v6"/></svg><span>Overview</span></a>
                        <a class="<?= $currentPath === '/subjects' || str_starts_with($currentPath, '/subjects/') ? 'is-active' : '' ?>" href="<?= e(url('/subjects')) ?>"><svg class="app-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7v14"/><path d="M3 18V5a2 2 0 0 1 2-2h3a4 4 0 0 1 4 4v14a4 4 0 0 0-4-4H5a2 2 0 0 0-2 2Z"/><path d="M21 18V5a2 2 0 0 0-2-2h-3a4 4 0 0 0-4 4v14a4 4 0 0 1 4-4h3a2 2 0 0 1 2 2Z"/></svg><span>Subjects</span></a>
                        <a class="<?= $currentPath === '/tasks' || str_starts_with($currentPath, '/tasks/') ? 'is-active' : '' ?>" href="<?= e(url('/tasks')) ?>"><svg class="app-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1 1 2-2m-3 7 1 1 2-2m-3 7 1 1 2-2"/></svg><span>Study tasks</span></a>
                        <a class="<?= $currentPath === '/exams' || str_starts_with($currentPath, '/exams/') ? 'is-active' : '' ?>" href="<?= e(url('/exams')) ?>"><svg class="app-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg><span>Exams</span></a>
                        <a class="<?= $currentPath === '/progress' ? 'is-active' : '' ?>" href="<?= e(url('/progress')) ?>"><svg class="app-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 17v-3m5 3V5m5 12V9"/></svg><span>Progress</span></a>
                        <div class="mobile-user-row">
                            <span class="account-name"><?= e($currentUser['name']) ?> <small><?= e(ucfirst($currentUser['role'])) ?></small></span>
                            <form method="post" action="<?= e(url('/logout')) ?>">
                                <?= csrf_field() ?>
                                <button class="mobile-signout-button" type="submit">Sign out</button>
                            </form>
                        </div>
                    </nav>
                </div>
            <?php endif; ?>
        </header>
    <?php endif; ?>
    <main class="page-shell<?= $isAdmin ? ' admin-page-shell' : ($currentUser ? ' app-page-shell' : '') ?>">
        <?php if ($isAdmin): ?>
            <div class="admin-layout">
                <aside class="admin-sidebar" aria-label="Admin workspace navigation">
                    <div class="admin-sidebar-heading">
                        <span class="admin-sidebar-mark">S</span>
                        <div><strong>StudyPlanner</strong><span class="admin-sidebar-kicker">ADMIN WORKSPACE</span></div>
                        <button class="menu-toggle app-menu-toggle admin-menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" aria-label="Open navigation">
                            <span></span><span></span><span></span>
                        </button>
                        <button class="app-nav-backdrop" type="button" aria-label="Close navigation" tabindex="-1"></button>
                        <nav id="mobile-nav" class="mobile-nav app-mobile-nav admin-mobile-nav" aria-label="Admin navigation">
                            <div class="app-mobile-nav-heading"><span>Admin navigation</span><button class="app-mobile-close" type="button" aria-label="Close navigation">×</button></div>
                            <a class="<?= $currentPath === '/admin' ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>" <?= $currentPath === '/admin' ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg><span>Overview</span></a>
                            <a class="<?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'is-active' : '' ?>" href="<?= e(url('/admin/manage-users')) ?>" <?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Manage users</span></a>
                            <a class="<?= $currentPath === '/admin/activity' ? 'is-active' : '' ?>" href="<?= e(url('/admin/activity')) ?>" <?= $currentPath === '/admin/activity' ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/></svg><span>Activity log</span></a>
                            <div class="mobile-user-row">
                                <span class="account-name"><?= e($currentUser['name']) ?> <small><?= e(ucfirst($currentUser['role'])) ?></small></span>
                                <form method="post" action="<?= e(url('/logout')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="mobile-signout-button" type="submit">Sign out</button>
                                </form>
                            </div>
                        </nav>
                    </div>
                    <nav class="admin-sidebar-nav" aria-label="Admin pages">
                        <a class="<?= $currentPath === '/admin' ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>" <?= $currentPath === '/admin' ? 'aria-current="page"' : '' ?>>
                            <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg><span>Overview</span>
                        </a>
                        <a class="<?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'is-active' : '' ?>" href="<?= e(url('/admin/manage-users')) ?>" <?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'aria-current="page"' : '' ?>>
                            <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Manage users</span>
                        </a>
                        <a class="<?= $currentPath === '/admin/activity' ? 'is-active' : '' ?>" href="<?= e(url('/admin/activity')) ?>" <?= $currentPath === '/admin/activity' ? 'aria-current="page"' : '' ?>>
                            <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/></svg><span>Activity log</span>
                        </a>
                    </nav>
                    <div class="admin-sidebar-account">
                        <span class="admin-account-avatar"><?= e(strtoupper(substr($currentUser['name'], 0, 1))) ?></span>
                        <span class="admin-account-copy"><strong><?= e($currentUser['name']) ?></strong><small>Administrator</small></span>
                    </div>
                </aside>
                    <div class="admin-content">
                        <?php if ($notice): ?><p class="notice-message" role="status"<?= $autoDismissNotice ? ' data-auto-dismiss="3000"' : '' ?>><?= e($notice) ?></p><?php endif; ?>
                        <?php if ($success): ?><p class="notice-message" role="status" data-swal-success><?= e($success) ?></p><?php endif; ?>
                        <?= $content ?>
                    </div>
            </div>
            <nav class="admin-mobile-quick-actions" aria-label="Admin pages">
                <a class="<?= $currentPath === '/admin' ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>" <?= $currentPath === '/admin' ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg><span>Overview</span></a>
                <a class="<?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'is-active' : '' ?>" href="<?= e(url('/admin/manage-users')) ?>" <?= $currentPath === '/admin/manage-users' || str_starts_with($currentPath, '/admin/users/') ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Manage users</span></a>
                <a class="<?= $currentPath === '/admin/activity' ? 'is-active' : '' ?>" href="<?= e(url('/admin/activity')) ?>" <?= $currentPath === '/admin/activity' ? 'aria-current="page"' : '' ?>><svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/></svg><span>Activity log</span></a>
            </nav>
        <?php elseif ($currentUser): ?>
            <div class="app-layout">
                <aside class="app-sidebar" aria-label="Main navigation">
                    <a class="brand app-sidebar-brand" href="<?= e(url('/')) ?>"><span class="brand-mark">S</span> StudyPlanner</a>
                    <nav class="app-sidebar-nav" aria-label="Main pages">
                        <a class="<?= $currentPath === '/' ? 'is-active' : '' ?>" href="<?= e(url('/')) ?>" <?= $currentPath === '/' ? 'aria-current="page"' : '' ?>>Overview</a>
                        <a class="<?= $currentPath === '/subjects' || str_starts_with($currentPath, '/subjects/') ? 'is-active' : '' ?>" href="<?= e(url('/subjects')) ?>" <?= $currentPath === '/subjects' || str_starts_with($currentPath, '/subjects/') ? 'aria-current="page"' : '' ?>>Subjects</a>
                        <a class="<?= $currentPath === '/tasks' || str_starts_with($currentPath, '/tasks/') ? 'is-active' : '' ?>" href="<?= e(url('/tasks')) ?>" <?= $currentPath === '/tasks' || str_starts_with($currentPath, '/tasks/') ? 'aria-current="page"' : '' ?>>Study tasks</a>
                        <a class="<?= $currentPath === '/exams' || str_starts_with($currentPath, '/exams/') ? 'is-active' : '' ?>" href="<?= e(url('/exams')) ?>" <?= $currentPath === '/exams' || str_starts_with($currentPath, '/exams/') ? 'aria-current="page"' : '' ?>>Exams</a>
                        <a class="<?= $currentPath === '/progress' ? 'is-active' : '' ?>" href="<?= e(url('/progress')) ?>" <?= $currentPath === '/progress' ? 'aria-current="page"' : '' ?>>Progress</a>
                    </nav>
                    <div class="app-sidebar-account">
                        <div class="app-sidebar-user">
                            <span class="app-account-avatar"><?= e(strtoupper(substr($currentUser['name'], 0, 1))) ?></span>
                            <span class="app-account-copy"><strong><?= e($currentUser['name']) ?></strong><small><?= e(ucfirst($currentUser['role'])) ?></small></span>
                        </div>
                        <form method="post" action="<?= e(url('/logout')) ?>">
                            <?= csrf_field() ?>
                            <button class="app-sidebar-signout" type="submit">Sign out</button>
                        </form>
                    </div>
                </aside>
                <div class="app-content">
                    <?php if ($notice): ?><p class="notice-message" role="status"<?= $autoDismissNotice ? ' data-auto-dismiss="3000"' : '' ?>><?= e($notice) ?></p><?php endif; ?>
                    <?php if ($success): ?><p class="notice-message" role="status" data-swal-success><?= e($success) ?></p><?php endif; ?>
                    <?= $content ?>
                </div>
            </div>
            <nav class="app-mobile-quick-actions" aria-label="Quick actions">
                <a href="<?= e(url('/tasks/create')) ?>" aria-label="Add a study task"><svg class="app-quick-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1 1 2-2m-3 7 1 1 2-2m-3 7 1 1 2-2"/></svg><span>Task</span></a>
                <a href="<?= e(url('/subjects/create')) ?>" aria-label="Add a subject"><svg class="app-quick-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7v14"/><path d="M3 18V5a2 2 0 0 1 2-2h3a4 4 0 0 1 4 4v14a4 4 0 0 0-4-4H5a2 2 0 0 0-2 2Z"/><path d="M21 18V5a2 2 0 0 0-2-2h-3a4 4 0 0 0-4 4v14a4 4 0 0 1 4-4h3a2 2 0 0 1 2 2Z"/></svg><span>Subject</span></a>
                <a href="<?= e(url('/exams/create')) ?>" aria-label="Add an exam"><svg class="app-quick-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg><span>Exam</span></a>
                <a href="<?= e(url('/progress')) ?>" aria-label="View progress"><svg class="app-quick-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 17v-3m5 3V5m5 12V9"/></svg><span>Progress</span></a>
            </nav>
        <?php else: ?>
                <?php if ($notice): ?><p class="notice-message" role="status"<?= $autoDismissNotice ? ' data-auto-dismiss="3000"' : '' ?>><?= e($notice) ?></p><?php endif; ?>
                <?php if ($success): ?><p class="notice-message" role="status" data-swal-success><?= e($success) ?></p><?php endif; ?>
            <?= $content ?>
        <?php endif; ?>
    </main>
</body>
</html>
