<?php

namespace Moro\Indexer\Common\View\Exception;

/**
 * Class KindFailedException
 * @package Moro\Indexer\Common\View\Exception
 */
class KindFailedException extends \RuntimeException
{
    const MSG = 'Kind "%2$s" failed, when handle "%1$s" with ID "%3$s".';

    public $type;
    public $kind;
    public $id;
}