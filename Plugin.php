<?php

namespace Kanboard\Plugin\Mailgun;

use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Mailgun Plugin
 *
 * @package  mailgun
 * @author   Frederic Guillot
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->emailClient->setTransport('mailgun', '\Kanboard\Plugin\Mailgun\EmailHandler');
        $this->template->hook->attach('template:config:integrations', 'mailgun:config/integration');
        $this->route->addRoute('/mailgun/handler/:token', 'WebhookController', 'receiver', 'mailgun');
        $this->applicationAccessMap->add('WebhookController', 'receiver', Role::APP_PUBLIC);
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginDescription()
    {
        return 'Mailgun Email Integration';
    }

    public function getPluginAuthor()
    {
        return 'Frédéric Guillot';
    }

    public function getPluginVersion()
    {
        return '1.0.7';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/plugin-mailgun';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.39';
    }
}
