<?php

namespace Moro\Indexer\Common\Regulation;

use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\TypeInterface as BaseInterface;

/**
 * Interface TypeInterface
 * @package Moro\Indexer\Common\Regulation
 */
interface TypeInterface extends BaseInterface
{
    /**
     * @param string $code
     * @return $this
     */
    function setCode(string $code): TypeInterface;

    /**
     * @param FactoryInterface $factory
     * @return TypeInterface
     */
    function setResultFactory(FactoryInterface $factory): TypeInterface;

    /**
     * @param InstructionInterface $instruction
     * @return TypeInterface
     */
    function addInstruction(InstructionInterface $instruction): TypeInterface;

    /**
     * @param EntityInterface $entity
     * @return ResultInterface
     *
     * @throws InstructionFailedException
     */
    function handle(EntityInterface $entity): ResultInterface;
}