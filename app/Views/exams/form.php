<section class="page-heading compact-heading">
    <div>
        <p class="eyebrow">EXAM DETAILS</p>
        <h1><?= e($formTitle) ?>.</h1>
        <p class="heading-copy">Choose a subject and set the date and time you need to be ready.</p>
    </div>
    <a class="back-button" href="<?= e(url('/exams')) ?>">All exams</a>
</section>

<section class="form-page content-section">
    <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if ($subjects === []): ?>
        <p class="muted-copy">Add a subject before scheduling an exam.</p>
        <a class="primary-button" href="<?= e(url('/subjects/create')) ?>">Add a subject</a>
    <?php else: ?>
        <form class="editor-form" method="post" action="<?= e($formAction) ?>">
            <?= csrf_field() ?>
            <label for="title">Exam name</label>
            <input id="title" name="title" type="text" minlength="1" maxlength="120" value="<?= e($exam['title'] ?? '') ?>" placeholder="e.g. Biology midterm" required>

            <label for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" required>
                <option value="">Choose a subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= (int) $subject['id'] ?>" <?= (string) ($exam['subject_id'] ?? '') === (string) $subject['id'] ? 'selected' : '' ?>><?= e($subject['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="exam_at">Exam date and time</label>
            <input id="exam_at" name="exam_at" type="datetime-local" value="<?= e(isset($exam['exam_at']) ? date('Y-m-d\TH:i', strtotime($exam['exam_at'])) : '') ?>" required>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="scheduled" <?= ($exam['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="completed" <?= ($exam['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="missed" <?= ($exam['status'] ?? '') === 'missed' ? 'selected' : '' ?>>Missed</option>
            </select>

            <div class="form-actions">
                <button class="primary-button" type="submit"><?= $exam ? 'Save changes' : 'Add exam' ?></button>
                <a class="secondary-button" href="<?= e(url('/exams')) ?>">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</section>