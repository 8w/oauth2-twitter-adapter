<?php
namespace OctOAuth\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Common interface summarising the methods used as part of standard OAuthentication.
 *
 * @package OctOAuth\OAuth2\Client\Provider
 */
interface ProviderInterface
{
    /**
     * Get a state string.  NOTE that you should call getAuthorizationUrl() first in order to
     * generate the state string.
     *
     * @return string The state string
     */
    public function getState(): string;

    /**
     * Get the URL that the user should be redirected to to authorize the token request.
     *
     * @param array $options An array of configuration options
     *
     * @return string The URL.
     * @throws IdentityProviderException If the URL cannot be generated.  For example, in the case
     *                                   of Twitter (which uses OAuth1), a temporary token must be
     *                                   retrieved from Twitter to be included in this URL.
     */
    public function getAuthorizationUrl(array $options = null): string;

    /**
     * Call the provider to convert the auth code into an access token.
     *
     * @param string $authCode The auth_code to be converted into an access token
     *
     * @return AccessToken The access token
     * @throws IdentityProviderException If the call to the provider fails
     */
    public function getAccessTokenFromAuthCode(string $authCode): AccessToken;

    /**
     * Retrieve the resource owner's details from the provider, identifying them using the access
     * token.
     *
     * @param AccessToken $accessToken The access token previously created
     *
     * @return ResourceOwnerInterface The resource owner object
     * @throws IdentityProviderException If the call to the provider to retrieve the owner details
     *                                   fails
     */
    public function getResourceOwner(AccessToken $accessToken): ResourceOwnerInterface;

    /**
     * Validate that the callback (where the user is redirected back after authorising the grant)
     * contains all of the expected parameters, including things like the CSRF state parameter and
     * the access code.
     *
     * @param string $state The state (CSRF token) value to be checked against the response
     *
     * @throws IdentityProviderException If the callback did not include the necessary values
     */
    public function checkCallback(string $state = null);

    /**
     * Parse the auth code out of the callback.
     *
     * NOTE:  Ensure you've called checkCallback() before calling this method, to make sure
     * nothing's gone wrong and that the auth code is there.
     *
     * @return string The authentication code retrieved from the callback
     * @see OAuthProvider::checkCallback()
     */
    public function getAuthCodeFromCallback(): string;
}
