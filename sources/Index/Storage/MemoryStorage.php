<?php

namespace Moro\Indexer\Common\Index\Storage;

use Moro\Indexer\Common\Index\StorageInterface;

/**
 * Class MemoryStorage
 * @package Moro\Indexer\Common\Index\Storage
 */
class MemoryStorage implements StorageInterface
{
    protected $_indexes = [null];
    protected $_aliases = [];
    protected $_types   = [];

    /**
     * @param int $index
     * @return null|string
     */
    public function hasAlias(int $index): ?string
    {
        return $this->_aliases[$index] ?? null;
    }

    /**
     * @param string $alias
     * @return int|null
     */
    public function hasIndex(string $alias): ?int
    {
        return $this->_aliases[$alias] ?? null;
    }

    /**
     * @param string $alias
     * @param string $type
     * @return int
     */
    public function addIndex(string $alias, string $type): int
    {
        end($this->_indexes);
        $index = key($this->_indexes);
        $index++;
        $this->_indexes[$index] = [];
        $this->_aliases[$alias] = $index;
        $this->_aliases[$index] = $alias;
        $this->_types[$type][] = $index;

        return $index;
    }

    /**
     * @param string $type
     * @param string|null $id
     * @return array
     */
    public function findIndexes(string $type, string $id = null): array
    {
        $list = [];

        foreach ($this->_types[$type] ?? [] as $index) {
            if ($id === null || isset($this->_indexes[$index][$id])) {
                $list[] = $index;
            }
        }

        return $list;
    }

    /**
     * @param int $index
     * @return bool
     */
    public function dropIndex(int $index): bool
    {
        $alias = $this->hasAlias($index) ?? '';
        $result = !empty($alias);

        unset($this->_indexes[$index]);
        unset($this->_aliases[$alias]);
        unset($this->_aliases[$index]);

        foreach ($this->_types as &$list) {
            if (false !== $key = array_search($index, $list)) {
                unset($list[$key]);
            }
        }

        return $result;
    }

    /**
     * @param string $alias
     * @return null|string
     */
    public function getTypeByIndex(string $alias): ?string
    {
        if (null === $index = $this->hasIndex($alias)) {
            return null;
        }

        foreach ($this->_types ?? [] as $type => $indexes) {
            if (in_array($index, $indexes)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param int $index
     * @param string $id
     * @param string|null $order
     */
    public function insert(int $index, string $id, string $order = null)
    {
        end($this->_indexes[$index]);
        $this->_indexes[$index][$id] = $order ?? (int)key($this->_indexes[$index]);
    }

    /**
     * @param int $index
     * @param int|null $from
     * @param int|null $limit
     * @param bool|null $withOrder
     * @return string[]
     */
    public function select(int $index, int $from = null, int $limit = null, bool $withOrder = null): array
    {
        asort($this->_indexes[$index], SORT_STRING | SORT_ASC);
        $list = $withOrder ? $this->_indexes[$index] : array_keys($this->_indexes[$index]);

        return array_map('strval', array_slice($list, $from ?? 0, $limit, (bool)$withOrder));
    }

    /**
     * @param int $index
     * @param string $id
     * @return bool
     */
    public function remove(int $index, string $id): bool
    {
        $result = isset($this->_indexes[$index][$id]);
        unset($this->_indexes[$index][$id]);

        return $result;
    }
}