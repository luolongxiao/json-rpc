<?php

declare(strict_types=1);

namespace Point\RpcClient\JsonRpc;


use Point\RpcClient\Rpc\Contract\DataFormatterInterface;

class DataFormatter implements DataFormatterInterface
{


    public function __construct()
    {
//        $this->context = $context;
    }

    public function formatRequest($data)
    {
        [$path, $params, $id] = $data;
        return [
            'jsonrpc' => '2.0',
            'method' => $path,
            'params' => $params,
            'id' => $id,
//            'context' => $this->context->getData(),
        ];
    }

    public function formatResponse($data)
    {
        [$id, $result] = $data;
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
//            'context' => $this->context->getData(),
        ];
    }

    public function formatErrorResponse($data)
    {
        [$id, $code, $message, $data] = $data;

        if (isset($data) && $data instanceof \Throwable) {
            $data = [
                'class' => get_class($data),
                'code' => $data->getCode(),
                'message' => $data->getMessage(),
            ];
        }
        return [
            'jsonrpc' => '2.0',
            'id' => $id ?? null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
//            'context' => $this->context->getData(),
        ];
    }
}
