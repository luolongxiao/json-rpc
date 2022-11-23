<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\Contract;

interface PathGeneratorInterface
{
    public function generate(string $service, string $method): string;
}
