<?php

namespace TestMonitor\Slack\Actions;

use SlackPhp\BlockKit\Surfaces\Message;
use TestMonitor\Slack\Exceptions\UnauthorizedException;

trait PostMessages
{
    /**
     * Post a new message.
     *
     * @param \SlackPhp\BlockKit\Surfaces\Message $message
     *
     * @throws \TestMonitor\Slack\Exceptions\FailedActionException
     * @throws \TestMonitor\Slack\Exceptions\MissingWebhookException
     * @throws \TestMonitor\Slack\Exceptions\NotFoundException
     * @throws \TestMonitor\Slack\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Slack\Exceptions\ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return bool
     */
    public function postMessage(Message $message)
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        $response = $this->post($this->token->webhookUrl(), [
            'json' => $message,
        ]);

        return $response === 'ok';
    }
}
