<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc\Contract;

use Point\RpcClient\Balancer\LoadBalancerInterface;

interface TransporterInterface
{
    public function send(string $data);

    public function recv();

    public function getLoadBalancer(): ?LoadBalancerInterface;

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface;
}
