<?php

namespace Moro\Indexer\Common\Action\UpdateEntity\Decorator;

use Exception;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\Event\SchedulerDeferEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManager;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Action\UpdateEntityInterface as UpdateAction;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;

/**
 * Class SourceRepeatDecorator
 * @package Moro\Indexer\Common\Action\UpdateEntity\Decorator
 */
class SourceRepeatDecorator implements UpdateAction
{
    /** @var UpdateAction */
    protected $_strategy;

    /** @var TransactionManager */
    protected $_transaction;

    /** @var SchedulerManager */
    protected $_scheduler;

    /** @var EventManager */
    protected $_events;

    /** @var integer[] */
    protected $_nextTime;

    /** @var array */
    protected $_nextSlots;

    /** @var int */
    protected $_interval;

    /**
     * @param UpdateAction $strategy
     * @param TransactionManager $transaction
     * @param SchedulerManager $scheduler
     * @param EventManager $events
     * @param integer $interval
     */
    public function __construct(
        UpdateAction $strategy,
        TransactionManager $transaction,
        SchedulerManager $scheduler,
        EventManager $events,
        int $interval
    ) {
        $this->_strategy = $strategy;
        $this->_transaction = $transaction;
        $this->_scheduler = $scheduler;
        $this->_events = $events;
        $this->_interval = $interval;
        $this->_nextTime = [];
        $this->_nextSlots = [];
    }

    /**
     * @param string $type
     * @param string $id
     * @throws \Throwable
     */
    public function update(string $type, string $id)
    {
        try {
            $this->_strategy->update($type, $id);

            return;
        } catch (AdapterFailedException $exception) {
            $this->_repair($exception, $type, $id);
        } catch (\Throwable $exception) {
            $e = $exception;

            while ($e = $e->getPrevious()) {
                if ($e instanceof AdapterFailedException) {
                    $this->_repair($exception, $type, $id);

                    return;
                }
            }

            throw $exception;
        }
    }

    /**
     * @param Exception $exception
     * @param string $type
     * @param string $id
     */
    protected function _repair(Exception $exception, string $type, string $id)
    {
        if (!isset($this->_nextTime[$id])) {
            for (; $this->_interval > 0; $this->_interval += $this->_interval) {
                for ($level = end($this->_nextSlots) ?: 1; floor($this->_interval / $level) > 1; $level++) {
                    for ($step = 1; $step <= $level; $step++) {
                        if (empty($this->_nextSlots[$slot = (int)ceil($this->_interval / $level * $step)])) {
                            $this->_nextSlots[$slot] = $level;
                            $this->_nextTime[$id] = $slot;
                            break 3;
                        }
                    }
                }
            }
        }

        $nextTime = $this->_interval * ceil(time() / $this->_interval) + ($this->_nextTime[$id] ?? 0);
        $entry = $this->_scheduler->newEntry()
            ->setAction('update')
            ->setType($type)
            ->setId($id);

        $this->_transaction->execute(function () use ($nextTime, $entry) {
            $this->_scheduler->defer($nextTime, $entry);
        });

        $this->_events->trigger(new ExceptionRepairedEvent($exception, static::class));
        $this->_events->trigger(new SchedulerDeferEvent($entry->getAction(), $type, $id, $nextTime));
        $this->_events->fire();
    }
}