<?php

namespace Moro\Indexer\Common\Bus\Exception;

use Moro\Indexer\Common\Bus\ExceptionInterface;

/**
 * Class CallTimeOutException
 * @package Moro\Indexer\Common\Bus\Exception
 */
class CallTimeOutException extends \RuntimeException implements ExceptionInterface
{
    const MSG = 'Timeout for call message: %1$s';
}