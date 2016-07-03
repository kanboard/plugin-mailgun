Mailgun plugin for Kanboard
===========================

[![Build Status](https://travis-ci.org/kanboard/plugin-mailgun.svg?branch=master)](https://travis-ci.org/kanboard/plugin-mailgun)

Use [Mailgun](http://www.mailgun.com/) to create tasks directly by email or to send notifications.

- Send emails through Mailgun API
- Create tasks from incoming emails

Author
------

- Frederic Guillot
- License MIT

Requirements
------------

- Kanboard >= 1.0.29
- Mailgun API credentials

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/Mailgun`
3. Clone this repository into the folder `plugins/Mailgun`

Note: Plugin folder is case-sensitive.

Use Mailgun to send emails
--------------------------

You can configure Mailgun from the user interface or with the config file.

### Use the user interface

Set the API credentials in **Settings > Integrations > Mailgun**:

![mailgun-kanboard-settings](https://cloud.githubusercontent.com/assets/323546/16546189/b49c90d0-4110-11e6-8e08-6d3bd5ed992b.png)

- 1) This URL is used to receive incoming emails
- 2) Copy and paste your Mailgun API key
- 3) Enter the domain name that you have registered in Mailgun control panel

Set the mail transport in **Settings > Email Settings**:

![mailgun-mail-transport](https://cloud.githubusercontent.com/assets/323546/16546216/296ac512-4111-11e6-95af-2b34bf92ad3e.png)

1. Define an authorized email sender (an email address with the same domain as the one registered in Mailgun)
2. Select "Mailgun" as mail transport and save

### Use the config file

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
---------------------------------------

This integration works with the inbound email service of Mailgun (routes).
Kanboard use a webhook to handle incoming emails.

### Mailgun configuration

Create a new route in the web interface or via the API ([official documentation](https://documentation.mailgun.com/user_manual.html#routes)), here an example:

Example of rule:

```
match_recipient("^kanboard\+(.*)@mydomain.tld$")
forward("http://my_kanboard_domain.tld/mailgun/handler/a7b561a8c48ebb9d8bffc48a465587767bffa0dc42d1f4ee11efb4c2d1fb")
```

![mailgun-config](https://cloud.githubusercontent.com/assets/323546/16546260/b5fcefb8-4112-11e6-8ef7-de3899157cc1.png)

This example says: All emails likes `kanboard+myproject1@mydomain.tld` or `kanboard+myproject2@mydomain.tld` will be forwarded to the URL specified.

![mailgun-kanboard-settings](https://cloud.githubusercontent.com/assets/323546/16546189/b49c90d0-4110-11e6-8e08-6d3bd5ed992b.png)

The Kanboard webhook url is displayed in **Settings > Integrations > Mailgun**.
Make sure your application URL is correctly defined otherwise the generated URL will be wrong.

### Kanboard configuration

You must define a project identifier:

![mailgun-project-setttings](https://cloud.githubusercontent.com/assets/323546/16546282/d0ea805a-4113-11e6-857c-c0dff38ad401.png)

All emails sent to `kanboard+myproject@mydomain.tld` will be created into the defined project. 

1. Make sure that your users have an email address in their profiles
2. The sender email address must be same as the user profile in Kanboard
3. The user must be member of the project

Notes
-----

- Make sure the application url is defined correctly in application settings
- Task will be created in the first active swimlane
- Attachments are not supported yet
- Only email with textual content or simple HTML can be interpreted because the content is converted in Markdown

