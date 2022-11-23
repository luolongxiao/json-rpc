<?php

declare(strict_types=1);

namespace Point\RpcClient\Balancer;

use Point\RpcClient\Balancer\Exception\RuntimeException;

class RoundRobin extends AbstractLoadBalancer
{
    /**
     * @var int
     */
    private static $current = 0;

    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $count = count($this->nodes);
        if ($count <= 0) {
            throw new RuntimeException('Nodes missing.');
        }
        $item = $this->nodes[self::$current % $count];
        ++self::$current;
        return $item;
    }
}
