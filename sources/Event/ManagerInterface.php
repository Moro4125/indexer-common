<?php

namespace Moro\Indexer\Common\Event;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Event
 */
interface ManagerInterface
{
    const TOP    = 99;
    const BEFORE = 75;
    const MIDDLE = 50;
    const AFTER  = 25;
    const LAST   = 10;

    /**
     * @param string $event
     * @param callable $listener
     * @param int|null $priority
     * @return $this
     */
    function attach(string $event, callable $listener, int $priority = null): ManagerInterface;

    /**
     * @param string $event
     * @param callable $listener
     * @return $this
     */
    function detach(string $event, callable $listener): ManagerInterface;

    /**
     * @return $this
     */
    function init(): ManagerInterface;

    /**
     * @param EventInterface $event
     * @return ManagerInterface
     */
    function trigger(EventInterface $event): ManagerInterface;

    /**
     * @return void
     */
    function fire();
}