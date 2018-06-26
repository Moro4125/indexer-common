<?php

namespace Moro\Indexer\Common\Regulation\Type\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Regulation\FactoryInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Regulation\Type\Decorator
 */
abstract class AbstractDecorator implements TypeInterface, DecoratorInterface
{
    protected $_instance;

    public function setCode(string $name): TypeInterface
    {
        $this->getDecoratedInstance()
            ->setCode($name);

        return $this;
    }

    public function getCode(): string
    {
        return $this->getDecoratedInstance()
            ->getCode();
    }

    public function setFactory(FactoryInterface $factory): TypeInterface
    {
        $this->getDecoratedInstance()
            ->setFactory($factory);

        return $this;
    }

    public function addInstruction(InstructionInterface $instruction): TypeInterface
    {
        $this->getDecoratedInstance()
            ->addInstruction($instruction);

        return $this;
    }

    public function handle(EntityInterface $entity): ResultInterface
    {
        return $this->getDecoratedInstance()
            ->handle($entity);
    }

    public function getDecoratedInstance(): TypeInterface
    {
        return $this->_instance;
    }
}