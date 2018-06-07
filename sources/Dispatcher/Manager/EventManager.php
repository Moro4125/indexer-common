<?php

namespace Moro\Indexer\Common\Dispatcher\Manager;

use Moro\Indexer\Common\Dispatcher\EventInterface;
use Moro\Indexer\Common\Dispatcher\ManagerInterface;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;
use SplObjectStorage;
use SplPriorityQueue;

/**
 * Class EventManager
 * @package Moro\Indexer\Common\Dispatcher\Manager
 */
class EventManager implements ManagerInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $_middlewares;

    /**
     * @var SplPriorityQueue
     */
    protected $_middlewaresQueue;

    /**
     * @var SplObjectStorage[]
     */
    protected $_listeners;

    /**
     * @var SplPriorityQueue[]
     */
    protected $_listenersQueue;

    /**
     * @var EventInterface[]
     */
    protected $_events;

    /**
     * @param MiddlewareInterface $middleware
     * @param int|null $priority
     * @return ManagerInterface
     */
    public function wrap(MiddlewareInterface $middleware, int $priority = null): ManagerInterface
    {
        $middlewares = $this->_middlewares ?? ($this->_middlewares = new SplObjectStorage());
        $middlewares->attach($middleware, $priority ?? self::MIDDLE);
        $this->_middlewaresQueue = null;

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return ManagerInterface
     */
    public function unwrap(MiddlewareInterface $middleware): ManagerInterface
    {
        $middlewares = $this->_middlewares ?? ($this->_middlewares = new SplObjectStorage());
        $middlewares->detach($middleware);
        $this->_middlewaresQueue = null;

        return $this;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param int|null $priority
     * @return $this
     */
    public function attach(string $event, callable $listener, int $priority = null): ManagerInterface
    {
        assert(is_object($listener));

        $listeners = $this->_listeners[$event] ?? ($this->_listeners[$event] = new SplObjectStorage());
        /** @noinspection PhpParamsInspection */
        $listeners->attach($listener, $priority ?? self::MIDDLE);
        $this->_listenersQueue[$event] = null;

        return $this;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return $this
     */
    public function detach(string $event, callable $listener): ManagerInterface
    {
        assert(is_object($listener));

        $listeners = $this->_listeners[$event] ?? new SplObjectStorage();
        /** @noinspection PhpParamsInspection */
        $listeners->detach($listener);
        $this->_listenersQueue[$event] = null;

        return $this;
    }

    /**
     * @return $this
     */
    public function init(): ManagerInterface
    {
        $this->_events = null;

        return $this;
    }

    /**
     * @param EventInterface $event
     * @return $this
     */
    public function trigger(EventInterface $event): ManagerInterface
    {
        $this->_events[] = $event;

        return $this;
    }

    /**
     * @return void
     */
    public function fire()
    {
        /** @var EventInterface $event */
        $queue = $this->_middlewaresQueue;
        $events = $this->_events ?? [];
        $this->_events = null;
        $event = null;

        if (!$queue && $this->_middlewares) {
            $this->_middlewaresQueue = $queue = new SplPriorityQueue();

            foreach ($this->_middlewares as $middleware) {
                $queue->insert($middleware, PHP_INT_MAX - $this->_middlewares->offsetGet($middleware));
            }
        }

        $step = function () use (&$event) {
            $name = $event->getName();
            $queue = $this->_listenersQueue[$name] ?? null;

            if (!$queue && $listeners = $this->_listeners[$name] ?? null) {
                $this->_listenersQueue[$name] = $queue = new SplPriorityQueue();

                foreach ($listeners as $listener) {
                    $queue->insert($listener, $listeners->offsetGet($listener));
                }
            }

            if ($queue) {
                foreach (clone $queue as $listener) {
                    if (!$event->isPropagationStopped()) {
                        $listener($event);
                    }
                }
            }
        };

        if ($queue) {
            /** @var MiddlewareInterface $middleware */
            foreach (clone $queue as $middleware) {
                $step = function () use (&$event, $step, $middleware) {
                    $middleware->handle($event, $step);
                };
            }
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($events as $event) {
            $step();
        }
    }
}