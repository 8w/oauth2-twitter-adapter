<?php
namespace OctOAuth\OAuth2\Client\Provider\Token;

/**
 * Data object representing an OAuth1 temporary token and its corresponding secret
 *
 * @package OctOAuth\OAuth2\Client\Provider\Token
 */
class OAuth1TemporaryToken
{
    /** @var string */
    private $tokenValue;
    /** @var string */
    private $tokenSecret;

    /**
     * OAuth1TemporaryToken constructor.
     *
     * @param string $tokenValue  The value of the token
     * @param string $tokenSecret The corresponding token 'secret'
     */
    public function __construct(string $tokenValue, string $tokenSecret)
    {
        $this->tokenValue = $tokenValue;
        $this->tokenSecret = $tokenSecret;
    }

    /**
     * Get the token's value
     *
     * @return string The value of the token
     */
    public function getTokenValue(): string
    {
        return $this->tokenValue;
    }

    /**
     * Get the secret value
     *
     * @return string The value of the token's secret
     */
    public function getTokenSecret(): string
    {
        return $this->tokenSecret;
    }
}
