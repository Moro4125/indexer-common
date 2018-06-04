<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\Adapter\MemoryAdapter;

/**
 * Class FailedSourceAdapter
 * @package Moro\Indexer\Test
 */
class FailedSourceAdapter extends MemoryAdapter
{
    /**
     * @param string $id
     * @return array
     */
    public function receiveEntityById(string $id): array
    {
        throw new \RuntimeException(self::class);
    }
}