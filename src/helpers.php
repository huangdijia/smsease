<?php

declare(strict_types=1);
/**
 * This file is part of Smsease.
 *
 * @link     https://github.com/huangdijia/smsease
 * @document https://github.com/huangdijia/smsease/blob/main/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/huangdijia/smsease/blob/main/LICENSE
 */
if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed $value
     * @param null|callable $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new class($value) {
                /**
                 * The target being tapped.
                 *
                 * @var mixed
                 */
                public $target;

                /**
                 * Create a new tap proxy instance.
                 *
                 * @param mixed $target
                 */
                public function __construct($target)
                {
                    $this->target = $target;
                }

                /**
                 * Dynamically pass method calls to the target.
                 *
                 * @param string $method
                 * @param array $parameters
                 * @return mixed
                 */
                public function __call($method, $parameters)
                {
                    $this->target->{$method}(...$parameters);

                    return $this->target;
                }
            };
        }

        $callback($value);

        return $value;
    }
}

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param mixed $value
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param mixed $condition
     * @param string|\Throwable $exception
     * @param array ...$parameters
     * @throws \Throwable
     * @return mixed
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int $times
     * @param int $sleep
     * @param null|callable $when
     * @throws \Exception
     * @return mixed
     */
    function retry($times, callable $callback, $sleep = 0, $when = null)
    {
        $attempts = 0;

        beginning:
        $attempts++;
        --$times;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if ($times < 1 || ($when && ! $when($e))) {
                throw $e;
            }

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}
