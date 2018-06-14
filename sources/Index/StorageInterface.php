<?php

namespace Moro\Indexer\Common\Index;

/**
 * Interface StorageInterface
 * @package Moro\Indexer\Common\Index
 */
interface StorageInterface
{
    /**
     * @param string $type
     * @return int
     */
    function lockType(string $type): int;

    /**
     * @param int $key
     * @return $this
     */
    function freeType(int $key): StorageInterface;

    /**
     * @param int $index
     * @return null|string
     */
    function hasAlias(int $index): ?string;

    /**
     * @param string $alias
     * @return int|null
     */
    function hasIndex(string $alias): ?int;

    /**
     * @param string $alias
     * @param string $type
     * @return int
     */
    function addIndex(string $alias, string $type): int;

    /**
     * @param string $type
     * @param string|null $id
     * @return array
     */
    function findIndexes(string $type, string $id = null): array;

    /**
     * @param int $index
     * @return bool
     */
    function dropIndex(int $index): bool;

    /**
     * @param string $alias
     * @return null|string
     */
    function getTypeByIndex(string $alias): ?string;

    /**
     * @param int $index
     * @param string $id
     * @param string|null $order
     */
    function insert(int $index, string $id, string $order = null);

    /**
     * @param int $index
     * @param int|null $from
     * @param int|null $limit
     * @param bool|null $withOrder
     * @return string[]
     */
    function select(int $index, int $from = null, int $limit = null, bool $withOrder = null): array;

    /**
     * @param int $index
     * @param string $id
     * @return bool
     */
    function remove(int $index, string $id): bool;
}