<?php

namespace TestMonitor\Slack\Tests;

use Mockery;
use Exception;
use SlackPhp\BlockKit\Kit;
use TestMonitor\Slack\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\AccessToken;
use TestMonitor\Slack\Exceptions\NotFoundException;
use TestMonitor\Slack\Exceptions\ValidationException;
use TestMonitor\Slack\Exceptions\FailedActionException;
use TestMonitor\Slack\Exceptions\UnauthorizedException;

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
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor('ok'));

        $message = Kit::newMessage()->text('Hello');

        // When
        $result = $slack->postMessage('https://slack.incoming.url/', $message);

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
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor(''));

        $this->expectException(UnauthorizedException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage('https://slack.incoming.url/', $message);
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
        $slack->postMessage('https://slack.incoming.url/', $message);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_sends_a_incomplete_request()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(422);
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor(json_encode(['foo' => 'bar'])));

        $this->expectException(ValidationException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage('https://slack.incoming.url/', $message);
    }

    /** @test */
    public function it_should_return_a_list_of_validation_exception_when_its_throwd()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(422);
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor(json_encode(['foo' => 'bar'])));

        $message = Kit::newMessage()->text('Hello');

        // When
        try {
            $slack->postMessage('https://slack.incoming.url/', $message);
        } catch(ValidationException $e) {
            $this->assertIsArray($e->errors());
            $this->arrayHasKey('foo', $e->errors());
            $this->assertEquals(['foo' => 'bar'], $e->errors());
        }
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_sends_a_bad_request()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(400);
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor(''));

        $this->expectException(FailedActionException::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage('https://slack.incoming.url/', $message);
    }

    /** @test */
    public function it_should_throw_a_fallback_exception_when_slack_becomes_a_teapot()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $this->token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(418);
        $response->shouldReceive('getBody')->andReturn(\GuzzleHttp\Psr7\Utils::streamFor(''));

        $this->expectException(Exception::class);

        $message = Kit::newMessage()->text('Hello');

        // When
        $slack->postMessage('https://slack.incoming.url/', $message);
    }
}
