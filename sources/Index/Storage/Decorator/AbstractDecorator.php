<?php

namespace Moro\Indexer\Common\Index\Storage\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Index\StorageInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Index\Storage\Decorator
 */
abstract class AbstractDecorator implements StorageInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @param string $type
     * @return int
     */
    public function lockType(string $type): int
    {
        return $this->getDecoratedInstance()
            ->lockType($type);
    }

    /**
     * @param int $key
     * @return $this
     */
    public function freeType(int $key): StorageInterface
    {
        $this->getDecoratedInstance()
            ->freeType($key);

        return $this;
    }

    public function hasAlias(int $index): ?string
    {
        return $this->getDecoratedInstance()
            ->hasAlias($index);
    }

    public function hasIndex(string $alias): ?int
    {
        return $this->getDecoratedInstance()
            ->hasIndex($alias);
    }

    public function getTypeByIndex(string $alias): ?string
    {
        return $this->getDecoratedInstance()
            ->getTypeByIndex($alias);
    }

    public function dropIndex(int $index): bool
    {
        return $this->getDecoratedInstance()
            ->dropIndex($index);
    }

    public function addIndex(string $alias, string $type): int
    {
        return $this->getDecoratedInstance()
            ->addIndex($alias, $type);
    }

    public function findIndexes(string $type, string $id = null): array
    {
        return $this->getDecoratedInstance()
            ->findIndexes($type, $id);
    }

    public function insert(int $index, string $id, string $order = null)
    {
        $this->getDecoratedInstance()
            ->insert($index, $id, $order);
    }

    public function select(int $index, int $from = null, int $limit = null, bool $withOrder = null): array
    {
        return $this->getDecoratedInstance()
            ->select($index, $from, $limit, $withOrder);
    }

    public function remove(int $index, string $id): bool
    {
        return $this->getDecoratedInstance()
            ->remove($index, $id);
    }

    public function getDecoratedInstance(): StorageInterface
    {
        return $this->_instance;
    }
}