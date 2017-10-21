# QuickBooks Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/rabrowne85/oauth2-quickbooks.svg?style=flat-square)](https://github.com/rabrowne85/oauth2-quickbooks/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
<!-- [![Build Status](https://img.shields.io/travis/thephpleague/oauth2-instagram/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-instagram) -->
<!-- [![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/oauth2-instagram.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-instagram/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-instagram.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-instagram) -->
[![Total Downloads](https://img.shields.io/packagist/dt/rabrowne/oauth2-quickbooks.svg?style=flat-square)](https://packagist.org/packages/rabrowne85/oauth2-quickbooks)

This package provides QuickBooks OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require rabrowne85/oauth2-quickbooks
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\QuickBooks` as the provider.

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\QuickBooks([
    'clientId'          => '{quickbooks-client-id}',
    'clientSecret'      => '{quickbooks-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'host'              => 'https://quickbooks.api.intuit.com' // Optional, defaults to https://quickbooks.api.intuit.com
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your QuickBooks authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['accounting','payments'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the [following scopes are available](https://instagram.com/developer/authentication/#scope).

- accounting
- payments
- openid
- profile
- email
- phone
- address

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/rabrowne85/oauth2-quickbooks/blob/master/LICENSE) for more information.
