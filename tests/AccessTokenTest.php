<?php

namespace TestMonitor\Slack\Tests;

use Mockery;
use SlackPhp\BlockKit\Kit;
use TestMonitor\Slack\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\AccessToken;
use TestMonitor\Slack\Provider\SlackAuthorizedUser;
use TestMonitor\Slack\Exceptions\TokenExpiredException;
use TestMonitor\Slack\Exceptions\UnauthorizedException;

class AccessTokenTest extends TestCase
{
    /** @test */
    public function it_should_return_the_name_of_the_channel()
    {
        // Given
        $token = new AccessToken('12345', '123456', time() + 3600, ['incoming_webhook' => ['channel' => '#testing']]);

        // When
        $channel = $token->channel();

        // Then
        $this->assertEquals('#testing', $channel);
    }

    /** @test */
    public function it_should_return_an_empty_string_when_the_channel_is_not_provided()
    {
        // Given
        $token = new AccessToken('12345', '123456', time() + 3600, ['incoming_webhook' => ['redirect' => 'https:/redirect.com']]);

        // When
        $channel = $token->channel();

        // Then
        $this->assertEquals('', $channel);
    }
}
