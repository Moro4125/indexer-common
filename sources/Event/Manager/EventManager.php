<?php

namespace Moro\Indexer\Common\Event\Manager;

use Moro\Indexer\Common\Event\EventInterface;
use Moro\Indexer\Common\Event\ManagerInterface;
use SplObjectStorage;
use SplPriorityQueue;

/**
 * Class EventManager
 * @package Moro\Indexer\Common\Event\Manager
 */
class EventManager implements ManagerInterface
{
    /**
     * @var SplObjectStorage[]
     */
    protected $_listeners;

    /**
     * @var EventInterface[]
     */
    protected $_events;

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
        $events = $this->_events ?? [];
        $this->_events = null;

        foreach ($events as $event) {
            if ($listeners = $this->_listeners[$event->getName()] ?? null) {
                $queue = new SplPriorityQueue();

                foreach ($listeners as $listener) {
                    $queue->insert($listener, $listeners->offsetGet($listener));
                }

                foreach ($queue as $listener) {
                    if (!$event->isPropagationStopped()) {
                        $listener($event);
                    }
                }
            }
        }
    }
}