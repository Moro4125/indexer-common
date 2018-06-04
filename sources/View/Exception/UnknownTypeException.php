<?php

namespace Moro\Indexer\Common\View\Exception;

use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Exception\UnknownTypeTrait;
use Moro\Indexer\Common\View\ExceptionInterface;

/**
 * Class UnknownTypeException
 * @package Moro\Indexer\Common\View\Exception
 */
class UnknownTypeException extends \RuntimeException implements ExceptionInterface, UnknownTypeInterface
{
    const MSG = 'Type "%1$s" is not registered in "View" manager.';

    use UnknownTypeTrait;
}