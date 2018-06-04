<?php

namespace Moro\Indexer\Common\Event\Event;

/**
 * Class IndexUpdateEvent
 * @package Moro\Indexer\Common\Event\Event
 */
class IndexUpdateEvent extends AbstractEvent
{
    private $alias;

    /**
     * @param string $alias
     */
    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}