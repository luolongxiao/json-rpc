<?php

declare(strict_types=1);

namespace Point\RpcClient;

use Point\RpcClient\Config;
use Point\RpcClient\Contract\ConfigInterface;
use Point\RpcClient\Contract\IdGeneratorInterface;
use Point\RpcClient\Balancer\LoadBalancerInterface;
use Point\RpcClient\Balancer\LoadBalancerManager;
use Point\RpcClient\Balancer\Node;
use Point\RpcClient\Rpc\Contract\DataFormatterInterface;
use Point\RpcClient\Rpc\Contract\PathGeneratorInterface;
use Point\RpcClient\Rpc\IdGenerator;
use Point\RpcClient\Rpc\Protocol;
use Point\RpcClient\Rpc\ProtocolManager;
use Point\RpcClient\Exception\RequestException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
//use Point\ServiceGovernance\DriverInterface;
//use Point\ServiceGovernance\DriverManager;

abstract class AbstractServiceClient
{
    protected $config = [
        // name 需与服务提供者的 name 属性相同
        'name' => '',
        // 服务提供者的服务协议，可选，默认值为 jsonrpc-http
        // 可选 jsonrpc-http jsonrpc jsonrpc-tcp-length-check（暂时只支持jsonrpc-http）
        'protocol' => 'jsonrpc-http',
        // 负载均衡算法，可选，默认值为 random
        'load_balancer' => 'random',
        // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息(此配置暂时不可用)
        'registry' => [],
        //['host' => '192.168.8.8', 'port' => 9504], 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
        'nodes' => [],
        // 配置项，会影响到 Packer 和 Transporter
        'protocols' => [
//            'jsonrpc' => [
//                'packer' => 'Point\RpcClient\JsonRpc\Packer\JsonEofPacker',
//                'transporter' => 'Point\RpcClient\JsonRpc\JsonRpcTransporter',
//                'path-generator' => 'Point\RpcClient\JsonRpc\PathGenerator',
//                'data-formatter' => 'Point\RpcClient\JsonRpc\DataFormatter',
//            ],
//            'jsonrpc-tcp-length-check' => [
//                'packer' => 'Point\RpcClient\JsonRpc\Packer\JsonLengthPacker',
//                'transporter' => 'Point\RpcClient\JsonRpc\JsonRpcTransporter',
//                'path-generator' => 'Point\RpcClient\JsonRpc\PathGenerator',
//                'data-formatter' => 'Point\RpcClient\JsonRpc\DataFormatter',
//            ],
            'jsonrpc-http' => [
                'packer' => 'Point\RpcClient\Utils\Packer\JsonPacker',
                'transporter' => 'Point\RpcClient\JsonRpc\JsonRpcHttpTransporter',
                'path-generator' => 'Point\RpcClient\JsonRpc\PathGenerator',
                'data-formatter' => 'Point\RpcClient\JsonRpc\DataFormatter',
            ],
        ],
        'options' => [
            'connect_timeout' => 3,
            'recv_timeout' => 3,
            'settings' => [
                // 根据协议不同，区分配置
                'open_eof_split' => true,
                'package_eof' => "\r\n",
            ]
        ],
    ];
    /**
     * The service name of the target service.
     *
     * @var string
     */
    protected $serviceName = '';

    /**
     * The protocol of the target service, this protocol name
     * needs to register into \Point\RpcClient\Rpc\ProtocolManager.
     *
     * @var string
     */
    protected $protocol = 'jsonrpc-http';

    /**
     * The load balancer of the client, this name of the load balancer
     * needs to register into \Point\LoadBalancer\LoadBalancerManager.
     *
     * @var string
     */
    protected $loadBalancer = 'random';

    /**
     * @var \Point\RpcClient\RpcClient\Client
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Point\LoadBalancer\LoadBalancerManager
     */
    protected $loadBalancerManager;

    /**
     * @var null|\Point\Contract\IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var PathGeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var DataFormatterInterface
     */
    protected $dataFormatter;

    public function __construct()
    {
        // 读取配置信息
        if(function_exists('env')) {
            $nodes = explode(',', env('JSONRPC.SERVER_NODES'));
            if(!empty($nodes)) {
                foreach ($nodes as $node) {
                    list($server, $port) = explode(':', $node);
                    if($server && $port) {
                        $this->config['nodes'][] = [
                            'host' => $server,
                            'port' => (int)$port
                        ];
                    }
                }
            }
            if(env('JSONRPC.RPC_CONNECT_TIMEOUT')) {
                $this->config['options']['connect_timeout'] = (int) env('JSONRPC.RPC_CONNECT_TIMEOUT');
            }
            if(env('JSONRPC.RPC_REV_TIMEOUT')) {
                $this->config['options']['recv_timeout'] = (int) env('JSONRPC.RPC_REV_TIMEOUT');
            }
        }

        $protocol = $this->protocol ?? $this->config['protocol'];
        $this->loadBalancer = $this->loadBalancer ?? $this->config['load_balancer'];
        $this->serviceName = $this->serviceName ?? $this->config['name'];

        $this->loadBalancerManager = LoadBalancerManager::getInstance();
        $protocol = new Protocol(new ProtocolManager(new Config($this->config)), $protocol, $this->getOptions());
        $loadBalancer = $this->createLoadBalancer(...$this->createNodes());
        $transporter = $protocol->getTransporter()->setLoadBalancer($loadBalancer);
        $this->client = make(Client::class)
            ->setPacker($protocol->getPacker())
            ->setTransporter($transporter);
        $this->idGenerator = $this->getIdGenerator();
        $this->pathGenerator = $protocol->getPathGenerator();
        $this->dataFormatter = $protocol->getDataFormatter();
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if (! $id && $this->idGenerator instanceof IdGeneratorInterface) {
            $id = $this->idGenerator->generate();
        }
        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (is_array($response)) {
            $response = $this->checkRequestIdAndTryAgain($response, $id);
            if (array_key_exists('result', $response)) {
                return $response['result'];
            }
            if (array_key_exists('error', $response)) {
                return $response['error'];
            }
        }
        throw new RequestException('Invalid response.');
    }

