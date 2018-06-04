<?php

namespace Moro\Indexer\Common\Strategy\ReceiveIds;

use Moro\Indexer\Common\Index\ManagerInterface;
use Moro\Indexer\Common\Strategy\ReceiveIdsInterface;

/**
 * Class ReceiveIdsStrategy
 * @package Moro\Indexer\Common\Strategy\ReceiveIds
 */
class ReceiveIdsStrategy implements ReceiveIdsInterface
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