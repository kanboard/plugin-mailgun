<?php

namespace Kanboard\Plugin\Mailgun;

require_once __DIR__.'/vendor/autoload.php';

use Exception;
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
            'from' => sprintf('%s <%s>', $author, $this->helper->mail->getMailSenderAddress()),
            'to' => sprintf('%s <%s>', $name, $email),
            'subject' => $subject,
            'html' => $html,
        );

        $this->httpClient->postFormAsync('https://api.mailgun.net/v3/'.$this->getDomain().'/messages', $payload, $headers);
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

        $taskId = $this->taskCreationModel->create(array(
            'project_id' => $project['id'],
            'title' => $this->getTitle($payload),
            'description' => trim($this->getDescription($payload)),
            'creator_id' => $user['id'],
            'swimlane_id' => $this->getSwimlaneId($project),
        ));

        if ($taskId > 0) {
            $this->addEmailBodyAsAttachment($taskId, $payload);
            $this->uploadAttachments($taskId, $payload);
            return true;
        }

        return false;
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
        $project = $this->projectModel->getByEmail($payload['recipient']);

        if (empty($project)) {
            $this->logger->info('Mailgun: ignored => project not found');
            return false;
        }

        // The user must be member of the project
        if (! $this->projectPermissionModel->isAssignable($project['id'], $user['id'])) {
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
            $htmlConverter = new HtmlConverter(array(
                'strip_tags'   => true,
                'remove_nodes' => 'meta script style link img span',
            ));

            $markdown = $htmlConverter->convert($payload['stripped-html']);

            // Document parsed incorrectly
            if (strpos($markdown, 'html') !== false && ! empty($payload['body-plain'])) {
                return $payload['body-plain'];
            }

            return $markdown;
        } elseif (! empty($payload['body-plain'])) {
            return $payload['body-plain'];
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

    protected function uploadAttachments($taskId, array $payload)
    {
        if (isset($payload['attachment-count']) && $payload['attachment-count'] > 0) {
            for ($i = 1; $i <= $payload['attachment-count']; $i++) {
                $this->uploadAttachment($taskId, 'attachment-' . $i);
            }
        }
    }

    protected function uploadAttachment($taskId, $name)
    {
        $fileInfo = $this->request->getFileInfo($name);

        if (! empty($fileInfo)) {
            try {
                $this->taskFileModel->uploadFile($taskId, $fileInfo);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function addEmailBodyAsAttachment($taskId, array $payload)
    {
        $filename = t('Email') . '.txt';
        $data = '';

        if (! empty($payload['body-html'])) {
            $data = $payload['body-html'];
            $filename = t('Email') . '.html';
        } elseif (! empty($payload['body-plain'])) {
            $data = $payload['body-plain'];
        }

        if (! empty($data)) {
            $this->taskFileModel->uploadContent($taskId, $filename, $data, false);
        }
    }
}
