<?php

namespace Moro\Indexer\Common\Regulation\Factory;

use Moro\Indexer\Common\Regulation\FactoryInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\ManagerInterface;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerFactory
 * @package Moro\Indexer\Common\Regulation\Factory
 */
class ContainerFactory implements FactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_managerKey     = ManagerInterface::class;
    protected $_typeKey        = TypeInterface::class;
    protected $_instructionKey = InstructionInterface::class;
    protected $_resultKey      = ResultInterface::class;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setManagerKey(string $key): FactoryInterface
    {
        $this->_managerKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setTypeKey(string $key): FactoryInterface
    {
        $this->_typeKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setInstructionKey(string $key): FactoryInterface
    {
        $this->_instructionKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setResultKey(string $key): FactoryInterface
    {
        $this->_resultKey = $key;

        return $this;
    }

    /**
     * @return ManagerInterface
     */
    public function newManager(): ManagerInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_managerKey);
    }

    /**
     * @param string $code
     * @return TypeInterface
     */
    public function newType(string $code): TypeInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->_container->get($this->_typeKey, $code);
    }

    /**
     * @return InstructionInterface
     */
    public function newInstruction(): InstructionInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_instructionKey);
    }

    /**
     * @return ResultInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function newResult(): ResultInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_resultKey);
    }
}