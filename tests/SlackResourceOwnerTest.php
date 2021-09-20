<?php

namespace TestMonitor\Slack\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\Provider\SlackResourceOwner;

class SlackResourceOwnerTest extends TestCase
{
    protected $profile = [
        'first_name' => 'Joanne',
        'last_name' => 'Doe',
        'real_name' => 'The Real Joanne Doe',
        'email' => 'jdoe@testmonitor.com',
        'skype' => 'jdoe@testmonitor.com',
        'phone' => '06123456789',
        'image_24' => 'avatar24',
        'image_32' => 'avatar32',
        'image_48' => 'avatar48',
        'image_72' => 'avatar72',
        'image_192' => 'avatar192',
    ];

    /** @test */
    public function it_can_instantiate_the_slack_resource_owner()
    {
        // When
        $resourceOwner = new SlackResourceOwner([]);

        // Then
        $this->assertInstanceOf(SlackResourceOwner::class, $resourceOwner);
    }

    /** @test */
    public function it_can_return_the_full_context_of_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['foo' => 'bar']);

        // When
        $array = $resourceOwner->toArray();

        // Then
        $this->assertIsArray($array);
        $this->assertEquals(['foo' => 'bar'], $array);
    }

    /** @test */
    public function it_can_return_the_id_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['id' => 123]]);

        // When
        $response = $resourceOwner->getId();

        // Then
        $this->assertEquals(123, $response);
    }

    /** @test */
    public function it_can_return_the_name_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['name' => 'John Doe']]);

        // When
        $response = $resourceOwner->getName();

        // Then
        $this->assertEquals('John Doe', $response);
    }

    /** @test */
    public function it_can_return_the_color_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['color' => '#AABBCC']]);

        // When
        $response = $resourceOwner->getColor();

        // Then
        $this->assertEquals('#AABBCC', $response);
    }

    /** @test */
    public function it_can_return_the_profile_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getProfile();

        // Then
        $this->assertIsArray($response);
        $this->assertEquals($this->profile, $response);
    }

    /** @test */
    public function it_can_return_the_first_name_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getFirstName();

        // Then
        $this->assertEquals('Joanne', $response);
    }

    /** @test */
    public function it_can_return_the_last_name_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getLastName();

        // Then
        $this->assertEquals('Doe', $response);
    }

    /** @test */
    public function it_can_return_the_skype_username_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getSkype();

        // Then
        $this->assertEquals('jdoe@testmonitor.com', $response);
    }

    /** @test */
    public function it_can_return_the_email_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getEmail();

        // Then
        $this->assertEquals('jdoe@testmonitor.com', $response);
    }

    /** @test */
    public function it_can_return_the_phonenumber_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getPhone();

        // Then
        $this->assertEquals('06123456789', $response);
    }

    /** @test */
    public function it_can_return_the_avatar_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getImage24();

        // Then
        $this->assertEquals('avatar24', $response);
    }

    /** @test */
    public function it_can_return_the_large_avatar_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['profile' => $this->profile]]);

        // When
        $response = $resourceOwner->getImage192();

        // Then
        $this->assertEquals('avatar192', $response);
    }

    /** @test */
    public function it_can_return_the_2fa_status_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['has_2fa' => true]]);

        // When
        $response = $resourceOwner->hasTwoFactorAuthentication();

        // Then
        $this->assertEquals(true, $response);
    }

    /** @test */
    public function it_can_return_the_admin_status_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['is_admin' => true]]);

        // When
        $response = $resourceOwner->isAdmin();

        // Then
        $this->assertEquals(true, $response);
    }

    /** @test */
    public function it_can_return_the_owner_status_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['is_owner' => true]]);

        // When
        $response = $resourceOwner->isOwner();

        // Then
        $this->assertEquals(true, $response);
    }
}
