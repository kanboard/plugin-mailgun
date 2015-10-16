<?php

namespace Kanboard\Plugin\Mailgun\Controller;

use Kanboard\Controller\Base;
use Kanboard\Plugin\Mailgun\EmailHandler;

/**
 * Webhook Controller
 *
 * @package  mailgun
 * @author   Frederic Guillot
 */
class Webhook extends Base
{
    /**
     * Handle Mailgun webhooks
     *
     * @access public
     */
    public function receiver()
    {
        $this->checkWebhookToken();

        $handler = new EmailHandler($this->container);
        echo $handler->receiveEmail($_POST) ? 'PARSED' : 'IGNORED';
    }
}
