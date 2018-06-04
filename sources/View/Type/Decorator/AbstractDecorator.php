<?php

namespace Moro\Indexer\Common\View\Type\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\View\Type\Decorator
 */
abstract class AbstractDecorator implements TypeInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @param string $name
     * @return TypeInterface
     */
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

    public function addKind(KindInterface $kind): TypeInterface
    {
        $this->getDecoratedInstance()
            ->addKind($kind);

        return $this;
    }

    /**
     * @param string $kind
     * @param EntityInterface $entity
     * @return string
     */
    public function handle(string $kind, EntityInterface $entity): string
    {
        return $this->getDecoratedInstance()
            ->handle($kind, $entity);
    }

    public function getDecoratedInstance(): TypeInterface
    {
        return $this->_instance;
    }
}