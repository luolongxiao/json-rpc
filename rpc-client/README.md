### 需要在thinkphp 的.env 添加如下配置
```
[JSONRPC]
# JSON_RPC服务器列表,多个使用,隔开
SERVER_NODES=192.168.8.8:9504
# JSON_RPC连接超时时间，默认：3
RPC_CONNECT_TIMEOUT=3
# JSON_RPC返回数据超时时间，默认：3
RPC_REV_TIMEOUT=3
```