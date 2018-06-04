<?php

namespace Moro\Indexer\Common\Bus\Exception;

use Moro\Indexer\Common\Bus\ExceptionInterface;
use Moro\Indexer\Common\Exception\AdapterFailedInterface;

/**
 * Class AdapterFailedException
 * @package Moro\Indexer\Common\Bus\Exception
 */
class AdapterFailedException extends \RuntimeException implements ExceptionInterface, AdapterFailedInterface
{
    const MSG = 'Adapter "%1$s" is failed, when execute "%2$s" method.';

    public $class;
    public $method;
}