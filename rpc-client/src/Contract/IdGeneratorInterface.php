<?php

declare(strict_types=1);

namespace Point\RpcClient\Contract;

interface IdGeneratorInterface
{
    public function generate();
}
