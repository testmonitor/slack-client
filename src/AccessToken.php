<?php

namespace TestMonitor\Slack;

use League\OAuth2\Client\Token\AccessTokenInterface;
use TestMonitor\Slack\Exceptions\MissingWebhookException;

class AccessToken
{
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var int
     */
    public $expiresIn;

    /**
     * @var array
     */
    public $values;

    /**
     * Token constructor.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expiresIn
     * @param array $values
     */
    public function __construct(string $accessToken = '', string $refreshToken = '', int $expiresIn = 0, array $values = [])
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
        $this->values = $values;
    }

    /**
     * @param \League\OAuth2\Client\Token\AccessToken $token
     *
     * @return \TestMonitor\Slack\AccessToken
     */
    public static function fromSlack(AccessTokenInterface $token, $values = [])
    {
        return new self(
            $token->getToken(),
            $token->getRefreshToken(),
            $token->getExpires(),
            array_merge(
                $values,
                $token->getValues()
            ),
        );
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
     * Returns the team id and name.
     *
     * @return array
     */
    public function team()
    {
        return $this->values['team'] ?? [];
    }

    /**
     * Returns the incoming webhook data.
     *
     * @throws \TestMonitor\Slack\Exceptions\MissingWebhookException
     * @return array
     */
    public function incomingWebhook()
    {
        if (empty($this->values['incoming_webhook'])) {
            throw new MissingWebhookException();
        }

        return $this->values['incoming_webhook'];
    }

    /**
     * Returns the channel name the webhook is assigned to.
     *
     * @throws \TestMonitor\Slack\Exceptions\MissingWebhookException
     * @return string
     */
    public function channel()
    {
        return $this->incomingWebhook()['channel'] ?? '';
    }

    /**
     * Returns the URL of the webhook.
     *
     * @throws \TestMonitor\Slack\Exceptions\MissingWebhookException
     * @return string
     */
    public function webhookUrl()
    {
        return $this->incomingWebhook()['url'] ?? '';
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
}
