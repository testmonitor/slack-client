<?php

namespace TestMonitor\Slack\Tests;

use Mockery;
use SlackPhp\BlockKit\Kit;
use TestMonitor\Slack\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\AccessToken;
use TestMonitor\Slack\Exceptions\UnauthorizedException;

class MessagesTest extends TestCase
{
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = new AccessToken('12345', '123456', time() + 3600, ['incoming_webhook' => ['channel' => '#testing']]);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_post_a_message()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn('ok');

        $message = Kit::newMessage()->text('Hello');

        // When
        $result = $slack->postMessage($message);

        // Then
        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_post_a_message()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(403);
        $response->shouldReceive('getBody')->andReturn('');

        $this->expectException(UnauthorizedException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage($message);
    }
}
