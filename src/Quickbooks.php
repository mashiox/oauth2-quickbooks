<?php

namespace mashiox\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Quickbooks 
{
    
    private $config;
    private $provider;

    const SESSION_KEY = 'oauth2-'.__CLASS__.'-accessToken';

    /**
     * Provide the user with access to Quickbooks
     * - Attempt to fetch the access object from the store (1. session, 2. objectstore)
     * - If the token is valid, save the access object in the session 
     * - If the refresh token is valid, refresh access object, persist, save in session
     * - If neither are valid, 
     *   - $redirect = false; Redirect the user to the oauth2 uri
     *   - $redirect = true ; Return the oauth2 uri
     */
    public static function login(bool $redirect = false)
    {
        $qb = new self();

        if (!$qb->getProvider()->accessIsValid())
        {
            if (!$qb->getProvider()->refreshIsValid())
            {
                // Seed the access. 
                $redirectUri = $qb->seedAccess();
                
                if ($redirect)
                {
                    header(sprintf("Location: %s", $redirectUri));
                }
                return $redirectUri;
            }

            // Refresh access
            $qb->refreshAccess();
        }

        return $qb;
    }

    private function __construct()
    {
        $this->config = $this->getProviderConfig();
        $this->store = new ProviderStore($this->config['store']);

        $accessToken = $_SESSION[self::SESSION_KEY] 
            ?: $this->get(self::SESSION_KEY);

        $this->provider = new QuickbooksProvider($accessToken);

    }

    public function getAccess()
    {
        return $this->provider->getAccessToken();
    }

    public function getProvider()
    {
        return $this->provider;
    }

    private function getProviderConfig() : array
    {
        $configFilePath = sprintf('$1%s/$2%s', getenv('OAUTH2_PROVIDER_CONFIG'), __CLASS__);
        return parse_ini_file($configFilePath, false);
    }
}

class ProviderStore
{
    private $storageEngine;
    private $storeKey;

    public function __construct(string $storageEngine, string $storeKey = null)
    {
        $this->storageEngine = $storageEngine;
        switch ($this->storageEngine)
        {
            /**
             * Key=Value Store Separated by newlines
             */
            case 'file':
                if (empty($storeKey))
                {
                    $storageObject = tmpfile();
                    $this->storeKey = stream_get_meta_data($storageObject)['uri'];
                }
                else {
                    $storageObject = fopen($storeKey, 'c');
                    $this->storeKey = $storeKey;
                }
                fclose($storageObject);
            break;

            default:
                throw new Exception("Not a store provider");
        }
    }

    public function set($key, $value)
    {
        switch ($this->storageEngine)
        {
            case 'file':
                $store = fopen($this->storeKey, 'r');
                $state = [];
                while ($savedState = stream_get_line($store, 1048576, "\n"))
                {
                    $map = explode("=", $savedState);
                    $savedKey = $map[0];
                    $savedValue = $map[1];
                    if ($savedKey === $key)
                    {
                        $state[$key] = $value;
                        continue;
                    }
                    $state[$key] = $savedValue;
                }
                $state = implode("\n", $state);
                fclose($store);
                $store = fopen($this->storeKey, 'c');
                fwrite($store, $state);
                fclose($store);
            break;

            default:
                throw new Exception("Not a store provider");
        }
    }

    public function get($key) : string
    {
        switch ($this->storageEngine)
        {
            case 'file':
                $store = fopen($this->storeKey, 'r');
                while ($savedState = stream_get_line($store, 1048576, "\n"))
                {
                    $map = explode("=", $savedState);
                    $savedKey = $map[0];
                    $savedValue = $map[1];
                    if ($savedKey === $key) 
                    {
                        $returnValue = $savedValue;
                        break;
                    }
                }
                fclose($this->storeKey);
            break;

            default:
                throw new Exception("Not a store provider");
        }
        return $returnValue;
    }
}

class QuickbooksProvider implements AbstractProvider
{
    
    /**
     * AbstractProvider requirements
     */
    public function getBaseAuthorizationUrl()
    {
    }
    
    public function getBaseAccessTokenUrl(array $params)
    {
    }
    
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
    }

    public function getDefaultScopes()
    {
    }

    public function checkResponse(ResponseInterface $response, $data)
    {
    }
}