<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\OceanEngine\OAuth;

use AdMarketingAPI\Kernel\AccessToken;
use AdMarketingAPI\Kernel\Contracts\AccessTokenInterface;
use AdMarketingAPI\Kernel\Exceptions\RuntimeException;
use AdMarketingAPI\Kernel\Http\RequestUtil;
use Psr\Http\Message\RequestInterface;

class OAuth extends AccessToken
{
    /**
     * @var string
     */
    protected $requestMethod = 'POST';

    /**
     * @var string
     */
    protected $cachePrefix = 'admarketingapi.oceanengine.';

    /**
     * @var string
     */
    protected $endpointToGetToken = 'open_api/oauth2/access_token/';

    /**
     * OceanEngine OAuth2.0 URL.
     *
     * @param string $state
     *
     * @return string
     */
    public function getAuthUrl($state = '')
    {
        $url = $this->app->config->get('http.base_uri') . 'openapi/audit/oauth.html';

        return $this->buildAuthUrlFromBase($url, $state);
    }

    /**
     * @throws \AdMarketingAPI\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        parse_str($request->getUri()->getQuery(), $query);
        $token = $this->getToken()[$this->tokenKey];
        $query = http_build_query($query);
        return $request->withHeader('Access-Token', $token)
            ->withHeader('Content-Type', 'application/json')
            ->withUri($request->getUri()->withQuery($query));
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', PHP_QUERY_RFC1738);

        return $url . '?' . $query;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'app_id' => $this->app->config->get('app_id'),
            'state' => $state ?: md5((string) time()),
            'scope' => $this->app->config->get('oauth.scopes'),
            'redirect_uri' => $this->prepareCallbackUrl(),
        ];
    }

    protected function getCredentials(): array
    {
        $credentials = [
            'grant_type' => 'auth_code',
            'appid' => $this->app['config']['app_id'],
            'secret' => $this->app['config']['secret'],
        ];
        $auth_code = RequestUtil::get($this->app->request, 'auth_code');
        if (empty($auth_code)) {
            // get refresh_token form cache
            $refreshToken = $this->getCachedToken('refresh_token');
            // get access token by refresh_token
            if ($refreshToken) {
                $this->endpointToGetToken = 'open_api/oauth2/refresh_token/';
                $credentials['grant_type'] = 'refresh_token';
                $credentials['refresh_token'] = $refreshToken['refresh_token'];
            }
        } else {
            // get access token by auth_code
            $credentials['auth_code'] = $auth_code;
        }

        return $credentials;
    }

    protected function setToken(array $token): AccessTokenInterface
    {
        if (! empty($token['advertiser_id'])) {
            $this->app['config']->set('account_id', $token['advertiser_id']);
        }
        if (! empty($token['advertiser_ids'])) {
            $this->app['config']->set('account_id', $token['advertiser_ids'][0]);
        }
        $cache = $this->getCache();
        // access token
        $accessTokenKey = $this->getCacheKey('access_token');

        $cache->set($accessTokenKey, [
            $this->tokenKey => $token['access_token'],
            'expires_in' => $token['expires_in'],
        ], $token['expires_in'] - $this->safeSeconds);

        if (! $cache->has($accessTokenKey)) {
            throw new RuntimeException('Failed to cache access token.');
        }

        // refresh token
        $refreshTokenKey = $this->getCacheKey('refresh_token');
        $cache->set($refreshTokenKey, [
            'refresh_token' => $token['refresh_token'],
            'expires_in' => $token['refresh_token_expires_in'],
        ], $token['refresh_token_expires_in'] - $this->safeSeconds);

        if (! $cache->has($refreshTokenKey)) {
            throw new RuntimeException('Failed to cache refresh token.');
        }

        return $this;
    }
}
