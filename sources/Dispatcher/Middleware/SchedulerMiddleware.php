<?php

namespace Moro\Indexer\Common\Dispatcher\Middleware;

use Moro\Indexer\Common\Dispatcher\Event\SchedulerDeriveEvent;
use Moro\Indexer\Common\Dispatcher\EventInterface;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as DispatcherInterface;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerInterface;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Throwable;

/**
 * Class SchedulerMiddleware
 * @package Moro\Indexer\Common\Dispatcher\Middleware
 */
class SchedulerMiddleware implements MiddlewareInterface
{
    private $_eventManager;
    private $_schedulerManager;

    /**
     * @param DispatcherInterface $dispatcher
     * @param SchedulerInterface $scheduler
     */
    public function __construct(DispatcherInterface $dispatcher, SchedulerInterface $scheduler)
    {
        $this->_eventManager = $dispatcher;
        $this->_schedulerManager = $scheduler;
    }

    /**
     * @param EventInterface $event
     * @param callable $next
     * @throws Throwable
     */
    public function handle(EventInterface $event, callable $next)
    {
        if (!$event instanceof SchedulerDeriveEvent) {
            $next();

            return;
        }

        $entry = clone $event->getEntry();

        try {
            try {
                $next();
            } catch (NotFoundException $exception) {
                if ($entry->getAction() == 'update') {
                    $entry->setAction('remove');

                    $event = new SchedulerDeriveEvent($entry, time());

                    $this->_eventManager->init();
                    $this->_eventManager->trigger($event);
                } else {
                    throw $exception;
                }
            }
        } catch (Throwable $exception) {
            $timestamp = ceil(time() / 60) * 60;
            $this->_schedulerManager->defer($timestamp, $entry);

            throw $exception;
        }

        $this->_eventManager->fire();
    }
}