<?php

namespace Moro\Indexer\Common\Action;

/**
 * Interface WaitingForActionInterface
 * @package Moro\Indexer\Common\Action
 */
interface WaitingForActionInterface
{
    /**
     * @param int|null $limit
     * @return mixed
     */
    function wait(int $limit = null);
}