<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc;

use Point\RpcClient\Contract\PackerInterface;
use Point\RpcClient\JsonRpc\DataFormatter;
use Point\RpcClient\ClientFactory;
use Point\RpcClient\Rpc\Contract\DataFormatterInterface;
use Point\RpcClient\Rpc\Contract\PathGeneratorInterface;
use Point\RpcClient\Rpc\Contract\TransporterInterface;

class Protocol
{
    /**
     * @var ProtocolManager
     */
    private $protocolManager;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    public function __construct(ProtocolManager $protocolManager, string $name, array $options = [])
    {
        $this->name = $name;
        $this->protocolManager = $protocolManager;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPacker(): PackerInterface
    {
        $packer = $this->protocolManager->getPacker($this->name);

        return make($packer, [$this->options]);
    }

    public function getTransporter(): TransporterInterface
    {
        $transporter = $this->protocolManager->getTransporter($this->name);
        return make($transporter, ['clientFactory' => make(ClientFactory::class),'config' => $this->options]);
    }

    public function getPathGenerator(): PathGeneratorInterface
    {
        $pathGenerator = $this->protocolManager->getPathGenerator($this->name);
        return make($pathGenerator);
    }

    public function getDataFormatter(): DataFormatterInterface
    {
        $dataFormatter = $this->protocolManager->getDataFormatter($this->name);
        return make($dataFormatter);
    }
}
