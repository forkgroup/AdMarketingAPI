<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Events;

use AdMarketingAPI\Kernel\ServiceContainer;

/**
 * Class ApplicationInitialized.
 */
class ApplicationInitialized
{
    /**
     * @var \AdMarketingAPI\Kernel\ServiceContainer
     */
    public $app;

    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }
}
