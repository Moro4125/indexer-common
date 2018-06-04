<?php

namespace Moro\Indexer\Common\Regulation\Manager\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Regulation\ManagerInterface;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Regulation\Manager\Decorator
 */
abstract class AbstractDecorator implements ManagerInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @param TypeInterface $type
     * @return $this
     */
    public function addType(TypeInterface $type): ManagerInterface
    {
        $this->getDecoratedInstance()->addType($type);

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->getDecoratedInstance()->getTypes();
    }

    /**
     * @param string $type
     * @param EntityInterface $entity
     * @return ResultInterface
     */
    public function handle(string $type, EntityInterface $entity): ResultInterface
    {
        return $this->getDecoratedInstance()->handle($type, $entity);
    }

    /**
     * @return ManagerInterface
     */
    public function getDecoratedInstance(): ManagerInterface
    {
        return $this->_instance;
    }
}