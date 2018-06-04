<?php

namespace Moro\Indexer\Common\Regulation\Exception;

use Moro\Indexer\Common\Regulation\ExceptionInterface;

/**
 * Class InstructionFailedException
 * @package Moro\Indexer\Common\Regulation\Exception
 */
class InstructionFailedException extends \RuntimeException implements ExceptionInterface
{
    const MSG = 'An error occurred while running method "handle" in "%1$s" for "%2$s" with ID "%3$s.';

    public $type;
    public $id;
}