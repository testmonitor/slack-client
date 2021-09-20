<?php

namespace TestMonitor\Slack\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\AccessToken;

class AccessTokenTest extends TestCase
{
    /** @test */
    public function it_can_return_the_access_token_as_an_array()
    {
        // Given
        $time = time() + 3600;
        $token = new AccessToken('12345', '123456', $time, ['incoming_webhook' => ['channel' => '#testing']]);

        // When
        $array = $token->toArray();

        // Then
        $this->assertIsArray($array);
        $this->assertEquals([
            'access_token' => '12345',
            'refresh_token' => '123456',
            'expires_in' => $time,
            'values' => ['incoming_webhook' => ['channel' => '#testing']],
        ], $array);
    }

    /** @test */
    public function it_can_return_the_name_of_the_channel()
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
}
