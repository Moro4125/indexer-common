<?php

namespace Moro\Indexer\Common\View\Storage;

use Moro\Indexer\Common\View\StorageInterface;

/**
 * Class MemoryStorage
 * @package Moro\Indexer\Common\View\Storage
 */
class MemoryStorage implements StorageInterface
{
    protected $_data;
    protected $_map;

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    public function find(string $type, string $id): array
    {
        return array_values($this->_map[$type][$id] ?? []);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @param string $content
     */
    public function save(string $type, string $kind, string $id, string $content)
    {
        $this->_data[$type][$kind][$id] = $content;

        if (false === array_search($kind, $this->_map[$type][$id] ?? [])) {
            $this->_map[$type][$id][] = $kind;
        }
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return string
     */
    public function load(string $type, string $kind, string $id): ?string
    {
        return $this->_data[$type][$kind][$id] ?? null;
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     */
    public function drop(string $type, string $kind, string $id): bool
    {
        $result = isset($this->_data[$type][$kind][$id]);
        unset($this->_data[$type][$kind][$id]);
        $this->_map[$type][$id] = array_diff($this->_map[$type][$id] ?? [], [$kind]);

        return $result;
    }
}