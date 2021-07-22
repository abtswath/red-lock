<?php

namespace Great\RedLock;

class Lock {
    private $resource;

    private $token;

    private $ttl;

    public function __construct(string $resource, string $token, ?int $ttl = null) {
        $this->resource = $resource;
        $this->token = $token;
        $this->ttl = $ttl;
    }

    public function getResource(): string {
        return $this->resource;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function getTtl(): ?int {
        return $this->ttl;
    }
}
