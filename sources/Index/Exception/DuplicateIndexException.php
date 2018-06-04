<?php

namespace Moro\Indexer\Common\Index\Exception;

use Moro\Indexer\Common\Index\ExceptionInterface;

/**
 * Class DuplicateIndexException
 * @package Moro\Indexer\Common\Index\Exception
 */
class DuplicateIndexException extends \RuntimeException implements ExceptionInterface
{
    const MSG = 'Index "%1$s" already exists.';
}