<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Http;

interface ClientInterface
{
    public function request($method, $uri, array $options = []);
}
