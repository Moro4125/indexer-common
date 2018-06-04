<?php

namespace Moro\Indexer\Common\Source\Exception;

use Moro\Indexer\Common\Source\ExceptionInterface;

/**
 * Class NotFoundException
 * @package Moro\Indexer\Common\Source\Exception
 */
class NotFoundException extends \RuntimeException implements ExceptionInterface
{
    const MSG = 'Entity "%2$s" with ID "%1$s" is not exists.';

    public $id;
    public $type;
}