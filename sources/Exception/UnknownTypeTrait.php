<?php

namespace Moro\Indexer\Common\Exception;

/**
 * Trait UnknownTypeTrait
 * @package Moro\Indexer\Common\Exception
 */
trait UnknownTypeTrait
{
    protected $_type;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }
}