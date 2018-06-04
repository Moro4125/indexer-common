<?php

namespace Moro\Indexer\Common\Transaction\Exception;

use Moro\Indexer\Common\Transaction\ExceptionInterface;

/**
 * Class UnknownFacadeException
 * @package Moro\Indexer\Common\Transaction\Exception
 */
class UnknownFacadeException extends \RuntimeException implements ExceptionInterface
{
    const MSG = 'This facade is not registered in transaction manager.';
}