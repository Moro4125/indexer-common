<?php

namespace Moro\Indexer\Common\Index\Manager;

use Moro\Indexer\Common\Index\Exception\DuplicateIndexException;
use Moro\Indexer\Common\Index\ManagerInterface;
use Moro\Indexer\Common\Index\StorageInterface;

/**
 * Class IndexManager
 * @package Moro\Indexer\Common\Index\Manager
 */
class IndexManager implements ManagerInterface
{
    /** @var StorageInterface */
    protected $_storage;

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
     * @param string $alias
     * @return bool
     */
    public function hasIndex(string $alias): bool
    {
        assert($this->_storage !== null);

        return $this->_storage->hasIndex($alias) !== null;
    }

    /**
     * @param string $alias
     * @param string $type
     */
    public function addIndex(string $alias, string $type)
    {
        assert($this->_storage !== null);

        if ($this->_storage->hasIndex($alias) !== null) {
            throw new DuplicateIndexException(sprintf(DuplicateIndexException::MSG, $alias));
        }

        $index = $this->_storage->addIndex($alias, $type);
        unset($index);
    }

    /**
     * @param string $type
     * @param string|null $id
     * @return array
     */
    public function findIndexes(string $type, string $id = null): array
    {
        assert($this->_storage !== null);

        $list = [];

        foreach ($this->_storage->findIndexes($type, $id) as $index) {
            $list[] = is_numeric($index) ? $this->_storage->hasAlias($index) : $index;
        }

        return $list;
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function dropIndex(string $alias): bool
    {
        assert($this->_storage !== null);

        $result = false;

        if ($index = $this->_storage->hasIndex($alias)) {
            $result = $this->_storage->dropIndex($index);
        }

        return $result;
    }

    /**
     * @param string $index
     * @return null|string
     */
    public function getTypeByIndex(string $index): ?string
    {
        assert($this->_storage !== null);

        $type = $this->_storage->getTypeByIndex($index);
        return $type ? $type : null;
    }

    /**
     * @param string $alias
     * @param string $id
     * @param string|null $order
     */
    public function insert(string $alias, string $id, string $order = null)
    {
        assert($this->_storage !== null);

        if ($index = $this->_storage->hasIndex($alias)) {
            $this->_storage->insert($index, $id, $order);
        }
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
        assert($this->_storage !== null);

        $result = [];

        if ($index = $this->_storage->hasIndex($alias)) {
            $result = $this->_storage->select($index, $from, $limit, $withOrder);
        }

        return $result;
    }

    /**
     * @param string $alias
     * @param string $id
     * @return bool
     */
    public function remove(string $alias, string $id): bool
    {
        assert($this->_storage !== null);

        $result = false;

        if ($index = $this->_storage->hasIndex($alias)) {
            $result = $this->_storage->remove($index, $id);
        }

        return $result;
    }
}