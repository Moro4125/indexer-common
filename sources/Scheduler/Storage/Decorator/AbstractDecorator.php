<?php

namespace Moro\Indexer\Common\Scheduler\Storage\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Scheduler\Storage\Decorator
 */
abstract class AbstractDecorator implements StorageInterface, DecoratorInterface
{
    protected $_instance;

    public function defer(int $timestamp, EntryInterface $entry)
    {
        $this->getDecoratedInstance()
            ->defer($timestamp, $entry);
    }

    public function derive(EntryInterface $entry): bool
    {
        return $this->getDecoratedInstance()
            ->derive($entry);
    }

    public function getDecoratedInstance(): StorageInterface
    {
        return $this->_instance;
    }
}