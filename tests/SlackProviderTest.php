<?php

namespace TestMonitor\Slack\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\Slack\Provider\SlackProvider;

class SlackProviderTest extends TestCase
{
    /** @test */
    public function it_should_instantiate_the_slackprovider()
    {
        // When
        $provider = new SlackProvider([
            'clientId' => 'xx',
            'clientSecret' => 'xx',
            'redirectUri' => 'xx',
        ]);

        // Then
        $this->assertInstanceOf(SlackProvider::class, $provider);
    }

    /** @test */
    public function it_could_instantiate_the_slackprovider_when_required_options_are_missing()
    {
        $this->expectException(\InvalidArgumentException::class);

        $provider = new SlackProvider();
    }

    /** @test */
    public function it_should_retrieve_the_autorized_user_test_url()
    {
        // When
        $provider = new SlackProvider([
            'clientId' => 'xx',
            'clientSecret' => 'xx',
            'redirectUri' => 'xx',
        ]);

        // When
        $url = $provider->getAuthorizedUserTestUrl();

        // Then
        $this->assertEquals('https://slack.com/api/auth.test', $url);
    }

    /** @test */
    public function it_should_retrieve_the_base_authorization_url()
    {
        // When
        $provider = new SlackProvider([
            'clientId' => 'xx',
            'clientSecret' => 'xx',
            'redirectUri' => 'xx',
        ]);

        // When
        $url = $provider->getBaseAuthorizationUrl();

        // Then
        $this->assertEquals('https://slack.com/oauth/v2/authorize', $url);
    }

    /** @test */
    public function it_should_retrieve_the_base_access_token_url()
    {
        // When
        $provider = new SlackProvider([
            'clientId' => 'xx',
            'clientSecret' => 'xx',
            'redirectUri' => 'xx',
        ]);

        // When
        $url = $provider->getBaseAccessTokenUrl([]);

        // Then
        $this->assertEquals('https://slack.com/api/oauth.v2.access', $url);
    }
}
