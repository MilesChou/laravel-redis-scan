<?php

declare(strict_types=1);

namespace MilesChou\Template;

use Illuminate\Redis\Connections\Connection;
use Redis;

class KeysByScan
{

    public function __construct(private readonly Connection $redis)
    {
    }

    public function __invoke(string $pattern): array
    {
        $prefix = $this->redis->client()->getOption(Redis::OPT_PREFIX);
        $cursor = $defaultCursorValue = '0';
        $keys = [];

        do {
            [$cursor, $tagsChunk] = $this->redis->scan(
                $cursor,
                ['match' => $prefix . $pattern],
            );

            if (!is_array($tagsChunk)) {
                break;
            }

            $tagsChunk = array_unique($tagsChunk);
            if (empty($tagsChunk)) {
                continue;
            }

            foreach ($tagsChunk as $tag) {
                $keys[] = $tag;
            }
        } while (((string)$cursor) !== $defaultCursorValue);

        sort($keys);

        return $keys;
    }
}
