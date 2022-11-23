<?php

declare(strict_types=1);

namespace Point\RpcClient\Utils\Serializer;

use Point\RpcClient\Contract\NormalizerInterface;

class SimpleNormalizer implements NormalizerInterface
{
    public function normalize($object)
    {
        return $object;
    }

    public function denormalize($data, string $class)
    {
        switch ($class) {
            case 'int':
                return (int) $data;
            case 'string':
                return (string) $data;
            case 'float':
                return (float) $data;
            case 'array':
                return (array) $data;
            case 'bool':
                return (bool) $data;
            default:
                return $data;
        }
    }
}
