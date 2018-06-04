<?php

namespace Moro\Indexer\Common\Source\Exception;

use Moro\Indexer\Common\Source\ExceptionInterface;
use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Exception\UnknownTypeTrait;

/**
 * Class UnknownTypeException
 * @package Moro\Indexer\Common\Source\Exception
 */
class UnknownTypeException extends \RuntimeException implements ExceptionInterface, UnknownTypeInterface
{
    const MSG = 'Type "%1$s" is not registered in "Source" manager.';

    use UnknownTypeTrait;
}