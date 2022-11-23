<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\IdGenerator;

use Point\RpcClient\Contract\IdGeneratorInterface;

class RequestIdGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        $us = strstr(microtime(), ' ', true);
        return strval($us * 1000 * 1000) . rand(100, 999);
    }
}
