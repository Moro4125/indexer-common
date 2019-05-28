<?php

namespace Moro\Indexer\Common\Action;

/**
 * Interface UpdateEntityInterface
 * @package Moro\Indexer\Common\Action
 */
interface UpdateEntityInterface
{
    /**
     * @param string $type
     * @param string $id
     */
    function update(string $type, string $id);
}