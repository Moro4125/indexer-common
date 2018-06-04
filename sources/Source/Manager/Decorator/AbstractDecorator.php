<?php

namespace Moro\Indexer\Common\Source\Manager\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\ManagerInterface;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Source\Manager\Decorator
 */
abstract class AbstractDecorator implements ManagerInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @return ManagerInterface
     */
    public function getDecoratedInstance(): ManagerInterface
    {
        return $this->_instance;
    }

    /**
     * @param TypeInterface $type
     * @return $this
     */
    public function addType(TypeInterface $type)
    {
        $this->getDecoratedInstance()
            ->addType($type);

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->getDecoratedInstance()
            ->getTypes();
    }

    /**
     * @param string $type
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getIdList(string $type, int $from, int $limit): array
    {
        return $this->getDecoratedInstance()
            ->getIdList($type, $from, $limit);
    }

    /**
     * @param string $type
     * @param string $id
     * @return EntityInterface
     */
    public function getEntity(string $type, string $id): EntityInterface
    {
        $entity = $this->getDecoratedInstance()
            ->getEntity($type, $id);

        return $entity;
    }
}