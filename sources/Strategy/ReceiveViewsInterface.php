<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface ReceiveViewsInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface ReceiveViewsInterface
{
    /**
     * @param string $index
     * @param string $kind
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    function receive(string $index, string $kind, int $offset = null, int $limit = null): array;
}