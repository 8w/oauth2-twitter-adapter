<?php
namespace OctOAuth\OAuth2\Client\Provider\Token;

/**
 * Store the token on the $_SESSION
 *
 * @package OctOAuth\OAuth2\Client\Provider\Token
 */
class OAuth1TemporaryTokenStoreOnSession implements OAuth1TemporaryTokenStore
{
    private static $sessionTemporaryToken = "Authenticator.twitter.temporary_token";
    private static $sessionTemporaryTokenSecret = "Authenticator.twitter.temporary_token_secret";

    /** @inheritdoc */
    public function saveToken(OAuth1TemporaryToken $token)
    {
        $_SESSION[self::$sessionTemporaryToken] = $token->getTokenValue();
        $_SESSION[self::$sessionTemporaryTokenSecret] = $token->getTokenSecret();
    }

    /** @inheritdoc */
    public function getToken(): OAuth1TemporaryToken
    {
        return new OAuth1TemporaryToken(
            $_SESSION[self::$sessionTemporaryToken],
            $_SESSION[self::$sessionTemporaryTokenSecret]
        );
    }

    /** @inheritdoc */
    public function clearToken()
    {
        unset($_SESSION[self::$sessionTemporaryToken]);
        unset($_SESSION[self::$sessionTemporaryTokenSecret]);
    }
}
