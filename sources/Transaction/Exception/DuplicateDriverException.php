<?php

namespace Moro\Indexer\Common\Transaction\Exception;

use Moro\Indexer\Common\Transaction\ExceptionInterface;

/**
 * Class DuplicateDriverException
 * @package Moro\Indexer\Common\Transaction\Exception
 */
class DuplicateDriverException extends \LogicException implements ExceptionInterface
{
    const MSG = 'This driver already registered in transaction manager.';
}