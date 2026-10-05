<section class="page-heading compact-heading"><div><p class="eyebrow">TASK DETAILS</p><h1><?= e($formTitle) ?>.</h1><p class="heading-copy">Give the next step a clear name, subject, and deadline.</p></div><a class="back-button" href="<?= e(url('/tasks')) ?>">All study tasks</a></section>
<section class="form-page content-section">
    <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form class="editor-form" method="post" action="<?= e($formAction) ?>">
        <?= csrf_field() ?>
        <label for="title">Task name</label><input id="title" name="title" type="text" minlength="1" maxlength="180" value="<?= e($task['title'] ?? '') ?>" placeholder="e.g. Review chapter 4" required>
        <label for="description">Notes <span class="optional">Optional</span></label><textarea id="description" name="description" maxlength="2000" rows="4" placeholder="What do you want to cover?"><?= e($task['description'] ?? '') ?></textarea>
        <div class="form-grid-three">
            <div><label for="subject_id">Subject</label><select id="subject_id" name="subject_id"><option value="">No subject</option><?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>" <?= (string) ($task['subject_id'] ?? '') === (string) $subject['id'] ? 'selected' : '' ?>><?= e($subject['name']) ?></option><?php endforeach; ?></select></div>
            <div><label for="due_date">Deadline</label><input id="due_date" name="due_date" type="date" value="<?= e($task['due_date'] ?? '') ?>"></div>
            <div><label for="priority">Priority</label><select id="priority" name="priority"><option value="low" <?= ($task['priority'] ?? 'medium') === 'low' ? 'selected' : '' ?>>Low</option><option value="medium" <?= ($task['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option><option value="high" <?= ($task['priority'] ?? 'medium') === 'high' ? 'selected' : '' ?>>High</option></select></div>
        </div>
        <div class="form-grid-single"><div><label for="status">Status</label><select id="status" name="status"><option value="pending" <?= ($task['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option><option value="in_progress" <?= ($task['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="completed" <?= ($task['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option></select></div></div>
        <div class="form-actions"><button class="primary-button" type="submit"><?= $task ? 'Save changes' : 'Add task' ?></button><a class="secondary-button" href="<?= e(url('/tasks')) ?>">Cancel</a></div>
    </form>
</section>