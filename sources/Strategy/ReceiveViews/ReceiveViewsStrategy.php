<?php

namespace Moro\Indexer\Common\Strategy\ReceiveViews;

use Moro\Indexer\Common\Strategy\ReceiveViewsInterface;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManager;
use Moro\Indexer\Common\View\ManagerInterface as ViewManager;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;

/**
 * Class ReceiveViewsStrategy
 * @package Moro\Indexer\Common\Strategy\ReceiveViews
 */
class ReceiveViewsStrategy implements ReceiveViewsInterface
{
    /**
     * @var IndexManager
     */
    protected $_indexManager;

    /**
     * @var ViewManager
     */
    protected $_viewManager;

    /**
     * @var TransactionManager
     */
    protected $_transactionManager;

    /**
     * ReceiveViewsStrategy constructor.
     * @param IndexManager $index
     * @param ViewManager $view
     * @param TransactionManager $transaction
     */
    public function __construct(IndexManager $index, ViewManager $view, TransactionManager $transaction)
    {
        $this->_indexManager = $index;
        $this->_viewManager = $view;
        $this->_transactionManager = $transaction;
    }

    /**
     * @param string $index
     * @param string $kind
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receive(string $index, string $kind, int $offset = null, int $limit = null): array
    {
        $list = $this->_indexManager->select($index, $offset, $limit);
        $type = $this->_indexManager->getTypeByIndex($index);

        $this->_transactionManager->execute(function() use (&$list, $type, $kind) {
            foreach ($list as &$value) {
                $value = $this->_viewManager->load($type, $kind, $value);
            }
        });

        return $list;
    }
}