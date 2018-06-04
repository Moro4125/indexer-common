<?php

namespace Moro\Indexer\Common\Scheduler\Factory;

use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\FactoryInterface;

/**
 * Class ClassFactory
 * @package Moro\Indexer\Common\Scheduler\Factory
 */
class ClassFactory implements FactoryInterface
{
    protected $_class;

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->_class = $class;
    }

    /**
     * @return EntryInterface
     */
    public function newEntry(): EntryInterface
    {
        return new $this->_class;
    }
}