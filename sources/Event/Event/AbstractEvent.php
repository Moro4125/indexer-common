<?php

namespace Moro\Indexer\Common\Event\Event;

use Moro\Indexer\Common\Event\EventInterface;

/**
 * Class AbstractEvent
 * @package Moro\Indexer\Common\Event\Event
 */
class AbstractEvent implements EventInterface
{
    private $_isPropagationStopped;

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * @return $this
     */
    public function stopPropagation(): EventInterface
    {
        $this->_isPropagationStopped = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return (bool)$this->_isPropagationStopped;
    }
}