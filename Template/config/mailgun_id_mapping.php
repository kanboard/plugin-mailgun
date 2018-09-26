<h3><img src="<?= $this->url->dir() ?>plugins/Mailgun/mailgun-icon.png"/>&nbsp;Mailgun Unknown ID mapping</h3>
<div class="panel">
    <?= $this->form->label(t('Set unknown senders to:'), 'MailgunProject_catchall') ?>
    <?= $this->form->text('MailgunProject_catchall', $values) ?>
    <?= $this->form->label(t('(NOTE: Blank value will cause unknown senders to be ignored)'), 'MailgunProject_catchall') ?>

    <p class="form-help"><a href="https://github.com/kanboard/plugin-mailgun#installation" target="_blank"><?= t('Help on Mailgun integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
