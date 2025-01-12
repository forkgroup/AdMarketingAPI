<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Supports\Traits;

use AdMarketingAPI\Kernel\Contracts\Arrayable;
use AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException;
use AdMarketingAPI\Kernel\Exceptions\InvalidConfigException;
use AdMarketingAPI\Kernel\Http\Response;
use AdMarketingAPI\Kernel\Supports\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait ResponseCastable.
 */
trait ResponseCastable
{
    /**
     * @param null|string $type
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     */
    protected function castResponseToType(ResponseInterface $response, $type = null)
    {
        $response = Response::buildFromPsrResponse($response);
        $response->getBody()->rewind();

        switch ($type ?? 'array') {
            case 'collection':
                return $response->toCollection();
            case 'array':
                return $response->toArray();
            case 'object':
                return $response->toObject();
            case 'raw':
                return $response;
            default:
                if (! is_subclass_of($type, Arrayable::class)) {
                    throw new InvalidConfigException(sprintf(
                        'Config key "response_type" classname must be an instanceof %s',
                        Arrayable::class
                    ));
                }

                return new $type($response);
        }
    }

    /**
     * @param mixed $response
     * @param null|string $type
     *
     * @return \AdMarketingAPI\Kernel\Supports\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidConfigException
     */
    protected function detectAndCastResponseToType($response, $type = null)
    {
        switch (true) {
            case $response instanceof ResponseInterface:
                $response = Response::buildFromPsrResponse($response);

                break;
            case $response instanceof Arrayable:
                $response = new Response(200, [], json_encode($response->toArray()));

                break;
            case ($response instanceof Collection) || is_array($response) || is_object($response):
                $response = new Response(200, [], json_encode($response));

                break;
            case is_scalar($response):
                $response = new Response(200, [], (string) $response);

                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported response type "%s"', gettype($response)));
        }

        return $this->castResponseToType($response, $type);
    }
}
