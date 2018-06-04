<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Transaction\DriverInterface;

/**
 * Class DummyDriver
 * @package Moro\Indexer\Test
 */
class DummyDriver implements DriverInterface
{
    protected $_actions = [];

    /**
     * @return void
     */
    public function init()
    {
        $this->_actions[] = __FUNCTION__;
    }

    /**
     * @return void
     */
    public function begin()
    {
        $this->_actions[] = __FUNCTION__;
    }

    /**
     * @return void
     */
    public function commit()
    {
        $this->_actions[] = __FUNCTION__;
    }

    /**
     * @return void
     */
    public function rollback()
    {
        $this->_actions[] = __FUNCTION__;
    }

    /**
     * @return void
     */
    public function free()
    {
        $this->_actions[] = __FUNCTION__;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->_actions;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->_actions = [];
    }
}