<?php

namespace Moro\Indexer\Common\Regulation;

/**
 * Interface FactoryInterface
 * @package Moro\Indexer\Common\Regulation
 */
interface FactoryInterface
{
    /**
     * @return ManagerInterface
     */
    function newManager(): ManagerInterface;

    /**
     * @param string $code
     * @return TypeInterface
     */
    function newType(string $code): TypeInterface;

    /**
     * @return InstructionInterface
     */
    function newInstruction(): InstructionInterface;

    /**
     * @return ResultInterface
     */
    function newResult(): ResultInterface;
}