    protected function __generateRpcPath(string $methodName): string
    {
        if (! $this->serviceName) {
            throw new InvalidArgumentException('Parameter $serviceName missing.');
        }
        return $this->pathGenerator->generate($this->serviceName, $methodName);
    }

    protected function __generateData(string $methodName, array $params, ?string $id)
    {
        return $this->dataFormatter->formatRequest([$this->__generateRpcPath($methodName), $params, $id]);
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    protected function getIdGenerator(): IdGeneratorInterface
    {
//        if ($this->container->has(IdGenerator\IdGeneratorInterface::class)) {
//            return $this->container->get(IdGenerator\IdGeneratorInterface::class);
//        }
//
//        if ($this->container->has(IdGeneratorInterface::class)) {
//            return $this->container->get(IdGeneratorInterface::class);
//        }
//
//        return $this->container->get(IdGenerator\UniqidIdGenerator::class);

        return make(IdGenerator\UniqidIdGenerator::class);
    }

    protected function createLoadBalancer(array $nodes, callable $refresh = null): LoadBalancerInterface
    {
        $loadBalancer = $this->loadBalancerManager->getBalancer($this->serviceName, $this->loadBalancer)->setNodes($nodes);
        $refresh && $loadBalancer->refresh($refresh);
        return $loadBalancer;
    }

    protected function getOptions(): array
    {

        return $this->config['options'] ?? [];
    }

    /**
     * Create nodes the first time.
     *
     * @return array [array, callable]
     */
    protected function createNodes(): array
    {
        $refreshCallback = null;
        $consumer = $this->getConsumerConfig();

        /*
         $registryProtocol = $consumer['registry']['protocol'] ?? null;
        $registryAddress = $consumer['registry']['address'] ?? null;
        // Current $consumer is the config of the specified consumer.
        if (! empty($registryProtocol) && $this->container->has(DriverManager::class)) {
            $governance = $this->container->get(DriverManager::class)->get($registryProtocol);
            if (! $governance) {
                throw new InvalidArgumentException(sprintf('Invalid protocol of registry %s', $registryProtocol));
            }
            $nodes = $this->getNodes($governance, $registryAddress);
            $refreshCallback = function () use ($governance, $registryAddress) {
                return $this->getNodes($governance, $registryAddress);
            };

            return [$nodes, $refreshCallback];
        }
         */
        // Not exists the registry config, then looking for the 'nodes' property.
        if (isset($consumer['nodes'])) {
            $nodes = [];
            foreach ($consumer['nodes'] ?? [] as $item) {
                if (isset($item['host'], $item['port'])) {
                    if (! is_int($item['port'])) {
                        throw new InvalidArgumentException(sprintf('Invalid node config [%s], the port option has to a integer.', implode(':', $item)));
                    }
                    $nodes[] = new Node($item['host'], $item['port'], $item['weight'] ?? 0, $item['path_prefix'] ?? '');
                }
            }
            return [$nodes, $refreshCallback];
        }

        throw new InvalidArgumentException('Config of registry or nodes missing.');
    }

    protected function getNodes(DriverInterface $governance, string $address): array
    {
        $nodeArray = $governance->getNodes($address, $this->serviceName, [
            'protocol' => $this->protocol,
        ]);
        $nodes = [];
        foreach ($nodeArray as $node) {
            $nodes[] = new Node($node['host'], $node['port'], $node['weight'] ?? 0, $node['path_prefix'] ?? '');
        }

        return $nodes;
    }
    protected function getConsumerConfig(): array
    {
        return $this->config;
    }
    protected function checkRequestIdAndTryAgain(array $response, $id, int $again = 1): array
    {
        if (is_null($id)) {
            // If the request id is null then do not check.
            return $response;
        }

        if (isset($response['id']) && $response['id'] === $id) {
            return $response;
        }

        if ($again <= 0) {
            throw new RequestException(sprintf(
                'Invalid response. Request id[%s] is not equal to response id[%s].',
                $id,
                $response['id'] ?? null
            ));
        }

        $response = $this->client->recv();
        --$again;

        return $this->checkRequestIdAndTryAgain($response, $id, $again);
    }
}
