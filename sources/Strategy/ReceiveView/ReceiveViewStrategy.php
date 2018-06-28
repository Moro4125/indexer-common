<?php

namespace Moro\Indexer\Common\Strategy\ReceiveView;

use Moro\Indexer\Common\Index\ManagerInterface as IndexManager;
use Moro\Indexer\Common\Strategy\ReceiveViewInterface;
use Moro\Indexer\Common\View\ManagerInterface as ViewManager;

/**
 * Class ReceiveViewStrategy
 * @package Moro\Indexer\Common\Strategy\ReceiveViews
 */
class ReceiveViewStrategy implements ReceiveViewInterface
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
     * @param IndexManager $index
     * @param ViewManager $view
     */
    public function __construct(IndexManager $index, ViewManager $view)
    {
        $this->_indexManager = $index;
        $this->_viewManager = $view;
    }

    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    public function receive(string $index, string $kind, string $id): ?string
    {
        $type = $this->_indexManager->getTypeByIndex($index);

        return $this->_viewManager->load($type, $kind, $id);
    }
}