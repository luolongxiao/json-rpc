<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\IdGenerator;

class UniqidIdGenerator implements IdGeneratorInterface
{
    public function generate()
    {
        return uniqid();
    }
}
