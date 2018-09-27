<?php

require_once 'tests/units/Base.php';

use Kanboard\Plugin\Mailgun\EmailHandler;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectMetadataModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\UserModel;
use Kanboard\Core\Security\Role;

class EmailHandlerTest extends Base
{
    public function testGetApiToken()
    {
        $handler = new EmailHandler($this->container);
        $this->assertEmpty($handler->getApiToken());

        $this->container['configModel']->save(array('mailgun_api_token' => 'my token'));
        $this->container['memoryCache']->flush();

        $this->assertEquals('my token', $handler->getApiToken());
    }

    public function testGetDomain()
    {
        $handler = new EmailHandler($this->container);
        $this->assertEmpty($handler->getDomain());

        $this->container['configModel']->save(array('mailgun_domain' => 'my domain'));
        $this->container['memoryCache']->flush();

        $this->assertEquals('my domain', $handler->getDomain());
    }

    public function testSendEmail()
    {
        $handler = new EmailHandler($this->container);

        $headers = array(
            'Authorization: Basic '.base64_encode('api:my token')
        );

        $this->container['configModel']
            ->save(array('mailgun_api_token' => 'my token', 'mailgun_domain' => 'my_domain'));

        $this->container['httpClient']
            ->expects($this->once())
            ->method('postFormAsync')
            ->with(
                'https://api.mailgun.net/v3/my_domain/messages',
                $this->anything(),
                $headers
            );

        $handler->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob');
    }

    public function testSendEmailWithAuthorEmail()
    {
        $handler = new EmailHandler($this->container);

        $headers = array(
            'Authorization: Basic '.base64_encode('api:my token')
        );

        $this->container['configModel']
            ->save(array('mailgun_api_token' => 'my token', 'mailgun_domain' => 'my_domain'));

        $this->container['httpClient']
            ->expects($this->once())
            ->method('postFormAsync')
            ->with(
                'https://api.mailgun.net/v3/my_domain/messages',
                $this->contains('bob@localhost'),
                $headers
            );

        $handler->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob', 'bob@localhost');
    }

    public function testHandlePayload()
    {
        $emailHandler = new EmailHandler($this->container);
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $userModel = new UserModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectMetadataModel = new ProjectMetadataModel($this->container);

        $this->assertEquals(2, $userModel->create(array('username' => 'me', 'email' => 'me@localhost')));

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(2, $projectModel->create(array('name' => 'test2', 'email' => 'test2@localhost')));
        $this->assertEquals(3, $projectModel->create(array('name' => 'test4', 'email' => 'test4@localhost')));

        // Allow project 3 to receive E-Mail from any sender
        $this->assertTrue($projectMetadataModel->save(3, array('MailgunProject_catchall' => 'me@localhost')));

        // Empty payload
        $this->assertFalse($emailHandler->receiveEmail(array()));

        // Unknown user
        $this->assertFalse($emailHandler->receiveEmail(array('sender' => 'a@b.c', 'subject' => 'Email task', 'recipient' => 'foobar', 'stripped-text' => 'boo')));

        // Unknown sender mapped to known user in project test4
        $this->assertTrue($emailHandler->receiveEmail(array('sender' => 'd@e.f', 'subject' => 'Email task', 'recipient' => 'test4@localhost', 'stripped-text' => 'boo')));

        // Project not found
        $this->assertFalse($emailHandler->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'test3@localhost', 'stripped-text' => 'boo')));

        // User is not member
        $this->assertFalse($emailHandler->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'test2@localhost', 'stripped-text' => 'boo')));
        $this->assertTrue($projectUserRoleModel->addUser(2, 2, Role::PROJECT_MEMBER));

        // The task must be created
        $this->assertTrue($emailHandler->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'test2@localhost', 'stripped-html' => '<strong>boo</strong>')));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**boo**', $task['description']);
        $this->assertEquals(2, $task['creator_id']);
    }

    public function testGetSubject()
    {
        $handler = new EmailHandler($this->container);
        $this->assertEquals('Test', $handler->getTitle(array('subject' => 'Test')));
        $this->assertEquals('Test', $handler->getTitle(array('subject' => 'RE: Test')));
        $this->assertEquals('Test', $handler->getTitle(array('subject' => 'FW: Test')));
    }

    public function testGetDescription()
    {
        $handler = new EmailHandler($this->container);
        $this->assertEquals('**Test**', $handler->getDescription(array('stripped-html' => '<b>Test</b>')));
        $this->assertEquals('foobar', $handler->getDescription(array('stripped-html' => '', 'body-plain' => 'foobar')));
        $this->assertEquals('', $handler->getDescription(array('stripped-html' => '', 'body-plain' => '')));
    }
}
