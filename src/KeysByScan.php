<?php

declare(strict_types=1);

namespace MilesChou\Template;

use Illuminate\Redis\Connections\Connection;
use Redis;

class KeysByScan
{
    private const DEFAULT_CURSOR = '0';

    public function __construct(private readonly Connection $redis)
    {
    }

    public function __invoke(string $pattern, ?int $count = null): array
    {
        $prefix = $this->redis->client()->getOption(Redis::OPT_PREFIX);
        $cursor = self::DEFAULT_CURSOR;
        $keys = [];

        $options = [
            'match' => $prefix . $pattern,
        ];

        if ($count !== null) {
            $options['count'] = $count;
        }

        do {
            [$cursor, $result] = $this->redis->scan($cursor, $options);

            if (!is_array($result)) {
                break;
            }

            if (empty($result)) {
                continue;
            }

            $result = array_unique($result);

            $keys = [
                ...$keys,
                ...$result,
            ];
        } while ((string)$cursor !== self::DEFAULT_CURSOR);

        sort($keys);

        return $keys;
    }
}
