<?php /** @noinspection PhpRedundantCatchClauseInspection */

namespace Moro\Indexer\Common\Strategy\CheckEntity\Decorator;

use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception\RetryableException;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionIgnoreEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManagerInterface;
use Moro\Indexer\Common\Strategy\CheckEntityInterface;

/**
 * Class DoctrineIgnoreDecorator
 * @package Moro\Indexer\Common\Strategy\CheckEntity\Decorator
 */
class DoctrineIgnoreDecorator implements CheckEntityInterface
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
        } catch (ConstraintViolationException $exception) {
            $this->_events->init()
                ->trigger(new ExceptionIgnoreEvent($exception, static::class))
                ->fire();

            return;
        } catch (RetryableException $exception) {
            $this->_events->init()
                ->trigger(new ExceptionIgnoreEvent($exception, static::class))
                ->fire();

            return;
        }
    }
}