<?php

namespace Moro\Indexer\Common\View\Exception;

use Moro\Indexer\Common\View\ExceptionInterface;

/**
 * Class DuplicateTypeException
 * @package Moro\Indexer\Common\View\Exception
 */
class DuplicateTypeException extends \LogicException implements ExceptionInterface
{
    const MSG = 'Type "%1$s" already registered in "View" manager.';
}