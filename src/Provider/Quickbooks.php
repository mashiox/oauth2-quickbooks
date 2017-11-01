<?php

namespace League\OAuth2\Client\Provider;

// use League\OAuth2\Client\Provider\Exception\QuickbooksIdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Quickbooks extends AbstractProvider
{
    use \League\OAuth2\Client\Tool\BearerAuthorizationTrait;

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    // const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = ['com.intuit.quickbooks.accounting', 'openid', 'profile', 'email', 'phone', 'address'];

    /**
     * Default host
     *
     * @var string
     */
    protected $host = 'https://quickbooks.api.intuit.com';

    /**
     * Gets host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the string used to separate scopes.
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://appcenter.intuit.com/connect/oauth2';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://sandbox-accounts.platform.intuit.com/v1/openid_connect/userinfo';
    }

    /**
     * Returns an authenticated PSR-7 request instance.
     *
     * @param  string $method
     * @param  string $url
     * @param  AccessToken|string $token
     * @param  array $options Any of "headers", "body", and "protocolVersion".
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        $parsedUrl = parse_url($url);
        $queryString = array();

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryString);
        }

        if (!isset($queryString['access_token'])) {
            $queryString['access_token'] = (string) $token;
        }

        $url = http_build_url($url, [
            'query' => http_build_query($queryString),
        ]);

        return $this->createRequest($method, $url, null, $options);
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @link   https://Quickbooks.com/developer/endpoints/
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        // Standard error response format
        if (!empty($data['meta']['error_type'])) {
            throw QuickbooksIdentityProviderException::clientException($response, $data);
        }

        // OAuthException error response format
        if (!empty($data['error_type'])) {
            throw QuickbooksIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Sets host.
     *
     * @param string $host
     *
     * @return string
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new QuickbooksResourceOwner($response);
    }
}
