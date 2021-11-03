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
}
