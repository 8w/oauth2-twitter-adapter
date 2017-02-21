<?php

namespace OctOAuth\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * An adapter class that implements the ProviderInterface (but passes almost everything straight
 * through to the underlying Provider).
 *
 * @package OctOAuth\OAuth2\Client\Provider
 */
class LeagueOAuth2Adapter implements ProviderInterface
{
    /** @var AbstractProvider */
    private $provider;

    /**
     * LeagueOAuth2Adapter constructor.
     *
     * @param AbstractProvider $provider The Provider implementation (already initialised/
     *                                   configured)
     */
    public function __construct(AbstractProvider $provider)
    {
        $this->provider = $provider;
    }

    /** @inheritdoc */
    public function getState()
    {
        return $this->provider->getState();
    }

    /** @inheritdoc */
    public function getAuthorizationUrl(array $options = null): string
    {
        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Call the underlying Provider to get an access token
     *
     * @param string $authCode The auth code to convert into an access token (via the provider)
     *
     * @return AccessToken The access token
     */
    public function getAccessTokenFromAuthCode(string $authCode): AccessToken
    {
        return $this->provider->getAccessToken("authorization_code", [
            "code" => $authCode
        ]);
    }

    /**
     * Use an access token to retrieve the resource owner.
     *
     * @param AccessToken $accessToken the access token
     *
     * @return ResourceOwnerInterface The User's data
     */
    public function getResourceOwner(AccessToken $accessToken): ResourceOwnerInterface
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    /**
     * @param string $state The "state" CSRF value to check
     *
     * @throws IdentityProviderException If the callback did not include the necessary values
     * @throws OAuthUserDeniedAccessException If the user denied access
     * @return void
     */
    public function checkCallback(string $state = null)
    {
        if (!empty($_GET['error'])) {
            if ($_GET['error'] === "access_denied") {
                throw new OAuthUserDeniedAccessException($_GET["error"]);
            } else {
                throw new IdentityProviderException($_GET["error"], 400, "");
            }
        }

        if (!isset($_GET['state'])) {
            throw new IdentityProviderException("State was not included in the callback", 400, "");
        }

        if ($_GET['state'] !== $state) {
            throw new IdentityProviderException("State received (" .
                $_GET['state']
                . ") does not match expected ("
                . $state
                . ")", 400, "");
        }

        if (empty($_GET["code"])) {
            throw new IdentityProviderException(
                "No authorization code received from provider",
                400,
                "");
        }
    }

    /**
     * Parse the auth code out of the callback.
     *
     * @return string the auth_code
     */
    public function getAuthCodeFromCallback(): string
    {
        return $_GET["code"];
    }

    /**
     * Access the underlying Provider object so that it can be used for authenticated (and
     * unauthenticated) requests.
     *
     * @return AbstractProvider The provider.
     */
    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }
}
