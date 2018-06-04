<?php

namespace Moro\Indexer\Common\View\Exception;

use Moro\Indexer\Common\Exception\UnknownTypeTrait;
use Moro\Indexer\Common\View\ExceptionInterface;

/**
 * Class UnknownKindException
 * @package Moro\Indexer\Common\View\Exception
 */
class UnknownKindException extends \RuntimeException implements ExceptionInterface
{
    use UnknownTypeTrait;

    const MSG = 'Kind "%1$s" is not registered for type "%2$s" in "View" manager.';

    protected $_kind;

    /**
     * @param string $kind
     */
    public function setKind(string $kind)
    {
        $this->_kind = $kind;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->_kind;
    }
}