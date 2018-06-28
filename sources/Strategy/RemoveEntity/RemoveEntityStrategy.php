<?php

namespace Moro\Indexer\Common\Strategy\RemoveEntity;

use Moro\Indexer\Common\Dispatcher\Event\IndexUpdateEvent;
use Moro\Indexer\Common\Dispatcher\Event\ViewDropEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManager;
use Moro\Indexer\Common\Strategy\RemoveEntityInterface;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;
use Moro\Indexer\Common\View\ManagerInterface as ViewManager;

/**
 * Class RemoveEntityStrategy
 * @package Moro\Indexer\Common\Strategy\UpdateEntity
 */
class RemoveEntityStrategy implements RemoveEntityInterface
{
    /**
     * @var ViewManager
     */
    protected $_viewManager;

    /**
     * @var IndexManager
     */
    protected $_indexManager;

    /**
     * @var TransactionManager
     */
    protected $_transactionManager;

    /**
     * @var EventManager
     */
    protected $_eventManager;

    /**
     * @param IndexManager $index
     * @param ViewManager $view
     * @param TransactionManager $transaction
     * @param EventManager $events
     */
    public function __construct(
        IndexManager $index,
        ViewManager $view,
        TransactionManager $transaction,
        EventManager $events
    ) {
        $this->_viewManager = $view;
        $this->_indexManager = $index;
        $this->_transactionManager = $transaction;
        $this->_eventManager = $events;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function remove(string $type, string $id)
    {
        $calls = [];
        $existsKinds = $this->_viewManager->findKinds($type, $id);
        $existsList = $this->_indexManager->findIndexes($type, $id);

        $this->_eventManager->init();

        if (count($existsList)) {
            foreach ($existsList as $alias) {
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

        if (count($existsKinds)) {
            foreach ($existsKinds as $kind) {
                $calls[] = function () use ($type, $kind, $id) {
                    $this->_viewManager->drop($type, $kind, $id);
                    $this->_eventManager->trigger(new ViewDropEvent($type, $kind, $id));
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