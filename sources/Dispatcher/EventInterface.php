<?php

namespace Moro\Indexer\Common\Dispatcher;

/**
 * Interface EventInterface
 * @package Moro\Indexer\Common\Dispatcher
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