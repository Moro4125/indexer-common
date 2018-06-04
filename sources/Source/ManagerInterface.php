<?php

namespace Moro\Indexer\Common\Source;

use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\UnknownTypeException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Source
 */
interface ManagerInterface
{
    /**
     * @param TypeInterface $type
     * @return $this
     *
     * @throws DuplicateTypeException
     */
    function addType(TypeInterface $type);

    /**
     * @return array
     */
    function getTypes(): array;

    /**
     * @param string $type
     * @param int $from
     * @param int $limit
     * @return array
     *
     * @throws UnknownTypeException
     */
    function getIdList(string $type, int $from, int $limit): array;

    /**
     * @param string $type
     * @param string $id
     * @return EntityInterface
     *
     * @throws UnknownTypeException
     * @throws NotFoundException
     * @throws WrongStructureException
     * @throws AdapterFailedException
     */
    function getEntity(string $type, string $id): EntityInterface;
}