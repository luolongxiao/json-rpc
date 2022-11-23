<?php

declare(strict_types=1);

namespace Point\RpcClient\Contract;

interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}
