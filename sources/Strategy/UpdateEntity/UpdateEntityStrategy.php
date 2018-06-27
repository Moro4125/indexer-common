<?php

namespace Moro\Indexer\Common\Strategy\UpdateEntity;

use Moro\Indexer\Common\Dispatcher\Event\IndexUpdateEvent;
use Moro\Indexer\Common\Dispatcher\Event\SchedulerDeferEvent;
use Moro\Indexer\Common\Dispatcher\Event\ViewDropEvent;
use Moro\Indexer\Common\Dispatcher\Event\ViewSaveEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManager;
use Moro\Indexer\Common\Regulation\ManagerInterface as RegulationManager;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManager;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\UnknownTypeException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManager;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;
use Moro\Indexer\Common\View\ManagerInterface as ViewManager;

/**
 * Class UpdateEntityStrategy
 * @package Moro\Indexer\Common\Strategy\UpdateEntity
 */
class UpdateEntityStrategy implements UpdateEntityInterface
{
    /**
     * @var SourceManager
     */
    protected $_sourceManager;

    /**
     * @var RegulationManager
     */
    protected $_regulationManager;

    /**
     * @var ViewManager
     */
    protected $_viewManager;

    /**
     * @var IndexManager
     */
    protected $_indexManager;

    /**
     * @var SchedulerManager
     */
    protected $_schedulerManager;

    /**
     * @var TransactionManager
     */
    protected $_transactionManager;

    /**
     * @var EventManager
     */
    protected $_eventManager;

    /**
     * @param SourceManager $source
     * @param RegulationManager $regulation
     * @param IndexManager $index
     * @param ViewManager $view
     * @param SchedulerManager $scheduler
     * @param TransactionManager $transaction
     * @param EventManager $events
     */
    public function __construct(
        SourceManager $source,
        RegulationManager $regulation,
        IndexManager $index,
        ViewManager $view,
        SchedulerManager $scheduler,
        TransactionManager $transaction,
        EventManager $events
    ) {
        $this->_sourceManager = $source;
        $this->_regulationManager = $regulation;
        $this->_viewManager = $view;
        $this->_indexManager = $index;
        $this->_schedulerManager = $scheduler;
        $this->_transactionManager = $transaction;
        $this->_eventManager = $events;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @throws UnknownTypeException
     * @throws NotFoundException
     * @throws WrongStructureException
     * @throws AdapterFailedException
     */
    public function update(string $type, string $id)
    {
        $calls = [];

        $entity = $this->_sourceManager->getEntity($type, $id);
        $updated = gmdate(\DateTime::ATOM, time());
        $result = $this->_regulationManager->handle($type, $entity)
            ->addToIndex($type . ':catalog', $updated);
        $existsKinds = array_flip($this->_viewManager->findKinds($type, $id));
        $existsList = array_flip($this->_indexManager->findIndexes($type, $id));

        $this->_eventManager->init();

        if ($result->getKindListCount() + count($existsKinds)) {
            foreach ($result->getKindListIterator() as $kind) {
                $calls[] = function () use ($type, $kind, $entity) {
                    $this->_viewManager->save($type, $kind, $entity);
                    $this->_eventManager->trigger(new ViewSaveEvent($type, $kind, $entity->getId()));
                };
                unset($existsKinds[$kind]);
            }

            foreach (array_keys($existsKinds) as $kind) {
                $calls[] = function () use ($type, $kind, $entity) {
                    $id = $entity->getId();
                    $this->_viewManager->drop($type, $kind, $id);
                    $this->_eventManager->trigger(new ViewDropEvent($type, $kind, $id));
                };
            }
        }

        if ($result->getIndexListCount() + count($existsList)) {
            foreach ($result->getIndexListIterator() as $alias => $order) {
                if (!$this->_indexManager->hasIndex($alias)) {
                    $calls[] = function () use ($alias, $type) {
                        $this->_indexManager->addIndex($alias, $type);
                    };
                }

                $calls[] = function () use ($alias, $id, $order) {
                    $this->_indexManager->insert($alias, $id, $order);
                    $this->_eventManager->trigger(new IndexUpdateEvent($alias));
                };
                unset($existsList[$alias]);
            }

            foreach (array_keys($existsList) as $alias) {
                if ($this->_indexManager->hasIndex($alias)) {
                    $calls[] = function () use ($alias, $id) {
                        $this->_indexManager->remove($alias, $id);
                        $this->_eventManager->trigger(new IndexUpdateEvent($alias));
                    };

                    if ($this->_indexManager->select($alias, 0, 2, false) == [$id]) {
                        $calls[] = function () use ($alias) {
                            $this->_indexManager->dropIndex($alias);
                        };
                    }
                }
            }
        }

        if ($result->getEntryListCount()) {
            foreach ($result->getEntryListIterator() as $timestamp) {
                $calls[] = function () use ($timestamp, $type, $id) {
                    $action = 'update';
                    $entry = $this->_schedulerManager->newEntry()
                        ->setAction($action)
                        ->setType($type)
                        ->setId($id);
                    $this->_schedulerManager->defer($timestamp, $entry);
                    $this->_eventManager->trigger(new SchedulerDeferEvent($action, $type, $id, $timestamp));
                };
            }
        }

        $this->_transactionManager->execute(function () use ($calls) {
            foreach ($calls as $call) {
                $call();
            }
        });

        $this->_eventManager->fire();
    }
}