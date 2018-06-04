<?php

namespace Moro\Indexer\Common\Strategy\RemoveEntity\Decorator;

use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator;
use Moro\Indexer\Common\Strategy\RemoveEntityInterface as RemoveStrategy;

/**
 * Class EntityCacheStrategy
 * @package Moro\Indexer\Common\Strategy\RemoveEntity\Decorator
 */
class EntityCacheStrategy implements RemoveStrategy
{
    /** @var RemoveStrategy */
    private $_strategy;

    /**
     * @param RemoveStrategy $strategy
     */
    public function __construct(RemoveStrategy $strategy)
    {
        $this->_strategy = $strategy;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function remove(string $type, string $id)
    {
        EntityCacheDecorator::clearEntity($type, $id);
        $this->_strategy->remove($type, $id);
    }
}