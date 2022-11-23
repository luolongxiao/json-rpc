<?php

declare(strict_types=1);
/**
 * This file is part of Point.
 *
 * @link     https://www.Point.io
 * @document https://Point.wiki
 * @contact  group@Point.io
 * @license  https://github.com/Point/Point/blob/master/LICENSE
 */
namespace Point\RpcClient\Utils;


class Context
{
    protected static $nonCoContext = [];

    public static function set(string $id, $value)
    {
        static::$nonCoContext[$id] = $value;
        return $value;
    }

    public static function get(string $id, $default = null, $coroutineId = null)
    {

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function has(string $id, $coroutineId = null)
    {
        return isset(static::$nonCoContext[$id]);
    }

    /**
     * Release the context when you are not in coroutine environment.
     */
    public static function destroy(string $id)
    {
        unset(static::$nonCoContext[$id]);
    }

    /**
     * Copy the context from a coroutine to current coroutine.
     */
    public static function copy(int $fromCoroutineId, array $keys = []): void
    {

    }

    /**
     * Retrieve the value and override it by closure.
     */
    public static function override(string $id, \Closure $closure)
    {
        $value = $closure($value);
        self::set($id, $value);
        return $value;
    }

    /**
     * Retrieve the value and store it if not exists.
     * @param mixed $value
     */
    public static function getOrSet(string $id, $value)
    {
        if (! self::has($id)) {
            return self::set($id, value($value));
        }
        return self::get($id);
    }

    public static function getContainer()
    {

        return static::$nonCoContext;
    }
}
