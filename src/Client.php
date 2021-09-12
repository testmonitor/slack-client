<?php

namespace TestMonitor\Slack;

use Psr\Http\Message\ResponseInterface;
use TestMonitor\Slack\Exceptions\Exception;
use TestMonitor\Slack\Provider\SlackProvider;
use TestMonitor\Slack\Exceptions\NotFoundException;
use TestMonitor\Slack\Exceptions\ValidationException;
use TestMonitor\Slack\Exceptions\FailedActionException;
use TestMonitor\Slack\Exceptions\TokenExpiredException;
use TestMonitor\Slack\Exceptions\UnauthorizedException;

class Client
{
    use Actions\PostMessages;

    /**
     * @var \TestMonitor\Slack\AccessToken|null
     */
    protected ?AccessToken $token;

    /**
     * @var array
     */
    public $credentials = [];

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \TestMonitor\Slack\Provider\SlackProvider
     */
    protected $provider;

    /**
     * Create a new client instance.
     *
     * @param array $credentials
     * @param \TestMonitor\Slack\AccessToken|null $token
     * @param \TestMonitor\Slack\Provider\SlackProvider|null $provider
     */
    public function __construct(
        array $credentials,
        AccessToken $token = null,
        SlackProvider $provider = null
    ) {
        $this->token = $token;

        $this->provider = $provider ?? new SlackProvider([
            'clientId' => $credentials['clientId'],
            'clientSecret' => $credentials['clientSecret'],
            'redirectUri' => $credentials['redirectUri'],
        ]);
    }

    /**
     * Create a new authorization URL for the given scope and state.
     *
     * @param string $state
     *
     * @return string
     */
    public function authorizationUrl(string $state = '')
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => 'incoming-webhook',
            'state' => $state,
        ]);
    }

    /**
     * Fetch the access and refresh token based on the authorization code.
     *
     * @param string $code
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return \TestMonitor\Slack\AccessToken
     */
    public function fetchToken(string $code)
    {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $this->token = AccessToken::fromSlack($token);

        return $this->token;
    }

    /**
     * Refresh the current access token.
     *
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return \TestMonitor\Slack\AccessToken
     */
    public function refreshToken(): AccessToken
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        $token = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->token->refreshToken,
        ]);

        $this->token = AccessToken::fromSlack($token, $this->token->values);

        return $this->token;
    }

    /**
     * Determines if the current access token has expired.
     *
     * @return bool
     */
    public function tokenExpired()
    {
        return $this->token->expired();
    }

    /**
     * Gets the details of the currently authenticated user.
     *
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function authorizedUser()
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        return $this->provider->getAuthorizedUser($this->token->toNativeToken());
    }

    /**
     * Returns an Guzzle client instance.
     *
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Slack\Exceptions\TokenExpiredException
     *
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        if ($this->token->expired()) {
            throw new TokenExpiredException();
        }

        return $this->client ?? new \GuzzleHttp\Client([
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Make a POST request to Slack servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Slack\Exceptions\FailedActionException
     * @throws \TestMonitor\Slack\Exceptions\NotFoundException
     * @throws \TestMonitor\Slack\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Slack\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function post(string $uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    /**
     * Make request to Slack servers and return the response.
     *
     * @param string $verb
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Slack\Exceptions\FailedActionException
     * @throws \TestMonitor\Slack\Exceptions\NotFoundException
     * @throws \TestMonitor\Slack\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Slack\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Slack\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function request($verb, $uri, array $payload = [])
    {
        $response = $this->client()->request(
            $verb,
            $uri,
            $payload
        );

        if (! in_array($response->getStatusCode(), [200, 201, 203, 204, 206])) {
            return $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody();

        return json_decode($responseBody, true) ?: $responseBody;
    }

    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \TestMonitor\Slack\Exceptions\ValidationException
     * @throws \TestMonitor\Slack\Exceptions\NotFoundException
     * @throws \TestMonitor\Slack\Exceptions\FailedActionException
     * @throws \Exception
     *
     * @return void
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException();
        }

        if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
            throw new UnauthorizedException();
        }

        if ($response->getStatusCode() == 400) {
            throw new FailedActionException((string) $response->getBody());
        }

        throw new Exception((string) $response->getStatusCode());
    }
}
