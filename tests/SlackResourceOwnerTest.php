<?php

namespace TestMonitor\Slack\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\Provider\SlackResourceOwner;

class SlackResourceOwnerTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_the_slack_resource_owner()
    {
        // When
        $resourceOwner = new SlackResourceOwner([]);

        // Then
        $this->assertInstanceOf(SlackResourceOwner::class, $resourceOwner);
    }

    /** @test */
    public function it_should_return_the_full_context_of_the_resource_owner()
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
    public function it_should_return_the_id_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['id' => 123]]);

        // When
        $response = $resourceOwner->getId();

        // Then
        $this->assertEquals(123, $response);
    }

    /** @test */
    public function it_should_return_null_when_the_id_from_the_resource_owner_is_missing()
    {
        // Given
        $resourceOwner = new SlackResourceOwner([]);

        // When
        $response = $resourceOwner->getId();

        // Then
        $this->assertNull($response);
    }

    /** @test */
    public function it_should_return_the_name_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['name' => 'John Doe']]);

        // When
        $response = $resourceOwner->getName();

        // Then
        $this->assertEquals('John Doe', $response);
    }

    /** @test */
    public function it_should_return_null_when_the_name_from_the_resource_owner_is_missing()
    {
        // Given
        $resourceOwner = new SlackResourceOwner([]);

        // When
        $response = $resourceOwner->getName();

        // Then
        $this->assertNull($response);
    }

    /** @test */
    public function it_should_return_the_color_from_the_resource_owner()
    {
        // Given
        $resourceOwner = new SlackResourceOwner(['user' => ['color' => '#AABBCC']]);

        // When
        $response = $resourceOwner->getColor();

        // Then
        $this->assertEquals('#AABBCC', $response);
    }

    /** @test */
    public function it_should_return_null_when_the_color_from_the_resource_owner_is_missing()
    {
        // Given
        $resourceOwner = new SlackResourceOwner([]);

        // When
        $response = $resourceOwner->getName();

        // Then
        $this->assertNull($response);
    }
}
