<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface RemoveEntityInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface RemoveEntityInterface
{
    /**
     * @param string $type
     * @param string $id
     */
    function remove(string $type, string $id);
}