<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface ReceiveViewInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface ReceiveViewInterface
{
    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    function receive(string $index, string $kind, string $id): ?string;
}