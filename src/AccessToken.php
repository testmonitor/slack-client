<?php

namespace TestMonitor\Slack;

use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken as LeagueAccessToken;

class AccessToken
{
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string|null
     */
    public $refreshToken;

    /**
     * @var int|null
     */
    public $expiresIn;

    /**
     * @var array
     */
    public $values;

    /**
     * AccessToken constructor.
     *
     * @param string $accessToken
     * @param string|null $refreshToken
     * @param int|null $expiresIn
     * @param array $values
     */
    public function __construct(
        string $accessToken = '',
        ?string $refreshToken = null,
        ?int $expiresIn = null,
        array $values = []
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
        $this->values = $values;
    }

    /**
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return \TestMonitor\Slack\AccessToken
     */
    public static function fromSlack(AccessTokenInterface $token)
    {
        return new self(
            $token->getToken(),
            $token->getRefreshToken(),
            $token->getExpires(),
            $token->getValues()
        );
    }

    /**
     * Determines if the access token can expire.
     *
     * @return bool
     */
    public function canExpire()
    {
        return ! is_null($this->expiresIn);
    }

    /**
     * Determines if the access token has expired.
     *
     * @return bool
     */
    public function expired()
    {
        return ($this->expiresIn - time()) < 60;
    }

    /**
     * Returns the token as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'values' => $this->values,
        ];
    }

    /**
     * Returns the token as a League token.
     *
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function toNativeToken()
    {
        return new LeagueAccessToken($this->toArray());
    }
}
