<?php

namespace Moro\Indexer\Common\Action;

/**
 * Interface ReceiveIdsInterface
 * @package Moro\Indexer\Common\Action
 */
interface ReceiveIdsInterface
{
    /**
     * @param string $index
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    function receiveIds(string $index, int $offset = null, int $limit = null): array;
}