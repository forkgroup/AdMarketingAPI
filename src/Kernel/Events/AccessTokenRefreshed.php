<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Events;

use AdMarketingAPI\Kernel\AccessToken;

/**
 * Class AccessTokenRefreshed.
 */
class AccessTokenRefreshed
{
    /**
     * @var \AdMarketingAPI\Kernel\AccessToken
     */
    public $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
