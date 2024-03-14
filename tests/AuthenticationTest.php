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
use TestMonitor\Slack\Exceptions\MissingRefreshTokenException;

class AuthenticationTest extends TestCase
{
    /**
     * @var \SlackPhp\BlockKit\Surfaces\Message|\SlackPhp\BlockKit\Surfaces\Surface
     */
    protected $message;

    protected function setUp(): void
    {
        $this->message = Kit::newMessage()->text('testing');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_create_a_token()
    {
        // When
        $token = new AccessToken('12345', '67890', time() + 3600);

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertIsArray($token->toArray());
        $this->assertFalse($token->expired());
    }

    /** @test */
    public function it_can_create_a_token_without_a_refresh_token()
    {
        // When
        $token = new AccessToken('12345');

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertIsArray($token->toArray());
        $this->assertFalse($token->canExpire());
    }

    /** @test */
    public function it_can_detect_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60);

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        // When
        $expired = $slack->tokenExpired();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertTrue($token->expired());
        $this->assertTrue($expired);
    }

    /** @test */
    public function it_can_not_provide_a_client_with_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60, ['incoming_webhook' => ['channel' => '#testing']]);

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $token);

        $slack->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $this->expectException(TokenExpiredException::class);

        // When
        $slack->postMessage('https://slack.incoming.url/', $this->message);
    }

    /** @test */
    public function it_can_provide_an_authorization_url()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], new AccessToken(), $provider = Mockery::mock('\TestMonitor\Slack\Provider\SlackProvider'));

        $options = ['state' => 'somestate', 'scope' => 'incoming-webhook'];

        $provider->shouldReceive('getAuthorizationUrl')->with($options)->andReturn('https://slack.authorization.url');

        // When
        $url = $slack->authorizationUrl($options['scope'], $options['state']);

        // Then
        $this->assertEquals('https://slack.authorization.url', $url);
    }

    /** @test */
    public function it_can_fetch_a_token()
    {
        // Given
        $provider = Mockery::mock('\TestMonitor\Slack\Provider\SlackProvider');

        $token = Mockery::mock('\League\OAuth2\Client\Token\AccessToken');

        $token->shouldReceive('getToken')->once()->andReturn('12345');
        $token->shouldReceive('getRefreshToken')->once()->andReturn('123456');
        $token->shouldReceive('getExpires')->once()->andReturn(time() + 3600);
        $token->shouldReceive('getValues')->once()->andReturn([]);

        $provider->shouldReceive('getAccessToken')->with('authorization_code', ['code' => '123'])->once()->andReturn($token);

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], new AccessToken(), $provider);

        // When
        $token = $slack->fetchToken('123');

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals('12345', $token->accessToken);
        $this->assertEquals('123456', $token->refreshToken);
    }

    /** @test */
    public function it_can_refresh_a_token()
    {
        // Given
        $oldToken = new AccessToken('12345', '567890', time() - 3600);

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $oldToken, $provider = Mockery::mock('\TestMonitor\Slack\Provider\SlackProvider'));

        $token = Mockery::mock('\League\OAuth2\Client\Token\AccessToken');

        $token->shouldReceive('getToken')->once()->andReturn('12345');
        $token->shouldReceive('getRefreshToken')->once()->andReturn('123456');
        $token->shouldReceive('getExpires')->once()->andReturn(time() + 3600);
        $token->shouldReceive('getValues')->once()->andReturn([]);

        $provider->shouldReceive('getAccessToken')->with('refresh_token', ['refresh_token' => '567890'])->once()->andReturn($token);

        // When
        $token = $slack->refreshToken();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals('12345', $token->accessToken);
        $this->assertEquals('123456', $token->refreshToken);
    }

    /** @test */
    public function it_can_not_refresh_a_token_without_a_refresh_token()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none']);

        $this->expectException(UnauthorizedException::class);

        // When
        $slack->refreshToken();
    }

    /** @test */
    public function it_can_not_refresh_a_token_when_the_token_can_not_expire()
    {
        // Given
        $oldToken = new AccessToken('12345', '567890');

        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $oldToken, $provider = Mockery::mock('\TestMonitor\Slack\Provider\SlackProvider'));

        $this->expectException(MissingRefreshTokenException::class);

        // When
        $slack->refreshToken();
    }

    /** @test */
    public function it_can_not_provide_a_client_without_a_token()
    {
        // Given
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none']);

        $this->expectException(UnauthorizedException::class);

        // When
        $slack->postMessage('https://slack.incoming.url/', $this->message);
    }

    /** @test */
    public function it_can_retrieve_the_details_of_the_current_authenticated_user()
    {
        // Given
        $provider = Mockery::mock('\TestMonitor\Slack\Provider\SlackProvider');

        $provider->shouldReceive('getAuthorizedUser')->once()->andReturn(new SlackAuthorizedUser([
            'user_id' => 1,
        ]));

        $token = new AccessToken('12345', '67890', time() + 60);
        $slack = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $token, $provider);

        // When
        $result = $slack->authorizedUser();

        // Then
        $this->assertInstanceOf(SlackAuthorizedUser::class, $result);
        $this->assertIsArray($result->toArray());
        $this->assertEquals(1, $result->getId());
    }
}
