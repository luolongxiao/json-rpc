<?php

declare(strict_types=1);

use Point\RpcClient\Utils\Arr;
use Point\RpcClient\Utils\Backoff;
use Point\RpcClient\Utils\Collection;
use Point\RpcClient\Utils\Str;
//if (! function_exists('env')) {
//    /**
//     * Gets the value of an environment variable.
//     *
//     * @param string $key
//     * @param null|mixed $default
//     */
//    function env($key, $default = null)
//    {
//        $value = getenv($key);
//        if ($value === false) {
//            return value($default);
//        }
//        switch (strtolower($value)) {
//            case 'true':
//            case '(true)':
//                return true;
//            case 'false':
//            case '(false)':
//                return false;
//            case 'empty':
//            case '(empty)':
//                return '';
//            case 'null':
//            case '(null)':
//                return;
//        }
//        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
//            return substr($value, 1, -1);
//        }
//        return $value;
//    }
//}
if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param float|int $times
     * @param int $sleep millisecond
     * @throws \Throwable
     */
    function retry($times, callable $callback, int $sleep = 0)
    {
        $attempts = 0;
        $backoff = new Backoff($sleep);

        beginning:
        try {
            return $callback(++$attempts);
        } catch (\Throwable $e) {
            if (--$times < 0) {
                throw $e;
            }

            $backoff->sleep();
            goto beginning;
        }
    }
}
if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param null|array|int|string $key
     * @param null|mixed $default
     * @param mixed $target
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', is_int($key) ? (string) $key : $key);
        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }
                $result = [];
                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }
                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }
            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }
}
if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param array|string $key
     * @param bool $overwrite
     * @param mixed $value
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }
            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }
                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];
            if ($segments) {
                $target[$segment] = [];
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }
        return $target;
    }
}

if (! function_exists('make')) {

    function make(string $name, array $parameters = [])
    {

        $parameters = array_values($parameters);
        return new $name(...$parameters);
    }
}

