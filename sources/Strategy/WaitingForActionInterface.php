<?php

namespace Moro\Indexer\Common\Strategy;

/**
 * Interface WaitingForActionInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface WaitingForActionInterface
{
    /**
     * @param int|null $limit
     * @return mixed
     */
    function wait(int $limit = null);
}