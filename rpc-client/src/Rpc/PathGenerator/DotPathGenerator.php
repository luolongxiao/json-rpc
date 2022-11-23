<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\PathGenerator;

use Point\RpcClient\Rpc\Contract\PathGeneratorInterface;
use Point\RpcClient\Utils\Str;

class DotPathGenerator implements PathGeneratorInterface
{
    public function generate(string $service, string $method): string
    {
        $handledNamespace = explode('\\', $service);
        $handledNamespace = Str::replaceArray('\\', ['/'], end($handledNamespace));
        $path = Str::studly($handledNamespace);

        return $path . '.' . Str::studly($method);
    }
}
