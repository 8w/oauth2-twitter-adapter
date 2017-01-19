# Twitter OAuth1 Wrapper for Consistency with phpleague/oauth2-client

Twitter is conspicously absent from the list of OAuth2 providers [support by the phpleague's oauth2-client](https://github.com/thephpleague/oauth2-client/blob/master/docs/providers/thirdparty.md).  That's because Twitter doesn't support OAuth2 for User Authentication, though they do offer OAuth 1.0 support.  

This package provides a wrapper for the excellent `abraham/twitteroauth` library (which uses OAuth 1.0 to interact with Twitter), so that it can be used more easily alongside the various PhpLeague's OAuth2 clients.
 
 ## Installation
 
 To install, use [composer](https://getcomposer.org/):
 
```bash
./composer.phar require octoauth/oauth2-twitter-adapter
```

## Usage

### Begin Auth
Instantiate a `TwitterOAuth1` client, passing in a mechanism for retaining the OAuth1 temporary token between page requests.  A session-based store is provided, but if you run a session-less app you can very easily create your own implementation of `OAuth1TemporaryTokenStore` to use a database or alternative:

```php
<?php
use OctOAuth\OAuth2\Client\Provider\Token\OAuth1TemporaryTokenStoreOnSession;
use OctOAuth\OAuth2\Client\Provider\Twitter\TwitterOAuth1;


$provider = new TwitterOAuth1(
    [
        // NOTE that the credentials array matches the format of phpleague/oauth2-clients
        'clientId'      => "YOUR_TWITTER_KEY",
        'clientSecret'  => "YOUR_TWITTER_SECRET",
        'redirectUri'   => "https://your-app.com/oauthCallbackPage?Provider=Twitter",
    ],
    new OAuth1TemporaryTokenStoreOnSession());

// You can pass-in an array of options such as scope for OAuth2 providers, but they
// aren't supported by the Twitter OAuth1 provider.  You app's scope is configured
// at https://apps.twitter.com/app
$authURL = $provider->getAuthorizationUrl();

header('Location: {$authURL}', true, 303);
```

Your user will now be sent off to the OAuth authorization page, and be redirected back to the `redirectUri` configured above when they've either approved or denied the authorization.

### Finish Auth

At your `redirectUri` you should create the provider as before (or load it from the session if you cached it), check that the callback contains everything required for successful authentication and then complete the login.  At that point you'll have an `AccessToken` which you can use as you wish - to retrieve the `ResourceOwner` in the example below.
 
```php
<?php
use OctOAuth\OAuth2\Client\Provider\Token\OAuth1TemporaryTokenStoreOnSession;
use OctOAuth\OAuth2\Client\Provider\Twitter\TwitterOAuth1;


$provider = new TwitterOAuth1(
    [
        // NOTE that the credentials array matches the format of phpleague/oauth2-clients
        'clientId'      => "YOUR_TWITTER_KEY",
        'clientSecret'  => "YOUR_TWITTER_SECRET",
        'redirectUri'   => "https://your-app.com/oauthCallbackPage?Provider=Twitter",
    ],
    new OAuth1TemporaryTokenStoreOnSession());


// an IdentityProviderException will be thrown if the authorization failed
$provider->checkCallback();
$authCode = $authCode = $provider->getAuthCodeFromCallback();
// and call Twitter to convert it to an AccessToken (which you'll likely want to store somewhere
// for later use)
$token = $provider->getAccessTokenFromAuthCode($authCode);

// at this point you can call the Twitter API to get your ResourceOwner
$resourceOwner = $provider->getResourceOwner($token);

echo "Twitter user ID: " . $resourceOwner->getId();
echo "\nTwitter screen name: " . $resourceOwner->getScreenName();
```

### Make API Calls

Now you've got an access token, you can make calls to the [Twitter REST APIs](https://dev.twitter.com/rest/reference) on behalf of the user:

```php
<?php
use OctOAuth\OAuth2\Client\Provider\Token\OAuth1TemporaryTokenStoreOnSession;
use OctOAuth\OAuth2\Client\Provider\Twitter\TwitterOAuth1;


$provider = new TwitterOAuth1(
    [
         // NOTE that the credentials array matches the format of phpleague/oauth2-clients
         'clientId'      => "YOUR_TWITTER_KEY",
         'clientSecret'  => "YOUR_TWITTER_SECRET",
         'redirectUri'   => "https://your-app.com/oauthCallbackPage?Provider=Twitter",
    ],
    new OAuth1TemporaryTokenStoreOnSession());

// retrieve your token from the session or wherever you put it when you completed
// authorization, then...
$client = $provider->getAuthenticatedConnection($token);
$apiResponse = $client->get("search/tweets", ["q" => "twitterapi"]);

echo "Retrieved Tweets: " . print_r($apiResponse, true); 
```

## Easing Use Alongside OAuth2-Clients

In order to complete the user authorization process without caring whether you're using an OAuth1.0 or OAuth2 provider, wrap your phpleague/oauth2-client implementation in the provided `LeagueOAuth2Adapter`, so that it implements the `ProviderInterface`.  You can then interact with the adapter the same way as described above:

```php
<?php
use OctOAuth\OAuth2\Client\Provider\LeagueOAuth2Adapter;
use League\OAuth2\Client\Provider\Google;

// doesn't have to be Google - use any of the phpleague/oauth2-client implementations
$provider = new LeagueOAuth2Adapter( new Google(
            [
                 'clientId'      => "YOUR_GOOGLE_KEY",
                 'clientSecret'  => "YOUR_GOOGLE_SECRET",
                 'redirectUri'   => "https://your-app.com/oauthCallbackPage?Provider=Google",
            ]
    ));

// ...
// go through the authorizations sequence described above...
// ...

// once you have a token you can get your ResourceOwner
$resourceOwner = $provider->getResourceOwner($token);

echo "Google user ID: " . $resourceOwner->getId();
echo "\nGoogle user's name: " . $resourceOwner->getName();

// you can also access the underlying provider to make authenticated calls to the APIs
$googleProvider = $provider->getProvider();
$googleProvider->getAuthenticatedRequest("GET", "https://www.googleapis.com/plus/v1/people/me", $token);
```
