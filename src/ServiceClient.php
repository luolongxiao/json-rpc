<?php

declare(strict_types=1);

namespace Point\RpcClient;

use Point\RpcClient\Contract\IdGeneratorInterface;
use Point\RpcClient\Contract\NormalizerInterface;
use Point\Di\MethodDefinitionCollectorInterface;
use Point\RpcClient\Exception\RequestException;
use Point\RpcClient\Utils\Arr;
use Point\RpcClient\Utils\Serializer\SimpleNormalizer;
use Psr\Container\ContainerInterface;

require_once __DIR__.'/Utils/Functions.php';

class ServiceClient extends AbstractServiceClient
{
    /**
     * @var MethodDefinitionCollectorInterface
     */
    protected $methodDefinitionCollector;
    /**
     * @var string
     */
    protected $serviceInterface;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(string $serviceName, string $protocol = 'jsonrpc-http', array $options = [])
    {
        $this->serviceName = $serviceName;
        $this->protocol = $protocol;
        parent::__construct();
        $this->normalizer = make(SimpleNormalizer::class);
    }
    public function __call(string $method, array $params)
    {
        return $this->__request($method, $params);
    }

}
