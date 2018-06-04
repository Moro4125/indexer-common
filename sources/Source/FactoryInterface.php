<?php

namespace Moro\Indexer\Common\Source;

/**
 * Interface FactoryInterface
 * @package Moro\Indexer\Common\Source
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
     * @return NormalizerInterface
     */
    function newNormalizer(): NormalizerInterface;

    /**
     * @return EntityInterface
     */
    function newEntity(): EntityInterface;

    /**
     * @return AdapterInterface
     */
    function newAdapter(): AdapterInterface;
}