<?php

namespace Moro\Indexer\Common\Scheduler;

/**
 * Interface FactoryInterface
 * @package Moro\Indexer\Common\Scheduler
 */
interface FactoryInterface
{
    /**
     * @return EntryInterface
     */
    function newEntry(): EntryInterface;
}