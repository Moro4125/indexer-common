<?php

namespace Moro\Indexer\Common\Action;

/**
 * Interface CheckEntityInterface
 * @package Moro\Indexer\Common\Action
 */
interface CheckEntityInterface
{
    /**
     * @param string $type
     */
    function check(string $type);
}