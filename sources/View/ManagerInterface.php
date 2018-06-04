<?php

namespace Moro\Indexer\Common\View;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\Exception\DuplicateTypeException;
use Moro\Indexer\Common\View\Exception\KindFailedException;
use Moro\Indexer\Common\View\Exception\UnknownKindException;
use Moro\Indexer\Common\View\Exception\UnknownTypeException;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\View
 */
interface ManagerInterface
{
    /**
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    function setStorage(StorageInterface $storage): ManagerInterface;

    /**
     * @param TypeInterface $type
     * @return ManagerInterface
     *
     * @throws DuplicateTypeException
     */
    function addType(TypeInterface $type): ManagerInterface;

    /**
     * @return array
     */
    function getTypes(): array;

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    function findKinds(string $type, string $id): array;

    /**
     * @param string $type
     * @param string $kind
     * @param EntityInterface $entity
     *
     * @throws UnknownTypeException
     * @throws UnknownKindException
     * @throws KindFailedException
     */
    function save(string $type, string $kind, EntityInterface $entity);

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return string
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