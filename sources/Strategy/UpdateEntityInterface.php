<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface UpdateEntityInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface UpdateEntityInterface
{
    /**
     * @param string $type
     * @param string $id
     */
    function update(string $type, string $id);
}