<?php

namespace Moro\Indexer\Common\Dispatcher\Event;

use Moro\Indexer\Common\Dispatcher\EventInterface;

/**
 * Class AbstractEvent
 * @package Moro\Indexer\Common\Dispatcher\Event
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