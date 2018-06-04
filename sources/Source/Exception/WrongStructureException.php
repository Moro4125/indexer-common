<?php

namespace Moro\Indexer\Common\Source\Exception;

use Moro\Indexer\Common\Source\ExceptionInterface;

/**
 * Class WrongStructureException
 * @package Moro\Indexer\Common\Source\Exception
 */
class WrongStructureException extends \RuntimeException implements ExceptionInterface
{
    const MSG_0 = 'Errors in structure of source.';
    const MSG_1 = 'Errors in structure of source for "%2$s" with ID "%1$s".';

    public $id;
    public $type;
}