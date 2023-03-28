<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Gdt;

use AdMarketingAPI\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @property \EasyAdm\Gdt\Auth\AccessToken $access_token
 */
class Application extends ServiceContainer
{
    /**
     * 普通模式.
     */
    public const MODE_NORMAL = 'normal';

    /**
     * 沙箱模式.
     */
    public const MODE_DEV = 'dev';

    /**
     * Const url.
     */
    public const URL = [
        self::MODE_NORMAL => 'https://api.e.qq.com/v1.1/',
        self::MODE_DEV => 'https://sandbox-api.e.qq.com/v1.1/',
    ];

    /**
     * @var array
     */
    protected $providers = [
        OAuth\ServiceProvider::class,
        Dmp\ServiceProvider::class,
    ];

    public function __construct(array $config = [], array $prepends = [])
    {
        if (isset($config['mode']) && $config['mode'] == self::MODE_DEV) {
            $config['http']['base_uri'] = self::URL[self::MODE_DEV];
        } else {
            $config['http']['base_uri'] = self::URL[self::MODE_NORMAL];
        }

        parent::__construct($config, $prepends);
    }
}
