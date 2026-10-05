<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">EXAM SCHEDULE</p>
        <h1>Your <span>exams.</span></h1>
        <p class="heading-copy">Keep each subject's exam date and progress in one place.</p>
    </div>
    <a class="primary-button heading-action" href="<?= e(url('/exams/create')) ?>">+ Add an exam</a>
</section>

<section class="content-section exam-section">
    <div class="section-heading">
        <div><p class="eyebrow">YOUR SCHEDULE</p><h2><?= count($exams) ?> <?= count($exams) === 1 ? 'exam' : 'exams' ?></h2></div>
    </div>
    <?php if ($exams === []): ?>
        <div class="empty-state"><span class="empty-symbol" aria-hidden="true">+</span><h3>No exams scheduled</h3><p>Add an exam, connect it to a subject, and track its status here.</p><a class="primary-button" href="<?= e(url('/exams/create')) ?>">Add your first exam</a></div>
    <?php else: ?>
        <ul class="exam-list">
            <?php foreach ($exams as $exam): ?>
                <?php $examTimestamp = strtotime($exam['exam_at']); ?>
                <li class="exam-row">
                    <div class="exam-date-block">
                        <span><?= e(date('M', $examTimestamp)) ?></span>
                        <strong><?= e(date('j', $examTimestamp)) ?></strong>
                    </div>
                    <div class="exam-details">
                        <a class="exam-title" href="<?= e(url('/exams/' . $exam['id'] . '/edit')) ?>"><?= e($exam['title']) ?></a>
                        <span class="exam-meta"><span class="subject-dot" style="--subject-color: <?= e($exam['subject_color'] ?? '#26745c') ?>"></span><?= e($exam['subject_name'] ?? 'Subject removed') ?><span class="dot-separator">·</span><time datetime="<?= e(date('Y-m-d\TH:i:s', $examTimestamp)) ?>"><?= e(date('l, g:i a', $examTimestamp)) ?></time></span>
                    </div>
                    <form class="exam-status-form" method="post" action="<?= e(url('/exams/' . $exam['id'] . '/status')) ?>">
                        <?= csrf_field() ?>
                        <label class="visually-hidden" for="exam-status-<?= (int) $exam['id'] ?>">Status for <?= e($exam['title']) ?></label>
                        <select id="exam-status-<?= (int) $exam['id'] ?>" name="status" class="status-pill status-<?= e($exam['status']) ?>" onchange="this.form.submit()">
                            <option value="scheduled" <?= $exam['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="completed" <?= $exam['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="missed" <?= $exam['status'] === 'missed' ? 'selected' : '' ?>>Missed</option>
                        </select>
                    </form>
                    <a class="icon-link" href="<?= e(url('/exams/' . $exam['id'] . '/edit')) ?>" aria-label="Edit <?= e($exam['title']) ?>">Edit</a>
                    <form method="post" action="<?= e(url('/exams/' . $exam['id'] . '/delete')) ?>">
                        <?= csrf_field() ?>
                        <button class="delete-button" type="submit" aria-label="Delete <?= e($exam['title']) ?>" data-confirm="Delete this exam?">×</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>