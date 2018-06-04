<?php

namespace Moro\Indexer\Common\View;

/**
 * Interface FactoryInterface
 * @package Moro\Indexer\Common\View
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
     * @return KindInterface
     */
    function newKind(): KindInterface;
}