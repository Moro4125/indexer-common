<?php

namespace Moro\Indexer\Common\Index\Manager;

use Moro\Indexer\Common\Index\ManagerInterface;
use Moro\Indexer\Common\Index\StorageInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\Index\Manager
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
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    public function setStorage(StorageInterface $storage): ManagerInterface
    {
        $this->_getManager()
            ->setStorage($storage);

        return $this;
    }

    /**
     * @param string $type
     * @return int
     */
    public function lockType(string $type): int
    {
        return $this->_getManager()
            ->lockType($type);
    }

    /**
     * @param int $key
     * @return $this
     */
    public function freeType(int $key): ManagerInterface
    {
        $this->_getManager()
            ->freeType($key);

        return $this;
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function hasIndex(string $alias): bool
    {
        return $this->_getManager()
            ->hasIndex($alias);
    }

    /**
     * @param string $alias
     * @param string $type
     */
    public function addIndex(string $alias, string $type)
    {
        $this->_getManager()
            ->addIndex($alias, $type);
    }

    /**
     * @param string $type
     * @param string|null $id
     * @return array
     */
    public function findIndexes(string $type, string $id = null): array
    {
        return $this->_getManager()
            ->findIndexes($type, $id);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function dropIndex(string $alias): bool
    {
        return $this->_getManager()
            ->dropIndex($alias);
    }

    /**
     * @param string $index
     * @return null|string
     */
    public function getTypeByIndex(string $index): ?string
    {
        return $this->_getManager()
            ->getTypeByIndex($index);
    }

    /**
     * @param string $alias
     * @param string $id
     * @param string|null $order
     */
    public function insert(string $alias, string $id, string $order = null)
    {
        $this->_getManager()
            ->insert($alias, $id, $order);
    }

    /**
     * @param string $alias
     * @param int|null $from
     * @param int|null $limit
     * @param bool|null $withOrder
     * @return array
     */
    public function select(string $alias, int $from = null, int $limit = null, bool $withOrder = null): array
    {
        return $this->_getManager()
            ->select($alias, $from, $limit, $withOrder);
    }

    /**
     * @param string $alias
     * @param string $id
     * @return bool
     */
    public function remove(string $alias, string $id): bool
    {
        return $this->_getManager()
            ->remove($alias, $id);
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