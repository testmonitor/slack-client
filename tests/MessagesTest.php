<?php

namespace TestMonitor\Slack\Tests;

use Mockery;
use SlackPhp\BlockKit\Kit;
use TestMonitor\Slack\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\AccessToken;
use TestMonitor\Slack\Exceptions\FailedActionException;
use TestMonitor\Slack\Exceptions\NotFoundException;
use TestMonitor\Slack\Exceptions\UnauthorizedException;
use TestMonitor\Slack\Exceptions\MissingWebhookException;

class MessagesTest extends TestCase
{
    /**
     * @var \TestMonitor\Slack\AccessToken
     */
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
    public function it_can_post_a_message()
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

    /** @test */
    public function it_should_throw_a_not_found_exception_when_client_cannot_reach_slack_to_post_a_message()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(404);

        $this->expectException(NotFoundException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage($message);
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_sends_a_bad_request()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(400);
        $response->shouldReceive('getBody')->andReturn('');

        $this->expectException(FailedActionException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage($message);
    }

    /** @test */
    public function it_should_throw_a_missing_webhook_exception_when_client_didnt_receive_a_webhook_to_post_a_message()
    {
        // Given
        $token = new AccessToken('12345', '123456', time() + 3600);

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $token);

        $this->expectException(MissingWebhookException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage($message);
    }

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
