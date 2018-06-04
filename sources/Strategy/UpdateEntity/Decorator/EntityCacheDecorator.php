<?php

namespace Moro\Indexer\Common\Strategy\UpdateEntity\Decorator;

use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator as EntityCache;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface as UpdateStrategy;

/**
 * Class EntityCacheDecorator
 * @package Moro\Indexer\Common\Strategy\UpdateEntity\Decorator
 */
class EntityCacheDecorator implements UpdateStrategy
{
    /** @var UpdateStrategy */
    protected $_strategy;

    /**
     * @param UpdateStrategy $strategy
     */
    public function __construct(UpdateStrategy $strategy)
    {
        $this->_strategy = $strategy;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function update(string $type, string $id)
    {
        EntityCache::clearEntity($type, $id);
        $this->_strategy->update($type, $id);
    }
}