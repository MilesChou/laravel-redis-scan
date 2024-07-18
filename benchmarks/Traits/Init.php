<?php

namespace Benchmarks\Traits;

use Illuminate\Container\Container;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Collection;

trait Init
{
    private ?Connection $connection = null;

    private array $keys = [];

    private function createRedisManager(): RedisManager
    {
        return new RedisManager(
            new Container(),
            'phpredis',
            [
                'options' => [
                    'cluster' => 'redis',
                ],

                'default' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 0,
                    'read_timeout' => 1,
                ],
            ],
        );
    }

    public function init(): void
    {
        if ($this->connection === null) {
            $this->connection = $this->createRedisManager()->connection();
        }

        $this->connection->flushdb();
        Collection::times(1_000, fn($num) => $this->connection->set("foo:$num", 1));
        Collection::times(100, fn($num) => $this->connection->set("bar:$num", 1));
    }

    public function check(): void
    {
        $count = count($this->keys);

        assert($count === 1_000, 'Check error, expect is 100000, actual is ' . $count);
    }
}