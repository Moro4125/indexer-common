<?php

namespace Moro\Indexer\Common\Regulation\Exception;

use Moro\Indexer\Common\Regulation\ExceptionInterface;

/**
 * Class DuplicateTypeException
 * @package Moro\Indexer\Common\Regulation\Exception
 */
class DuplicateTypeException extends \LogicException implements ExceptionInterface
{
    const MSG = 'Type "%1$s" already registered in "Regulation" manager.';
}