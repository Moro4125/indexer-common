<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface CheckEntityInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface CheckEntityInterface
{
    /**
     * @param string $type
     */
    function check(string $type);
}