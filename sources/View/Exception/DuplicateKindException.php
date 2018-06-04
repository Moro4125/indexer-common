<?php

namespace Moro\Indexer\Common\View\Exception;

use Moro\Indexer\Common\View\ExceptionInterface;

/**
 * Class DuplicateKindException
 * @package Moro\Indexer\Common\View\Exception
 */
class DuplicateKindException extends \LogicException implements ExceptionInterface
{
    const MSG = 'Kind "%1$s" already registered for type "%2$s" in "View" manager.';
}