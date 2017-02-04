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

- Kanboard >= 1.0.39
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

### Use the config file (alternative method)

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

Create a new route in Mailgun control panel:

![Mailgun Route](https://cloud.githubusercontent.com/assets/323546/22621567/c7158a4e-eaf4-11e6-8d12-6e102e84c2f8.png)

1. Select "Match recipient" for expression type
2. In the recipient field, enter the email address of the Kanboard project
3. In the action field, copy and paste the Mailgun Webhook URL from Kanboard

The Mailgun webhook url is displayed in **Settings > Integrations > Mailgun**.

Make sure your application URL is correctly defined otherwise the generated URL will be wrong.

### Kanboard configuration

You must define an email address for your project:

![Project Settings](https://cloud.githubusercontent.com/assets/323546/22621584/2bd9eaf6-eaf5-11e6-9510-258b1c84300b.png)

1. Set an email address for your project (in Edit Project)
2. The sender email address must be same as the user profile in Kanboard
3. The user must be member of the project

Notes
-----

- Make sure the application url is defined correctly in application settings
- Task will be created in the first active swimlane
- Only email with textual content or simple HTML can be interpreted because the content is converted in Markdown by the plugin

Changes
-------

### Version 1.0.7

- Add original email body as attachment
- Add support for attachments
- Use project email address instead of project identifier (Kanboard >=1.0.39)
