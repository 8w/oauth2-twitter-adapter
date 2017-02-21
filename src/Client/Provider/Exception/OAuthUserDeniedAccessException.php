<?php
namespace OctOAuth\OAuth2\Client\Provider;

use Exception;

class OAuthUserDeniedAccessException extends Exception
{
    private $getParams;

    public function __construct(array $getParams = [])
    {
        parent::__construct("OAuth user denied access", 400);
        $this->getParams = $getParams;
    }

    public function getRequestParams(): array
    {
        return $this->getParams;
    }

    public function getRequestParamsAsString() : string
    {
        return print_r($this->getRequestParams(), true);
    }
}
