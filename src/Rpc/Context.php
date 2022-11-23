<?php

declare(strict_types=1);

namespace Point\RpcClient\Rpc;

use Point\RpcClient\Utils\Arr;
use Point\RpcClient\Utils\Context as ContextUtil;

class Context2
{
    public function getData(): array
    {
        return ContextUtil::get($this->getContextKey(), []);
    }

    public function setData(array $data): void
    {
        ContextUtil::set($this->getContextKey(), $data);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->getData(), $key, $default);
    }

    public function set($key, $value): void
    {
        $data = $this->getData();
        $data[$key] = $value;
        ContextUtil::set($this->getContextKey(), $data);
    }

    public function clear(): void
    {
        ContextUtil::set($this->getContextKey(), []);
    }

    protected function getContextKey(): string
    {
        return static::class . '::DATA';
    }
}
