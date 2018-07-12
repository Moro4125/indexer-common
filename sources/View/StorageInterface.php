<?php

namespace Moro\Indexer\Common\View;

/**
 * Interface StorageInterface
 * @package Moro\Indexer\Common\View
 */
interface StorageInterface
{
    /**
     * @param string $type
     * @param string $id
     * @return array of "kinds"
     */
    function find(string $type, string $id): array;

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @param string $content
     */
    function save(string $type, string $kind, string $id, string $content);

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    function load(string $type, string $kind, string $id): ?string;

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     */
    function drop(string $type, string $kind, string $id): bool;
}