<?php

namespace Moro\Indexer\Common\Action;

/**
 * Interface RemoveEntityInterface
 * @package Moro\Indexer\Common\Action
 */
interface RemoveEntityInterface
{
    /**
     * @param string $type
     * @param string $id
     */
    function remove(string $type, string $id);
}