<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI;

use AdMarketingAPI\OceanEngine\Application;

/**
 * Class Factory.
 *
 * @method static \AdMarketingAPI\OceanEngine\Application oceanEngine(array $config, array $providers = [])
 */
class Factory
{
    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }

    /**
     * @param string $name
     *
     * @return \AdMarketingAPI\Kernel\ServiceContainer
     */
    public static function make($name, array $config, array $providers = [])
    {
        $namespace = Kernel\Supports\Str::studly($name);
        $application = "\\AdMarketingAPI\\{$namespace}\\Application";

        return new $application($config, providers: $providers);
    }

    public static function makeOceanEngine(array $config, array $providers = []): Application
    {
        return new Application($config, providers: $providers);
    }
}
