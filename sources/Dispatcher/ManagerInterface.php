<?php

namespace Moro\Indexer\Common\Dispatcher;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Dispatcher
 */
interface ManagerInterface
{
    const TOP    = 99;
    const BEFORE = 75;
    const MIDDLE = 50;
    const AFTER  = 25;
    const LAST   = 10;

    /**
     * @param MiddlewareInterface $middleware
     * @param int|null $priority
     * @return ManagerInterface
     */
    function wrap(MiddlewareInterface $middleware, int $priority = null): ManagerInterface;

    /**
     * @param MiddlewareInterface $middleware
     * @return ManagerInterface
     */
    function unwrap(MiddlewareInterface $middleware): ManagerInterface;

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