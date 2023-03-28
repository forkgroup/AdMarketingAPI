<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestUtil
{
    public static function get(ServerRequestInterface $request, string $key)
    {
        if ($result = $request->getAttribute('key')) {
            return $result;
        }

        if ($result = $request->getQueryParams()[$key] ?? null) {
            return $result;
        }

        return $request->getParsedBody()[$key] ?? null;
    }
}
