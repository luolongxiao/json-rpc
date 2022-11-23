<?php

declare(strict_types=1);

namespace Point\RpcClient\Contract;

interface NormalizerInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param mixed $object
     * @return null|array|\ArrayObject|bool|float|int|string
     */
    public function normalize($object);

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param string $class The expected class to instantiate
     * @return mixed|object
     */
    public function denormalize($data, string $class);
}
