<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Supports\Traits;

use AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException;
use AdMarketingAPI\Kernel\Supports\Arr;
use AdMarketingAPI\Kernel\Supports\Str;
use BadMethodCallException;

/**
 * Trait Attributes.
 */
trait HasAttributes
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var bool
     */
    protected $snakeable = true;

    /**
     * Magic call.
     *
     * @param string $method
     * @param array $args
     *
     * @return $this
     */
    public function __call($method, $args)
    {
        if (stripos($method, 'with') === 0) {
            return $this->with(substr($method, 4), array_shift($args));
        }

        throw new BadMethodCallException(sprintf('Method "%s" does not exists.', $method));
    }

    /**
     * Magic get.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * Magic set.
     *
     * @param string $property
     * @param mixed $value
     *
     * @return $this
     */
    public function __set($property, $value)
    {
        return $this->with($property, $value);
    }

    /**
     * Whether or not an data exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Set Attributes.
     *
     * @return $this
     */
    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set attribute.
     *
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute($attribute, $value)
    {
        Arr::set($this->attributes, $attribute, $value);

        return $this;
    }

    /**
     * Get attribute.
     *
     * @param string $attribute
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($attribute, $default = null)
    {
        return Arr::get($this->attributes, $attribute, $default);
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function isRequired($attribute)
    {
        return in_array($attribute, $this->getRequired(), true);
    }

    /**
     * @return array|mixed
     */
    public function getRequired()
    {
        return property_exists($this, 'required') ? $this->required : [];
    }

    /**
     * Set attribute.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this
     */
    public function with($attribute, $value)
    {
        $this->snakeable && $attribute = Str::snake($attribute);

        $this->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * Override parent set() method.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this
     */
    public function set($attribute, $value)
    {
        $this->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * Override parent get() method.
     *
     * @param string $attribute
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($attribute, $default = null)
    {
        return $this->getAttribute($attribute, $default);
    }

    /**
     * @return bool
     */
    public function has(string $key)
    {
        return Arr::has($this->attributes, $key);
    }

    /**
     * @return $this
     */
    public function merge(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * @param array|string $keys
     *
     * @return array
     */
    public function only($keys)
    {
        return Arr::only($this->attributes, $keys);
    }

    /**
     * Return all items.
     *
     * @return array
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     */
    public function all()
    {
        $this->checkRequiredAttributes();

        return $this->attributes;
    }

    /**
     * Check required attributes.
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     */
    protected function checkRequiredAttributes()
    {
        foreach ($this->getRequired() as $attribute) {
            if (is_null($this->get($attribute))) {
                throw new InvalidArgumentException(sprintf('"%s" cannot be empty.', $attribute));
            }
        }
    }
}
