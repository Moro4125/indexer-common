<?php

namespace Moro\Indexer\Common\Event\Event;

/**
 * Class SchedulerDeferEvent
 * @package Moro\Indexer\Common\Event\Event
 */
class SchedulerDeferEvent extends AbstractEvent
{
    private $action;
    private $type;
    private $id;
    private $timestamp;

    /**
     * @param string $action
     * @param string $type
     * @param string $id
     * @param int $timestamp
     */
    public function __construct(string $action, string $type, string $id, int $timestamp)
    {
        $this->type = $type;
        $this->action = $action;
        $this->id = $id;
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}