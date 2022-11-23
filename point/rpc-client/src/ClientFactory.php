<?php

declare(strict_types=1);

namespace Point\RpcClient;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class ClientFactory
{
    public function __construct()
    {

    }

    public function create(array $options = []): Client
    {
        $stack = null;

        $config = array_replace(['handler' => $stack], $options);

        return new Client($config);
    }
}
