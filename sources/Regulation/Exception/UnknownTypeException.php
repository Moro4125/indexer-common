<?php

namespace Moro\Indexer\Common\Regulation\Exception;

use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Exception\UnknownTypeTrait;
use Moro\Indexer\Common\Regulation\ExceptionInterface;

/**
 * Class UnknownTypeException
 * @package Moro\Indexer\Common\Regulation\Exception
 */
class UnknownTypeException extends \RuntimeException implements ExceptionInterface, UnknownTypeInterface
{
    const MSG = 'Type "%1$s" is not registered in "Regulation" manager.';

    use UnknownTypeTrait;
}