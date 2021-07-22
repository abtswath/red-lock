# Redis distributed locks in php

```
$instances = [
    new Redis('127.0.0.1', 6379, 0.01),
    new Redis('127.0.0.1', 6380, 0.01),
    new Redis('127.0.0.1', 6381, 0.01),
    new Redis('127.0.0.1', 6382, 0.01),
];
$redLock = new RedLock($instances);
```
### lock
```
$lock = $redLock->lock('resource', 10 * 1000);

```
The returned value is `null` if the lock was not acquired, or: 
```
object(Great\RedLock\Lock)#3 (3) {
    ["resource":"Great\RedLock\Lock":private]=>
    string(8) "resource"
    ["token":"Great\RedLock\Lock":private]=>
    string(36) "0b103701-d030-4f06-b744-4e69a6aed051"
    ["ttl":"Great\RedLock\Lock":private]=>
    int(1000)
}
```
### unlock
```
$redLock->unlock($lock);
```
