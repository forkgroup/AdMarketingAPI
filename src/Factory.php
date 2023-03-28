<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI;

/**
 * Class Factory.
 *
 * @method static \AdMarketingAPI\OceanEngine\Application oceanengine(array $config)
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
     * @return \EasyWeChat\Kernel\ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = Kernel\Supports\Str::studly($name);
        $application = "\\AdMarketingAPI\\{$namespace}\\Application";

        return new $application($config);
    }
}
