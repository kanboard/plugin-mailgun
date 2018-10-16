<h3><img src="<?= $this->url->dir() ?>plugins/Mailgun/mailgun-icon.png"/>&nbsp;Mailgun</h3>
<div class="panel">
    <?= $this->form->label(t('Catch-All Email Address'), 'mailgun_catch_all') ?>
    <?= $this->form->email('mailgun_catch_all', $values) ?>

    <p class="form-help">
        <?= t('Blank value will cause unknown senders to be ignored') ?> -
        <a href="https://github.com/kanboard/plugin-mailgun#installation" target="_blank"><?= t('Help on Mailgun integration') ?></a>
    </p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
