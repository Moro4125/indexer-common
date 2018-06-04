<?php

namespace Moro\Indexer\Common\Scheduler\Manager;

use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\FactoryInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;
use Moro\Indexer\Common\Scheduler\ManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\Scheduler\Manager
 */
class LazyManager implements ManagerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_manager;
    protected $_instance;

    /**
     * @param ContainerInterface $container
     * @param string|null $manager
     */
    public function __construct(ContainerInterface $container, string $manager = null)
    {
        $this->_container = $container;
        $this->_manager = $manager;
    }

    /**
     * @param FactoryInterface $factory
     * @return ManagerInterface
     */
    public function setFactory(FactoryInterface $factory): ManagerInterface
    {
        $this->_getManager()->setFactory($factory);
        return $this;
    }

    /**
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    public function setStorage(StorageInterface $storage): ManagerInterface
    {
        $this->_getManager()->setStorage($storage);
        return $this;
    }

    /**
     * @return EntryInterface
     */
    public function newEntry(): EntryInterface
    {
        return $this->_getManager()->newEntry();
    }

    /**
     * @param int $timestamp
     * @param EntryInterface $entry
     */
    public function defer(int $timestamp, EntryInterface $entry)
    {
        $this->_getManager()->defer($timestamp, $entry);
    }

    /**
     * @return EntryInterface|null
     */
    public function derive(): ?EntryInterface
    {
        return $this->_getManager()->derive();
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @return ManagerInterface
     */
    protected function _getManager(): ManagerInterface
    {
        $this->_manager = $this->_manager ?? ManagerInterface::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_instance = $this->_instance ?? $this->_container->get($this->_manager);

        return $this->_instance;
    }
}