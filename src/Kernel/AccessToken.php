<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel;

use AdMarketingAPI\Kernel\Contracts\AccessTokenInterface;
use AdMarketingAPI\Kernel\Exceptions\HttpException;
use AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException;
use AdMarketingAPI\Kernel\Supports\Traits\HasHttpRequests;
use AdMarketingAPI\Kernel\Supports\Traits\InteractsWithCache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AccessToken.
 */
abstract class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests;
    use InteractsWithCache;

    /**
     * @var \AdMarketingAPI\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var string
     */
    protected $requestMethod = 'GET';

    /**
     * @var string
     */
    protected $endpointToGetToken;

    /**
     * @var string
     */
    protected $queryName;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var int
     */
    protected $safeSeconds = 500;

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'admarketingapi.kernel.';

    /**
     * @var string
     */
    protected $accountId;

    /**
     * AccessToken constructor.
     *
     * @param \AdMarketingAPI\Kernel\ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @throws \AdMarketingAPI\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    public function getToken(bool $refresh = false): array
    {
        if (! $refresh && $token = $this->getCachedToken($this->tokenKey)) {
            return $token;
        }

        /** @var array $token */
        $token = $this->requestToken($this->getCredentials(), true);

        // cache access token and refresh token
        $this->setToken($token);

        $this->app->events->dispatch(new Events\AccessTokenRefreshed($this));

        return $token;
    }

    /**
     * @param string $tokenKey
     *
     * @return array|bool
     */
    public function getCachedToken($tokenKey)
    {
        $cacheKey = $this->getCacheKey($tokenKey);
        $cache = $this->getCache();
        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        return false;
    }

    /**
     * @throws \AdMarketingAPI\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    public function refresh(): AccessTokenInterface
    {
        $this->getToken(true);

        return $this;
    }

    /**
     * @param bool $toArray
     *
     * @return \AdMarketingAPI\Kernel\Support\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\HttpException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        if (empty($result['data'][$this->tokenKey])) {
            throw new HttpException('Request access_token fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }

        return $toArray ? $result['data'] : $formatted['data'];
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

        $query = http_build_query(array_merge($this->getQuery(), $query));

        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }

        return $this->endpointToGetToken;
    }

    /**
     * Prepare the OAuth callback url.
     *
     * @param Container $app
     *
     * @return string
     */
    public function prepareCallbackUrl()
    {
        $callback = $this->app['config']->get('oauth.redirect_uri');
        if (stripos($callback, 'http') === 0) {
            return $callback;
        }
        $baseUrl = $this->app['request']->getSchemeAndHttpHost();

        return $baseUrl . '/' . ltrim($callback, '/');
    }

    /**
     * @return string
     */
    public function getTokenKey()
    {
        return $this->tokenKey;
    }

    /**
     * Send http request.
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(array $credentials): ResponseInterface
    {
        $options = [
            ($this->requestMethod === 'GET') ? 'query' : 'json' => $credentials,
        ];

        return $this->setHttpClient($this->app['http_client'])
            ->request($this->getEndpoint(), $this->requestMethod, $options);
    }

    /**
     * @return string
     */
    protected function getCacheKey(string $tokenKey)
    {
        $account_id = $this->app['config']->get('account_id');
        return $this->cachePrefix . "{$tokenKey}.{$account_id}";
    }

    /**
     * The request query will be used to add to the request.
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    protected function getQuery(): array
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }

    /**
     * Credential for get token.
     */
    abstract protected function getCredentials(): array;

    /**
     * cache access token and refresh token.
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    abstract protected function setToken(array $token): AccessTokenInterface;
}
