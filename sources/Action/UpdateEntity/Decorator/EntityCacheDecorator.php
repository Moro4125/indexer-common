<?php

namespace Moro\Indexer\Common\Action\UpdateEntity\Decorator;

use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator as EntityCache;
use Moro\Indexer\Common\Action\UpdateEntityInterface as UpdateAction;

/**
 * Class EntityCacheDecorator
 * @package Moro\Indexer\Common\Action\UpdateEntity\Decorator
 */
class EntityCacheDecorator implements UpdateAction
{
    /** @var UpdateAction */
    protected $_strategy;

    /**
     * @param UpdateAction $strategy
     */
    public function __construct(UpdateAction $strategy)
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