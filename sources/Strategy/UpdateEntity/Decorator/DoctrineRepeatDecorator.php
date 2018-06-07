<?php /** @noinspection PhpRedundantCatchClauseInspection */

namespace Moro\Indexer\Common\Strategy\UpdateEntity\Decorator;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface as UpdateStrategy;

/**
 * Class DoctrineRepeatDecorator
 * @package Moro\Indexer\Common\Strategy\UpdateEntity\Decorator
 */
class DoctrineRepeatDecorator implements UpdateStrategy
{
    /** @var UpdateStrategy */
    protected $_strategy;

    /** @var EventManager */
    protected $_events;

    /** @var integer */
    protected $_retry;

    /**
     * @param UpdateStrategy $strategy
     * @param EventManager $events
     * @param null|integer $retry
     */
    public function __construct(UpdateStrategy $strategy, EventManager $events, int $retry = null)
    {
        $this->_strategy = $strategy;
        $this->_events = $events;
        $this->_retry = $retry ?? 3;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @throws RetryableException
     * @throws UniqueConstraintViolationException
     * @throws ForeignKeyConstraintViolationException
     */
    public function update(string $type, string $id)
    {
        $retry = $this->_retry;

        while ($retry) {
            try {
                $this->_strategy->update($type, $id);

                return;
            } catch (ForeignKeyConstraintViolationException $exception) {
                $retry--;
            } catch (UniqueConstraintViolationException $exception) {
                $retry--;
            } catch (RetryableException $exception) {
                $retry--;
            }

            if ($retry) {
                $this->_events->init()
                    ->trigger(new ExceptionRepairedEvent($exception, static::class))
                    ->fire();

                usleep(mt_rand(10000, 100000));
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        throw $exception;
    }
}