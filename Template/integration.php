<h3><img src="<?= $this->url->dir() ?>plugins/Mailgun/mailgun-icon.png"/>&nbsp;Mailgun</h3>
<div class="listing">
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('webhook', 'receiver', array('plugin' => 'mailgun', 'token' => $values['webhook_token']), false, '', true) ?>"/><br/>
    <p class="form-help"><a href="https://github.com/kanboard/plugin-mailgun" target="_blank"><?= t('Help on Mailgun integration') ?></a></p>
</div>