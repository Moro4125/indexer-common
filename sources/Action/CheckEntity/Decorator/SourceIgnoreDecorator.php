<?php

namespace Moro\Indexer\Common\Action\CheckEntity\Decorator;

use Moro\Indexer\Common\Dispatcher\Event\ExceptionIgnoreEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManagerInterface;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Action\CheckEntityInterface;

/**
 * Class SourceIgnoreDecorator
 * @package Moro\Indexer\Common\Action\CheckEntity\Decorator
 */
class SourceIgnoreDecorator implements CheckEntityInterface
{
    private $_strategy;
    private $_events;

    public function __construct(CheckEntityInterface $strategy, EventManagerInterface $events)
    {
        $this->_strategy = $strategy;
        $this->_events = $events;
    }

    public function check(string $type)
    {
        try {
            $this->_strategy->check($type);

            return;
        } catch (AdapterFailedException $exception) {
            $this->_events->init()
                ->trigger(new ExceptionIgnoreEvent($exception, static::class))
                ->fire();

            return;
        } catch (\Throwable $exception) {
            $e = $exception;

            while ($e = $e->getPrevious()) {
                if ($e instanceof AdapterFailedException) {
                    $this->_events->init()
                        ->trigger(new ExceptionIgnoreEvent($exception, static::class))
                        ->fire();

                    return;
                }
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        throw $exception;
    }
}