<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Supports;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class ArrayAccessible.
 */
class ArrayAccessible implements ArrayAccess, IteratorAggregate
{
    private $array;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    public function toArray()
    {
        return $this->array;
    }
}
