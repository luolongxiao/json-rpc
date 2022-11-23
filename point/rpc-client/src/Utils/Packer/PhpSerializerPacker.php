<?php

declare(strict_types=1);

namespace Point\RpcClient\Utils\Packer;

use Point\RpcClient\Contract\PackerInterface;

class PhpSerializerPacker implements PackerInterface
{
    public function pack($data): string
    {
        return serialize($data);
    }

    public function unpack(string $data)
    {
        return unserialize($data);
    }
}
