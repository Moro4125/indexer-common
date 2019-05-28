<?php

namespace Moro\Indexer\Common\Action\UpdateEntity\Decorator;

use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Index\Exception\DuplicateIndexException;
use Moro\Indexer\Common\Index\Storage\Decorator\AliasCacheDecorator;
use Moro\Indexer\Common\Action\UpdateEntityInterface as UpdateAction;

/**
 * Class IndexRepeatDecorator
 * @package Moro\Indexer\Common\Action\UpdateEntity\Decorator
 */
class IndexRepeatDecorator implements UpdateAction
{
    /** @var UpdateAction */
    protected $_strategy;

    /** @var EventManager */
    protected $_events;

    /** @var integer */
    protected $_retry;

    /**
     * @param UpdateAction $strategy
     * @param EventManager $events
     * @param null|integer $retry
     */
    public function __construct(UpdateAction $strategy, EventManager $events, int $retry = null)
    {
        $this->_strategy = $strategy;
        $this->_events = $events;
		$this->_retry = max(1, $retry ?? 3);
    }

    /**
     * @param string $type
     * @param string $id
     * @throws \Throwable
     */
    public function update(string $type, string $id)
    {
        $retry = $this->_retry;

        try {
            while ($retry) {
                try {
                    $this->_strategy->update($type, $id);

                    return;
                } catch (DuplicateIndexException $exception) {
                    AliasCacheDecorator::clear();
                    $retry--;
                }

                if ($retry) {
                    $this->_events->init()
                        ->trigger(new ExceptionRepairedEvent($exception, static::class))
                        ->fire();

                    usleep(mt_rand(10000, 100000));
                }
            }
        } catch (\Throwable $exception) {
            AliasCacheDecorator::clear();
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        throw $exception;
    }
}