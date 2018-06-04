<?php

namespace Moro\Indexer\Common\Strategy\WaitingForAction;

use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Moro\Indexer\Common\Event\Event\MessageIsDerivedEvent;
use Moro\Indexer\Common\Event\Event\SchedulerDeriveEvent;
use Moro\Indexer\Common\Event\Event\WaitRandomTickEvent;
use Moro\Indexer\Common\Event\ManagerInterface as EventManager;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManager;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManager;
use Moro\Indexer\Common\Strategy\WaitingForActionInterface;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;

/**
 * Class WaitingForActionStrategy
 * @package Moro\Indexer\Common\Strategy\WaitingForAction
 */
class WaitingForActionStrategy implements WaitingForActionInterface
{
    private $_percent = 1;
    /** @var BusManager */
    private $_busManager;
    /** @var SourceManager */
    private $_sourceManager;
    /** @var SchedulerManager */
    private $_schedulerManager;
    /** @var EventManager */
    private $_eventManager;
    /** @var TransactionManager */
    private $_transactionManager;

    /**
     * @param BusManager $bus
     * @param SourceManager $source
     * @param SchedulerManager $scheduler
     * @param EventManager $events
     * @param TransactionManager $transaction
     */
    public function __construct(
        BusManager $bus,
        SourceManager $source,
        SchedulerManager $scheduler,
        EventManager $events,
        TransactionManager $transaction
    ) {
        $this->_busManager = $bus;
        $this->_sourceManager = $source;
        $this->_schedulerManager = $scheduler;
        $this->_eventManager = $events;
        $this->_transactionManager = $transaction;
    }

    /**
     * @param int|null $limit
     */
    public function wait(int $limit = null)
    {
        $limit = $limit ?? -1;
        $types = $this->_sourceManager->getTypes();
        $tLine = (int)ceil(count($types) * (100 / $this->_percent));

        $cask = array_values($types);
        $list = array_fill(0, $tLine - count($cask), false);
        $cask = array_merge($list, $cask);

        shuffle($cask);

        while ($limit) {
            $this->_eventManager->init();
            $result = null;

            $this->_transactionManager->execute(function () use (&$limit, &$cask, &$result) {
                while ($limit) {
                    $limit--;

                    if ($this->_busManager && null !== $message = $this->_busManager->read()) {
                        $result = $message;
                        $event = new MessageIsDerivedEvent($message);
                        $this->_eventManager->trigger($event);
                        break;
                    }

                    if (null !== $entry = $this->_schedulerManager->derive()) {
                        $result = $entry;
                        $event = new SchedulerDeriveEvent($entry, time());
                        $this->_eventManager->trigger($event);
                        break;
                    }

                    if (null !== ($type = array_shift($cask)) && array_push($cask, $type) && $type) {
                        $result = $type;
                        $event = new WaitRandomTickEvent($type);
                        $this->_eventManager->trigger($event);
                        break;
                    }

                    usleep(mt_rand(1000, 100000)); // 1000000 = 1 sec.
                }
            });

            if ($result instanceof EntryInterface && $result->getAction() == 'update') {
                try {
                    $this->_eventManager->fire();
                } catch (NotFoundException $exception) {
                    $entry = clone $result;
                    $entry->setAction('remove');

                    $event = new SchedulerDeriveEvent($entry, time());
                    $this->_eventManager->init()
                        ->trigger($event);
                }
            }

            $this->_eventManager->fire();
        }
    }
}