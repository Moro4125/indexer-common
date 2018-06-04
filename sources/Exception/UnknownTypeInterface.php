<?php

namespace Moro\Indexer\Common\Exception;

/**
 * Interface UnknownTypeInterface
 * @package Moro\Indexer\Common\Exception
 */
interface UnknownTypeInterface
{
    function getType(): string;
}