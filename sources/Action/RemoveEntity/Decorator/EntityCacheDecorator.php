<?php

namespace Moro\Indexer\Common\Action\RemoveEntity\Decorator;

use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator as ECD;
use Moro\Indexer\Common\Action\RemoveEntityInterface as RemoveAction;

/**
 * Class EntityCacheAction
 * @package Moro\Indexer\Common\Action\RemoveEntity\Decorator
 */
class EntityCacheDecorator implements RemoveAction
{
    /** @var RemoveAction */
    private $_strategy;

    /**
     * @param RemoveAction $strategy
     */
    public function __construct(RemoveAction $strategy)
    {
        $this->_strategy = $strategy;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function remove(string $type, string $id)
    {
        ECD::clearEntity($type, $id);
        $this->_strategy->remove($type, $id);
    }
}