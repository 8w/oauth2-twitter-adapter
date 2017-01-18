<?php
namespace OctOAuth\OAuth2\Client\Provider\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use OctOAuth\OAuth2\Client\Provider\ProviderInterface;
use OctOAuth\OAuth2\Client\Provider\Token\OAuth1TemporaryToken;
use OctOAuth\OAuth2\Client\Provider\Token\OAuth1TemporaryTokenStore;

/**
 * This class wraps the Twitter OAuth 1 provider from Abraham\TwitterOAuth to provide methods that
 * match the phpleague oauth2-client library.
 */
class TwitterOAuth1 implements ProviderInterface
{
    private $clientID = "";
    private $clientSecret = "";
    private $callbackURL = "";
    /** @var OAuth1TemporaryTokenStore */
    private $tokenStore;

    /**
     * Create a Twitter provider.
     *
     * @param array                     $credentials  An array of configuration parameters,
     *                                                including:
     *                                                'clientId'
     *                                                'clientSecret'
     *                                                'redirectUri'
     *
     * @param OAuth1TemporaryTokenStore $tokenStore   The place to store the temporary token and
     *                                                secret between page requests.
     */
    public function __construct(array $credentials, OAuth1TemporaryTokenStore $tokenStore)
    {
        $this->clientID = $credentials['clientId'];
        $this->clientSecret = $credentials['clientSecret'];
        $this->callbackURL = $credentials['redirectUri'];
        $this->tokenStore = $tokenStore;
    }

    /**
     * @inheritdoc
     *
     * @return string  Always returns an empty string - the OAuth1 provider uses a combination of a
     *                 nonce and a temporary token to identify CSRF
     */
    public function getState(): string
    {
        // state isn't used in OAUth1 - instead, the temporary resource token is passed
        // forward and then back again
        return "";
    }

    /**
     * @inheritdoc
     *
     * @param array $options IGNORED
     *
     * @return string if unable to connect to Twitter to retrieve a
     * request_token at the start of the OAuth1 process.
     * @throws IdentityProviderException if unable to connect to Twitter to retrieve a
     * request_token at the start of the OAuth1 process.
     */
    public function getAuthorizationUrl(array $options = null): string
    {
        $connection = new TwitterOAuth($this->clientID, $this->clientSecret);

        try {
            $request_token = $connection->oauth(
                'oauth/request_token',
                ['oauth_callback' => $this->callbackURL]);
        } catch (TwitterOAuthException $e) {
            throw new IdentityProviderException(
                "Unable to retrieve Twitter request_token",
                $e->getCode(),
                $e->getMessage());
        }

        $temporary_token = new OAuth1TemporaryToken(
            $request_token['oauth_token'],
            $request_token['oauth_token_secret']);

        $this->tokenStore->saveToken($temporary_token);

        $url = $connection->url(
            'oauth/authorize',
            ['oauth_token' => $temporary_token->getTokenValue()]);

        return $url;
    }

    /**
     * Check that the callback contains all required fields etc.
     *
     * @param string $state IGNORED
     *
     * @throws IdentityProviderException if some fields are missing
     */
    public function checkCallback($state = null)
    {
        $tempToken = $this->tokenStore->getToken();
        if (!isset($_REQUEST['oauth_token'])) {
            throw new IdentityProviderException(
                "No oauth_token received on callback",
                400,
                print_r($_GET, true));
        } elseif ($_REQUEST['oauth_token'] !== $tempToken->getTokenValue()) {
            throw new IdentityProviderException(
                "oauth_token received on callback ("
                . $_REQUEST['oauth_token']
                . ") doesn't match the one originally passed to Twitter ("
                . $tempToken->getTokenValue()
                . ")",
                400,
                print_r($_GET, true));
        } elseif (empty($_REQUEST['oauth_verifier'])) {
            throw new IdentityProviderException(
                "Twitter did not send back an oauth_verifier token",
                400,
                print_r($_GET, true));
        }
    }

    /**
     * Parse the auth code from the callback
     *
     * @return string the auth code
     */
    public function getAuthCodeFromCallback(): string
    {
        return $_REQUEST['oauth_verifier'];
    }

    /**
     * Call Twitter to convert the auth code into an access token.
     *
     * @param string $authCode The auth code to convert
     *
     * @return AccessToken The access token retrieved from Twitter
     * @throws IdentityProviderException should there be an issue making the call
     */
    public function getAccessTokenFromAuthCode(string $authCode): AccessToken
    {
        $tempToken = $this->tokenStore->getToken();
        $connection = new TwitterOAuth(
            $this->clientID,
            $this->clientSecret,
            $tempToken->getTokenValue(),
            $tempToken->getTokenSecret()
        );

        try {
            $tokenArray = $connection->oauth(
                "oauth/access_token",
                ["oauth_verifier" => $authCode]);
        } catch (TwitterOAuthException $e) {
            throw new IdentityProviderException(
                "Twitter call to convert auth_code $authCode to acces token failed",
                400,
                $e->getMessage());
        }

        // dump the temporary token now we have a full access token
        $this->tokenStore->clearToken();
        // rename the relevant key values to match the phpLeague ones, but retain the
        // Twitter-specific ones
        $this->renameArrayKey($tokenArray, "oauth_token", "access_token");
        $this->renameArrayKey($tokenArray, "user_id", "resource_owner_id");
        $this->renameArrayKey($tokenArray, "x_auth_expires", "expires");
        // oauth_token_secret
        // screen_name

        return new AccessToken($tokenArray);
    }

    /**
     * Get the Twitter user's details
     *
     * @param AccessToken $accessToken The access token to use
     *
     * @return ResourceOwnerInterface The Owner object
     * @throws IdentityProviderException should the call to Twitter fail
     */
    public function getResourceOwner(AccessToken $accessToken): ResourceOwnerInterface
    {
        $accessTokenValues = $accessToken->getValues();
        $connection = new TwitterOAuth(
            $this->clientID,
            $this->clientSecret,
            $accessToken->getToken(),
            $accessTokenValues["oauth_token_secret"]
        );

        $response = $connection->get("account/verify_credentials",
            [
                "include_entities" => "false",
                "skip_status" => "true",
                "include_email" => "true",
            ]);

        if ($connection->getLastHttpCode() !== 200) {
            throw new IdentityProviderException(
                "Unable to retrieve user profile from Twitter",
                $connection->getLastHttpCode(),
                $response);
        }

        return new TwitterResourceOwner($accessToken->getResourceOwnerId(), $response);
    }

    /**
     * NOTE does not preserve key ordering.
     *
     * @param array  $array The array - passed by reference
     * @param string $from  The current key name
     * @param string $to    The new key name
     */
    private function renameArrayKey(array &$array, string $from, string $to)
    {
        $array[$to] = $array[$from];
        unset($array[$from]);
    }
}
