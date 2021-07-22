<?php

namespace Great\RedLock;

use Redis;
use Ramsey\Uuid\Uuid;

class RedLock {

    /**
     * @var array
     */
    private $instances;

    private $clockDriftFactor = 0.01;

    private $effectiveNum;

    /**
     * 
     * @param array
     * 
     */
    public function __construct(array $instances) {
        $this->instances = $instances;

        $num = count($instances);
        $this->effectiveNum = $num - ($num / 2 + 1);
    }

    /**
     * Lock
     * 
     * @param string $resource
     * @param int $ttl milliseconds
     * 
     * @return Lock
     */
    public function lock(string $resource, int $ttl): ?Lock {
        $startTime = microtime(true) * 1000;
        $token = Uuid::uuid4()->toString();

        $n = 0;
        foreach ($this->instances as $instance) {
            if ($this->lockInstance($instance, $resource, $token, $ttl)) {
                $n++;
            }
        }

        $drift = ($ttl * $this->clockDriftFactor) + 2;

        $validityPeriod = $ttl - (microtime(true) * 1000 - $startTime) - $drift;

        if ($n >= $this->effectiveNum && $validityPeriod > 0) {
            return new Lock($resource, $token, $validityPeriod);
        }

        $this->unlock(new Lock($resource, $token));
        return null;
    }

    /**
     * 
     * @param Redis $instance
     * @param string $resource
     * @param string $token
     * @param int $ttl
     * 
     * @return bool
     */
    protected function lockInstance(Redis $instance, string $resource, string $token, int $ttl): bool {
        return $instance->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param Lock $lock
     * 
     * @return void
     */
    public function unlock(Lock $lock): void {
        $resource = $lock->getResource();
        $token = $lock->getToken();

        foreach ($this->instances as $instance) {
            $this->unlockInstance($instance, $resource, $token);
        }
    }

    protected function unlockInstance(Redis $instance, string $resource, string $token): void {
        $script = <<<SCRIPT
        if redis.call("GET", KEYS[1]) === ARGV[1] then
            return redis.call("DEL", KEYS[1])
        else
            return 0
        end
SCRIPT;
        $instance->eval($script, [$resource, $token], 1);
    }
}
