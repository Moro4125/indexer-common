<?php

namespace Moro\Indexer\Common\Event\Event;

/**
 * Class WaitRandomTickEvent
 * @package Moro\Indexer\Common\Event\Event
 */
class WaitRandomTickEvent extends AbstractEvent
{
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}