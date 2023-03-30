<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Supports;

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;

class Config extends Collection
{
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function set($key, $value)
    {
        return Arr::set($this->items, $key, $value);
    }
}
