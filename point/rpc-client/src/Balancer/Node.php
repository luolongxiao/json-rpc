<?php

declare(strict_types=1);

namespace Point\RpcClient\Balancer;

class Node
{
    /**
     * @var int
     */
    public $weight;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * The path prefix, only support protocol `jsonrpc-http`.
     * @var string
     */
    public $pathPrefix = '';

    public function __construct(string $host, int $port, int $weight = 0, string $pathPrefix = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->weight = $weight;
        $this->pathPrefix = $pathPrefix;
    }
}
