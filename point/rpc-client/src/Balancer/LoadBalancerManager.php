<?php

declare(strict_types=1);

namespace Point\RpcClient\Balancer;

use InvalidArgumentException;

class LoadBalancerManager
{
    /**
     * @var array
     */
    private $algorithms = [
        'random' => Random::class,
        'round-robin' => RoundRobin::class,
        'weighted-random' => WeightedRandom::class,
        'weighted-round-robin' => WeightedRoundRobin::class,
    ];
    /**
     * @var \Point\LoadBalancer\LoadBalancerInterface[]
     */
    protected $balancer;

    /**
     * @var LoadBalancerManager
     */
    private static $instance;

    /**
     * Retrieve a class name of load balancer.
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException(sprintf('The %s algorithm does not exists.', $name));
        }
        return $this->algorithms[$name];
    }

    /**
     * Retrieve a class name of load balancer and create a object instance,
     * If $container object exists, then the class will create via container.
     *
     * @param string $key key of the load balancer instance
     * @param string $algorithm The name of the load balance algorithm
     */
    public function getBalancer(string $key, string $algorithm): LoadBalancerInterface
    {
        if (isset($this->balancer[$key])) {
            return $this->balancer[$key];
        }
        $class = $this->get($algorithm);
        if (function_exists('make')) {
            $instance = make($class);
        } else {
            $instance = new $class();
        }
        $this->balancer[$key] = $instance;
        return $instance;
    }
    public static function getInstance()
    {
        if(self::$instance) {
            return self::$instance;
        }

        self::$instance = new LoadBalancerManager();

        return self::$instance;
    }


    /**
     * Determire if the algorithm is exists.
     */
    public function has(string $name): bool
    {
        return isset($this->algorithms[$name]);
    }

    /**
     * Override the algorithms.
     */
    public function set(array $algorithms): self
    {
        foreach ($algorithms as $algorithm) {
            if (! class_exists($algorithm)) {
                throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
            }
        }
        $this->algorithms = $algorithms;
        return $this;
    }

    /**
     * Register a algorithm to the manager.
     */
    public function register(string $key, string $algorithm): self
    {
        if (! class_exists($algorithm)) {
            throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
        }
        $this->algorithms[$key] = $algorithm;
        return $this;
    }
}
