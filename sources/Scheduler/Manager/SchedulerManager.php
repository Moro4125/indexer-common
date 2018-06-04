<?php

namespace Moro\Indexer\Common\Scheduler\Manager;

use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\FactoryInterface;
use Moro\Indexer\Common\Scheduler\ManagerInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;

/**
 * Class SchedulerManager
 * @package Moro\Indexer\Common\Scheduler\Manager
 */
class SchedulerManager implements ManagerInterface
{
    /** @var FactoryInterface */
    protected $_factory;

    /** @var StorageInterface */
    protected $_storage;

    /**
     * @param FactoryInterface $factory
     * @return ManagerInterface
     */
    public function setFactory(FactoryInterface $factory): ManagerInterface
    {
        $this->_factory = $factory;
        return $this;
    }

    /**
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    public function setStorage(StorageInterface $storage): ManagerInterface
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * @return EntryInterface
     */
    public function newEntry(): EntryInterface
    {
        assert($this->_factory !== null);

        return $this->_factory->newEntry();
    }

    /**
     * @param int $timestamp
     * @param EntryInterface $entry
     */
    public function defer(int $timestamp, EntryInterface $entry)
    {
        assert($this->_storage !== null);

        $this->_storage->defer($timestamp, $entry);
    }

    /**
     * @return EntryInterface|null
     */
    public function derive(): ?EntryInterface
    {
        assert($this->_storage !== null);
        $entry = $this->newEntry();

        return $this->_storage->derive($entry) ? $entry : null;
    }
}