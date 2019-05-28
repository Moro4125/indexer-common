<?php

namespace Moro\Indexer\Common\Action\ReceiveIds;

use Moro\Indexer\Common\Action\ReceiveIdsInterface;
use Moro\Indexer\Common\Index\ManagerInterface;

/**
 * Class ReceiveIdsAction
 * @package Moro\Indexer\Common\Action\ReceiveIds
 */
class ReceiveIdsAction implements ReceiveIdsInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_manager;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->_manager = $manager;
    }

    /**
     * @param string $index
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receiveIds(string $index, int $offset = null, int $limit = null): array
    {
        return $this->_manager->select($index, $offset, $limit);
    }
}