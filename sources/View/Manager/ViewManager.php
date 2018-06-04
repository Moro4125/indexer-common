<?php

namespace Moro\Indexer\Common\View\Manager;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\Exception\DuplicateTypeException;
use Moro\Indexer\Common\View\Exception\UnknownTypeException;
use Moro\Indexer\Common\View\ManagerInterface;
use Moro\Indexer\Common\View\StorageInterface;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class ViewManager
 * @package Moro\Indexer\Common\View\Manager
 */
class ViewManager implements ManagerInterface
{
    /** @var StorageInterface */
    protected $_storage;
    /** @var TypeInterface[] */
    protected $_types;

    /**
     * @param TypeInterface $type
     * @return ManagerInterface
     */
    public function addType(TypeInterface $type): ManagerInterface
    {
        $code = $type->getCode();

        if (isset($this->_types[$code])) {
            throw new DuplicateTypeException(sprintf(DuplicateTypeException::MSG, $code));
        }

        $this->_types[$code] = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return array_keys($this->_types ?? []);
    }

    /**
     * @param StorageInterface $storage
     * @return ManagerInterface
     */
    public function setStorage(StorageInterface $storage): ManagerInterface
    {
        $this->_storage = $storage;

        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    public function findKinds(string $type, string $id): array
    {
        assert($this->_storage !== null);

        return $this->_storage->find($type, $id);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param EntityInterface $entity
     */
    public function save(string $type, string $kind, EntityInterface $entity)
    {
        assert($this->_storage !== null);

        if (empty($this->_types[$type])) {
            throw new UnknownTypeException(sprintf(UnknownTypeException::MSG, $type));
        }

        $value = $this->_types[$type]->handle($kind, $entity);

        $this->_storage->save($type, $kind, $entity->getId(), $value);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return string
     */
    public function load(string $type, string $kind, string $id): ?string
    {
        assert($this->_storage !== null);

        return $this->_storage->load($type, $kind, $id);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     */
    public function drop(string $type, string $kind, string $id): bool
    {
        assert($this->_storage !== null);

        return $this->_storage->drop($type, $kind, $id);
    }
}