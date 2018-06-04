<?php

namespace Moro\Indexer\Common\View\Manager\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\ManagerInterface;
use Moro\Indexer\Common\View\StorageInterface;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\View\Manager\Decorator
 */
abstract class AbstractDecorator implements ManagerInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @param TypeInterface $type
     * @return ManagerInterface
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
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    public function setStorage(StorageInterface $storage): ManagerInterface
    {
        $this->getDecoratedInstance()->setStorage($storage);
        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    public function findKinds(string $type, string $id): array
    {
        return $this->getDecoratedInstance()->findKinds($type, $id);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param EntityInterface $entity
     */
    public function save(string $type, string $kind, EntityInterface $entity)
    {
        $this->getDecoratedInstance()->save($type, $kind, $entity);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return string
     */
    public function load(string $type, string $kind, string $id): ?string
    {
        return $this->getDecoratedInstance()->load($type, $kind, $id);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     */
    public function drop(string $type, string $kind, string $id): bool
    {
        return $this->getDecoratedInstance()->drop($type, $kind, $id);
    }

    /**
     * @return ManagerInterface
     */
    public function getDecoratedInstance(): ManagerInterface
    {
        return $this->_instance;
    }
}