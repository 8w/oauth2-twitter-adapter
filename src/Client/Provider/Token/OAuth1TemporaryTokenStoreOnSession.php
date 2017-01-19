<?php
namespace OctOAuth\OAuth2\Client\Provider\Token;

use Abraham\TwitterOAuth\Token;

/**
 * Store the token on the $_SESSION
 *
 * @package OctOAuth\OAuth2\Client\Provider\Token
 */
class OAuth1TemporaryTokenStoreOnSession implements OAuth1TemporaryTokenStore
{
    private static $sessionTemporaryToken = "Authenticator.twitter.token_key";
    private static $sessionTemporaryTokenSecret = "Authenticator.twitter.token_secret";

    /** @inheritdoc */
    public function saveToken(Token $token)
    {
        $_SESSION[self::$sessionTemporaryToken] = $token->key;
        $_SESSION[self::$sessionTemporaryTokenSecret] = $token->secret;
    }

    /** @inheritdoc */
    public function getToken(): Token
    {
        return new Token(
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
