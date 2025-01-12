<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Supports\Traits;

use AdMarketingAPI\Kernel\Clauses\Clause;
use AdMarketingAPI\Kernel\Contracts\EventHandlerInterface;
use AdMarketingAPI\Kernel\Decorators\FinallyResult;
use AdMarketingAPI\Kernel\Decorators\TerminateResult;
use AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException;
use AdMarketingAPI\Kernel\ServiceContainer;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Trait Observable.
 */
trait Observable
{
    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $clauses = [];

    /**
     * @param callable|Closure|EventHandlerInterface|string $handler
     * @param callable|Closure|EventHandlerInterface|string $condition
     *
     * @return \AdMarketingAPI\Kernel\Clauses\Clause
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    public function push($handler, $condition = '*')
    {
        [$handler, $condition] = $this->resolveHandlerAndCondition($handler, $condition);

        if (! isset($this->handlers[$condition])) {
            $this->handlers[$condition] = [];
        }

        array_push($this->handlers[$condition], $handler);

        return $this->newClause($handler);
    }

    /**
     * @param Closure|EventHandlerInterface|string $handler
     * @param Closure|EventHandlerInterface|string $condition
     *
     * @return \AdMarketingAPI\Kernel\Clauses\Clause
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    public function unshift($handler, $condition = '*')
    {
        [$handler, $condition] = $this->resolveHandlerAndCondition($handler, $condition);

        if (! isset($this->handlers[$condition])) {
            $this->handlers[$condition] = [];
        }

        array_unshift($this->handlers[$condition], $handler);

        return $this->newClause($handler);
    }

    /**
     * @param string $condition
     * @param Closure|EventHandlerInterface|string $handler
     *
     * @return \AdMarketingAPI\Kernel\Clauses\Clause
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    public function observe($condition, $handler)
    {
        return $this->push($handler, $condition);
    }

    /**
     * @param string $condition
     * @param Closure|EventHandlerInterface|string $handler
     *
     * @return \AdMarketingAPI\Kernel\Clauses\Clause
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    public function on($condition, $handler)
    {
        return $this->push($handler, $condition);
    }

    /**
     * @param int|string $event
     * @param mixed ...$payload
     *
     * @return null|mixed
     */
    public function dispatch($event, $payload)
    {
        return $this->notify($event, $payload);
    }

    /**
     * @param int|string $event
     * @param mixed ...$payload
     *
     * @return null|mixed
     */
    public function notify($event, $payload)
    {
        $result = null;

        foreach ($this->handlers as $condition => $handlers) {
            if ($condition === '*' || ($condition & $event) === $event) {
                foreach ($handlers as $handler) {
                    if ($clause = $this->clauses[$this->getHandlerHash($handler)] ?? null) {
                        if ($clause->intercepted($payload)) {
                            continue;
                        }
                    }

                    $response = $this->callHandler($handler, $payload);

                    switch (true) {
                        case $response instanceof TerminateResult:
                            return $response->content;
                        case $response === true:
                            continue 2;
                        case $response === false:
                            break 2;
                        case ! empty($response) && ! ($result instanceof FinallyResult):
                            $result = $response;
                    }
                }
            }
        }

        return $result instanceof FinallyResult ? $result->content : $result;
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param mixed $handler
     */
    protected function newClause($handler): Clause
    {
        return $this->clauses[$this->getHandlerHash($handler)] = new Clause();
    }

    /**
     * @param mixed $handler
     *
     * @return string
     */
    protected function getHandlerHash($handler)
    {
        if (is_string($handler)) {
            return $handler;
        }

        if (is_array($handler)) {
            return is_string($handler[0])
                ? $handler[0] . '::' . $handler[1]
                : get_class($handler[0]) . $handler[1];
        }

        return spl_object_hash($handler);
    }

    /**
     * @param mixed $payload
     *
     * @return mixed
     */
    protected function callHandler(callable $handler, $payload)
    {
        try {
            return call_user_func_array($handler, [$payload]);
        } catch (Exception $e) {
            if (property_exists($this, 'app') && $this->app instanceof ServiceContainer) {
                $this->app['logger']->error($e->getCode() . ': ' . $e->getMessage(), [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }
    }

    /**
     * @param mixed $handler
     *
     * @return Closure
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    protected function makeClosure($handler)
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            if (! class_exists($handler)) {
                throw new InvalidArgumentException(sprintf('Class "%s" not exists.', $handler));
            }

            if (! in_array(EventHandlerInterface::class, (new ReflectionClass($handler))->getInterfaceNames(), true)) {
                throw new InvalidArgumentException(sprintf('Class "%s" not an instance of "%s".', $handler, EventHandlerInterface::class));
            }

            return function ($payload) use ($handler) {
                return (new $handler($this->app ?? null))->handle($payload);
            };
        }

        if ($handler instanceof EventHandlerInterface) {
            return function () use ($handler) {
                return $handler->handle(...func_get_args());
            };
        }

        throw new InvalidArgumentException('No valid handler is found in arguments.');
    }

    /**
     * @param mixed $handler
     * @param mixed $condition
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws ReflectionException
     */
    protected function resolveHandlerAndCondition($handler, $condition): array
    {
        if (is_int($handler) || (is_string($handler) && ! class_exists($handler))) {
            [$handler, $condition] = [$condition, $handler];
        }

        return [$this->makeClosure($handler), $condition];
    }
}
