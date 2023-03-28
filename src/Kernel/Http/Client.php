<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Http;

use GuzzleHttp;

class Client implements ClientInterface
{
    public function __construct(protected array $options)
    {
    }

    public function request($method, $uri, array $options = [])
    {
        return $this->client()->request($method, $uri, $options);
    }

    protected function client(): GuzzleHttp\Client
    {
        return new GuzzleHttp\Client($this->options);
    }
}
