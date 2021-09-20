<?php

namespace TestMonitor\Slack\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\Provider\SlackAuthorizedUser;

class SlackAuthorizedUserTest extends TestCase
{
    protected $userData = [
        'url' => 'https://my.url',
        'team' => 'Team Yellow',
        'user' => 'Pete Heijn',
        'team_id' => 1234,
        'user_id' => 12345,
    ];

    /** @test */
    public function it_can_instantiate_the_slack_authorized_user()
    {
        // When
        $user = new SlackAuthorizedUser([]);

        // Then
        $this->assertInstanceOf(SlackAuthorizedUser::class, $user);
    }

    /** @test */
    public function it_can_return_the_full_context_of_the_authorized_user()
    {
        // Given
        $user = new SlackAuthorizedUser(['foo' => 'bar']);

        // When
        $array = $user->toArray();

        // Then
        $this->assertIsArray($array);
        $this->assertEquals(['foo' => 'bar'], $array);
    }

    /** @test */
    public function it_can_return_the_id_from_the_authorized_user()
    {
        // Given
        $resourceOwner = new SlackAuthorizedUser($this->userData);

        // When
        $response = $resourceOwner->getId();

        // Then
        $this->assertEquals(12345, $response);
    }

    /** @test */
    public function it_can_return_the_team_id_from_the_authorized_user()
    {
        // Given
        $resourceOwner = new SlackAuthorizedUser($this->userData);

        // When
        $response = $resourceOwner->getTeamId();

        // Then
        $this->assertEquals(1234, $response);
    }

    /** @test */
    public function it_can_return_the_url_from_the_authorized_user()
    {
        // Given
        $resourceOwner = new SlackAuthorizedUser($this->userData);

        // When
        $response = $resourceOwner->getUrl();

        // Then
        $this->assertEquals('https://my.url', $response);
    }

    /** @test */
    public function it_can_return_the_user_from_the_authorized_user()
    {
        // Given
        $resourceOwner = new SlackAuthorizedUser($this->userData);

        // When
        $response = $resourceOwner->getUser();

        // Then
        $this->assertEquals('Pete Heijn', $response);
    }

    /** @test */
    public function it_can_return_the_team_from_the_authorized_user()
    {
        // Given
        $resourceOwner = new SlackAuthorizedUser($this->userData);

        // When
        $response = $resourceOwner->getTeam();

        // Then
        $this->assertEquals('Team Yellow', $response);
    }
}
