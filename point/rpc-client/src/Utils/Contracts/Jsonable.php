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
namespace Point\RpcClient\Utils\Contracts;

interface Jsonable
{
    public function __toString(): string;
}
