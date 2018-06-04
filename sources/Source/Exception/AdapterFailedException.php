<?php

namespace Moro\Indexer\Common\Source\Exception;

use Moro\Indexer\Common\Exception\AdapterFailedInterface;
use Moro\Indexer\Common\Source\ExceptionInterface;

/**
 * Class AdapterFailedException
 * @package Moro\Indexer\Common\Source\Exception
 */
class AdapterFailedException extends \RuntimeException implements ExceptionInterface, AdapterFailedInterface
{
    const MSG = 'An error occurred while get source from adapter "%1$s" for "%2$s" with ID "%3$s.';

    public $type;
    public $id;
}