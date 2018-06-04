<?php

namespace Moro\Indexer\Common\Event;

/**
 * Interface EventInterface
 * @package Moro\Indexer\Common\Event
 */
interface EventInterface
{
    /**
     * @return string
     */
    function getName(): string;

    /**
     * @return $this
     */
    function stopPropagation(): EventInterface;

    /**
     * @return bool
     */
    function isPropagationStopped(): bool;
}