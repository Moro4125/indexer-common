<?php

namespace Moro\Indexer\Common\Strategy;

use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;

/**
 * Interface WriteStrategyInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface WriteStrategyInterface
{
    /**
     * @param string $type
     * @param string $id
     *
     * @throws UnknownTypeInterface
     * @throws NotFoundException
     * @throws WrongStructureException
     * @throws AdapterFailedException
     * @throws InstructionFailedException
     */
    function updateEntity(string $type, string $id);

    /**
     * @param string $type
     * @param string $id
     */
    function removeEntity(string $type, string $id);
}