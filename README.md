# TestMonitor Slack Client

[![Latest Stable Version](https://poser.pugx.org/testmonitor/slack-client/v/stable)](https://packagist.org/packages/testmonitor/slack-client)
[![CircleCI](https://img.shields.io/circleci/project/github/testmonitor/slack-client.svg)](https://circleci.com/gh/testmonitor/slack-client)
[![Travis Build](https://travis-ci.com/testmonitor/slack-client.svg?branch=main)](https://travis-ci.com/testmonitor/slack-client)
[![Code Coverage](https://scrutinizer-ci.com/g/testmonitor/slack-client/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/testmonitor/slack-client/?branch=main)
[![Code Quality](https://scrutinizer-ci.com/g/testmonitor/slack-client/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/testmonitor/slack-client/?branch=main)
[![StyleCI](https://styleci.io/repos/401647581/shield)](https://styleci.io/repos/401647581)
[![License](https://poser.pugx.org/testmonitor/slack-client/license)](https://packagist.org/packages/testmonitor/slack-client)

This package provides a very basic, convenient, and unified wrapper for sending posts to Slack.
Out of the box, it comes with:
- Slack V2 OAuth 2.0 protocol for authentication (inspired by the [Slack Provider for OAuth 2.0 Client](https://github.com/adam-paterson/oauth2-slack)).
- The [Slack Block Kit for PHP](https://github.com/slack-php/slack-php-block-kit), a library that provides an OOP interface in PHP for composing messages/modals. 

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Tests](#tests)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)
  
## Installation

To install the client you need to require the package using composer:

	$ composer require testmonitor/slack-client

Use composer's autoload:

```php
require __DIR__.'/../vendor/autoload.php';
```

You're all set up now!

## Usage

This client only supports **oAuth 2.0 authentication**. You'll need a Slack application to proceed. If you haven't done so,
please read up with the [Slack authentication API docs](https://api.slack.com/authentication) on how
to create an application.

When you already have an application available, make sure it's a new Slack app, as this package does not
support Slack classic apps. To learn more about the differences between classic and new Slack apps, refer
to the [differences between old and new Slack apps](https://api.slack.com/authentication/quickstart).

When your Slack application is up and running, start with the oAuth authorization:

```php
$oauth = [
    'clientId' => '12345',
    'clientSecret' => 'abcdef',
    'redirectUri' => 'https://redirect.myapp.com/',
];

$slack = new \TestMonitor\Slack\Client($oauth);

header('Location: ' . $slack->authorizationUrl('incoming-webhook', 'state'));
exit();
```

This will redirect the user to a page asking confirmation for your app getting access to Slack. Make sure your redirectUrl points
back to your app. Slack will provide you with a temporary code that allows you to create an access code that can be used for
authentication. Route the redirect URL to the following code:

```php
$oauth = [
    'clientId' => '12345',
    'clientSecret' => 'abcdef',
    'redirectUri' => 'https://redirect.myapp.com/',
];

$slack = new \TestMonitor\Slack\Client($oauth);

$token = $slack->fetchToken($_REQUEST['code']);
```

When everything went ok, you should have an access token (available through AccessToken object). The AccessToken contains
all the information you should need to post a message using a webhook:

```php
var_dump ($token->getValues());

array() {
    ["ok"] => true
    ["app_id"] => "APPID"
    ["authed_user"] => array(1) {}
    ["scope"] => "incoming-webhook"
    ["token_type"] => "bot"
    ["bot_user_id"] => "USERID"
    ["team"] => array(2) {}
    ["enterprise"] => null
    ["is_enterprise_install"] => false
    ["incoming_webhook"] => array(4) {
      ["channel"] => "#testmonitor"
      ["channel_id"] => "CHANNELID"
      ["configuration_url"] => "https://domain.slack.com/services/B123456USA"
      ["url"] => "https://hooks.slack.com/services/T123456/B123456USA/tEsTm0n1t0r"
    }
```

Make sure to save incoming webhook URL in your database, you'll need this later to post messages.

In case your Slack app is not configured for token rotation, you're all done now! 

When token rotation has been enabled, your access token will be valid for **twelve hours**. After that, you'll have to refresh 
the token to regain access:

```php
$oauth = ['clientId' => '12345', 'clientSecret' => 'abcdef', 'redirectUri' => 'https://redirect.myapp.com/'];
$token = new \TestMonitor\Slack\Token('eyJ0...', '0/34ccc...', 1574600877); // the token you got last time

$slack = new \TestMonitor\Slack\Client($oauth, $token);

if ($token->expired()) {
    $newToken = $slack->refreshToken();
}
```

The new token will be valid again for the next twelve hours. 

## Examples

Post a simple message to Slack:

```php
$message = Kit::newMessage()->text('Hello world!');

$slack->postMessage('https://webhook.url/', $message);
```

Block Kit allows you to create way more comprehensive messages. Here's another example:

```php
$user = (object) ['name' => 'John Doe'];

$message = Kit::newMessage()
    ->tap(function (Message $message) {
        $message->newSection()
            ->mrkdwnText("*{$user->name}* created a new issue");
    })
    ->divider()
    ->tap(function (Message $message) {
        $message->newContext()
            ->mrkdwnText('Status: *Open*')
            ->mrkdwnText('Priority: *High*')
            ->mrkdwnText('Resolution: *Unresolved*');
    })

$slack->postMessage('https://webhook.url/', $message);
```

For more information on composing messages with Block Kit, head over to the [Slack Block Kit 
for PHP documentation](https://github.com/slack-php/slack-php-block-kit) or refer to the
official [Slack Block Kit documentation](https://api.slack.com/block-kit).

## Tests

The package contains integration tests. You can run them using PHPUnit.

    $ vendor/bin/phpunit
    
## Changelog

Refer to [CHANGELOG](CHANGELOG.md) for more information.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

## Credits

* **Thijs Kok** - *Lead developer* - [ThijsKok](https://github.com/thijskok)
* **Stephan Grootveld** - *Developer* - [Stefanius](https://github.com/stefanius)
* **Frank Keulen** - *Developer* - [FrankIsGek](https://github.com/frankisgek)
* **Muriel Nooder** - *Developer* - [ThaNoodle](https://github.com/thanoodle)

## License

The MIT License (MIT). Refer to the [License](LICENSE.md) for more information.
