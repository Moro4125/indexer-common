<?php

namespace Moro\Indexer\Test;

use Psr\Container\ContainerInterface;

/**
 * Class SimpleContainer
 * @package Moro\Indexer\Test
 */
class SimpleContainer implements ContainerInterface
{
    protected $_map;

    /**
     * @param string $id
     * @param $service
     */
    public function set($id, $service)
    {
        $this->_map[$id] = $service;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->_map);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return clone $this->_map[$id];
    }
}