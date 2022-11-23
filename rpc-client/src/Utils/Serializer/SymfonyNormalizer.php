<?php

declare(strict_types=1);

namespace Point\RpcClient\Utils\Serializer;

use Point\RpcClient\Contract\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class SymfonyNormalizer implements NormalizerInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalize($object)
    {
        return $this->serializer->normalize($object);
    }

    public function denormalize($data, string $class)
    {
        return $this->serializer->denormalize($data, $class);
    }
}
