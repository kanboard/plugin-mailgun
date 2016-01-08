<?php

require_once 'tests/units/Base.php';

use Kanboard\Plugin\Mailgun\EmailHandler;
use Kanboard\Model\TaskCreation;
use Kanboard\Model\TaskFinder;
use Kanboard\Model\Project;
use Kanboard\Model\ProjectUserRole;
use Kanboard\Model\User;
use Kanboard\Core\Security\Role;

class EmailHandlerTest extends Base
{
    public function testSendEmail()
    {
        $pm = new EmailHandler($this->container);
        $pm->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob');

        $this->assertStringStartsWith('https://api.mailgun.net/v3/', $this->container['httpClient']->getUrl());

        $data = $this->container['httpClient']->getData();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('html', $data);

        $this->assertEquals('Me <test@localhost>', $data['to']);
        $this->assertEquals('Bob <notifications@kanboard.local>', $data['from']);
        $this->assertEquals('Test', $data['subject']);
        $this->assertEquals('Content', $data['html']);
    }

    public function testHandlePayload()
    {
        $w = new EmailHandler($this->container);
        $p = new Project($this->container);
        $pp = new ProjectUserRole($this->container);
        $u = new User($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFinder($this->container);

        $this->assertEquals(2, $u->create(array('username' => 'me', 'email' => 'me@localhost')));

        $this->assertEquals(1, $p->create(array('name' => 'test1')));
        $this->assertEquals(2, $p->create(array('name' => 'test2', 'identifier' => 'TEST1')));

        // Empty payload
        $this->assertFalse($w->receiveEmail(array()));

        // Unknown user
        $this->assertFalse($w->receiveEmail(array('sender' => 'a@b.c', 'subject' => 'Email task', 'recipient' => 'foobar', 'stripped-text' => 'boo')));

        // Project not found
        $this->assertFalse($w->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'foo+test@localhost', 'stripped-text' => 'boo')));

        // User is not member
        $this->assertFalse($w->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'foo+test1@localhost', 'stripped-text' => 'boo')));
        $this->assertTrue($pp->addUser(2, 2, Role::PROJECT_MEMBER));

        // The task must be created
        $this->assertTrue($w->receiveEmail(array('sender' => 'me@localhost', 'subject' => 'Email task', 'recipient' => 'foo+test1@localhost', 'stripped-html' => '<strong>boo</strong>')));

        $task = $tf->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**boo**', $task['description']);
        $this->assertEquals(2, $task['creator_id']);
    }
}
