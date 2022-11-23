<?php

declare(strict_types=1);

namespace Point\RpcClient\JsonRpc\Packer;

use Point\RpcClient\Contract\PackerInterface;

class JsonEofPacker implements PackerInterface
{
    /**
     * @var string
     */
    protected $eof;

    public function __construct(array $options = [])
    {
        $this->eof = $options['settings']['package_eof'] ?? "\r\n";
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $data . $this->eof;
    }

    public function unpack(string $data)
    {
        $data = rtrim($data, $this->eof);
        return json_decode($data, true);
    }
}
