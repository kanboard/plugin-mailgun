Mailgun plugin for Kanboard
============================

Use [Mailgun](http://www.mailgun.com/) to create tasks directly by email or to send notifications.

- Send emails through Mailgun API
- Create tasks from incoming emails

Author
------

- Frederic Guillot
- License MIT

Installation
------------

- Create a folder **plugins/Mailgun**
- Copy all files under this directory

Use Mailgun to send emails
---------------------------

Define those constants in your `config.php` file to send notifications with Mailgun:

```php
// We choose "mailgun" as mail transport
define('MAIL_TRANSPORT', 'mailgun');

// Mailgun API key
define('MAILGUN_API_TOKEN', 'YOUR_API_KEY');

// Mailgun domain name
define('MAILGUN_DOMAIN', 'YOUR_DOMAIN_CONFIGURED_IN_MAILGUN');

// Be sure to use the sender email address configured in Mailgun
define('MAIL_FROM', 'sender-address-configured-in-mailgun@example.org');
```

Use Mailgun to create tasks from emails
----------------------------------------

You can use the service [Mailgun](http://www.mailgun.com/) to create tasks directly by email.

This integration works with the inbound email service of Mailgun (routes).
Kanboard use a webhook to handle incoming emails.

### Mailgun configuration

Create a new route in the web interface or via the API ([official documentation](https://documentation.mailgun.com/user_manual.html#routes)), here an example:

```
match_recipient("^kanboard\+(.*)@mydomain.tld$")
forward("https://mykanboard/?controller=webhook&action=receiver&plugin=mailgun&token=mytoken")
```

The Kanboard webhook url is displayed in **Settings > Integrations > Mailgun**

### Kanboard configuration

1. Be sure that your users have an email address in their profiles
2. Assign a project identifier to the desired projects: **Project settings > Edit**
3. Try to send an email to your project: something+myproject@mydomain.tld

The sender email address must be same as the user profile in Kanboard and the user must be member of the project.
