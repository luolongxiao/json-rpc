<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\PathGenerator;

use Point\RpcClient\Rpc\Contract\PathGeneratorInterface;

class FullPathGenerator implements PathGeneratorInterface
{
    public function generate(string $service, string $method): string
    {
        $path = str_replace('\\', '/', $service);
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path . '/' . $method;
    }
}
