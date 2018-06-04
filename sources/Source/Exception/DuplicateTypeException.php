<?php

namespace Moro\Indexer\Common\Source\Exception;

use Moro\Indexer\Common\Source\ExceptionInterface;

/**
 * Class DuplicateTypeException
 * @package Moro\Indexer\Common\Source\Exception
 */
class DuplicateTypeException extends \LogicException implements ExceptionInterface
{
    const MSG = 'Type "%1$s" already registered in "Source" manager.';
}