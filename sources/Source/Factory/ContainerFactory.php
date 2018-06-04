<?php

namespace Moro\Indexer\Common\Source\Factory;

use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\FactoryInterface;
use Moro\Indexer\Common\Source\ManagerInterface;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerFactory
 * @package Moro\Indexer\Common\Source\Factory
 */
class ContainerFactory implements FactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_managerKey    = ManagerInterface::class;
    protected $_typeKey       = TypeInterface::class;
    protected $_normalizerKey = NormalizerInterface::class;
    protected $_entityKey     = EntityInterface::class;
    protected $_adapterKey    = AdapterInterface::class;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * @param string $key
     */
    public function setManagerKey(string $key)
    {
        $this->_managerKey = $key;
    }

    /**
     * @param string $key
     */
    public function setTypeKey(string $key)
    {
        $this->_typeKey = $key;
    }

    /**
     * @param string $key
     */
    public function setNormalizerKey(string $key)
    {
        $this->_normalizerKey = $key;
    }

    /**
     * @param string $key
     */
    public function setEntityKey(string $key)
    {
        $this->_entityKey = $key;
    }

    /**
     * @param string $key
     */
    public function setAdapterKey(string $key)
    {
        $this->_adapterKey = $key;
    }

    /**
     * @return ManagerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function newManager(): ManagerInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_managerKey);
    }

    /**
     * @param string $code
     * @return TypeInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function newType(string $code): TypeInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->_container->get($this->_typeKey, $code);
    }

    /**
     * @return NormalizerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function newNormalizer(): NormalizerInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_normalizerKey);
    }

    /**
     * @return EntityInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function newEntity(): EntityInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_entityKey);
    }

    /**
     * @return AdapterInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function newAdapter(): AdapterInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_adapterKey);
    }
}