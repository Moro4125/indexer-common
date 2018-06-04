<?php

namespace Moro\Indexer\Common\Regulation;

use Moro\Indexer\Common\Regulation\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Regulation\Exception\UnknownTypeException;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Regulation
 */
interface ManagerInterface
{
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
     * @param EntityInterface $entity
     * @return ResultInterface
     *
     * @throws UnknownTypeException
     * @throws InstructionFailedException
     */
    function handle(string $type, EntityInterface $entity): ResultInterface;
}