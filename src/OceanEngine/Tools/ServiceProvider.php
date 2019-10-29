<?php

namespace AdMarketingAPI\OceanEngine\Tools;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 *
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['tools'] = function ($app) {
            return new Tools($app);
        };
    }
}