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

    public function __invoke(string $pattern, ?int $count = null): array
    {
        $prefix = $this->redis->client()->getOption(Redis::OPT_PREFIX);
        $cursor = $defaultCursorValue = '0';
        $keys = [];

        do {
            $options = [
                'match' => $prefix . $pattern,
            ];

            if ($count !== null) {
                $options['count'] = $count;
            }

            [$cursor, $tagsChunk] = $this->redis->scan(
                $cursor,
                $options,
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
