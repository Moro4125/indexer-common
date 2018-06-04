<?php

namespace Moro\Indexer\Common\View\Factory;

use Moro\Indexer\Common\View\FactoryInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\ManagerInterface;
use Moro\Indexer\Common\View\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerFactory
 * @package Moro\Indexer\Common\View\Factory
 */
class ContainerFactory implements FactoryInterface
{
    private $_container;
    private $_managerKey = ManagerInterface::class;
    private $_typeKey    = TypeInterface::class;
    private $_kindKey    = KindInterface::class;

    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setManagerKey(string $key): ContainerFactory
    {
        $this->_managerKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setTypeKey(string $key): ContainerFactory
    {
        $this->_typeKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKindKey(string $key): ContainerFactory
    {
        $this->_kindKey = $key;

        return $this;
    }

    /**
     * @return ManagerInterface
     */
    public function newManager(): ManagerInterface
    {
        return $this->_container->get($this->_managerKey);
    }

    /**
     * @param string $code
     * @return TypeInterface
     */
    public function newType(string $code): TypeInterface
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->_container->get($this->_typeKey, $code);
    }

    /**
     * @return KindInterface
     */
    public function newKind(): KindInterface
    {
        return $this->_container->get($this->_kindKey);
    }
}