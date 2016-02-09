<h3><img src="<?= $this->url->dir() ?>plugins/Mailgun/mailgun-icon.png"/>&nbsp;Mailgun</h3>
<div class="listing">
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('webhook', 'receiver', array('plugin' => 'mailgun', 'token' => $values['webhook_token']), false, '', true) ?>">

    <?= $this->form->label(t('Mailgun API token'), 'mailgun_api_token') ?>
    <?= $this->form->text('mailgun_api_token', $values) ?>

    <?= $this->form->label(t('Mailgun domain'), 'mailgun_domain') ?>
    <?= $this->form->text('mailgun_domain', $values) ?>

    <p class="form-help"><a href="https://github.com/kanboard/plugin-mailgun" target="_blank"><?= t('Help on Mailgun integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>