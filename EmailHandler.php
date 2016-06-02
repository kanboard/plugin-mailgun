<?php

namespace Kanboard\Plugin\Mailgun;

require_once __DIR__.'/vendor/autoload.php';

use Kanboard\Core\Base;
use Kanboard\Core\Mail\ClientInterface;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Mailgun Mail Handler
 *
 * @package  mailgun
 * @author   Frederic Guillot
 */
class EmailHandler extends Base implements ClientInterface
{
    /**
     * Send a HTML email
     *
     * @access public
     * @param  string  $email
     * @param  string  $name
     * @param  string  $subject
     * @param  string  $html
     * @param  string  $author
     */
    public function sendEmail($email, $name, $subject, $html, $author)
    {
        $headers = array(
            'Authorization: Basic '.base64_encode('api:'.$this->getApiToken())
        );

        $payload = array(
            'from' => sprintf('%s <%s>', $author, MAIL_FROM),
            'to' => sprintf('%s <%s>', $name, $email),
            'subject' => $subject,
            'html' => $html,
        );

        $this->httpClient->postForm('https://api.mailgun.net/v3/'.$this->getDomain().'/messages', $payload, $headers);
    }

    /**
     * Parse incoming email
     *
     * @access public
     * @param  array   $payload   Incoming email
     * @return boolean
     */
    public function receiveEmail(array $payload)
    {
        $result = $this->validate($payload);

        if ($result === false) {
            return false;
        }

        list($user, $project) = $result;

        return (bool) $this->taskCreationModel->create(array(
            'project_id' => $project['id'],
            'title' => $this->getTitle($payload),
            'description' => $this->getDescription($payload),
            'creator_id' => $user['id'],
            'swimlane_id' => $this->getSwimlaneId($project),
        ));
    }

    /**
     * Validate incoming email
     *
     * @access public
     * @param  array $payload
     * @return array|boolean
     */
    public function validate(array $payload)
    {
        if (empty($payload['sender']) || empty($payload['subject']) || empty($payload['recipient'])) {
            return false;
        }

        // The user must exists in Kanboard
        $user = $this->userModel->getByEmail($payload['sender']);

        if (empty($user)) {
            $this->logger->info('Mailgun: ignored => user not found');
            return false;
        }

        // The project must have a short name
        $project = $this->projectModel->getByIdentifier($this->helper->mail->getMailboxHash($payload['recipient']));

        if (empty($project)) {
            $this->logger->info('Mailgun: ignored => project not found');
            return false;
        }

        // The user must be member of the project
        if (! $this->projectPermissionModel->isMember($project['id'], $user['id'])) {
            $this->logger->info('Mailgun: ignored => user is not member of the project');
            return false;
        }

        return array($user, $project);
    }

    /**
     * Get task title
     *
     * @access public
     * @param  array $payload
     * @return string
     */
    public function getTitle(array $payload)
    {
        return $this->helper->mail->filterSubject($payload['subject']);
    }

    /**
     * Get Markdown content for the task
     *
     * @access public
     * @param  array $payload
     * @return string
     */
    public function getDescription(array $payload)
    {
        if (! empty($payload['stripped-html'])) {
            $htmlConverter = new HtmlConverter(array('strip_tags' => true));
            return $htmlConverter->convert($payload['stripped-html']);
        } elseif (! empty($payload['stripped-text'])) {
            return $payload['stripped-text'];
        }

        return '';
    }

    /**
     * Get swimlane id
     *
     * @access public
     * @param  array $project
     * @return string
     */
    public function getSwimlaneId(array $project)
    {
        $swimlane = $this->swimlaneModel->getFirstActiveSwimlane($project['id']);
        return empty($swimlane) ? 0 : $swimlane['id'];
    }

    /**
     * Get API token
     *
     * @access public
     * @return string
     */
    public function getApiToken()
    {
        if (defined('MAILGUN_API_TOKEN')) {
            return MAILGUN_API_TOKEN;
        }

        return $this->configModel->get('mailgun_api_token');
    }

    /**
     * Get Mailgun domain
     *
     * @access public
     * @return string
     */
    public function getDomain()
    {
        if (defined('MAILGUN_DOMAIN')) {
            return MAILGUN_DOMAIN;
        }

        return $this->configModel->get('mailgun_domain');
    }
}
