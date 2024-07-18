<?php

namespace Benchmarks;

use Illuminate\Container\Container;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Collection;
use MilesChou\Template\KeysByScan;

/**
 * Benchmark keys
 *
 * @BeforeMethods({"init"})
 * @AfterMethods({"check"})
 */
class KeysBench
{
    private array $keys = [];

    private ?Connection $connection = null;

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
        Collection::times(100_000, fn($num) => $this->connection->set("foo:$num", 1));
        Collection::times(10_000, fn($num) => $this->connection->set("bar:$num", 1));
    }

    public function check(): void
    {
        $count = count($this->keys);

        assert($count === 100_000, 'Check error, expect is 100000, actual is ' . $count);
    }

    /**
     * @Revs(1)
     * @Iterations(1)
     */
    public function benchKeys(): void
    {
        $this->keys = $this->connection->keys('foo:*');
    }
}