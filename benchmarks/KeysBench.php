<?php

namespace Benchmarks;

/**
 * Benchmark keys
 *
 * @BeforeMethods({"init"})
 * @AfterMethods({"check"})
 */
class KeysBench
{
    use Traits\Init;


    /**
     * @Revs(100)
     * @Iterations(3)
     */
    public function benchKeys(): void
    {
        $this->keys = $this->connection->keys('foo:*');
    }
}