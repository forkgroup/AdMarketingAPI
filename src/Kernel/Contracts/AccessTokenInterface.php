<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * Interface AuthorizerAccessToken.
 */
interface AccessTokenInterface
{
    public function getToken(): array;

    /**
     * @return \AdMarketingAPI\Kernel\Contracts\AccessTokenInterface
     */
    public function refresh(): self;

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface;
}
