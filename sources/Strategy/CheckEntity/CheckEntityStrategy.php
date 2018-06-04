<?php

namespace Moro\Indexer\Common\Strategy\CheckEntity;

use DateTime;
use Moro\Indexer\Common\Event\ManagerInterface as EventManager;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManager;
use Moro\Indexer\Common\Scheduler\FactoryInterface as EntryFactory;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManager;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManager;
use Moro\Indexer\Common\Strategy\CheckEntityInterface;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;

/**
 * Class CheckEntityStrategy
 * @package Moro\Indexer\Common\Strategy\CheckEntity
 */
class CheckEntityStrategy implements CheckEntityInterface
{
    /** @var SourceManager */
    protected $_source;
    /** @var IndexManager */
    protected $_index;
    /** @var SchedulerManager */
    protected $_scheduler;
    /** @var EventManager */
    protected $_events;
    /** @var TransactionManager */
    protected $_transaction;
    /** @var EntryFactory */
    protected $_factory;

    /** @var integer */
    protected $_receiveLimit;
    /** @var integer */
    protected $_updateLimit;

    /**
     * @param SourceManager $source
     * @param IndexManager $index
     * @param SchedulerManager $scheduler
     * @param EventManager $events
     * @param TransactionManager $transaction
     * @param EntryFactory $factory
     * @param null|int $receive
     * @param null|int $update
     */
    public function __construct(
        SourceManager $source,
        IndexManager $index,
        SchedulerManager $scheduler,
        EventManager $events,
        TransactionManager $transaction,
        EntryFactory $factory,
        int $receive = null,
        int $update = null
    ) {
        $this->_source = $source;
        $this->_index = $index;
        $this->_scheduler = $scheduler;
        $this->_events = $events;
        $this->_transaction = $transaction;
        $this->_factory = $factory;
        $this->_receiveLimit = $receive ?? 1000;
        $this->_updateLimit = $update ?? 100;
    }

    /**
     * @param string $type
     */
    public function check(string $type)
    {
        if (!$this->_index->hasIndex($type . ':catalog')) {
            return;
        }

        $list = $this->_index->select($type . ':catalog', 0, null, true);

        $entitiesLimit = $this->_updateLimit;
        $limit = $this->_receiveLimit;
        $step = 0;

        while ($entitiesLimit && $idList = $this->_source->getIdList($type, $step * $limit, $limit)) {
            $this->_checkOldestRecord($list, $type);
            $step++;

            $this->_transaction->execute(function () use ($type, $idList, &$list, &$entitiesLimit) {
                foreach ($idList as $id => $updatedAt) {
                    $updated = (new DateTime($updatedAt))->getTimestamp();

                    if (empty($list[$id]) || (new DateTime($list[$id]))->getTimestamp() < $updated) {
                        $entitiesLimit--;

                        $entry = $this->_factory->newEntry();
                        $entry->setAction('update');
                        $entry->setType($type);
                        $entry->setId($id);

                        $timestamp = floor(time() / $this->_receiveLimit) * $this->_receiveLimit;
                        $this->_scheduler->defer($timestamp, $entry);
                    }

                    unset($list[$id]);

                    if (!$entitiesLimit) {
                        return;
                    }
                }
            });
        }

        while ($entitiesLimit && reset($list) && $id = key($list)) {
            $entitiesLimit--;
            array_shift($list);

            $entry = $this->_factory->newEntry();
            $entry->setAction('remove');
            $entry->setType($type);
            $entry->setId($id);

            $this->_scheduler->defer(time(), $entry);
        }
    }

    private function _checkOldestRecord(&$list, $type)
    {
        if (reset($list) && $id = key($list)) {
            unset($list[$id]);
            $list[$id] = gmdate(\DateTime::ATOM, time());

            $entry = $this->_factory->newEntry();
            $entry->setAction('update');
            $entry->setType($type);
            $entry->setId($id);

            $this->_scheduler->defer(time(), $entry);
        }
    }
}