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
namespace Point\RpcClient\Utils\Codec;

use Point\RpcClient\Utils\Contracts\Arrayable;
use Point\RpcClient\Utils\Contracts\Jsonable;
use Point\RpcClient\Utils\Exception\InvalidArgumentException;

class Json
{
    /**
     * @param mixed $data
     * @throws InvalidArgumentException
     */
    public static function encode($data, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        try {
            $json = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0)
    {
        try {
            $decode = json_decode($json, $assoc, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}
