<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc;

use Point\RpcClient\Contract\ConfigInterface;
use Point\RpcClient\Utils\Str;
use InvalidArgumentException;

class ProtocolManager
{
    /**
     * @var \Point\RpcClient\Contract\ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function register(string $name, array $data)
    {
        return $this->config->set('protocol.' . $name, $data);
    }

    public function registerOrAppend(string $name, array $data)
    {
        $key = 'protocols.' . $name;
        return $this->config->set($key, array_merge($this->config->get($key, []), $data));
    }

    public function getProtocol(string $name): array
    {
        return $this->config->get('protocols.' . $name, []);
    }

    public function getPacker(string $name): string
    {
        return $this->getTarget($name, 'packer');
    }

    public function getTransporter(string $name): string
    {
        return $this->getTarget($name, 'transporter');
    }

    public function getPathGenerator(string $name): string
    {
        return $this->getTarget($name, 'path-generator');
    }

    public function getDataFormatter(string $name): string
    {
        return $this->getTarget($name, 'data-formatter');
    }

    private function getTarget(string $name, string $target)
    {
        $result = $this->config->get('protocols.' . Str::lower($name) . '.' . Str::lower($target));
        if (! is_string($result)) {
            throw new InvalidArgumentException(sprintf('%s is not exists.', Str::studly($target, ' ')));
        }
        return $result;
    }
}
