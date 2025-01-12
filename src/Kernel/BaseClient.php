<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel;

use AdMarketingAPI\Kernel\Contracts\AccessTokenInterface;
use AdMarketingAPI\Kernel\Exceptions\HttpException;
use AdMarketingAPI\Kernel\Http\Response;
use AdMarketingAPI\Kernel\Supports\Traits\HasHttpRequests;
use Closure;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

/**
 * Class BaseClient.
 */
class BaseClient
{
    use HasHttpRequests { request as performRequest; }

    /**
     * @var \AdMarketingAPI\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var \AdMarketingAPI\Kernel\Contracts\AccessTokenInterface
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * BaseClient constructor.
     *
     * @param \AdMarketingAPI\Kernel\ServiceContainer $app
     */
    public function __construct(ServiceContainer $app, AccessTokenInterface $accessToken = null)
    {
        $this->app = $app;
        $this->accessToken = $accessToken ?? $this->app['oauth'];
    }

    /**
     * GET request.
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet(string $url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * POST request.
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost(string $url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * JSON request.
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPostJson(string $url, array $data = [], array $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * Upload file.
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpUpload(string $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request($url, 'POST', ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30]);
    }

    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }

    /**
     * @return $this
     */
    public function setAccessToken(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $url, string $method = 'GET', array $options = [])
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($url, $method, $options);

        $this->app->events?->dispatch(new Events\HttpResponseCreated($response));

        return $this->proccessApiResult($url, $response);
    }

    /**
     * @return \AdMarketingAPI\Kernel\Http\Response
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestRaw(string $url, string $method = 'GET', array $options = [])
    {
        return Response::buildFromPsrResponse($this->request($url, $method, $options, true));
    }

    /**
     * processingApiResult.
     *
     * @param string $endpoint
     *
     * @return array
     * @throws RuntimeException
     */
    protected function proccessApiResult(string $url, ResponseInterface $response)
    {
        $result = $this->castResponseToType($response);
        if (! isset($result['code']) || $result['code'] != 0) {
            throw new HttpException(
                "Request [{$url}] fail:" . json_encode($result, JSON_UNESCAPED_UNICODE),
                $response,
                $result
            );
        }

        return $result['data'];
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // access token
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
        // log
        // $this->pushMiddleware($this->logMiddleware(), 'log');
    }

    /**
     * Attache access token to request query.
     *
     * @return Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $this->accessToken->applyToRequest($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * Log the request.
     *
     * @return Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter, LogLevel::DEBUG);
    }

    /**
     * Return retry middleware.
     *
     * @return Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries < $this->app->config->get('http.max_retries', 1) && $response && $body = $response->getBody()) {
                // Retry on server errors
                $response = json_decode((string) $body, true);

                if (! empty($response['errcode']) && in_array(abs($response['errcode']), [40001, 40014, 42001], true)) {
                    $this->accessToken->refresh();
                    $this->app['logger']->debug('Retrying with refreshed access token.');

                    return true;
                }
            }

            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }
}
