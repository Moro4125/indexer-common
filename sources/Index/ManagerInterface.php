<?php

namespace Moro\Indexer\Common\Index;

use Moro\Indexer\Common\Index\Exception\DuplicateIndexException;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Index
 */
interface ManagerInterface
{
    /**
     * @param StorageInterface $storage
     * @return $this
     */
    function setStorage(StorageInterface $storage): ManagerInterface;

    /**
     * @param string $alias
     * @return bool
     */
    function hasIndex(string $alias): bool;

    /**
     * @param string $alias
     * @param string $type
     *
     * @throws DuplicateIndexException
     */
    function addIndex(string $alias, string $type);

    /**
     * @param string $type
     * @param string|null $id
     * @return string[]
     */
    function findIndexes(string $type, string $id = null): array;

    /**
     * @param string $alias
     * @return bool
     */
    function dropIndex(string $alias): bool;

    /**
     * @param string $index
     * @return null|string
     */
    function getTypeByIndex(string $index): ?string;

    /**
     * @param string $index
     * @param string $id
     * @param string|null $order
     */
    function insert(string $index, string $id, string $order = null);

    /**
     * @param string $index
     * @param int|null $from
     * @param int|null $limit
     * @param bool|null $withOrder
     * @return array
     */
    function select(string $index, int $from = null, int $limit = null, bool $withOrder = null): array;

    /**
     * @param string $index
     * @param string $id
     * @return bool
     */
    function remove(string $index, string $id): bool;
}