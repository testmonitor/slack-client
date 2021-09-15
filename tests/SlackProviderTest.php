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

class SlackProviderTest extends TestCase
{
    /** @test */
    public function it_should_return_an_empty_array_when_the_team_is_not_provided()
    {
        // Given
        $token = new AccessToken('12345', '123456', time() + 3600, ['incoming_webhook' => ['redirect' => 'https:/redirect.com']]);

        // When
        $team = $token->team();

        // Then
        $this->assertEquals([], $team);
    }

    /** @test */
    public function it_should_return_the_id_of_the_team()
    {
        // Given
        $token = new AccessToken('12345', '123456', time() + 3600, ['incoming_webhook' => ['channel' => '#testing'], 'team' => ['id' => 1]]);

        // When
        $team = $token->team();

        // Then
        $this->assertEquals(['id' => 1], $team);
    }

}
