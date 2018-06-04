<?php

namespace Moro\Indexer\Common\Event\Event;

use Moro\Indexer\Common\Scheduler\EntryInterface;

/**
 * Class SchedulerDeriveEvent
 * @package Moro\Indexer\Common\Event\Event
 */
class SchedulerDeriveEvent extends AbstractEvent
{
    private $entry;
    private $timestamp;

    /**
     * @param EntryInterface $entry
     * @param int $timestamp
     */
    public function __construct(EntryInterface $entry, int $timestamp)
    {
        $this->entry = $entry;
        $this->timestamp = $timestamp;
    }


    public function getEntry(): EntryInterface
    {
        return $this->entry;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}