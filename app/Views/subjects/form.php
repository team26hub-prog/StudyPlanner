<section class="page-heading compact-heading"><div><p class="eyebrow">SUBJECT DETAILS</p><h1><?= e($formTitle) ?>.</h1><p class="heading-copy">A clear subject name makes your plan easier to browse.</p></div><a class="back-button" href="<?= e(url('/subjects')) ?>">All subjects</a></section>
<section class="form-page content-section">
    <?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form class="editor-form" method="post" action="<?= e($formAction) ?>">
        <?= csrf_field() ?>
        <label for="name">Subject name</label><input id="name" name="name" type="text" minlength="1" maxlength="100" value="<?= e($subject['name'] ?? '') ?>" placeholder="e.g. Biology" required>
        <label for="description">Description <span class="optional">Optional</span></label><textarea id="description" name="description" maxlength="1000" rows="4" placeholder="Add a short note about this subject."><?= e($subject['description'] ?? '') ?></textarea>
        <fieldset class="color-fieldset"><legend>Subject color</legend><div class="color-options"><?php foreach (['#26745c', '#d47458', '#5277a5', '#997044', '#8065a3'] as $color): ?><label class="color-choice"><input type="radio" name="color" value="<?= e($color) ?>" <?= ($subject['color'] ?? '#26745c') === $color ? 'checked' : '' ?> required><span style="--swatch: <?= e($color) ?>" aria-label="<?= e($color) ?>"></span></label><?php endforeach; ?></div></fieldset>
        <div class="form-actions"><button class="primary-button" type="submit"><?= $subject ? 'Save changes' : 'Add subject' ?></button><a class="secondary-button" href="<?= e(url('/subjects')) ?>">Cancel</a></div>
    </form>
</section>